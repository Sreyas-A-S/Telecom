<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

// Shared Hosting Scheduler Workaround
Route::get('/scheduler/run', function (Request $request) {
    $token = env('SCHEDULER_TOKEN');
    if (!$token || $request->query('token') !== $token) {
        abort(403, 'Unauthorized');
    }

    try {
        Artisan::call('schedule:run');
        return response()->json([
            'status' => 'success',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Emergency Artisan Commands (Internal Execution for CGI-only hosts)
Route::get('/artisan', function (Request $request) {
    $token = env('SCHEDULER_TOKEN');
    if (!$token || $request->query('token') !== $token) {
        abort(403, 'Unauthorized');
    }

    $action = $request->query('action');
    $command = null;

    switch ($action) {
        case 'migrate':
            $command = 'migrate --force';
            break;
        case 'migrate-fresh':
            $command = 'migrate:fresh --force';
            break;
        case 'migrate-fresh-seed':
            $command = 'migrate:fresh --seed --force';
            break;
        case 'db-seed':
            $class = $request->query('class');
            $command = $class ? "db:seed --class=$class --force" : "db:seed --force";
            break;
        case 'optimize':
            $command = 'optimize';
            break;
        case 'optimize-clear':
            $command = 'optimize:clear';
            break;
        case 'storage-link':
            $command = 'storage:link';
            break;
        case 'swagger':
            $command = 'l5-swagger:generate';
            break;
        default:
            return response()->json(['error' => 'Invalid action. Supported: migrate, migrate-fresh, migrate-fresh-seed, db-seed, optimize, optimize-clear, swagger'], 400);
    }

    try {
        Artisan::call($command);
        return response()->json([
            'command' => $command,
            'status' => 'success',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'command' => $command,
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
