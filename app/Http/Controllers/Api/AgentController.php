<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Agents",
 *     description="API Endpoints for Agents"
 * )
 */

class AgentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/agents",
     *     tags={"Agents"},
     *     summary="Get list of agents",
     *     description="Returns list of agents",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(ref="#/components/schemas/Agent")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(Request $request)
    {
        $canonicalOrder = [
            'id',
            'user_id',
            'dealership_id',
            'zone_id',
            'name',
            'email',
            'phone_number',
            'status',
            'employee_id',
            'is_employee',
            'created_at',
            'updated_at',
            'employee',
            'type',
            'display_name',
        ];

        // Fetch all agents with their associated employee data
        $agents = Agent::with('employee')->get()->map(function ($agent) use ($canonicalOrder) {
            $agentData = $agent->toArray();
            // Force is_employee to boolean
            $agentData['is_employee'] = (bool)($agentData['is_employee'] ?? false);

            $agentData['type'] = 'App\\Models\\Agent';
            $agentData['display_name'] = $agent->name; // Default display name

            // If the agent is associated with an employee, update display_name
            if ($agentData['is_employee'] && $agent->employee) {
                $agentData['display_name'] = $agent->employee->name;
                if ($agentData['employee'] && isset($agentData['employee']['employee_id'])) {
                    $agentData['display_name'] .= ' (' . $agentData['employee']['employee_id'] . ')';
                }
            }

            $orderedAgentData = [];
            foreach ($canonicalOrder as $key) {
                $orderedAgentData[$key] = $agentData[$key] ?? null;
            }
            return (object) $orderedAgentData;
        });

        // Fetch employee-brokers
        $employeeBrokers = Employee::where('is_broker', true)->get()->map(function ($employee) use ($canonicalOrder) {
            $display_name = $employee->name;
            if ($employee->employee_id) {
                $display_name .= ' (' . $employee->employee_id . ')';
            }

            $employeeMap = [
                'id' => $employee->id,
                'user_id' => $employee->user_id ?? null,
                'dealership_id' => $employee->dealership_id ?? null,
                'zone_id' => $employee->zone_id ?? null,
                'name' => $employee->name,
                'email' => $employee->email,
                'phone_number' => $employee->mobile, // Map employee's mobile to agent's phone_number
                'status' => $employee->status ?? 'active', // Assuming employee has a status or default
                'employee_id' => $employee->id, // This employee is acting as an agent
                // is_employee shall either be true or false based on context; here it's true
                'is_employee' => !empty($employee->user_id),
                'created_at' => $employee->created_at,
                'updated_at' => $employee->updated_at,
                'employee' => null, // Explicitly add employee as null for consistency
                'type' => 'App\\Models\\Employee',
                'display_name' => $display_name,
            ];

            $orderedEmployeeData = [];
            foreach ($canonicalOrder as $key) {
                $orderedEmployeeData[$key] = $employeeMap[$key] ?? null;
            }
            return (object) $orderedEmployeeData;
        });

        // Combine and sort all agents
        $allAgents = $agents->merge($employeeBrokers)->sortBy('name')->values();

        return response()->json([
            'data' => $allAgents,
        ]);
    }

        /**
         * @OA\Post(
         *     path="/agents",
         *     tags={"Agents"},
         *     summary="Create a new agent",
         *     description="Create a new agent record",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(ref="#/components/schemas/AgentRequest")
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Agent created successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Agent")
         *     ),
         *     @OA\Response(
         *         response=400,
         *         ref="#/components/responses/ErrorResponse"
         *     ),
         *     security={{"bearerAuth":{}}}
         * )
         */
        public function store(Request $request)
        {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'dealership_id' => 'nullable|exists:dealerships,id',
            'zone_id' => 'nullable|exists:zones,id',
            'status' => 'nullable|in:active,inactive',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'employee_id' => 'nullable|exists:employees,id',
            'is_employee' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $agent = Agent::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Agent created successfully',
            'data' => $agent
        ], 200);
    }

        /**
         * @OA\Get(
         *     path="/agents/{id}",
         *     tags={"Agents"},
         *     summary="Get agent by ID",
         *     description="Returns a single agent",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Successful operation",
         *         @OA\JsonContent(ref="#/components/schemas/Agent")
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Agent not found",
         *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
         *     ),
         *     security={{"bearerAuth":{}}}
         * )
         */
        public function show($id)
        {
            $agent = Agent::find($id);

            if (is_null($agent)) {
                return response()->json(['error' => 'Agent not found.'], 404);
            }

            return response()->json(['agent' => $agent]);
        }

        /**
         * @OA\Put(
         *     path="/agents/{id}",
         *     tags={"Agents"},
         *     summary="Update an existing agent",
         *     description="Update an existing agent record",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(ref="#/components/schemas/AgentRequest")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Agent updated successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Agent")
         *     ),
         *     @OA\Response(
         *         response=400,
         *         ref="#/components/responses/ErrorResponse"
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Agent not found",
         *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
         *     ),
         *     security={{"bearerAuth":{}}}
         * )
         */
        public function update(Request $request, $id)
        {
            $agent = Agent::find($id);

            if (is_null($agent)) {
                return response()->json(['error' => 'Agent not found.'], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:users,id',
                'dealership_id' => 'nullable|exists:dealerships,id',
                'zone_id' => 'nullable|exists:zones,id',
                'status' => 'nullable|in:active,inactive',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone_number' => 'nullable|string|max:20',
                'employee_id' => 'nullable|exists:employees,id',
                'is_employee' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $agent->update($request->all());

            return response()->json(['agent' => $agent]);
        }

        /**
         * @OA\Delete(
         *     path="/agents/{id}",
         *     tags={"Agents"},
         *     summary="Delete an agent",
         *     description="Deletes a specific agent by ID",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Agent deleted successfully",
         *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Agent not found",
         *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
         *     ),
         *     security={{"bearerAuth":{}}}
         * )
         */
        public function destroy($id)
        {
        $agent = Agent::find($id);

        if (is_null($agent)) {
            return response()->json(['error' => 'Agent not found.'], 404);
        }

        $agent->delete();
        return response()->json(['message' => 'Agent deleted successfully.']);
    }
}