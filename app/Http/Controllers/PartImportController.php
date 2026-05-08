<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\PartsImport;
use App\Exports\PartsTemplateExport;
use App\Models\PartImport;
use App\Models\Part;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PartImportController extends Controller
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

        // Create a PartImport record
        PartImport::create([
            'id' => $importId,
            'file_name' => $fileName,
        ]);

        // Initialize progress in cache before queuing
        Cache::put('part_import_progress:' . $importId, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => []
        ], now()->addHours(2));

        try {
            // Perform the import synchronously but with memory optimizations in the Import class
            Excel::import(new PartsImport($importId), Storage::path($filePath));

            return response()->json(['message' => 'Import completed successfully.', 'import_id' => $importId]);
        } catch (\Exception $e) {
            Log::error("Part Import Failed: " . $e->getMessage());

            // Update cache status to failed
            $progress = Cache::get('part_import_progress:' . $importId);
            if ($progress) {
                $progress['status'] = 'failed';
                $progress['results'][] = ['status' => 'failed', 'reason' => $e->getMessage()];
                Cache::put('part_import_progress:' . $importId, $progress, now()->addHours(2));
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
        return Excel::download(new PartsTemplateExport, 'Parts Template.xlsx');
    }

    public function getImportProgress($importId)
    {
        $progress = Cache::get('part_import_progress:' . $importId);

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
        $recentImports = PartImport::withCount('parts')
            ->with(['parts' => function($query) {
                $query->select('id', 'import_id', 'material_description')->take(5);
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return response()->json($recentImports);
    }

    public function undo($importId)
    {
        $import = PartImport::find($importId);

        if (!$import) {
            return response()->json(['message' => 'Import not found.'], 404);
        }

        DB::beginTransaction();

        try {
            // Delete related parts
            $partsDeleted = Part::where('import_id', $importId)->delete();

            // Delete the import record
            $import->delete();

            // Clear cache
            Cache::forget('part_import_progress:' . $importId);

            DB::commit();

            return response()->json([
                'message' => 'Import undone successfully.',
                'parts_deleted' => $partsDeleted,
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
