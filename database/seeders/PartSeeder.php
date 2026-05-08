<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\ProductModel;
use App\Models\Tax;
use App\Models\Employee;
use App\Models\ModelSeries;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productModels = ProductModel::all();
        $taxes = Tax::all();
        $modelSeries = ModelSeries::all();
        $dealerships = \App\Models\Dealership::all();
        $products = \App\Models\Product::all(); // Get all products

        if ($dealerships->isEmpty()) {
            $this->command->info('No dealerships found. Please seed dealerships first.');
            return;
        }

        for ($i = 1; $i <= 50; $i++) { // Create 50 parts
            $partNumber = 'PN' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $dealershipId = $dealerships->random()->id;
            $taxId = $taxes->isNotEmpty() ? $taxes->random()->id : null;
            $productModel = $productModels->isNotEmpty() ? $productModels->random() : null;
            $modelSerie = $modelSeries->isNotEmpty() ? $modelSeries->random() : null;
            $product = $products->isNotEmpty() ? $products->random() : null; // Random product

            $part = Part::firstOrCreate(
                ['part_number' => $partNumber],
                [
                    'material_description' => 'Material ' . $partNumber,
                    'tax_id' => $taxId,
                    'unit_price' => rand(5, 200) + (rand(0, 99) / 100), // Random price
                    'hsn' => 'HSN' . rand(1000, 9999),
                    'dealer' => 'Dealer ' . \Illuminate\Support\Str::random(5),
                    'dealership_id' => $dealershipId,
                    'bin' => 'BIN' . chr(rand(65, 90)) . rand(1, 9),
                    'stock_quantity' => rand(10, 500),
                    'is_active' => true,
                ]
            );

            if ($product) {
                $part->products()->sync([$product->id]);
            }
            if ($productModel) {
                $part->productModels()->sync([$productModel->id]);
            }
            if ($modelSerie) {
                $part->modelSeries()->sync([$modelSerie->id]);
            }
        }
    }
}