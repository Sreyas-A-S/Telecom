<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apps = App\Models\JobApplication::all(['id', 'candidate_name', 'years_of_experience', 'custom_form_responses']);
echo json_encode($apps, JSON_PRETTY_PRINT);
