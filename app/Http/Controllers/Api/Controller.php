<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Logiprompt API Documentation",
 *      description="API Documentation for all Logiprompt modules",
 *      @OA\Contact(
 *          email="admin@example.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url="http://127.0.0.1:8000/api",
 *      description="API Server"
 * )
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 * )
 * @OA\Components(
 *     @OA\Response(
 *         response="ErrorResponse",
 *         description="Unauthorized or other error response",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="status_code", type="integer", example=499),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     schemas={
 *         @OA\Schema(
 *             schema="TaskLog",
 *             title="TaskLog",
 *             description="TaskLog model",
 *             @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *             @OA\Property(property="task_id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="action", type="string", example="Task started"),
 *             @OA\Property(property="timestamp", type="string", format="date-time", readOnly=true, example="2021-08-19T12:00:00.000000Z"),
 *             @OA\Property(property="created_at", type="string", format="date-time", readOnly=true, example="2021-08-19T12:00:00.000000Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true, example="2021-08-19T12:00:00.000000Z")
 *         ),
 *         @OA\Schema(
 *             schema="User",
 *             title="User",
 *             description="User model",
 *             @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *             @OA\Property(property="user_type", type="string", example="employee"),
 *             @OA\Property(property="created_at", type="string", format="date-time", readOnly=true, example="2021-08-19T12:00:00.000000Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true, example="2021-08-19T12:00:00.0000000Z")
 *         )
 *     }
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function sendResponse($result, $message)
    {
        $response = [
            'status' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        if ($code == 401) {
            $code = 499;
        }
        $response = [
            'status' => false,
            'status_code' => $code, // Add status_code key
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
