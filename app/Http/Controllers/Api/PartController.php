<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/parts",
     *      summary="Get a list of parts",
     *      tags={"Parts"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="query",
     *          in="query",
     *          description="Keyword to search for in part numbers or material descriptions",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page (default: 15)",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Part")),
     *              @OA\Property(property="links", type="object"),
     *              @OA\Property(property="meta", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    public function index(Request $request)
    {
        $partsQuery = Part::query();

        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $partsQuery->where('dealership_id', $user->employee->dealership_id);
            }
        }

        // Add search functionality to index for convenience
        if ($request->has('query') && !empty($request->input('query'))) {
            $search = $request->input('query');
            $partsQuery->where(function ($q) use ($search) {
                $q->where('part_number', 'LIKE', "%{$search}%")
                    ->orWhere('material_description', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $parts = $partsQuery->paginate($perPage);

        return response()->json($parts);
    }

    /**
     * Search for parts by part_number or material_description.
     *
     * @OA\Get(
     *      path="/parts/search",
     *      summary="Search for parts",
     *      tags={"Parts"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="query",
     *          in="query",
     *          description="Keyword to search for in part numbers or material descriptions",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="exclude",
     *          in="query",
     *          description="Comma-separated list of part IDs to exclude from results",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Part")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $exclude = $request->input('exclude', []);
        
        // Handle comma-separated exclude string if provided
        if (is_string($exclude)) {
            $exclude = explode(',', $exclude);
        }

        $partsQuery = Part::where(function ($q) use ($query) {
            $q->where('part_number', 'LIKE', "%{$query}%")
                ->orWhere('material_description', 'LIKE', "%{$query}%");
        });

        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $partsQuery->where('dealership_id', $user->employee->dealership_id);
            }
        }

        if (!empty($exclude)) {
            $partsQuery->whereNotIn('id', $exclude);
        }

        $parts = $partsQuery->limit(20)->get();

        return response()->json($parts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}