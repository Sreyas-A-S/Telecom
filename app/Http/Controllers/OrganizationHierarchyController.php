<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

use App\Models\Dealership;

class OrganizationHierarchyController extends Controller
{
    public function index()
    {
        // Logic to fetch and structure hierarchy data will go here
        $users = User::with('employee.subordinates', 'employee.reporter3')->get();
        $employees = Employee::with('user', 'subordinates.user', 'reporter3.user', 'dealership', 'department')->get();
        
        $brand0Dealerships = Dealership::where('brand', 0)->get();
        $brand1Dealerships = Dealership::where('brand', 1)->get();
        $departments = \App\Models\Department::all();

        $hierarchy = $this->buildHierarchy($users, $employees, $brand0Dealerships, $brand1Dealerships, $departments);

        return view('organization.hierarchy', compact('hierarchy'));
    }

    public function embed()
    {
        $users = User::with('employee.subordinates', 'employee.reporter3')->get();
        $employees = Employee::with('user', 'subordinates.user', 'reporter3.user', 'dealership', 'department')->get();
        
        $brand0Dealerships = Dealership::where('brand', 0)->get();
        $brand1Dealerships = Dealership::where('brand', 1)->get();
        $departments = \App\Models\Department::all();

        $hierarchy = $this->buildHierarchy($users, $employees, $brand0Dealerships, $brand1Dealerships, $departments);

        // Debug: Check if hierarchy has data
        // dd($hierarchy); 

        return view('organization.hierarchy_embed', compact('hierarchy'));
    }

    private function buildHierarchy($users, $employees, $brand0Dealerships, $brand1Dealerships, $departments)
    {
        $nodes = [];
        $employeeMap = $employees->keyBy('id');
        
        // Find the super admin
        $superAdminUser = $users->firstWhere('user_type', 'admin');
        $superAdminNode = null;

        if ($superAdminUser) {
            $superAdminNode = [
                'id' => 'user-' . $superAdminUser->id,
                'name' => $superAdminUser->name,
                'title' => 'Super Admin',
                'parent' => null,
                'children' => [],
                'user_type' => $superAdminUser->user_type,
                'employee_id' => null,
                'department' => null,
                'profile_pic' => $superAdminUser->profile_pic ?? null,
            ];
            $nodes[$superAdminNode['id']] = $superAdminNode;
        } else {
            $superAdminNode = [
                'id' => 'user-0',
                'name' => 'Undefined Super Admin',
                'title' => 'System Admin',
                'parent' => null,
                'children' => [],
                'user_type' => 'admin',
                'employee_id' => null,
                'department' => null,
                'profile_pic' => null,
            ];
            $nodes[$superAdminNode['id']] = $superAdminNode;
        }

        // Level 1: Brand 0 Dealerships
        foreach ($brand0Dealerships as $b0) {
            $b0NodeId = 'b0-' . $b0->id;
            $b0Node = [
                'id' => $b0NodeId,
                'name' => $b0->name,
                'title' => 'Corporate Division',
                'parent' => $superAdminNode['id'],
                'children' => [],
                'user_type' => 'dealership',
                'employee_id' => null,
                'department' => null,
                'profile_pic' => null,
                'className' => 'dealership-node'
            ];
            $nodes[$b0NodeId] = $b0Node;
            $nodes[$superAdminNode['id']]['children'][] = $b0NodeId;

            // Level 2A: Brand 1 Dealerships (Available)
            foreach ($brand1Dealerships as $b1) {
                $b1NodeId = $b0NodeId . '-b1-' . $b1->id;
                $b1Node = [
                    'id' => $b1NodeId,
                    'name' => $b1->name,
                    'title' => 'Dealership',
                    'parent' => $b0NodeId,
                    'children' => [],
                    'user_type' => 'dealership',
                    'employee_id' => null,
                    'department' => null,
                    'profile_pic' => null,
                    'className' => 'dealership-node'
                ];
                $nodes[$b1NodeId] = $b1Node;
                $nodes[$b0NodeId]['children'][] = $b1NodeId;

                // Level 3: Departments under Brand 1
                foreach ($departments as $dept) {
                    $deptNodeId = $b1NodeId . '-d-' . $dept->id;
                    $deptNode = [
                        'id' => $deptNodeId,
                        'name' => $dept->name,
                        'title' => 'Department',
                        'parent' => $b1NodeId,
                        'children' => [],
                        'user_type' => 'department',
                        'employee_id' => null,
                        'department' => $dept->name,
                        'profile_pic' => null,
                    ];
                    $nodes[$deptNodeId] = $deptNode;
                    $nodes[$b1NodeId]['children'][] = $deptNodeId;

                    // Level 4: Employees in this Brand 1 and Department
                    // Note: This duplicates employees across Brand 0 nodes
                    $deptEmployees = $employees->filter(function($e) use ($b1, $dept) {
                        return $e->dealership_id == $b1->id && $e->department_id == $dept->id;
                    });

                    foreach ($deptEmployees as $employee) {
                        $this->addEmployeeNode($employee, $deptNodeId, $nodes, $superAdminNode['id']);
                    }
                }
            }

            // Level 2B: No Dealership (General/Head Office Staff)
            // If employee has NO dealership_id, they go here under Brand 0 > Department
            foreach ($departments as $dept) {
                $generalDeptNodeId = $b0NodeId . '-general-d-' . $dept->id;
                
                // Only create this node if there are employees for it, to reduce clutter?
                // Or create it to show structure. Let's create it.
                $generalDeptNode = [
                    'id' => $generalDeptNodeId,
                    'name' => $dept->name,
                    'title' => 'Department (HQ)',
                    'parent' => $b0NodeId,
                    'children' => [],
                    'user_type' => 'department',
                    'employee_id' => null,
                    'department' => $dept->name,
                    'profile_pic' => null,
                ];
                $nodes[$generalDeptNodeId] = $generalDeptNode;
                $nodes[$b0NodeId]['children'][] = $generalDeptNodeId;

                $generalEmployees = $employees->filter(function($e) use ($dept) {
                    return $e->dealership_id == null && $e->department_id == $dept->id;
                });

                foreach ($generalEmployees as $employee) {
                    $this->addEmployeeNode($employee, $generalDeptNodeId, $nodes, $superAdminNode['id']);
                }
            }
        }

        // Filter out empty nodes to clean up the chart?
        // The user might want to see the structure even if empty.
        // But with Cartesian product, it will be huge.
        // Let's filter out empty Department nodes and empty Brand 1 nodes.
        $this->pruneEmptyNodes($nodes, $superAdminNode['id']);

        // Collect final hierarchy starting from Super Admin
        $finalHierarchy = [];
        if ($superAdminNode) {
            $this->collectChildren($superAdminNode['id'], $nodes, $finalHierarchy);
        }

        // ... existing code ...
        
        // Debugging: Log counts
        Log::info('Hierarchy Build Stats:', [
            'brand0_count' => $brand0Dealerships->count(),
            'brand1_count' => $brand1Dealerships->count(),
            'departments_count' => $departments->count(),
            'employees_count' => $employees->count(),
            'final_hierarchy_count' => count($finalHierarchy)
        ]);

        return array_values($finalHierarchy);
    }

    private function addEmployeeNode($employee, $parentId, &$nodes, $superAdminId)
    {
        if (!$employee->user) return;

        // Unique ID for employee in this specific branch of the tree
        $nodeId = $parentId . '-emp-' . $employee->user_id;
        
        $node = [
            'id' => $nodeId,
            'name' => $employee->user->name ?? 'N/A',
            'title' => $employee->position ?? 'Employee',
            'parent' => $parentId,
            'children' => [],
            'user_type' => $employee->user->user_type ?? 'employee',
            'employee_id' => $employee->id,
            'department' => $employee->department->name ?? 'N/A',
            'profile_pic' => ($employee->profile_pic && file_exists(public_path('storage/'.$employee->profile_pic))) ? asset('storage/'.$employee->profile_pic) : null,
        ];
        $nodes[$nodeId] = $node;
        $nodes[$parentId]['children'][] = $nodeId;
    }

    private function pruneEmptyNodes(&$nodes, $rootId)
    {
        // Bottom-up pruning is hard with this flat structure.
        // Simple approach: Remove Department nodes with no children.
        // Then remove Dealership nodes with no children.
        
        // 1. Prune Departments
        foreach ($nodes as $id => $node) {
            if ($node['user_type'] === 'department' && empty($node['children'])) {
                // Remove from parent's children list
                if (isset($nodes[$node['parent']])) {
                    $parent = &$nodes[$node['parent']];
                    $parent['children'] = array_diff($parent['children'], [$id]);
                }
                unset($nodes[$id]);
            }
        }

        // 2. Prune Dealerships (Brand 1)
        foreach ($nodes as $id => $node) {
            if ($node['user_type'] === 'dealership' && strpos($id, '-b1-') !== false && empty($node['children'])) {
                if (isset($nodes[$node['parent']])) {
                    $parent = &$nodes[$node['parent']];
                    $parent['children'] = array_diff($parent['children'], [$id]);
                }
                unset($nodes[$id]);
            }
        }
        
        // 3. Prune Brand 0 Dealerships if empty?
        // Maybe keep them to show the top level structure.
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
