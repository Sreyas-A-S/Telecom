<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the setting already exists to prevent duplicate entry errors
        if (!Setting::where('key', 'task_continuation_approval')->exists()) {
            Setting::create([
                'key' => 'task_continuation_approval',
                'value' => '1',
                'name' => 'Task Continuation Approval',
                'description' => 'Enable or disable approval for task continuation.'
            ]);
        }

        if (!Setting::where('key', 'casual_leave_limit')->exists()) {
            Setting::create([
                'key' => 'casual_leave_limit',
                'value' => '12',
                'name' => 'Casual Leave Limit',
                'description' => 'Annual limit for casual leaves.'
            ]);
        }

        if (!Setting::where('key', 'sick_leave_limit')->exists()) {
            Setting::create([
                'key' => 'sick_leave_limit',
                'value' => '12',
                'name' => 'Sick Leave Limit',
                'description' => 'Annual limit for sick leaves.'
            ]);
        }

        if (!Setting::where('key', 'privileged_leave_limit')->exists()) {
            Setting::create([
                'key' => 'privileged_leave_limit',
                'value' => '12',
                'name' => 'Privileged Leave Limit',
                'description' => 'Annual limit for privileged leaves.'
            ]);
        }
    }
}
