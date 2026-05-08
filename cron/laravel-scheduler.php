<?php

use Illuminate\Contracts\Console\Kernel;

// Set the current working directory to the project root
chdir(__DIR__ . '/..');

// Load the autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Run the scheduler
$kernel = $app->make(Kernel::class);

echo "Running scheduler...\n";

$status = $kernel->call('schedule:run');

echo "Scheduler finished with status: " . $status . "\n";
