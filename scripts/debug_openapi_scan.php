<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use OpenApi\Generator;

$paths = [
    __DIR__ . '/app/Http/Controllers',
    __DIR__ . '/app/Models'
];

// Use Generator::scan for compatibility
$analysis = \OpenApi\Generator::scan($paths);

// Access components property for discovered schemas
$components = $analysis->components ?? null;
$schemas = [];
if ($components && isset($components->schemas) && is_array($components->schemas)) {
    foreach ($components->schemas as $name => $schema) {
        // schema keys may be objects or strings depending on analysis
        $schemas[] = (string) $name;
    }
}

echo "Discovered schemas:\n";
foreach ($schemas as $s) {
    echo " - $s\n";
}

// For debugging: show any warnings collected
if (property_exists($analysis, 'openapi')) {
    // nothing
}

$analysisFile = __DIR__ . '/storage/logs/openapi_analysis_debug.json';
file_put_contents($analysisFile, json_encode([
    'schemas' => $schemas,
    'paths' => array_keys($analysis->paths ?? []),
], JSON_PRETTY_PRINT));

echo "Wrote analysis to $analysisFile\n";
