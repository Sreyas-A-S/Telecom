<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ServiceManagerApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userDealershipId = null;

        if ($user->employee) {
            $userDealershipId = $user->employee->dealership_id;
        }

        // Authorization: Only service managers can access this page
   

        if ($request->ajax()) {
            $data = Task::with('assignedEmployee')
                        ->whereNotIn('status', ['completed'])
                      // where start_date_time is not null or is less than today
                      ->where(function ($query) {
                          $query->whereNotNull('start_date_time')
                                ->orWhereDate('start_date_time', '<', today());
                      })
                     
                        ->where(function ($query) {
                            $query->whereNull('sm_approved_early_action_date')
                                  ->orWhereDate('sm_approved_early_action_date', '<', today());
                        });

            if ($userDealershipId) {
                $data->where('dealership_id', $userDealershipId);
            }

            $data->select('tasks.*', 'assigned_to', 'sm_approved_early_action_date');

                        //get the sql query and params in laravel logs
                       \Log::info('SQL Query: ' . $data->toSql());
                       \Log::info('SQL Params: ' . json_encode($data->getBindings()));

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('employee_name', function ($row) {
                    return $row->assignedEmployee->name ?? 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-4">';




                    $isTaskNotCompleted = ($row->status !== 'completed');
                    $isStartDatePast = ($row->start_date_time && $row->start_date_time->toDateString() < now()->toDateString());

                    $requiresApproval = $isTaskNotCompleted && $isStartDatePast;

                    \Log::info('Approve Button Debug:', [
                        'task_id' => $row->id,
                        'status' => $row->status,
                        'start_date_time' => $row->start_date_time,
                        'isTaskNotCompleted' => $isTaskNotCompleted,
                        'isStartDatePast' => $isStartDatePast,
                        'requiresApproval' => $requiresApproval,
                    ]);

                    if ($requiresApproval) {
                        $btn .= '<li><a href="javascript:void(0)" class="approve-early-action-btn btn btn-warning btn-sm" data-id="' . $row->id . '" title="Approve">Approve</a></li>';
                    }
                    $btn .= '</ul>';
                    return $btn;
                })
                ->addColumn('last_approved_date', function ($row) {
                    return $row->sm_approved_early_action_date ? \Carbon\Carbon::parse($row->sm_approved_early_action_date)->format('d/m/Y') : 'N/A';
                })
                ->editColumn('type', function ($row) {
                    if ($row->type == 'client_based') {
                        return '<span class="badge bg-primary">Client Based</span>';
                    } else if ($row->type == 'open') {
                        return '<span class="badge bg-secondary">Open</span>';
                    } else {
                        return 'N/A'; // Handle unexpected values
                    }
                })
                ->editColumn('due_date', function ($row) {
                    return $row->due_date ? $row->due_date->format('d/m/Y') : 'N/A';
                })
                ->editColumn('start_date_time', function ($row) {
                    return $row->start_date_time ? $row->start_date_time->format('d/m/Y') : 'N/A';
                })
                ->editColumn('end_date_time', function ($row) {
                    return $row->end_date_time ? $row->end_date_time->format('d/m/Y') : 'N/A';
                })
                ->rawColumns(['action', 'type'])
                ->make(true);
        }

        return view('servicemanager.approvals');
    }
}
