<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

use App\Models\LeadItem;
use App\Observers\LeadItemObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        if (str_contains(url()->current(), 'ngrok-free.app')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Relation::morphMap([
            'Employee' => 'App\\Models\\Employee',
            'Agent' => 'App\\Models\\Agent',
        ]);

        LeadItem::observe(LeadItemObserver::class);

        // Automatically backup database before migrate:fresh
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if ($event->command === 'migrate:fresh') {
                $output = $event->output;
                $output->writeln("\n<bg=blue;fg=white;options=bold> AUTOMATIC BACKUP </>\n");
                $output->writeln("<info>Command detected: migrate:fresh. Starting pre-migration database backup...</info>");
                
                try {
                    // We pass $output to Artisan::call so the backup progress prints directly to the terminal
                    Artisan::call('db:backup', [
                        '--filename' => 'pre_migrate_fresh_' . date('Ymd_His') . '.sql'
                    ], $output);
                    
                    $output->writeln("\n<info>✓ Backup completed. Proceeding with migration...</info>\n");
                } catch (\Exception $e) {
                    $output->writeln("\n<error>✗ Automatic backup failed: " . $e->getMessage() . "</error>");
                    $output->writeln("<comment>Continuing with migration anyway...</comment>\n");
                    Log::error('Automatic pre-migration backup failed: ' . $e->getMessage());
                }
            }
        });
    }
}
