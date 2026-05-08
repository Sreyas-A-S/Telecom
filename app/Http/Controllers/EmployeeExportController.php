<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeExportController extends Controller
{
    private function getOrganizationDetails()
    {
        // Fetch organization details from Settings table, with fallbacks
        return [
            'name' => Setting::where('key', 'organization_name')->value('value') ?? 'Admiro Tech',
            'address' => Setting::where('key', 'organization_address')->value('value') ?? '123 Business Street, Tech Park',
            'phone' => Setting::where('key', 'organization_phone')->value('value') ?? '+91 98765 43210',
            'website' => Setting::where('key', 'organization_website')->value('value') ?? 'www.admirotech.com',
            'logo' => Setting::where('key', 'organization_logo')->value('value'), // Path to logo if exists
        ];
    }

    public function exportAll(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        $employees = Employee::with(['role', 'department', 'dealership', 'zone', 'reporter2'])->get();
        $organization = $this->getOrganizationDetails();

        $pdf = Pdf::loadView('employees.pdf-export', compact('employees', 'organization'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('employees_list.pdf');
    }

    public function exportSingle(Employee $employee)
    {
        $employee->load(['role', 'department', 'dealership', 'zone', 'reporter2']);
        $employees = collect([$employee]);
        $organization = $this->getOrganizationDetails();

        $pdf = Pdf::loadView('employees.pdf-export', compact('employees', 'organization'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('employee_' . $employee->employee_id . '.pdf');
    }
}
