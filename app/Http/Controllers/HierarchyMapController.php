<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

class HierarchyMapController extends Controller
{
    public function index()
    {
        $users = User::with('employee.subordinates', 'employee.reporter3')->get();
        $employees = Employee::with('user', 'subordinates.user', 'reporter3.user')->get();

        $hierarchyData = $this->buildHierarchy($users, $employees);

        return view('hierarchy-map', compact('hierarchyData'));
    }

    private function buildHierarchy($users, $employees)
    {
        $nodes = [];
        $employeeMap = $employees->keyBy('id');
        $userMap = $users->keyBy('id');

        // Find the super admin
        $superAdminUser = $users->firstWhere('user_type', 'admin');
        $superAdminNode = null;

        if ($superAdminUser) {
            $superAdminNode = [
                'id' => 'user-' . $superAdminUser->id,
                'name' => $superAdminUser->name,
                'children' => [],
                'user_type' => $superAdminUser->user_type,
                'employee_id' => null,
                'department' => null,
                'title' => 'Super Admin',
                'parent' => null,
            ];
            $nodes[$superAdminNode['id']] = $superAdminNode;
        } else {
            // Create a placeholder super admin if not found
            $superAdminNode = [
                'id' => 'user-0',
                'name' => 'Undefined Super Admin',
                'title' => 'System Admin',
                'parent' => null,
                'children' => [],
                'user_type' => 'admin',
                'employee_id' => null,
                'department' => null,
            ];
            $nodes[$superAdminNode['id']] = $superAdminNode;
        }

        // Add all employees as nodes
        foreach ($employees as $employee) {
            // Ensure employee has a user relationship before accessing user properties
            if (!$employee->user) {
                Log::warning('Employee with ID ' . $employee->id . ' has no associated user.');
                continue; // Skip employees without a user
            }
            $node = [
                'id' => 'employee-' . $employee->id,
                'name' => $employee->user->name ?? 'N/A',
                'title' => $employee->position ?? 'Employee',
                'parent' => null, // Will be set later
                'children' => [],
                'user_type' => $employee->user->user_type ?? 'employee',
                'employee_id' => $employee->id,
                'department' => $employee->department->name ?? 'N/A',
            ];
            $nodes[$node['id']] = $node;
        }

        // Establish parent-child relationships
        foreach ($employees as $employee) {
            // Skip if employee was skipped earlier due to missing user
            if (!isset($nodes['employee-' . $employee->id])) {
                continue;
            }

            $employeeNodeId = 'employee-' . $employee->id;

            if ($employee->reporting_to) {
                $parentNodeId = 'employee-' . $employee->reporting_to;
                // Ensure parent node exists before linking
                if (isset($nodes[$parentNodeId])) {
                    $nodes[$employeeNodeId]['parent'] = $parentNodeId;
                    $nodes[$parentNodeId]['children'][] = $employeeNodeId;
                } else {
                    Log::warning('Employee ' . $employee->id . ' reports to non-existent employee ' . $employee->reporting_to . '. Linking to Super Admin.');
                    // Link to super admin if reporting_to is invalid
                    $nodes[$employeeNodeId]['parent'] = $superAdminNode['id'];
                    $nodes[$superAdminNode['id']]['children'][] = $employeeNodeId;
                }
            } else {
                // If an employee doesn't report to anyone, they report to the super admin
                $nodes[$employeeNodeId]['parent'] = $superAdminNode['id'];
                $nodes[$superAdminNode['id']]['children'][] = $employeeNodeId;
            }
        }

        // Filter out nodes that are not connected to the super admin or another employee
        // This step ensures only connected nodes are part of the final hierarchy
        $finalHierarchy = [];
        if ($superAdminNode) {
            $this->collectChildren($superAdminNode['id'], $nodes, $finalHierarchy);
        }

        Log::info('Final Hierarchy Data: ' . json_encode(array_values($finalHierarchy)));

        return array_values($finalHierarchy);
    }

    private function collectChildren($nodeId, &$allNodes, &$collectedNodes)
    {
        if (!isset($allNodes[$nodeId]) || isset($collectedNodes[$nodeId])) {
            return;
        }

        $collectedNodes[$nodeId] = $allNodes[$nodeId];

        foreach ($allNodes[$nodeId]['children'] as $childId) {
            $this->collectChildren($childId, $allNodes, $collectedNodes);
        }
    }
}
