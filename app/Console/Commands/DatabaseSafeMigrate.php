<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DatabaseSafeMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:safe-migrate {--seed : Seed the database after migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database before running migrate:fresh';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting safe migration process...');

        // 1. Run backup
        $exitCode = Artisan::call('db:backup');
        $this->info(Artisan::output());

        if ($exitCode !== 0) {
            if (!$this->confirm('Database backup failed. Do you want to continue with the migration anyway?', false)) {
                $this->error('Aborting migration.');
                return 1;
            }
        }

        // 2. Run migrate:fresh
        $this->info('Running migrate:fresh...');
        $params = ['--force' => true];
        if ($this->option('seed')) {
            $params['--seed'] = true;
        }

        Artisan::call('migrate:fresh', $params);
        $this->info(Artisan::output());

        $this->info('Safe migration completed successfully.');
        return 0;
    }
}
