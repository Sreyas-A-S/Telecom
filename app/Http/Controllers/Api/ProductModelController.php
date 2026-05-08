<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="ProductModels",
 *     description="API Endpoints for Product Models"
 * )
 */
class ProductModelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/product-models",
     *     tags={"ProductModels"},
     *     summary="Get list of product models",
     *     description="Returns list of product models",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(ref="#/components/schemas/ProductModel")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        $productModels = ProductModel::all();
        return response()->json(['product_models' => $productModels]);
    }

    /**
     * @OA\Post(
     *     path="/product-models",
     *     tags={"ProductModels"},
     *     summary="Create a new product model",
     *     description="Create a new product model record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProductModelRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product model created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ProductModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         ref="#/components/responses/ErrorResponse"
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $productModel = ProductModel::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Product model created successfully',
            'data' => $productModel
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/products/{product_id}/product-models",
     *     tags={"ProductModels"},
     *     summary="Get product models by product ID",
     *     description="Returns a list of product models for a given product ID",
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to retrieve models for",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(ref="#/components/schemas/ProductModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Product not found."))
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function getProductModelsByProductId(Product $product)
    {
        $productModels = $product->models;
        return response()->json(['product_models' => $productModels]);
    }

  
    public function getModelSeries(ProductModel $productModel)
    {
        return response()->json(['model_series' => $productModel->modelSeries]);
    }
}
