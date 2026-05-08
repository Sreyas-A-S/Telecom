@extends('layouts.admin')

@section('title', 'Attendance Calendar')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    .fc-toolbar-title {
        font-size: 1.25rem !important;
    }

    .fc-event {
        cursor: pointer;
    }

    @media (max-width: 767.98px) {
        .fc .fc-toolbar {
            flex-direction: column;
            gap: 10px;
        }
        
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .fc .fc-button {
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
        }
    }
</style>
@endpush

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Attendance Calendar</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">HR</li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Calendar</li>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Attendance & Leaves Calendar</h5>
                    <a href="{{ route('attendance.index') }}" class="btn btn-primary">List View</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="dealership-filter">Dealership</label>
                            <select id="dealership-filter" class="form-control select2" @if(!$canViewSubordinates || ($userDealershipId && !$canViewAllAttendance)) disabled @endif>
                                <option value="">All Dealerships</option>
                                @foreach($dealerships as $dealership)
                                    <option value="{{ $dealership->id }}" @if(($userDealershipId ?: null) == $dealership->id) selected @endif>{{ $dealership->name }}</option>
                                @endforeach
                            </select>
                            @if(!$canViewSubordinates || ($userDealershipId && !$canViewAllAttendance))
                                <input type="hidden" name="dealership_id" value="{{ $userDealershipId }}">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label for="department-filter">Department</label>
                            <select id="department-filter" class="form-control select2" @if(!$canViewSubordinates || ($userDepartmentId && !$canViewAllAttendance)) disabled @endif>
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @if(($userDepartmentId ?: null) == $department->id) selected @endif>{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @if(!$canViewSubordinates || ($userDepartmentId && !$canViewAllAttendance))
                                <input type="hidden" name="department_id" value="{{ $userDepartmentId }}">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label for="employee-filter">Employee Filter</label>
                            <select id="employee-filter" class="form-control select2" @if(!$canViewSubordinates) disabled @endif>
                                @if($canViewSubordinates)
                                    <option value="">All Employees</option>
                                @endif
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @if(!$canViewSubordinates) selected @endif>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            @if(!$canViewSubordinates)
                                <input type="hidden" name="employee_id" value="{{ $employees[0]->id ?? '' }}">
                            @endif
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        function getHeaderToolbar() {
            if (window.innerWidth < 768) {
                return {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                };
            }
            return {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            };
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: getHeaderToolbar(),
            windowResize: function(view) {
                calendar.setOption('headerToolbar', getHeaderToolbar());
            },
            events: {
                url: "{{ route('attendance.calendar-events') }}",
                extraParams: function() {
                    return {
                        employee_id: $('#employee-filter').val(),
                        dealership_id: $('#dealership-filter').val(),
                        department_id: $('#department-filter').val()
                    };
                }
            },
            eventTimeFormat: { // like '14:30:00'
                hour: '2-digit',
                minute: '2-digit',
                meridiem: 'short'
            },
            eventDidMount: function(info) {
                if (info.event.extendedProps.description) {
                    info.el.title = info.event.title + '\n' + info.event.extendedProps.description;
                } else {
                    info.el.title = info.event.title;
                }
            }
        });
        calendar.render();

        $('.select2').select2();
        
        $('#employee-filter, #dealership-filter, #department-filter').on('change', function() {
            calendar.refetchEvents();
        });
    });
</script>
@endpush
