<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeadSource; // Import the LeadSource model

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leadSources = [
            'Website',
            'Referral',
            'Cold Call',
            'Advertisement',
            'Social Media',
        ];

        foreach ($leadSources as $source) {
            LeadSource::firstOrCreate(['name' => $source]);
        }
    }
}