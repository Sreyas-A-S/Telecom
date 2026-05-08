<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ProductsImport;
use App\Exports\ProductsTemplateExport;
use App\Models\ProductImport;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductImportController extends Controller
{
    public function import(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $importId = (string) Str::uuid();
        $fileName = time() . '_' . $request->file('excel_file')->getClientOriginalName();
        $filePath = $request->file('excel_file')->storeAs('public/imports', $fileName);

        // Create a ProductImport record
        ProductImport::create([
            'id' => $importId,
            'file_name' => $fileName,
        ]);

        try {
            // Perform the import synchronously (for now, as per user environment)
            Excel::import(new ProductsImport($importId), Storage::path($filePath));

            return response()->json(['message' => 'Import completed successfully.', 'import_id' => $importId]);
        } catch (\Exception $e) {
            Log::error("Product Import Failed: " . $e->getMessage());

            // Update cache status to failed
            $progress = Cache::get('product_import_progress:' . $importId);
            if ($progress) {
                $progress['status'] = 'failed';
                $progress['results'][] = ['status' => 'failed', 'reason' => $e->getMessage()];
                Cache::put('product_import_progress:' . $importId, $progress, now()->addHours(2));
            }

            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        } finally {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductsTemplateExport, 'Products Template.xlsx');
    }

    public function getImportProgress($importId)
    {
        $progress = Cache::get('product_import_progress:' . $importId);

        if (!$progress) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Import progress not found or expired.'
            ], 404);
        }

        return response()->json($progress);
    }

    public function getRecentImports()
    {
        $recentImports = ProductImport::withCount('products')
            ->with(['products' => function($query) {
                $query->select('id', 'import_id', 'name')->take(5);
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return response()->json($recentImports);
    }

    public function undo($importId)
    {
        $import = ProductImport::find($importId);

        if (!$import) {
            return response()->json(['message' => 'Import not found.'], 404);
        }

        DB::beginTransaction();

        try {
            // Delete related products through Eloquent to trigger cascade deletion hooks
            $products = Product::where('import_id', $importId)->get();
            $productsDeleted = $products->count();
            $products->each->delete();

            // Delete the import record
            $import->delete();

            // Clear cache
            Cache::forget('product_import_progress:' . $importId);

            DB::commit();

            return response()->json([
                'message' => 'Import undone successfully.',
                'products_deleted' => $productsDeleted,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error undoing import.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
