<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\Tax;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $subCategories = SubCategory::all();
        $taxes = Tax::all();
        $unitTypes = ['PCS', 'KG', 'Liter', 'Dozen', 'Meter'];

        if ($categories->isEmpty() || $subCategories->isEmpty() || $taxes->isEmpty()) {
            $this->command->info('Please seed the categories, sub-categories and taxes first.');
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            Product::create([
                'name' => 'Product ' . $i,
                'price' => rand(100, 1000),
                'hsn_sac' => 'HSN' . rand(1000, 9999),
                'description' => 'This is a sample product description.',
                'category_id' => $categories->random()->id,
                'sub_category_id' => $subCategories->random()->id,
                'unit_type' => $unitTypes[array_rand($unitTypes)],
                'tax_id' => $taxes->random()->id,
            ]);
        }
    }
}
