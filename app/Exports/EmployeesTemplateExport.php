<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeesTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Employee Import sheet';
    }

    public function collection()
    {
        return collect([
            [
                'sl_no' => '1',
                'employee_name' => 'John Doe',
                'employee_id' => 'EMP001',
                'email_id' => 'john.doe@example.com',
                'phone_no' => '9876543210',
                'gender' => 'Male',
                'date_of_birth' => '1990-01-01',
                'date_of_joining' => '2023-01-01',
                'address' => '123 Main St, City',
                'designation' => 'Sales Executive',
                'department' => 'Sales Department',
                'dealership' => 'Main Dealership',
                'zone' => 'North Zone',
                'branch' => 'Main Branch', // Moved right after zone
                'country' => 'India',
                'is_agent' => 'No',
                'reporting_authority_email' => 'manager@example.com',
                'marital_status' => 'Single',
                'fathers_name' => 'Robert Doe',
                'mothers_name' => 'Mary Doe',
                'spouses_name' => '',
                'shirt_size' => 'L',
                'tshirt_size' => 'L',
                'blood_group' => 'O+',
                'bank_name' => 'HDFC Bank',
                'account_number' => '123456789012',
                'ifsc_code' => 'HDFC0001234',
                'pf_no' => 'PF123456',
                'esi_no' => 'ESI123456',
                'lwf_no' => 'LWF123456',
                'aadhar_no' => '123412341234',
                'pan_no' => 'ABCDE1234F',
                'emergency_contact' => '9876543211',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Employee Name',
            'Employee ID',
            'Email ID',
            'Phone No',
            'Gender',
            'Date of Birth',
            'Date of Joining',
            'Address',
            'Designation',
            'Department',
            'Dealership',
            'Zone',
            'Branch', // Moved here
            'Country',
            'Is Agent',
            'Reporting Authority Email',
            'Marital Status',
            'Fathers Name',
            'Mothers Name',
            'Spouses Name',
            'Shirt Size',
            'T-Shirt Size',
            'Blood Group',
            'Bank Name',
            'Account Number',
            'IFSC Code',
            'PF No',
            'ESI No',
            'LWF No',
            'Aadhar No',
            'PAN No',
            'Emergency Contact',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEEEEEE'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // Sl No
            'B' => 30, // Employee Name
            'D' => 35, // Email ID
            'I' => 50, // Address
            'R' => 25, // Fathers Name
            'S' => 25, // Mothers Name
            'T' => 25, // Spouses Name
        ];
    }
}
