<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeadCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="LeadCategories",
 *     description="API Endpoints for Lead Categories"
 * )
 */
class LeadCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/lead-categories",
     *     tags={"LeadCategories"},
     *     summary="Get list of lead categories",
     *     description="Returns list of lead categories",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(ref="#/components/schemas/LeadCategory")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        $leadCategories = LeadCategory::all();
        return response()->json(['lead_categories' => $leadCategories]);
    }

    /**
     * @OA\Post(
     *     path="/lead-categories",
     *     tags={"LeadCategories"},
     *     summary="Create a new lead category",
     *     description="Create a new lead category record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LeadCategoryRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lead category created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/LeadCategory")
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

        $leadCategory = LeadCategory::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Lead category created successfully',
            'data' => $leadCategory
        ], 200);
    }
}
