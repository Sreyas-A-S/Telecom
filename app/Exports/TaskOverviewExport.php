<?php

namespace App\Exports;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaskOverviewExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $task;
    protected $totalTime;
    protected $taskLogs;

    public function __construct($task, $totalTime, $taskLogs)
    {
        $this->task = $task;
        $this->totalTime = $totalTime;
        $this->taskLogs = $taskLogs;
    }

    public function view(): View
    {
        return view('leads.task_overview_export', [
            'task' => $this->task,
            'totalTime' => $this->totalTime,
            'taskLogs' => $this->taskLogs
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
