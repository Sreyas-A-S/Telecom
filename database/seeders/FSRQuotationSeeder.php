<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FSRQuotation;
use App\Models\FSRReport;
use App\Models\Part;
use App\Models\User;
use Faker\Factory as Faker;

class FSRQuotationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $fsrReports = FSRReport::all();
        $parts = Part::all();
        $users = User::all();

        if ($fsrReports->isEmpty() || $parts->isEmpty() || $users->isEmpty()) {
            $this->command->info('Skipping FSRQuotationSeeder: No FSR Reports, Parts, or Users found. Please run FSRReportSeeder, PartSeeder, and UserSeeder first.');
            return;
        }

        foreach ($fsrReports as $fsrReport) {
            // Create 1 to 3 quotations for each FSR Report
            for ($i = 0; $i < $faker->numberBetween(1, 3); $i++) {
                FSRQuotation::create([
                    'fsr_id' => $fsrReport->id,
                    'part_id' => $parts->random()->id,
                    'quoted_quantity' => $faker->numberBetween(1, 10),
                    'quoted_unit_price' => $faker->randomFloat(2, 10, 1000),
                    'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                    'approved_by_user_id' => $users->random()->id,
                    'approved_at' => $faker->dateTimeBetween('-1 month', 'now'),
                    'remarks' => $faker->sentence,
                ]);
            }
        }
    }
}
