<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LossOrder;
use App\Models\Dealership;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\ModelSeries;
use Faker\Factory as Faker;

class LossOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $dealershipIds = Dealership::pluck('id')->toArray();

        if (empty($dealershipIds)) {
            echo "No dealerships found. Please seed dealerships first.\n";
            return;
        }

        // Generate random month and year for the last 12 months
        $randomDate = $faker->dateTimeBetween('-12 months', 'now');
        $monthToSeed = $randomDate->format('Y-m');

        foreach (range(1, 20) as $index) { // Create 20 dummy loss orders
            LossOrder::create([
                'month' => $monthToSeed,
                'dealership_id' => $faker->randomElement($dealershipIds),
                'product_name' => $faker->word(),
                'tonnage' => $faker->randomFloat(2, 10, 1000),
                'product_model_name' => $faker->word(),
                'model_series_name' => $faker->word(),
                'customer' => $faker->name(),
                'segment' => $faker->randomElement(['Rented', 'Captive']),
                'application' => $faker->word(),
                'financier' => $faker->company(),
                'district' => $faker->city(),
                'category' => $faker->word(),
                'participation' => $faker->randomElement(['Yes', 'No']),
                'reasons_for_loss' => $faker->sentence(),
                'remarks' => $faker->paragraph(),
                'engineer_name' => $faker->name(),
            ]);
        }
    }
}

