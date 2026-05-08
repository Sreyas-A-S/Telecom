<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Zones",
 *     description="API Endpoints of Zones"
 * )
 */
class ZoneController extends Controller
{
    /**
     * @OA\Get(
     *      path="/zones",
     *      operationId="getZonesList",
     *      tags={"Zones"},
     *      summary="Get list of zones",
     *      description="Returns list of zones",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(type="array", @OA\Items(
     *              @OA\Property(property="id", type="integer", format="int64", example=1),
     *              @OA\Property(property="name", type="string", example="North Zone"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z")
     *          ))
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function index()
    {
        $zones = Zone::orderBy('name')->get();
        return response()->json($zones);
    }
}
