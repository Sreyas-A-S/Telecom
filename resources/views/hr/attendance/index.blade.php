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
