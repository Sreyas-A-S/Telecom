<?php

namespace App\Http\Controllers;

use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Models\User;
use App\Models\EmployeeImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
// use App\Jobs\ProcessEmployeeImport; // Removed
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Exports\EmployeesTemplateExport;

class EmployeeImportController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new EmployeesTemplateExport, 'Employee Import sheet.xlsx');
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

        // Create an EmployeeImport record
        $importPayload = [
            'id' => $importId,
        ];

        if (Schema::hasColumn('employee_imports', 'file_name')) {
            $importPayload['file_name'] = $fileName;
        }

        EmployeeImport::create($importPayload);

        // Initialize progress in cache
        Cache::put('import_progress:' . $importId, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => [],
            'errors' => []
        ], now()->addHours(2));

        try {
            // Perform the import synchronously
            Excel::import(new EmployeesImport($importId), Storage::path($filePath));

            // The afterImport event in EmployeesImport will set status to 'completed'
            $progress = Cache::get('import_progress:' . $importId);
            if ($progress) {
                $resultsCount = is_array($progress['results'] ?? null) ? count($progress['results']) : 0;
                $importedCount = Employee::where('import_id', $importId)->count();
                $derivedTotal = max(
                    (int) ($progress['total_rows'] ?? 0),
                    (int) ($progress['processed_rows'] ?? 0),
                    $resultsCount,
                    $importedCount
                );

                if ($derivedTotal > 0) {
                    $progress['total_rows'] = $derivedTotal;
                    $progress['processed_rows'] = $derivedTotal;
                    $progress['percentage'] = 100;
                }

                if (($progress['status'] ?? 'pending') !== 'failed') {
                    $progress['status'] = 'completed';
                }

                Cache::put('import_progress:' . $importId, $progress, now()->addHours(2));
            }

        } catch (\Exception $e) {
            Log::error("Employee Import Failed for ID: {$importId} - " . $e->getMessage());

            $errorMessage = 'An unexpected error occurred during the import.';
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1062) {
                preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $e->getMessage(), $matches);
                if (count($matches) > 2) {
                    $duplicateEntry = $matches[1];
                    $key = $matches[2];
                    $errorMessage = "Duplicate entry found: \"{$duplicateEntry}\". Please ensure all employee emails and IDs are unique.";
                } else {
                    $errorMessage = 'A duplicate entry was found. Please ensure all employee emails and IDs are unique.';
                }
            }

            $progress = Cache::get('import_progress:' . $importId) ?? [
                'total_rows' => 0,
                'processed_rows' => 0,
                'percentage' => 0,
                'status' => 'pending',
                'results' => [],
                'errors' => []
            ];

            $importedCount = Employee::where('import_id', $importId)->count();

            if ($importedCount > 0) {
                $resultsCount = is_array($progress['results'] ?? null) ? count($progress['results']) : 0;
                $derivedTotal = max(
                    (int) ($progress['total_rows'] ?? 0),
                    (int) ($progress['processed_rows'] ?? 0),
                    $resultsCount,
                    $importedCount
                );

                if ($derivedTotal > 0) {
                    $progress['total_rows'] = $derivedTotal;
                    $progress['processed_rows'] = $derivedTotal;
                    $progress['percentage'] = 100;
                }

                $progress['status'] = 'completed';
                $progress['errors'][] = 'Import completed with warnings: ' . $errorMessage;
                Cache::put('import_progress:' . $importId, $progress, now()->addHours(2));
            } else {
                $progress['status'] = 'failed';
                $progress['errors'][] = $errorMessage;
                Cache::put('import_progress:' . $importId, $progress, now()->addHours(2));

                // Delete the EmployeeImport record only when no rows were imported
                $importRecord = EmployeeImport::find($importId);
                if ($importRecord) {
                    $importRecord->delete();
                }
            }
        } finally {
            // Clean up the temporary file
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }

        return response()->json(['message' => 'Import started successfully.', 'import_id' => $importId]);
    }


    public function undo($import_id)
    {
        $import = EmployeeImport::with('employees')->find($import_id);

        if (!$import) {
            return response()->json(['message' => 'Import not found.'], 404);
        }

        DB::beginTransaction();

        try {
            // Get user IDs before deleting employees
            $userIds = $import->employees()->pluck('user_id')->filter();

            // Count employees before deleting
            $employeeCount = $import->employees()->count();

            // Delete related employees
            $employeesDeleted = $import->employees()->delete();

            // Delete related users
            if ($userIds->isNotEmpty()) {
                User::whereIn('id', $userIds)->delete();
            }

            // Delete the import record
            $importDeleted = $import->delete();

            // Also remove progress from cache if it exists
            Cache::forget('import_progress:' . $import_id);

            DB::commit();

            // Verify that both deletions succeeded
            if ($importDeleted && ($employeeCount === 0 || $employeesDeleted >= 0)) {
                return response()->json([
                    'message' => 'Import undone successfully.',
                    'employees_deleted' => $employeesDeleted,
                ]);
            }

            return response()->json([
                'message' => 'Failed to fully undo import.',
                'employees_deleted' => $employeesDeleted ?? 0,
                'import_deleted' => $importDeleted,
            ], 500);
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
        $recentImports = EmployeeImport::withCount('employees')
            ->with(['employees' => function($query) {
                $query->select('id', 'import_id', 'name')->take(5);
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return response()->json($recentImports);
    }

    public function getImportProgress(Request $request, $importId)
    {
        $progress = Cache::get('import_progress:' . $importId);

        if (!$progress) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Import progress not found or expired.'
            ], 404);
        }

        return response()->json($progress);
    }
}
