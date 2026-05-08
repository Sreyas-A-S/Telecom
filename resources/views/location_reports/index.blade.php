@extends('layouts.admin')

@section('title', 'Location Reports')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Location Reports</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Location Reports</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Report List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="location-reports-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Visit ID</th>
                            <th>Remarks</th>
                            <th>Reported At</th>
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

{{-- Visit Details Modal --}}
<div class="modal fade" id="visitDetailsModal" tabindex="-1" role="dialog" aria-labelledby="visitDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="visitDetailsModalLabel">Visit Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><strong>User:</strong> <span id="modal-user"></span></div>
                    <div class="col-md-6 mb-2"><strong>Date:</strong> <span id="modal-date"></span></div>
                    <div class="col-md-6 mb-2"><strong>Start Time:</strong> <span id="modal-start"></span></div>
                    <div class="col-md-6 mb-2"><strong>End Time:</strong> <span id="modal-end"></span></div>
                    <div class="col-md-6 mb-2"><strong>Duration:</strong> <span id="modal-duration"></span></div>
                    <div class="col-md-6 mb-2"><strong>Total Distance:</strong> <span id="modal-distance"></span></div>
                    <div class="col-md-6 mb-2"><strong>Traces Count:</strong> <span id="modal-traces"></span></div>
                    <div class="col-md-12 mb-2"><strong>Reported Location:</strong> <span id="modal-coordinates"></span></div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Visit Route:</h6>
                        <div id="visit-map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=geometry,marker"></script>
<script>
    $(document).ready(function() {
        let map;
        let flightPath;
        let markers = [];

        function initMap() {
            map = new google.maps.Map(document.getElementById("visit-map"), {
                zoom: 5,
                center: {
                    lat: 20.5937,
                    lng: 78.9629
                }, // Center of India
                mapId: "DEMO_MAP_ID",
            });
        }

        // Initialize map when modal is shown to handle resizing issues
        $('#visitDetailsModal').on('shown.bs.modal', function() {
            if (!map) {
                initMap();
            } else {
                // Trigger resize event to ensure map renders correctly
                google.maps.event.trigger(map, "resize");
            }
        });

        $('#location-reports-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('location-reports.index') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'visit_id',
                    name: 'visit_id'
                },

                {
                    data: 'remarks',
                    name: 'remarks'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [4, 'desc']
            ]
        });

        // Event delegation for the view button
        $(document).on('click', '.view-visit-btn', function() {
            var visitId = $(this).data('visit-id');
            var reportLat = parseFloat($(this).data('lat'));
            var reportLng = parseFloat($(this).data('lng'));
            var url = "{{ route('location-reports.visit-details', ':id') }}".replace(':id', visitId);

            // Show loading or clear previous data
            $('#modal-user').text('Loading...');
            $('#modal-coordinates').text(`${reportLat}, ${reportLng}`);

            // Clear map data
            if (flightPath) {
                flightPath.setMap(null);
            }
            if (markers.length > 0) {
                for (let i = 0; i < markers.length; i++) {
                    markers[i].map = null;
                }
                markers = [];
            }

            $('#visitDetailsModal').modal('show');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#modal-user').text(response.user);
                    $('#modal-date').text(response.date);
                    $('#modal-start').text(response.start_time);
                    $('#modal-end').text(response.end_time);
                    $('#modal-duration').text(response.duration);
                    $('#modal-distance').text(response.total_distance);
                    $('#modal-traces').text(response.trace_count);

                    // Render Markers for all points
                    if (response.traces && response.traces.length > 0 && map) {
                        const bounds = new google.maps.LatLngBounds();

                        // Add Report Marker if valid
                        if (!isNaN(reportLat) && !isNaN(reportLng)) {
                            const reportPos = { lat: reportLat, lng: reportLng };
                            const reportMarker = new google.maps.marker.AdvancedMarkerElement({
                                position: reportPos,
                                map: map,
                                title: "Reported Location",
                                content: new google.maps.marker.PinElement({
                                    background: '#00E676',
                                    borderColor: '#00C853',
                                    glyphColor: 'white'
                                }),
                                zIndex: 1000
                            });
                            
                            const infoWindow = new google.maps.InfoWindow({
                                content: `<strong>Reported Location</strong><br>Lat: ${reportLat}<br>Lng: ${reportLng}`
                            });
                            
                            reportMarker.addListener('click', () => {
                                infoWindow.open(map, reportMarker);
                            });
                            
                            markers.push(reportMarker);
                            bounds.extend(reportPos);
                        }

                        response.traces.forEach(trace => {
                            const position = {
                                lat: parseFloat(trace.lat),
                                lng: parseFloat(trace.lng)
                            };

                            const marker = new google.maps.marker.AdvancedMarkerElement({
                                position: position,
                                map: map,
                                title: "Trace Point",
                                content: new google.maps.marker.PinElement({
                                    background: '#4285F4',
                                    borderColor: '#FFFFFF',
                                    glyphColor: '#FFFFFF',
                                    scale: 0.7
                                })
                            });
                            markers.push(marker);
                            bounds.extend(position);
                        });

                        map.fitBounds(bounds);
                    }
                },
                error: function() {
                    $('#modal-user').text('Error loading data');
                    $('#visitDetailsModal').modal('hide');
                    alert('Failed to load visit details.');
                }
            });
        });
    });
</script>
@endpush