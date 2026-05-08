<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use App\Traits\OneSignalNotificationTrait;

/**
 * @OA\Tag(
 *     name="Employees",
 *     description="API Endpoints for Employees"
 * )
 */
class EmployeeController extends Controller
{
    use OneSignalNotificationTrait;

    /**
     * @OA\Get(
     *     path="/employees",
     *     tags={"Employees"},
     *     summary="Get list of employees",
     *     description="Returns list of employees",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(ref="#/components/schemas/Employee")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        $employees = Employee::all();
        return response()->json(['employees' => $employees]);
    }

    /**
     * @OA\Post(
     *     path="/employees/update-vehicle-type",
     *     tags={"Employees"},
     *     summary="Update current vehicle type for authenticated employee",
     *     description="Updates the vehicle type (e.g., Bike, Car) for the logged-in employee",
     *     security={{"bearerAuth":{}}},
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"vehicle_type"},
     *          @OA\Property(property="vehicle_type", type="string", enum={"idle", "walk", "bike", "car", "bus", "train", "other"}, example="bike")
     *      )
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Vehicle type updated successfully",
     *      @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Vehicle type updated successfully."),
     *          @OA\Property(property="vehicle_type", type="string", example="bike")
     *      )
     *  ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee record not found"
     *     )
     * )
     */
    public function updateVehicleType(Request $request)
    {
        $request->validate([
            'vehicle_type' => 'required|string|in:idle,walk,bike,car,bus,train,other',
        ]);

        $user = Auth::user();
        if (!$user || !$user->employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        $user->employee->update([
            'current_vehicle_type' => $request->vehicle_type,
        ]);

        return response()->json([
            'message' => 'Vehicle type updated successfully.',
            'vehicle_type' => $request->vehicle_type,
        ]);
    }
    /**
     * @OA\Get(
     *     path="/employees/current-vehicle-type",
     *     tags={"Employees"},
     *     summary="Get current vehicle type for authenticated employee",
     *     description="Returns the current vehicle type for the logged-in employee",
     *     security={{"bearerAuth":{}}},
     *  @OA\Response(
     *      response=200,
     *      description="Successful operation",
     *      @OA\JsonContent(
     *          @OA\Property(property="vehicle_type", type="string", enum={"idle", "walk", "bike", "car", "bus", "train", "other"}, example="bike")
     *      )
     *  ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Employee record not found")
     * )
     */
    public function getVehicleType()
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        return response()->json([
            'vehicle_type' => $user->employee->current_vehicle_type,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/employees/notify-vehicle-type-change",
     *     tags={"Employees"},
     *     summary="Send vehicle type change prompt notification to authenticated employee",
     *     description="Sends a OneSignal push notification to the logged-in user asking if they want to change vehicle type when movement/speed is detected by mobile app.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Notification sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vehicle type change notification sent successfully.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Employee record not found")
     * )
     */
    public function notifyVehicleTypeChange(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        $this->sendOneSignalNotification(
            $user,
            'Vehicle Type Update',
            'Movement detected. Do you want to change your vehicle type?',
            [
                'type' => 'vehicle_type_change_prompt',
                'route' => 'VehicleTypeUpdate',
                'current_vehicle_type' => $user->employee->current_vehicle_type,
            ]
        );

        return response()->json([
            'message' => 'Vehicle type change notification sent successfully.',
        ], 200);
    }
}
