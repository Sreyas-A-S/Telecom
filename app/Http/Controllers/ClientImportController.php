<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ClientsImport;
use App\Imports\ClientProductsImport;
use App\Exports\ClientsTemplateExport;
use App\Exports\ClientProductsTemplateExport;
use App\Models\ClientImport;
use App\Models\Client;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClientImportController extends Controller
{
    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ClientsTemplateExport, 'Client Import sheet.xlsx');
    }

    public function downloadProductsTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ClientProductsTemplateExport, 'Client Products Import sheet.xlsx');
    }

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

        // Create a ClientImport record
        ClientImport::create([
            'id' => $importId,
            'file_name' => $fileName,
            'type' => 'client',
        ]);

        // Initialize progress in cache
        Cache::put('client_import_progress:' . $importId, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => []
        ], now()->addHours(2));

        try {
            // Perform the import synchronously
            \Maatwebsite\Excel\Facades\Excel::import(new ClientsImport($importId), Storage::path($filePath));

            return response()->json(['message' => 'Import completed successfully.', 'import_id' => $importId]);
        } catch (\Exception $e) {
            Log::error("Client Import Failed: " . $e->getMessage());
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        } finally {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }
    }

    public function importProducts(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $importId = (string) Str::uuid();
        $fileName = time() . '_products_' . $request->file('excel_file')->getClientOriginalName();
        $filePath = $request->file('excel_file')->storeAs('public/imports', $fileName);

        // Create a ClientImport record with type 'product'
        ClientImport::create([
            'id' => $importId,
            'file_name' => $fileName,
            'type' => 'product',
        ]);

        // Initialize progress in cache
        Cache::put('client_import_progress:' . $importId, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => []
        ], now()->addHours(2));

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ClientProductsImport($importId), Storage::path($filePath));
            return response()->json(['message' => 'Products import completed successfully.', 'import_id' => $importId]);
        } catch (\Exception $e) {
            Log::error("Client Products Import Failed: " . $e->getMessage());
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        } finally {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }
    }

    public function getImportProgress($importId)
    {
        $progress = Cache::get('client_import_progress:' . $importId);

        if (!$progress) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Import progress not found or expired.'
            ], 404);
        }

        return response()->json($progress);
    }

    public function getRecentImports(Request $request)
    {
        $type = $request->input('type', 'client');
        $query = ClientImport::where('type', $type);

        if ($type === 'client') {
            $query->withCount('clients as items_count')
                ->with(['clients' => function($q) {
                    $q->select('id', 'import_id', 'name')->take(5);
                }]);
        } elseif ($type === 'product') {
            $query->withCount('clientProducts as items_count')
                ->with(['clientProducts' => function($q) {
                    $q->select('id', 'import_id', 'machine_serial_number as name')->take(5);
                }]);
        } elseif ($type === 'update_product') {
            $query->withCount('updatedClientProducts as items_count')
                ->with(['updatedClientProducts' => function($q) {
                    $q->select('id', 'update_import_id', 'machine_serial_number as name')->take(5);
                }]);
        }

        $recentImports = $query->orderBy('created_at', 'desc')->take(5)->get();
        return response()->json($recentImports);
    }

    public function downloadUpdateProductsTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UpdateClientProductsTemplateExport, 'Update Client Products Import sheet.xlsx');
    }

    public function updateProducts(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $importId = (string) Str::uuid();
        $fileName = time() . '_update_products_' . $request->file('excel_file')->getClientOriginalName();
        $filePath = $request->file('excel_file')->storeAs('public/imports', $fileName);

        ClientImport::create([
            'id' => $importId,
            'file_name' => $fileName,
            'type' => 'update_product',
        ]);

        Cache::put('client_import_progress:' . $importId, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => []
        ], now()->addHours(2));

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UpdateClientProductsImport($importId), Storage::path($filePath));
            return response()->json(['message' => 'Products update completed successfully.', 'import_id' => $importId]);
        } catch (\Exception $e) {
            Log::error("Client Products Update Failed: " . $e->getMessage());
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        } finally {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }
    }

    public function undo($importId)
    {
        $import = ClientImport::find($importId);

        if (!$import) {
            return response()->json(['message' => 'Import not found.'], 404);
        }

        DB::beginTransaction();

        try {
            $itemsDeleted = 0;
            if ($import->type === 'product') {
                // Delete related leads and client products for product import
                $itemsDeleted = \App\Models\ClientProduct::where('import_id', $importId)->delete();
                \App\Models\Lead::where('import_id', $importId)->delete();
            } else if ($import->type === 'update_product') {
                // Restore old machine serial number and engine serial number
                $updatedProducts = \App\Models\ClientProduct::where('update_import_id', $importId)->get();
                foreach ($updatedProducts as $product) {
                    $product->machine_serial_number = $product->old_machine_serial_number;
                    if ($product->old_engine_serial_number) {
                        $product->engine_serial_number = $product->old_engine_serial_number;
                        $product->old_engine_serial_number = null;
                    }
                    $product->update_import_id = null;
                    $product->old_machine_serial_number = null;
                    $product->save();
                    $itemsDeleted++;
                }
            } else {
                // Delete related clients for client import
                $itemsDeleted = Client::where('import_id', $importId)->delete();
            }

            // Delete the import record
            $import->delete();

            // Clear cache
            Cache::forget('client_import_progress:' . $importId);

            DB::commit();

            return response()->json([
                'message' => 'Import undone successfully.',
                'items_deleted' => $itemsDeleted,
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
