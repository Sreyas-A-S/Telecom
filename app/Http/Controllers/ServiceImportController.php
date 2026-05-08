<?php

namespace App\Http\Controllers;

use App\Imports\ServicesImport;
use App\Models\Service;
use App\Models\ServiceImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Exports\ServicesTemplateExport;

class ServiceImportController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new ServicesTemplateExport, 'Service Import sheet.xlsx');
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

        // Create a ServiceImport record
        ServiceImport::create([
            'id' => $importId,
            'file_name' => $fileName,
        ]);

        // Initialize progress in cache
        Cache::put('service_import_progress:' . $importId, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => [],
            'errors' => []
        ], now()->addHours(2));

        try {
            // Perform the import synchronously
            Excel::import(new ServicesImport($importId), Storage::path($filePath));
        } catch (\Exception $e) {
            Log::error("Service Import Failed to dispatch for ID: {$importId} - " . $e->getMessage());

            $progress = Cache::get('service_import_progress:' . $importId);
            if ($progress) {
                $progress['status'] = 'failed';
                $progress['errors'][] = 'Failed to start import: ' . $e->getMessage();
                Cache::put('service_import_progress:' . $importId, $progress, now()->addHours(2));
            }

            // Delete the ServiceImport record on failure to dispatch
            $importRecord = ServiceImport::find($importId);
            if ($importRecord) {
                $importRecord->delete();
            }
            
            return response()->json(['message' => 'Failed to start import.'], 500);
        }

        return response()->json(['message' => 'Import started successfully. You can track progress on this page.', 'import_id' => $importId]);
    }

    public function undo($import_id)
    {
        $import = ServiceImport::with('services')->find($import_id);

        if (!$import) {
            return response()->json(['message' => 'Import not found.'], 404);
        }

        DB::beginTransaction();

        try {
            $servicesDeleted = $import->services()->delete();
            $import->delete();

            Cache::forget('service_import_progress:' . $import_id);

            DB::commit();

            return response()->json([
                'message' => 'Import undone successfully.',
                'services_deleted' => $servicesDeleted,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error undoing import.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRecentImports()
    {
        $recentImports = ServiceImport::withCount('services')
            ->with(['services' => function($query) {
                $query->select('id', 'import_id', 'name')->take(5);
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return response()->json($recentImports);
    }

    public function getImportProgress(Request $request, $importId)
    {
        $progress = Cache::get('service_import_progress:' . $importId);

        if (!$progress) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Import progress not found or expired.'
            ], 404);
        }

        return response()->json($progress);
    }
}
