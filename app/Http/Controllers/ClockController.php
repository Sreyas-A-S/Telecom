<?php

namespace App\Http\Controllers;

use App\Models\Clock;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClockController extends Controller
{
    public function clockIn(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['message' => 'You are not an employee.'], 403);
        }

        $existingClock = Clock::where('employee_id', $employee->id)->whereDate('clock_in_time', today())->first();

        if ($existingClock) {
            return response()->json(['message' => 'You have already clocked in today.'], 400);
        }

        $clock = Clock::create([
            'employee_id' => $employee->id,
            'clock_in_time' => now(),
        ]);

        return response()->json(['message' => 'Clocked in successfully.', 'clock' => $clock], 200);
    }

    public function clockOut(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['message' => 'You are not an employee.'], 403);
        }

        $request->validate([
            'remarks' => 'required|string',
        ]);

        $clock = Clock::where('employee_id', $employee->id)->whereNull('clock_out_time')->latest()->first();

        if (!$clock) {
            return response()->json(['message' => 'You have not clocked in yet.'], 400);
        }

        $clock->update([
            'clock_out_time' => now(),
            'remarks' => $request->remarks,
        ]);

        \App\Models\UserGpsTrace::where('user_id', Auth::id())
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        return response()->json(['message' => 'Clocked out successfully.', 'clock' => $clock], 200);
    }
}
