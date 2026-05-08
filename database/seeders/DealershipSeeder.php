<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dealership; // Import the Dealership model

class DealershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        Dealership::create(['name' => 'Hyundai']);
        Dealership::create(['name' => 'Ajax']);
        Dealership::create(['name' => 'Kion']);
        Dealership::create(['name' => 'M B Crushher']);
        Dealership::create(['name' => 'Accounts', 'brand' => 0]);
        Dealership::create(['name' => 'HR & Administration', 'brand' => 0]);
        Dealership::create(['name' => 'Workshop', 'brand' => 0]);
    }
}
