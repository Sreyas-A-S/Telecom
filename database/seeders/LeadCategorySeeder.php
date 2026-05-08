<?php

namespace Database\Seeders;

use App\Models\LeadCategory;
use Illuminate\Database\Seeder;

class LeadCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $leadCategories = [
            'Category 1',
            'Category 2',
            'Category 3',
            'Category 4',
            'Category 5',
        ];

        foreach ($leadCategories as $category) {
            LeadCategory::firstOrCreate(['name' => $category]);
        }
    }
}
