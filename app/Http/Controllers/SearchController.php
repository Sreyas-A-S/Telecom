<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SearchController extends Controller
{
    public function searchPages(Request $request)
    {
        $query = strtolower($request->input('q', ''));

        if (empty($query)) {
            return response()->json([]);
        }

        $results = [];

        // 1. Static Pages
        $pages = $this->getAllPages();
        $filteredPages = array_filter($pages, function ($page) use ($query) {
            return str_contains(strtolower($page['title']), $query) ||
                str_contains(strtolower($page['description']), $query) ||
                (isset($page['keywords']) && str_contains(strtolower(implode(' ', $page['keywords'])), $query));
        });
        $results = array_merge($results, array_values($filteredPages));

        // 2. Dynamic Data (Limited to avoid performance issues)

        // Employees
        $employees = \App\Models\Employee::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('employee_id', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($employees as $employee) {
            $results[] = [
                'title' => $employee->name,
                'description' => "Employee ID: {$employee->employee_id} • {$employee->designation}",
                'url' => route('employees.index') . "?search=" . urlencode($employee->name),
                'icon' => 'user',
                'category' => 'Employees'
            ];
        }

        // Leads
        $leads = \App\Models\Lead::where('name', 'like', "%{$query}%")
            ->orWhere('phone_number', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($leads as $lead) {
            $results[] = [
                'title' => $lead->name,
                'description' => "Lead • {$lead->phone_number} • {$lead->status}",
                'url' => route('leads.index') . "?search=" . urlencode($lead->name),
                'icon' => 'target',
                'category' => 'Leads'
            ];
        }

        // Clients
        $clients = \App\Models\Client::where('name', 'like', "%{$query}%")
            ->orWhere('phone_number', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($clients as $client) {
            $results[] = [
                'title' => $client->name,
                'description' => "Client • {$client->phone_number}",
                'url' => route('clients.index') . "?search=" . urlencode($client->name),
                'icon' => 'briefcase',
                'category' => 'Clients'
            ];
        }

        // Tasks
        $tasks = \App\Models\Task::where('title', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(3)
            ->get();
        foreach ($tasks as $task) {
            $results[] = [
                'title' => $task->title,
                'description' => "Task #{$task->id} • " . ucfirst($task->status),
                'url' => route('tasks.index') . "?search=" . urlencode($task->title),
                'icon' => 'check-square',
                'category' => 'Tasks'
            ];
        }

        return response()->json(array_values($results));
    }

    private function getAllPages()
    {
        return [
            // Dashboard
            [
                'title' => 'Dashboard',
                'description' => 'Main dashboard with statistics and overview',
                'url' => route('dashboard'),
                'icon' => 'home',
                'category' => 'Main',
                'keywords' => ['home', 'overview', 'statistics', 'main']
            ],

            // Requests
            [
                'title' => 'Leave Requests',
                'description' => 'Manage employee leave requests and calendar',
                'url' => route('leave-requests.index'),
                'icon' => 'calendar',
                'category' => 'Requests',
                'keywords' => ['leave', 'vacation', 'time off', 'absence', 'calendar']
            ],
            [
                'title' => 'Expense Requests',
                'description' => 'Track and manage expense claims',
                'url' => route('expense-requests.index'),
                'icon' => 'dollar-sign',
                'category' => 'Requests',
                'keywords' => ['expense', 'money', 'reimbursement', 'claims', 'travel']
            ],
            [
                'title' => 'Document Requests',
                'description' => 'Request and manage documents',
                'url' => route('document-requests.index'),
                'icon' => 'file-text',
                'category' => 'Requests',
                'keywords' => ['document', 'files', 'papers', 'certificates']
            ],
            [
                'title' => 'Loan Requests',
                'description' => 'Employee loan requests and approvals',
                'url' => route('loan-requests.index'),
                'icon' => 'credit-card',
                'category' => 'Requests',
                'keywords' => ['loan', 'advance', 'money', 'finance']
            ],

            // HR Management
            [
                'title' => 'Employees',
                'description' => 'Manage employee information and records',
                'url' => route('employees.index'),
                'icon' => 'users',
                'category' => 'HR',
                'keywords' => ['employees', 'staff', 'personnel', 'team', 'people']
            ],
            [
                'title' => 'Attendance',
                'description' => 'Track employee attendance and clock in/out',
                'url' => route('attendance.index'),
                'icon' => 'clock',
                'category' => 'HR',
                'keywords' => ['attendance', 'clock', 'time', 'present', 'absent']
            ],
            [
                'title' => 'Organization',
                'description' => 'View organization hierarchy and structure',
                'url' => route('organization.index'),
                'icon' => 'sitemap',
                'category' => 'HR',
                'keywords' => ['organization', 'hierarchy', 'structure', 'chart']
            ],

            // Task Management
            [
                'title' => 'Tasks',
                'description' => 'Manage tasks and assignments',
                'url' => route('tasks.index'),
                'icon' => 'check-square',
                'category' => 'Tasks',
                'keywords' => ['tasks', 'assignments', 'todo', 'work', 'jobs']
            ],
            [
                'title' => 'Live Location',
                'description' => 'Track employee live locations',
                'url' => route('live-location.index'),
                'icon' => 'map-pin',
                'category' => 'Tasks',
                'keywords' => ['location', 'gps', 'tracking', 'map', 'live']
            ],
            [
                'title' => 'Timeline',
                'description' => 'View employee activity timeline',
                'url' => route('timeline.index'),
                'icon' => 'activity',
                'category' => 'Tasks',
                'keywords' => ['timeline', 'history', 'activity', 'log']
            ],

            // CRM
            [
                'title' => 'Leads',
                'description' => 'Manage sales leads and prospects',
                'url' => route('leads.index'),
                'icon' => 'target',
                'category' => 'CRM',
                'keywords' => ['leads', 'prospects', 'sales', 'customers']
            ],
            [
                'title' => 'Clients',
                'description' => 'Manage client information',
                'url' => route('clients.index'),
                'icon' => 'briefcase',
                'category' => 'CRM',
                'keywords' => ['clients', 'customers', 'accounts']
            ],

            // Services
            [
                'title' => 'Services',
                'description' => 'Manage service requests and FSR',
                'url' => route('entries.index'),
                'icon' => 'tool',
                'category' => 'Services',
                'keywords' => ['services', 'fsr', 'maintenance', 'repair']
            ],

            // Products
            [
                'title' => 'Products',
                'description' => 'Manage product catalog',
                'url' => route('products.index'),
                'icon' => 'package',
                'category' => 'Products',
                'keywords' => ['products', 'catalog', 'items', 'inventory']
            ],
            [
                'title' => 'Categories',
                'description' => 'Manage product categories',
                'url' => route('categories.index'),
                'icon' => 'grid',
                'category' => 'Products',
                'keywords' => ['categories', 'groups', 'classification']
            ],

            // Job Management
            [
                'title' => 'Job Vacancies',
                'description' => 'Post and manage job openings',
                'url' => route('job-vacancies.index'),
                'icon' => 'briefcase',
                'category' => 'Jobs',
                'keywords' => ['jobs', 'vacancies', 'hiring', 'recruitment', 'careers']
            ],
            [
                'title' => 'Interviews',
                'description' => 'Schedule and manage interviews',
                'url' => route('interviews.index'),
                'icon' => 'user-check',
                'category' => 'Jobs',
                'keywords' => ['interviews', 'candidates', 'hiring']
            ],

            // Settings
            [
                'title' => 'Application Settings',
                'description' => 'Configure travel allowance and system settings',
                'url' => route('settings.index'),
                'icon' => 'settings',
                'category' => 'Settings',
                'keywords' => ['settings', 'configuration', 'travel allowance', 'rates']
            ],
            // Removed Users as users.index is not defined and UserController is empty
            [
                'title' => 'Roles & Permissions',
                'description' => 'Configure user roles and permissions',
                'url' => route('roles.index'),
                'icon' => 'shield',
                'category' => 'Settings',
                'keywords' => ['roles', 'permissions', 'access control', 'security']
            ],

            // Profile
            [
                'title' => 'My Profile',
                'description' => 'View and edit your profile',
                'url' => route('my-profile'),
                'icon' => 'user',
                'category' => 'Profile',
                'keywords' => ['profile', 'account', 'personal', 'settings']
            ],
            [
                'title' => 'Notifications',
                'description' => 'View all notifications',
                'url' => route('notifications.index'),
                'icon' => 'bell',
                'category' => 'Profile',
                'keywords' => ['notifications', 'alerts', 'messages']
            ],
        ];
    }
}
