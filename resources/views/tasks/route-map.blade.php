@extends('layouts.admin')

@section('title', 'Task Route Map')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endpush

@section('breadcrumb')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Route Map</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                        <li class="breadcrumb-item active">Route Map</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="">
        <h1 class="my-4">Route Map for Task: {{ $task->title }}</h1>

        <div class="card mb-4">
            <div class="card-header">
                User GPS Timeline
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="map-date-start" class="form-label">Start Date</label>
                        <input type="text" id="map-date-start" class="form-control datepicker" placeholder="YYYY-MM-DD" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-6">
                        <label for="map-date-end" class="form-label">End Date</label>
                        <input type="text" id="map-date-end" class="form-control datepicker" placeholder="YYYY-MM-DD" value="{{ $endDate }}">
                    </div>
                </div>
                <div id="map" style="height: 500px; width: 100%;"></div>
                <p class="text-muted mt-2">Last updated: <span id="last-updated-text">just now</span></p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap"
    async defer></script>

<script>
    let map;
    let userPath;
    let borderPath;
    let markers = [];
    let lastUpdatedTime = Date.now();
    let updateInterval;

    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        $('#map-date-start, #map-date-end').on('change', function() {
            fetchAndRenderGpsData();
            resetUpdateTimer();
        });

        // Start initial update timer
        resetUpdateTimer();
    });

    function initMap() {
        const defaultLocation = { lat: 20.5937, lng: 78.9629 }; // Center of India as a default
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 5,
            center: defaultLocation,
        });

        userPath = new google.maps.Polyline({
            geodesic: true,
            strokeColor: "#27BEFF", // main line color
            strokeOpacity: 1.0,
            strokeWeight: 6,
            zIndex: 2
        });

        borderPath = new google.maps.Polyline({
            geodesic: true,
            strokeColor: "#1100FE",  // border color
            strokeOpacity: 1.0,
            strokeWeight: 8,         // bigger so it looks like a border
            zIndex: 1
        });

        userPath.setMap(map);
        borderPath.setMap(map);

        fetchAndRenderGpsData();
        setInterval(updateLastUpdatedText, 1000);
    }

    function fetchAndRenderGpsData() {
        lastUpdatedTime = Date.now(); // Update last updated time at the start of fetch
        const taskId = {{ $task->id }};
        const startDate = $('#map-date-start').val();
        const endDate = $('#map-date-end').val();

        $.ajax({
            url: `/tasks/${taskId}/route-map`, // Use the new route
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate,
                fetch_data_only: true // Indicate that only data is needed, not the view
            },
            success: function(response) {
                renderMap(response.gpsTraces, response.followups);
                lastUpdatedTime = Date.now(); // Update last updated time on success
            },
            error: function(error) {
                console.error('Error fetching GPS data:', error);
            }
        });
    }

    function updateLastUpdatedText() {
        const secondsAgo = Math.floor((Date.now() - lastUpdatedTime) / 1000);
        $('#last-updated-text').text(`${secondsAgo} seconds ago`);
    }

    function resetUpdateTimer() {
        clearInterval(updateInterval);
        updateInterval = setInterval(fetchAndRenderGpsData, 10000); // Refresh every 10 seconds
    }

    </script>

@endpush
