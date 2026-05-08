@extends('layouts.admin')

@section('title', 'Birthday Notifications Log')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Birthday Notifications Log</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">HR</li>
                    <li class="breadcrumb-item"><a href="{{ route('birthdays.index') }}">Birthdays</a></li>
                    <li class="breadcrumb-item active">Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Logs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>Recipient</th>
                                    <th>Message</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @php
                                        $employeeId = $log->data['employee_id'] ?? null;
                                        $employee = $employeeId ? ($employees[$employeeId] ?? null) : null;
                                        @endphp
                                        @if($employee)
                                        <span class="fw-bold">{{ $employee->name }}</span>
                                        <div class="small text-muted">{{ $employee->designation }}</div>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->user ? $log->user->name : 'Unknown User' }}</td>
                                    <td>{{ $log->message }}</td>
                                    <td>
                                        @if(isset($log->data['type']) && $log->data['type'] == 'birthday_self')
                                        <span class="badge badge-success">Employee</span>
                                        @elseif(isset($log->data['type']) && $log->data['type'] == 'birthday_hr')
                                        <span class="badge badge-info">HR Alert</span>
                                        @else
                                        <span class="badge badge-secondary">{{ $log->data['type'] ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($log->data['status']))
                                        @if($log->data['status'] === 'sent')
                                        <span class="badge badge-success">Sent</span>
                                        @else
                                        <span class="badge badge-danger">Failed</span>
                                        @endif
                                        @else
                                        <span class="badge badge-secondary">Unknown</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No logs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection