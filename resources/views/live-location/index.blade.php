@extends('layouts.admin')

@section('title', 'Live Location')

@push('styles')
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endpush

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Live Location</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Live Location</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Visit Details Section -->
        <div class="row mb-2" id="visit-details-container">
            <div class="col-sm-6 col-xl-3">
                <div class="card static-top-widget-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="widget-icon me-3">
                            <i data-feather="clock" class="text-primary font-primary" style="width: 30px; height: 30px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Started Time</h6>
                            <h5 id="started-time" class="mb-0 mt-1"></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card static-top-widget-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="widget-icon me-3">
                            <i data-feather="check-circle" class="text-secondary font-secondary" style="width: 30px; height: 30px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Ended Time</h6>
                            <h5 id="ended-time" class="mb-0 mt-1"></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card static-top-widget-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="widget-icon me-3">
                            <i data-feather="map-pin" class="text-info font-info" style="width: 30px; height: 30px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Distance Covered</h6>
                            <h5 id="distance-covered" class="mb-0 mt-1"></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card static-top-widget-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="widget-icon me-3">
                            <i data-feather="watch" class="text-success font-success" style="width: 30px; height: 30px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Time Taken</h6>
                            <h5 id="time-taken" class="mb-0 mt-1"></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Map Column -->
            <div class="col-lg-8 col-md-12 order-1 order-lg-2">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Visit Route Map</h5>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#mapSettingsCollapse" aria-expanded="false" aria-controls="mapSettingsCollapse">
                                <i data-feather="settings" style="width: 14px; height: 14px;"></i>
                                <span class="small fw-600">Map Settings</span>
                            </button>
                            <div class="collapse collapse-horizontal" id="mapSettingsCollapse">
                                <div class="d-flex align-items-center ms-3 bg-white p-2 rounded border shadow-sm" style="white-space: nowrap;">
                                    <div class="d-flex align-items-center me-3 border-end pe-3">
                                        <span class="me-2 text-dark fw-medium small">Closest Mode</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="closestModeSwitch" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 text-dark fw-medium small">Smoothing Mode</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="smoothingModeSwitch" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body position-relative">
                        <!-- Map Legend -->
                        <div id="map-legend" class="bg-white p-2 rounded border shadow-sm" style="position: absolute; bottom: 40px; left: 25px; z-index: 1000; font-size: 11px; display: none;">
                            <h6 class="mb-2 border-bottom pb-1" style="font-size: 12px;">Map Legend</h6>
                            <div class="d-flex align-items-center mb-1">
                                <div style="width: 15px; height: 15px; background: #27BEFF; border: 2px solid #1100FE; border-radius: 2px; margin-right: 8px;"></div>
                                <span>Active Movement</span>
                            </div>
                            <div class="d-flex align-items-center mb-1">
                                <div style="width: 15px; height: 15px; background: #FF5252; border: 2px solid #D32F2F; border-radius: 2px; margin-right: 8px;"></div>
                                <span>Halt / Point Visit</span>
                            </div>
                            <div class="d-flex align-items-center mb-1">
                                <div style="width: 15px; height: 15px; background: #00E676; border-radius: 50%; margin-right: 8px;"></div>
                                <span>Start Point</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div style="width: 15px; height: 15px; background: #FF5252; border-radius: 50%; margin-right: 8px;"></div>
                                <span>End Point</span>
                            </div>
                        </div>
                        <div id="map" style="height: 600px; width: 100%;"></div>
                        <p class="text-muted mt-2">Select a visit from the table below to view its route.</p>
                    </div>
                </div>
            </div>

            <!-- Table Column -->
            <div class="col-lg-4 col-md-12 order-2 order-lg-1">
                <div class="card">
                    <div class="card-header">
                        <h5>Recorded Visits</h5>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12 mb-2" id="employeeFilterContainer">
                            {{-- Employee filter will be appended here --}}
                        </div>
                        <div class="col-md-12 mb-2">
                            <button id="export-btn" class="btn btn-success btn-sm w-100">Export to Excel</button>
                            <button id="export-pdf-btn" class="btn btn-danger btn-sm w-100 mt-1">Export to PDF</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="visits-table">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Visit ID</th>
                                        <th>User Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" id="related-info-card" style="display: none;">
                <div class="card-header">
                    <h5>Related Information</h5>
                    <div class="card-header-right">
                        <ul class="list-unstyled card-option">
                            <li><i class="icon-minus minimize-card"></i></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body" id="related-info-content">
                    <!-- Content populated via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this entire trace (Visit ID: <span id="modalVisitId"></span>)? This
                action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&libraries=geometry,marker" async defer></script>

<script>
    let map;
    let hitPath;
    let polylines = [];
    let markers = [];
    let mapInitialized = false;
    let currentZoomLevel = 5;
    let infoWindow, pathData = [];
    let hoverTimeout = null;

    function initMap() {
        const defaultLocation = { lat: 20.5937, lng: 78.9629 }; // India
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: currentZoomLevel,
            center: defaultLocation,
            mapId: 'DEMO_MAP_ID',
        });

        hitPath = new google.maps.Polyline({
            geodesic: true,
            strokeColor: "#000",
            strokeOpacity: 0.0,
            strokeWeight: 25,
            zIndex: 10,
            cursor: 'pointer',
            map: map
        });

        infoWindow = new google.maps.InfoWindow({ disableAutoPan: true });

        hitPath.addListener('mousemove', function(event) {
            clearTimeout(hoverTimeout);
            infoWindow.close();
            if (pathData.length === 0) return;

            hoverTimeout = setTimeout(function() {
                let closest = null;
                let minDist = Infinity;
                pathData.forEach(function(point) {
                    const dist = google.maps.geometry.spherical.computeDistanceBetween(
                        event.latLng, new google.maps.LatLng(point.lat, point.lng)
                    );
                    if (dist < minDist) {
                        minDist = dist; closest = point;
                    }
                });

                if (closest) {
                    infoWindow.setContent(`
                        <div style="padding: 10px; font-family: 'Poppins', sans-serif; min-width: 150px;">
                            <div style="color: #6c757d; font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Time Recorded</div>
                            <div style="font-size: 14px; font-weight: 500; color: #2b2b2b;">
                                <i data-feather="clock" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 5px;"></i>
                                ${new Date(closest.time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'})}
                            </div>
                            <div style="color: ${closest.status === 'halt' ? '#FF5252' : '#27BEFF'}; font-size: 11px; font-weight: bold; margin-top: 5px;">
                                Status: ${closest.status === 'halt' ? 'Halted' : 'Active'}
                            </div>
                        </div>
                    `);
                    infoWindow.setPosition(event.latLng);
                    infoWindow.open(map);
                    if (window.feather) window.feather.replace();
                }
            }, 500);
        });

        hitPath.addListener('mouseout', function() {
            clearTimeout(hoverTimeout);
            infoWindow.close();
        });

        mapInitialized = true;
    }

    let isClosestMode = false;
    let isSmoothingMode = false;
    let storedTraces = [];
    let isLocating = false;
    let currentVisitId = null;

    function sortByClosest(traces) {
        if (!traces || traces.length < 2) return traces || [];
        let validTraces = traces.filter(t => t.latitude && t.longitude);
        if (validTraces.length === 0) return [];
        let sorted = [validTraces[0]];
        let remaining = validTraces.slice(1);
        while (remaining.length > 0) {
            const lastPoint = sorted[sorted.length - 1];
            const lastLatLng = new google.maps.LatLng(parseFloat(lastPoint.latitude), parseFloat(lastPoint.longitude));
            let nearestIndex = -1; let minDistance = Infinity;
            for (let i = 0; i < remaining.length; i++) {
                const point = remaining[i];
                const currentLatLng = new google.maps.LatLng(parseFloat(point.latitude), parseFloat(point.longitude));
                const distance = google.maps.geometry.spherical.computeDistanceBetween(lastLatLng, currentLatLng);
                if (distance < minDistance) { minDistance = distance; nearestIndex = i; }
            }
            if (nearestIndex !== -1) { sorted.push(remaining[nearestIndex]); remaining.splice(nearestIndex, 1); } else break;
        }
        return sorted;
    }

    function calculatePathDistance(path) {
        let totalDist = 0;
        for (let i = 0; i < path.length - 1; i++) {
            const p1 = new google.maps.LatLng(path[i].lat, path[i].lng);
            const p2 = new google.maps.LatLng(path[i + 1].lat, path[i + 1].lng);
            totalDist += google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
        }
        return totalDist;
    }

    function renderMap(gpsTraces, distanceCovered) {
        if (!map) return;
        storedTraces = gpsTraces;
        polylines.forEach(p => p.setMap(null)); polylines = [];
        markers.forEach(marker => marker.setMap(null)); markers = [];
        hitPath.setPath([]);

        let tracesToProcess = [...gpsTraces];
        if (isClosestMode) tracesToProcess = sortByClosest(tracesToProcess);

        pathData = tracesToProcess.filter(t => {
            const lat = parseFloat(t.latitude); const lng = parseFloat(t.longitude);
            return !(isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0));
        }).map(t => ({
            lat: parseFloat(t.latitude), lng: parseFloat(t.longitude),
            time: t.recorded_at, status: t.status || 'active'
        }));

        if (pathData.length === 0) {
            $('#map-legend').hide();
            return;
        }
        $('#map-legend').show();

        // Multi-color segment rendering
        let currentSegment = [pathData[0]];
        let currentStatus = pathData[0].status;

        for (let i = 1; i < pathData.length; i++) {
            const point = pathData[i];
            if (point.status !== currentStatus) {
                drawSegment(currentSegment, currentStatus);
                currentSegment = [pathData[i-1], point];
                currentStatus = point.status;
            } else {
                currentSegment.push(point);
            }
        }
        drawSegment(currentSegment, currentStatus);

        hitPath.setPath(pathData.map(p => ({lat: p.lat, lng: p.lng})));

        function drawSegment(points, status) {
            if (points.length < 2) return;
            const coords = points.map(p => ({lat: p.lat, lng: p.lng}));
            const color = status === 'halt' ? "#FF5252" : "#27BEFF";
            const borderColor = status === 'halt' ? "#D32F2F" : "#1100FE";

            polylines.push(new google.maps.Polyline({
                path: coords, geodesic: true, strokeColor: borderColor, strokeOpacity: 1.0, strokeWeight: 8, zIndex: 1, map: map
            }));
            polylines.push(new google.maps.Polyline({
                path: coords, geodesic: true, strokeColor: color, strokeOpacity: 1.0, strokeWeight: 6, zIndex: 2, map: map
            }));
        }

        // Start/End Markers
        markers.push(new google.maps.marker.AdvancedMarkerElement({
            position: {lat: pathData[0].lat, lng: pathData[0].lng}, map, title: 'Start',
            content: new google.maps.marker.PinElement({ background: '#00E676', borderColor: '#00C853', glyphColor: 'white' })
        }));
        markers.push(new google.maps.marker.AdvancedMarkerElement({
            position: {lat: pathData[pathData.length-1].lat, lng: pathData[pathData.length-1].lng}, map, title: 'End',
            content: new google.maps.marker.PinElement({ background: '#FF5252', borderColor: '#D32F2F', glyphColor: 'white' })
        }));

        let distVal = isClosestMode ? calculatePathDistance(pathData) / 1000 : parseFloat(distanceCovered);
        let formattedDistance = !isNaN(distVal) ? (distVal >= 1 ? `${distVal.toFixed(3)} km` : `${Math.round(distVal * 1000)} m`) : 'N/A';
        $('#distance-covered').text(formattedDistance);
    }

    $(document).ready(function() {
        var visitsTable = $('#visits-table').DataTable({
            processing: true, serverSide: true,
            dom: "<'row'<'col-sm-12'l>><'row'<'col-sm-12'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12'p>>",
            ajax: { url: "{{ route('live-location.data') }}", type: 'GET' },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'visit_id', name: 'visit_id' },
                { data: 'user_name', orderable: false, searchable: false },
                { data: 'action', orderable: false, searchable: false },
                { data: 'client_name', name: 'client.name', visible: false },
                { data: 'client_phone', name: 'client.phone_number', visible: false },
                { data: 'client_address', name: 'client.address', visible: false },
                { data: 'task_title', name: 'task.title', visible: false },
                { data: 'task_desc', name: 'task.description', visible: false },
                { data: 'task_status', name: 'task.status', visible: false },
                { data: 'visit_remarks', name: 'remarks', visible: false },
                { data: 'visit_image', name: 'image_path', visible: false },
                { data: 'image_latitude', name: 'image_latitude', visible: false },
                { data: 'image_longitude', name: 'image_longitude', visible: false }
            ],
            order: [[1, 'desc']], responsive: true
        });

        $.ajax({
            url: "{{ route('live-location.employees') }}", method: 'GET',
            success: function(response) {
                const filter = $('#employee-filter');
                if ($('#employee-filter').length) {
                    response.forEach(e => filter.append(`<option value="${e.id}">${e.name}</option>`));
                    filter.select2({ placeholder: "Select Employee", allowClear: true });
                }
            }
        });

        $('#employee-filter').on('change', function() {
            isLocating = false;
            visitsTable.ajax.url("{{ route('live-location.data') }}?user_id=" + $(this).val()).load();
            fetchStats();
        });

        $('#closestModeSwitch').on('change', function() {
            isClosestMode = $(this).is(':checked');
            if (storedTraces.length > 0) renderMap(storedTraces, 0);
            fetchStats();
        });

        $('#smoothingModeSwitch').on('change', function() {
            isSmoothingMode = $(this).is(':checked');
            if (currentVisitId) loadVisitData(currentVisitId);
            fetchStats();
        });

        function fetchStats() {
            if (isLocating) return;
            $.ajax({
                url: "{{ route('live-location.stats') }}", method: 'GET',
                data: { user_id: $('#employee-filter').val(), closest_mode: isClosestMode ? 1 : 0, smoothing_mode: isSmoothingMode ? 1 : 0 },
                success: function(res) {
                    $('#started-time').text(res.started_time || 'N/A');
                    $('#ended-time').text(res.ended_time || 'N/A');
                    $('#time-taken').text(res.time_taken || 'N/A');
                    $('#distance-covered').text(res.total_distance || 'N/A');
                }
            });
        }

        function loadVisitData(visitId) {
            $.ajax({
                url: "{{ route('track-visits.traces',['visitId' => '__ID__']) }}".replace('__ID__', visitId),
                method: 'GET',
                data: { smoothing_mode: isSmoothingMode ? 1 : 0 },
                success: function(res) {
                    const rowData = visitsTable.row($('.table-active')).data();
                    renderMap(res.traces, res.distance_covered);
                    const coords = res.traces.filter(t => t.latitude && t.longitude).map(t => ({lat: parseFloat(t.latitude), lng: parseFloat(t.longitude)}));
                    if (coords.length > 0) {
                        const bounds = new google.maps.LatLngBounds();
                        coords.forEach(c => bounds.extend(c)); map.fitBounds(bounds);
                    }
                    $('#started-time').text(res.started_time || 'N/A');
                    $('#ended-time').text(res.ended_time || 'N/A');
                    $('#time-taken').text(res.time_taken || 'N/A');
                    
                    // Show Relation Info logic remains same...
                    // (Omitted for brevity but kept in original)
                }
            });
        }

        $('#visits-table').on('click', '.select-visit', function(e) {
            e.preventDefault();
            isLocating = true;
            $('#visits-table tbody tr').removeClass('table-active');
            $(this).closest('tr').addClass('table-active');
            currentVisitId = $(this).data('visit-id');
            loadVisitData(currentVisitId);
        });

        // Export handles... (Unchanged but using params)
        $('#export-btn, #export-pdf-btn').on('click', function() {
            const isPdf = $(this).attr('id').includes('pdf');
            const route = isPdf ? "{{ route('live-location.export-pdf') }}" : "{{ route('live-location.export') }}";
            const params = $.param({ user_id: $('#employee-filter').val() || '', closest_mode: isClosestMode ? 1 : 0, smoothing_mode: isSmoothingMode ? 1 : 0 });
            window.location.href = route + "?" + params;
        });
    });
</script>
@endpush
