@extends('layouts.admin')

@section('title', 'Performance Review')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    .review-card {
        border-left: 4px solid #7366ff;
        transition: transform 0.2s;
    }

    .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Customize FullCalendar colors */
    .fc-event {
        border-color: transparent;
        cursor: pointer;
    }

    .fc-day-today {
        background-color: rgba(115, 102, 255, 0.05) !important;
    }

    #calendar {
        opacity: 0;
        transition: opacity 0.5s ease;
    }
</style>
@endpush

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Performance Review</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item active">Performance Review</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Upcoming Reviews Sidebar -->
        <div class="col-xl-3 col-md-4 box-col-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Upcoming Reviews</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                        @forelse($upcomingReviews as $ur)
                        <div class="list-group-item d-flex align-items-center mb-3 border-0 shadow-sm rounded review-card p-3 upcoming-review-item"
                            style="cursor: pointer;"
                            data-user-id="{{ $ur['user_id'] }}"
                            data-name="{{ $ur['name'] }}"
                            data-dept="{{ $ur['department'] }}">
                            <img class="rounded-circle me-3" src="{{ $ur['profile_pic'] ? asset('storage/'.$ur['profile_pic']) : 'https://ui-avatars.com/api/?name='.urlencode($ur['name']) }}" width="50" height="50" alt="" style="object-fit:cover;">
                            <div>
                                <h6 class="mb-0 text-primary">{{ $ur['name'] }}</h6>
                                <p class="mb-0 small text-muted">{{ $ur['designation'] }}</p>
                                <small class="text-danger fw-bold">Due: {{ \Carbon\Carbon::parse($ur['date'])->format('d M, Y') }}</small>
                                @if($ur['pending_count'] > 1)
                                <div class="mt-1">
                                    <span class="badge badge-light-danger text-danger px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fa fa-warning me-1"></i>{{ $ur['pending_count'] }} Pending Reviews
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center p-4">
                            <i class="fa fa-calendar-check-o text-success mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted">No reviews due soon.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-md-8 box-col-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Performance Reviews</h5>
                    <div class="d-flex gap-2">
                        @if(checkMenu(Session::get('role_id'), 26, 'read'))
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportReviewModal">Export to Excel</button>
                        @endif
                        @if(checkMenu(Session::get('role_id'), 26, 'create'))
                        <button class="btn btn-primary btn-sm" type="button" id="create-review-btn">Create New Review</button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="performanceTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="false">
                                Overview
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="calendar-tab-btn" data-bs-toggle="tab" data-bs-target="#calendar-tab" type="button" role="tab" aria-controls="calendar-tab" aria-selected="true">
                                Calendar View
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab" aria-controls="create" aria-selected="false">
                                Create/Edit Review
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Filter by Dealership</label>
                                    <select class="form-control select2" id="filter_dealership_id">
                                        <option value="">All Dealerships</option>
                                        @foreach($dealerships as $dealership)
                                        <option value="{{ $dealership->id }}">{{ $dealership->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="display datatables table-striped" id="performance-reviews-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Employee Name</th>
                                            <th>Reviewer Name</th>
                                            <th>Review Date</th>
                                            <th>Period</th>
                                            <!-- Removed Average Rating Column -->
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade show active" id="calendar-tab" role="tabpanel" aria-labelledby="calendar-tab-btn">
                            <div class="p-3">
                                <div id="calendar-loader" class="text-center p-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading Calendar...</p>
                                </div>
                                <div id="calendar"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                            <form action="{{ route('performance-review.store') }}" method="POST" id="create-review-form" enctype="multipart/form-data">
                                @csrf
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6>Review Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-4">
                                                <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                                <select class="form-control select2" id="employee_id" name="employee_id" required>
                                                    <option value="">Select Employee</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="review_year">Review Year <span class="text-danger">*</span></label>
                                                <select class="form-control select2" id="review_year" name="review_year" required>
                                                    <option value="">Select Year</option>
                                                </select>
                                                <small class="text-muted" id="year-hint">Select the anniversary year this review is for.</small>
                                            </div>
                                            <!-- Review Date is set automatically -->

                                            <!-- Review Period is set automatically -->
                                            <div class="form-group col-md-4">
                                                <label for="final_report">Final Report (PDF)</label>
                                                <input type="file" class="form-control" id="final_report" name="final_report" accept="application/pdf">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3" id="employee-history-card" style="display: none;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Employee Review History</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <strong>Joining Date:</strong> <span id="history-joining-date">-</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Next Review Due:</strong> <span id="history-next-review" class="text-primary fw-bold">-</span>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered border-bottom">
                                                <thead>
                                                    <tr>
                                                        <th>Review Year</th>
                                                        <th>Actual Date</th>
                                                        <th>Period</th>
                                                        <th>Reviewer</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="employee-history-body">
                                                    <!-- History rows will be added here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>



                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6>Review/Comments</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="initial_comment">Enter your review comments here:</label>
                                            <textarea class="form-control" id="initial_comment" name="initial_comment" rows="5" placeholder="Write your detailed performance review here..."></textarea>
                                            <small class="text-muted">This will be added as the first comment to the review.</small>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Submit Review</button>
                                <button type="reset" class="btn btn-secondary mt-3">Reset</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Performance Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="view-review-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this performance review? This action cannot be undone.</p>
                <input type="hidden" id="delete-review-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-review-btn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Review Modal -->
<div class="modal fade" id="exportReviewModal" tabindex="-1" role="dialog" aria-labelledby="exportReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportReviewModalLabel">Export Performance Reviews</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('performance-review.export') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_from_date" class="form-label">From Date (Review Date)</label>
                                <input type="date" class="form-control" id="export_from_date" name="from_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_to_date" class="form-label">To Date (Review Date)</label>
                                <input type="date" class="form-control" id="export_to_date" name="to_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" data-bs-dismiss="modal">Export Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Date Reviews Modal -->
<div class="modal fade" id="dateReviewsModal" tabindex="-1" aria-labelledby="dateReviewsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateReviewsModalLabel">Reviews for...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="dateReviewsModalBody">
                <!-- Reviews will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
    $(document).ready(function() {
        // FullCalendar Initialization
        var calendarEl = document.getElementById('calendar');
        var calendar = null;

        function initCalendar() {
            if (calendar) return;

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                dayMaxEvents: true, // Automatically handle event stacking
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                events: @json($events),
                dateClick: function(info) {
                    var clickedDateStr = info.dateStr;
                    var eventsOnDate = calendar.getEvents().filter(function(event) {
                        // Compare only the date part of the event start time
                        var eventDate = new Date(event.start);
                        var eventDateStr = eventDate.getFullYear() + '-' + String(eventDate.getMonth() + 1).padStart(2, '0') + '-' + String(eventDate.getDate()).padStart(2, '0');
                        return eventDateStr === clickedDateStr;
                    });

                    var modalBody = $('#dateReviewsModalBody');
                    modalBody.empty();

                    if (eventsOnDate.length > 0) {
                        var content = '<div class="list-group list-group-flush">';
                        eventsOnDate.forEach(function(event) {
                            const props = event.extendedProps;
                            const profilePic = props.profile_pic || `https://ui-avatars.com/api/?name=${encodeURIComponent(event.title)}&color=7F9CF5&background=EBF4FF`;
                            
                            let actionButton;
                            if (props.review_id) {
                                actionButton = `<a href="/performance-review/${props.review_id}" target="_blank" class="btn btn-sm btn-info ms-auto">View Details</a>`;
                            } else {
                                actionButton = `<button class="btn btn-sm btn-primary ms-auto create-review-from-modal" data-user-id="${props.user_id}" data-user-name="${event.title}" data-dept-name="${props.department || ''}" data-review-year="${props.review_year || ''}">Create Review</button>`;
                            }

                            content += `
                                <div class="list-group-item d-flex align-items-center">
                                    <img src="${profilePic}" class="rounded-circle me-3" width="40" height="40" style="object-fit:cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 text-primary">${event.title}</h6>
                                        <p class="mb-1 small text-muted">${props.designation || ''}</p>
                                    </div>
                                    ${actionButton}
                                </div>
                            `;
                        });
                        content += '</div>';
                        modalBody.html(content);
                    } else {
                        modalBody.html('<p class="text-center text-muted p-3">No reviews scheduled for this date.</p>');
                    }
                    
                    var modalTitle = new Date(info.dateStr + 'T00:00:00'); // Ensure correct date parsing
                    $('#dateReviewsModalLabel').text('Reviews for ' + modalTitle.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }));
                    
                    var dateReviewsModal = new bootstrap.Modal(document.getElementById('dateReviewsModal'));
                    dateReviewsModal.show();
                },
                eventClick: function(info) {
                    var reviewId = info.event.extendedProps.review_id;
                    if (reviewId) {
                        window.open('/performance-review/' + reviewId, '_blank');
                        return;
                    }

                    // If it's a pending event, trigger the dateClick handler for that date
                    // to show the modal with all events for the day.
                    // FullCalendar's trigger method for dateClick expects a specific object structure.
                    var startDate = info.event.start; // JS Date in local timezone
                    var localDateStr = startDate.getFullYear() + '-' + String(startDate.getMonth() + 1).padStart(2, '0') + '-' + String(startDate.getDate()).padStart(2, '0');
                    var dateClickInfo = {
                        date: info.event.start, // The Date object
                        // Avoid toISOString() here (UTC conversion can shift the date back a day in positive timezones)
                        dateStr: localDateStr, // YYYY-MM-DD string in local timezone
                        allDay: true, // Assuming events on the calendar are typically allDay or we want to treat them as such for dateClick
                        dayEl: info.el, // The DOM element for the day cell
                        jsEvent: info.jsEvent,
                        view: info.view // The current view object
                    };
                    calendar.trigger('dateClick', dateClickInfo);
                },
                eventContent: function(arg) {
                    let customHtml = '';
                    customHtml += '<div class="fc-event-main-frame d-flex align-items-center gap-1 p-1">';
                    if (arg.event.extendedProps.profile_pic) {
                        customHtml += '<img src="' + arg.event.extendedProps.profile_pic + '" style="width:18px;height:18px;border-radius:50%;object-fit:cover;margin-right:4px;">';
                    }
                    customHtml += '<div class="fc-event-title-container"><div class="fc-event-title fc-sticky" style="font-size: 0.85em;">' + arg.event.title + '</div></div>';
                    customHtml += '</div>';

                    return {
                        html: customHtml
                    }
                },
                eventDidMount: function(info) {
                    info.el.title = info.event.title + " (" + info.event.extendedProps.designation + ")";
                }
            });

            calendar.render();

            setTimeout(function() {
                if (document.getElementById('calendar-loader')) document.getElementById('calendar-loader').style.display = 'none';
                document.getElementById('calendar').style.opacity = '1';
                calendar.updateSize();
            }, 300);
        }

        // Initialize calendar on page load
        initCalendar();

        // Initialize calendar when tab is shown
        $('button[data-bs-target="#calendar-tab"]').on('shown.bs.tab', function() {
            initCalendar();
            if (calendar) {
                calendar.updateSize();
            }
        });


        // Handle Create button click
        $('#create-review-btn').on('click', function() {
            $('#create-tab').tab('show');
        });

        // Initialize Select2 for Dealership Filter (Static options)
        $('#filter_dealership_id').select2({
            width: '100%',
            placeholder: 'All Dealerships',
            allowClear: true
        });

        // Initialize Select2 for Employee Search (AJAX)
        $('#employee_id').select2({
            width: '100%',
            ajax: {
                url: "{{ route('employees.search') }}",
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
                        results: data.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.text + (item.department_name ? ' (' + item.department_name + ')' : ''),
                                profile_pic: item.profile_pic
                            };
                        }),
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                },
                cache: true
            },
            placeholder: 'Search for an employee',
            minimumInputLength: 0,
            templateResult: formatEmployee,
            templateSelection: formatEmployeeSelection,
            escapeMarkup: function(m) {
                return m;
            }
        });

        function formatEmployee(state) {
            if (!state.id) {
                return state.text;
            }
            var avatar = state.profile_pic ? state.profile_pic : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(state.text);
            var $state = $(
                '<div class="d-flex align-items-center">' +
                '<img src="' + avatar + '" class="rounded-circle me-2" width="30" height="30" style="object-fit: cover;">' +
                '<span>' + state.text + '</span>' +
                '</div>'
            );
            return $state;
        }

        function formatEmployeeSelection(state) {
            if (!state.id) {
                return state.text;
            }
            var avatar = state.profile_pic ? state.profile_pic : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(state.text);
            var $state = $(
                '<div class="d-flex align-items-center">' +
                '<img src="' + avatar + '" class="rounded-circle me-2" width="20" height="20" style="object-fit: cover;">' +
                '<span>' + state.text + '</span>' +
                '</div>'
            );
            return $state;
        }

        $('#filter_dealership_id').change(function() {
            table.draw();
        });

        var table = $('#performance-reviews-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('performance-review.index') }}",
                data: function(d) {
                    d.dealership_id = $('#filter_dealership_id').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'employee_name',
                    name: 'employee.employee.name'
                },
                {
                    data: 'reviewer_name',
                    name: 'reviewer.employee.name'
                },
                {
                    data: 'review_date',
                    name: 'review_date'
                },
                {
                    data: 'review_period',
                    name: 'review_period'
                },
                // Removed average_rating column
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });


        // Fetch Employee History on Selection
        $('#employee_id').on('change', function() {
            var employeeId = $(this).val();

            // Reset form state
            $('#eligibility-alert').remove();
            $('#create-review-form').find('input:not(#employee_id), textarea, button[type="submit"]').prop('disabled', false);

            if (employeeId) {
                $.ajax({
                    url: '/performance-review/employee-history/' + employeeId,
                    type: 'GET',
                    success: function(response) {
                        $('#employee-history-card').show();
                        $('#history-joining-date').text(response.joining_date || 'N/A');
                        $('#history-next-review').text(response.next_review_date || 'N/A');

                        // Populate Year Dropdown
                        var yearSelect = $('#review_year');
                        yearSelect.empty().append('<option value="">Select Year</option>');
                        if (response.years_list.length > 0) {
                            $.each(response.years_list, function(index, item) {
                                var option = $('<option></option>')
                                    .val(item.year)
                                    .text(item.label);
                                if (item.status === 'completed') {
                                    option.prop('disabled', true).addClass('text-muted');
                                }
                                yearSelect.append(option);
                            });
                        }

                        var autoSelectYear = yearSelect.data('auto-select-year');
                        if (autoSelectYear) {
                            yearSelect.val(autoSelectYear).trigger('change');
                            yearSelect.removeData('auto-select-year');
                        }

                        var historyBody = $('#employee-history-body');
                        historyBody.empty();

                        if (response.reviews.length > 0) {
                            $.each(response.reviews, function(index, review) {
                                var row = `
                                        <tr>
                                            <td><span class="badge badge-light-primary text-primary">${review.review_year || 'N/A'}</span></td>
                                            <td>${review.review_date}</td>
                                            <td>${review.review_period}</td>
                                            <td>${review.reviewer && review.reviewer.employee ? review.reviewer.employee.name : 'N/A'}</td>
                                            <td>
                                                <a href="/performance-review/${review.id}" target="_blank" class="btn btn-sm btn-info text-white"><i class="fa fa-eye"></i> View</a>
                                                <button type="button" class="btn btn-sm btn-danger delete-history-btn" data-id="${review.id}"><i class="fa fa-trash"></i> Delete</button>
                                            </td>
                                        </tr>
                                    `;
                                historyBody.append(row);
                            });
                        } else {
                            historyBody.append('<tr><td colspan="5" class="text-center">No previous reviews found.</td></tr>');
                        }

                        // Check eligibility ONLY if not in edit mode
                        var isEditMode = $('#create-review-form input[name="_method"]').val() === 'PUT';

                        if (!isEditMode && response.next_review_date) {
                            var nextReview = new Date(response.next_review_date);
                            var today = new Date();
                            today.setHours(0, 0, 0, 0);

                            if (today < nextReview) {
                                var alertHtml = '<div id="eligibility-alert" class="alert alert-warning alert-dismissible fade show mt-3" role="alert">' +
                                    '<i class="fa fa-exclamation-triangle me-2"></i>' +
                                    '<strong>Note:</strong> According to records, the next review for this employee is due on <strong>' + response.next_review_date + '</strong>. You may still proceed if this is an out-of-cycle review.' +
                                    '</div>';
                                $('#employee-history-card').after(alertHtml);
                                // Warning only, do not disable
                            }
                        }
                    },
                    error: function() {
                        console.error('Failed to fetch employee history');
                    }
                });
            } else {
                $('#employee-history-card').hide();
            }
        });

        // Open Delete Confirmation Modal for history table
        $(document).on('click', '.delete-history-btn', function() {
            var id = $(this).data('id');
            $('#delete-review-id').val(id);
            $('#deleteReviewModal').modal('show');
        });

        // Handle Confirm Delete Logic
        $('#confirm-delete-review-btn').on('click', function() {
            var id = $('#delete-review-id').val();
            $('#deleteReviewModal').modal('hide'); // Hide modal immediately

            $.ajax({
                url: '/performance-review/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    // Refresh the history by triggering change on the select
                    $('#employee_id').trigger('change');
                    // Also refresh the main table if it's visible
                    table.draw();
                },
                error: function() {
                    showToast('Failed to delete review.', 'danger');
                }
            });
        });

        $('#create-review-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = new FormData(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    table.draw();
                    showToast(response.message, 'success');
                    if (response.redirect_url) {
                        setTimeout(function() {
                            window.location.href = response.redirect_url;
                        }, 1000); // Redirect after 1 second to let toast show
                    } else {
                        form[0].reset();
                        $('#employee_id').val('').trigger('change');
                        $('#employee-history-card').hide(); // Hide history on success
                        $('#overview-tab').tab('show');
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = 'Error creating review.';
                    if (errors) {
                        errorMessage = Object.values(errors).flat().join('');
                    }
                    showToast(errorMessage, 'danger');
                }
            });
        });

        // View functionality - Redirect to show page
        $('#performance-reviews-table').on('click', '.view-review-btn', function() {
            var id = $(this).data('id');
            window.location.href = '/performance-review/' + id;
        });

        // Edit functionality
        $('#performance-reviews-table').on('click', '.edit-review-btn', function() {
            var id = $(this).data('id');
            $.get('/performance-review/' + id + '/edit', function(data) {
                $('#create-review-form').attr('action', '/performance-review/' + id);
                $('#create-review-form').append('<input type="hidden" name="_method" value="PUT">');

                // Pre-select employee using an Option tag since it's AJAX loaded
                if (data.employee && data.employee.employee) {
                    var employeeName = data.employee.employee.name + (data.employee.employee.department ? ' (' + data.employee.employee.department.name + ')' : '');
                    var newOption = new Option(employeeName, data.employee_id, true, true);
                    $('#employee_id').append(newOption).trigger('change');
                } else {
                    $('#employee_id').val(data.employee_id).trigger('change');
                }

                // Handle Year pre-selection (need to wait for AJAX triggered by change)
                if (data.review_year) {
                    var checkYear = setInterval(function() {
                        if ($('#review_year option[value="' + data.review_year + '"]').length) {
                            $('#review_year').val(data.review_year).trigger('change');
                            clearInterval(checkYear);
                        }
                    }, 100);
                    // Timeout safety
                    setTimeout(function() {
                        clearInterval(checkYear);
                    }, 3000);
                }



                /**
                 * Criteria removed.
                 * If editing, the initial comment is already saved as a comment.
                 * We generally don't edit the initial comment here, but rather through the comment system.
                 * However, for simplicity, we focus on the review metadata here.
                 */

                $('#create-tab').tab('show');
                $('html, body').animate({
                    scrollTop: $("#create-tab").offset().top
                }, 500);
            });
        });

        // Handle click on upcoming review items
        $(document).on('click', '.upcoming-review-item', function() {
            var userId = $(this).data('user-id');
            var userName = $(this).data('name');
            var deptName = $(this).data('dept');

            // Format name version for Select2
            var formattedName = userName + (deptName ? ' (' + deptName + ')' : '');

            // Create option and select it
            var newOption = new Option(formattedName, userId, true, true);
            $('#employee_id').empty().append(newOption).trigger('change');

            // Switch tab
            $('#create-tab').tab('show');

            // Scroll to form
            $('html, body').animate({
                scrollTop: $("#create-tab").offset().top - 100
            }, 500);
        });

        // Handle create review from calendar modal
        $(document).on('click', '.create-review-from-modal', function() {
            var modal = bootstrap.Modal.getInstance(document.getElementById('dateReviewsModal'));
            modal.hide();

            var userId = $(this).data('user-id');
            var userName = $(this).data('user-name');
            var deptName = $(this).data('dept-name');
            var reviewYear = $(this).data('review-year');

            var formattedName = userName + (deptName ? ' (' + deptName + ')' : '');
            
            if (reviewYear) {
                $('#review_year').data('auto-select-year', reviewYear);
            }

            var newOption = new Option(formattedName, userId, true, true);
            $('#employee_id').empty().append(newOption).trigger('change');

            $('#create-tab').tab('show');

            $('html, body').animate({
                scrollTop: $("#create-tab").offset().top - 100
            }, 500);
        });
    });

    // Reset form when switching to create tab manually
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if (e.target.id === 'create-tab') {
            // Check if we are in edit mode (method field exists)
            if ($('#create-review-form input[name="_method"]').length === 0) {
                $('#create-review-form')[0].reset();
                $('#employee_id').val('').trigger('change');
                $('#employee-history-card').hide(); // Hide history on reset
                $('#create-review-form').attr('action', "{{ route('performance-review.store') }}");
            }
        }
    });

    // Reset form handler
    $('#create-review-form').on('reset', function() {
        $(this).find('input[name="_method"]').remove();
        $(this).attr('action', "{{ route('performance-review.store') }}");
        $('#employee_id').val('').trigger('change');
        $('#employee-history-card').hide(); // Hide history on reset
    });
</script>
@endpush
