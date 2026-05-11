@extends('layouts.admin')

@section('title', 'Attendance')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Attendance</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">HR</li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<style>
    .live-status-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Attendance</h5>
                    <div class="d-flex gap-2">
                        @if($canViewSubordinates)
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportAttendanceModal">Export to Excel</button>
                        @endif
                        <a href="{{ route('attendance.calendar') }}" class="btn btn-primary">Calendar View</a>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs nav-primary mb-4" id="attendanceTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="live-status-tab" data-bs-toggle="tab" href="#live-status" role="tab" aria-controls="live-status" aria-selected="true">
                                <i class="icofont icofont-ui-play me-2"></i>Live Status
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="attendance-log-tab" data-bs-toggle="tab" href="#attendance-log" role="tab" aria-controls="attendance-log" aria-selected="false">
                                <i class="icofont icofont-list me-2"></i>Attendance Log
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="attendanceTabsContent">
                        <!-- Live Status Tab -->
                        <div class="tab-pane fade show active" id="live-status" role="tabpanel" aria-labelledby="live-status-tab">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="text-muted">Real-time Employee Availability</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="live-status-container">
                                <!-- Live status cards will be loaded here -->
                                <div class="col-12 text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading status...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Fetching live status...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Log Tab -->
                        <div class="tab-pane fade" id="attendance-log" role="tabpanel" aria-labelledby="attendance-log-tab">

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="date-filter">Date</label>
                            <input type="text" id="date-filter" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="dealership-filter">Dealership</label>
                            <select id="dealership-filter" class="form-control select2" @if((!$canViewSubordinates && !$canViewAllAttendance) || ($userDealershipId && !$canViewAllAttendance)) disabled @endif>
                                <option value="">All Dealerships</option>
                                @foreach($dealerships as $dealership)
                                    <option value="{{ $dealership->id }}" @if(($userDealershipId ?: null) == $dealership->id) selected @endif>{{ $dealership->name }}</option>
                                @endforeach
                            </select>
                            @if((!$canViewSubordinates && !$canViewAllAttendance) || ($userDealershipId && !$canViewAllAttendance))
                                <input type="hidden" name="dealership_id" value="{{ $userDealershipId }}">
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label for="department-filter">Department</label>
                            <select id="department-filter" class="form-control select2" @if((!$canViewSubordinates && !$canViewAllAttendance) || ($userDepartmentId && !$canViewAllAttendance)) disabled @endif>
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @if(($userDepartmentId ?: null) == $department->id) selected @endif>{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @if((!$canViewSubordinates && !$canViewAllAttendance) || ($userDepartmentId && !$canViewAllAttendance))
                                <input type="hidden" name="department_id" value="{{ $userDepartmentId }}">
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label for="employee-filter">Employee</label>
                            <select id="employee-filter" class="form-control select2" @if(!$canViewSubordinates && !$canViewAllAttendance) disabled @endif>
                                @if($canViewSubordinates || $canViewAllAttendance)
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
                        <div class="col-md-3">
                            <label for="filter-type">Filter Type</label>
                            <select id="filter-type" class="form-control select2">
                                <option value="attendees">Attendees Only</option>
                                <option value="absents">Absents Only</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="attendance-table">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Employee</th>
                                    <th>Clock In Time</th>
                                    <th>Clock Out Time</th>
                                    <th>Remarks</th>
                                    <th>Task Ran Time</th>
                                    <th>GPS</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                        </div>
                    </div>
                </div>
    </div>
</div>
<!-- Export Attendance Modal -->
<div class="modal fade" id="exportAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="exportAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportAttendanceModalLabel">Export Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('attendance.export') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="export_dealership_id" class="form-label">Dealership</label>
                            <select class="form-control select2-modal" id="export_dealership_id" name="dealership_id" @if($userDealershipId && !$canViewAllAttendance) disabled @endif>
                                <option value="">All Dealerships</option>
                                @foreach($dealerships as $dealership)
                                    <option value="{{ $dealership->id }}" @if($userDealershipId == $dealership->id) selected @endif>{{ $dealership->name }}</option>
                                @endforeach
                            </select>
                            @if($userDealershipId && !$canViewAllAttendance)
                                <input type="hidden" name="dealership_id" value="{{ $userDealershipId }}">
                            @endif
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="export_department_id" class="form-label">Department</label>
                            <select class="form-control select2-modal" id="export_department_id" name="department_id" @if($userDepartmentId && !$canViewAllAttendance) disabled @endif>
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @if($userDepartmentId == $department->id) selected @endif>{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @if($userDepartmentId && !$canViewAllAttendance)
                                <input type="hidden" name="department_id" value="{{ $userDepartmentId }}">
                            @endif
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="export_employee_id" class="form-label">Employee (Optional)</label>
                            <select class="form-control select2-modal" id="export_employee_id" name="employee_id">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                    <button type="submit" class="btn btn-success">Export Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- GPS Map Modal -->
<div class="modal fade" id="gpsMapModal" tabindex="-1" role="dialog" aria-labelledby="gpsMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gpsMapModalLabel">Attendance Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="attendanceMap" style="height: 450px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>


@endsection



@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=marker"></script>
<script>
    $(document).ready(function() {
        const currentEmployeeId = {{ auth()->user()->employee->id ?? 'null' }};

        // Live Status Logic
        function fetchLiveStatus() {
            $.ajax({
                url: "{{ route('attendance.live-status') }}",
                method: 'GET',
                success: function(data) {
                    renderLiveStatus(data);
                },
                error: function(xhr) {
                    console.error('Error fetching live status:', xhr);
                }
            });
        }

        function renderLiveStatus(employees) {
            const container = $('#live-status-container');
            container.empty();

            if (employees.length === 0) {
                container.append('<div class="col-12 text-center py-5"><p class="text-muted">No employees found.</p></div>');
                return;
            }

            employees.forEach(employee => {
                let agentStatusBadge = '';
                switch(employee.agent_status.toLowerCase()) {
                    case 'available':
                        agentStatusBadge = '<span class="badge badge-success">Online</span>';
                        break;
                    case 'busy':
                        agentStatusBadge = '<span class="badge badge-warning">On Call</span>';
                        break;
                    case 'connecting':
                        agentStatusBadge = '<span class="badge badge-info">Connecting</span>';
                        break;
                    default:
                        agentStatusBadge = '<span class="badge badge-secondary">Offline</span>';
                }

                let attendanceBadge = '';
                if (employee.attendance_status === 'Present') {
                    attendanceBadge = '<span class="badge badge-light-success border-success text-success">Present</span>';
                } else if (employee.attendance_status === 'On Leave') {
                    attendanceBadge = '<span class="badge badge-light-warning border-warning text-warning">On Leave</span>';
                } else if (employee.attendance_status === 'Present (Comp)') {
                    attendanceBadge = '<span class="badge badge-light-info border-info text-info">Comp. Work</span>';
                } else {
                    attendanceBadge = '<span class="badge badge-light-danger border-danger text-danger">Absent</span>';
                }

                const isYou = employee.id === currentEmployeeId ? ' <span class="badge badge-light-primary" style="font-size: 10px; vertical-align: middle;">(You)</span>' : '';

                const card = `
                    <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                        <div class="card h-100 mb-0 shadow-sm border-0 live-status-card" style="border-radius: 15px; transition: all 0.3s ease; ${employee.id === currentEmployeeId ? 'border: 1.5px solid #7366ff !important; box-shadow: 0 15px 35px rgba(115, 102, 255, 0.15) !important; transform: translateY(-5px);' : 'box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;'}">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="position-relative">
                                        <img src="${employee.profile_pic}" alt="${employee.name}" class="rounded-circle shadow-sm" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #fff;">
                                        <span class="position-absolute bottom-0 end-0 translate-middle p-1 rounded-circle ${employee.agent_status === 'available' ? 'bg-success' : (employee.agent_status === 'busy' ? 'bg-warning' : 'bg-secondary')}" style="width: 12px; height: 12px; border: 2px solid #fff;"></span>
                                    </div>
                                    <div class="ms-3 overflow-hidden">
                                        <h6 class="mb-0 text-truncate f-w-600">${employee.name}${isYou}</h6>
                                        <small class="text-muted text-truncate d-block">${employee.designation || 'Employee'}</small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="f-w-600 text-muted">Attendance</small>
                                    ${attendanceBadge}
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="f-w-600 text-muted">Call Status</small>
                                    ${agentStatusBadge}
                                </div>
                                <div class="border-top pt-2 d-flex justify-content-between">
                                    <small class="text-muted f-11">Last Activity</small>
                                    <small class="text-muted f-11 f-w-600">${employee.last_activity}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        fetchLiveStatus();
        setInterval(fetchLiveStatus, 4000); // Poll every 4 seconds

        // Existing Attendance Log Logic
        var table = $('#attendance-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('attendance.index') }}",
                data: function(d) {
                    d.date = $('#date-filter').val();
                    d.employee_id = $('#employee-filter').val();
                    d.dealership_id = $('#dealership-filter').val();
                    d.department_id = $('#department-filter').val();
                    d.filter_type = $('#filter-type').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, // Sr. No. column
                {
                    data: 'employee_name',
                    name: 'employee_name',
                    render: function(data, type, row) {
                        var profilePicSrc = row.profile_pic ? "{{ asset('storage') }}/" + row.profile_pic : "{{ asset('admin/assets/images/dashboard/profile.png') }}";
                        var designationBadge = row.designation ? '<span class="badge bg-primary me-1">' + row.designation + '</span>' : '';
                        var departmentBadge = row.department_name ? '<span class="badge bg-info">' + row.department_name + '</span>' : '';

                        return '<div class="d-flex align-items-center">' +
                            '<img src="' + profilePicSrc + '" alt="Profile Picture" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">' +
                            '<div>' +
                            '<h6 class="mb-0">' + row.employee_name + '</h6>' +
                            '<small>' + designationBadge + departmentBadge + '</small>' +
                            '</div>' +
                            '</div>';
                    }
                },
                {
                    data: 'clock_in_time',
                    name: 'clock_in_time'
                },
                {
                    data: 'clock_out_time',
                    name: 'clock_out_time'
                },
                {
                    data: 'remarks',
                    name: 'remarks',
                    render: function(data, type, row) {
                        return data ? '<span title="' + data + '">' + (data.length > 30 ? data.substring(0, 30) + '...' : data) + '</span>' : 'N/A';
                    }
                },
                {
                    data: 'total_task_time',
                    name: 'total_task_time'
                },
                {
                    data: 'gps',
                    name: 'gps',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if ((row.clock_in_latitude && row.clock_in_longitude) || (row.clock_out_latitude && row.clock_out_longitude)) {
                            return '<button class="btn btn-xs btn-primary view-gps-btn" ' +
                                'data-in-lat="' + (row.clock_in_latitude || '') + '" ' +
                                'data-in-lng="' + (row.clock_in_longitude || '') + '" ' +
                                'data-out-lat="' + (row.clock_out_latitude || '') + '" ' +
                                'data-out-lng="' + (row.clock_out_longitude || '') + '" ' +
                                '><i class="fa fa-map-marker-alt"></i> GPS</button>';
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'employee_id',
                    name: 'employee_id',
                    visible: false
                },
            ]
        });

        $('#date-filter').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        }).on('changeDate', function(e) {
            table.ajax.reload();
        });

        $('#employee-filter, #dealership-filter, #department-filter, #filter-type').on('change', function() {
            table.ajax.reload();
        });

        $('.select2').select2();
        $('.select2-modal').select2({
            dropdownParent: $('#exportAttendanceModal')
        });

        $('#attendance-table tbody').on('click', 'tr', function(e) {
            // Prevent redirection if the clicked element is the GPS button or inside it
            if ($(e.target).closest('.view-gps-btn').length) {
                return;
            }
            var data = table.row(this).data();
            var employeeId = data.employee_id;
            var date = $('#date-filter').val();
            window.location.href = '/attendance/' + employeeId + '?date=' + date;
        });

        $('#attendance-table').on('click', '.view-gps-btn', function(e) {
            e.stopPropagation();
            var inLat = $(this).data('in-lat');
            var inLng = $(this).data('in-lng');
            var outLat = $(this).data('out-lat');
            var outLng = $(this).data('out-lng');

            $('#gpsMapModal').modal('show');

            // Initialize map after modal is shown
            setTimeout(function() {
                initAttendanceMap(inLat, inLng, outLat, outLng);
            }, 500);
        });

        var map;
        var markers = [];

        function initAttendanceMap(inLat, inLng, outLat, outLng) {
            var centerLat = inLat || outLat || 0;
            var centerLng = inLng || outLng || 0;

            var mapOptions = {
                center: {
                    lat: parseFloat(centerLat),
                    lng: parseFloat(centerLng)
                },
                zoom: 15,
                mapId: "DEMO_MAP_ID",
            };

            if (!map) {
                map = new google.maps.Map(document.getElementById('attendanceMap'), mapOptions);
            } else {
                map.setCenter({
                    lat: parseFloat(centerLat),
                    lng: parseFloat(centerLng)
                });
                map.setZoom(15);
            }

            // Clear previous markers
            markers.forEach(function(marker) {
                marker.map = null;
            });
            markers = [];

            var bounds = new google.maps.LatLngBounds();
            var hasPoints = false;

            if (inLat && inLng) {
                var inPos = {
                    lat: parseFloat(inLat),
                    lng: parseFloat(inLng)
                };
                var markerIn = new google.maps.marker.AdvancedMarkerElement({
                    position: inPos,
                    map: map,
                    title: 'Clock In',
                    content: new google.maps.marker.PinElement({
                        glyph: 'IN',
                        background: '#00E676',
                        borderColor: '#00C853',
                        glyphColor: 'white'
                    })
                });
                markers.push(markerIn);
                bounds.extend(inPos);
                hasPoints = true;
            }

            if (outLat && outLng) {
                var outPos = {
                    lat: parseFloat(outLat),
                    lng: parseFloat(outLng)
                };
                var markerOut = new google.maps.marker.AdvancedMarkerElement({
                    position: outPos,
                    map: map,
                    title: 'Clock Out',
                    content: new google.maps.marker.PinElement({
                        glyph: 'OUT',
                        background: '#FF5252',
                        borderColor: '#D32F2F',
                        glyphColor: 'white'
                    })
                });
                markers.push(markerOut);
                bounds.extend(outPos);
                hasPoints = true;
            }

            if (hasPoints && (markers.length > 1)) {
                map.fitBounds(bounds);
            } else if (hasPoints) {
                map.setCenter(bounds.getCenter());
                map.setZoom(15);
            }
        }
    });
</script>
@endpush
