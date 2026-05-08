@extends('layouts.admin')

@section('title', 'Expense Requests')

@section('content')
<div class="container">
    <h1>Expense Request Management</h1>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs d-flex mt-4" id="myTab" role="tablist">
                @if(Auth::user()->user_type === 'admin' || (Auth::user()->employee && Auth::user()->employee->is_manager))
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ !session('active_tab') || session('active_tab') =='all'?'active':'' }}" id="all-requests-tab" data-bs-toggle="tab" data-bs-target="#all-requests" type="button" role="tab" aria-controls="all-requests" aria-selected="{{ !session('active_tab') || session('active_tab') =='all'?'true':'false' }}">All Expense Requests</button>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ session('active_tab') =='my'?'active':'' }}" id="my-requests-tab" data-bs-toggle="tab" data-bs-target="#my-requests" type="button" role="tab" aria-controls="my-requests" aria-selected="{{ session('active_tab') =='my'?'true':'false' }}">My Expense Requests</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button" role="tab" aria-controls="calendar-view" aria-selected="false">Calendar</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab" aria-controls="create" aria-selected="false">Create</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
                    <div class="row mb-3 mt-3">
                        <div class="col-md-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="calendarFilter" id="calendarFilterAll" value="all" checked>
                                <label class="form-check-label" for="calendarFilterAll">All Expenses</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="calendarFilter" id="calendarFilterMy" value="my">
                                <label class="form-check-label" for="calendarFilterMy">My Expenses</label>
                            </div>
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>
                <div class="tab-pane fade {{ session('active_tab') =='my'?'show active':'' }}" id="my-requests" role="tabpanel" aria-labelledby="my-requests-tab">
                    <table class="table m-2" id="my-expense-requests-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Expense Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                    <form id="createExpenseRequestForm" action="{{ route('expense-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expense_type">Expense Type</label>
                                    <select name="expense_type" id="expense_type" class="form-control">
                                        <option value="travel">Travel</option>
                                        <option value="food">Food</option>
                                        <option value="accommodation">Accommodation</option>
                                        <option value="miscellaneous">Miscellaneous</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="image">Image</label>
                                    <input type="file" name="image" id="image" class="form-control">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Submit Expense Request</button>
                    </form>
                </div>

                @if(Auth::user()->user_type === 'admin' || (Auth::user()->employee && Auth::user()->employee->is_manager))
                <div class="tab-pane fade {{ !session('active_tab') || session('active_tab') =='all'?'show active':'' }}" id="all-requests" role="tabpanel" aria-labelledby="all-requests-tab">
                    <table class="table" id="all-expense-requests-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Expense Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>


{{-- Modal for Changing Status --}}
<div class="modal fade" id="changeStatusExpenseRequestModal" tabindex="-1" aria-labelledby="changeStatusExpenseRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="changeStatusExpenseRequestForm" method="POST">
            @csrf
            {{-- @method('PUT') --}}
            {{-- Form action is set via JS --}}

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusExpenseRequestModalLabel">Change Request Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="change-status-expense-request-id">

                    <div class="mb-3">
                        <label for="change_status" class="form-label">Status</label>
                        <select name="status" id="change_status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="processed">Processed</option>
                            <option value="approved and forwarded">Approved and Forwarded</option>
                        </select>
                    </div>

                    <div class="mb-3" id="approved-amount-wrapper" style="display: none;">
                        <label for="approved_amount" class="form-label">Approved Amount</label>
                        <input type="number" name="approved_amount" id="approved_amount" class="form-control" step="0.01" min="0">
                        <small class="text-muted">Requested Amount: <span id="original-requested-amount" class="fw-bold"></span></small>
                    </div>

                    <div class="mb-3" id="forward-to-wrapper" style="display: none;">
                        <label for="forwarded_to_employee_id" class="form-label">Forward To</label>
                        <select name="forwarded_to_employee_id" id="forwarded_to_employee_id" class="form-control" style="width: 100%;">
                            {{-- Options populated by JS or Select2 --}}
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    .fc-event {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                var filter = $('input[name="calendarFilter"]:checked').val();
                var params = {
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr
                };
                if (filter === 'my') {
                    params.my_requests = true;
                }

                $.ajax({
                    url: '{{ route("expense-requests.calendar-events") }}',
                    data: params,
                    success: function(events) {
                        successCallback(events);
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },
            eventContent: function(arg) {
                let italic = document.createElement('i');
                var iconClass = 'fa-money-bill-wave'; // Default icon
                if (arg.event.extendedProps.status === 'approved') {
                    iconClass = 'fa-check';
                } else if (arg.event.extendedProps.status === 'rejected') {
                    iconClass = 'fa-times';
                }

                // Customize icon based on type if needed
                if (arg.event.title.toLowerCase().includes('travel')) iconClass = 'fa-plane';
                if (arg.event.title.toLowerCase().includes('food')) iconClass = 'fa-utensils';

                italic.className = 'fa ' + iconClass + ' me-1';

                let title = document.createElement('span');
                title.innerHTML = arg.event.title;

                let arrayOfDomNodes = [italic, title];
                return {
                    domNodes: arrayOfDomNodes
                };
            },
            eventClick: function(info) {
                var expenseRequestId = info.event.id;
                // Open view modal
                let url = '{{ route("expense-requests.show", ["expense_request" => ":id"]) }}';
                url = url.replace(':id', expenseRequestId);
                $.get(url, function(data) {
                    $('#expense-user-name').text(data.user.name || 'N/A');
                    $('#expense-reporting-to-name').text(data.user.employee && data.user.employee.reporting_to ? data.user.employee.reporting_to.name : 'N/A');
                    $('#expense-type').text(data.expense_type);
                    $('#expense-amount').text(data.amount);
                    $('#expense-status').text(data.status);
                    $('#expense-description').text(data.description || 'N/A');
                    $('#expense-image').html(data.image ? '<a href="/storage/' + data.image + '" target="_blank">View Image</a>' : 'N/A');
                    $('#expense-created-at').text(new Date(data.created_at).toLocaleString());

                    $('#viewExpenseRequestModal').modal('show');
                });
            }
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (e.target.id === 'calendar-tab') {
                calendar.render();
            }
        });

        $('input[name="calendarFilter"]').change(function() {
            calendar.refetchEvents();
        });
    });
</script>

<script>
    // JavaScript equivalent of PHP's ucfirst
    function ucfirst(str) {
        if (typeof str !== 'string' || str.length === 0) {
            return '';
        }
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    $(document).ready(function() {
        var allExpenseRequestsTable, myExpenseRequestsTable;

        function initializeFilters(api, filterHtml) {
            $('.filter-container', api.table().container()).html(filterHtml);

            $('#filter_employee').select2({
                placeholder: 'Select an Employee',
                allowClear: true,
                ajax: {
                    url: "{{ route('employees.searchEmployee') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data, params) {

                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: data.pagination
                        };
                    },
                    cache: true
                },
                templateResult: function(employee) {
                    if (employee.loading) return employee.text;
                    return employee.text;
                },
                templateSelection: function(employee) {
                    return employee.text;
                }
            });

            $('#filter_expense_type, #filter_status').select2({
                allowClear: true,
                minimumResultsForSearch: Infinity
            });

            $('#forwarded_to_employee_id').select2({
                dropdownParent: $('#changeStatusExpenseRequestModal'),
                placeholder: 'Select an Employee',
                allowClear: true,
                ajax: {
                    url: "{{ route('employees.searchEmployee') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: data.pagination
                        };
                    },
                    cache: true
                }
            });

            $('#filter_date_range').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            }, function(start, end, label) {
                $('#filter_start_date').val(start.format('YYYY-MM-DD'));
                $('#filter_end_date').val(end.format('YYYY-MM-DD'));
                $('#filter_date_range').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
                api.ajax.reload();
            });

            $('#filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                api.ajax.reload();
            });

            $(document).on('change', '#filter_employee, #filter_expense_type, #filter_status', function() {
                api.ajax.reload();
            });
        }

        function initializeAllExpenseRequestsTable() {
            if ($.fn.DataTable.isDataTable('#all-expense-requests-table')) {
                $('#all-expense-requests-table').DataTable().destroy();
            }
            allExpenseRequestsTable = $('#all-expense-requests-table').DataTable({
                processing: true,
                serverSide: true,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                        extend: 'csv',
                        className: 'btn btn-sm btn-primary text-white'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-success text-white'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-danger text-white'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-info text-white'
                    }
                ],
                ajax: {
                    url: "{{ route('expense-requests.index') }}",
                    data: function(d) {
                        d.employee_id = $('#filter_employee').val();
                        d.expense_type = $('#filter_expense_type').val();
                        d.status = $('#filter_status').val();
                        d.start_date = $('#filter_start_date').val();
                        d.end_date = $('#filter_end_date').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'user.name',
                        name: 'user.name'
                    },
                    {
                        data: 'expense_type',
                        name: 'expense_type'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            var statusClass = 'bg-secondary';
                            if (data == 'approved') statusClass = 'bg-success';
                            else if (data == 'rejected') statusClass = 'bg-danger';
                            else if (data == 'pending') statusClass = 'bg-warning';
                            else if (data == 'processed') statusClass = 'bg-info';
                            return ucfirst(data);
                        }
                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return data ? '<a href="/storage/' + data + '" target="_blank">View</a>' : 'N/A';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                initComplete: function() {
                    var api = this.api();
                    var filterHtml = `
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-3">
                                <label for="filter_employee" class="form-label">Employee</label>
                                <select class="form-control" id="filter_employee" name="employee_id"><option value="">All Employees</option></select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_expense_type" class="form-label">Expense Type</label>
                                <select class="form-control" id="filter_expense_type" name="expense_type">
                                    <option value="">All Types</option>
                                    <option value="travel">Travel</option>
                                    <option value="food">Food</option>
                                    <option value="accommodation">Accommodation</option>
                                    <option value="miscellaneous">Miscellaneous</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select class="form-control" id="filter_status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="processed">Processed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_date_range" class="form-label">Date Range</label>
                                <input type="text" class="form-control" id="filter_date_range" name="date_range">
                                <input type="hidden" name="start_date" id="filter_start_date">
                                <input type="hidden" name="end_date" id="filter_end_date">
                            </div>
                        </div>
                    `;
                    initializeFilters(api, filterHtml);
                    $('#all-expense-requests-table_filter input').addClass('form-control').attr('placeholder', 'Search...').wrap('<div class="input-group"></div>').before('<span class="input-group-text"><i class="fas fa-search"></i></span>');
                }
            });
        }

        function initializeMyExpenseRequestsTable() {
            if ($.fn.DataTable.isDataTable('#my-expense-requests-table')) {
                $('#my-expense-requests-table').DataTable().destroy();
            }
            myExpenseRequestsTable = $('#my-expense-requests-table').DataTable({
                processing: true,
                serverSide: true,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                        extend: 'csv',
                        className: 'btn btn-sm btn-primary text-white'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-success text-white'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-danger text-white'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-info text-white'
                    }
                ],
                language: {
                    search: '<div class="input-group"><span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>_INPUT_</div>',
                    searchPlaceholder: "Search..."
                },
                ajax: {
                    url: "{{ route('expense-requests.index') }}",
                    data: {
                        my_requests: true
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'expense_type',
                        name: 'expense_type'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            var statusClass = 'bg-secondary';
                            if (data == 'approved') statusClass = 'bg-success';
                            else if (data == 'rejected') statusClass = 'bg-danger';
                            else if (data == 'pending') statusClass = 'bg-warning';
                            else if (data == 'processed') statusClass = 'bg-info';
                            return ucfirst(data);
                        }
                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return data ? '<a href="/storage/' + data + '" target="_blank">View</a>' : 'N/A';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        }

        @if(Auth::user()->user_type === 'admin' || (Auth::user()->employee && Auth::user()->employee->is_manager))
        initializeAllExpenseRequestsTable();
        @endif

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).data('bs-target');
            if (target === '#all-requests') {
                initializeAllExpenseRequestsTable();
            } else if (target === '#my-requests') {
                initializeMyExpenseRequestsTable();
            }
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });
    });

    let expenseRequestToDeleteId;

    $(document).on('click', '.delete-expense-request', function() {
        expenseRequestToDeleteId = $(this).data('id');
    });

    $('#confirmDeleteExpenseRequestBtn').on('click', function() {
        let url = '{{ route("expense-requests.destroy", ["expense_request" => ":id"]) }}';
        url = url.replace(':id', expenseRequestToDeleteId);
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _method: 'DELETE',
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#deleteExpenseRequestModal').modal('hide');
                toastr.success('Expense request deleted successfully.');
                if (allExpenseRequestsTable) allExpenseRequestsTable.ajax.reload();
                if (myExpenseRequestsTable) myExpenseRequestsTable.ajax.reload();
            },
            error: function(response) {
                handleAjaxError(response, 'Error deleting expense request.');
            }
        });
    });

    $(document).on('click', '.change-status-expense-request', function() {
        let expenseRequestId = $(this).data('id');
        let url = '{{ route("expense-requests.show", ["expense_request" => ":id"]) }}';
        url = url.replace(':id', expenseRequestId);
        $.get(url, function(data) {
            $('#change-status-expense-request-id').val(data.id);
            $('#change_status').val(data.status); // Don't trigger change yet

            $('#original-requested-amount').text(data.amount);
            if (data.approved_amount) {
                $('#approved_amount').val(data.approved_amount);
            } else {
                $('#approved_amount').val(data.amount);
            }

            // Trigger change to update UI
            $('#change_status').trigger('change');

            let updateStatusUrl = '{{ route("expense-requests.changeStatus", ["expense_request" => ":id"]) }}';
            updateStatusUrl = updateStatusUrl.replace(':id', expenseRequestId);
            $('#changeStatusExpenseRequestForm').attr('action', updateStatusUrl);
        });
    });

    $('#change_status').on('change', function() {
        let status = $(this).val();
        if (status === 'approved' || status === 'approved and forwarded') {
            $('#approved-amount-wrapper').fadeIn();
            $('#approved_amount').prop('required', true);
        } else {
            $('#approved-amount-wrapper').fadeOut();
            $('#approved_amount').prop('required', false);
        }

        if (status === 'approved and forwarded') {
            $('#forward-to-wrapper').fadeIn();
        } else {
            $('#forward-to-wrapper').fadeOut();
        }
    });


    $('#changeStatusExpenseRequestForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#changeStatusExpenseRequestModal').modal('hide');
                toastr.success(response.message);
                if (allExpenseRequestsTable) allExpenseRequestsTable.ajax.reload();
                if (myExpenseRequestsTable) myExpenseRequestsTable.ajax.reload();
            },
            error: function(response) {
                handleAjaxError(response, 'Error changing expense request status.');
            }
        });
    });

    $(document).on('click', '.view-expense-request', function() {
        var expenseRequestId = $(this).data('id');
        let url = '{{ route("expense-requests.show", ["expense_request" => ":id"]) }}';
        url = url.replace(':id', expenseRequestId);
        $.get(url, function(data) {
            $('#expense-user-name').text(data.user.name || 'N/A');
            $('#expense-reporting-to-name').text(data.user.employee && data.user.employee.reporting_to ? data.user.employee.reporting_to.name : 'N/A');
            $('#expense-type').text(data.expense_type);
            $('#expense-amount').text(data.amount);
            $('#expense-status').text(data.status);
            $('#expense-image').html(data.image ? '<a href="/storage/' + data.image + '" target="_blank">View Image</a>' : 'N/A');
            $('#expense-created-at').text(new Date(data.created_at).toLocaleString());
        });
    });

    // Handle Create Expense Request Form Submission via AJAX
    $('#createExpenseRequestForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.form-group .is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success(response.message || 'Expense request created successfully.');
                $('#createExpenseRequestForm')[0].reset(); // Reset the form
                if (allExpenseRequestsTable) allExpenseRequestsTable.ajax.reload(); // Reload DataTables
                if (myExpenseRequestsTable) myExpenseRequestsTable.ajax.reload(); // Reload DataTables
                // Optionally switch to the All Expense Requests tab if admin/manager, else to My Expense Requests tab
                @if(Auth::user()->user_type === 'admin' || (Auth::user()->employee && Auth::user()->employee->is_manager))
                new bootstrap.Tab(document.getElementById('all-requests-tab')).show();
                @else
                new bootstrap.Tab(document.getElementById('my-requests-tab')).show();
                @endif
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function(key, value) {
                        var inputElement = $('#' + key);
                        inputElement.addClass('is-invalid');
                        inputElement.after('<div class="invalid-feedback">' + value[0] + '</div>');
                    });
                } else {
                    toastr.error(xhr.responseJSON.message || 'Error creating expense request.');
                }
            }
        });
    });

    function handleAjaxError(response, defaultMessage) {
        let errorMessage = defaultMessage;
        if (response.responseJSON && response.responseJSON.message) {
            errorMessage = response.responseJSON.message;
        } else if (response.responseText) {
            try {
                const parsed = JSON.parse(response.responseText);
                if (parsed.message) errorMessage = parsed.message;
            } catch (e) {
                // Not a JSON response
            }
        }
        toastr.error(errorMessage);
    }
</script>
@endpush