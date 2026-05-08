<?php

namespace App\Exports;

use App\Models\ExpenseRequest;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LegacyWeeklyExpenseExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $employeeId;

    public function __construct($startDate, $endDate, $employeeId = null)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        // The user specifically asked for "Sundays to Mondays", 
        // which usually implies a 9-day range (Sun to next Mon) 
        // or just ensuring the range is covered.
        $this->endDate = Carbon::parse($endDate)->endOfDay();
    }

    public function collection()
    {
        $days = [];
        $current = clone $this->startDate;
        while ($current <= $this->endDate) {
            $days[] = $current->toDateString();
            $current->addDay();
        }

        $usersQuery = User::whereHas('employee')->with(['employee.department']);
        if ($this->employeeId) {
            $usersQuery->where('id', $this->employeeId);
        }
        $users = $usersQuery->get();

        $expenses = ExpenseRequest::whereBetween('date', [$this->startDate->toDateString(), $this->endDate->toDateString()])
            ->when($this->employeeId, function($q) {
                $q->where('user_id', $this->employeeId);
            })
            ->get();

        $rows = new Collection();

        foreach ($users as $user) {
            $rowData = [
                'name' => $user->name,
                'id_no' => $user->employee->employee_id ?? 'N/A',
                'department' => $user->employee->department->name ?? 'N/A',
            ];

            $total = 0;
            foreach ($days as $day) {
                $dayTotal = $expenses->where('user_id', $user->id)
                    ->where('date', $day)
                    ->sum('amount');
                $rowData[$day] = $dayTotal > 0 ? $dayTotal : 0;
                $total += $dayTotal;
            }

            $rowData['total'] = $total;
            $rows->push($rowData);
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['Employee Name', 'Emp ID', 'Department'];
        
        $current = clone $this->startDate;
        while ($current <= $this->endDate) {
            $headings[] = $current->format('D (d/m)');
            $current->addDay();
        }

        $headings[] = 'Grand Total';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
