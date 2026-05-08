@extends('layouts.admin')

@section('title', 'Task Reports')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Task Reports</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">Reports</li>
                    <li class="breadcrumb-item active">Task Reports</li>
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
                <div class="card-header">
                    <h5>Filter Tasks</h5>
                </div>
                <div class="card-body">
                    <form id="filter-form" class="row">
                        <div class="col-md-2 mb-3">
                            <label for="date_period">Date Period</label>
                            <input type="text" id="date_period" class="form-control" placeholder="Select Date Range">
                            <input type="hidden" id="start_date" name="start_date">
                            <input type="hidden" id="end_date" name="end_date">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="dealership_id">Dealership</label>
                            <select id="dealership_id" name="dealership_id" class="form-control select2" @if(!$canViewSubordinates || $userDealershipId) disabled @endif>
                                <option value="">All Dealerships</option>
                                @foreach($dealerships as $dealer)
                                    <option value="{{ $dealer->id }}" @if(($userDealershipId ?: null) == $dealer->id) selected @endif>{{ $dealer->name }}</option>
                                @endforeach
                            </select>
                            @if(!$canViewSubordinates || $userDealershipId)
                                <input type="hidden" name="dealership_id" value="{{ $userDealershipId }}">
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="department_id">Department</label>
                            <select id="department_id" name="department_id" class="form-control select2" @if(!$canViewSubordinates || $userDepartmentId) disabled @endif>
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @if(($userDepartmentId ?: null) == $dept->id) selected @endif>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @if(!$canViewSubordinates || $userDepartmentId)
                                <input type="hidden" name="department_id" value="{{ $userDepartmentId }}">
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="employee_id">Employee</label>
                            <select id="employee_id" name="employee_id" class="form-control select2" @if(!$canViewSubordinates) disabled @endif>
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
                        <div class="col-md-2 mb-3">
                            <label for="task_type">Task Type</label>
                            <select id="task_type" name="task_type" class="form-control select2">
                                <option value="">All Types</option>
                                <option value="leads">Leads</option>
                                <option value="service">Service</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" id="reset-btn" class="btn btn-danger text-white">Reset</button>
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <button type="button" id="export-excel-btn" class="btn btn-success">Export Excel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Task Report Results</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display" id="task-report-table">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Dealership</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Follow-ups</th>
                                    <th>Elapsed Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    table.dataTable thead th {
        font-size: 0.85rem !important;
        padding: 10px 8px !important;
    }
    .pulse-small {
        animation: pulse-small-animation 2s infinite;
    }
    @keyframes pulse-small-animation {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(0.95); }
        100% { opacity: 1; transform: scale(1); }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#date_period').daterangepicker({
            autoUpdateInput: false,
            applyButtonClasses: 'btn-primary',
            cancelButtonClasses: 'btn-danger',
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
            }
        });

        $('#date_period').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            table.ajax.reload();
        });

        $('#date_period').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#start_date').val('');
            $('#end_date').val('');
            table.ajax.reload();
        });

        var table = $('#task-report-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('task-reports.data') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.dealership_id = $('#dealership_id').val();
                    d.department_id = $('#department_id').val();
                    d.employee_id = $('#employee_id').val();
                    d.task_type = $('#task_type').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'date', name: 'created_at' },
                { data: 'title', name: 'title' },
                { data: 'client_name', name: 'client_name', orderable: false, searchable: false },
                { data: 'task_type_label', name: 'type', orderable: false, searchable: false },
                { data: 'dealership_name', name: 'dealership.name' },
                { data: 'employee_name', name: 'assignedEmployee.name' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data, type, row) {
                        var badgeClass = 'bg-light text-dark';
                        var status = data.toLowerCase();
                        
                        if (status === 'ongoing') badgeClass = 'bg-primary';
                        else if (status.includes('settled')) badgeClass = 'bg-success';
                        else if (status.includes('approved')) badgeClass = 'bg-success';
                        else if (status.includes('awaiting approval')) badgeClass = 'bg-warning';
                        else if (status.includes('rejected')) badgeClass = 'bg-danger';
                        else if (status === 'pending') badgeClass = 'bg-warning';
                        else if (status === 'hold') badgeClass = 'bg-secondary';
                        else if (status.startsWith('completed')) badgeClass = 'bg-success';
                        else if (status === 'cancelled') badgeClass = 'bg-danger';
                        
                        return '<span class="badge ' + badgeClass + '">' + data.toUpperCase() + '</span>';
                    }
                },
                { data: 'followups', name: 'followups', orderable: false, searchable: false },
                { data: 'formatted_elapsed_time', name: 'total_elapsed_time', orderable: false, searchable: false },
                { 
                    data: 'id', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        return '<a href="/task-reports/' + data + '" class="btn btn-xs btn-outline-primary"><i class="fa fa-eye"></i> View</a>';
                    }
                }
            ],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'csv', className: 'btn btn-sm btn-primary text-white' },
                { extend: 'excel', className: 'btn btn-sm btn-success text-white' },
                { extend: 'pdf', className: 'btn btn-sm btn-danger text-white' },
                { extend: 'print', className: 'btn btn-sm btn-info text-white' }
            ]
        });

        // Auto-reload table on filter change
        $('#dealership_id, #department_id, #employee_id, #task_type').on('change', function() {
            table.ajax.reload();
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        $('#reset-btn').on('click', function() {
            $('#filter-form')[0].reset();
            $('#start_date').val('');
            $('#end_date').val('');
            $('#date_period').val('');
            $('.select2').val('').trigger('change');
            table.ajax.reload();
        });

        $('#export-excel-btn').on('click', function() {
            var query = $('#filter-form').serialize();
            window.location.href = "{{ route('task-reports.export-excel') }}?" + query;
        });
    });
</script>
@endpush
