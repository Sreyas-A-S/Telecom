@extends('layouts.admin')

@section('title', 'Birthday Calendar')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    .fc-toolbar-title {
        font-size: 1.25rem !important;
    }

    .birthday-card {
        border-left: 4px solid #7366ff;
        transition: transform 0.2s;
    }

    .birthday-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Customize FullCalendar colors */
    .fc-event {
        border-color: transparent;
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
                <h3>Birthday Calendar</h3>
            </div>
            <div class="col-6">
                <div class="d-flex justify-content-end align-items-center h-100">
                    <ol class="breadcrumb mb-0 me-3">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item">HR</li>
                        <li class="breadcrumb-item">Birthdays</li>
                    </ol>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportBirthdayModal">
                        <i class="fa fa-file-excel-o"></i> Export to Excel
                    </button>
                    <a href="{{ route('birthdays.settings') }}" class="btn btn-outline-primary btn-sm ms-2">Settings</a>
                    <a href="{{ route('birthdays.logs') }}" class="btn btn-outline-info btn-sm ms-2">Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Upcoming Birthdays Sidebar -->
        <div class="col-xl-3 col-md-4 box-col-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Upcoming Birthdays</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @forelse($upcomingBirthdays as $birthday)
                        <div class="list-group-item d-flex align-items-center mb-2 border-0 shadow-sm rounded birthday-card p-3 upcoming-birthday-item"
                            style="cursor: pointer;"
                            data-name="{{ $birthday['name'] }}"
                            data-profile-pic="{{ $birthday['profile_pic'] ? asset('storage/'.$birthday['profile_pic']) : asset('admin/assets/images/dashboard/profile.png') }}"
                            data-designation="{{ $birthday['designation'] ?? '' }}"
                            data-department="{{ $birthday['department'] ?? '' }}"
                            data-dob="{{ $birthday['dob'] ?? '' }}"
                            data-event-date="{{ $birthday['date']->format('Y-m-d') }}">
                            <img class="rounded-circle me-3" src="{{ $birthday['profile_pic'] ? asset('storage/'.$birthday['profile_pic']) : asset('admin/assets/images/dashboard/profile.png') }}" width="50" height="50" alt="" style="object-fit:cover;">
                            <div>
                                <h6 class="mb-0">{{ $birthday['name'] }}</h6>
                                <small class="text-muted">{{ $birthday['date']->format('d M') }} (Turning {{ $birthday['age'] }})</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted">No upcoming birthdays soon.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="col-xl-9 col-md-8 box-col-8">
            <div class="card">
                <div class="card-body">
                    <div id="calendar-loader" class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading Calendar...</p>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Birthday Modal -->
<div class="modal fade" id="birthdayModal" tabindex="-1" role="dialog" aria-labelledby="birthdayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="birthdayModalLabel">Birthday Details</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalProfilePic" src="" alt="Profile Picture" class="rounded-circle mb-3" width="100" height="100" style="object-fit: cover;">
                <h4 id="modalName" class="mb-1"></h4>
                <p id="modalDesignation" class="text-muted mb-1"></p>
                <p id="modalDepartment" class="text-muted mb-3"></p>

                <div class="alert alert-light-primary" role="alert">
                    <h5 class="alert-heading text-primary mb-1">Happy Birthday!</h5>
                    <span id="modalBirthdayDate" class="fw-bold"></span>
                    <br>
                    <small id="modalAge" class="text-muted"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Birthday Modal -->
<div class="modal fade" id="exportBirthdayModal" tabindex="-1" role="dialog" aria-labelledby="exportBirthdayModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportBirthdayModalLabel">Export Birthdays</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('birthdays.export') }}" method="GET">
                <div class="modal-body">
                    <p>Select a date range to filter birthdays (Year is ignored, filters by Month & Day).</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_from_date" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="export_from_date" name="from_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_to_date" class="form-label">To Date</label>
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

@endsection

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
                right: 'dayGridMonth,listMonth'
            },
            events: @json($events),
            eventClick: function(info) {
                showBirthdayDetails({
                    title: info.event.title,
                    start: info.event.start,
                    extendedProps: info.event.extendedProps
                });
            },
            eventContent: function(arg) {
                // Custom rendering for event
                let customHtml = '';
                // We can add the image if we want, simplified version
                customHtml += '<div class="fc-event-main-frame d-flex align-items-center gap-1">';
                if (arg.event.extendedProps.profile_pic) {
                    customHtml += '<img src="' + arg.event.extendedProps.profile_pic + '" style="width:20px;height:20px;border-radius:50%;object-fit:cover;margin-right:4px;">';
                }
                customHtml += '<div class="fc-event-title-container"><div class="fc-event-title fc-sticky">' + arg.event.title + '</div></div>';
                customHtml += '</div>';

                return {
                    html: customHtml
                }
            },
            eventDidMount: function(info) {
                // Start tooltip logic if needed, but not implementing full tooltip lib for now
                info.el.title = info.event.title + " (" + info.event.extendedProps.designation + ")";
            }
        });
        // Shared function to show birthday details
        function showBirthdayDetails(data) {
            var props = data.extendedProps;
            var profilePic = props.profile_pic || '{{ asset("admin/assets/images/dashboard/profile.png") }}';

            document.getElementById('modalProfilePic').src = profilePic;
            document.getElementById('modalName').textContent = data.title;
            document.getElementById('modalDesignation').textContent = props.designation || '';
            document.getElementById('modalDepartment').textContent = props.department || '';

            // Format Date
            var date = new Date(data.start);
            var options = {
                day: 'numeric',
                month: 'long'
            };
            document.getElementById('modalBirthdayDate').textContent = date.toLocaleDateString('en-US', options);

            // Calculate Age
            if (props.dob) {
                var dobDate = new Date(props.dob);
                var dobYear = dobDate.getFullYear();
                var eventYear = date.getFullYear();
                var turnAge = eventYear - dobYear;
                document.getElementById('modalAge').textContent = 'Turning ' + turnAge + ' Years Old';
            } else {
                document.getElementById('modalAge').textContent = '';
            }

            var myModal = new bootstrap.Modal(document.getElementById('birthdayModal'));
            myModal.show();
        }

        // Handle click on upcoming birthday items
        document.querySelectorAll('.upcoming-birthday-item').forEach(function(item) {
            item.addEventListener('click', function() {
                var data = {
                    title: this.dataset.name,
                    start: this.dataset.eventDate,
                    extendedProps: {
                        profile_pic: this.dataset.profilePic,
                        designation: this.dataset.designation,
                        department: this.dataset.department,
                        dob: this.dataset.dob
                    }
                };
                showBirthdayDetails(data);
            });
        });

        calendar.render();

        // Hide loader and show calendar
        setTimeout(function() {
            if (document.getElementById('calendar-loader')) document.getElementById('calendar-loader').style.display = 'none';
            document.getElementById('calendar').style.opacity = '1';
        }, 300);
    });
</script>
@endpush