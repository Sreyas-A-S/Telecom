<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return Employee::query()->with(['department', 'role', 'dealership', 'zone', 'reporter']);
    }

    public function map($employee): array
    {
        return [
            $employee->name,
            $employee->employee_id,
            $employee->email,
            $employee->mobile,
            $employee->gender,
            $employee->dob,
            $employee->joining_date,
            $employee->address,
            $employee->designation,
            $employee->department ? $employee->department->name : 'N/A',
            $employee->role ? str_replace('_', ' ', strtoupper($employee->role->role)) : 'N/A',
            $employee->dealership ? $employee->dealership->name : 'N/A',
            $employee->zone ? $employee->zone->name : 'N/A',
            $employee->reporter ? $employee->reporter->name : 'N/A',
            $employee->is_broker ? 'Yes' : 'No',
            $employee->marital_status,
            $employee->emergency_contact,
            $employee->father_name,
            $employee->mother_name,
            $employee->spouse_name,
            $employee->shirt_size,
            $employee->tshirt_size,
            $employee->blood_group,
            $employee->bank_name,
            $employee->account_number,
            $employee->ifsc_code,
            $employee->branch,
            $employee->pf_no,
            $employee->esi_no,
            $employee->lwf_no,
            $employee->aadhar_no,
            $employee->pan_no,
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Employee ID',
            'Email',
            'Mobile',
            'Gender',
            'Date of Birth',
            'Joining Date',
            'Address',
            'Designation',
            'Department',
            'Role',
            'Dealership',
            'Zone',
            'Reporting To',
            'Is Agent', // Showing 'Is Agent' for user friendliness as per recent changes
            'Marital Status',
            'Emergency Contact',
            'Father Name',
            'Mother Name',
            'Spouse Name',
            'Shirt Size',
            'T-Shirt Size',
            'Blood Group',
            'Bank Name',
            'Account Number',
            'IFSC Code',
            'Branch',
            'PF No',
            'ESI No',
            'LWF No',
            'Aadhar No',
            'PAN No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
