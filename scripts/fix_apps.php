<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apps = App\Models\JobApplication::all();
foreach ($apps as $app) {
    if (empty($app->custom_form_responses)) continue;

    $custom = $app->custom_form_responses;
    $changed = false;

    foreach ($custom as $resp) {
        if (!isset($resp['label']) || !isset($resp['value'])) continue;

        $label = strtolower(trim($resp['label']));
        $val = $resp['value'];

        if (in_array($label, ['full name', 'candidate name', 'name']) && empty($app->candidate_name)) {
            $app->candidate_name = $val;
            $changed = true;
        }
        if (in_array($label, ['highest qualification', 'qualification', 'degree']) && empty($app->educational_qualification)) {
            $app->educational_qualification = $val;
            $changed = true;
        }
        if (str_contains($label, 'experience') && empty($app->years_of_experience)) {
            $app->years_of_experience = $val;
            $changed = true;
        }
        if (in_array($label, ['current employer', 'company']) && empty($app->current_employer)) {
            $app->current_employer = $val;
            $changed = true;
        }
        if (str_contains($label, 'email') && empty($app->email_id)) {
            $app->email_id = $val;
            $changed = true;
        }
        if (str_contains($label, 'contact') && empty($app->contact_number)) {
            $app->contact_number = $val;
            $changed = true;
        }
        if (str_contains($label, 'ctc') && str_contains($label, 'current') && empty($app->last_current_ctc)) {
            $app->last_current_ctc = $val;
            $changed = true;
        }
        if (str_contains($label, 'ctc') && str_contains($label, 'expected') && empty($app->expected_ctc)) {
            $app->expected_ctc = $val;
            $changed = true;
        }
    }

    if ($changed) {
        $app->save();
        echo "Fixed App #" . $app->id . "\n";
    }
}
echo "Done fixing apps.\n";
