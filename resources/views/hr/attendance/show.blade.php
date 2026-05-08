@extends('layouts.admin')

@section('title', 'Attendance Details')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Attendance Details</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Details</li>
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
                <div class="card-header align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="{{ $employee->profile_pic ? asset('storage/'. $employee->profile_pic) : asset('admin/assets/images/dashboard/profile.png') }}" alt="Profile Picture" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                        <h5>Attendance for {{ $employee->name }} on {{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</h5>
                    </div>
                    <br>
                    <div class="mt-2">
                        @if($employee->designation)
                        <span class="badge bg-primary me-2">{{ $employee->designation }}</span>
                        @endif
                        @if($employee->department)
                        <span class="badge bg-info">{{ $employee->department->name }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3 text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Clock In Time</h6>
                                    <p class="card-text fs-4">{{ $clock ? \Carbon\Carbon::parse($clock->clock_in_time)->format('h:i:s A') :'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3 text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Clock Out Time</h6>
                                    <p class="card-text fs-4">{{ $clock && $clock->clock_out_time ? \Carbon\Carbon::parse($clock->clock_out_time)->format('h:i:s A') :'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($clock && $clock->remarks)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light mb-3 text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Clock Out Remarks</h6>
                                    <p class="card-text">{{ $clock->remarks }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <h5 class="mb-3">Tasks Performed</h5>
                    @if($details->isEmpty())
                    <div class="alert alert-info" role="alert">
                        No tasks found for this employee on the selected date.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Task</th>
                                    <th>Total Time</th>
                                    <th>Breaks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $task)
                                <tr>
                                    <td>{{ $task['task_title'] }}</td>
                                    <td><span class="badge bg-secondary">{{ $task['total_time'] }}</span></td>
                                    <td>
                                        @if(count($task['breaks']) > 0)
                                        <ul class="list-unstyled mb-0">
                                            @foreach($task['breaks'] as $break)
                                            <li class="d-flex justify-content-between align-items-center">
                                                <span>{{ $break['start'] }} - {{ $break['end'] }}</span>
                                                <span class="badge bg-warning text-dark ms-2">{{ $break['duration'] }}</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @else
                                        <span class="text-muted">No breaks</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection