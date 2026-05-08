<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * @OA\Post(
     *      path="/auth/login",
     *      summary="Authenticate user and get JWT",
     *      tags={"Authentication"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(
     *                      required={"email", "password"},
     *                      @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *                      @OA\Property(property="password", type="string", format="password", example="password")
     *                  ),
     *                  @OA\Schema(
     *                      required={"phone", "password"},
     *                      @OA\Property(property="phone", type="string", example="1234567890"),
     *                      @OA\Property(property="password", type="string", format="password", example="password")
     *                  )
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful login",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="status_code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="Authentication successful"),
     *              @OA\Property(property="access_token", type="string"),
     *              @OA\Property(property="token_type", type="string", example="bearer"),
     *              @OA\Property(property="user", type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="email", type="string"),
     *                  @OA\Property(property="role_name", type="string"),
     *                  @OA\Property(property="dealership_name", type="string"),
     *                  @OA\Property(property="zone_name", type="string")
     *              ),
     *              @OA\Property(property="organization", type="object",
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="address", type="string"),
     *                  @OA\Property(property="phone", type="string"),
     *                  @OA\Property(property="website", type="string"),
     *                  @OA\Property(property="logo", type="string")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *              @OA\Property(property="data", type="object")
     *          )
     *      )
     * )
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string',
            'password' => 'required|string',
        ]);

        $loginField = $request->has('email') ? 'email' : 'phone';
        $loginValue = $request->input($loginField);

        $user = User::where($loginField, $loginValue)->first();


        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return $this->sendError('Invalid credentials.', [], 499);
        }

        if ($user->user_type === 'employee') {
            $user->load(['employee.department', 'employee.role', 'employee.dealership', 'employee.zone']);
        }

        if ($user->user_type === 'employee' && $user->employee) {
            $user->employee->department_name = $user->employee->department->name ?? null;
            $user->employee->role_name = $user->employee->role->role ?? null;
            $user->employee->dealership_name = $user->employee->dealership->name ?? null;
            $user->employee->zone_name = $user->employee->zone->name ?? null;

            $user->employee->loadMissing('reporter');
            $user->employee->reporting_to_name = $user->employee->reporter2 ? $user->employee->reporter2->name : 'N/A';
            unset($user->employee->reporter);
            unset($user->employee->reporter2);
        }

        $user->role_name = $user->employee->role_name ?? null;
        $user->dealership_name = $user->employee->dealership_name ?? null;
        $user->zone_name = $user->employee->zone_name ?? null;

        $claims = ['role_id' => $user->role_id]; // Add role_id to JWT claims

        if (!$token = auth('api')->claims($claims)->login($user)) {
            return $this->sendError('Unauthorized', [], 499);
        }

        Session::put('jwt_token', $token);
        Session::put('role_id', $user->role_id);
        Session::put('user_id', $user->id);

        $tokenData = $this->respondWithTokenData($token);

        $organization = [
            'name' => Setting::where('key', 'organization_name')->value('value'),
            'address' => Setting::where('key', 'organization_address')->value('value'),
            'phone' => Setting::where('key', 'organization_phone')->value('value'),
            'website' => Setting::where('key', 'organization_website')->value('value'),
            'logo' => Setting::where('key', 'organization_logo')->value('value'),
        ];

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Authentication successful',
            'access_token' => $tokenData['access_token'],
            'token_type' => $tokenData['token_type'],
            'user' => $user,
            'organization' => $organization
        ], 200);
    }



    /**
     * @OA\Post(
     *      path="/auth/logout",
     *      summary="Log out user (invalidate token)",
     *      tags={"Authentication"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successfully logged out",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Successfully logged out"),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unauthenticated."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      )
     * )
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth('api')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Session::forget('jwt_token');
        Session::forget('role_id');
        Session::forget('user_id');

        return response()->json(['message' => 'Successfully logged out']);
    }



    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function respondWithTokenData($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
        ];
    }

    /**
     * @OA\Put(
     *      path="/user/player-id",
     *      summary="Update the authenticated user's OneSignal player ID",
     *      tags={"User Management"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="player_id", type="string", example="a1b2c3d4-e5f6-7890-1234-567890abcdef", description="OneSignal player ID")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Player ID updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Player ID updated successfully.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function updatePlayerId(Request $request)
    {
        $request->validate([
            'player_id' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->player_id = $request->player_id;
        $user->save();

        return response()->json(['status' => true, 'message' => 'Player ID updated successfully.']);
    }
}
