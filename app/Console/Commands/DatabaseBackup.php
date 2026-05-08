<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--filename= : Custom filename for the backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the entire database to an SQL file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $connection = Config::get('database.default');
        
        if ($connection !== 'mysql') {
            $this->error("Backup is currently only supported for MySQL. Current connection: {$connection}");
            return 1;
        }

        $config = Config::get("database.connections.mysql");
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        $host = $config['host'];
        $port = $config['port'];

        $backupDir = storage_path('backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = $this->option('filename') ?: $database . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
        
        // Ensure filename ends with .sql
        if (!str_ends_with($filename, '.sql')) {
            $filename .= '.sql';
        }
        
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Try to find mysqldump
        $mysqldump = $this->getMysqldumpPath();
        
        // Handle column-statistics issue for MariaDB/MySQL 8 mismatches
        $extraParams = "--column-statistics=0";

        // Merge current environment with MYSQL_PWD to ensure SystemRoot and other 
        // critical Windows variables are preserved for Winsock/networking.
        $env = array_merge(getenv(), ['MYSQL_PWD' => (string)$password]);
        
        $command = "\"{$mysqldump}\" --user=\"{$username}\" --host=\"{$host}\" --port=\"{$port}\" {$extraParams} \"{$database}\" > \"{$filepath}\"";

        $this->comment("Executing backup to: {$filename}");

        $process = Process::fromShellCommandline($command, base_path(), $env);
        $process->setTimeout(300); // 5 minutes
        $process->run();

        if (!$process->isSuccessful()) {
            // Fallback: try without extraParams if it failed due to unknown option
            if (str_contains($process->getErrorOutput(), 'unknown option \'--column-statistics\'')) {
                $command = "\"{$mysqldump}\" --user=\"{$username}\" --host=\"{$host}\" --port=\"{$port}\" \"{$database}\" > \"{$filepath}\"";
                $process = Process::fromShellCommandline($command, base_path(), $env);
                $process->run();
            }
        }

        if (!$process->isSuccessful()) {
            $this->error('Backup failed!');
            $this->error($process->getErrorOutput());
            Log::error('Database backup failed', ['error' => $process->getErrorOutput()]);
            return 1;
        }

        $this->info("Backup successfully saved to: {$filepath}");
        return 0;
    }

    /**
     * Get the path to the mysqldump binary.
     */
    private function getMysqldumpPath()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Common WAMP paths - checking several versions
            $mysqlBinBase = 'C:\wamp64\bin\mysql';
            if (File::exists($mysqlBinBase)) {
                $versions = File::directories($mysqlBinBase);
                // Sort versions to get the latest one
                rsort($versions);
                foreach ($versions as $version) {
                    $dumpPath = $version . '\bin\mysqldump.exe';
                    if (File::exists($dumpPath)) {
                        return $dumpPath;
                    }
                }
            }
            
            // Check if it's in the PATH
            $check = shell_exec('where mysqldump 2>NUL');
            if ($check) {
                return 'mysqldump';
            }
        }

        return 'mysqldump';
    }
}
