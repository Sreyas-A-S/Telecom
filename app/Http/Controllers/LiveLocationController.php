<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserGpsTrace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Exports\VisitsExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class LiveLocationController extends Controller
{
    private $settingsCache = [];
    private $historyCache = [];

    private function timelineExportCacheKey(string $token): string
    {
        $userId = Auth::id() ?: 'guest';
        return "timeline_export:{$userId}:{$token}";
    }

    private function buildProcessedRowForVisitTraces($traces, array $options, float $engineerRate): ?array
    {
        if (!$traces || $traces->isEmpty()) {
            return null;
        }

        $closestMode = !empty($options['closest_mode']);
        $smoothingMode = !empty($options['smoothing_mode']);

        $visitTraces = $traces;
        if ($closestMode) {
            $visitTraces = $this->reorderTracesByClosest($visitTraces);
        }
        if ($smoothingMode) {
            $visitTraces = $this->snapTracesToRoads($visitTraces);
        }

        $firstTrace = $visitTraces->first();
        $lastTrace = $visitTraces->last();
        $originalFirstTrace = $traces->first();

        $totalDistance = 0;
        if ($visitTraces->count() > 1) {
            for ($i = 0; $i < $visitTraces->count() - 1; $i++) {
                $point1 = $visitTraces[$i];
                $point2 = $visitTraces[$i + 1];
                if (empty($point1->latitude) || empty($point2->latitude)) continue;

                $totalDistance += calculateDistance(
                    $point1->latitude,
                    $point1->longitude,
                    $point2->latitude,
                    $point2->longitude
                );
            }
        }

        $user = \App\Models\User::with(['employee.department', 'employee.dealership'])->find($originalFirstTrace->user_id);
        if (!$user) {
            return null;
        }
        $employee = $user->employee ?? null;

        $managerName = 'N/A';
        if ($employee && $employee->reporting_to) {
            $managerName = \App\Models\User::where('id', $employee->reporting_to)->value('name') ?: 'N/A';
        }

        $visitStart = \Carbon\Carbon::parse($firstTrace->created_at);
        $visitEnd = \Carbon\Carbon::parse($lastTrace->created_at);

        $visitTaskLogs = collect();
        if ($employee?->id) {
            $visitTaskLogs = \App\Models\TaskLog::with('task')
                ->where('employee_id', $employee->id)
                ->where('start_time', '<=', $visitEnd)
                ->where(function ($q) use ($visitStart) {
                    $q->whereNull('end_time')->orWhere('end_time', '>=', $visitStart);
                })
                ->get();
        }

        $traceTaskIds = $traces->whereNotNull('task_id')->pluck('task_id');
        $logTaskIds = $visitTaskLogs->pluck('task_id')->filter();

        $visitTaskIds = $traceTaskIds->merge($logTaskIds)->unique();

        $tasks = $visitTaskIds->isNotEmpty()
            ? \App\Models\Task::whereIn('id', $visitTaskIds)->get()->keyBy('id')
            : collect();

        $traceClientIds = $traces->whereNotNull('client_id')->pluck('client_id')->unique();

        $leadIds = $tasks->pluck('lead_id')->filter()->unique();
        $leads = $leadIds->isNotEmpty()
            ? \App\Models\Lead::whereIn('id', $leadIds)->get(['id', 'client_id', 'location', 'name', 'phone_number', 'status', 'remarks'])->keyBy('id')
            : collect();

        $serviceEntryIds = $tasks
            ->filter(function ($t) {
                if (empty($t->entry_id)) return false;
                if (!empty($t->is_service)) return true;
                if (empty($t->entry_type)) return false;
                return $t->entry_type === \App\Models\Service::class || $t->entry_type === 'App\\Models\\Service';
            })
            ->pluck('entry_id')
            ->filter()
            ->unique();

        $services = $serviceEntryIds->isNotEmpty()
            ? \App\Models\Service::whereIn('id', $serviceEntryIds)->get(['id', 'client_id', 'requested_location', 'name', 'contact_info', 'call_status', 'call_remarks'])->keyBy('id')
            : collect();

        $taskTitles = $tasks->map(function ($t) use ($leads, $services) {
            $category = $t->is_service ? '[Service]' : ($t->lead_id ? '[Lead]' : '[General]');
            $title = trim($t->title ?? '');
            if (empty($title)) {
                if ($t->is_service && isset($services[$t->entry_id])) {
                    $title = trim($services[$t->entry_id]->name ?? '');
                } elseif ($t->lead_id && isset($leads[$t->lead_id])) {
                    $title = trim($leads[$t->lead_id]->name ?? '');
                }
            }
            return trim("$category " . ($title ?: 'N/A'));
        });

        $taskTitles = $taskTitles->unique()->implode("\n") ?: 'N/A';

        $serviceTaskCount = $tasks
            ->where('is_service', 1)
            ->whereIn('status', ['completed', 'partial'])
            ->count();
        $totalCallTa = $serviceTaskCount * $engineerRate;

        $derivedClientIds = collect()
            ->merge($traceClientIds)
            ->merge($leads->pluck('client_id')->filter())
            ->merge($services->pluck('client_id')->filter())
            ->unique()
            ->values();

        $clients = $derivedClientIds->isNotEmpty()
            ? \App\Models\Client::whereIn('id', $derivedClientIds)->get()->keyBy('id')
            : collect();

        $allClientNames = collect();
        $clients->each(fn($c) => $c->name ? $allClientNames->push($c->name) : null);
        $leads->each(fn($l) => $l->name ? $allClientNames->push($l->name) : null);
        $services->each(fn($s) => $s->name ? $allClientNames->push($s->name) : null);
        $clientNames = $allClientNames->filter()->unique()->implode("\n") ?: 'N/A';

        $allContacts = collect();
        $clients->each(fn($c) => $c->phone_number ? $allContacts->push($c->phone_number) : null);
        $leads->each(fn($l) => $l->phone_number ? $allContacts->push($l->phone_number) : null);
        $services->each(fn($s) => $s->contact_info ? $allContacts->push($s->contact_info) : null);
        $clientPhones = $allContacts->filter()->unique()->implode("\n") ?: 'N/A';

        $vehicleTypes = $traces->pluck('vehicle_type')->filter()->unique()->map(fn($v) => ucfirst($v))->implode("\n") ?: 'N/A';
        $allRemarks = $traces->whereNotNull('remarks')->pluck('remarks')->unique()->implode("\n") ?: 'N/A';

        $haltPoints = $this->findHaltPoints($traces, $tasks, $clients);
        $pointDetails = $haltPoints->map(function ($hp, $index) {
            $i = $index + 1;
            $details = "Point #$i: at {$hp['start_time']} - {$hp['location_info']}";
            if ($hp['active_tasks']) {
                $details .= " | Tasks: {$hp['active_tasks']}";
            }
            if ($hp['remarks']) {
                $details .= " | Remarks: {$hp['remarks']}";
            }
            if ($hp['images']->isNotEmpty()) {
                $imageUrls = $hp['images']->map(fn($img) => url($img))->implode("\n");
                $details .= " | Images: $imageUrls";
            }
            return $details;
        })->implode("\n") ?: 'None';

        // $visitTaskLogs is already fetched above.

        $logDetails = $visitTaskLogs->map(function ($log) {
            return ($log->task ? $log->task->title : 'Task') . ' (' . Carbon::parse($log->start_time)->format('H:i') . ' - ' . ($log->end_time ? Carbon::parse($log->end_time)->format('H:i') : 'Active') . ')';
        })->implode("\n") ?: 'N/A';

        $totalTaskSeconds = 0;
        foreach ($visitTaskLogs as $log) {
            $logStart = Carbon::parse($log->start_time);
            $logEnd = $log->end_time ? Carbon::parse($log->end_time) : now();

            $overlapStart = $logStart->gt($visitStart) ? $logStart : $visitStart;
            $overlapEnd = $logEnd->lt($visitEnd) ? $logEnd : $visitEnd;

            if ($overlapStart->lt($overlapEnd)) {
                $totalTaskSeconds += $overlapStart->diffInSeconds($overlapEnd);
            }
        }
        $taskDuration = gmdate('H:i:s', $totalTaskSeconds);

        $startedTime = Carbon::parse($firstTrace->created_at);
        $endedTime = Carbon::parse($lastTrace->created_at);
        $timeSpent = gmdate('H:i:s', $endedTime->diffInSeconds($startedTime));

        $allLocations = collect();
        $tasks->each(fn($t) => $t->location ? $allLocations->push($t->location) : null);
        $leads->each(fn($l) => $l->location ? $allLocations->push($l->location) : null);
        $services->each(fn($s) => $s->requested_location ? $allLocations->push($s->requested_location) : null);
        $clients->each(fn($c) => $c->address ? $allLocations->push($c->address) : null);
        $location = $allLocations->filter()->unique()->implode("\n") ?: 'N/A';

        $allStatus = $tasks->pluck('status');
        $leads->each(fn($l) => $l->status ? $allStatus->push($l->status) : null);
        $services->each(fn($s) => $s->call_status ? $allStatus->push($s->call_status) : null);
        $statusStr = $allStatus->filter()->unique()->implode("\n") ?: 'N/A';

        $finalRemarks = $tasks->pluck('description');
        $leads->each(fn($l) => $l->remarks ? $finalRemarks->push($l->remarks) : null);
        $services->each(fn($s) => $s->call_remarks ? $finalRemarks->push($s->call_remarks) : null);
        $remarksStr = $finalRemarks->filter()->unique()->implode("\n") ?: 'N/A';

        // Fix for PDF: add missing fields
        $allClientDetails = collect();
        $clients->each(function ($c) use ($allClientDetails) {
            $detail = "Client: " . ($c->name ?: 'N/A');
            if ($c->phone_number) $detail .= " (Ph: $c->phone_number)";
            if ($c->email) $detail .= " (Email: $c->email)";
            if ($c->address) $detail .= " (Addr: $c->address)";
            $allClientDetails->push($detail);
        });
        $clientFullInfo = $allClientDetails->unique()->implode("\n") ?: 'N/A';

        $detailedLeadServiceInfo = collect();
        $leads->each(function ($l) use ($detailedLeadServiceInfo) {
            $productName = property_exists($l, 'product') && $l->product ? $l->product->name : 'N/A';
            $detailedLeadServiceInfo->push("[Lead] Name: $l->name | Phone: $l->phone_number | Product: $productName | Status: $l->status | Remarks: $l->remarks");
        });
        $services->each(function ($s) use ($detailedLeadServiceInfo) {
            $productName = property_exists($s, 'product') && $s->product ? $s->product->name : 'N/A';
            $detailedLeadServiceInfo->push("[Service] Name: $s->name | Contact: $s->contact_info | Product: $productName | S/N: $s->machine_serial_number | Status: $s->call_status | Remarks: $s->call_remarks");
        });
        $leadServiceSummary = $detailedLeadServiceInfo->unique()->implode("\n") ?: 'No detailed Lead/Service info.';

        return [
            'user_name' => $user->name ?? 'N/A',
            'employee_code' => $employee->employee_id ?? 'N/A',
            'designation' => $employee->designation ?? 'N/A',
            'department' => $employee?->department?->name ?? 'N/A',
            'dealership' => $employee?->dealership?->name ?? 'N/A',
            'manager' => $managerName,
            'email' => $user->email ?? 'N/A',
            'phone' => $user->phone ?? 'N/A',
            'task_type' => $taskTitles,
            'vehicle_type' => $vehicleTypes,
            'point_count' => $haltPoints->count(),
            'point_info' => $pointDetails,
            'halt_points' => $haltPoints,
            'visit_remarks' => "Tasks: $logDetails. Visit Remarks: $allRemarks",
            'date' => $startedTime->setTimezone('Asia/Kolkata')->format('d-m-Y'),
            'started_time' => $startedTime->setTimezone('Asia/Kolkata')->format('H:i:s'),
            'ended_time' => $endedTime->setTimezone('Asia/Kolkata')->format('H:i:s'),
            'time_spent' => $timeSpent,
            'task_duration' => $taskDuration,
            'kms_travelled' => round($totalDistance / 1000, 3),
            'travel_expense' => $this->calculateVisitTravelExpense($visitTraces),
            'call_ta' => $totalCallTa,
            'client_name' => $clientNames,
            'contact' => $clientPhones,
            'location' => $location,
            'status' => $statusStr,
            'remarks' => $remarksStr,
            'client_full_info' => $clientFullInfo,
            'lead_service_summary' => $leadServiceSummary,
        ];
    }

    public function index()
    {
        return view('live-location.index');
    }

    public function timeline()
    {
        $user = Auth::user();
        $employee = $user->employee;

        $dealerships = \App\Models\Dealership::where('brand', 1)->get();
        $departments = \App\Models\Department::all();

        $currentDealershipId = $employee ? $employee->dealership_id : null;
        $currentDepartmentId = $employee ? $employee->department_id : null;

        $isRestricted = $user->user_type !== 'admin';

        return view('timeline.index', compact('dealerships', 'departments', 'currentDealershipId', 'currentDepartmentId', 'isRestricted'));
    }

    private function applyCreatedAtDateFilters(Request $request, $query, string $column): void
    {
        if ($request->filled('visit_date')) {
            try {
                $visitDate = Carbon::createFromFormat('Y-m-d', (string) $request->visit_date)->toDateString();
                $query->whereDate($column, $visitDate);
            } catch (\Exception $e) {
                Log::warning('Invalid visit_date filter', ['visit_date' => $request->visit_date]);
            }
            return;
        }

        $start = null;
        $end = null;

        if ($request->filled('start_date')) {
            try {
                $start = Carbon::createFromFormat('Y-m-d', (string) $request->start_date)->startOfDay();
            } catch (\Exception $e) {
                Log::warning('Invalid start_date filter', ['start_date' => $request->start_date]);
            }
        }

        if ($request->filled('end_date')) {
            try {
                $end = Carbon::createFromFormat('Y-m-d', (string) $request->end_date)->endOfDay();
            } catch (\Exception $e) {
                Log::warning('Invalid end_date filter', ['end_date' => $request->end_date]);
            }
        }

        if ($start && $end) {
            $query->whereBetween($column, [$start, $end]);
            return;
        }

        if ($start) {
            $query->where($column, '>=', $start);
            return;
        }

        if ($end) {
            $query->where($column, '<=', $end);
            return;
        }

        $query->whereDate($column, now()->toDateString());
    }



    private function getFilteredQuery(Request $request, $includeRelationships = true)
    {
        $query = UserGpsTrace::query()
            ->join('users', 'user_gps_traces.user_id', '=', 'users.id')
            ->join('employees', 'users.id', '=', 'employees.user_id');

        if ($includeRelationships) {
            $query->with(['user.employee.department', 'task', 'client']);
        }

        $query->select('user_gps_traces.visit_id', 'user_gps_traces.user_id', 'user_gps_traces.task_id', 'user_gps_traces.client_id', 'user_gps_traces.created_at', 'user_gps_traces.latitude', 'user_gps_traces.longitude', 'user_gps_traces.remarks', 'user_gps_traces.vehicle_type')
            ->whereNotNull('user_gps_traces.visit_id')
            ->orderBy('user_gps_traces.visit_id', 'desc')
            ->orderBy('user_gps_traces.created_at', 'asc')
            ->orderBy('user_gps_traces.id', 'asc');

        // IF visit_id is provided, we skip all other filters for maximum precision
        if ($request->has('visit_id') && !empty($request->visit_id)) {
            $query->where('user_gps_traces.visit_id', $request->visit_id);

            // But allow an optional single-day split for the same visit_id (Timeline "By date" mode)
            if ($request->has('visit_date') && !empty($request->visit_date)) {
                $query->whereDate('user_gps_traces.created_at', $request->visit_date);
            }

            return $query;
        }

        $this->applyCreatedAtDateFilters($request, $query, 'user_gps_traces.created_at');

        // Dealership and Department Filtering
        $user = Auth::user();
        $employee = $user->employee;

        if ($user->user_type !== 'admin') {
            if ($employee && $employee->dealership_id) {
                $query->where('employees.dealership_id', $employee->dealership_id);
            } elseif ($request->has('dealership_id') && !empty($request->dealership_id)) {
                $query->where('employees.dealership_id', $request->dealership_id);
            }

            if ($employee && $employee->department_id) {
                $query->where('employees.department_id', $employee->department_id);
            } elseif ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('employees.department_id', $request->department_id);
            }
        } else {
            if ($request->has('dealership_id') && !empty($request->dealership_id)) {
                $query->where('employees.dealership_id', $request->dealership_id);
            }
            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('employees.department_id', $request->department_id);
            }
        }

        $canViewAll = checkMenu(Session::get('role_id'), 38, 'read');
        $canViewSub = checkMenu(Session::get('role_id'), 40, 'read');
        $canViewSelf = checkMenu(Session::get('role_id'), 29, 'read');

        if ($user->user_type !== 'admin' && !$canViewAll) {
            $allowedUserIds = [];
            if ($canViewSelf) {
                $allowedUserIds[] = $user->id;
            }
            if ($canViewSub && $employee) {
                $subordinateIds = $employee->subordinates->pluck('user_id')->toArray();
                $allowedUserIds = array_merge($allowedUserIds, $subordinateIds);
            }
            $allowedUserIds = array_unique($allowedUserIds);

            if (empty($allowedUserIds)) {
                $query->where('user_gps_traces.user_id', -1);
            } else {
                if ($request->has('user_id') && !empty($request->user_id)) {
                    if (in_array($request->user_id, $allowedUserIds)) {
                        $query->where('user_gps_traces.user_id', $request->user_id);
                    } else {
                        $query->where('user_gps_traces.user_id', -1);
                    }
                } else {
                    $query->whereIn('user_gps_traces.user_id', $allowedUserIds);
                }
            }
        } elseif ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_gps_traces.user_id', $request->user_id);
        }

        return $query;
    }

    public function getDataTableData(Request $request)
    {
        $query = $this->getFilteredQuery($request, false);

        $data = $query->get();

        $groupedData = $data->groupBy('visit_id');

        $processedData = [];

        // Bulk load user, task and client names for efficiency
        $userIds = $data->pluck('user_id')->unique();
        $taskIds = $data->whereNotNull('task_id')->pluck('task_id')->unique();
        $clientIds = $data->whereNotNull('client_id')->pluck('client_id')->unique();

        $users = \App\Models\User::whereIn('id', $userIds)->pluck('name', 'id');
        $tasks = \App\Models\Task::whereIn('id', $taskIds)->get(['id', 'title', 'description', 'status'])->keyBy('id');
        $clients = \App\Models\Client::whereIn('id', $clientIds)->get(['id', 'name', 'phone_number', 'address'])->keyBy('id');

        foreach ($groupedData as $visitId => $traces) {
            $userNames = $traces->pluck('user_id')->unique()->map(function ($id) use ($users) {
                return $users[$id] ?? 'N/A';
            })->implode(', ');

            $visitTraces = $traces;

            $totalDistance = 0;
            if ($visitTraces->count() > 1) {
                for ($i = 0; $i < $visitTraces->count() - 1; $i++) {
                    $point1 = $visitTraces[$i];
                    $point2 = $visitTraces[$i + 1];
                    $totalDistance += calculateDistance(
                        $point1->latitude,
                        $point1->longitude,
                        $point2->latitude,
                        $point2->longitude
                    );
                }
            }

            $traceWithTask = $traces->whereNotNull('task_id')->first();
            $traceWithClient = $traces->whereNotNull('client_id')->first();
            $traceWithRemarks = $traces->whereNotNull('remarks')->first();

            $taskId = $traceWithTask ? $traceWithTask->task_id : null;
            $task = $taskId ? ($tasks[$taskId] ?? null) : null;

            $clientId = $traceWithClient ? $traceWithClient->client_id : null;
            $client = $clientId ? ($clients[$clientId] ?? null) : null;

            $processedData[] = [
                'visit_id' => $visitId,
                'user_name' => $userNames,
                'distance_covered' => round($totalDistance / 1000, 3), // Convert to km and round to 2 decimal places
                'task_title' => $task ? $task->title : null,
                'task_desc' => $task ? $task->description : null,
                'task_status' => $task ? $task->status : null,
                'client_name' => $client ? $client->name : null,
                'client_phone' => $client ? $client->phone_number : null,
                'client_address' => $client ? $client->address : null,
                'visit_remarks' => $traceWithRemarks ? $traceWithRemarks->remarks : null,
                'visit_image' => $traces->whereNotNull('image_path')->first()?->image_path,
                'image_latitude' => $traces->whereNotNull('image_latitude')->first()?->image_latitude,
                'image_longitude' => $traces->whereNotNull('image_longitude')->first()?->image_longitude,
            ];
        }
        Log::info('Processed data for DataTables:', $processedData);

        try {
            return DataTables::of(collect($processedData))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<span style="white-space: nowrap;">';
                    $btn .= '<a href="#" class="btn btn-info btn-sm select-visit" data-visit-id="' . $row['visit_id'] . '" title="Locate"><i class="fa fa-map-marker-alt"></i></a>';
                    $btn .= ' <a href="#" class="btn btn-warning btn-sm unlocate-visit" data-visit-id="' . $row['visit_id'] . '" style="display:none;" title="Unlocate"><i class="fa fa-undo"></i></a>';
                    if (Auth::user()->user_type === 'admin') {
                        $btn .= ' <button class="btn btn-danger btn-sm delete-trace" data-visit-id="' . $row['visit_id'] . '" title="Delete"><i class="fa fa-trash"></i></button>';
                    }
                    $btn .= '</span>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Datatables error in LiveLocationController: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getTimelineDataTableData(Request $request)
    {
        Log::info('--- Optimized Request for getTimelineDataTableData ---');

        $engineerRate = (float) DB::table('settings')->where('key', 'travel_allowance_engineer_rate_per_call')->value('value') ?: 0.0;
        $splitByDate = $request->boolean('split_by_date');

        $query = UserGpsTrace::query()
            ->join('users', 'user_gps_traces.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('tasks', 'user_gps_traces.task_id', '=', 'tasks.id')
            ->leftJoin('clients', 'user_gps_traces.client_id', '=', 'clients.id')
            ->select(
                'user_gps_traces.visit_id',
                'user_gps_traces.user_id',
                'users.name as user_name',
                DB::raw('MIN(user_gps_traces.created_at) as date'),
                DB::raw('MAX(tasks.title) as task_title'),
                DB::raw('MAX(user_gps_traces.task_id) as task_id'),
                DB::raw('MAX(clients.name) as client_name'),
                DB::raw('MAX(clients.phone_number) as client_phone'),
                DB::raw('MAX(user_gps_traces.remarks) as visit_remarks'),
                DB::raw('MAX(user_gps_traces.image_path) as visit_image')
            )
            ->whereNotNull('user_gps_traces.visit_id')
            ->groupBy('user_gps_traces.visit_id', 'user_gps_traces.user_id', 'users.name');

        if ($splitByDate) {
            $query->addSelect(DB::raw('DATE(user_gps_traces.created_at) as visit_date'));
            $query->groupBy(DB::raw('DATE(user_gps_traces.created_at)'));
        }

        $this->applyCreatedAtDateFilters($request, $query, 'user_gps_traces.created_at');

        // Dealership and Department Filtering with server-side enforcement
        $user = Auth::user();
        $employee = $user->employee;

        if ($user->user_type !== 'admin') {
            // Restriction: if user has dealership/department, they CANNOT override it
            if ($employee && $employee->dealership_id) {
                $query->where('employees.dealership_id', $employee->dealership_id);
            } elseif ($request->has('dealership_id') && !empty($request->dealership_id)) {
                $query->where('employees.dealership_id', $request->dealership_id);
            }

            if ($employee && $employee->department_id) {
                $query->where('employees.department_id', $employee->department_id);
            } elseif ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('employees.department_id', $request->department_id);
            }
        } else {
            // Admin can filter by anything
            if ($request->has('dealership_id') && !empty($request->dealership_id)) {
                $query->where('employees.dealership_id', $request->dealership_id);
            }
            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('employees.department_id', $request->department_id);
            }
        }

        $canViewAll = checkMenu(Session::get('role_id'), 38, 'read');
        $canViewSub = checkMenu(Session::get('role_id'), 40, 'read');
        $canViewSelf = checkMenu(Session::get('role_id'), 29, 'read');

        if ($user->user_type !== 'admin' && !$canViewAll) {
            $allowedUserIds = [];
            if ($canViewSelf) {
                $allowedUserIds[] = $user->id;
            }
            if ($canViewSub && $employee) {
                $subordinateIds = $employee->subordinates->pluck('user_id')->toArray();
                $allowedUserIds = array_merge($allowedUserIds, $subordinateIds);
            }
            $allowedUserIds = array_unique($allowedUserIds);

            if (empty($allowedUserIds)) {
                $query->where('user_gps_traces.user_id', -1);
            } else {
                if ($request->has('user_id') && !empty($request->user_id)) {
                    if (in_array($request->user_id, $allowedUserIds)) {
                        $query->where('user_gps_traces.user_id', $request->user_id);
                    } else {
                        $query->where('user_gps_traces.user_id', -1);
                    }
                } else {
                    $query->whereIn('user_gps_traces.user_id', $allowedUserIds);
                }
            }
        } elseif ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_gps_traces.user_id', $request->user_id);
        }

        try {
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('date', function ($row) use ($splitByDate) {
                    if ($splitByDate && !empty($row->visit_date)) {
                        return Carbon::parse($row->visit_date)->format('Y-m-d');
                    }
                    return Carbon::parse($row->date)->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');
                })
                ->addColumn('travel_expense', function ($row) {
                    return null; // Will be loaded lazily
                })
                ->addColumn('engineer_rate', function ($row) use ($engineerRate) {
                    return $engineerRate;
                })
                ->addColumn('action', function ($row) use ($splitByDate) {
                    $visitDateAttr = '';
                    if ($splitByDate && !empty($row->visit_date)) {
                        $visitDateAttr = ' data-visit-date="' . e($row->visit_date) . '"';
                    }
                    $btn = '<span style="white-space: nowrap;">';
                    $btn .= '<a href="#" class="btn btn-info btn-sm select-visit" data-visit-id="' . $row->visit_id . '"' . $visitDateAttr . ' title="Locate"><i class="fa fa-map-marker-alt"></i></a>';
                    $btn .= ' <a href="#" class="btn btn-warning btn-sm unlocate-visit" data-visit-id="' . $row->visit_id . '"' . $visitDateAttr . ' style="display:none;" title="Unlocate"><i class="fa fa-undo"></i></a>';
                    if (Auth::user()->user_type === 'admin') {
                        $btn .= ' <button class="btn btn-danger btn-sm delete-trace" data-visit-id="' . $row->visit_id . '"' . $visitDateAttr . ' title="Delete"><i class="fa fa-trash"></i></button>';
                    }
                    $btn .= '</span>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Datatables error in LiveLocationController (Timeline): ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getEmployeesWithVisits(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        $query = \App\Models\User::query()
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->select('users.id', 'users.name');

        // Only filter by gpsTraces if 'all' is not requested
        if (!$request->has('all') || $request->all != 1) {
            $query->whereHas('gpsTraces', function ($q) use ($request) {
                $this->applyCreatedAtDateFilters($request, $q, 'created_at');
            });
        }

        // Apply Dealership/Department filters with server-side enforcement
        if ($user->user_type !== 'admin') {
            if ($employee && $employee->dealership_id) {
                $query->where('employees.dealership_id', $employee->dealership_id);
            } elseif ($request->has('dealership_id') && !empty($request->dealership_id)) {
                $query->where('employees.dealership_id', $request->dealership_id);
            }

            if ($employee && $employee->department_id) {
                $query->where('employees.department_id', $employee->department_id);
            } elseif ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('employees.department_id', $request->department_id);
            }
        } else {
            if ($request->has('dealership_id') && !empty($request->dealership_id)) {
                $query->where('employees.dealership_id', $request->dealership_id);
            }
            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('employees.department_id', $request->department_id);
            }
        }

        $canViewAll = checkMenu(Session::get('role_id'), 38, 'read');
        $canViewSub = checkMenu(Session::get('role_id'), 40, 'read');
        $canViewSelf = checkMenu(Session::get('role_id'), 29, 'read');

        if ($user->user_type !== 'admin' && !$canViewAll) {
            $allowedUserIds = [];
            if ($canViewSelf) {
                $allowedUserIds[] = $user->id;
            }
            if ($canViewSub && $employee) {
                $subordinateIds = $employee->subordinates->pluck('user_id')->toArray();
                $allowedUserIds = array_merge($allowedUserIds, $subordinateIds);
            }
            $allowedUserIds = array_unique($allowedUserIds);

            if (empty($allowedUserIds)) {
                $query->where('users.id', -1);
            } else {
                $query->whereIn('users.id', $allowedUserIds);
            }
        }

        $employees = $query->get();
        return response()->json($employees);
    }

    public function getVisitTraces(Request $request, $visitId)
    {
        $query = UserGpsTrace::where('visit_id', $visitId)
            ->with(['task'])
            ->orderBy('created_at')
            ->orderBy('id');

        $this->applyCreatedAtDateFilters($request, $query, 'created_at');

        $traces = $query->get();
        $rawTraces = $traces;

        $startedTime = null;
        $endedTime = null;
        $timeTaken = null;
        $taskLogs = collect();
        $taskFollowups = collect();
        $visitTasks = collect();
        $haltPoints = collect();

        if ($traces->isNotEmpty()) {
            $engineerRate = (float) DB::table('settings')->where('key', 'travel_allowance_engineer_rate_per_call')->value('value') ?: 0.0;

            $user = Auth::user();
            $employee = $user->employee;
            $canViewAll = checkMenu(Session::get('role_id'), 38, 'read');
            $canViewSub = checkMenu(Session::get('role_id'), 40, 'read');
            $canViewSelf = checkMenu(Session::get('role_id'), 29, 'read');

            $firstTrace = $traces->first();
            $traceUserId = $firstTrace->user_id;

            if ($user->user_type !== 'admin' && !$canViewAll) {
                $allowed = false;
                if ($canViewSelf && $traceUserId == $user->id) {
                    $allowed = true;
                }
                if (!$allowed && $canViewSub && $employee) {
                    $isSubordinate = $employee->subordinates()->where('user_id', $traceUserId)->exists();
                    if ($isSubordinate) {
                        $allowed = true;
                    }
                }
                if (!$allowed) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
            }

            $lastTrace = $traces->last();
            $startedTime = $firstTrace->created_at->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');
            $endedTime = $lastTrace->created_at->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');

            $start = $firstTrace->created_at;
            $end = $lastTrace->created_at;
            $totalSeconds = $start->diffInSeconds($end);
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds / 60) % 60);
            $seconds = $totalSeconds % 60;
            $timeTaken = sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);

            // Build visit tasks + halt points from chronological (non-reordered) traces
            $visitTaskIdsRaw = $rawTraces->whereNotNull('task_id')->pluck('task_id')->unique();
            if ($visitTaskIdsRaw->isNotEmpty()) {
                $visitTasks = \App\Models\Task::whereIn('id', $visitTaskIdsRaw)->get([
                    'id',
                    'title',
                    'status',
                    'is_service',
                    'lead_id',
                    'entry_id',
                    'entry_type',
                ]);
            }

            $haltSourceTraces = $rawTraces
                ->filter(fn($t) => !empty($t->latitude) && !empty($t->longitude))
                ->values();
            $haltPoints = $this->findHaltPoints($haltSourceTraces, $visitTasks, null);

            // Apply path modifications if requested
            if ($request->boolean('closest_mode')) {
                $traces = $this->reorderTracesByClosest($traces);
            }

            if ($request->boolean('smoothing_mode')) {
                $traces = $this->snapTracesToRoads($traces);
            }

            // Calculate total distance for this specific visit
            $totalDistance = 0;
            if ($traces->count() > 1) {
                for ($i = 0; $i < $traces->count() - 1; $i++) {
                    $point1 = $traces[$i];
                    $point2 = $traces[$i + 1];
                    if (empty($point1->latitude) || empty($point2->latitude)) continue;
                    $totalDistance += calculateDistance(
                        $point1->latitude,
                        $point1->longitude,
                        $point2->latitude,
                        $point2->longitude
                    );
                }
            }
            $distanceCovered = round($totalDistance / 1000, 3);

            // Fetch task logs for this user during this visit
            $user = $firstTrace->user;
            if ($user && $user->employee) {
                $taskLogs = \App\Models\TaskLog::with('task')
                    ->where('employee_id', $user->employee->id)
                    ->where(function ($query) use ($start, $end) {
                        $query->whereBetween('start_time', [$start, $end])
                            ->orWhereBetween('end_time', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('start_time', '<=', $start)
                                    ->where(function ($q2) use ($end) {
                                        $q2->where('end_time', '>=', $end)
                                            ->orWhereNull('end_time');
                                    });
                            });
                    })
                    ->orderBy('start_time')
                    ->get();

                // Fetch task follow-ups
                $taskFollowups = \App\Models\TaskFollowup::whereIn('task_id', $taskLogs->pluck('task_id')->unique())
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
            }

            // Calculate Call TA based on unique service tasks in this visit
            $visitTaskIds = $traces->whereNotNull('task_id')->pluck('task_id')->unique();
            $serviceTaskCount = \App\Models\Task::whereIn('id', $visitTaskIds)
                ->where('is_service', 1)
                ->whereIn('status', ['completed', 'partial'])
                ->count();
            $calculatedCallTa = $serviceTaskCount * $engineerRate;
        }

        // Map logs to ensure they always have a start time for the frontend sorting/display
        $mappedLogs = $taskLogs->map(function ($log) {
            $effectiveStart = $log->start_time ?: $log->created_at;
            $log->start_time = $effectiveStart;
            return $log;
        });

        return response()->json([
            'traces' => $traces,
            'started_time' => $startedTime,
            'ended_time' => $endedTime,
            'time_taken' => $timeTaken,
            'distance_covered' => $distanceCovered ?? 0,
            'travel_expense' => $this->calculateVisitTravelExpense($traces),
            'engineer_rate' => $engineerRate,
            'call_ta' => $calculatedCallTa ?? 0,
            'task_logs' => $mappedLogs,
            'task_followups' => $taskFollowups,
            'visit_tasks' => $visitTasks->values(),
            'halt_points' => $haltPoints->values(),
        ]);
    }

    public function deleteTrace($visitId)
    {
        if (Auth::user()->user_type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        try {
            UserGpsTrace::where('visit_id', $visitId)->delete();
            return response()->json(['message' => 'Trace deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting trace: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting trace'], 500);
        }
    }

    /**
     * Get the distance covered for a specific visit.
     */
    public function getDistanceCovered($visitId)
    {
        $visitTraces = UserGpsTrace::where('visit_id', $visitId)->orderBy('created_at')->get();

        $totalDistance = 0;
        if ($visitTraces->count() > 1) {
            for ($i = 0; $i < $visitTraces->count() - 1; $i++) {
                $point1 = $visitTraces[$i];
                $point2 = $visitTraces[$i + 1];
                $totalDistance += calculateDistance(
                    $point1->latitude,
                    $point1->longitude,
                    $point2->latitude,
                    $point2->longitude
                );
            }
        }

        return response()->json(['distance_covered' => round($totalDistance / 1000, 3)]);
    }

    private function reorderTracesByClosest($traces)
    {
        if ($traces->count() < 2) {
            return $traces;
        }

        $validTraces = $traces->filter(function ($t) {
            return !empty($t->latitude) && !empty($t->longitude);
        })->values();

        if ($validTraces->isEmpty()) {
            return $traces;
        }

        // Optimization: If too many traces, thin them out before reordering O(N^2)
        if ($validTraces->count() > 1000) {
            $thinned = collect([$validTraces->first()]);
            $totalCount = $validTraces->count();
            $step = floor($totalCount / 500); // Reduce to roughly 500 points
            for ($i = $step; $i < $totalCount - 1; $i += $step) {
                $thinned->push($validTraces[$i]);
            }
            $thinned->push($validTraces->last());
            $validTraces = $thinned;
        }

        $sorted = new \Illuminate\Support\Collection([$validTraces->first()]);
        $remaining = $validTraces->slice(1)->values();

        while ($remaining->isNotEmpty()) {
            $lastPoint = $sorted->last();
            $nearestIndex = -1;
            $minDist = PHP_FLOAT_MAX;

            foreach ($remaining as $index => $point) {
                $dist = calculateDistance($lastPoint->latitude, $lastPoint->longitude, $point->latitude, $point->longitude);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $nearestIndex = $index;
                }
            }

            if ($nearestIndex !== -1) {
                $sorted->push($remaining[$nearestIndex]);
                $remaining->forget($nearestIndex);
                $remaining = $remaining->values();
            } else {
                break;
            }
        }

        return $sorted;
    }

    public function getStats(Request $request)
    {
        // Optimization: If no user is selected, skip expensive stats calculation
        // and return empty values, as the 4 cards should only be processed for specific routes.
        if (!$request->has('user_id') || empty($request->user_id)) {
            return response()->json([
                'started_time' => 'N/A',
                'ended_time' => 'N/A',
                'time_taken' => 'N/A',
                'total_distance' => 'N/A',
                'total_travel_expense' => 0,
                'total_call_ta' => 0
            ]);
        }

        $query = $this->getFilteredQuery($request, false); // No relationships for stats

        // Use cursor for large datasets to save memory or chunking
        // For stats, we need all traces to calculate aggregate distance and expenses.
        // We'll use toBase() to avoid Eloquent overhead for 10k+ traces.
        $data = $query->toBase()->get();
        $groupedData = $data->groupBy('visit_id');

        // Bulk load all required tasks for stats calculation
        $allTaskIds = $data->whereNotNull('task_id')->pluck('task_id')->unique();
        $tasks = \App\Models\Task::whereIn('id', $allTaskIds)->get()->keyBy('id');

        $totalDistance = 0;
        $totalTimeSeconds = 0;
        $totalTravelExpense = 0.0;
        $totalCallTa = 0.0;
        $earliestStart = null;
        $latestEnd = null;

        $engineerRate = (float) DB::table('settings')->where('key', 'travel_allowance_engineer_rate_per_call')->value('value') ?: 0.0;

        foreach ($groupedData as $visitId => $traces) {
            if ($traces->count() > 0) {
                // Calculate Call TA for this visit based on service tasks
                $visitTaskIds = $traces->whereNotNull('task_id')->pluck('task_id')->unique();
                $visitTasks = $tasks->whereIn('id', $visitTaskIds);
                $serviceTaskCount = $visitTasks
                    ->where('is_service', 1)
                    ->whereIn('status', ['completed', 'partial'])
                    ->count();
                $totalCallTa += ($serviceTaskCount * $engineerRate);

                // Time markers should always be chronological
                $start = Carbon::parse($traces->first()->created_at);
                $end = Carbon::parse($traces->last()->created_at);

                if ($earliestStart === null || $start->lt($earliestStart)) {
                    $earliestStart = $start;
                }
                if ($latestEnd === null || $end->gt($latestEnd)) {
                    $latestEnd = $end;
                }

                // Distance calculation might use closest mode reordering
                $visitTraces = $traces;
                if ($request->boolean('closest_mode')) {
                    $visitTraces = $this->reorderTracesByClosest($visitTraces);
                }

                if ($request->boolean('smoothing_mode')) {
                    $visitTraces = $this->snapTracesToRoads($visitTraces);
                }

                if ($visitTraces->count() > 1) {
                    for ($i = 0; $i < $visitTraces->count() - 1; $i++) {
                        $point1 = $visitTraces[$i];
                        $point2 = $visitTraces[$i + 1];

                        if (empty($point1->latitude) || empty($point2->latitude)) continue;

                        $totalDistance += calculateDistance(
                            $point1->latitude,
                            $point1->longitude,
                            $point2->latitude,
                            $point2->longitude
                        );
                    }
                }
                $totalTravelExpense += $this->calculateVisitTravelExpense($visitTraces);
                // Time taken for this visit
                $totalTimeSeconds += $end->diffInSeconds($start);
            }
        }

        // Format total time
        $formattedTime = gmdate('H\h i\m s\s', $totalTimeSeconds);

        return response()->json([
            'total_distance' => round($totalDistance / 1000, 3) . ' km',
            'total_travel_expense' => round($totalTravelExpense, 2),
            'total_call_ta' => round($totalCallTa, 2),
            'started_time' => $earliestStart ? $earliestStart->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s') : 'N/A',
            'ended_time' => $latestEnd ? $latestEnd->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s') : 'N/A',
            'time_taken' => $formattedTime
        ]);
    }

    public function export(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');

        // Use false for relationships to avoid hydrating thousands of models with nested relations
        $query = $this->getFilteredQuery($request, false);

        // Select only necessary columns to reduce memory
        $data = $query->select(
            'user_gps_traces.visit_id',
            'user_gps_traces.user_id',
            'user_gps_traces.task_id',
            'user_gps_traces.client_id',
            'user_gps_traces.created_at',
            'user_gps_traces.latitude',
            'user_gps_traces.longitude',
            'user_gps_traces.remarks',
            'user_gps_traces.vehicle_type',
            'user_gps_traces.image_path'
        )->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'No data found for the selected filters.');
        }

        $groupedData = $data->groupBy('visit_id');

        // Bulk load all required metadata for all visits to avoid N+1
        $userIds = $data->pluck('user_id')->unique();
        $taskIds = $data->whereNotNull('task_id')->pluck('task_id')->unique();
        $clientIdsFromTraces = $data->whereNotNull('client_id')->pluck('client_id')->unique();

        $users = \App\Models\User::with(['employee.department', 'employee.dealership'])->whereIn('id', $userIds)->get()->keyBy('id');
        $tasks = \App\Models\Task::whereIn('id', $taskIds)->get()->keyBy('id');

        // Extract all Lead and Service IDs from the pre-loaded tasks
        $allLeadIds = $tasks->pluck('lead_id')->filter()->unique();
        $allServiceEntryIds = $tasks->filter(function ($t) {
            if (empty($t->entry_id)) return false;
            if (!empty($t->is_service)) return true;
            if (empty($t->entry_type)) return false;
            return $t->entry_type === \App\Models\Service::class || $t->entry_type === 'App\\Models\\Service';
        })->pluck('entry_id')->filter()->unique();

        $allLeads = \App\Models\Lead::whereIn('id', $allLeadIds)->with(['product', 'leadSource'])->get(['id', 'client_id', 'location', 'name', 'phone_number', 'email', 'company', 'status', 'remarks', 'product_id', 'lead_source_id'])->keyBy('id');
        $allServices = $allServiceEntryIds->isNotEmpty()
            ? \App\Models\Service::whereIn('id', $allServiceEntryIds)->with(['product'])->get(['id', 'client_id', 'requested_location', 'name', 'contact_info', 'contact_person', 'call_status', 'call_remarks', 'product_id', 'type_of_service', 'machine_serial_number'])->keyBy('id')
            : collect();

        // Collect ALL potential client IDs from traces, leads, and services
        $allClientIds = collect()
            ->merge($clientIdsFromTraces)
            ->merge($allLeads->pluck('client_id')->filter())
            ->merge($allServices->pluck('client_id')->filter())
            ->unique();

        $clients = \App\Models\Client::whereIn('id', $allClientIds)->get(['id', 'name', 'phone_number', 'email', 'address'])->keyBy('id');

        // Extract manager IDs to load their names
        $managerIds = $users->map(fn($u) => $u->employee?->reporting_to)->filter()->unique();
        $managers = \App\Models\User::whereIn('id', $managerIds)->pluck('name', 'id');

        // Extract employee IDs for task logs
        $employeeIds = $users->map(fn($u) => $u->employee?->id)->filter()->unique();

        // Bulk load task logs for the entire time range if possible
        $minDate = $data->min('created_at');
        $maxDate = $data->max('created_at');

        $allTaskLogs = \App\Models\TaskLog::with('task')
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($q) use ($minDate, $maxDate) {
                $q->whereBetween('start_time', [$minDate, $maxDate])
                    ->orWhereBetween('end_time', [$minDate, $maxDate]);
            })
            ->get();

        $processedData = collect();
        $engineerRate = (float) DB::table('settings')->where('key', 'travel_allowance_engineer_rate_per_call')->value('value') ?: 0.0;

        foreach ($groupedData as $visitId => $traces) {
            $visitTraces = $traces;
            if ($request->boolean('closest_mode')) {
                $visitTraces = $this->reorderTracesByClosest($visitTraces);
            }

            if ($request->boolean('smoothing_mode')) {
                $visitTraces = $this->snapTracesToRoads($visitTraces);
            }

            $firstTrace = $visitTraces->first();
            $lastTrace = $visitTraces->last();

            // Use original traces for metadata lookup as $visitTraces might be snapped stdClasses
            $originalFirstTrace = $traces->first();

            $totalDistance = 0;
            if ($visitTraces->count() > 1) {
                // If snapTracesToRoads was used, $visitTraces might contain stdClass objects instead of Models
                for ($i = 0; $i < $visitTraces->count() - 1; $i++) {
                    $point1 = $visitTraces[$i];
                    $point2 = $visitTraces[$i + 1];
                    if (empty($point1->latitude) || empty($point2->latitude)) continue;

                    $totalDistance += calculateDistance(
                        $point1->latitude,
                        $point1->longitude,
                        $point2->latitude,
                        $point2->longitude
                    );
                }
            }

            $user = $users[$originalFirstTrace->user_id] ?? null;
            if (!$user) continue;

            $employee = $user->employee ?? null;

            // Collect all unique tasks, leads, services, and clients in this visit from pre-loaded data
            $visitTaskIds = $traces->whereNotNull('task_id')->pluck('task_id')->unique();
            $visitTasks = $tasks->whereIn('id', $visitTaskIds);

            $visitLeadIds = $visitTasks->pluck('lead_id')->filter()->unique();
            $visitLeads = $allLeads->whereIn('id', $visitLeadIds);

            $visitServiceEntryIds = $visitTasks
                ->filter(function ($t) {
                    if (empty($t->entry_id)) return false;
                    if (!empty($t->is_service)) return true;
                    if (empty($t->entry_type)) return false;
                    return $t->entry_type === \App\Models\Service::class || $t->entry_type === 'App\\Models\\Service';
                })
                ->pluck('entry_id')
                ->filter()
                ->unique();
            $visitServices = $allServices->whereIn('id', $visitServiceEntryIds);

            $taskTitlesCol = $visitTasks->map(function ($t) {
                $category = $t->is_service ? '[Service]' : ($t->lead_id ? '[Lead]' : '[General]');
                return "$category {$t->title}";
            });

            if ($taskTitlesCol->isEmpty()) {
                if ($visitServices->isNotEmpty()) {
                    $visitServices->each(fn($s) => $taskTitlesCol->push("[Service] " . ($s->name ?: 'Service')));
                }
                if ($visitLeads->isNotEmpty()) {
                    $visitLeads->each(fn($l) => $taskTitlesCol->push("[Lead] " . ($l->name ?: 'Lead')));
                }
            }
            $taskTitles = $taskTitlesCol->unique()->implode(', ') ?: 'N/A';

            $serviceTaskCount = $visitTasks
                ->where('is_service', 1)
                ->whereIn('status', ['completed', 'partial'])
                ->count();
            $totalCallTa = $serviceTaskCount * $engineerRate;

            $visitTraceClientIds = $traces->whereNotNull('client_id')->pluck('client_id')->unique();
            $visitDerivedClientIds = collect()
                ->merge($visitTraceClientIds)
                ->merge($visitLeads->pluck('client_id')->filter())
                ->merge($visitServices->pluck('client_id')->filter())
                ->unique();

            $visitClients = $clients->whereIn('id', $visitDerivedClientIds);

            // Correctly aggregate all relevant data, prioritizing Tasks/Services/Leads but capturing all.
            $allClientDetails = collect();
            $visitClients->each(function ($c) use ($allClientDetails) {
                $detail = "Client: $c->name";
                if ($c->phone_number) $detail .= " (Ph: $c->phone_number)";
                if ($c->email) $detail .= " (Email: $c->email)";
                if ($c->address) $detail .= " (Addr: $c->address)";
                $allClientDetails->push($detail);
            });
            $clientFullInfo = $allClientDetails->unique()->implode('; ') ?: 'N/A';

            $allClientNames = collect();
            $visitClients->each(fn($c) => $c->name ? $allClientNames->push($c->name) : null);
            $visitLeads->each(fn($l) => $l->name ? $allClientNames->push($l->name) : null);
            $visitServices->each(fn($s) => $s->name ? $allClientNames->push($s->name) : null);
            $clientNames = $allClientNames->filter()->unique()->implode(', ') ?: 'N/A';

            $allContacts = collect();
            $visitClients->each(fn($c) => $c->phone_number ? $allContacts->push($c->phone_number) : null);
            $visitLeads->each(fn($l) => $l->phone_number ? $allContacts->push($l->phone_number) : null);
            $visitServices->each(fn($s) => $s->contact_info ? $allContacts->push($s->contact_info) : null);
            $clientPhones = $allContacts->filter()->unique()->implode(', ') ?: 'N/A';

            // Additional Detailed Summary for Leads/Services
            $detailedLeadServiceInfo = collect();
            $visitLeads->each(function ($l) use ($detailedLeadServiceInfo) {
                $productName = $l->product ? $l->product->name : 'N/A';
                $source = $l->leadSource ? $l->leadSource->name : 'N/A';
                $detailedLeadServiceInfo->push("[Lead] Name: $l->name | Phone: $l->phone_number | Product: $productName | Source: $source | Status: $l->status | Remarks: $l->remarks");
            });
            $visitServices->each(function ($s) use ($detailedLeadServiceInfo) {
                $productName = $s->product ? $s->product->name : 'N/A';
                $detailedLeadServiceInfo->push("[Service] Name: $s->name | Contact: $s->contact_info | Product: $productName | S/N: $s->machine_serial_number | Status: $s->call_status | Remarks: $s->call_remarks");
            });
            $leadServiceSummary = $detailedLeadServiceInfo->unique()->implode("\n") ?: 'No detailed Lead/Service info.';

            $vehicleTypes = $traces->pluck('vehicle_type')->filter()->unique()->map(fn($v) => ucfirst($v))->implode(', ') ?: 'N/A';
            $allRemarks = $traces->whereNotNull('remarks')->pluck('remarks')->unique()->implode('; ') ?: 'N/A';

            $haltPoints = $this->findHaltPoints($traces, $tasks, $clients);
            $pointDetails = $haltPoints->map(function ($hp, $index) {
                $i = $index + 1;
                $details = "Point #$i: at {$hp['start_time']} - {$hp['location_info']}";
                if ($hp['active_tasks']) {
                    $details .= " | Tasks: {$hp['active_tasks']}";
                }
                if ($hp['remarks']) {
                    $details .= " | Remarks: {$hp['remarks']}";
                }
                if ($hp['images']->isNotEmpty()) {
                    $imageUrls = $hp['images']->map(fn($img) => url($img))->implode(', ');
                    $details .= " | Images: $imageUrls";
                }
                return $details;
            })->implode("\n") ?: 'None';

            // Filter relevant logs from the pre-fetched collection by employee_id
            $visitTaskLogs = $allTaskLogs->where('employee_id', $employee?->id)
                ->filter(function ($log) use ($firstTrace, $lastTrace) {
                    $logStart = Carbon::parse($log->start_time);
                    $logEnd = $log->end_time ? Carbon::parse($log->end_time) : null;
                    $visitStart = Carbon::parse($firstTrace->created_at);
                    $visitEnd = Carbon::parse($lastTrace->created_at);

                    return ($logStart->between($visitStart, $visitEnd)) ||
                        ($logEnd && $logEnd->between($visitStart, $visitEnd)) ||
                        ($logStart->lte($visitStart) && (!$logEnd || $logEnd->gte($visitEnd)));
                });

            $logDetails = $visitTaskLogs->map(function ($log) {
                return ($log->task ? $log->task->title : 'Task') . ' (' . Carbon::parse($log->start_time)->format('H:i') . ' - ' . ($log->end_time ? Carbon::parse($log->end_time)->format('H:i') : 'Active') . ')';
            })->implode('; ') ?: 'N/A';

            // Calculate Task Running Duration (Overlap of logs with visit time)
            $totalTaskSeconds = 0;
            $visitStart = Carbon::parse($firstTrace->created_at);
            $visitEnd = Carbon::parse($lastTrace->created_at);

            foreach ($visitTaskLogs as $log) {
                $logStart = Carbon::parse($log->start_time);
                $logEnd = $log->end_time ? Carbon::parse($log->end_time) : now();

                // Intersect the log interval with the visit interval
                $overlapStart = $logStart->gt($visitStart) ? $logStart : $visitStart;
                $overlapEnd = $logEnd->lt($visitEnd) ? $logEnd : $visitEnd;

                if ($overlapStart->lt($overlapEnd)) {
                    $totalTaskSeconds += $overlapStart->diffInSeconds($overlapEnd);
                }
            }
            $taskDuration = gmdate('H:i:s', $totalTaskSeconds);

            $startedTime = Carbon::parse($firstTrace->created_at);
            $endedTime = Carbon::parse($lastTrace->created_at);
            $timeSpent = gmdate('H:i:s', $endedTime->diffInSeconds($startedTime));

            // Aggregate location from all tasks and clients
            $allLocations = collect();
            $visitTasks->each(fn($t) => $t->location ? $allLocations->push($t->location) : null);
            $visitLeads->each(fn($l) => $l->location ? $allLocations->push($l->location) : null);
            $visitServices->each(fn($s) => $s->requested_location ? $allLocations->push($s->requested_location) : null);
            $visitClients->each(fn($c) => $c->address ? $allLocations->push($c->address) : null);
            $location = $allLocations->filter()->unique()->implode('; ') ?: 'N/A';

            $allStatus = $visitTasks->pluck('status');
            $visitLeads->each(fn($l) => $l->status ? $allStatus->push($l->status) : null);
            $visitServices->each(fn($s) => $s->call_status ? $allStatus->push($s->call_status) : null);
            $statusStr = $allStatus->filter()->unique()->implode(', ') ?: 'N/A';

            $finalRemarks = $visitTasks->pluck('description');
            $visitLeads->each(fn($l) => $l->remarks ? $finalRemarks->push($l->remarks) : null);
            $visitServices->each(fn($s) => $s->call_remarks ? $finalRemarks->push($s->call_remarks) : null);
            $remarksStr = $finalRemarks->filter()->unique()->implode('; ') ?: 'N/A';

            $processedData->push([
                'user_name' => $user->name ?? 'N/A',
                'employee_code' => $employee->employee_id ?? 'N/A',
                'designation' => $employee->designation ?? 'N/A',
                'department' => $employee->department->name ?? 'N/A',
                'dealership' => $employee->dealership->name ?? 'N/A',
                'manager' => $managers[$employee->reporting_to] ?? 'N/A',
                'email' => $user->email ?? 'N/A',
                'phone' => $user->phone ?? 'N/A',
                'task_type' => $taskTitles,
                'vehicle_type' => $vehicleTypes,
                'point_count' => $haltPoints->count(),
                'point_info' => $pointDetails,
                'halt_points' => $haltPoints,
                'visit_remarks' => "Tasks: $logDetails. Visit Remarks: $allRemarks",
                'date' => $startedTime->setTimezone('Asia/Kolkata')->format('d-m-Y'),
                'started_time' => $startedTime->setTimezone('Asia/Kolkata')->format('H:i:s'),
                'ended_time' => $endedTime->setTimezone('Asia/Kolkata')->format('H:i:s'),
                'time_spent' => $timeSpent,
                'task_duration' => $taskDuration,
                'kms_travelled' => round($totalDistance / 1000, 3),
                'travel_expense' => $this->calculateVisitTravelExpense($visitTraces),
                'call_ta' => $totalCallTa,
                'client_name' => $clientNames,
                'client_full_info' => $clientFullInfo,
                'lead_service_summary' => $leadServiceSummary,
                'contact' => $clientPhones,
                'location' => $location,
                'status' => $statusStr,
                'remarks' => $remarksStr,
            ]);
        }

        return Excel::download(new VisitsExport($processedData), 'visits_report.xlsx');
    }

    public function exportManifest(Request $request)
    {
        $exportType = strtolower((string) $request->get('export_type', ''));
        if (!in_array($exportType, ['excel', 'pdf'], true)) {
            return response()->json(['message' => 'Invalid export type.'], 422);
        }

        $splitByDate = $request->boolean('split_by_date');

        $visits = collect();
        if ($splitByDate) {
            $visits = $this->getFilteredQuery($request, false)
                ->reorder()
                ->select(
                    'user_gps_traces.visit_id',
                    DB::raw('DATE(user_gps_traces.created_at) as visit_date')
                )
                ->groupBy('user_gps_traces.visit_id', DB::raw('DATE(user_gps_traces.created_at)'))
                ->orderBy(DB::raw('DATE(user_gps_traces.created_at)'), 'desc')
                ->get()
                ->map(function ($row) {
                    return [
                        'visit_id' => (string) ($row->visit_id ?? ''),
                        'visit_date' => (string) ($row->visit_date ?? ''),
                    ];
                })
                ->filter(fn($v) => !empty($v['visit_id']) && !empty($v['visit_date']))
                ->values();
        } else {
            $visitIds = $this->getFilteredQuery($request, false)
                ->reorder()
                ->select('user_gps_traces.visit_id')
                ->distinct()
                ->pluck('user_gps_traces.visit_id')
                ->filter()
                ->values();

            $visits = $visitIds->map(fn($v) => ['visit_id' => (string) $v, 'visit_date' => null])->values();
        }

        $token = (string) Str::uuid();
        $visitKeys = $visits->map(function ($v) {
            $visitId = (string) ($v['visit_id'] ?? '');
            $visitDate = $v['visit_date'] ?? null;
            return $visitDate ? "{$visitId}|{$visitDate}" : $visitId;
        })->values();
        $visitKeySet = array_fill_keys($visitKeys->all(), true);

        $filters = $request->only(['user_id', 'start_date', 'end_date', 'dealership_id', 'department_id', 'visit_date', 'split_by_date']);
        $options = [
            'closest_mode' => $request->boolean('closest_mode'),
            'smoothing_mode' => $request->boolean('smoothing_mode'),
        ];

        Cache::store('file')->put($this->timelineExportCacheKey($token), [
            'export_type' => $exportType,
            'filters' => $filters,
            'options' => $options,
            'split_by_date' => $splitByDate,
            'visits' => $visits->all(),
            'visit_keys' => $visitKeys->all(),
            'visit_key_set' => $visitKeySet,
            'processed_visit_keys' => [],
            'rows' => [],
        ], now()->addHour());

        return response()->json([
            'token' => $token,
            'visits' => $visits->all(),
        ]);
    }

    public function exportProcess(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'visit_id' => ['nullable'],
            'visit_date' => ['nullable', 'date'],
            'visits' => ['nullable', 'array']
        ]);

        $token = (string) $request->get('token');
        $cacheKey = $this->timelineExportCacheKey($token);
        $session = Cache::store('file')->get($cacheKey);
        if (!$session) {
            return response()->json(['message' => 'Export session expired.'], 410);
        }

        $splitByDate = !empty($session['split_by_date']);

        $visitsParam = $request->get('visits');
        if (empty($visitsParam) && $request->has('visit_id')) {
            $visitsParam = [[
                'visit_id' => $request->get('visit_id'),
                'visit_date' => $request->get('visit_date'),
            ]];
        }

        if (empty($visitsParam) || !is_array($visitsParam)) {
            return response()->json(['message' => 'No visits provided.'], 422);
        }

        $engineerRate = (float) DB::table('settings')->where('key', 'travel_allowance_engineer_rate_per_call')->value('value') ?: 0.0;

        $summary = null;

        foreach ($visitsParam as $visitData) {
            $visitId = (string) ($visitData['visit_id'] ?? '');
            $visitDate = $visitData['visit_date'] ?? null;

            if ($splitByDate && empty($visitDate)) {
                continue;
            }

            $visitKey = $splitByDate ? "{$visitId}|{$visitDate}" : $visitId;

            if (empty($session['visit_key_set'][$visitKey])) {
                continue;
            }

            if (in_array($visitKey, $session['processed_visit_keys'], true)) {
                continue;
            }

            $traceQuery = UserGpsTrace::query()->where('visit_id', $visitId);

            if ($splitByDate && !empty($visitDate)) {
                $traceQuery->whereDate('created_at', $visitDate);
            }

            $traces = $traceQuery
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get([
                    'visit_id',
                    'user_id',
                    'task_id',
                    'client_id',
                    'created_at',
                    'latitude',
                    'longitude',
                    'remarks',
                    'vehicle_type',
                    'image_path',
                ]);

            $row = $this->buildProcessedRowForVisitTraces($traces, $session['options'] ?? [], $engineerRate);

            $session['processed_visit_keys'][] = $visitKey;
            if ($row) {
                $session['rows'][] = $row;
                $summary = [
                    'user_name' => $row['user_name'] ?? null,
                    'date' => $row['date'] ?? null,
                ];
            }
        }

        Cache::store('file')->put($cacheKey, $session, now()->addHour());

        return response()->json([
            'processed' => count($session['processed_visit_keys']),
            'total' => count($session['visit_keys']),
            'summary' => $summary,
        ]);
    }

    public function exportCancel(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        Cache::store('file')->forget($this->timelineExportCacheKey((string) $request->get('token')));
        return response()->json(['ok' => true]);
    }

    public function exportDownload(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $token = (string) $request->get('token');
        $cacheKey = $this->timelineExportCacheKey($token);
        $session = Cache::store('file')->get($cacheKey);
        if (!$session) {
            return back()->with('error', 'Export session expired. Please try again.');
        }

        $total = count($session['visit_keys'] ?? []);
        $processed = count($session['processed_visit_keys'] ?? []);
        if ($total > 0 && $processed < $total) {
            return back()->with('error', 'Export is not finished yet. Please wait for processing to complete.');
        }

        $exportType = $session['export_type'] ?? null;
        $processedData = collect($session['rows'] ?? []);

        Cache::store('file')->forget($cacheKey);

        if ($processedData->isEmpty()) {
            return back()->with('error', 'No data found for the selected filters.');
        }

        if ($exportType === 'excel') {
            return Excel::download(new VisitsExport($processedData), 'visits_report.xlsx');
        }

        if ($exportType === 'pdf') {
            $primaryLogoPath = public_path('admin/assets/images/logo/korps-sync-crm-logo-white.png');
            $secondaryLogoPath = public_path('admin/assets/images/logo/svhe.png');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('timeline.pdf', [
                'data' => $processedData,
                'primaryLogoPath' => $primaryLogoPath,
                'secondaryLogoPath' => $secondaryLogoPath
            ])->setPaper('a4', 'landscape');

            return $pdf->download('visits_report.pdf');
        }

        return back()->with('error', 'Invalid export type.');
    }

    public function exportPdf(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');

        $query = $this->getFilteredQuery($request, false);
        $data = $query->select(
            'user_gps_traces.visit_id',
            'user_gps_traces.user_id',
            'user_gps_traces.task_id',
            'user_gps_traces.client_id',
            'user_gps_traces.created_at',
            'user_gps_traces.latitude',
            'user_gps_traces.longitude',
            'user_gps_traces.remarks',
            'user_gps_traces.vehicle_type',
            'user_gps_traces.image_path'
        )->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'No data found for the selected filters.');
        }

        $groupedData = $data->groupBy('visit_id');

        $userIds = $data->pluck('user_id')->unique();
        $taskIds = $data->whereNotNull('task_id')->pluck('task_id')->unique();
        $clientIdsFromTraces = $data->whereNotNull('client_id')->pluck('client_id')->unique();

        $users = \App\Models\User::with(['employee.department', 'employee.dealership'])->whereIn('id', $userIds)->get()->keyBy('id');
        $tasks = \App\Models\Task::whereIn('id', $taskIds)->get()->keyBy('id');

        // Extract all Lead and Service IDs from the pre-loaded tasks
        $allLeadIds = $tasks->pluck('lead_id')->filter()->unique();
        $allServiceEntryIds = $tasks->filter(function ($t) {
            if (empty($t->entry_id)) return false;
            if (!empty($t->is_service)) return true;
            if (empty($t->entry_type)) return false;
            return $t->entry_type === \App\Models\Service::class || $t->entry_type === 'App\\Models\\Service';
        })->pluck('entry_id')->filter()->unique();

        $allLeads = \App\Models\Lead::whereIn('id', $allLeadIds)->with(['product', 'leadSource'])->get(['id', 'client_id', 'location', 'name', 'phone_number', 'email', 'company', 'status', 'remarks', 'product_id', 'lead_source_id'])->keyBy('id');
        $allServices = $allServiceEntryIds->isNotEmpty()
            ? \App\Models\Service::whereIn('id', $allServiceEntryIds)->with(['product'])->get(['id', 'client_id', 'requested_location', 'name', 'contact_info', 'contact_person', 'call_status', 'call_remarks', 'product_id', 'type_of_service', 'machine_serial_number'])->keyBy('id')
            : collect();

        // Collect ALL potential client IDs from traces, leads, and services
        $allClientIds = collect()
            ->merge($clientIdsFromTraces)
            ->merge($allLeads->pluck('client_id')->filter())
            ->merge($allServices->pluck('client_id')->filter())
            ->unique();

        $clients = \App\Models\Client::whereIn('id', $allClientIds)->get(['id', 'name', 'phone_number', 'email', 'address'])->keyBy('id');

        // Extract manager IDs to load their names
        $managerIds = $users->map(fn($u) => $u->employee?->reporting_to)->filter()->unique();
        $managers = \App\Models\User::whereIn('id', $managerIds)->pluck('name', 'id');

        // Extract employee IDs for task logs
        $employeeIds = $users->map(fn($u) => $u->employee?->id)->filter()->unique();

        $minDate = $data->min('created_at');
        $maxDate = $data->max('created_at');
        $allTaskLogs = \App\Models\TaskLog::with('task')
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($q) use ($minDate, $maxDate) {
                $q->whereBetween('start_time', [$minDate, $maxDate])
                    ->orWhereBetween('end_time', [$minDate, $maxDate]);
            })->get();

        $processedData = collect();
        $engineerRate = (float) DB::table('settings')->where('key', 'travel_allowance_engineer_rate_per_call')->value('value') ?: 0.0;

        foreach ($groupedData as $visitId => $traces) {
            $visitTraces = $traces;
            if ($request->boolean('closest_mode')) {
                $visitTraces = $this->reorderTracesByClosest($visitTraces);
            }

            if ($request->boolean('smoothing_mode')) {
                $visitTraces = $this->snapTracesToRoads($visitTraces);
            }

            $firstTrace = $visitTraces->first();
            $lastTrace = $visitTraces->last();

            // Use original traces for metadata lookup as $visitTraces might be snapped stdClasses
            $originalFirstTrace = $traces->first();

            $totalDistance = 0;
            if ($visitTraces->count() > 1) {
                // If snapTracesToRoads was used, $visitTraces might contain stdClass objects instead of Models
                for ($i = 0; $i < $visitTraces->count() - 1; $i++) {
                    $point1 = $visitTraces[$i];
                    $point2 = $visitTraces[$i + 1];
                    if (empty($point1->latitude) || empty($point2->latitude)) continue;

                    $totalDistance += calculateDistance(
                        $point1->latitude,
                        $point1->longitude,
                        $point2->latitude,
                        $point2->longitude
                    );
                }
            }

            $user = $users[$originalFirstTrace->user_id] ?? null;
            if (!$user) continue;

            $employee = $user->employee ?? null;

            // Collect all unique tasks, leads, services, and clients in this visit from pre-loaded data
            $visitTaskIds = $traces->whereNotNull('task_id')->pluck('task_id')->unique();
            $visitTasks = $tasks->whereIn('id', $visitTaskIds);

            $visitLeadIds = $visitTasks->pluck('lead_id')->filter()->unique();
            $visitLeads = $allLeads->whereIn('id', $visitLeadIds);

            $visitServiceEntryIds = $visitTasks
                ->filter(function ($t) {
                    if (empty($t->entry_id)) return false;
                    if (!empty($t->is_service)) return true;
                    if (empty($t->entry_type)) return false;
                    return $t->entry_type === \App\Models\Service::class || $t->entry_type === 'App\\Models\\Service';
                })
                ->pluck('entry_id')
                ->filter()
                ->unique();
            $visitServices = $allServices->whereIn('id', $visitServiceEntryIds);

            $taskTitlesCol = $visitTasks->map(function ($t) {
                $category = $t->is_service ? '[Service]' : ($t->lead_id ? '[Lead]' : '[General]');
                return "$category {$t->title}";
            });

            if ($taskTitlesCol->isEmpty()) {
                if ($visitServices->isNotEmpty()) {
                    $visitServices->each(fn($s) => $taskTitlesCol->push("[Service] " . ($s->name ?: 'Service')));
                }
                if ($visitLeads->isNotEmpty()) {
                    $visitLeads->each(fn($l) => $taskTitlesCol->push("[Lead] " . ($l->name ?: 'Lead')));
                }
            }
            $taskTitles = $taskTitlesCol->unique()->implode(', ') ?: 'N/A';

            $serviceTaskCount = $visitTasks
                ->where('is_service', 1)
                ->whereIn('status', ['completed', 'partial'])
                ->count();
            $totalCallTa = $serviceTaskCount * $engineerRate;

            $visitTraceClientIds = $traces->whereNotNull('client_id')->pluck('client_id')->unique();
            $visitDerivedClientIds = collect()
                ->merge($visitTraceClientIds)
                ->merge($visitLeads->pluck('client_id')->filter())
                ->merge($visitServices->pluck('client_id')->filter())
                ->unique();

            $visitClients = $clients->whereIn('id', $visitDerivedClientIds);

            // Correctly aggregate all relevant data, prioritizing Tasks/Services/Leads but capturing all.
            $allClientDetails = collect();
            $visitClients->each(function ($c) use ($allClientDetails) {
                $detail = "Client: $c->name";
                if ($c->phone_number) $detail .= " (Ph: $c->phone_number)";
                if ($c->email) $detail .= " (Email: $c->email)";
                if ($c->address) $detail .= " (Addr: $c->address)";
                $allClientDetails->push($detail);
            });
            $clientFullInfo = $allClientDetails->unique()->implode('; ') ?: 'N/A';

            $allClientNames = collect();
            $visitClients->each(fn($c) => $c->name ? $allClientNames->push($c->name) : null);
            $visitLeads->each(fn($l) => $l->name ? $allClientNames->push($l->name) : null);
            $visitServices->each(fn($s) => $s->name ? $allClientNames->push($s->name) : null);
            $clientNames = $allClientNames->filter()->unique()->implode(', ') ?: 'N/A';

            $allContacts = collect();
            $visitClients->each(fn($c) => $c->phone_number ? $allContacts->push($c->phone_number) : null);
            $visitLeads->each(fn($l) => $l->phone_number ? $allContacts->push($l->phone_number) : null);
            $visitServices->each(fn($s) => $s->contact_info ? $allContacts->push($s->contact_info) : null);
            $clientPhones = $allContacts->filter()->unique()->implode(', ') ?: 'N/A';

            // Additional Detailed Summary for Leads/Services
            $detailedLeadServiceInfo = collect();
            $visitLeads->each(function ($l) use ($detailedLeadServiceInfo) {
                $productName = $l->product ? $l->product->name : 'N/A';
                $source = $l->leadSource ? $l->leadSource->name : 'N/A';
                $detailedLeadServiceInfo->push("[Lead] Name: $l->name | Phone: $l->phone_number | Product: $productName | Source: $source | Status: $l->status | Remarks: $l->remarks");
            });
            $visitServices->each(function ($s) use ($detailedLeadServiceInfo) {
                $productName = $s->product ? $s->product->name : 'N/A';
                $detailedLeadServiceInfo->push("[Service] Name: $s->name | Contact: $s->contact_info | Product: $productName | S/N: $s->machine_serial_number | Status: $s->call_status | Remarks: $s->call_remarks");
            });
            $leadServiceSummary = $detailedLeadServiceInfo->unique()->implode("\n") ?: 'No detailed Lead/Service info.';

            $vehicleTypes = $traces->pluck('vehicle_type')->filter()->unique()->map(fn($v) => ucfirst($v))->implode(', ') ?: 'N/A';
            $allRemarks = $traces->whereNotNull('remarks')->pluck('remarks')->unique()->implode('; ') ?: 'N/A';

            $haltPoints = $this->findHaltPoints($traces, $tasks, $clients);
            $pointDetails = $haltPoints->map(function ($hp, $index) {
                $i = $index + 1;
                $details = "Point #$i: at {$hp['start_time']} - {$hp['location_info']}";
                if ($hp['active_tasks']) {
                    $details .= " | Tasks: {$hp['active_tasks']}";
                }
                if ($hp['remarks']) {
                    $details .= " | Remarks: {$hp['remarks']}";
                }
                if ($hp['images']->isNotEmpty()) {
                    $imageUrls = $hp['images']->map(fn($img) => url($img))->implode(', ');
                    $details .= " | Images: $imageUrls";
                }
                return $details;
            })->implode("\n") ?: 'None';

            // Filter relevant logs from the pre-fetched collection by employee_id
            $visitTaskLogs = $allTaskLogs->where('employee_id', $employee?->id)
                ->filter(function ($log) use ($firstTrace, $lastTrace) {
                    $logStart = Carbon::parse($log->start_time);
                    $logEnd = $log->end_time ? Carbon::parse($log->end_time) : null;
                    $visitStart = Carbon::parse($firstTrace->created_at);
                    $visitEnd = Carbon::parse($lastTrace->created_at);

                    return ($logStart->between($visitStart, $visitEnd)) ||
                        ($logEnd && $logEnd->between($visitStart, $visitEnd)) ||
                        ($logStart->lte($visitStart) && (!$logEnd || $logEnd->gte($visitEnd)));
                });

            $logDetails = $visitTaskLogs->map(function ($log) {
                return ($log->task ? $log->task->title : 'Task') . ' (' . Carbon::parse($log->start_time)->format('H:i') . ' - ' . ($log->end_time ? Carbon::parse($log->end_time)->format('H:i') : 'Active') . ')';
            })->implode('; ') ?: 'N/A';

            // Calculate Task Running Duration (Overlap of logs with visit time)
            $totalTaskSeconds = 0;
            $visitStart = Carbon::parse($firstTrace->created_at);
            $visitEnd = Carbon::parse($lastTrace->created_at);

            foreach ($visitTaskLogs as $log) {
                $logStart = Carbon::parse($log->start_time);
                $logEnd = $log->end_time ? Carbon::parse($log->end_time) : now();

                // Intersect the log interval with the visit interval
                $overlapStart = $logStart->gt($visitStart) ? $logStart : $visitStart;
                $overlapEnd = $logEnd->lt($visitEnd) ? $logEnd : $visitEnd;

                if ($overlapStart->lt($overlapEnd)) {
                    $totalTaskSeconds += $overlapStart->diffInSeconds($overlapEnd);
                }
            }
            $taskDuration = gmdate('H:i:s', $totalTaskSeconds);

            $startedTime = Carbon::parse($firstTrace->created_at);
            $endedTime = Carbon::parse($lastTrace->created_at);
            $timeSpent = gmdate('H:i:s', $endedTime->diffInSeconds($startedTime));

            // Aggregate location from all tasks and clients
            $allLocations = collect();
            $visitTasks->each(fn($t) => $t->location ? $allLocations->push($t->location) : null);
            $visitLeads->each(fn($l) => $l->location ? $allLocations->push($l->location) : null);
            $visitServices->each(fn($s) => $s->requested_location ? $allLocations->push($s->requested_location) : null);
            $visitClients->each(fn($c) => $c->address ? $allLocations->push($c->address) : null);
            $location = $allLocations->filter()->unique()->implode('; ') ?: 'N/A';

            $allStatus = $visitTasks->pluck('status');
            $visitLeads->each(fn($l) => $l->status ? $allStatus->push($l->status) : null);
            $visitServices->each(fn($s) => $s->call_status ? $allStatus->push($s->call_status) : null);
            $statusStr = $allStatus->filter()->unique()->implode(', ') ?: 'N/A';

            $finalRemarks = $visitTasks->pluck('description');
            $visitLeads->each(fn($l) => $l->remarks ? $finalRemarks->push($l->remarks) : null);
            $visitServices->each(fn($s) => $s->call_remarks ? $finalRemarks->push($s->call_remarks) : null);
            $remarksStr = $finalRemarks->filter()->unique()->implode('; ') ?: 'N/A';

            $processedData->push([
                'user_name' => $user->name ?? 'N/A',
                'employee_code' => $employee->employee_id ?? 'N/A',
                'designation' => $employee->designation ?? 'N/A',
                'department' => $employee->department->name ?? 'N/A',
                'dealership' => $employee->dealership->name ?? 'N/A',
                'manager' => $managers[$employee->reporting_to] ?? 'N/A',
                'email' => $user->email ?? 'N/A',
                'phone' => $user->phone ?? 'N/A',
                'task_type' => $taskTitles,
                'vehicle_type' => $vehicleTypes,
                'point_count' => $haltPoints->count(),
                'point_info' => $pointDetails,
                'halt_points' => $haltPoints,
                'visit_remarks' => "Tasks: $logDetails. Visit Remarks: $allRemarks",
                'date' => $startedTime->setTimezone('Asia/Kolkata')->format('d-m-Y'),
                'started_time' => $startedTime->setTimezone('Asia/Kolkata')->format('H:i:s'),
                'ended_time' => $endedTime->setTimezone('Asia/Kolkata')->format('H:i:s'),
                'time_spent' => $timeSpent,
                'task_duration' => $taskDuration,
                'kms_travelled' => round($totalDistance / 1000, 3),
                'travel_expense' => $this->calculateVisitTravelExpense($visitTraces),
                'call_ta' => $totalCallTa,
                'client_name' => $clientNames,
                'client_full_info' => $clientFullInfo,
                'lead_service_summary' => $leadServiceSummary,
                'contact' => $clientPhones,
                'location' => $location,
                'status' => $statusStr,
                'remarks' => $remarksStr,
            ]);
        }

        $primaryLogoPath = public_path('admin/assets/images/logo/korps-sync-crm-logo-white.png');
        $secondaryLogoPath = public_path('admin/assets/images/logo/svhe.png');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('timeline.pdf', [
            'data' => $processedData,
            'primaryLogoPath' => $primaryLogoPath,
            'secondaryLogoPath' => $secondaryLogoPath
        ])->setPaper('a4', 'landscape');
        return $pdf->download('visits_report.pdf');
    }

    private function calculateVisitTravelExpense($visitTraces): float
    {
        if ($visitTraces->count() < 2) {
            return 0.0;
        }

        $total = 0.0;
        $dayTotals = [];

        for ($i = 0; $i < $visitTraces->count() - 1; $i++) {
            $point1 = $visitTraces[$i];
            $point2 = $visitTraces[$i + 1];

            if (empty($point1->latitude) || empty($point1->longitude) || empty($point2->latitude) || empty($point2->longitude)) {
                continue;
            }

            $distanceMeters = calculateDistance(
                $point1->latitude,
                $point1->longitude,
                $point2->latitude,
                $point2->longitude
            );

            $distanceKm = $distanceMeters / 1000;
            if ($distanceKm <= 0) {
                continue;
            }

            $at = $point1->created_at instanceof Carbon ? $point1->created_at : Carbon::parse($point1->created_at);
            $rate = $this->getTravelRateForVehicleAt($point1->vehicle_type, $at);
            if ($rate <= 0) {
                continue;
            }

            $segmentAmount = $distanceKm * $rate;

            $dayKey = $at->toDateString();
            $currentDayTotal = $dayTotals[$dayKey] ?? 0.0;
            $maxDaily = $this->getHistoricalSettingValue('travel_allowance_max_daily', $at, 0.0);

            if ($maxDaily > 0) {
                $allowedRemaining = max(0.0, $maxDaily - $currentDayTotal);
                $segmentAmount = min($segmentAmount, $allowedRemaining);
            }

            $dayTotals[$dayKey] = $currentDayTotal + $segmentAmount;
            $total += $segmentAmount;
        }

        return round($total, 2);
    }

    private function getTravelRateForVehicleAt($vehicleType, Carbon $at): float
    {
        $vehicleType = strtolower((string) ($vehicleType ?: 'other'));

        if ($vehicleType === 'idle') {
            return 0.0;
        }

        $legacyMap = [
            'two_wheeler' => 'bike',
            'four_wheeler' => 'car',
        ];
        if (isset($legacyMap[$vehicleType])) {
            $vehicleType = $legacyMap[$vehicleType];
        }

        $vehicleRateKeys = [
            'walk' => 'travel_allowance_walk',
            'bike' => 'travel_allowance_bike',
            'car' => 'travel_allowance_car',
            'bus' => 'travel_allowance_bus',
            'train' => 'travel_allowance_train',
            'other' => 'travel_allowance_other',
        ];

        $defaultRate = $this->getHistoricalSettingValue(
            'travel_allowance_other',
            $at,
            $this->getHistoricalSettingValue('travel_allowance_rate', $at, 0.0)
        );
        $rateKey = $vehicleRateKeys[$vehicleType] ?? 'travel_allowance_other';

        return $this->getHistoricalSettingValue($rateKey, $at, $defaultRate);
    }

    private $hasHistoryTable = null;

    private function getHistoricalSettingValue(string $key, Carbon $asOf, float $fallback = 0.0): float
    {
        // Cache settings to avoid repeated queries in the loop
        if (!isset($this->settingsCache[$key])) {
            $this->settingsCache[$key] = DB::table('settings')->where('key', $key)->value('value');
        }

        $setting = $this->settingsCache[$key];
        $value = is_null($setting) ? $fallback : (float) $setting;

        if ($this->hasHistoryTable === null) {
            $this->hasHistoryTable = Schema::hasTable('travel_allowance_histories');
        }

        if (!$this->hasHistoryTable) {
            return (float) $value;
        }

        // Optimization: If the record is recent (e.g. from today), skips history check
        if ($asOf->isToday()) {
            return (float) $value;
        }

        $historyCacheKey = $key . '_' . $asOf->toDateString(); // Use date string for cache key efficiency
        if (isset($this->historyCache[$historyCacheKey])) {
            return $this->historyCache[$historyCacheKey];
        }

        $changesAfter = DB::table('travel_allowance_histories')
            ->where('setting_key', $key)
            ->where('created_at', '>', $asOf)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get(['old_value']);

        foreach ($changesAfter as $change) {
            $value = (float) ($change->old_value ?? 0);
        }

        $this->historyCache[$historyCacheKey] = (float) $value;
        return (float) $value;
    }

    private function findHaltPoints($traces, $tasks = null, $clients = null)
    {
        if ($traces->count() < 2) return collect();

        $haltPoints = collect();
        $minHaltDuration = 300; // 5 minutes in seconds
        $maxHaltRadius = 100; // meters

        $currentHalt = null;

        foreach ($traces as $trace) {
            if (!$currentHalt) {
                $currentHalt = [
                    'lat' => $trace->latitude,
                    'lng' => $trace->longitude,
                    'start_time' => Carbon::parse($trace->created_at),
                    'last_time' => Carbon::parse($trace->created_at),
                    'halt_traces' => collect([$trace])
                ];
                continue;
            }

            $dist = calculateDistance($currentHalt['lat'], $currentHalt['lng'], $trace->latitude, $trace->longitude);

            if ($dist <= $maxHaltRadius) {
                $currentHalt['last_time'] = Carbon::parse($trace->created_at);
                $currentHalt['halt_traces']->push($trace);
            } else {
                $durationSeconds = $currentHalt['last_time']->diffInSeconds($currentHalt['start_time']);
                if ($durationSeconds >= $minHaltDuration) {
                    $haltPoints->push($this->formatHaltPoint($currentHalt, $durationSeconds, $tasks, $clients));
                }

                $currentHalt = [
                    'lat' => $trace->latitude,
                    'lng' => $trace->longitude,
                    'start_time' => Carbon::parse($trace->created_at),
                    'last_time' => Carbon::parse($trace->created_at),
                    'halt_traces' => collect([$trace])
                ];
            }
        }

        if ($currentHalt) {
            $durationSeconds = $currentHalt['last_time']->diffInSeconds($currentHalt['start_time']);
            if ($durationSeconds >= $minHaltDuration) {
                $haltPoints->push($this->formatHaltPoint($currentHalt, $durationSeconds, $tasks, $clients));
            }
        }

        return $haltPoints;
    }

    private function formatHaltPoint($halt, $durationSeconds, $tasks, $clients)
    {
        $durationFormatted = gmdate('H:i:s', $durationSeconds);
        $lat = $halt['lat'];
        $lng = $halt['lng'];
        $halt_traces = $halt['halt_traces'];

        $locationInfo = "[$lat, $lng]";
        $remarks = $halt_traces->whereNotNull('remarks')->pluck('remarks')->unique()->implode('; ');
        $images = $halt_traces->whereNotNull('image_path')->pluck('image_path')->unique();

        // Identify tasks active during this specific halt
        $haltTaskIds = $halt_traces->whereNotNull('task_id')->pluck('task_id')->unique();
        $haltTaskTitles = '';
        if ($tasks && $haltTaskIds->isNotEmpty()) {
            $haltTaskTitles = $tasks->whereIn('id', $haltTaskIds)->map(function ($t) {
                $category = $t->is_service ? '[Service]' : ($t->lead_id ? '[Lead]' : '[General]');
                return "$category {$t->title}";
            })->implode(', ');
        }

        return [
            'lat' => $lat,
            'lng' => $lng,
            'duration' => $durationFormatted,
            'start_time' => $halt['start_time']->setTimezone('Asia/Kolkata')->format('H:i'),
            'end_time' => $halt['last_time']->setTimezone('Asia/Kolkata')->format('H:i'),
            'location_info' => $locationInfo,
            'remarks' => $remarks,
            'images' => $images,
            'active_tasks' => $haltTaskTitles
        ];
    }

    private function snapTracesToRoads($traces)
    {
        if ($traces->count() < 2) {
            return $traces;
        }

        // Use visit_id for caching if available
        $visitId = $traces->first()->visit_id ?? null;
        $cacheKey = $visitId ? "snapped_traces_{$visitId}" : null;

        if ($cacheKey && Cache::store('file')->has($cacheKey)) {
            $cached = Cache::store('file')->get($cacheKey);
            return collect($cached)->map(function ($item) {
                return (object)$item;
            });
        }

        $apiKey = env('GOOGLE_MAPS_API_KEY');
        if (empty($apiKey)) {
            return $traces;
        }

        // Filter valid points
        $rawTraces = $traces->filter(function ($t) {
            return !empty($t->latitude) && !empty($t->longitude) && (float) $t->latitude !== 0.0;
        })->values();

        if ($rawTraces->count() < 2) {
            return $traces;
        }

        // Thin out points that are too close together
        $validTraces = collect([$rawTraces->first()]);
        $minMoveThreshold = 3; // meters default

        // If we have a massive amount of traces, increase the threshold to avoid excessive API calls
        if ($rawTraces->count() > 5000) {
            $minMoveThreshold = 10;
        } elseif ($rawTraces->count() > 2000) {
            $minMoveThreshold = 5;
        }
        for ($i = 1; $i < $rawTraces->count(); $i++) {
            $p1 = $validTraces->last();
            $p2 = $rawTraces[$i];
            $d = calculateDistance($p1->latitude, $p1->longitude, $p2->latitude, $p2->longitude);
            if ($d > $minMoveThreshold) {
                $validTraces->push($p2);
            }
        }

        if ($validTraces->count() < 2) {
            return $traces;
        }

        // Densify points if they are too far apart (similar to frontend logic)
        $densified = collect();
        $thresholdMeters = 100; // Aligned with frontend

        for ($i = 0; $i < $validTraces->count() - 1; $i++) {
            $p1 = $validTraces[$i];
            $p2 = $validTraces[$i + 1];
            $densified->push($p1);

            $dist = calculateDistance($p1->latitude, $p1->longitude, $p2->latitude, $p2->longitude);
            if ($dist > $thresholdMeters) {
                $pointsToInject = min(floor($dist / $thresholdMeters), 50);
                for ($j = 1; $j <= $pointsToInject; $j++) {
                    $ratio = $j / ($pointsToInject + 1);
                    $interpLat = $p1->latitude + ($p2->latitude - $p1->latitude) * $ratio;
                    $interpLng = $p1->longitude + ($p2->longitude - $p1->longitude) * $ratio;

                    $densified->push((object)[
                        'latitude' => $interpLat,
                        'longitude' => $interpLng,
                        'created_at' => $p1->created_at,
                        'task_id' => $p1->task_id,
                        'client_id' => $p1->client_id,
                        'vehicle_type' => $p1->vehicle_type,
                        'remarks' => $p1->remarks,
                        'visit_id' => $visitId
                    ]);
                }
            }
        }
        $densified->push($validTraces->last());

        $allSnapped = collect();
        $batchSize = 100;

        for ($i = 0; $i < $densified->count(); $i += ($batchSize - 1)) {
            $batch = $densified->slice($i, $batchSize);
            if ($batch->count() < 2) break;

            $pathStr = $batch->map(function ($t) {
                return $t->latitude . ',' . $t->longitude;
            })->implode('|');

            try {
                $response = Http::withOptions([
                    'verify' => false,
                ])->get("https://roads.googleapis.com/v1/snapToRoads", [
                    'path' => $pathStr,
                    'interpolate' => 'true',
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $snappedPoints = $response->json()['snappedPoints'] ?? [];
                    foreach ($snappedPoints as $sp) {
                        $allSnapped->push((object)[
                            'latitude' => $sp['location']['latitude'],
                            'longitude' => $sp['location']['longitude'],
                            'created_at' => $batch->first()->created_at ?? null,
                            'vehicle_type' => $batch->first()->vehicle_type ?? 'other',
                            'visit_id' => $visitId
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Roads API Backend Exception: " . $e->getMessage());
            }

            if ($i + $batchSize >= $densified->count()) break;
        }

        $result = $allSnapped->count() >= 2 ? $allSnapped : $traces;

        if ($cacheKey && $allSnapped->count() >= 2) {
            // Cache for 30 days since historical visit paths don't change
            Cache::store('file')->put($cacheKey, $result->toArray(), now()->addDays(30));
        }

        return $result;
    }
}
