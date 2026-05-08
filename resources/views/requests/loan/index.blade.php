@extends('layouts.admin')

@section('title', 'Loan Requests')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    /* Custom Calendar Styles */
    #calendar {
        font-family: 'Montserrat', sans-serif;
        padding: 20px;
        min-height: 600px;
    }

    .fc {
        height: auto !important;
    }

    .fc-view-harness {
        height: auto !important;
    }

    .fc-daygrid-body {
        height: auto !important;
    }

    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 700;
        color: #2c323f;
    }

    .fc-button-primary {
        background-color: #7366ff !important;
        border-color: #7366ff !important;
        text-transform: capitalize;
        border-radius: 5px !important;
        font-weight: 500;
        padding: 8px 16px !important;
    }

    .fc-button-primary:hover,
    .fc-button-primary:active,
    .fc-button-primary.fc-button-active {
        background-color: #5e53d1 !important;
        border-color: #5e53d1 !important;
        box-shadow: none !important;
    }

    .fc-event {
        border: none !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        font-size: 0.85rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.12);
    }

    .fc-daygrid-day-number {
        font-weight: 600;
        color: #555;
        text-decoration: none !important;
        padding: 8px !important;
    }

    .fc-col-header-cell {
        background-color: #f8f9fa;
        padding: 15px 0 !important;
    }

    .fc-col-header-cell-cushion {
        color: #444;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        text-decoration: none !important;
    }

    .fc-day-today {
        background-color: rgba(115, 102, 255, 0.05) !important;
    }

    /* Soften grid lines */
    .fc-theme-standard td,
    .fc-theme-standard th {
        border-color: #eaeaea !important;
    }

    .fc-scrollgrid {
        border-color: #eaeaea !important;
        border-radius: 10px;
        overflow: hidden;
    }

    /* Responsive Calendar Toolbar */
    @media (max-width: 767.98px) {
        #calendar {
            padding: 10px;
            width: 100%;
            overflow-x: auto;
        }

        .fc .fc-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }

        .fc .fc-toolbar-title {
            font-size: 1.1rem !important;
            margin-bottom: 0.25rem;
        }

        .fc .fc-button {
            font-size: 0.7rem !important;
            padding: 0.35rem 0.6rem !important;
        }

        .fc-toolbar-chunk {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem;
        }

        .fc-daygrid-day-number {
            font-size: 0.75rem;
            padding: 4px !important;
        }

        .fc-col-header-cell-cushion {
            font-size: 0.65rem;
        }

        .fc-event {
            font-size: 0.7rem;
            padding: 2px 4px !important;
        }

        .btn-group {
            width: 100%;
        }

        .btn-group .btn {
            flex: 1;
            font-size: 0.85rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1>Loan Requests</h1>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs d-flex mt-4" id="loanRequestTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button" role="tab" aria-controls="calendar-view" aria-selected="true">Calendar</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-requests-tab" data-bs-toggle="tab" data-bs-target="#all-requests" type="button" role="tab" aria-controls="all-requests" aria-selected="false">All Loan Requests</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="my-requests-tab" data-bs-toggle="tab" data-bs-target="#my-requests" type="button" role="tab" aria-controls="my-requests" aria-selected="false">My Loan Requests</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="add-request-tab" data-bs-toggle="tab" data-bs-target="#add-request" type="button" role="tab" aria-controls="add-request" aria-selected="false">Create</button>
                </li>
            </ul>

            <div class="tab-content" id="loanRequestTabContent">
                <div class="tab-pane fade show active" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
                    <div class="card">
                        <div class="card-body position-relative">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="btn-group" role="group" aria-label="Calendar Filter">
                                    <input type="radio" class="btn-check" name="calendarFilter" id="filterAll" value="all" checked>
                                    <label class="btn btn-outline-primary" for="filterAll">All Loans</label>

                                    <input type="radio" class="btn-check" name="calendarFilter" id="filterMy" value="my">
                                    <label class="btn btn-outline-primary" for="filterMy">My Loans</label>
                                </div>
                            </div>
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="all-requests" role="tabpanel" aria-labelledby="all-requests-tab">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="employee_filter">Employee</label>
                            <select id="employee_filter" class="form-control">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="status_filter">Status</label>
                            <select id="status_filter" class="form-control">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="processed">Processed</option>
                                <option value="forwarded">Forwarded</option>
                                <option value="approved and forwarded">Approved and Forwarded</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date_filter">Date</label>
                            <input type="text" id="date_filter" class="form-control" placeholder="Select date range">
                            <input type="hidden" id="start_date_filter" name="start_date">
                            <input type="hidden" id="end_date_filter" name="end_date">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="all-loan-requests-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Requested On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data will be loaded via AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="my-requests" role="tabpanel" aria-labelledby="my-requests-tab">
                    <div class="table-responsive">
                        <table class="table" id="my-loan-requests-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Amount</th>
                                    <th>Requested On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data will be loaded via AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="add-request" role="tabpanel" aria-labelledby="add-request-tab">
                    <form id="createLoanRequestForm" action="{{ route('loan-requests.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0">
                                <div class="invalid-feedback" id="amount_error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Submit Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- View Loan Request Modal -->
<div class="modal fade" id="viewLoanRequestModal" tabindex="-1" aria-labelledby="viewLoanRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLoanRequestModalLabel">Loan Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>User:</strong> <span id="loan-user-name"></span></p>
                <p><strong>Amount:</strong> <span id="loan-amount"></span></p>
                <p><strong>Requested On:</strong> <span id="loan-requested-on"></span></p>
                <p><strong>Status:</strong> <span id="loan-status"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteLoanRequestModal" tabindex="-1" aria-labelledby="deleteLoanRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLoanRequestModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this loan request?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteLoanRequestBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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
                    url: '{{ route("loan-requests.calendar-events") }}',
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
                var iconClass = 'fa-hand-holding-usd'; // Default icon
                if (arg.event.extendedProps.status === 'approved') {
                    iconClass = 'fa-check';
                } else if (arg.event.extendedProps.status === 'rejected') {
                    iconClass = 'fa-times';
                }

                italic.className = 'fa ' + iconClass + ' me-1';

                let title = document.createElement('span');
                title.innerHTML = arg.event.title;

                let arrayOfDomNodes = [italic, title];
                return {
                    domNodes: arrayOfDomNodes
                };
            },
            eventClick: function(info) {
                var loanRequestId = info.event.id;
                // Open view modal
                let url = '{{ route("loan-requests.show", ["loan_request" => ":id"]) }}';
                url = url.replace(':id', loanRequestId);
                $.get(url, function(data) {
                    $('#loan-user-name').text(data.user.name || 'N/A');
                    $('#loan-amount').text(data.amount);
                    $('#loan-requested-on').text(new Date(data.requested_on).toLocaleDateString());
                    $('#loan-status').text(data.status);

                    $('#viewLoanRequestModal').modal('show');
                });
            }
        });

        // Render calendar immediately since it's the active tab on page load
        calendar.render();

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (e.target.id === 'calendar-tab') {
                calendar.render();
            }
        });

        $('input[name="calendarFilter"]').change(function() {
            var filter = $(this).val();
            // Update labels style
            if (filter === 'my') {
                $('label[for="filterMy"]').addClass('active');
                $('label[for="filterAll"]').removeClass('active');
            } else {
                $('label[for="filterAll"]').addClass('active');
                $('label[for="filterMy"]').removeClass('active');
            }
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

    function getStatusClasses(status) {
        switch (status) {
            case 'pending':
                return 'bg-warning text-dark';
            case 'approved':
                return 'bg-success text-white';
            case 'rejected':
                return 'bg-danger text-white';
            case 'processed':
                return 'bg-info text-white';
            case 'forwarded':
                return 'bg-primary text-white';
            case 'approved and forwarded':
                return 'bg-secondary text-white';
            default:
                return 'bg-secondary text-white';
        }
    }

    function showToast(message, type) {
        var toastContainer = $('#toast-container');
        if (toastContainer.length === 0) {
            toastContainer = $('<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>');
            $('body').append(toastContainer);
        }

        var toastClass = '';
        switch (type) {
            case 'success':
                toastClass = 'text-bg-success';
                break;
            case 'error':
            case 'danger':
                toastClass = 'text-bg-danger';
                break;
            case 'warning':
                toastClass = 'text-bg-warning';
                break;
            case 'info':
                toastClass = 'text-bg-info';
                break;
            default:
                toastClass = 'text-bg-primary';
        }

        var toastId = 'toast-' + Date.now();
        var toastHtml = `
            <div id="${toastId}" class="toast align-items-center ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        toastContainer.append(toastHtml);
        var toastEl = new bootstrap.Toast(document.getElementById(toastId));
        toastEl.show();
    }

    $(document).ready(function() {
        var allLoanRequestsTable, myLoanRequestsTable;

        // Function to reload only the visible DataTable
        function reloadLoanTables() {
            var activeTab = $('#loanRequestTabs button.active').attr('id');
            if (activeTab === 'all-requests-tab') {
                allLoanRequestsTable.draw();
            } else if (activeTab === 'my-requests-tab') {
                myLoanRequestsTable.draw();
            }
        }

        // Initialize tables once
        allLoanRequestsTable = $('#all-loan-requests-table').DataTable({
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
                url: "{{ route('loan-requests.index') }}",
                data: function(d) {
                    d.employee_id = $('#employee_filter').val();
                    d.status = $('#status_filter').val();
                    d.start_date = $('#start_date_filter').val();
                    d.end_date = $('#end_date_filter').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'user',
                    name: 'user.name',
                    orderable: true
                },
                {
                    data: 'amount',
                    name: 'amount',
                    orderable: true
                },
                {
                    data: 'requested_on',
                    name: 'requested_on',
                    orderable: true
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        var statuses = ['pending', 'approved', 'rejected', 'processed', 'forwarded', 'approved and forwarded'];
                        var options = '';
                        statuses.forEach(function(status) {
                            var selected = (status === data) ? 'selected' : '';
                            options += '<option value="' + status + '" ' + selected + '>' + ucfirst(status) + '</option>';
                        });

                        var select = '<select class="status-select form-control form-control-sm ' + getStatusClasses(data) + '" data-id="' + row.id + '" data-current-status="' + data + '">' + options + '</select>';

                        var forwardedInfo = '';
                        if (data === 'approved and forwarded' && row.forwarded_to_employee) {
                            forwardedInfo = '<div class="forwarded-employee-info mt-1 text-muted" style="font-size: 0.8em;">Forwarded to: <strong>' + row.forwarded_to_employee.name + '</strong>' +
                                (row.forwarded_to_employee.department_name ? ' <span class="badge bg-secondary">' + row.forwarded_to_employee.department_name + '</span>' : '') +
                                '</div>';
                        }

                        return select + forwardedInfo;
                    }
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        myLoanRequestsTable = $('#my-loan-requests-table').DataTable({
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
                url: "{{ route('loan-requests.index') }}",
                data: function(d) {
                    d.my_requests = true;
                    d.status = $('#status_filter').val();
                    d.start_date = $('#start_date_filter').val();
                    d.end_date = $('#end_date_filter').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'amount',
                    name: 'amount',
                    orderable: true
                },
                {
                    data: 'requested_on',
                    name: 'requested_on',
                    orderable: true
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        var statusClasses = getStatusClasses(data);
                        var statusHtml = '<span class="badge ' + statusClasses + '">' + ucfirst(data) + '</span>';
                        if (row.status === 'approved and forwarded' && row.forwarded_to_employee) {
                            statusHtml += '<br><small>(Forwarded to: ' + row.forwarded_to_employee.name + ' (' + row.forwarded_to_employee.department_name + '))</small>';
                        }
                        return statusHtml;
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

        // Style search inputs globally
        $('#all-loan-requests-table_filter input, #my-loan-requests-table_filter input').addClass('form-control').attr('placeholder', 'Search...').wrap('<div class="input-group"></div>').before('<span class="input-group-text"><i class="fas fa-search"></i></span>');

        $('#employee_filter').select2({
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
            dropdownParent: $('body')
        });

        $('#status_filter').select2({
            allowClear: true,
            minimumResultsForSearch: Infinity
        });

        reloadLoanTables(); // Trigger initial load with current filter values

        $('#date_filter').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        }, function(start, end, label) {
            $('#date_filter').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
            $('#start_date_filter').val(start.format('YYYY-MM-DD'));
            $('#end_date_filter').val(end.format('YYYY-MM-DD'));
            reloadLoanTables();
        });

        $('#date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#start_date_filter').val('');
            $('#end_date_filter').val('');
            reloadLoanTables();
        });

        $(document).on('change', '#employee_filter, #status_filter, #start_date_filter, #end_date_filter', function() {
            reloadLoanTables();
        });

        // Tab switching logic
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            reloadLoanTables();
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });

        // Create Form Submission
        $('#createLoanRequestForm').on('submit', function(e) {
            e.preventDefault();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            var formData = $(this).serialize();

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                success: function(response) {
                    showToast(response.message || 'Loan request submitted successfully.', 'success');
                    $('#createLoanRequestForm')[0].reset();
                    reloadLoanTables();
                    new bootstrap.Tab(document.getElementById('my-requests-tab')).show();
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            $('#' + key).addClass('is-invalid');
                            $('#' + key + '_error').text(value[0]);
                        });
                    } else {
                        showToast(xhr.responseJSON.message || 'Error submitting loan request.', 'danger');
                    }
                }
            });
        });

        // Delete Logic
        let loanRequestToDeleteId;
        $(document).on('click', '.delete-loan-request', function() {
            loanRequestToDeleteId = $(this).data('id');
            $('#deleteLoanRequestModal').modal('show');
        });

        $('#confirmDeleteLoanRequestBtn').on('click', function() {
            let url = '{{ route("loan-requests.destroy", ["loan_request" => ":id"]) }}';
            url = url.replace(':id', loanRequestToDeleteId);
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#deleteLoanRequestModal').modal('hide');
                    showToast('Loan request deleted successfully.', 'success');
                    reloadLoanTables();
                },
                error: function(response) {
                    showToast('Error deleting loan request.', 'danger');
                }
            });
        });

        // View Logic
        $(document).on('click', '.view-loan-request', function() {
            var loanRequestId = $(this).data('id');
            let url = '{{ route("loan-requests.show", ["loan_request" => ":id"]) }}';
            url = url.replace(':id', loanRequestId);
            $.get(url, function(data) {
                $('#loan-user-name').text(data.user.name || 'N/A');
                $('#loan-amount').text(data.amount);
                $('#loan-requested-on').text(new Date(data.requested_on).toLocaleDateString());
                $('#loan-status').text(data.status);
                $('#viewLoanRequestModal').modal('show');
            });
        });

        // Status Change Logic
        $(document).on('change', '.status-select', function() {
            var loanRequestId = $(this).data('id');
            var newStatus = $(this).val();
            var $selectElement = $(this);
            var originalStatus = $selectElement.data('current-status');

            if (newStatus === 'approved and forwarded') {
                var $statusTd = $selectElement.closest('td');

                // Check if the forwarding UI already exists for this row
                if ($statusTd.find('.forwarding-ui').length) {
                    return; // Already showing, do nothing
                }

                var uniqueSelectId = 'forward-to-employee-select-' + loanRequestId;
                var uniqueConfirmBtnId = 'confirm-forward-btn-' + loanRequestId;
                var uniqueCancelBtnId = 'cancel-forward-btn-' + loanRequestId;

                var forwardingUiHtml = `
                    <div class="forwarding-ui mt-2" style="display:none;">
                        <select class="form-control form-control-sm mb-1" id="${uniqueSelectId}" style="width: 100%;">
                            <option></option>
                        </select>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-primary btn-sm" id="${uniqueConfirmBtnId}">Confirm</button>
                            <button type="button" class="btn btn-secondary btn-sm" id="${uniqueCancelBtnId}">Cancel</button>
                        </div>
                    </div>
                `;

                $statusTd.append(forwardingUiHtml);
                $statusTd.find('.forwarding-ui').slideDown();

                // Initialize Select2 for the new dropdown
                $('#' + uniqueSelectId).select2({
                    placeholder: 'Select Employee',
                    allowClear: true,
                    dropdownParent: $statusTd, // Important for in-line Select2
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
                        var $container = $(
                            '<div class="select2-result-employee clearfix">' +
                            '<div class="select2-result-employee__title"></div>' +
                            '<div class="select2-result-employee__department"></div>' +
                            '</div>'
                        );
                        $container.find('.select2-result-employee__title').text(employee.text);
                        if (employee.department && employee.department.name) {
                            $container.find('.select2-result-employee__department').html('<span class="badge bg-secondary">' + employee.department.name + '</span>');
                        }
                        return $container;
                    },
                    templateSelection: function(employee) {
                        if (employee.text) {
                            var deptBadge = (employee.department && employee.department.name) ? ' <span class="badge bg-secondary">' + employee.department.name + '</span>' : '';
                            return employee.text + deptBadge;
                        }
                        return employee.name || employee.text;
                    },
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });

                // Handle Confirm Forward button click
                $(document).on('click', '#' + uniqueConfirmBtnId, function() {
                    var forwardedToEmployeeId = $('#' + uniqueSelectId).val();
                    var selectedEmployeeData = $('#' + uniqueSelectId).select2('data')[0];
                    var forwardedEmployeeName = selectedEmployeeData ? selectedEmployeeData.text : 'N/A';
                    var forwardedEmployeeDepartment = (selectedEmployeeData && selectedEmployeeData.department) ? selectedEmployeeData.department.name : '';

                    if (!forwardedToEmployeeId) {
                        showToast('Please select an employee to forward to.', 'warning');
                        return;
                    }

                    $.ajax({
                        url: '{{ route("loan-requests.changeStatus", ["loan_request" => ":id"]) }}'.replace(':id', loanRequestId),
                        method: 'POST',
                        data: {
                            status: 'approved and forwarded',
                            forwarded_to_employee_id: forwardedToEmployeeId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            showToast(response.message || 'Loan request forwarded successfully.', 'success');

                            // Dynamically update the status dropdown
                            $selectElement.val('approved and forwarded');
                            $selectElement.removeClass().addClass('status-select form-control form-control-sm'); // Reset classes
                            $selectElement.addClass(getStatusClasses('approved and forwarded'));
                            $selectElement.data('current-status', 'approved and forwarded'); // Update data attribute

                            // Clean up the dynamic UI and then display forwarded employee info
                            $statusTd.find('.forwarding-ui').slideUp(function() {
                                $(this).remove();
                                // Display forwarded employee info
                                var forwardedInfoHtml = `
                                    <div class="forwarded-employee-info mt-1 text-muted" style="font-size: 0.8em;">
                                        Forwarded to: <strong>${forwardedEmployeeName}</strong>
                                        ${forwardedEmployeeDepartment ? `<span class="badge bg-secondary">${forwardedEmployeeDepartment}</span>` : ''}
                                    </div>
                                `;
                                $statusTd.append(forwardedInfoHtml);
                            });
                            $('#' + uniqueSelectId).select2('destroy'); // Destroy Select2 instance

                            reloadLoanTables();
                        },
                        error: function(xhr) {
                            showToast('Error forwarding loan request: ' + (xhr.responseJSON.message || ''), 'danger');
                            // Revert UI on error
                            $statusTd.find('.forwarding-ui').slideUp(function() {
                                $(this).remove();
                            });
                            $('#' + uniqueSelectId).select2('destroy'); // Destroy Select2 instance
                            $selectElement.val(originalStatus); // Revert status dropdown
                            $selectElement.addClass(getStatusClasses(originalStatus));
                        }
                    });
                });

                // Handle Cancel Forward button click
                $(document).on('click', '#' + uniqueCancelBtnId, function() {
                    // Revert status dropdown to original value
                    $selectElement.val(originalStatus);
                    $selectElement.addClass(getStatusClasses(originalStatus));
                    // Remove the dynamic UI
                    $statusTd.find('.forwarding-ui').slideUp(function() {
                        $(this).remove();
                    });
                    $('#' + uniqueSelectId).select2('destroy'); // Destroy Select2 instance
                });

                return; // Exit the function, don't proceed with AJAX status update yet
            }

            $.ajax({
                url: '{{ route("loan-requests.changeStatus", ["loan_request" => ":id"]) }}'.replace(':id', loanRequestId),
                method: 'POST',
                data: {
                    status: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    var allStatusClasses = [
                        'bg-warning text-dark', 'bg-success text-white', 'bg-danger text-white',
                        'bg-info text-white', 'bg-primary text-white', 'bg-secondary text-white'
                    ];
                    $.each(allStatusClasses, function(index, className) {
                        $selectElement.removeClass(className);
                    });
                    $selectElement.addClass(getStatusClasses(newStatus));
                    $selectElement.data('current-status', newStatus);
                    showToast(response.message, 'success');
                    reloadLoanTables();
                },
                error: function(xhr) {
                    showToast('Error updating status: ' + (xhr.responseJSON.message || ''), 'danger');
                    $selectElement.val(originalStatus);
                    var allStatusClasses = [
                        'bg-warning text-dark', 'bg-success text-white', 'bg-danger text-white',
                        'bg-info text-white', 'bg-primary text-white', 'bg-secondary text-white'
                    ];
                    $.each(allStatusClasses, function(index, className) {
                        $selectElement.removeClass(className);
                    });
                    $selectElement.addClass(getStatusClasses(originalStatus));
                }
            });
        });
    });
</script>
@endpush