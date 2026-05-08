@extends('layouts.admin')

@section('title', 'Leave Requests')

@push('styles')
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
    <h1 class="mb-4">Leave Requests</h1>

    @if(isset($leaveBalances))
    <div class="row">
        <div class="col-xl-3 col-sm-6 box-col-6">
            <div class="card widget-1">
                <div class="card-body">
                    <div class="widget-content">
                        <div class="widget-round primary">
                            <div class="bg-round">
                                <svg class="svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-calendar') }}"></use>
                                </svg>
                                <svg class="half-circle svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#halfcircle') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4><span id="casual-remaining">{{ $leaveBalances['casual']['remaining'] }}</span></h4><span class="f-light">Casual Leave (Rem)</span>
                            <div class="font-primary f-w-500 mt-2"><span class="me-2">Taken: <span id="casual-taken">{{ $leaveBalances['casual']['taken'] }}</span></span><span class="f-light">/ <span id="casual-allotted">{{ $leaveBalances['casual']['allotted'] }}</span></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 box-col-6">
            <div class="card widget-1">
                <div class="card-body">
                    <div class="widget-content">
                        <div class="widget-round warning">
                            <div class="bg-round">
                                <svg class="svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                                </svg>
                                <svg class="half-circle svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#halfcircle') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4><span id="sick-remaining">{{ $leaveBalances['sick']['remaining'] }}</span></h4><span class="f-light">Sick Leave (Rem)</span>
                            <div class="font-warning f-w-500 mt-2"><span class="me-2">Taken: <span id="sick-taken">{{ $leaveBalances['sick']['taken'] }}</span></span><span class="f-light">/ <span id="sick-allotted">{{ $leaveBalances['sick']['allotted'] }}</span></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 box-col-6">
            <div class="card widget-1">
                <div class="card-body">
                    <div class="widget-content">
                        <div class="widget-round success">
                            <div class="bg-round">
                                <svg class="svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#rate') }}"></use>
                                </svg>
                                <svg class="half-circle svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#halfcircle') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4><span id="paid-remaining">{{ $leaveBalances['paid']['remaining'] }}</span></h4><span class="f-light">Privileged Leave (Rem)</span>
                            <div class="font-success f-w-500 mt-2"><span class="me-2">Taken: <span id="paid-taken">{{ $leaveBalances['paid']['taken'] }}</span></span><span class="f-light">/ <span id="paid-allotted">{{ $leaveBalances['paid']['allotted'] }}</span></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 box-col-6">
            <div class="card widget-1">
                <div class="card-body">
                    <div class="widget-content">
                        <div class="widget-round danger">
                            <div class="bg-round">
                                <svg class="svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-task') }}"></use>
                                </svg>
                                <svg class="half-circle svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#halfcircle') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4><span id="unpaid-taken">{{ $leaveBalances['unpaid']['taken'] }}</span></h4><span class="f-light">Unpaid Leave</span>
                            <div class="font-danger f-w-500 mt-2"><span>Total Taken</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">

        <div class="card-body">
            <ul class="nav nav-tabs d-flex mt-4" id="leaveRequestTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view"
                        type="button" role="tab" aria-controls="calendar-view" aria-selected="true">Calendar</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-requests-tab" data-bs-toggle="tab"
                        data-bs-target="#all-requests" type="button" role="tab" aria-controls="all-requests"
                        aria-selected="false">All Leave Requests</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="my-requests-tab" data-bs-toggle="tab" data-bs-target="#my-requests"
                        type="button" role="tab" aria-controls="my-requests" aria-selected="false">My Leave
                        Requests</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="add-request-tab" data-bs-toggle="tab" data-bs-target="#add-request"
                        type="button" role="tab" aria-controls="add-request" aria-selected="false">Create</button>
                </li>
            </ul>

            <div class="tab-content" id="leaveRequestTabContent">
                <div class="tab-pane fade show active" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
                    <div class="card">
                        <div class="card-body position-relative">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="btn-group" role="group" aria-label="Calendar Filter">
                                    <input type="radio" class="btn-check" name="calendarFilter" id="filterAll" value="all">
                                    <label class="btn btn-outline-primary" for="filterAll">All Leaves</label>

                                    <input type="radio" class="btn-check" name="calendarFilter" id="filterMy" value="my" checked>
                                    <label class="btn btn-outline-primary" for="filterMy">My Leaves</label>
                                </div>
                            </div>
                            <div id="calendar-loader" class="position-absolute top-50 start-50 translate-middle" style="z-index: 10; display: none;">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="all-requests" role="tabpanel"
                    aria-labelledby="all-requests-tab">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="employee_filter">Employee</label>
                            <select id="employee_filter" class="form-control">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="leave_type_filter">Leave Type</label>
                            <select id="leave_type_filter" class="form-control">
                                <option value="">All</option>
                                <option value="casual">Casual</option>
                                <option value="sick">Sick</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="compensatory">Compensatory</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status_filter">Status</label>
                            <select id="status_filter" class="form-control">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="approved and forwarded">Approved and Forwarded</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="cancelled by admin">Cancelled by Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_filter">Date</label>
                            <input type="text" id="date_filter" class="form-control" placeholder="Select date range" readonly>
                            <input type="hidden" id="start_date_filter" name="start_date">
                            <input type="hidden" id="end_date_filter" name="end_date">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="all-leave-requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Leave Type</th>
                                    <th>Duration</th>
                                    <th>Leave Period</th>
                                    <th>Status</th>
                                    <th>Attachment</th>
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
                        <table class="table" id="my-leave-requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Leave Type</th>
                                    <th>Duration</th>
                                    <th>Leave Period</th>
                                    <th>Status</th>
                                    <th>Attachment</th>
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
                    <form id="createLeaveRequestForm" action="{{ route('leave-requests.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 my-3 d-none">
                                <input type="hidden" name="is_compensatory" id="is_compensatory_hidden" value="0">
                            </div>
                        </div>
                        <div class="row" id="compensatory_date_row" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="compensatory_date" class="form-label">Compensatory Date</label>
                                <input type="text" name="compensatory_date" id="compensatory_date" class="form-control" readonly>
                                <div class="invalid-feedback" id="compensatory_date_error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="leave_type" class="form-label">Leave Type</label>
                                <select name="leave_type" id="leave_type" class="form-control">
                                    <option value="casual">Casual</option>
                                    <option value="sick">Sick</option>
                                    <option value="paid">Paid</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="compensatory">Compensatory</option>
                                </select>
                                <div class="invalid-feedback" id="leave_type_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Select Duration</label>
                                <select name="duration" id="duration" class="form-control">
                                    <option value="full_day">Full Day</option>
                                    <option value="first_half">First Half</option>
                                    <option value="second_half">Second Half</option>
                                    <option value="multiple">Multiple</option>
                                </select>
                                <div class="invalid-feedback" id="duration_error"></div>
                            </div>
                        </div>
                        <input type="file" name="attachment" id="attachment" class="form-control">
                        <div class="invalid-feedback" id="attachment_error"></div>

                        <input type="hidden" name="end_date" id="end_date">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="text" name="start_date" id="start_date" class="form-control" readonly>
                                <div class="invalid-feedback" id="start_date_error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                                <div class="invalid-feedback" id="reason_error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-end">

                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
                    <div class="card">
                        <div class="card-body">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteLeaveRequestModal" tabindex="-1" aria-labelledby="deleteLeaveRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLeaveRequestModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this leave request?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteLeaveRequestBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- View Leave Request Modal -->
<div class="modal fade" id="viewLeaveRequestModal" tabindex="-1" aria-labelledby="viewLeaveRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLeaveRequestModalLabel">Leave Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>User:</strong> <span id="view-leave-user-name"></span></p>
                <p><strong>Leave Type:</strong> <span id="view-leave-type"></span></p>
                <p><strong>Leave Period:</strong> <span id="view-leave-period"></span></p>
                <p><strong>Status:</strong> <span id="view-leave-status"></span></p>
                <p><strong>Attachment:</strong> <span id="view-leave-attachment"></span></p>
                <p><strong>Reason:</strong> <span id="view-leave-reason"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Leave Request Modal -->
<div class="modal fade" id="editLeaveRequestModal" tabindex="-1" aria-labelledby="editLeaveRequestModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLeaveRequestModalLabel">Edit Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLeaveRequestForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-leave-request-id" name="leave_request_id">
                    <div class="row">
                        <div class="col-md-6 mb-3 d-none">
                            <input type="hidden" name="is_compensatory" id="edit-is_compensatory_hidden" value="0">
                        </div>
                    </div>
                    <div class="row" id="edit-compensatory_date_row" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="edit-compensatory_date" class="form-label">Compensatory Date</label>
                            <input type="text" name="compensatory_date" id="edit-compensatory_date"
                                class="form-control" readonly>
                            <div class="invalid-feedback" id="edit-compensatory_date_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit-start_date" class="form-label">Start Date</label>
                            <input type="text" name="start_date" id="edit-start_date" class="form-control" readonly>
                            <div class="invalid-feedback" id="edit-start_date_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit-leave_type" class="form-label">Leave Type</label>
                            <select name="leave_type" id="edit-leave_type" class="form-control">
                                <option value="casual">Casual</option>
                                <option value="sick">Sick</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="compensatory">Compensatory</option>
                            </select>
                            <div class="invalid-feedback" id="edit-leave_type_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit-duration" class="form-label">Select Duration</label>
                            <select name="duration" id="edit-duration" class="form-control">
                                <option value="full_day">Full Day</option>
                                <option value="first_half">First Half</option>
                                <option value="second_half">Second Half</option>
                                <option value="multiple">Multiple</option>
                            </select>
                            <div class="invalid-feedback" id="edit-duration_error"></div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit-attachment" class="form-label">Attachment</label>
                        <input type="file" name="attachment" id="edit-attachment" class="form-control">
                        <div class="invalid-feedback" id="edit-attachment_error"></div>
                        <small id="edit-current-attachment" class="form-text text-muted"></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit-reason" class="form-label">Reason</label>
                        <textarea name="reason" id="edit-reason" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback" id="edit-reason_error"></div>
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
@endsection

@push('scripts')
<script src="{{ asset('admin/assets/js/chart/apex-chart/moment.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'standard',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day',
                list: 'List'
            },
            dayMaxEvents: true,
            height: 'auto',
            events: function(info, successCallback, failureCallback) {
                var filterValue = $('input[name="calendarFilter"]:checked').val();
                var params = {
                    start: info.startStr,
                    end: info.endStr
                };
                if (filterValue === 'my') {
                    params.my_requests = true;
                }

                $('#calendar-loader').show();
                $.ajax({
                    url: "{{ route('leave-requests.calendar-events') }}",
                    data: params,
                    success: function(response) {
                        $('#calendar-loader').hide();
                        successCallback(response);
                    },
                    error: function() {
                        $('#calendar-loader').hide();
                        failureCallback();
                    }
                });
            },
            eventDidMount: function(info) {
                // Tooltip
                if (info.event.extendedProps.description) {
                    info.el.setAttribute('title', info.event.title + '\nReason: ' + info.event.extendedProps.description);
                }
            },
            eventContent: function(arg) {
                let iconClass = 'fa-circle';
                const type = arg.event.extendedProps.leave_type;

                if (type === 'casual') iconClass = 'fa-coffee';
                else if (type === 'sick') iconClass = 'fa-medkit';
                else if (type === 'paid') iconClass = 'fa-check-circle';
                else if (type === 'unpaid') iconClass = 'fa-ban';
                else if (type === 'compensatory') iconClass = 'fa-history';

                return {
                    html: `<div class="d-flex align-items-center overflow-hidden">
                              <i class="fa ${iconClass} me-1" style="font-size: 0.8em; opacity: 0.9;"></i> 
                              <div class="text-truncate fw-500">${arg.event.title}</div>
                            </div>`
                };
            },
            eventClick: function(info) {
                var eventObj = info.event;
                // Fetch full details via AJAX to ensure we have latest data (like attachment, full description)
                $.ajax({
                    url: '/leave-requests/' + eventObj.id, // Re-using show route which returns JSON
                    type: 'GET',
                    success: function(response) {
                        $('#view-leave-user-name').text(response.user ? response.user.name : 'N/A');
                        $('#view-leave-type').text(ucfirst(response.leave_type));

                        var start = moment(response.start_date);
                        var end = moment(response.end_date);
                        var periodStr = start.format('DD/MM/YYYY');
                        if (!start.isSame(end, 'day')) {
                            periodStr += ' to ' + end.format('DD/MM/YYYY');
                        }
                        $('#view-leave-period').text(periodStr);

                        var statusClass = getStatusClasses(response.status);
                        $('#view-leave-status').html('<span class="badge ' + statusClass + '">' + ucfirst(response.status) + '</span>');

                        $('#view-leave-reason').text(response.reason || 'N/A');

                        if (response.attachment) {
                            $('#view-leave-attachment').html('<a href="/storage/' + response.attachment + '" target="_blank" class="text-primary"><i class="fa fa-paperclip me-1"></i>View Attachment</a>');
                        } else {
                            $('#view-leave-attachment').text('None');
                        }

                        $('#viewLeaveRequestModal').modal('show');
                    },
                    error: function() {
                        showToast('Failed to fetch leave details.', 'danger');
                    }
                });
            }
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (e.target.id === 'calendar-tab') {
                calendar.render();
            }
        });

        $('input[name="calendarFilter"]').on('change', function() {
            calendar.refetchEvents();
        });

        calendar.render(); // Render immediately on load
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
            case 'approved and forwarded':
                return 'bg-info text-white';
            case 'rejected':
                return 'bg-danger text-white';
            case 'cancelled':
                return 'bg-secondary text-white';
            case 'cancelled by admin':
                return 'bg-dark text-white';
            default:
                return 'bg-info text-white';
        }
    }

    function showToast(message, type) {
        var toastContainer = $('#toast-container');
        if (toastContainer.length === 0) {
            toastContainer = $(
                '<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>');
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

    function reloadLeaveTables() {
        var activeTab = $('#leaveRequestTabs button.active').attr('id');
        if (activeTab === 'all-requests-tab') {
            allLeaveRequestsTable.ajax.reload();
        } else if (activeTab === 'my-requests-tab') {
            myLeaveRequestsTable.ajax.reload();
        }
    }

    function fetchLeaveBalances() {
        $.ajax({
            url: "{{ route('leave-requests.balances') }}",
            type: 'GET',
            success: function(response) {
                // Update Casual
                $('#casual-remaining').text(response.casual.remaining);
                $('#casual-taken').text(response.casual.taken);
                $('#casual-allotted').text(response.casual.allotted);

                // Update Sick
                $('#sick-remaining').text(response.sick.remaining);
                $('#sick-taken').text(response.sick.taken);
                $('#sick-allotted').text(response.sick.allotted);

                // Update Paid
                $('#paid-remaining').text(response.paid.remaining);
                $('#paid-taken').text(response.paid.taken);
                $('#paid-allotted').text(response.paid.allotted);

                // Update Unpaid
                $('#unpaid-taken').text(response.unpaid.taken);
            },
            error: function(xhr) {
                console.error('Failed to fetch leave balances');
            }
        });
    }

    var allLeaveRequestsTable;
    var myLeaveRequestsTable;

    $(document).ready(function() {
        // Initialize Select2 for employee filter
        $('#employee_filter').select2({
            placeholder: 'Select an Employee',
            allowClear: true,
            ajax: {
                url: "{{ route('employees.searchEmployee') }}", // Use the generic employee search route
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
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





        allLeaveRequestsTable = $('#all-leave-requests-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('leave-requests.index') }}",
                data: function(d) {
                    d.employee_id = $('#employee_filter').val();

                    d.leave_type = $('#leave_type_filter').val();
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
                    name: 'user.name',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'leave_type',
                    name: 'leave_type'
                },
                {
                    data: 'duration_display',
                    name: 'duration_display',
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    name: 'leave_period',
                    orderable: false,
                    render: function(data, type, row) {
                        compensatory = null;
                        var isCompensatoryType = row.leave_type.indexOf('Compensatory') !== -1;
                        if (row.is_compensatory == 1 && row.compensatory_date) {
                            if (isCompensatoryType) {
                                compensatory = '<br> Worked on : ' + moment(row.compensatory_date).format('DD/MM/YYYY');
                            } else {
                                compensatory = '<br><span class="badge bg-success text-white">Compensatory</span>  Worked on : ' + moment(row.compensatory_date).format('DD/MM/YYYY');
                            }
                        }

                        if (row.start_date && row.end_date) {
                            return moment(row.start_date).format('DD/MM/YYYY') + ' to ' +
                                moment(row.end_date).format('DD/MM/YYYY') + compensatory;
                        } else if (row.start_date) {
                            return moment(row.start_date).format('DD/MM/YYYY') + compensatory;
                        } else {
                            return '';
                        }
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    render: function(data, type, row) {
                        var currentStatus = data.status;
                        var forwardedToEmployeeName = data.forwarded_to_employee_name;
                        var forwardedToEmployeeDepartment = data
                            .forwarded_to_employee_department;
                        var isMyRequests = data.my_requests;

                        var statusClasses = getStatusClasses(currentStatus);
                        var statusHtml = '';

                        if (isMyRequests) {
                            statusHtml = ucfirst(currentStatus);
                        } else {
                            var options = '';
                            var allStatuses = ['pending', 'approved', 'rejected',
                                'approved and forwarded', 'cancelled', 'cancelled by admin'
                            ];
                            allStatuses.forEach(function(status) {
                                var selected = (status === currentStatus) ? 'selected' :
                                    '';
                                options +=
                                    `<option value="${status}" ${selected}>${ucfirst(status)}</option>`;
                            });
                            statusHtml =
                                `<select class="status-select form-control form-control-sm ${statusClasses}" data-id="${row.id}" data-current-status="${currentStatus}">${options}</select>`;
                        }

                        if (currentStatus === 'approved and forwarded' &&
                            forwardedToEmployeeName) {
                            var departmentBadge = forwardedToEmployeeDepartment ?
                                `<span class="badge bg-secondary">${forwardedToEmployeeDepartment}</span>` :
                                '';
                            statusHtml += `
                                <div class="forwarded-employee-info mt-1 text-muted" style="font-size: 0.8em;">
                                    Forwarded to: <strong>${forwardedToEmployeeName}</strong>
                                    ${departmentBadge}
                                </div>
                            `;
                        }
                        return statusHtml;
                    }
                },
                {
                    data: 'attachment',
                    name: 'attachment',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (data) {
                            return '<a href="/storage/' + data + '" target="_blank">View</a>';
                        } else {
                            return '';
                        }
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

        myLeaveRequestsTable = $('#my-leave-requests-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('leave-requests.index') }}",
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
                    data: 'user.name',
                    name: 'user.name',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'leave_type',
                    name: 'leave_type'
                },
                {
                    data: 'duration_display',
                    name: 'duration_display',
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    name: 'leave_period',
                    orderable: false,
                    render: function(data, type, row) {
                        compensatory = null;
                        var isCompensatoryType = row.leave_type.indexOf('Compensatory') !== -1;
                        if (row.is_compensatory == 1 && row.compensatory_date) {
                            if (isCompensatoryType) {
                                compensatory = '<br> Worked on : ' + moment(row.compensatory_date).format('DD/MM/YYYY');
                            } else {
                                compensatory = '<br><span class="badge bg-success text-white">Compensatory</span>  Worked on : ' + moment(row.compensatory_date).format('DD/MM/YYYY');
                            }
                        }

                        if (row.start_date && row.end_date) {
                            return moment(row.start_date).format('DD/MM/YYYY') + ' to ' +
                                moment(row.end_date).format('DD/MM/YYYY') + (compensatory ?? '');
                        } else if (row.start_date) {
                            return moment(row.start_date).format('DD/MM/YYYY') + (compensatory ?? '');
                        } else {
                            return '';
                        }
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        var statusClasses = getStatusClasses(data.status);
                        var statusHtml = '<span class="badge ' + statusClasses + '">' + ucfirst(
                            data.status) + '</span>';
                        if (data.status === 'approved and forwarded' && row
                            .forwarded_to_employee_name) {
                            var departmentBadge = row.forwarded_to_employee_department ?
                                '<span class="badge bg-secondary">' + row
                                .forwarded_to_employee_department + '</span>' : '';
                            statusHtml +=
                                '<div class="forwarded-employee-info mt-1 text-muted" style="font-size: 0.8em;">Forwarded to: <strong>' +
                                row.forwarded_to_employee_name + '</strong> ' +
                                departmentBadge + '</div>';
                        }
                        return statusHtml;
                    }
                },
                {
                    data: 'attachment',
                    name: 'attachment',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (data) {
                            return '<a href="/storage/' + data + '" target="_blank">View</a>';
                        } else {
                            return '';
                        }
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

        $('#employee_filter, #leave_type_filter, #status_filter').on('change', function() {
            allLeaveRequestsTable.ajax.reload();
        });

        $('#date_filter').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY',
                cancelLabel: 'Clear'
            }
        });

        $('#date_filter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                'DD/MM/YYYY'));
            $('#start_date_filter').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date_filter').val(picker.endDate.format('YYYY-MM-DD'));
            allLeaveRequestsTable.ajax.reload();
        });

        $('#date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#start_date_filter').val('');
            $('#end_date_filter').val('');
            allLeaveRequestsTable.ajax.reload();
        });



        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            $.fn.dataTable.tables(true).forEach(function(table) {
                $(table).DataTable().columns.adjust();
            });
        });

        let leaveRequestIdToDelete;

        $(document).on('click', '.delete-leave-request-btn', function() {
            leaveRequestIdToDelete = $(this).data('id');
            $('#deleteLeaveRequestModal').modal('show');
        });

        $('#confirmDeleteLeaveRequestBtn').on('click', function() {
            $.ajax({
                url: '/leave-requests/' + leaveRequestIdToDelete,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#deleteLeaveRequestModal').modal('hide');
                    showToast('Leave request deleted successfully.', 'success');
                    allLeaveRequestsTable.ajax.reload();
                    myLeaveRequestsTable.ajax.reload();
                    fetchLeaveBalances();
                },
                error: function(response) {
                    $('#deleteLeaveRequestModal').modal('hide');
                    showToast('Error deleting leave request.', 'danger');
                }
            });
        });



        // Handle status change for leave requests
        $(document).on('change', '.status-select', function() {
            var leaveRequestId = $(this).data('id');
            var newStatus = $(this).val();
            var $selectElement = $(this); // Store a reference to the select element
            var originalStatus = $selectElement.data(
                'current-status'); // Get original status from data attribute



            if (newStatus === 'approved and forwarded') {
                var $statusTd = $selectElement.closest('td');
                var leaveRequestId = $selectElement.data('id');

                // Check if the forwarding UI already exists for this row
                if ($statusTd.find('.forwarding-ui').length) {
                    return; // Already showing, do nothing
                }

                var uniqueSelectId = 'forward-to-employee-select-' + leaveRequestId;
                var uniqueConfirmBtnId = 'confirm-forward-btn-' + leaveRequestId;
                var uniqueCancelBtnId = 'cancel-forward-btn-' + leaveRequestId;

                var forwardingUiHtml = `
                    <div class="forwarding-ui mt-2" style="display: none;">
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
                                            $container.find('.select2-result-employee__title').text(employee
                                                .text);
                                            if (employee.department && employee.department.name) {
                                                $container.find('.select2-result-employee__department').html(
                                                    '<span class="badge bg-secondary">' + employee
                                                    .department.name + '</span>');
                                            }
                                            return $container;
                                        },
                                        templateSelection: function(employee) {
                                            if (employee.text) {
                                                var deptBadge = (employee.department && employee.department.name) ?
                                                    ' <span class="badge bg-secondary">' + employee
                                                    .department.name + '</span>' : '';
                                                return employee.text + deptBadge;
                                            }
                                            return employee.name || employee.text;
                                        },                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });

                // Handle Confirm Forward button click
                $(document).on('click', '#' + uniqueConfirmBtnId, function() {
                    var forwardedToEmployeeId = $('#' + uniqueSelectId).val();
                    var selectedEmployeeData = $('#' + uniqueSelectId).select2('data')[0];
                    var forwardedEmployeeName = selectedEmployeeData ? selectedEmployeeData
                        .text : 'N/A';
                    var forwardedEmployeeDepartment = (selectedEmployeeData &&
                        selectedEmployeeData.department) ? selectedEmployeeData
                        .department.name : '';

                    if (!forwardedToEmployeeId) {
                        showToast('Please select an employee to forward to.', 'warning');
                        return;
                    }

                    $.ajax({
                        url: '/leave-requests/' + leaveRequestId + '/change-status',
                        method: 'POST',
                        data: {
                            status: 'approved and forwarded',
                            forwarded_to_employee_id: forwardedToEmployeeId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            showToast(response.message ||
                                'Leave request forwarded successfully.',
                                'success');

                            // Dynamically update the status dropdown
                            $selectElement.val('approved and forwarded');
                            $selectElement.removeClass().addClass(
                                'status-select form-control form-control-sm'
                            ); // Reset classes
                            $selectElement.addClass(getStatusClasses(
                                'approved and forwarded'));
                            $selectElement.data('current-status',
                                'approved and forwarded'); // Update data attribute

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
                            $('#' + uniqueSelectId).select2(
                                'destroy'); // Destroy Select2 instance

                            reloadLeaveTables();
                            fetchLeaveBalances();
                        },
                        error: function(xhr) {
                            showToast('Error forwarding leave request: ' + (xhr
                                .responseJSON.message || ''), 'danger');
                            // Revert UI on error
                            $statusTd.find('.forwarding-ui').slideUp(function() {
                                $(this).remove();
                            });
                            $('#' + uniqueSelectId).select2(
                                'destroy'); // Destroy Select2 instance
                            $selectElement.val(
                                originalStatus); // Revert status dropdown
                            $selectElement.addClass(getStatusClasses(
                                originalStatus));
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
                url: '/leave-requests/' + leaveRequestId + '/change-status', // New route
                method: 'POST',
                data: {
                    status: newStatus,
                    _token: '{{ csrf_token() }}' // CSRF token for Laravel
                },
                success: function(response) {
                    var allStatusClasses = [
                        'bg-warning text-dark', 'bg-success text-white',
                        'bg-danger text-white',
                        'bg-secondary text-white', 'bg-dark text-white',
                        'bg-info text-white'
                    ];
                    $.each(allStatusClasses, function(index, className) {
                        $selectElement.removeClass(className);
                    });

                    // Add the new color class
                    $selectElement.addClass(getStatusClasses(newStatus));
                    $selectElement.data('current-status',
                        newStatus); // Update the data attribute
                    showToast(response.message, 'success');
                    reloadLeaveTables();
                    fetchLeaveBalances();
                },
                error: function(xhr) {
                    showToast('Error updating status: ' + (xhr.responseJSON.message || ''),
                        'danger');
                    // Revert the dropdown to its original value on error
                    $selectElement.val(originalStatus);
                    // Revert classes as well
                    var allStatusClasses = [
                        'bg-warning text-dark', 'bg-success text-white',
                        'bg-danger text-white',
                        'bg-secondary text-white', 'bg-dark text-white',
                        'bg-info text-white'
                    ];
                    $.each(allStatusClasses, function(index, className) {
                        $selectElement.removeClass(className);
                    });
                    $selectElement.addClass(getStatusClasses(originalStatus));
                }
            });
        });

        // Handle duration change for Create form
        $('#duration').on('change', function() {
            var isMultiple = $(this).val() === 'multiple';
            var currentStartDate = $('#start_date').val();
            var singleDate = currentStartDate && currentStartDate.split(' - ')[0] ? currentStartDate
                .split(' - ')[0] : moment().format('DD/MM/YYYY');

            // Destroy existing daterangepicker instance and remove apply handler if any
            if ($('#start_date').data('daterangepicker')) {
                $('#start_date').daterangepicker('destroy');
            }
            $('#start_date').off('apply.daterangepicker');

            // Re-initialize daterangepicker with explicit format
            $('#start_date').daterangepicker({
                singleDatePicker: !isMultiple,
                autoApply: !isMultiple, // Auto apply if single date picker
                startDate: moment(singleDate, 'DD/MM/YYYY'),
                endDate: isMultiple && $('#end_date').val() ? moment($('#end_date').val(),
                    'YYYY-MM-DD') : moment(singleDate, 'DD/MM/YYYY'),
                autoUpdateInput: false,
                locale: {
                    format: 'DD/MM/YYYY',
                    cancelLabel: 'Clear'
                }
            });

            // Rebind apply handler to ensure it fires for the new instance
            $('#start_date').on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('DD/MM/YYYY');
                var endDate = picker.endDate.format('DD/MM/YYYY');

                if ($('#duration').val() === 'multiple') {
                    $(this).val(startDate + ' - ' + endDate);
                    $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                } else {
                    $(this).val(startDate);
                    $('#end_date').val(picker.startDate.format('YYYY-MM-DD'));
                }
            });

            // Always set end_date to start_date for non-multiple
            if (!isMultiple) {
                $('#end_date').val(moment(singleDate, 'DD/MM/YYYY').isValid() ? moment(singleDate,
                    'DD/MM/YYYY').format('YYYY-MM-DD') : moment().format('YYYY-MM-DD'));
                $('#start_date').val(singleDate);
                $('#end_date').prop('readonly', true);
            } else {
                $('#end_date').prop('readonly', false);
            }
        });

        $('#start_date').on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate.format('DD/MM/YYYY');
            var endDate = picker.endDate.format('DD/MM/YYYY');

            if ($('#duration').val() === 'multiple') {
                $(this).val(startDate + ' - ' + endDate);
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            } else {
                $(this).val(startDate);
                $('#end_date').val(picker.startDate.format('YYYY-MM-DD'));
            }
        });

        $('#createLeaveRequestForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            var formData = new FormData(this);

            // Before submit, always set end_date to start_date for non-multiple
            if ($('#duration').val() !== 'multiple') {
                $('#end_date').val($('#start_date').val());
            }

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
                    showToast(response.message || 'Leave request created successfully.',
                        'success');
                    $('#createLeaveRequestForm')[0].reset(); // Reset the form
                    $('#compensatory_date_row').hide(); // Hide compensatory row
                    $('#is_compensatory_hidden').val(0); // Reset hidden input
                    allLeaveRequestsTable.ajax.reload(); // Reload DataTables
                    myLeaveRequestsTable.ajax.reload(); // Reload DataTables
                    fetchLeaveBalances();
                    // Optionally switch to the All Leave Requests tab
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
                        showToast(xhr.responseJSON.message ||
                            'Error creating leave request.', 'danger');
                    }
                }
            });
        });

        // View Logic
        $(document).on('click', '.view a[data-id]', function() { // Changed selector
            var leaveRequestId = $(this).attr('data-id'); // $(this) is now the <a> tag

            $.get('/leave-requests/' + leaveRequestId, function(data) {
                $('#view-leave-user-name').text(data.user ? data.user.name : 'N/A');
                $('#view-leave-type').text(data.leave_type);
                if (data.start_date && data.end_date) {
                    $('#view-leave-period').text(moment(data.start_date).format('DD/MM/YYYY') +
                        ' to ' + moment(data.end_date).format('DD/MM/YYYY'));
                } else if (data.start_date) {
                    $('#view-leave-period').text(moment(data.start_date).format('DD/MM/YYYY'));
                } else {
                    $('#view-leave-period').text('');
                }
                $('#view-leave-status').text(data.status);
                if (data.attachment) {
                    $('#view-leave-attachment').html('<a href="/storage/' + data.attachment +
                        '" target="_blank">View Attachment</a>');
                } else {
                    $('#view-leave-attachment').text('No attachment');
                }
                $('#view-leave-reason').text(data.reason || 'N/A');
                $('#viewLeaveRequestModal').modal('show');
            });
        });

        // Edit Logic
        $(document).on('click', '.edit-leave-request-btn', function() {
            var leaveRequestId = $(this).data('id');
            $('#edit-leave-request-id').val(leaveRequestId);

            // Clear previous errors
            $('#editLeaveRequestForm .form-control').removeClass('is-invalid');
            $('#editLeaveRequestForm .invalid-feedback').text('');

            $.get('/leave-requests/' + leaveRequestId + '/edit', function(data) {
                $('#edit-leave_type').val(data.leave_type);
                $('#edit-duration').val(data.duration);
                $('#edit-reason').val(data.reason);

                // Set is_compensatory checkbox and show/hide compensatory_date field
                if (data.is_compensatory) {
                    $('#edit-leave_type').val('compensatory').trigger('change');
                    $('#edit-is_compensatory_hidden').val(1);
                    $('#edit-compensatory_date_row').show();
                    $('#edit-compensatory_date').val(moment(data.compensatory_date).format(
                        'DD/MM/YYYY'));
                } else {
                    $('#edit-leave_type').val(data.leave_type).trigger('change'); // Trigger change to ensure correct UI state
                    $('#edit-is_compensatory_hidden').val(0);
                    $('#edit-compensatory_date_row').hide();
                    $('#edit-compensatory_date').val('');
                }

                // Handle attachment display
                if (data.attachment) {
                    $('#edit-current-attachment').html('Current: <a href="/storage/' + data
                        .attachment + '" target="_blank">View</a>');
                } else {
                    $('#edit-current-attachment').text('No current attachment');
                }

                // Initialize daterangepicker for edit form
                var startDate = data.start_date;
                var endDate = data.end_date;
                var isMultipleEdit = data.duration === 'multiple';

                // Destroy existing daterangepicker instance and remove apply handler if any
                if ($('#edit-start_date').data('daterangepicker')) {
                    $('#edit-start_date').daterangepicker('destroy');
                }
                $('#edit-start_date').off('apply.daterangepicker');

                $('#edit-start_date').daterangepicker({
                    singleDatePicker: !isMultipleEdit,
                    startDate: moment(startDate, 'YYYY-MM-DD'),
                    endDate: isMultipleEdit && endDate ? moment(endDate, 'YYYY-MM-DD') : moment(startDate, 'YYYY-MM-DD'),
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD/MM/YYYY',
                        cancelLabel: 'Clear'
                    }
                });

                if (isMultipleEdit) {
                    $('#edit-start_date').val(moment(startDate, 'YYYY-MM-DD').format(
                        'DD/MM/YYYY') + ' - ' + moment(endDate, 'YYYY-MM-DD').format(
                        'DD/MM/YYYY'));
                    $('#edit-end_date').val(moment(endDate, 'YYYY-MM-DD').format('YYYY-MM-DD'));
                } else {
                    $('#edit-start_date').val(moment(startDate, 'YYYY-MM-DD').format(
                        'DD/MM/YYYY'));
                    $('#edit-end_date').val(moment(startDate, 'YYYY-MM-DD').format(
                        'YYYY-MM-DD'));
                }

                // Ensure apply handler is bound to the current picker instance
                $('#edit-start_date').off('apply.daterangepicker').on('apply.daterangepicker',
                    function(ev, picker) {
                        var startDate = picker.startDate.format('DD/MM/YYYY');
                        var endDate = picker.endDate.format('DD/MM/YYYY');

                        if ($('#edit-duration').val() === 'multiple') {
                            $(this).val(startDate + ' - ' + endDate);
                            $('#edit-end_date').val(picker.endDate.format('YYYY-MM-DD'));
                        } else {
                            $(this).val(startDate);
                            $('#edit-end_date').val(picker.startDate.format('YYYY-MM-DD'));
                        }
                    });

                // Handle duration change for Edit form
                $('#edit-duration').off('change').on('change', function() {
                    var isMultiple = $(this).val() === 'multiple';
                    var currentStartDateEdit = $('#edit-start_date').val();
                    var singleDateEdit = currentStartDateEdit && currentStartDateEdit
                        .split(' - ')[0] ? currentStartDateEdit.split(' - ')[0] :
                        moment().format('DD/MM/YYYY');
                    var currentEndDateEdit = $('#edit-end_date').val();

                    // Destroy existing daterangepicker instance if any
                    if ($('#edit-start_date').data('daterangepicker')) {
                        $('#edit-start_date').daterangepicker('destroy');
                    }

                    $('#edit-start_date').daterangepicker({
                        singleDatePicker: !isMultiple,
                        startDate: moment(singleDateEdit, 'DD/MM/YYYY'),
                        endDate: isMultiple && currentEndDateEdit ? moment(
                            currentEndDateEdit, 'YYYY-MM-DD') : moment(
                            singleDateEdit, 'DD/MM/YYYY'),
                        autoUpdateInput: false,
                        locale: {
                            cancelLabel: 'Clear'
                        }
                    });

                    // Always set end_date to start_date for non-multiple
                    if (!isMultiple) {
                        $('#edit-end_date').val(moment(singleDateEdit, 'DD/MM/YYYY')
                            .isValid() ? moment(singleDateEdit, 'DD/MM/YYYY')
                            .format('YYYY-MM-DD') : moment().format('YYYY-MM-DD'));
                        $('#edit-start_date').val(singleDateEdit);
                        $('#edit-end_date').prop('readonly', true);
                    } else {
                        $('#edit-end_date').prop('readonly', false);
                    }
                });

                $('#editLeaveRequestModal').modal('show');
            });
        });

        // Handle Edit Leave Request Form Submission via AJAX
        $('#editLeaveRequestForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('#editLeaveRequestForm .form-control').removeClass('is-invalid');
            $('#editLeaveRequestForm .invalid-feedback').text('');

            var formData = new FormData(this);
            var leaveRequestId = $('#edit-leave-request-id').val();

            // Before submit, always set end_date to start_date for non-multiple
            if ($('#edit-duration').val() !== 'multiple') {
                $('#edit-end_date').val($('#edit-start_date').val());
            }

            $.ajax({
                url: '/leave-requests/' + leaveRequestId,
                method: 'POST', // Use POST for PUT method spoofing
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-HTTP-Method-Override': 'PUT' // Spoof PUT method
                },
                success: function(response) {
                    showToast(response.message || 'Leave request updated successfully.',
                        'success');
                    $('#editLeaveRequestModal').modal('hide');
                    allLeaveRequestsTable.ajax.reload();
                    myLeaveRequestsTable.ajax.reload();
                    fetchLeaveBalances();
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            $('#edit-' + key).addClass('is-invalid');
                            $('#edit-' + key + '_error').text(value[0]);
                        });
                    } else {
                        showToast(xhr.responseJSON.message ||
                            'Error updating leave request.', 'danger');
                    }
                }
            });
        });

        // Initialize daterangepicker for Create form on page load
        var initialIsMultiple = $('#duration').val() === 'multiple';
        var initialStartDate = $('#start_date').val();
        var singleDate = initialStartDate && initialStartDate.split(' - ')[0] ? initialStartDate.split(' - ')[
            0] : moment().format('DD/MM/YYYY');
        $('#start_date').daterangepicker({
            singleDatePicker: !initialIsMultiple,
            autoApply: !initialIsMultiple, // Auto apply if single date picker
            startDate: moment(singleDate, 'DD/MM/YYYY'),
            endDate: initialIsMultiple && $('#end_date').val() ? moment($('#end_date').val(),
                'YYYY-MM-DD') : moment(singleDate, 'DD/MM/YYYY'),
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY',
                cancelLabel: 'Clear'
            }
        });

        // Toggle compensatory date field based on leave type
        $('#leave_type').on('change', function() {
            if ($(this).val() === 'compensatory') {
                $('#compensatory_date_row').show();
                $('#is_compensatory_hidden').val(1);
            } else {
                $('#compensatory_date_row').hide();
                $('#is_compensatory_hidden').val(0);
            }
        });

        $('#edit-leave_type').on('change', function() {
            if ($(this).val() === 'compensatory') {
                $('#edit-compensatory_date_row').show();
                $('#edit-is_compensatory_hidden').val(1);
            } else {
                $('#edit-compensatory_date_row').hide();
                $('#edit-is_compensatory_hidden').val(0);
            }
        });

        // Initialize daterangepicker for compensatory date fields
        $('#compensatory_date').daterangepicker({
            singleDatePicker: true,
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY',
                cancelLabel: 'Clear'
            }
        });

        $('#compensatory_date').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY'));
        });

        $('#edit-compensatory_date').daterangepicker({
            singleDatePicker: true,
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY',
                cancelLabel: 'Clear'
            }
        });

        $('#edit-compensatory_date').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY'));
        });
    });

    // Handle Forward Leave Request button click using event delegation
    $('#forwardLeaveRequestModal').on('click', '#confirmForwardLeaveRequestBtn', function() {
        var leaveRequestId = $('#forward-leave-request-id').val();
        var forwardedToEmployeeId = $('#forward-to-employee-select').val();

        if (!forwardedToEmployeeId) {
            showToast('Please select an employee to forward to.', 'warning');
            return;
        }

        $.ajax({
            url: '/leave-requests/' + leaveRequestId + '/change-status',
            method: 'POST',
            data: {
                status: 'approved and forwarded',
                forwarded_to_employee_id: forwardedToEmployeeId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#forwardLeaveRequestModal').modal('hide');
                showToast(response.message || 'Leave request forwarded successfully.', 'success');
                reloadLeaveTables();
            },
            error: function(xhr) {
                showToast('Error forwarding leave request: ' + (xhr.responseJSON.message || ''),
                    'danger');
            }
        });
    });
</script>
''