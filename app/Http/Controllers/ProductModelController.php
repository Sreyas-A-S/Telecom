<?php

namespace App\Http\Controllers;

use App\Models\ProductModel;
use Illuminate\Http\Request;

class ProductModelController extends Controller
{
    public function show(ProductModel $productModel)
    {
        return response()->json($productModel->load('product'));
    }

    public function getModelSeries(ProductModel $productModel)
    {
        return response()->json(['model_series' => $productModel->modelSeries]);
    }
}
