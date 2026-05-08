<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TaskReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Task::with(['assignedEmployee', 'dealership', 'entry', 'lead', 'fsrReport']);

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['dealership_id'])) {
            $query->where('dealership_id', $this->filters['dealership_id']);
        }

        if (!empty($this->filters['employee_id'])) {
            $query->where('assigned_to', $this->filters['employee_id']);
        }

        if (!empty($this->filters['department_id'])) {
            $query->whereHas('assignedEmployee', function($q) {
                $q->where('department_id', $this->filters['department_id']);
            });
        }

        if (!empty($this->filters['task_type'])) {
            if ($this->filters['task_type'] === 'leads') {
                $query->whereNotNull('lead_id');
            } elseif ($this->filters['task_type'] === 'service') {
                $query->where('is_service', 1);
            } elseif ($this->filters['task_type'] === 'other') {
                $query->whereNull('lead_id')->where(function($q) {
                    $q->where('is_service', 0)->orWhereNull('is_service');
                });
            }
        }

        return $query->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Title',
            'Type',
            'Dealership',
            'Assigned To',
            'Status',
            'Elapsed Time',
            'FSR Status',
            'FSR Assessment',
            'FSR Cause',
            'FSR Actions',
            'Follow-ups History',
            'Description'
            ];
            }

            public function map($task): array
            {
            $followupsHistory = $task->followups->map(function ($f) {
            return $f->created_at->format('d M H:i') . ' (' . ($f->user->name ?? 'N/A') . '): ' . $f->notes;
            })->implode(" | ");

            return [
            $task->id,
            $task->created_at->format('d M, Y'),
            $task->title,
            $task->task_type_label,
            $task->dealership ? $task->dealership->name : 'N/A',
            $task->assignedEmployee ? $task->assignedEmployee->name : 'Unassigned',
            $task->derived_status,
            $task->getFormattedElapsedTime() . ($task->timer_started_at ? ' (Running)' : ''),
            $task->fsrReport ? ucfirst($task->fsrReport->status) : 'No FSR',
            $task->fsrReport ? $task->fsrReport->on_site_assessment : 'N/A',
            $task->fsrReport ? $task->fsrReport->analysis_of_cause : 'N/A',
            $task->fsrReport ? $task->fsrReport->actions_taken : 'N/A',
            $followupsHistory ?: 'No follow-ups',
            $task->description
            ];
            }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
