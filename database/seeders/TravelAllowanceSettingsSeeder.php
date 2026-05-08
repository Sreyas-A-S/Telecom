<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class TravelAllowanceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'travel_allowance_rate',
                'name' => 'Travel Allowance Rate (Legacy Default)',
                'description' => 'Legacy default travel allowance rate per kilometer',
                'value' => '10'
            ],
            [
                'key' => 'travel_allowance_max_daily',
                'name' => 'Maximum Daily Travel Allowance',
                'description' => 'Maximum amount that can be claimed per day',
                'value' => '1000'
            ],
            [
                'key' => 'travel_allowance_other',
                'name' => 'Other Rate (Default)',
                'description' => 'Default travel allowance rate per kilometer for "other" vehicle type',
                'value' => '10'
            ],
            [
                'key' => 'travel_allowance_walk',
                'name' => 'Walk Rate',
                'description' => 'Travel allowance rate for walk per kilometer',
                'value' => '5'
            ],
            [
                'key' => 'travel_allowance_bike',
                'name' => 'Bike Rate',
                'description' => 'Travel allowance rate for bike per kilometer',
                'value' => '5'
            ],
            [
                'key' => 'travel_allowance_car',
                'name' => 'Car Rate',
                'description' => 'Travel allowance rate for car per kilometer',
                'value' => '10'
            ],
            [
                'key' => 'travel_allowance_bus',
                'name' => 'Bus Rate',
                'description' => 'Travel allowance rate for bus per kilometer',
                'value' => '10'
            ],
            [
                'key' => 'travel_allowance_train',
                'name' => 'Train Rate',
                'description' => 'Travel allowance rate for train per kilometer',
                'value' => '10'
            ],
            [
                'key' => 'travel_allowance_engineer_rate_per_call',
                'name' => 'Engineer Rate Per Call',
                'description' => 'Per-call travel allowance for engineers',
                'value' => '0'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
