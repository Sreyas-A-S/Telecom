<?php

namespace App\Http\Controllers;

use App\Models\Agent; // Import the Agent model
use App\Models\Employee; // Import the Employee model
use Illuminate\Support\Facades\DB; // Import the DB facade
use Illuminate\Support\Facades\Log; // Import the Log facade
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AgentController extends Controller
{
    public function getAgentsList()
    {
        if (!checkMenu(Session::get('role_id'), 16, 'read')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $agents = Agent::all();
        return response()->json(['data' => $agents]);
    }

    public function store(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 16, 'create')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'name' => 'required|string|max:255|unique:agents,name',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id',
            'dealership_id' => 'nullable|exists:dealerships,id',
            'zone_id' => 'nullable|exists:zones,id',
            'status' => 'nullable|in:active,inactive',
            'employee_id' => 'nullable|exists:employees,id',
            'is_employee' => 'boolean',
        ]);

        $agent = Agent::create($request->all());

        return response()->json(['message' => 'Agent created successfully.', 'agent' => $agent]);
    }

    public function show($id)
    {
        if (!checkMenu(Session::get('role_id'), 16, 'read')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $agent = Agent::find($id);
        if (!$agent) {
            $agent = Employee::find($id);
            $agent->type = 'Employee';
        } else {
            $agent->type = 'Agent';
        }
        return response()->json($agent);
    }

    public function update(Request $request, $id)
    {
        if (!checkMenu(Session::get('role_id'), 16, 'update')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $agent = Agent::find($id);
        $isEmployee = false;
        if (!$agent) {
            $employee = Employee::find($id);
            if ($employee) {
                $isEmployee = true;
                $agent = $employee; // Treat employee as agent for update
            } else {
                return response()->json(['message' => 'Agent or Employee not found.'], 404);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id',
            'dealership_id' => 'nullable|exists:dealerships,id',
            'zone_id' => 'nullable|exists:zones,id',
            'status' => 'nullable|in:active,inactive',
            'employee_id' => 'nullable|exists:employees,id',
            'is_employee' => 'boolean',
        ]);

        if ($isEmployee) {
            $agent->name = $request->name;
            $agent->email = $request->email;
            $agent->mobile = $request->phone_number; // Assuming 'mobile' for Employee
            $agent->save();
        } else {
            $agent->update($request->all());
        }

        return response()->json(['message' => 'Agent updated successfully.']);
    }

    public function getAgentsForDatatables(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 16, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($request->ajax()) {
            $agents = Agent::select('id', 'name', 'email', 'phone_number', DB::raw('"Agent" as type'), DB::raw('"App\\\\Models\\\\Agent" as raw_type'))->get();
            $employees = Employee::where('is_broker', 1)
                ->select('id', 'name', 'email', 'mobile as phone_number', DB::raw('"Employee" as type'), DB::raw('"App\\\\Models\\\\Employee" as raw_type'))
                ->get();

            $data = $agents->concat($employees);

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('type', function ($row) {
                    if ($row->type == 'Employee') {
                        return '<span class="badge bg-primary">Employee</span>';
                    } else {
                        return '<span class="badge bg-secondary">Agent</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="javascript:void(0)" data-id="' . $row->id . '" class="view-agent-btn"><i class="icon-eye"></i></a></li>';
                    if ($row->type !== 'Employee') {
                        $btn .= '<li class="edit"><a href="javascript:void(0)" title="Edit" data-id="' . $row->id . '" class="edit-agent-btn"><i class="icon-pencil-alt"></i></a></li>';
                    }
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['type', 'action'])
                ->make(true);
        }
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $agents = Agent::select('id', 'name', DB::raw('"App\\\\Models\\\\Agent" as type'), DB::raw('name as display_name'))->get();
            $employees = Employee::where('is_broker', 1)
                ->select('id', 'name', 'employee_id', DB::raw('"App\\\\Models\\\\Employee" as type'))
                ->get()
                ->map(function ($employee) {
                    $employee->display_name = $employee->name . ' (' . $employee->employee_id . ')';
                    return $employee;
                });

            $data = $agents->concat($employees);
            return response()->json(['data' => $data]);
        }
        // If not an AJAX request, return a view (assuming 'agents.index' view exists)
        return view('agents.index');
    }
}
