<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clock;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClockApiController extends Controller
{
    /**
    /**
     * @OA\Post(
     *      path="/clock/in",
     *      operationId="clockIn",
     *      tags={"Clock"},
     *      summary="Clock in an employee",
     *      description="Clock in an employee with GPS location",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent(
     *              @OA\Property(property="latitude", type="number", format="float", example=40.7128),
     *              @OA\Property(property="longitude", type="number", format="float", example=-74.0060)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Clocked in successfully."),
     *              @OA\Property(property="clock", ref="#/components/schemas/Clock")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="You are not an employee."),
     *          )
     *      )
     * )
     */
    public function clockIn(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['message' => 'You are not an employee.'], 403);
        }

        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $existingClock = Clock::where('employee_id', $employee->id)->whereDate('clock_in_time', today())->first();

        if ($existingClock) {
            return response()->json(['message' => 'You have already clocked in today.'], 200);
        }

        $clock = Clock::create([
            'employee_id' => $employee->id,
            'clock_in_time' => now(),
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
        ]);

        return response()->json(['message' => 'Clocked in successfully.', 'clock' => $clock], 200);
    }

    /**
     * @OA\Post(
     *      path="/clock/out",
     *      operationId="clockOut",
     *      tags={"Clock"},
     *      summary="Clock out an employee",
     *      description="Clock out an employee with GPS location",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"remarks"},
     *              @OA\Property(property="remarks", type="string", example="Work finished for today."),
     *              @OA\Property(property="latitude", type="number", format="float", example=40.7128),
     *              @OA\Property(property="longitude", type="number", format="float", example=-74.0060)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Clocked out successfully."),
     *              @OA\Property(property="clock", ref="#/components/schemas/Clock")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="You are not an employee."),
     *          )
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    public function clockOut(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['message' => 'You are not an employee.'], 403);
        }

        $request->validate([
            'remarks' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Find the active clock-in session (where clock_out_time is NULL)
        $activeClock = Clock::where('employee_id', $employee->id)
            ->whereNull('clock_out_time')
            ->latest()
            ->first();

        if ($activeClock) {
            // An active clock-in session exists, proceed to clock out
            $activeClock->update([
                'clock_out_time' => now(),
                'remarks' => $request->remarks,
                'clock_out_latitude' => $request->latitude,
                'clock_out_longitude' => $request->longitude,
            ]);

            // Set active traces to inactive so the route ends
            \App\Models\UserGpsTrace::where('user_id', Auth::id())
                ->where('status', 'active')
                ->update(['status' => 'inactive']);

            return response()->json(['message' => 'Clocked out successfully.', 'clock' => $activeClock], 200);
        } else {
            // No active clock-in session found.
            // Now, check if the employee has already clocked out today.
            $lastClockToday = Clock::where('employee_id', $employee->id)
                ->whereDate('clock_in_time', today())
                ->latest()
                ->first();

            if ($lastClockToday && $lastClockToday->clock_out_time !== null) {
                // Employee has a clock record for today, and it's already clocked out.
                return response()->json(['message' => 'You are already clocked out for today.'], 200);
            } else {
                // No clock-in record at all for today, or an incomplete one that's not active.
                return response()->json(['message' => 'You have not clocked in yet for today.'], 200);
            }
        }
    }

    /**
     * @OA\Get(
     *      path="/clock/status",
     *      operationId="getClockStatus",
     *      tags={"Clock"},
     *      summary="Get the current clock status of an employee",
     *      description="Get the current clock status of an employee",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="is_clocked_in", type="boolean"),
     *              @OA\Property(property="clock", type="string", format="date-time", nullable=true, description="Clock in time string"),
     *              @OA\Property(property="remarks", type="string", nullable=true, description="Remarks from the last clock session"),
     *              @OA\Property(property="clock_out_time", type="string", format="date-time", nullable=true, description="Clock out time if the session ended")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="You are not an employee."),
     *          )
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    public function getClockStatus(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['message' => 'You are not an employee.'], 403);
        }

        $status = false;
        $cit = null;

        $clock = Clock::where('employee_id', $employee->id)
            ->whereDate('clock_in_time', today())
            ->first();
        if (!$clock) {
            $status = false;
            $cit = null;
        } elseif ($clock->clock_out_time === null) {
            $status = true;
            $cit = $clock->clock_in_time;
        } else {
            $status = false;
            $cit = $clock->clock_in_time;
        }

        return response()->json([
            'is_clocked_in' => $status,
            'clock' => $cit ?? null,
            'remarks' => $clock ? $clock->remarks : null,
            'clock_out_time' => $clock ? $clock->clock_out_time : null,
        ], 200);
    }
}
