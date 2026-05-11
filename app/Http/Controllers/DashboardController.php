<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Client;
use App\Models\Call;
use App\Models\Employee;
use App\Models\User;
use App\Models\Followup;
use App\Models\Task;
use App\Models\Log;
use App\Models\Agent;
use App\Models\Part; // Added Part model
use App\Models\PackageKit; // Added PackageKit model
use App\Models\ProductModel; // Added ProductModel model for totalModelSeriesWithParts
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Added DB facade
use Illuminate\Support\Facades\Session; // Added Session facade
use App\Models\Dealership; // Add this line
use App\Models\Service; // Added Service model
use App\Models\Product; // Added Product model
use App\Models\ModelSeries; // Added ModelSeries model
use App\Models\Clock; // Added Clock model

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userDealershipId = $this->getFilteredDealershipId($request);
        $userDepartment = $user->employee->department->name ?? null;

        $totalLeads = Lead::when($userDealershipId, function ($query) use ($userDealershipId) {
            return $query->where('dealership_id', $userDealershipId);
        })->count();

        $totalAgents = Agent::when($userDealershipId, function ($query) use ($userDealershipId) {
            return $query->where('dealership_id', $userDealershipId);
        })->count();

        $totalEmployees = Employee::when($userDealershipId, function ($query) use ($userDealershipId) {
            return $query->where('dealership_id', $userDealershipId);
        })->count();

        $totalClients = Client::when($userDealershipId, function ($query) use ($userDealershipId) {
            return $query->where('dealership_id', $userDealershipId);
        })->count();

        // Service Statistics
        $serviceBaseQuery = Service::query();

        if ($userDealershipId) {
            $serviceBaseQuery->where('dealership_id', $userDealershipId);
        }

        $totalServices = $serviceBaseQuery->count();
        $totalServiceEngineers = Employee::whereHas('tasks')->count();
        $totalClientsOnServices = (clone $serviceBaseQuery)->distinct()->count('client_id');
        $totalProductsOnServices = (clone $serviceBaseQuery)->distinct()->count('product_id');

        if ($userDealershipId) {
            $totalServiceEngineers = Employee::where('dealership_id', $userDealershipId)->whereHas('tasks')->count();
        }

        // Parts Statistics
        $partsBaseQuery = Part::query();
        $packageKitsBaseQuery = PackageKit::query();

        if ($userDealershipId) {
            $partsBaseQuery->where('dealership_id', $userDealershipId);
            $packageKitsBaseQuery->where('dealership_id', $userDealershipId);
        }

        $stockStatusCounts = [
            'Out of Stock' => (clone $partsBaseQuery)->where('stock_quantity', 0)->count(),
            'Low Stock' => (clone $partsBaseQuery)->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count(),
            'In Stock' => (clone $partsBaseQuery)->where('stock_quantity', '>', 10)->count(),
        ];

        $totalParts = $partsBaseQuery->count();
        $totalPackageKits = $packageKitsBaseQuery->count();

        $totalProductsWithParts = Product::whereHas('parts', function ($query) use ($userDealershipId) {
            if ($userDealershipId) {
                $query->where('dealership_id', $userDealershipId);
            }
        })->count();

        $totalModelSeriesWithParts = ModelSeries::whereHas('parts', function ($query) use ($userDealershipId) {
            if ($userDealershipId) {
                $query->where('dealership_id', $userDealershipId);
            }
        })->count();

        // Parts Analytics
        $topSellingParts = (clone $partsBaseQuery)->withCount('packageKits')
            ->orderByDesc('package_kits_count')
            ->limit(5)
            ->get();

        // Call Center Specific Metrics
        $today = Carbon::today();
        
        $leadsToday = Lead::when($userDealershipId, function ($query) use ($userDealershipId) {
            return $query->where('dealership_id', $userDealershipId);
        })->whereDate('created_at', $today)->count();

        $convertedToday = Lead::when($userDealershipId, function ($query) use ($userDealershipId) {
            return $query->where('dealership_id', $userDealershipId);
        })->where('status', 'Converted') // Adjust status name if different
          ->whereDate('updated_at', $today)->count();

        $callsToday = Call::whereDate('created_at', $today)->count();
        $inboundCallsToday = Call::where('direction', 'inbound')->whereDate('created_at', $today)->count();
        $outboundCallsToday = Call::where('direction', 'outbound')->whereDate('created_at', $today)->count();
        $missedCallsToday = Call::whereIn('status', ['no-answer', 'failed', 'busy'])->whereDate('created_at', $today)->count();
        
        $followupsDueToday = Followup::whereDate('next_follow_up_date', $today)->count();

        $avgCallDuration = Call::whereDate('created_at', $today)
            ->whereNotNull('end_time')
            ->whereNotNull('start_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, start_time, end_time)) as avg_duration')
            ->first()
            ->avg_duration ?? 0;
        
        $avgCallDuration = round($avgCallDuration / 60, 1); // convert to minutes

        $totalCallDuration = Call::whereDate('created_at', $today)
            ->whereNotNull('end_time')
            ->whereNotNull('start_time')
            ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_duration')
            ->first()
            ->total_duration ?? 0;
        
        $totalCallDuration = round($totalCallDuration / 60, 1); // convert to minutes

        $agentPerformance = Agent::with(['employee'])
            ->when($userDealershipId, function ($query) use ($userDealershipId) {
                return $query->where('dealership_id', $userDealershipId);
            })
            ->get()
            ->map(function ($agent) use ($today) {
                $calls = Call::where('caller_user_id', $agent->user_id)
                    ->orWhere('receiver_user_id', $agent->user_id)
                    ->whereDate('created_at', $today)
                    ->count();
                
                $conversions = Lead::where('agent_id', $agent->id)
                    ->where('status', 'Converted')
                    ->whereDate('updated_at', $today)
                    ->count();

                $session = \App\Models\AgentSession::where('employee_id', $agent->employee_id)->first();

                return [
                    'name' => $agent->name,
                    'status' => $session->status ?? 'offline',
                    'calls' => $calls,
                    'conversions' => $conversions
                ];
            });

        $activeAgentsCount = \App\Models\AgentSession::where('status', 'available')->count();


        $topPackageKits = (clone $packageKitsBaseQuery)->withCount('parts')
            ->orderByDesc('parts_count')
            ->limit(5)
            ->get();



        $partsSalesQuery = (clone $partsBaseQuery)->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);

        $dailyPartsSales = $partsSalesQuery->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $daysInMonth = Carbon::now()->daysInMonth;
        $partsSalesData = [];
        $partsSalesLabels = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::now()->day($i)->format('Y-m-d');
            $partsSalesLabels[] = Carbon::parse($date)->format('j M');
            $partsSalesData[] = $dailyPartsSales->has($date) ? $dailyPartsSales[$date]->count : 0;
        }

        // General Analytics for All Users
        $employeeId = $user->employee ? $user->employee->id : null;
        $myTotalTasks = 0;
        $myPendingTasks = 0;
        $myCompletedTasks = 0;
        $myAttendanceCount = 0;

        if ($employeeId) {
            $myTotalTasks = Task::where('assigned_to', $employeeId)->count();
            $myPendingTasks = Task::where('assigned_to', $employeeId)->where('status', '!=', 'completed')->count();
            $myCompletedTasks = Task::where('assigned_to', $employeeId)->where('status', 'completed')->count();

            $myAttendanceCount = Clock::where('employee_id', $employeeId)
                ->whereMonth('clock_in_time', Carbon::now()->month)
                ->whereYear('clock_in_time', Carbon::now()->year)
                ->count();

            // My Task Charts
            $myTaskStatusCounts = Task::where('assigned_to', $employeeId)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $myWeeklyAttendance = [];
            $myWeeklyAttendanceLabels = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                $myWeeklyAttendanceLabels[] = $date->format('D');

                $clock = Clock::where('employee_id', $employeeId)
                    ->whereDate('clock_in_time', $dateStr)
                    ->first();

                $hours = 0;
                if ($clock && $clock->clock_out_time) {
                    $start = Carbon::parse($clock->clock_in_time);
                    $end = Carbon::parse($clock->clock_out_time);
                    $hours = $end->diffInMinutes($start) / 60;
                } elseif ($clock && !$clock->clock_out_time) {
                    $start = Carbon::parse($clock->clock_in_time);
                    $hours = Carbon::now()->diffInMinutes($start) / 60;
                }
                $myWeeklyAttendance[] = round($hours, 1);
            }

            $myMonthlyCompletionData = [];
            $myMonthlyCompletionLabels = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $month = $date->month;
                $year = $date->year;
                $myMonthlyCompletionLabels[] = $date->format('M');

                $count = Task::where('assigned_to', $employeeId)
                    ->where('status', 'completed')
                    ->whereMonth('updated_at', $month)
                    ->whereYear('updated_at', $year)
                    ->count();
                $myMonthlyCompletionData[] = $count;
            }

            $myTaskTypeCounts = Task::where('assigned_to', $employeeId)
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();
        } else {
            $myTaskStatusCounts = [];
            $myWeeklyAttendance = [];
            $myWeeklyAttendanceLabels = [];
            $myMonthlyCompletionData = [];
            $myMonthlyCompletionLabels = [];
            $myTaskTypeCounts = [];
        }

        return view('dashboard', compact(
            'userDealershipId', 'totalLeads', 'totalAgents', 'totalEmployees', 'totalClients', 'userDepartment', 
            'totalServices', 'totalServiceEngineers', 'totalClientsOnServices', 'totalProductsOnServices', 
            'totalParts', 'totalPackageKits', 'totalProductsWithParts', 'totalModelSeriesWithParts', 
            'topSellingParts', 'topPackageKits', 'partsSalesData', 'partsSalesLabels', 'stockStatusCounts', 
            'myTotalTasks', 'myPendingTasks', 'myCompletedTasks', 'myAttendanceCount', 'myTaskStatusCounts', 
            'myWeeklyAttendance', 'myWeeklyAttendanceLabels', 'myMonthlyCompletionData', 'myMonthlyCompletionLabels', 'myTaskTypeCounts',
            'leadsToday', 'convertedToday', 'callsToday', 'inboundCallsToday', 'outboundCallsToday', 'agentPerformance', 'activeAgentsCount',
            'missedCallsToday', 'followupsDueToday', 'avgCallDuration', 'totalCallDuration'
        ));
    }

    public function topContributors(Request $request)
    {
        $category = $request->input('category', 'sales');
        $dateRange = $request->input('date_range', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Employee::query();

        if ($category === 'sales') {
            $query->withCount(['leads' => function ($q) use ($dateRange, $startDate, $endDate) {
                $q->where('status', 'win');
                $this->applyDateRange($q, $dateRange, $startDate, $endDate);
            }])->orderBy('leads_count', 'desc');
        } elseif ($category === 'service') {
            $query->withCount(['tasks' => function ($q) use ($dateRange, $startDate, $endDate) {
                $q->where('is_service', 1)->where('status', 'completed');
                $this->applyDateRange($q, $dateRange, $startDate, $endDate);
            }])->orderBy('tasks_count', 'desc');
        } elseif ($category === 'parts') {
            // Logic for parts will be added here
        }

        $contributors = $query->limit(5)->get();

        return view('partials.top_contributors', compact('contributors', 'category'));
    }

    private function applyDateRange($query, $dateRange, $startDate, $endDate)
    {
        switch ($dateRange) {
            case 'this_week':
                $query->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'last_week':
                $query->whereBetween('updated_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                break;
            case 'last_month':
                $query->whereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('updated_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
                }
                break;
            case 'this_month':
            default:
                $query->whereMonth('updated_at', Carbon::now()->month);
                break;
        }
    }

    public function getLeadStatistics(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $baseQuery = Lead::query();
        if ($dealershipId) {
            $baseQuery->where('dealership_id', $dealershipId);
        }

        $totalLeads = (clone $baseQuery)->count();
        $convertedLeads = (clone $baseQuery)->where('status', 'converted')->count();
        $lostLeads = (clone $baseQuery)->where('status', 'lost')->count();
        $inProgressLeads = (clone $baseQuery)->where('status', 'in_progress')->count();

        return response()->json([
            'totalLeads' => $totalLeads,
            'convertedLeads' => $convertedLeads,
            'lostLeads' => $lostLeads,
            'inProgressLeads' => $inProgressLeads,
        ]);
    }

    public function getLeadSourceBreakdown(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $leadSources = Lead::select('lead_source_id')
            ->selectRaw('count(*) as count')
            ->when($dealershipId, function ($query) use ($dealershipId) {
                return $query->where('dealership_id', $dealershipId);
            })
            ->groupBy('lead_source_id')
            ->with('leadSource')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $data = $leadSources->map(function ($item) {
            return [
                'name' => $item->leadSource ? $item->leadSource->name : 'Unknown',
                'y' => $item->count,
            ];
        });

        return response()->json($data);
    }

    public function getEmployeeLeadPerformance(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $employeePerformance = Employee::withCount(['leads', 'clients'])
            ->has('user') // Only employees with an associated user
            ->when($dealershipId, function ($query) use ($dealershipId) {
                return $query->where('dealership_id', $dealershipId);
            })
            ->get()
            ->map(function ($employee) {
                return [
                    'employee_name' => $employee->user->name,
                    'total_leads' => $employee->leads_count,
                    'converted_leads' => $employee->leads()->where('status', 'converted')->count(),
                    'total_clients' => $employee->clients_count,
                ];
            });

        return response()->json($employeePerformance);
    }

    public function getTopClients(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $topClientsQuery = Client::query();

        if ($dealershipId) {
            $topClientsQuery->where('dealership_id', $dealershipId);
        }

        $topClients = $topClientsQuery->withCount(['leads' => function ($query) {
            $query->where('status', 'win');
        }])
            ->with('state', 'district')
            ->orderBy('leads_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json($topClients);
    }

    public function getRecentActivities(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $recentActivities = Log::with('user')
            ->when($dealershipId, function ($query) use ($dealershipId) {
                return $query->whereHas('user.employee', function ($q) use ($dealershipId) {
                    $q->where('dealership_id', $dealershipId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($recentActivities);
    }

    public function getUpcomingEvents(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $upcomingFollowups = Followup::when($dealershipId, function ($query) use ($dealershipId) {
            return $query->whereHas('lead', function ($q) use ($dealershipId) {
                $q->where('dealership_id', $dealershipId);
            });
        })
            ->where('next_follow_up_date', '>=', Carbon::today())
            ->orderBy('next_follow_up_date')
            ->limit(10)
            ->get();

        $upcomingTasks = Task::when($dealershipId, function ($query) use ($dealershipId) {
            return $query->where('dealership_id', $dealershipId);
        })
            ->where('due_date', '>=', Carbon::today())
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return response()->json([
            'followups' => $upcomingFollowups,
            'tasks' => $upcomingTasks,
        ]);
    }

    public function getTopAgents(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $topAgentsQuery = Agent::query();

        if ($dealershipId) {
            $topAgentsQuery->whereHas('leads', function ($query) use ($dealershipId) {
                $query->where('dealership_id', $dealershipId);
            });
        }

        $topAgents = $topAgentsQuery->withCount(['leads' => function ($query) use ($dealershipId) {
            $query->where('status', 'win');
            if ($dealershipId) {
                $query->where('dealership_id', $dealershipId);
            }
        }])
            ->orderBy('leads_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json($topAgents);
    }

    public function getAllLeads(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $allLeads = Lead::with(['client', 'employee.user', 'leadSource'])
            ->when($dealershipId, function ($query) use ($dealershipId) {
                return $query->where('dealership_id', $dealershipId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($allLeads);
    }

    public function getSalesStatistics(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $query = Lead::where('status', 'win');

        if ($dealershipId) {
            $query->where('dealership_id', $dealershipId);
        }

        $filter = $request->input('filter', 'this_month');

        switch ($filter) {
            case 'this_week':
                $query->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'last_week':
                $query->whereBetween('updated_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                break;
            case 'last_month':
                $query->whereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;
            case 'custom':
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                if ($startDate && $endDate) {
                    $query->whereBetween('updated_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
                }
                break;
            case 'this_month':
            default:
                $query->whereMonth('updated_at', Carbon::now()->month);
                break;
        }

        $sales = $query->count();

        return response()->json(['sales' => $sales]);
    }

    public function getTopProductsOnServices(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $productsQuery = \App\Models\Service::query();

        if ($dealershipId) {
            $productsQuery->where('dealership_id', $dealershipId);
        }

        $topProducts = $productsQuery->whereNotNull('product_id') // Filter out services with no product
            ->select('product_id')
            ->selectRaw('count(*) as service_count')
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('service_count')
            ->limit(5)
            ->get();

        return response()->json($topProducts->map(function ($item) {
            return [
                'name' => $item->product ? $item->product->name : 'Unknown Product',
                'count' => $item->service_count,
            ];
        }));
    }

    public function getTopServiceEngineers(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $engineersQuery = Employee::query();

        if ($dealershipId) {
            $engineersQuery->where('dealership_id', $dealershipId);
        }

        $topEngineers = $engineersQuery->withCount(['tasks' => function ($query) use ($dealershipId) {
            if ($dealershipId) {
                $query->where('dealership_id', $dealershipId);
            }
        }])
            ->has('user') // Only employees with an associated user
            ->orderByDesc('tasks_count')
            ->limit(5)
            ->get();

        return response()->json($topEngineers->map(function ($item) {
            return [
                'name' => $item->user ? $item->user->name : 'Unknown Engineer',
                'count' => $item->tasks_count,
            ];
        }));
    }

    public function getTopClientsOnServices(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $clientsQuery = \App\Models\Service::query();

        if ($dealershipId) {
            $clientsQuery->where('dealership_id', $dealershipId);
        }

        $topClients = $clientsQuery->whereNotNull('client_id') // Filter out services with no client
            ->select('client_id')
            ->selectRaw('count(*) as service_count')
            ->groupBy('client_id')
            ->with('client')
            ->orderByDesc('service_count')
            ->limit(5)
            ->get();

        return response()->json($topClients->map(function ($item) {
            return [
                'name' => $item->client ? $item->client->name : 'Unknown Client',
                'count' => $item->service_count,
            ];
        }));
    }

    public function getThisMonthsServices(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $filter = $request->input('filter', 'today');

        $servicesQuery = \App\Models\Service::query();
        $revenueQuery = \App\Models\Service::query()
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->whereHas('tasks', function ($taskQuery) use ($request, $filter) {
                $taskQuery->where('is_service', 1)
                    ->whereHas('fsrReport', function ($fsrQuery) use ($request, $filter) {
                        $fsrQuery->where('payment_status', 'paid');
                        $this->applyChartDateFilter($fsrQuery, $filter, $request, 'updated_at');
                    });
            });

        $this->applyChartDateFilter($servicesQuery, $filter, $request, 'created_at');

        $serviceType = $request->input('service_type');
        if (!empty($serviceType)) {
            $servicesQuery->where('type_of_service', $serviceType);
            $revenueQuery->where('type_of_service', $serviceType);
        }

        if ($dealershipId) {
            $servicesQuery->where('dealership_id', $dealershipId);
            $revenueQuery->where('dealership_id', $dealershipId);
        }

        $dailyServices = $servicesQuery->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $servicesData = [];

        if ($dailyServices->isNotEmpty()) {
            $firstDate = Carbon::parse($dailyServices->keys()->min());
            $lastDate = Carbon::parse($dailyServices->keys()->max());

            if ($filter == 'this_month') {
                $firstDate = Carbon::now()->startOfMonth();
                $lastDate = Carbon::now()->endOfMonth();
            }

            $currentDate = $firstDate->copy();
            while ($currentDate <= $lastDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $labels[] = $currentDate->format('j M');
                $servicesData[] = $dailyServices->has($dateStr) ? $dailyServices[$dateStr]->count : 0;
                $currentDate->addDay();
            }
        } else {
            if ($filter == 'this_month') {
                $daysInMonth = Carbon::now()->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $date = Carbon::now()->day($i)->format('Y-m-d');
                    $labels[] = Carbon::parse($date)->format('j M');
                    $servicesData[] = 0;
                }
            }
        }

        $revenue = (float) $revenueQuery->sum('price');

        return response()->json([
            'labels' => $labels,
            'servicesData' => $servicesData,
            'revenue' => $revenue,
        ]);
    }

    public function getPartsAddedAnalytics(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $query = Part::query();

        $filter = $request->input('filter', 'this_month');
        $this->applyChartDateFilter($query, $filter, $request, 'created_at');

        if ($dealershipId) {
            $query->where('dealership_id', $dealershipId);
        }

        $dailyData = $query->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $data = [];

        // Similar label logic
        $firstDate = Carbon::now()->startOfMonth();
        $lastDate = Carbon::now()->endOfMonth();

        if ($filter != 'this_month' && $dailyData->isNotEmpty()) {
            $firstDate = Carbon::parse($dailyData->keys()->min());
            $lastDate = Carbon::parse($dailyData->keys()->max());
        } elseif ($filter == 'last_month') {
            $firstDate = Carbon::now()->subMonth()->startOfMonth();
            $lastDate = Carbon::now()->subMonth()->endOfMonth();
        }

        $currentDate = $firstDate->copy();
        // Limit loop to avoid infinite if something wrong, e.g. 365 days max
        $days = 0;

        // Safe check: if no data and custom range, empty chart
        if ($dailyData->isEmpty() && $filter != 'this_month' && $filter != 'last_month') {
            // return empty
        } else {
            while ($currentDate <= $lastDate && $days < 366) {
                $dateStr = $currentDate->format('Y-m-d');
                $labels[] = $currentDate->format('j M');
                $data[] = $dailyData->has($dateStr) ? $dailyData[$dateStr]->count : 0;
                $currentDate->addDay();
                $days++;
            }
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    private function applyChartDateFilter($query, $filter, $request, $column = 'created_at')
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        switch ($filter) {
            case 'today':
                $query->whereDate($column, Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'last_week':
                $query->whereBetween($column, [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                break;
            case 'last_month':
                $query->whereBetween($column, [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;
            case 'this_year':
                $query->whereYear($column, Carbon::now()->year);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween($column, [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                }
                break;
            case 'this_month':
            default:
                $query->whereMonth($column, Carbon::now()->month)->whereYear($column, Carbon::now()->year);
                break;
        }
    }

    public function getThisMonthsSales(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $salesQuery = Lead::where('status', 'win');

        $filter = $request->input('filter', 'this_month');
        $this->applyChartDateFilter($salesQuery, $filter, $request, 'updated_at');

        if ($dealershipId) {
            $salesQuery->where('dealership_id', $dealershipId);
        }

        $revenue = (float) (clone $salesQuery)->sum('lead_value');

        $dailySales = $salesQuery->selectRaw('DATE(updated_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Logic to generate labels based on range (filling gaps)
        // For simplicity/robustness, if standard range (month/week), we fill gaps. 
        // For custom/year, we might return just the data points or dynamic range.
        // Let's stick to returning data points found or constructing a range.

        // Constructing range dynamically
        $labels = [];
        $salesData = [];

        if ($dailySales->isNotEmpty()) {
            $firstDate = Carbon::parse($dailySales->keys()->min());
            $lastDate = Carbon::parse($dailySales->keys()->max());

            // If filter is specific, override range
            if ($filter == 'this_month') {
                $firstDate = Carbon::now()->startOfMonth();
                $lastDate = Carbon::now()->endOfMonth();
            } // Add other overrides if needed

            $currentDate = $firstDate->copy();
            while ($currentDate <= $lastDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $labels[] = $currentDate->format('j M');
                $salesData[] = $dailySales->has($dateStr) ? $dailySales[$dateStr]->count : 0;
                $currentDate->addDay();
            }
        } else {
            // Return empty or default for 'this_month' if no data
            if ($filter == 'this_month') {
                $daysInMonth = Carbon::now()->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $date = Carbon::now()->day($i)->format('Y-m-d');
                    $labels[] = Carbon::parse($date)->format('j M');
                    $salesData[] = 0;
                }
            }
        }


        return response()->json([
            'labels' => $labels,
            'salesData' => $salesData,
            'revenue' => $revenue,
        ]);
    }

    public function getServiceStatistics(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $serviceBaseQuery = Service::query();

        if ($dealershipId) {
            $serviceBaseQuery->where('dealership_id', $dealershipId);
        }

        $totalServices = $serviceBaseQuery->count();

        if ($dealershipId) {
            $totalServiceEngineers = Employee::where('dealership_id', $dealershipId)->whereHas('tasks')->count();
        } else {
            $totalServiceEngineers = Employee::whereHas('tasks')->count();
        }

        $totalClientsOnServices = (clone $serviceBaseQuery)->distinct()->count('client_id');
        $totalProductsOnServices = (clone $serviceBaseQuery)->distinct()->count('product_id');

        $totalRevenue = (clone $serviceBaseQuery)
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->whereHas('tasks', function ($taskQuery) {
                $taskQuery->where('is_service', 1)
                    ->whereHas('fsrReport', function ($fsrQuery) {
                        $fsrQuery->where('payment_status', 'paid');
                    });
            })->sum('price');

        return response()->json([
            'totalServices' => $totalServices,
            'totalServiceEngineers' => $totalServiceEngineers,
            'totalClientsOnServices' => $totalClientsOnServices,
            'totalProductsOnServices' => $totalProductsOnServices,
            'totalRevenue' => (float)$totalRevenue,
        ]);
    }

    public function getPartsStatistics(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $partsBaseQuery = Part::query();
        $packageKitsBaseQuery = PackageKit::query();

        if ($dealershipId) {
            $partsBaseQuery->where('dealership_id', $dealershipId);
            $packageKitsBaseQuery->where('dealership_id', $dealershipId);
        }

        $totalParts = $partsBaseQuery->count();
        $totalPackageKits = $packageKitsBaseQuery->count();

        $totalProductsWithParts = Product::whereHas('parts', function ($query) use ($dealershipId) {
            if ($dealershipId) {
                $query->where('dealership_id', $dealershipId);
            }
        })->count();

        $totalModelSeriesWithParts = ModelSeries::whereHas('parts', function ($query) use ($dealershipId) {
            if ($dealershipId) {
                $query->where('dealership_id', $dealershipId);
            }
        })->count();

        return response()->json([
            'totalParts' => $totalParts,
            'totalPackageKits' => $totalPackageKits,
            'totalProductsWithParts' => $totalProductsWithParts,
            'totalModelSeriesWithParts' => $totalModelSeriesWithParts,
        ]);
    }

    private function getFilteredDealershipId(Request $request)
    {
        $user = auth()->user();
        $userDealershipId = $user->employee->dealership_id ?? null;
        
        // If user is Super Admin or has specific permission, they can view all.
        // Otherwise, they're restricted to their own.
        $canViewAllDashboards = checkMenu(Session::get('role_id'), 1, 'read') || $user->user_type === 'admin';

        if ($canViewAllDashboards) {
            return $request->query('dealership_id');
        }
        
        // Forced to their assigned dealership. If they don't have one, they should be blocked or return null (if that's safe).
        // Returning -1 to ensure they don't see "General View" if they are restricted but don't have a dealership.
        return $userDealershipId ?: -1; 
    }
}
