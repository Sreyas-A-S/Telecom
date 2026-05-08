<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Product;
use App\Models\ProductModel;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker; // Added Faker import

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $keralaDistricts = [
            'Alappuzha', 'Ernakulam', 'Idukki', 'Kannur', 'Kasaragod',
            'Kollam', 'Kottayam', 'Kozhikode', 'Malappuram', 'Palakkad',
            'Pathanamthitta', 'Thiruvananthapuram', 'Thrissur', 'Wayanad'
        ];

        $productIds = Product::pluck('id')->toArray();

        Lead::factory(60)->create([
            'location' => function (array $attributes) use ($keralaDistricts) {
                return $keralaDistricts[array_rand($keralaDistricts)];
            },
            'quantity' => $faker->numberBetween(1, 5),
            'company' => $faker->company(),
            'alternate_contact_number' => $faker->phoneNumber(),
            'financier' => $faker->company(),
            'type' => $faker->randomElement(['FTB', 'FTU', 'Retail', 'Strategic']),
            'login_status' => $faker->randomElement(['Logged In', 'Yet to Login']),
            'stage' => $faker->randomElement(['opportunity', 'lead', 'pending']),
            'billing' => $faker->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d'),
            'remarks' => $faker->paragraph(),
            'product_id' => function () use ($productIds) {
                return $productIds[array_rand($productIds)];
            },
            'product_model_id' => function (array $attributes) {
                $productModels = ProductModel::where('product_id', $attributes['product_id'])->pluck('id')->toArray();
                return !empty($productModels) ? $productModels[array_rand($productModels)] : null;
            },
        ]);
    }
}
