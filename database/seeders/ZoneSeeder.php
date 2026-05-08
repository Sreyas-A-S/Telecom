<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Zone; // Import the Zone model
use App\Models\Dealership; // Import the Dealership model

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dealerships = Dealership::all();

        if ($dealerships->isEmpty()) {
            echo "No dealerships found. Please run DealershipSeeder first.\n";
            return;
        }

        // Create zones for each dealership
        foreach ($dealerships as $dealership) {
            Zone::create([
                'name' => $dealership->name . ' - Zone 1',
                'dealership_id' => $dealership->id,
            ]);
            Zone::create([
                'name' => $dealership->name . ' - Zone 2',
                'dealership_id' => $dealership->id,
            ]);
        }

        // Create some zones for a specific dealership (e.g., Dealership A)
        $dealershipA = Dealership::where('name', 'Dealership A')->first();
        if ($dealershipA) {
            Zone::create([
                'name' => 'Special Zone A',
                'dealership_id' => $dealershipA->id,
            ]);
        }
    }
}
