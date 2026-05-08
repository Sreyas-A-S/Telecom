<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\State; // Added
use App\Models\District; // Added

class IndianStatesAndDistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for bulk insertion
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data to prevent duplicates on re-run
        District::truncate(); // Use model truncate
        State::truncate();    // Use model truncate

        // Path to the JSON file
        $jsonPath = public_path('storage/states-and-districts.json');
        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);

        if (isset($data['states']) && is_array($data['states'])) {
            foreach ($data['states'] as $stateData) {
                $stateName = $stateData['state'];
                $state = State::create(['name' => $stateName]); // Use model create

                if (isset($stateData['districts']) && is_array($stateData['districts'])) {
                    foreach ($stateData['districts'] as $districtName) {
                        $state->districts()->create([ // Use relationship to create district
                            'name' => $districtName,
                        ]);
                    }
                }
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
