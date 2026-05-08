<?php

/**
 * Shared Hosting Scheduler Workaround (CGI-Safe)
 * This script boots Laravel and executes the scheduler.
 */

// 1. Define the project root
$projectRoot = __DIR__;

// 2. Load the Composer Autoloader
require $projectRoot . '/vendor/autoload.php';

// 3. Boot Laravel
$app = require_once $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// 4. Set the script to ignore user aborts and run for as long as needed
ignore_user_abort(true);
set_time_limit(0);

// 5. Build a custom input to call Artisan schedule:run
try {
    // We simulate a CLI call using strings
    $status = $kernel->call('schedule:run');
    $output = $kernel->output();

    // Log the output if needed (optional)
    // file_put_contents($projectRoot . '/storage/logs/cron.log', "[" . date('Y-m-d H:i:s') . "] " . $output . PHP_EOL, FILE_APPEND);

    echo "Scheduler ran successfully.\n";
    echo $output;
} catch (Exception $e) {
    echo "Error running scheduler: " . $e->getMessage();
}

exit($status ?? 0);
