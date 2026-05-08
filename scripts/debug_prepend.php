<?php
// debug_prepend.php
// This file is auto-prepended to log included files during a PHP run.
register_shutdown_function(function() {
    $logPath = __DIR__ . '/storage/logs/includes_l5swg.json';
    $data = [
        'time' => date('c'),
        'included_files' => array_values(get_included_files()),
    ];
    @file_put_contents($logPath, json_encode($data, JSON_PRETTY_PRINT));
});

// ensure errors get logged right away
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

?>