<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Dealership;
use App\Models\Employee;
use App\Models\Interview;
use Illuminate\Database\Seeder;

class InterviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dealershipIds = Dealership::pluck('id')->toArray();

        if (empty($dealershipIds)) {
            $this->command->info('No dealerships found. Please run DealershipSeeder first.');
            return;
        }

        Interview::factory()->count(50)->create();
    }
}