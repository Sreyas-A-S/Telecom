<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Followup;
use App\Models\Task;
use App\Models\Log;
use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use OpenApi\Annotations as OA;

class DashboardAnalyticsApiController extends Controller
{
    /**
     * @OA\Get(
     *      path="/dashboard/lead-statistics",
     *      summary="Get lead statistics",
     *      tags={"Dashboard Analytics"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          description="Filter by dealership ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead statistics retrieved successfully."),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="totalLeads", type="integer", example=100),
     *                  @OA\Property(property="convertedLeads", type="integer", example=20),
     *                  @OA\Property(property="lostLeads", type="integer", example=10),
     *                  @OA\Property(property="inProgressLeads", type="integer", example=70)
     *              )
     *          )
     *      )
     * )
     */
    public function getLeadStatistics(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $baseQuery = Lead::query();
        if ($dealershipId && $dealershipId != -1) {
            $baseQuery->where('dealership_id', $dealershipId);
        }

        $totalLeads = (clone $baseQuery)->count();
        $convertedLeads = (clone $baseQuery)->where('status', 'converted')->count();
        $lostLeads = (clone $baseQuery)->where('status', 'lost')->count();
        $inProgressLeads = (clone $baseQuery)->where('status', 'in_progress')->count();

        return $this->sendResponse([
            'totalLeads' => $totalLeads,
            'convertedLeads' => $convertedLeads,
            'lostLeads' => $lostLeads,
            'inProgressLeads' => $inProgressLeads,
        ], 'Lead statistics retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/dashboard/lead-source-breakdown",
     *      summary="Get lead source breakdown",
     *      tags={"Dashboard Analytics"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          description="Filter by dealership ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead source breakdown retrieved successfully."),
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  @OA\Property(property="name", type="string", example="Website"),
     *                  @OA\Property(property="y", type="integer", example=50)
     *              ))
     *          )
     *      )
     * )
     */
    public function getLeadSourceBreakdown(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $leadSources = Lead::select('lead_source_id')
            ->selectRaw('count(*) as count')
            ->when($dealershipId && $dealershipId != -1, function ($query) use ($dealershipId) {
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

        return $this->sendResponse($data, 'Lead source breakdown retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/dashboard/employee-lead-performance",
     *      summary="Get employee lead performance",
     *      tags={"Dashboard Analytics"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          description="Filter by dealership ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getEmployeeLeadPerformance(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $employeePerformance = Employee::withCount(['leads', 'clients'])
            ->has('user')
            ->when($dealershipId && $dealershipId != -1, function ($query) use ($dealershipId) {
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

        return $this->sendResponse($employeePerformance, 'Employee lead performance retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/dashboard/top-clients",
     *      summary="Get top clients",
     *      tags={"Dashboard Analytics"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          description="Filter by dealership ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getTopClients(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $topClientsQuery = Client::query();

        if ($dealershipId && $dealershipId != -1) {
            $topClientsQuery->where('dealership_id', $dealershipId);
        }

        $topClients = $topClientsQuery->withCount(['leads' => function ($query) {
            $query->where('status', 'win');
        }])
            ->with('state', 'district')
            ->orderBy('leads_count', 'desc')
            ->limit(5)
            ->get();

        return $this->sendResponse($topClients, 'Top clients retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/dashboard/recent-activities",
     *      summary="Get recent activities",
     *      tags={"Dashboard Analytics"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          description="Filter by dealership ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getRecentActivities(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $recentActivities = Log::with('user')
            ->when($dealershipId && $dealershipId != -1, function ($query) use ($dealershipId) {
                return $query->whereHas('user.employee', function ($q) use ($dealershipId) {
                    $q->where('dealership_id', $dealershipId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return $this->sendResponse($recentActivities, 'Recent activities retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/dashboard/upcoming-events",
     *      summary="Get upcoming events",
     *      tags={"Dashboard Analytics"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          description="Filter by dealership ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getUpcomingEvents(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $upcomingFollowups = Followup::when($dealershipId && $dealershipId != -1, function ($query) use ($dealershipId) {
            return $query->whereHas('lead', function ($q) use ($dealershipId) {
                $q->where('dealership_id', $dealershipId);
            });
        })
            ->where('next_follow_up_date', '>=', Carbon::today())
            ->orderBy('next_follow_up_date')
            ->limit(10)
            ->get();

        $upcomingTasks = Task::when($dealershipId && $dealershipId != -1, function ($query) use ($dealershipId) {
            return $query->where('dealership_id', $dealershipId);
        })
            ->where('due_date', '>=', Carbon::today())
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return $this->sendResponse([
            'followups' => $upcomingFollowups,
            'tasks' => $upcomingTasks,
        ], 'Upcoming events retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/dashboard/all-leads",
     *      summary="Get recent leads",
     *      tags={"Dashboard Analytics"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          description="Filter by dealership ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function getAllLeads(Request $request)
    {
        $dealershipId = $this->getFilteredDealershipId($request);

        $allLeads = Lead::with(['client', 'employee.user', 'leadSource'])
            ->when($dealershipId && $dealershipId != -1, function ($query) use ($dealershipId) {
                return $query->where('dealership_id', $dealershipId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return $this->sendResponse($allLeads, 'Recent leads retrieved successfully.');
    }

    private function getFilteredDealershipId(Request $request)
    {
        $user = auth()->user();
        $userDealershipId = $user->employee->dealership_id ?? null;
        
        $role_id = Session::get('role_id') ?? ($user->employee->role_id ?? null);
        $canViewAllDashboards = checkMenu($role_id, 1, 'read') || $user->user_type === 'admin';

        if ($canViewAllDashboards) {
            return $request->query('dealership_id');
        }
        
        return $userDealershipId ?: -1; 
    }
}
