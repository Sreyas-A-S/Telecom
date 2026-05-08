@extends('layouts.admin')

@section('title', 'Expense Requests')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
    <h1>Expense Requests</h1>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs d-flex mt-4" id="expenseRequestTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button" role="tab" aria-controls="calendar-view" aria-selected="true">Calendar</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-requests-tab" data-bs-toggle="tab" data-bs-target="#all-requests" type="button" role="tab" aria-controls="all-requests" aria-selected="false">All Expense Requests</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="my-requests-tab" data-bs-toggle="tab" data-bs-target="#my-requests" type="button" role="tab" aria-controls="my-requests" aria-selected="false">My Expense Requests</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="add-request-tab" data-bs-toggle="tab" data-bs-target="#add-request" type="button" role="tab" aria-controls="add-request" aria-selected="false">Create</button>
                </li>
            </ul>

            <div class="tab-content" id="expenseRequestTabContent">
                <div class="tab-pane fade show active" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
                    <div class="card">
                        <div class="card-body position-relative">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="btn-group" role="group" aria-label="Calendar Filter">
                                    <input type="radio" class="btn-check" name="calendarFilter" id="filterAll" value="all">
                                    <label class="btn btn-outline-primary" for="filterAll">All Expenses</label>

                                    <input type="radio" class="btn-check" name="calendarFilter" id="filterMy" value="my" checked>
                                    <label class="btn btn-outline-primary active" for="filterMy">My Expenses</label>
                                </div>
                            </div>
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="all-requests" role="tabpanel" aria-labelledby="all-requests-tab">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="employee_filter">Employee</label>
                            <select id="employee_filter" class="form-control">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="expense_type_filter">Expense Type</label>
                            <select id="expense_type_filter" class="form-control">
                                <option value="">All</option>
                                <option value="travel">Travel</option>
                                <option value="food">Food</option>
                                <option value="accommodation">Accommodation</option>
                                <option value="miscellaneous">Miscellaneous</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status_filter">Status</label>
                            <select id="status_filter" class="form-control">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="processed">Processed</option>
                                <option value="approved and forwarded">Approved and Forwarded</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_filter">Date</label>
                            <input type="text" id="date_filter" class="form-control" placeholder="Select date range">
                            <input type="hidden" id="start_date_filter" name="start_date">
                            <input type="hidden" id="end_date_filter" name="end_date">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#legacyReportModal">
                                <i class="fas fa-file-pdf me-1"></i> Legacy Weekly Report
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="all-expense-requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Expense Type</th>
                                    <th>Amount</th>
                                    <th>Approved Amount</th>
                                    <th>Date</th>
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
                        <table class="table" id="my-expense-requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Expense Type</th>
                                    <th>Amount</th>
                                    <th>Approved Amount</th>
                                    <th>Date</th>
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
                <div class="tab-pane fade" id="add-request" role="tabpanel" aria-labelledby="add-request-tab">
                    <form id="createExpenseRequestForm" action="{{ route('expense-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expense_type" class="form-label">Expense Type</label>
                                <select name="expense_type" id="expense_type" class="form-control">
                                    <option value="travel">Travel</option>
                                    <option value="food">Food</option>
                                    <option value="accommodation">Accommodation</option>
                                    <option value="miscellaneous">Miscellaneous</option>
                                </select>
                                <div class="invalid-feedback" id="expense_type_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01">
                                <div class="invalid-feedback" id="amount_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" name="date" id="date" class="form-control">
                                <div class="invalid-feedback" id="date_error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control"></textarea>
                                <div class="invalid-feedback" id="description_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" name="image" id="image" class="form-control">
                                <div class="invalid-feedback" id="image_error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteExpenseRequestModal" tabindex="-1" aria-labelledby="deleteExpenseRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteExpenseRequestModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this expense request?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteExpenseRequestBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- View Expense Request Modal -->
<div class="modal fade" id="viewExpenseRequestModal" tabindex="-1" aria-labelledby="viewExpenseRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewExpenseRequestModalLabel">Expense Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>User:</strong> <span id="view-expense-user-name"></span></p>
                <p><strong>Expense Type:</strong> <span id="view-expense-type"></span></p>
                <p><strong>Amount:</strong> <span id="view-expense-amount"></span></p>
                <p><strong>Date:</strong> <span id="view-expense-date"></span></p>
                <p><strong>Description:</strong> <span id="view-expense-description"></span></p>
                <p><strong>Status:</strong> <span id="view-expense-status"></span></p>
                <p><strong>Image:</strong> <span id="view-expense-image"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Expense Image Modal -->
<div class="modal fade" id="viewExpenseImageModal" tabindex="-1" aria-labelledby="viewExpenseImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewExpenseImageModalLabel">Expense Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="expenseImageDisplay" src="" alt="Expense Image" class="img-fluid">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Expense Request Modal -->
<div class="modal fade" id="editExpenseRequestModal" tabindex="-1" aria-labelledby="editExpenseRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExpenseRequestModalLabel">Edit Expense Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editExpenseRequestForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-expense-request-id" name="expense_request_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit-expense_type" class="form-label">Expense Type</label>
                            <select name="expense_type" id="edit-expense_type" class="form-control">
                                <option value="travel">Travel</option>
                                <option value="food">Food</option>
                                <option value="accommodation">Accommodation</option>
                                <option value="miscellaneous">Miscellaneous</option>
                            </select>
                            <div class="invalid-feedback" id="edit-expense_type_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit-amount" class="form-label">Amount</label>
                            <input type="number" name="amount" id="edit-amount" class="form-control" step="0.01">
                            <div class="invalid-feedback" id="edit-amount_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit-date" class="form-label">Date</label>
                            <input type="date" name="date" id="edit-date" class="form-control">
                            <div class="invalid-feedback" id="edit-date_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit-description" class="form-label">Description</label>
                            <textarea name="description" id="edit-description" class="form-control"></textarea>
                            <div class="invalid-feedback" id="edit-description_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit-image" class="form-label">Image</label>
                            <input type="file" name="image" id="edit-image" class="form-control">
                            <div class="invalid-feedback" id="edit-image_error"></div>
                            <small id="edit-current-image" class="form-text text-muted"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal for Changing Status --}}
<div class="modal fade" id="changeStatusExpenseRequestModal" tabindex="-1" aria-labelledby="changeStatusExpenseRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="changeStatusExpenseRequestForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusExpenseRequestModalLabel">Change Request Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="change-status-expense-request-id">

                    <div class="mb-3">
                        <label for="change_status" class="form-label">Status</label>
                        <select name="status" id="change_status" class="form-control" required disabled>
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

<!-- Legacy Weekly Report Modal -->
<div class="modal fade" id="legacyReportModal" tabindex="-1" aria-labelledby="legacyReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legacyReportModalLabel">Generate Legacy Weekly Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="legacyReportForm">
                    <div class="mb-3">
                        <label for="legacy_employee_id" class="form-label">Employee</label>
                        <select id="legacy_employee_id" class="form-control" style="width: 100%;">
                            <option value="{{ Auth::id() }}">My Reports</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="legacy_week_date" class="form-label">Select Date (Any day in the week)</label>
                        <input type="date" id="legacy_week_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        <small class="text-muted">The report will be generated for the full week (Sunday to Saturday) containing this date.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="generateLegacyReportBtn" class="btn btn-primary">Generate PDF</button>
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

                italic.className = 'fas ' + iconClass + ' me-1';

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
                    $('#view-expense-user-name').text(data.user.name || 'N/A');
                    $('#view-expense-type').text(data.expense_type);
                    $('#view-expense-amount').text(data.amount);
                    $('#view-expense-status').text(data.status);
                    $('#view-expense-description').text(data.description || 'N/A');
                    $('#view-expense-date').text(data.date);
                    $('#view-expense-image').html(data.image ? '<a href="/storage/' + data.image + '" target="_blank">View Image</a>' : 'N/A');

                    $('#viewExpenseRequestModal').modal('show');
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
            case 'approved and forwarded':
                return 'bg-primary text-white';
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
        var toastHeaderClass = '';
        var toastHeaderText = '';

        switch (type) {
            case 'success':
                toastClass = 'text-bg-success';
                toastHeaderClass = 'bg-success text-white';
                toastHeaderText = 'Success';
                break;
            case 'error':
            case 'danger':
                toastClass = 'text-bg-danger';
                toastHeaderClass = 'bg-danger text-white';
                toastHeaderText = 'Error';
                break;
            case 'warning':
                toastClass = 'text-bg-warning';
                toastHeaderClass = 'bg-warning text-dark';
                toastHeaderText = 'Warning';
                break;
            case 'info':
                toastClass = 'text-bg-info';
                toastHeaderClass = 'bg-info text-white';
                toastHeaderText = 'Info';
                break;
            default:
                toastClass = 'text-bg-primary';
                toastHeaderClass = 'bg-primary text-white';
                toastHeaderText = 'Notification';
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


    function reloadExpenseTables() {
        var activeTab = $('#expenseRequestTabs button.active').attr('id');
        if (activeTab === 'all-requests-tab') {
            $('#all-expense-requests-table').DataTable().ajax.reload(null, false);
        } else if (activeTab === 'my-requests-tab') {
            $('#my-expense-requests-table').DataTable().ajax.reload(null, false);
        }
    }

    $(document).ready(function() {

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
            templateResult: function(employee) {
                if (employee.loading) return employee.text;
                return employee.text;
            },
            templateSelection: function(employee) {
                return employee.text;
            }
        });

        // Initialize Select2 for expense type filter
        $('#expense_type_filter').select2({
            placeholder: 'Select Expense Type',
            allowClear: true,
            minimumResultsForSearch: Infinity // Hide search box for static options
        });

        // Initialize Select2 for status filter
        $('#status_filter').select2({
            placeholder: 'Select Status',
            allowClear: true,
            minimumResultsForSearch: Infinity // Hide search box for static options
        });

        var allExpenseRequestsTable = $('#all-expense-requests-table').DataTable({
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
                    d.employee_id = $('#employee_filter').val();
                    d.expense_type = $('#expense_type_filter').val();
                    d.status = $('#status_filter').val();
                    d.date_range = $('#date_filter').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
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
                    data: 'approved_amount',
                    name: 'approved_amount'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    render: function(data, type, row) {
                        var currentStatus = data; // data is now the raw status string
                        var forwardedInfoHtml = row.forwarded_info_html; // Get the pre-rendered HTML from the controller

                        var statusClasses = getStatusClasses(currentStatus);
                        var options = '';
                        var allStatuses = ['pending', 'approved', 'rejected', 'processed', 'approved and forwarded'];
                        allStatuses.forEach(function(status) {
                            var selected = (status === currentStatus) ? 'selected' : '';
                            options += `<option value="${status}" ${selected}>${ucfirst(status)}</option>`;
                        });
                        var statusHtml = `<select class="status-select form-control form-control-sm ${statusClasses}" data-id="${row.id}" data-current-status="${currentStatus}">${options}</select>`;

                        if (forwardedInfoHtml) {
                            statusHtml += forwardedInfoHtml;
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

        var myExpenseRequestsTable = $('#my-expense-requests-table').DataTable({
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
                data: {
                    my_requests: true
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
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
                    data: 'approved_amount',
                    name: 'approved_amount'
                },
                {
                    data: 'date',
                    name: 'date'
                }, // Added date column
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $('#employee_filter, #expense_type_filter, #status_filter').on('change', function() {
            allExpenseRequestsTable.ajax.reload();
        });

        $('#date_filter').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#date_filter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            $('#start_date_filter').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date_filter').val(picker.endDate.format('YYYY-MM-DD'));
            allExpenseRequestsTable.ajax.reload();
        });

        $('#date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#start_date_filter').val('');
            $('#end_date_filter').val('');
            allExpenseRequestsTable.ajax.reload();
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            $.fn.dataTable.tables(true).forEach(function(table) {
                $(table).DataTable().columns.adjust();
            });
        });

        $(document).on('click', '.delete-expense-request-btn', function() {

            let expenseRequestId;

            let activeTab = $('#expenseRequestTabs button.active').attr('id');



            if (activeTab === 'all-requests-tab') {

                var table = $('#all-expense-requests-table').DataTable();

                var row = table.row($(this).parents('tr')).data();

                expenseRequestId = row.id;

            } else if (activeTab === 'my-requests-tab') {

                var table = $('#my-expense-requests-table').DataTable();

                var row = table.row($(this).parents('tr')).data();

                expenseRequestId = row.id;

            } else {

                console.error('Could not determine active tab for delete operation.');

                showToast('Error: Could not determine active tab for deletion.', 'danger');

                return;

            }





            if (expenseRequestId) {

                $('#deleteExpenseRequestModal').data('expense-request-id', expenseRequestId);

                $('#deleteExpenseRequestModal').modal('show');

            } else {

                console.error('Expense Request ID is undefined. Cannot proceed with delete.');

                showToast('Error: Could not retrieve expense request ID for deletion.', 'danger');

            }

        });

        $('#confirmDeleteExpenseRequestBtn').on('click', function() {
            let expenseRequestIdToDelete = $('#deleteExpenseRequestModal').data('expense-request-id'); // Retrieve from modal

            $.ajax({
                url: '/expense-requests/' + expenseRequestIdToDelete,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#deleteExpenseRequestModal').modal('hide');
                    showToast('Expense request deleted successfully.', 'success');
                    allExpenseRequestsTable.ajax.reload();
                    myExpenseRequestsTable.ajax.reload();
                },
                error: function(response) {
                    $('#deleteExpenseRequestModal').modal('hide');
                    showToast('Error deleting expense request.', 'danger');
                }
            });
        });

        // Initialize Select2 for forward forwarding_to_employee_id inside the modal
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
                        results: $.map(data.results, function(item) {
                            return {
                                text: item.text,
                                id: item.user_id, // Use user_id as the value
                                data: item
                            };
                        }),
                        pagination: data.pagination
                    };
                },
                cache: true
            }
        });

        // Handle status change for expense requests (Dropdown Change)
        $(document).on('change', '.status-select', function() {
            var $selectElement = $(this);
            var expenseRequestId = $selectElement.data('id');
            var newStatus = $selectElement.val();
            var originalStatus = $selectElement.data('current-status');

            // If approved or approved and forwarded, trigger Modal
            if (newStatus === 'approved' || newStatus === 'approved and forwarded') {

                // Open Modal
                $('#change-status-expense-request-id').val(expenseRequestId);
                $('#change_status').val(newStatus).trigger('change'); // Trigger change for modal logic

                // Temporarily revert dropdown until modal confirmation
                $selectElement.val(originalStatus);

                // Fetch data to populate modal (approved amount fallback)
                $.get('/expense-requests/' + expenseRequestId, function(data) {
                    $('#original-requested-amount').text(data.amount);
                    if (data.approved_amount) {
                        $('#approved_amount').val(data.approved_amount);
                    } else {
                        $('#approved_amount').val(data.amount);
                    }

                    $('#changeStatusExpenseRequestForm').attr('action', '/expense-requests/' + expenseRequestId + '/change-status');
                    $('#changeStatusExpenseRequestModal').modal('show');
                });

                return;
            }

            // For other statuses, proceed with direct update
            $.ajax({
                url: '/expense-requests/' + expenseRequestId + '/change-status',
                method: 'POST',
                data: {
                    status: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    var allStatusClasses = [
                        'bg-warning text-dark', 'bg-success text-white', 'bg-danger text-white',
                        'bg-secondary text-white', 'bg-dark text-white', 'bg-info text-white', 'bg-primary text-white'
                    ];
                    $.each(allStatusClasses, function(index, className) {
                        $selectElement.removeClass(className);
                    });

                    // Add the new color class
                    $selectElement.addClass(getStatusClasses(newStatus));
                    $selectElement.data('current-status', newStatus); // Update the data attribute
                    showToast(response.message, 'success');
                    reloadExpenseTables();
                },
                error: function(xhr) {
                    showToast('Error updating status: ' + (xhr.responseJSON.message || ''), 'danger');
                    $selectElement.val(originalStatus); // Revert
                }
            });
        });

        // Handle Modal Status Change Logic (Inside Modal)
        $('#change_status').on('change', function() {
            let status = $(this).val();
            if (status === 'approved' || status === 'approved and forwarded') {
                $('#approved-amount-wrapper').show();
                $('#approved_amount').prop('required', true);
            } else {
                $('#approved-amount-wrapper').hide();
                $('#approved_amount').prop('required', false);
            }

            if (status === 'approved and forwarded') {
                $('#forward-to-wrapper').show();
            } else {
                $('#forward-to-wrapper').hide();
            }
        });

        // Handle Modal Form Submission
        $('#changeStatusExpenseRequestForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let url = form.attr('action');

            // Serialize form data and manually add the disabled 'status' field
            let formData = form.serializeArray();
            formData.push({
                name: 'status',
                value: $('#change_status').val()
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#changeStatusExpenseRequestModal').modal('hide');
                    showToast(response.message, 'success');
                    reloadExpenseTables();
                },
                error: function(xhr) {
                    showToast('Error updating status: ' + (xhr.responseJSON.message || ''), 'danger');
                }
            });
        });

        // Handle Create Expense Request Form Submission via AJAX
        $('#createExpenseRequestForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

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
                    showToast(response.message || 'Expense request created successfully.', 'success');
                    $('#createExpenseRequestForm')[0].reset(); // Reset the form
                    allExpenseRequestsTable.ajax.reload(); // Reload DataTables
                    myExpenseRequestsTable.ajax.reload(); // Reload DataTables
                    // Optionally switch to the All Expense Requests tab
                    new bootstrap.Tab(document.getElementById('all-requests-tab')).show();
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            $('#' + key).addClass('is-invalid');
                            $('#' + key + '_error').text(value[0]);
                        });
                    } else {
                        showToast(xhr.responseJSON.message || 'Error creating expense request.', 'danger');
                    }
                }
            });
        });

        // View Logic for Expense Request Details
        $(document).on('click', '.view-expense-request', function() {
            var expenseRequestId = $(this).data('id'); // Use .data('id') for buttons
            $.get('/expense-requests/' + expenseRequestId, function(data) {
                $('#view-expense-user-name').text(data.user ? data.user.name : 'N/A');
                $('#view-expense-type').text(data.expense_type);
                $('#view-expense-amount').text(data.amount);
                $('#view-expense-date').text(data.date);
                $('#view-expense-description').text(data.description || 'N/A');
                $('#view-expense-status').text(data.status);
                if (data.image) {
                    $('#view-expense-image').html('<a href="/storage/' + data.image + '" target="_blank">View Image</a>');
                } else {
                    $('#view-expense-image').text('No image');
                }
                $('#viewExpenseRequestModal').modal('show');
            });
        });

        // View Logic for Expense Image
        $(document).on('click', '.view-expense-image', function() {
            var imagePath = $(this).data('image-path');
            $('#expenseImageDisplay').attr('src', imagePath);
            $('#viewExpenseImageModal').modal('show');
        });

        // Edit Logic
        $(document).on('click', '.edit-expense-request-btn', function() {
            var expenseRequestId = $(this).data('id');
            $('#edit-expense-request-id').val(expenseRequestId);

            // Clear previous errors
            $('#editExpenseRequestForm .form-control').removeClass('is-invalid');
            $('#editExpenseRequestForm .invalid-feedback').text('');

            $.get('/expense-requests/' + expenseRequestId + '/edit', function(data) {
                $('#edit-expense_type').val(data.expense_type);
                $('#edit-amount').val(data.amount);
                $('#edit-date').val(data.date);
                $('#edit-description').val(data.description);

                // Handle image display
                if (data.image) {
                    $('#edit-current-image').html('Current: <a href="/storage/' + data.image + '" target="_blank">View Image</a>');
                } else {
                    $('#edit-current-image').text('No current image');
                }

                $('#editExpenseRequestModal').modal('show');
            });
        });

        // Handle Edit Expense Request Form Submission via AJAX
        $('#editExpenseRequestForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('#editExpenseRequestForm .form-control').removeClass('is-invalid');
            $('#editExpenseRequestForm .invalid-feedback').text('');

            var formData = new FormData(this);
            var expenseRequestId = $('#edit-expense-request-id').val();

            $.ajax({
                url: '/expense-requests/' + expenseRequestId,
                method: 'POST', // Use POST for PUT method spoofing
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-HTTP-Method-Override': 'PUT' // Spoof PUT method
                },
                success: function(response) {
                    showToast(response.message || 'Expense request updated successfully.', 'success');
                    $('#editExpenseRequestModal').modal('hide');
                    reloadExpenseTables();
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            $('#edit-' + key).addClass('is-invalid');
                            $('#edit-' + key + '_error').text(value[0]);
                        });
                    } else {
                        showToast(xhr.responseJSON.message || 'Error updating expense request.', 'danger');
                    }
                }
            });
        });

        // Legacy Weekly Report
        $('#legacy_employee_id').select2({
            dropdownParent: $('#legacyReportModal'),
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
                        results: $.map(data.results, function(item) {
                            return {
                                text: item.text,
                                id: item.user_id,
                                data: item
                            };
                        }),
                        pagination: data.pagination
                    };
                },
                cache: true
            }
        });

        $('#generateLegacyReportBtn').on('click', function() {
            let employeeId = $('#legacy_employee_id').val();
            let weekDate = $('#legacy_week_date').val();

            if (!weekDate) {
                showToast('Please select a date.', 'warning');
                return;
            }

            let url = "{{ route('expense-requests.export-legacy-pdf') }}?week_date=" + weekDate;
            if (employeeId) {
                url += "&employee_id=" + employeeId;
            }

            window.location.href = url;
        });
    });
</script>
@endpush