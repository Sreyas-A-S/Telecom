@extends('layouts.admin')

@section('title', 'Service Follow-ups')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <div class="">
        <h1 class="my-4">Follow-ups for Service Request</h1>

        <div class="card mb-4">
            <div class="card-header">
                Service Details
            </div>
            <div class="card-body">
                <p><strong>Client:</strong> {{ $service->client->name }}</p>
                <p><strong>Product:</strong> {{ $service->product->name }}</p>
                <p><strong>Product Model:</strong> {{ $service->productModel->name ??'N/A' }}</p>
                <p><strong>Model Series:</strong> {{ $service->modelSeries->name ??'N/A' }}</p>
                <p><strong>Complaint:</strong> {{ $service->name ?? 'N/A' }}</p>
                <p><strong>Description:</strong> {{ $service->description }}</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                User GPS Timeline
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="engineer-select" class="form-label">Select Engineer</label>
                        <select id="engineer-select" class="form-control">
                            <option value="all">All Engineers</option>
                            @foreach($service->tasks->unique('assigned_to') as $task)
                                @if($task->assignedEmployee)
                                    <option value="{{ $task->assignedEmployee->id }}">{{ $task->assignedEmployee->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="map-date-start" class="form-label">Start Date</label>
                        <input type="text" id="map-date-start" class="form-control datepicker" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-4">
                        <label for="map-date-end" class="form-label">End Date</label>
                        <input type="text" id="map-date-end" class="form-control datepicker" placeholder="YYYY-MM-DD">
                    </div>
                </div>
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>



        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                Follow-up List
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="followups-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Task</th>
                                <th>Notes</th>
                                <th>By</th>
                                <th>Date</th>
                                <th>Images</th>
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

    <!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel">Follow-up Images</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <!-- Carousel items will be injected here -->
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
    let map;
    let userPath;
    let markers = [];

    let loaderCounter = 0;
    function showLoader() {
        loaderCounter++;
        $(".loader-wrapper").show();
    }

    function hideLoader() {
        loaderCounter--;
        if (loaderCounter <= 0) {
            loaderCounter = 0;
            $(".loader-wrapper").fadeOut('slow');
        }
    }

    function renderMap(gpsTraces, followups) {
        // Clear existing path and markers
        userPath.setPath([]);
        markers.forEach(marker => marker.map = null);
        markers = [];

        const pathCoordinates = gpsTraces.map(trace => ({
            lat: parseFloat(trace.latitude),
            lng: parseFloat(trace.longitude),
        }));
        userPath.setPath(pathCoordinates);

        if (pathCoordinates.length > 0) {
            map.setCenter(pathCoordinates[0]);
            map.setZoom(12); // Zoom in on the first point
        }

        followups.forEach(followup => {
            if (followup.latitude && followup.longitude) {
                const marker = new google.maps.marker.AdvancedMarkerElement({
                    position: { lat: parseFloat(followup.latitude), lng: parseFloat(followup.longitude) },
                    map,
                    title: `Follow-up: ${followup.notes}`,
                    content: new google.maps.marker.PinElement({
                        background: '#4285F4',
                        borderColor: '#FFFFFF',
                        glyphColor: '#FFFFFF',
                    })
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div>
                            <h6>Follow-up Notes:</h6>
                            <p>${followup.notes}</p>
                                                            <h6>Submitted By:</h6>
                                                            <p>${followup.user_name}</p>
                                                            <h6>Time:</h6>                            <p>${new Date(followup.created_at).toLocaleString()}</p>
                            ${followup.images && followup.images.length > 0 ?
                                `<h6>Images:</h6>` +
                                followup.images.map(img => `<img src="/${img}" style="width:50px; height:50px; object-fit:cover; margin-right:5px;">`).join('')
                                : ''
                            }
                        </div>
                    `,
                });

                marker.addListener("click", () => {
                    infoWindow.open(map, marker);
                });
                markers.push(marker);
            }
        });
    }

    function fetchAndRenderGpsData() {
        const serviceId = {{ $service->id }};
        const startDate = $('#map-date-start').val();
        const endDate = $('#map-date-end').val();
        const selectedEngineerId = $('#engineer-select').val();

        let userIdsToFetch = [];
        if (selectedEngineerId === 'all') {
            userIdsToFetch = @json($service->tasks->pluck('assigned_to')->filter()->unique()->values());
        } else {
            userIdsToFetch = [selectedEngineerId];
        }

        if (userIdsToFetch.length > 0) {
            showLoader();
            $.ajax({
                url: `{{ route('services.gps.data') }}`,
                method: 'GET',
                data: {
                    user_ids: userIdsToFetch,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(gpsResponse) {
                    renderMap(gpsResponse.gpsTraces, gpsResponse.followups);
                },
                error: function(error) {
                    console.error('Error fetching GPS data:', error);
                },
                complete: function() {
                    hideLoader();
                }
            });
        }
    }

    function initMap() {
        const defaultLocation = { lat: 20.5937, lng: 78.9629 }; // Center of India as a default
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 5,
            center: defaultLocation,
            mapId: "DEMO_MAP_ID",
        });

        userPath = new google.maps.Polyline({
            geodesic: true,
            strokeColor: "#FF0000",
            strokeOpacity: 1.0,
            strokeWeight: 2,
        });
        userPath.setMap(map);

        fetchAndRenderGpsData();
    }

    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        // Date range filter inputs
        $('#map-date-start').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        $('#map-date-end').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        var followupsTable = $('#followups-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('services.followups.datatable', $service->id) }}',
                type: 'GET',
                data: function(d) {
                    d.start_date = $('#map-date-start').val();
                    d.end_date = $('#map-date-end').val();
                    d.engineer_id = $('#engineer-select').val();
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'task_title', name: 'task.title'},
                {data: 'notes', name: 'notes'},
                {data: 'user_name', name: 'user.name', orderable: false, searchable: false},
                {data: 'created_at', name: 'created_at'},
                {data: 'images_column', name: 'images_column', orderable: false, searchable: false},
            ],
            order: [[0, 'desc']],
            responsive: true
        });

        followupsTable.on('preXhr.dt', function() {
            showLoader();
        }).on('draw.dt', function() {
            hideLoader();
        });

        // Reload data when date range or engineer selection changes
        $('#map-date-start, #map-date-end').datepicker().on('changeDate', function() {
            fetchAndRenderGpsData();
            followupsTable.ajax.reload();
        });

        $('#engineer-select').on('change', function() {
            fetchAndRenderGpsData();
            followupsTable.ajax.reload();
        });

        $('#followups-table').on('click', '.view-images-btn', function() {
            var images = $(this).data('images');
            var carouselInner = $('#imageCarousel .carousel-inner');
            carouselInner.empty();
            if (images && images.length > 0) {
                images.forEach(function(image, index) {
                    var activeClass = index === 0 ? 'active' : '';
                    var carouselItem = `
                        <div class="carousel-item ${activeClass}">
                            <img src="/${image}" class="d-block w-100">
                        </div>`;
                    carouselInner.append(carouselItem);
                });
                var imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
                imageModal.show();
            }
        });
    });

    // Load Google Maps API
    const googleMapsScript = document.createElement('script');
    googleMapsScript.src = `https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&libraries=geometry,marker&loading=async`;
    googleMapsScript.async = true;
    document.head.appendChild(googleMapsScript);
</script>
@endpush
