<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ModelSeries; // Added this line

class ModelSeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the model series.
     *
     * @OA\Get(
     *     path="/model-series",
     *     operationId="getModelSeriesList",
     *     tags={"Model Series"},
     *     summary="Get list of model series",
     *     description="Returns list of model series",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ModelSeries")
     *         )
     *     )
     * )
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created model series in storage.
     *
     * @OA\Post(
     *     path="/model-series",
     *     operationId="storeModelSeries",
     *     tags={"Model Series"},
     *     summary="Store a new model series",
     *     description="Creates a new model series record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ModelSeries")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Model series created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ModelSeries")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_model_id' => 'required|exists:product_models,id',
        ]);

        $modelSeries = ModelSeries::create($request->all());

        return response()->json($modelSeries, 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified model series.
     *
     * @OA\Get(
     *     path="/model-series/{id}",
     *     operationId="getModelSeriesById",
     *     tags={"Model Series"},
     *     summary="Get model series information",
     *     description="Returns a single model series",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of model series to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ModelSeries")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Model series not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $modelSeries = ModelSeries::find($id);

        if (!$modelSeries) {
            return response()->json(['message' => 'Model series not found'], 404);
        }

        return response()->json($modelSeries);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified model series in storage.
     *
     * @OA\Put(
     *     path="/model-series/{id}",
     *     operationId="updateModelSeries",
     *     tags={"Model Series"},
     *     summary="Update an existing model series",
     *     description="Updates a model series record by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of model series to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ModelSeries")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ModelSeries")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Model series not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $modelSeries = ModelSeries::find($id);

        if (!$modelSeries) {
            return response()->json(['message' => 'Model series not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'product_model_id' => 'required|exists:product_models,id',
        ]);

        $modelSeries->update($request->all());

        return response()->json($modelSeries);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Remove the specified model series from storage.
     *
     * @OA\Delete(
     *     path="/model-series/{id}",
     *     operationId="deleteModelSeries",
     *     tags={"Model Series"},
     *     summary="Delete a model series",
     *     description="Deletes a model series record by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of model series to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Model series deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Model series not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $modelSeries = ModelSeries::find($id);

        if (!$modelSeries) {
            return response()->json(['message' => 'Model series not found'], 404);
        }

        $modelSeries->delete();

        return response()->json(['message' => 'Model series deleted successfully.']);
    }


}
