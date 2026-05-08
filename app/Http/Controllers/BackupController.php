<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $backups = [];
            $path = storage_path('app/backups');

            if (File::exists($path)) {
                $files = File::files($path);

                foreach ($files as $file) {
                    // $file is a SplFileInfo object
                    if ($file->getExtension() !== 'zip') {
                        continue;
                    }

                    $backups[] = [
                        'name' => $file->getFilename(), // just filename
                        'path' => $file->getPathname(), // full path
                        'size' => $this->formatSizeUnits($file->getSize()),
                        'date' => Carbon::createFromTimestamp($file->getMTime())->toDateTimeString(),
                        'timestamp' => $file->getMTime() // for sorting
                    ];
                }
            }

            // Sort by date desc
            usort($backups, function ($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });

            return response()->json(['data' => $backups]);
        }

        $organizedTables = $this->getOrganizedTables();
        return view('settings.backups.index', compact('organizedTables'));
    }

    public function create()
    {
        return redirect()->route('backups.index');
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'full'); // full or selective
        $tables = $request->input('tables', []);
        $includeFiles = $request->has('include_files');

        $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s');
        $tempPath = storage_path('app/temp_backups/' . $filename);

        if (!File::exists($tempPath)) {
            File::makeDirectory($tempPath, 0755, true);
        }

        // 1. Database Backup
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $sqlFile = $tempPath . '/database.sql';

        // Construct command
        $mysqldumpPath = $this->getBinaryPath('mysqldump');

        // Handle column-statistics issue for MariaDB/MySQL 8 mismatches
        $extraParams = "--column-statistics=0";

        // Command structure: mysqldump [options] db_name [tables]
        // Important: Tables must be the LAST arguments if they are specified

        $cmdTables = "";
        if ($type === 'selective' && !empty($tables)) {
            // Escape table names just in case
            $escapedTables = array_map(function ($t) {
                return escapeshellarg($t);
            }, $tables);
            $cmdTables = " " . implode(' ', $escapedTables);
        }

        // Use MYSQL_PWD env var to avoid warning and corruption
        putenv("MYSQL_PWD={$dbPass}");
        $command = "\"{$mysqldumpPath}\" --user=\"{$dbUser}\" --host=\"{$dbHost}\" --port=\"{$dbPort}\" {$extraParams} \"{$dbName}\"{$cmdTables} > \"{$sqlFile}\"";

        // Log the command for debugging
        \Illuminate\Support\Facades\Log::info("Backup Command Executed via MYSQL_PWD");

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        putenv("MYSQL_PWD"); // Clear env var

        if ($returnVar !== 0) {
            // cleanup
            File::deleteDirectory($tempPath);
            $errorMessage = implode("\n", $output);
            // Fallback: if output is empty, maybe return var has a clue, but usually output has stderr
            if (empty($errorMessage)) {
                $errorMessage = "Command failed with exit code $returnVar. Check logs.";
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Database backup failed: ' . $errorMessage], 500);
            }
            return redirect()->back()->with('error', 'Database backup failed: ' . $errorMessage);
        }

        // 2. Zip creation
        $zipPath = storage_path('app/backups/' . $filename . '.zip');
        // Ensure backups dir exists
        if (!File::exists(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            // Add SQL
            $zip->addFile($sqlFile, 'database.sql');

            // Add Files if requested or if full backup (implies files usually, but let's stick to user choice or default)
            // For logic simplicity: Full = DB + Storage. Selective = DB(selected) + Storage(optional)

            if ($type === 'full' || ($type === 'selective' && $includeFiles)) {
                $filesPath = public_path('storage'); // Usually typicall uploads are here
                // Note: Recursive zip can be slow. Limiting to storage/uploads generally.
                // Assuming standard Laravel storage link exists: public/storage -> storage/app/public
                $source = storage_path('app/public');
                if (File::exists($source)) {
                    $this->addFolderToZip($source, $zip, 'storage');
                }
            }

            $zip->close();
        } else {
            File::deleteDirectory($tempPath);
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to create zip file.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to create zip file.');
        }

        // Cleanup temp
        File::deleteDirectory($tempPath);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Backup created successfully.']);
        }
        return redirect()->route('backups.index')->with('success', 'Backup created successfully.');
    }

    public function download($backupName)
    {
        $path = storage_path('app/backups/' . $backupName);
        if (!File::exists($path)) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        return response()->download($path);
    }

    public function destroy($backupName)
    {
        $path = storage_path('app/backups/' . $backupName);
        if (File::exists($path)) {
            File::delete($path);
            return redirect()->route('backups.index')->with('success', 'Backup deleted successfully.');
        }
        return redirect()->back()->with('error', 'Backup not found.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip',
        ]);

        $file = $request->file('backup_file');
        $filename = 'uploaded_' . time() . '_' . $file->getClientOriginalName();

        // Store in backups folder manually to ensure path correctness
        $movePath = storage_path('app/backups');
        $file->move($movePath, $filename);

        return redirect()->route('backups.index')->with('success', 'Backup uploaded successfully. You can now restore from it.');
    }

    public function restore(Request $request, $backupName)
    {
        // Increase memory and time limits for restore
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $path = storage_path('app/backups/' . $backupName);
        if (!File::exists($path)) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        $tempPath = storage_path('app/temp_restore/' . time());
        if (!File::exists($tempPath)) {
            File::makeDirectory($tempPath, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($path) === TRUE) {
            $zip->extractTo($tempPath);
            $zip->close();
        } else {
            return redirect()->back()->with('error', 'Failed to open backup zip.');
        }

        // 1. Restore Database
        $sqlFile = $tempPath . '/database.sql';
        if (File::exists($sqlFile)) {
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            // Construct command
            $mysqlPath = $this->getBinaryPath('mysql');
            // Construct command
            $mysqlPath = $this->getBinaryPath('mysql');

            // Use MYSQL_PWD env var to avoid warning
            putenv("MYSQL_PWD={$dbPass}");
            $command = "\"{$mysqlPath}\" --user=\"{$dbUser}\" --host=\"{$dbHost}\" --port=\"{$dbPort}\" \"{$dbName}\" < \"{$sqlFile}\" 2>&1";

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);
            putenv("MYSQL_PWD"); // Clear env var

            if ($returnVar !== 0) {
                File::deleteDirectory($tempPath);
                $errorMessage = implode("\n", $output);
                \Illuminate\Support\Facades\Log::error("Restore failed: " . $errorMessage);
                return redirect()->back()->with('error', 'Database restore failed: ' . $errorMessage);
            }
        }

        // 2. Restore Files
        $storageSource = $tempPath . '/storage';
        if (File::exists($storageSource)) {
            $destination = storage_path('app/public');
            // Copy directory content. using shell for speed if linux or specialized iterator
            File::copyDirectory($storageSource, $destination);
        }

        File::deleteDirectory($tempPath);

        return redirect()->route('backups.index')->with('success', 'System restored successfully.');
    }

    private function getBinaryPath($binary)
    {
        // Check standard WAMP path for recent MySQL version found: 8.3.0
        $wampPath = "c:\\wamp64\\bin\\mysql\\mysql8.3.0\\bin\\{$binary}.exe";
        if (file_exists($wampPath)) {
            return $wampPath;
        }

        // Fallback to checking if it is in PATH
        return $binary;
    }

    protected function getOrganizedTables()
    {
        $tables = DB::select('SHOW TABLES');
        $allTables = array_map(function ($table) {
            return array_values((array)$table)[0];
        }, $tables);

        // Define Module Mappings
        $modules = [
            'Users & Authentication' => ['users', 'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions', 'password_resets', 'personal_access_tokens', 'sessions'],
            'HR & Employees' => ['employees', 'departments', 'designations', 'attendances', 'leave_requests', 'performance_reviews', 'interviews', 'candidates', 'salaries', 'payrolls'],
            'Products & Inventory' => ['products', 'categories', 'sub_categories', 'brands', 'product_meta', 'product_models', 'model_series', 'parts', 'service_kits', 'taxes'],
            'Sales & CRM' => ['leads', 'clients', 'deals', 'pipelines', 'loss_orders', 'dealerships', 'zones', 'districts', 'lead_sources', 'lead_categories', 'followups'],
            'Services & Operations' => ['services', 'entries', 'tasks', 'task_followups', 'fsr_reports', 'fsr_quotations', 'live_locations', 'gps_traces', 'package_kits'],
            'Financials' => ['expenses', 'expense_requests', 'settlements', 'loans', 'loan_requests', 'invoices', 'payments'],
            'Settings & System' => ['settings', 'brand_settings', 'migrations', 'failed_jobs', 'jobs', 'notifications', 'activity_log'],
        ];

        $organizedTables = [];
        $assignedTables = [];

        foreach ($modules as $moduleName => $keywords) {
            $organizedTables[$moduleName] = [];
            foreach ($allTables as $table) {
                foreach ($keywords as $keyword) {
                    if (str_contains($table, $keyword)) {
                        $organizedTables[$moduleName][] = $table;
                        $assignedTables[] = $table;
                        break; // Assign to first matching module
                    }
                }
            }
        }

        // Catch-all for remaining tables
        $others = array_diff($allTables, $assignedTables);
        if (!empty($others)) {
            $organizedTables['Others'] = array_values($others);
        }

        return array_filter($organizedTables);
    }
    private function addFolderToZip($dir, $zipArchive, $zipDir = '')
    {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                // Add the directory
                if (!empty($zipDir)) {
                    $zipArchive->addEmptyDir($zipDir);
                }
                while (($file = readdir($dh)) !== false) {
                    if (!is_file($dir . $file) && !is_dir($dir . $file)) {
                        continue;
                    }
                    if (in_array($file, ['.', '..'])) {
                        continue;
                    }

                    $root = $dir . '/' . $file;
                    $newZipDir = empty($zipDir) ? $file : $zipDir . '/' . $file;

                    if (is_dir($root)) {
                        $this->addFolderToZip($root, $zipArchive, $newZipDir);
                    } else {
                        $zipArchive->addFile($root, $newZipDir);
                    }
                }
            }
        }
    }

    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }
}
