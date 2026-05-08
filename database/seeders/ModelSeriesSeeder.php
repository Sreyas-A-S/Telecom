<?php

namespace Database\Seeders;

use App\Models\ProductModel;
use App\Models\ModelSeries;
use Illuminate\Database\Seeder;

class ModelSeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productModels = ProductModel::all();

        if ($productModels->isEmpty()) {
            $this->command->info('Please seed the product models first.');
            return;
        }

        foreach ($productModels as $productModel) {
            for ($i = 0; $i < 3; $i++) {
                ModelSeries::create([
                    'name' => 'Series ' . $i . ' for ' . $productModel->name,
                    'product_model_id' => $productModel->id,
                ]);
            }
        }
    }
}
