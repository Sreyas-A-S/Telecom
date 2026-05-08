<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="LeadSources",
 *     description="API Endpoints for Lead Sources"
 * )
 */
class LeadSourceController extends Controller
{
    public function index()
    {
        $leadSources = LeadSource::all();
        return response()->json(['lead_sources' => $leadSources]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $leadSource = LeadSource::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Lead source created successfully',
            'data' => $leadSource
        ], 200);
    }
}
