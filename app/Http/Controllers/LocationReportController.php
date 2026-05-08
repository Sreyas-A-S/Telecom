<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationReportController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $query = \App\Models\LocationReport::with(['user.employee.role', 'user.employee.dealership']);

            // Admin Logic (View All) - Assuming user_type 'admin' or specific role checks if needed
            if ($user->user_type !== 'admin') {
                $employee = $user->employee;
                if ($employee) {
                    $roleName = $employee->role ? $employee->role->role : null;
                    $dealershipId = $employee->dealership_id;

                    if ($roleName === 'service_manager') {
                        // Service Manager: View Service Engineers in same dealership
                        $query->whereHas('user.employee', function ($q) use ($dealershipId) {
                            $q->where('dealership_id', $dealershipId)
                                ->whereHas('role', function ($r) {
                                    $r->where('role', 'service_engineer');
                                });
                        });
                    } elseif ($roleName === 'Sales Manager') {
                        // Sales Manager: View Sales Engineers in same dealership
                        $query->whereHas('user.employee', function ($q) use ($dealershipId) {
                            $q->where('dealership_id', $dealershipId)
                                ->whereHas('role', function ($r) {
                                    $r->where('role', 'Sales Engineer');
                                });
                        });
                    } else {
                        // Regular Employee: View own reports only
                        $query->where('user_id', $user->id);
                    }
                } else {
                    // Fallback for users without employee record (if any): View own data
                    $query->where('user_id', $user->id);
                }
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user->name ?? 'N/A';
                })
                ->addColumn('action', function ($row) {
                    // Only show view button if visit_id exists
                    if ($row->visit_id) {
                        return '<button type="button" class="btn btn-primary btn-sm view-visit-btn" 
                                    data-visit-id="' . $row->visit_id . '" 
                                    data-lat="' . $row->latitude . '" 
                                    data-lng="' . $row->longitude . '">
                                    <i class="fa fa-eye"></i> View Visit
                                </button>';
                    }
                    return '<span class="text-muted">No Visit Info</span>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('location_reports.index');
    }

    public function getVisitDetails($visitId)
    {
        $traces = \App\Models\UserGpsTrace::where('visit_id', $visitId)
            ->with(['client', 'task'])
            ->orderBy('recorded_at')
            ->get();

        if ($traces->isEmpty()) {
            return response()->json(['error' => 'No traces found for this visit.'], 404);
        }

        $firstTrace = $traces->first();
        $lastTrace = $traces->last();

        $totalDistance = 0;
        if ($traces->count() > 1) {
            for ($i = 0; $i < $traces->count() - 1; $i++) {
                $point1 = $traces[$i];
                $point2 = $traces[$i + 1];
                $totalDistance += calculateDistance(
                    $point1->latitude,
                    $point1->longitude,
                    $point2->latitude,
                    $point2->longitude
                );
            }
        }

        $formattedDistance = round($totalDistance / 1000, 3) . ' km';

        // Duration
        $start = $firstTrace->recorded_at; // Assuming casted to datetime
        $end = $lastTrace->recorded_at;
        // In case recorded_at isn't casted (though model says it is), use Carbon parse if needed. 
        // Model casts: 'recorded_at' => 'datetime'

        $durationSeconds = $end->diffInSeconds($start);
        $formattedDuration = gmdate('H\h i\m s\s', $durationSeconds);

        // Collect unique Clients and Tasks
        $clients = $traces->whereNotNull('client_id')->pluck('client.name')->unique()->values();
        $tasks = $traces->whereNotNull('task_id')->pluck('task.title')->unique()->values();

        return response()->json([
            'visit_id' => $visitId,
            'user' => $firstTrace->user->name ?? 'N/A',
            'date' => $start->format('Y-m-d'),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'duration' => $formattedDuration,
            'total_distance' => $formattedDistance,
            'clients' => $clients,
            'tasks' => $tasks,
            'trace_count' => $traces->count(),
            'traces' => $traces->map(function ($trace) {
                return [
                    'lat' => $trace->latitude,
                    'lng' => $trace->longitude,
                    'recorded_at' => $trace->recorded_at,
                ];
            }),
        ]);
    }
}
