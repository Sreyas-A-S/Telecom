<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dealership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Dealerships",
 *     description="API Endpoints for Dealerships"
 * )
 */
class DealershipController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dealerships",
     *     tags={"Dealerships"},
     *     summary="Get list of dealerships",
     *     description="Returns list of dealerships",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(ref="#/components/schemas/Dealership")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        //only fetch the dealerships which are brands
        $dealerships = Dealership::where('brand', 1)->get();
        return response()->json(['dealerships' => $dealerships]);
    }

    /**
     * @OA\Post(
     *     path="/dealerships",
     *     tags={"Dealerships"},
     *     summary="Create a new dealership",
     *     description="Create a new dealership record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DealershipRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dealership created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Dealership")
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
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $dealership = Dealership::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Dealership created successfully',
            'data' => $dealership
        ], 200);
    }
}
