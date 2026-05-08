<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->info('Please seed the products first.');
            return;
        }

        foreach ($products as $product) {
            for ($i = 1; $i <= 3; $i++) { // Create 3 models for each product
                ProductModel::create([
                    'product_id' => $product->id,
                    'name' => $product->name . ' Model ' . $i,
                    'description' => 'Description for ' . $product->name . ' Model ' . $i,
                ]);
            }
        }
    }
}
