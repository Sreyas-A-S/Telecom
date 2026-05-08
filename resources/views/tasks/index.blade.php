@extends('layouts.admin')

@section('title', 'Task Management')

@push('styles')
<style>
    .task-details-panel {
        border: 1px solid #e9edf3;
        border-radius: 12px;
        padding: 14px;
        background: #f8fafc;
    }

    .task-details-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }

    .task-details-description {
        color: #475569;
        margin-bottom: 0;
        white-space: pre-wrap;
    }

    .task-meta-label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .task-timer-pill {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #075985;
        font-weight: 600;
        min-width: 96px;
        text-align: center;
    }

    .task-detail-card {
        border: 1px solid #e9edf3;
        border-radius: 10px;
        padding: 10px 12px;
        background: #fff;
        height: 100%;
    }

    .task-detail-card .detail-label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .task-detail-card .detail-value {
        margin: 0;
        color: #0f172a;
        font-weight: 600;
        word-break: break-word;
    }

    .task-section-block {
        border-top: 1px dashed #d8e0ea;
        padding-top: 12px;
        margin-top: 12px;
    }

    .task-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
    }

    #task-map-display {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="">
    <!-- <h1>Task Management</h1> -->

    <div id="task-summary-cards">
        <div class="row mt-3 pt-3">
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Tasks</h5>
                        <h2 class="text-primary">{{ $totalTasks }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Pending</h5>
                        <h2 class="text-warning">{{ $pendingTasks }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Ongoing</h5>
                        <h2 class="text-info">{{ $ongoingTasks }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">On Hold</h5>
                        <h2 class="text-danger">{{ $holdTasks }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-3 mt-3 mb-3" id="clock-buttons-container">
        @if($clockedIn)
        <button class="btn btn-danger" id="clock-out-btn">Clock Out</button>
        <button class="btn btn-primary d-none" id="clock-in-btn">Clock In</button>
        @else
        <button class="btn btn-danger d-none" id="clock-out-btn">Clock Out</button>
        <button class="btn btn-primary" id="clock-in-btn">Clock In</button>
        @endif
    </div>
    <div class="card table-responsive">
        <div class="card-body">
            <ul class="nav nav-tabs d-flex mt-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list"
                        type="button" role="tab" aria-controls="list" aria-selected="true">Task List</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create"
                        type="button" role="tab" aria-controls="create" aria-selected="false">Add Task</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
                    <table class="table" id="tasks-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                @if($showEmployeeColumn)
                                <th>Employee</th>
                                @endif
                                <th>Title</th>
                                <th>Task Type</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>For Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Timer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data will be loaded via AJAX --}}
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                    <form id="createTaskForm" action="{{ route('tasks.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="type">Type</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">Select Type</option>
                                        <option value="client_based">Client Based</option>
                                        <option value="open">Open</option>
                                    </select>
                                    <div class="invalid-feedback" id="type_error"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="title">Task Title</label>
                                    <input type="text" name="title" id="title" class="form-control">
                                    <div class="invalid-feedback" id="title_error"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control"></textarea>
                                    <div class="invalid-feedback" id="description_error">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="assigned_to">Assign to Employee</label>
                                    <select name="assigned_to" id="assigned_to" class="form-control">
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="assigned_to_error">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="due_date">Due Date</label>
                                    <input type="text" name="due_date" id="due_date"
                                        class="form-control datepicker">
                                    <div class="invalid-feedback" id="due_date_error">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="amount_to_be_collected">Amount to be Collected</label>
                                    <input type="number" name="amount_to_be_collected" id="amount_to_be_collected" class="form-control" step="0.01">
                                    <div class="invalid-feedback" id="amount_to_be_collected_error">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="location">Location</label>
                                    <input type="text" name="location" id="location" class="form-control">
                                    <div class="invalid-feedback" id="location_error">
                                    </div>
                                    <input type="hidden" name="latitude" id="latitude">
                                    <input type="hidden" name="longitude" id="longitude">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="map">Map</label>
                                    <div id="map" style="height: 400px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-labelledby="deleteTaskModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaskModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this task?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- View Task Modal -->
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTaskModalLabel">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="tab-content" id="taskDetailsTabContent">
                    <div class="tab-pane fade show active" id="task-info" role="tabpanel"
                        aria-labelledby="task-info-tab">
                        <div class="task-details-panel">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                <div class="pe-3">
                                    <h4 id="task-title" class="task-details-title mb-1"></h4>
                                    <p id="task-description" class="task-details-description"></p>
                                </div>
                                <div class="text-md-end">
                                    <div class="task-meta-label">Status</div>
                                    <span id="task-status" class="badge rounded-pill bg-light text-dark">N/A</span>
                                    <div class="task-meta-label mt-2">Timer</div>
                                    <div id="task-timer-modal" class="task-timer-pill">N/A</div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Due Date</div>
                                        <p id="task-due-date" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Location</div>
                                        <p id="task-location" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Amount to be Collected</div>
                                        <p id="task-amount-to-be-collected" class="detail-value">0.00</p>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div id="client-details" class="task-section-block" style="display: none;">
                            <h5 class="task-section-title">Client Details</h5>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Client Name</div>
                                        <p id="client-name" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Client Email</div>
                                        <p id="client-email" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Client Phone</div>
                                        <p id="client-phone" class="detail-value">N/A</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="service-product-details" class="task-section-block" style="display: none;">
                            <h5 class="task-section-title">Service/Product Details</h5>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Service Name</div>
                                        <p id="service-name" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Service Description</div>
                                        <p id="service-description" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Product Name</div>
                                        <p id="product-name" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Product Model</div>
                                        <p id="product-model" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Model Series</div>
                                        <p id="model-series" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Price</div>
                                        <p id="service-price" class="detail-value">N/A</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="fsr-details-section" class="task-section-block" style="display: none;">
                            <h5 class="task-section-title">Field Service Report (FSR) Details</h5>
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <div class="task-detail-card">
                                        <div class="detail-label">On-Site Assessment</div>
                                        <p id="fsr-assessment" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Analysis of Cause</div>
                                        <p id="fsr-cause" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Actions Taken</div>
                                        <p id="fsr-actions" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="task-detail-card">
                                        <div class="detail-label">FSR Status</div>
                                        <p id="fsr-status" class="detail-value">N/A</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="task-detail-card">
                                        <div class="detail-label">Payment Status</div>
                                        <p id="fsr-payment-status" class="detail-value">N/A</p>
                                    </div>
                                </div>
                            </div>
                            <div id="fsr-images-container" class="mt-3" style="display: none;">
                                <div class="detail-label mb-2">FSR Images</div>
                                <div id="fsr-images-list" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                        <div id="task-map-display" style="height: 300px; width: 100%; display: none;" class="mt-3">
                        </div>
                        <div id="route-map-button-container" class="mt-3" style="display: none;">
                            <a href="#" id="view-route-map-btn" class="btn btn-info" target="_blank">View Route Map</a>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="followups" role="tabpanel" aria-labelledby="followups-tab">
                        <div class="mt-3">
                            <h5>Follow-up History</h5>
                            <div id="followups-list">
                                <!-- Follow-ups will be loaded here -->
                            </div>
                            <hr>
                            <h5>Add New Follow-up</h5>
                            <form id="addFollowupFormModal">
                                @csrf
                                <input type="hidden" name="task_id" id="followup-task-id-modal">
                                <div class="form-group mb-3">
                                    <label for="followup-notes-modal">Notes</label>
                                    <textarea name="notes" id="followup-notes-modal" class="form-control" rows="3"></textarea>
                                    <div class="invalid-feedback" id="followup-notes-modal_error"></div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Save
                                    Follow-up</button>
                            </form>
                        </div>
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
<script>
    // Check if jQuery is loaded, if not, load it
    if (typeof jQuery == 'undefined') {
        document.write('<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"><\/script>');
    }
</script>
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,marker&callback=initMap"
    async defer></script>

<script>
    window.currentEmployeeId = @json(Auth::user()->employee->id ?? null);
    window.taskTimers = {};

    function formatTaskStatus(status) {
        if (!status) return 'N/A';
        return status.replace(/_/g, ' ').replace(/\b\w/g, function(ch) {
            return ch.toUpperCase();
        });
    }

    function setTaskStatusBadge(status) {
        const statusBadge = $('#task-status');
        const statusClassMap = {
            pending: 'bg-warning text-dark',
            in_progress: 'bg-info text-dark',
            hold: 'bg-secondary text-white',
            stopped: 'bg-danger text-white',
            completed: 'bg-success text-white',
        };

        statusBadge.removeClass('bg-warning bg-info bg-secondary bg-danger bg-success bg-light text-dark text-white');
        statusBadge.addClass(statusClassMap[status] || 'bg-light text-dark');
        statusBadge.text(formatTaskStatus(status));
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

    function clearValidationErrors() {
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function displayValidationErrors(errors) {
        $.each(errors, function(key, value) {
            $('#' + key).addClass('is-invalid');
            $('#' + key + '_error').text(value[0]);
        });
    }

    let map;
    let marker;
    let geocoder;
    let autocomplete;

    let taskMap;
    let taskMarker;

    function initTaskMap(latitude, longitude) {
        const latLng = {
            lat: parseFloat(latitude),
            lng: parseFloat(longitude)
        };
        taskMap = new google.maps.Map(document.getElementById('task-map-display'), {
            zoom: 15,
            center: latLng,
            mapId: 'DEMO_MAP_ID'
        });
        taskMarker = new google.maps.marker.AdvancedMarkerElement({
            position: latLng,
            map: taskMap,
        });
    }

    function initMap() {
        const defaultLatLng = {
            lat: 20.5937,
            lng: 78.9629
        }; // Default to India
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 5,
            center: defaultLatLng,
            mapId: 'DEMO_MAP_ID'
        });
        geocoder = new google.maps.Geocoder();

        marker = new google.maps.marker.AdvancedMarkerElement({
            map: map,
            position: defaultLatLng,
            gmpDraggable: true,
        });

        const locationInput = document.getElementById('location');
        autocomplete = new google.maps.places.Autocomplete(locationInput);
        autocomplete.bindTo('bounds', map);

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry) {
                showToast('No details available for input: ' + place.name, 'warning');
                return;
            }

            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            marker.position = place.geometry.location;
            $('#location').val(place.formatted_address);
            $('#latitude').val(place.geometry.location.lat());
            $('#longitude').val(place.geometry.location.lng());
        });

        marker.addListener('gmp-dragend', function() {
            const latlng = marker.position;
            geocoder.geocode({
                'location': latlng
            }, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        $('#location').val(results[0].formatted_address);
                        $('#latitude').val(latlng.lat());
                        $('#longitude').val(latlng.lng());
                    }
                }
            });
        });

        map.addListener('click', function(event) {
            marker.position = event.latLng;
            geocoder.geocode({
                'location': event.latLng
            }, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        $('#location').val(results[0].formatted_address);
                        $('#latitude').val(event.latLng.lat());
                        $('#longitude').val(event.latLng.lng());
                    }
                }
            });
        });
    }

    $(document).ready(function() {
        // Initialize datepicker for due_date field
        $('#due_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
        var showEmployeeColumn = {{{ json_encode($showEmployeeColumn) }}};

        var fsrEditUrlTemplate = {!! json_encode(route('fsr.edit', ['fsrReport' => ':id'])) !!};
        var fsrCreateUrlTemplate = {!! json_encode(route('tasks.fsr.create', ['task' => ':id'])) !!};
        var followupsUrlTemplate = {!! json_encode(route('tasks.followups.index', ['task' => ':id'])) !!};
        var columns = [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'title',
                name: 'title'
            },
            {
                data: 'type',
                name: 'type',
                render: function(data, type, row) {
                    if (data === 'client_based') {
                        return '<span class="badge bg-info">Client Based</span>';
                    } else if (data === 'open') {
                        return '<span class="badge bg-secondary">Open</span>';
                    }
                    return data;
                }
            },
            {
                data: 'is_service',
                name: 'is_service'
            },
            {
                data: 'amount_to_be_collected',
                name: 'amount_to_be_collected',
                render: function(data) {
                    return data ? parseFloat(data).toFixed(2) : '0.00';
                }
            },
            {
                data: 'due_date',
                name: 'due_date'
            },
            {
                data: 'start_date_time',
                name: 'start_date_time'
            },
            {
                data: 'end_date_time',
                name: 'end_date_time'
            },
            {
                data: null,
                name: 'timer',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    // Render timer for in_progress, hold, stopped, and completed tasks
                    if (row.status === 'in_progress' || row.status === 'hold' || row.status ===
                        'stopped' || row.status === 'completed') {
                        const timerStartedAt = row.timer_started_at ? row.timer_started_at : '';
                        const totalElapsedTime = row.total_elapsed_time ? row.total_elapsed_time : 0;
                        return `<div class="badge bg-light text-dark p-2">
                                                                    <i class="fa fa-clock-o me-1"></i>
                                                                    <span class="task-timer" id="task-timer-${row.id}" data-task-id="${row.id}" data-timer-started-at="${timerStartedAt}" data-total-elapsed-time="${totalElapsedTime}" data-status="${row.status}">
                                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                                            <span class="visually-hidden">Loading...</span>
                                                                        </div>
                                                                    </span>
                                                                </div>`;
                    }
                    return '';
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {

                    var btn = '<ul class="action d-flex justify-content-around list-unstyled gap-4">';

                    // define disabled variables early so they're available to all branches
                    var disabledClass = !window.isClockedIn ? 'disabled' : '';
                    var disabledStyle = !window.isClockedIn ? 'pointer-events: none; opacity: 0.5;' : '';

                    // FSR Button
                    // Relation may come back as camelCase or snake_case depending on serializer
                    var fsr = row.fsrReport || row.fsr_report || null;

                    if (fsr && fsr.id) {
                        btn += '<li><a href="' + fsrEditUrlTemplate.replace(':id', fsr.id) +
                            '" class="btn btn-success btn-sm view-fsr-task ' + disabledClass +
                            '" title="View FSR" style="' + disabledStyle + '">FSR</a></li>';
                    } else {
                        btn += '<li><a href="' + fsrCreateUrlTemplate.replace(':id', row.id) +
                            '" class="btn btn-info btn-sm add-fsr-task ' +
                            disabledClass + '" title="Add FSR" style="' + disabledStyle +
                            '">FSR</a></li>';
                    }
                    btn += '<li><a href="javascript:void(0)" class="view-task" data-id="' + row.id +
                        '" title="View"><i class="fa fa-eye text-success"></i></a></li>';

                    // Add Follow-up button conditions
                    var addFollowupDisabledClass = '';
                    var addFollowupDisabledStyle = '';

                    if (!window.isClockedIn || row.status !== 'in_progress') {
                        addFollowupDisabledClass = 'disabled';
                        addFollowupDisabledStyle = 'pointer-events: none; opacity: 0.5;';
                    }

                    btn += '<li><a href="' + followupsUrlTemplate.replace(':id', row.id) +
                        '" class="add-followup-task ' + addFollowupDisabledClass + '" title="Add Follow-up" style="' + addFollowupDisabledStyle + '"><i class="fa fa-plus text-primary"></i></a></li>';

                    // Delete button for service managers
                    if (window.currentUserRole === 'service_manager' || window.currentUserRole === 'Service Manager') {
                        var disabledClass = !window.isClockedIn ? 'disabled' : '';
                        var disabledStyle = !window.isClockedIn ?
                            'pointer-events: none; opacity: 0.5;' : '';
                        btn += '<li><a href="javascript:void(0)" class="delete-task ' + disabledClass +
                            '" data-id="' + row.id +
                            '" data-bs-toggle="modal" data-bs-target="#deleteTaskModal" title="Delete" style="' +
                            disabledStyle + '"><i class="icon-trash text-danger"></i></a></li>';
                    }


                    // Start/Pause/Resume/Stop buttons for assigned engineer
                    if (row.assigned_to == window.currentEmployeeId) {

                        var disabledClass = !window.isClockedIn ? 'disabled' : '';
                        var disabledStyle = !window.isClockedIn ?
                            'pointer-events: none; opacity: 0.5;' : '';

                        // Check for Service Manager approval for early action
                        var isTaskNotCompleted = (row.status !== 'completed');
                        var taskStartDate = row.start_date_time ? new Date(row.start_date_time) : null;
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);

                        var isStartDateAfterToday = (taskStartDate && taskStartDate.setHours(0, 0, 0,
                            0) > today.setHours(0, 0, 0, 0));
                        var smApprovedToday = (row.sm_approved_early_action_date && new Date(row
                                .sm_approved_early_action_date).toDateString() === today
                            .toDateString());

                        var requiresApproval = isTaskNotCompleted && isStartDateAfterToday && !
                            smApprovedToday;

                        if (requiresApproval) {
                            // Disable action buttons if approval is required
                            disabledClass = 'disabled';
                            disabledStyle = 'pointer-events: none; opacity: 0.5;';
                        }


                        if (row.status == 'pending' || row.status == 'Pending') {
                            btn += '<li><a href="javascript:void(0)" class="start-task ' +
                                disabledClass + '" data-id="' + row.id +
                                '" data-action="start" title="Start" style="' +
                                disabledStyle + '"><i class="fa fa-play text-primary"></i></a></li>';
                        } else if (row.status == 'hold' || row.status == 'Hold') {
                            btn += '<li><a href="javascript:void(0)" class="resume-task ' +
                                disabledClass + '" data-id="' + row.id +
                                '" data-action="resume" title="Resume" style="' +
                                disabledStyle + '"><i class="fa fa-play text-success"></i></a></li>';
                        } else if (row.status == 'in_progress' || row.status == 'In_Progress') {
                            btn += '<li><a href="javascript:void(0)" class="pause-task ' +
                                disabledClass + '" data-id="' + row.id +
                                '" data-action="pause" title="Pause" style="' +
                                disabledStyle + '"><i class="fa fa-pause text-warning"></i></a></li>';
                            btn += '<li><a href="javascript:void(0)" class="stop-task ' +
                                disabledClass + '" data-id="' + row.id +
                                '" data-action="stop" title="Stop" style="' +
                                disabledStyle + '"><i class="fa fa-stop text-danger"></i></a></li>';
                        } else if (row.status == 'stopped' || row.status == 'Stopped' || row.status ==
                            'completed' || row.status == 'Completed') {
                            // Allow resuming both stopped and completed tasks
                            btn += '<li><a href="javascript:void(0)" class="resume-task ' +
                                disabledClass + '" data-id="' + row.id +
                                '" data-action="resume" title="Resume" style="' +
                                disabledStyle + '"><i class="fa fa-play text-info"></i></a></li>';
                        }
                    }



                    btn += '</ul>';
                    return btn;
                }
            }
        ];

        if (showEmployeeColumn) {
            columns.splice(1, 0, {
                data: 'employee_name',
                name: 'employee_name',
                render: function(data, type, row) {
                    if (row.assigned_to == window.currentEmployeeId) {
                        return '<span class="text-primary fw-bold">' + data +
                            '</span>'; // Highlight with primary color and bold
                    }
                    return data;
                }
            });
        }

        var tasksTable = $('#tasks-table').DataTable({
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
                url: "{{ route('tasks.index') }}",
                dataSrc: function(json) {
                    window.isClockedIn = json.clockedIn;
                    window.currentUserRole = json.userRole; // Capture user role
                    return json.data;
                }
            },
            columns: columns,
            drawCallback: function(settings) {
                const currentActiveTaskIds = new Set();

                // Identify currently active tasks in the drawn rows
                this.api().rows({
                    page: 'current'
                }).every(function() {
                    const rowData = this.data();
                    if (rowData.status === 'in_progress') {
                        currentActiveTaskIds.add(rowData.id);
                    }
                });

                // Clear timers for tasks that are no longer active or not in the current view
                for (const taskId in window.taskTimers) {
                    if (!currentActiveTaskIds.has(parseInt(taskId))) {
                        clearInterval(window.taskTimers[taskId]);
                        delete window.taskTimers[taskId];
                    }
                }

                // Initialize or update timers for all visible rows
                this.api().rows({
                    page: 'current'
                }).every(function() {
                    const rowData = this.data();
                    const timerElement = $('#task-timer-' + rowData.id);

                    if (timerElement.length) {
                        const status = rowData.status;
                        let totalElapsedTime = rowData.total_elapsed_time ? parseInt(rowData
                            .total_elapsed_time) : 0;

                        const formatTime = (seconds) => {
                            let h = Math.floor(seconds / 3600).toString().padStart(2,
                                '0');
                            let m = Math.floor((seconds % 3600) / 60).toString()
                                .padStart(2, '0');
                            let s = (seconds % 60).toString().padStart(2, '0');
                            return `${h}:${m}:${s}`;
                        };

                        // Clear existing timer for this task if any
                        if (window.taskTimers[rowData.id]) {
                            clearInterval(window.taskTimers[rowData.id]);
                            delete window.taskTimers[rowData.id];
                        }

                        if (status === 'in_progress') {
                            const timerStartedAt = new Date(rowData.timer_started_at);
                            let currentElapsed = totalElapsedTime + Math.floor((new Date()
                                .getTime() - timerStartedAt.getTime()) / 1000);

                            timerElement.html(formatTime(currentElapsed));

                            window.taskTimers[rowData.id] = setInterval(function() {
                                currentElapsed++;
                                timerElement.html(formatTime(currentElapsed));
                            }, 1000);
                        } else {
                            timerElement.html(formatTime(totalElapsedTime));
                        }
                    }
                });
            }
        });

        $('#assigned_to').select2({
            placeholder: "Select an Employee",
            allowClear: true
        });

        // Handle conditional validation for assigned_to
        $('#type').on('change', function() {
            if ($(this).val() === 'client_based') {
                $('#assigned_to').prop('required', true);
            } else {
                $('#assigned_to').prop('required', false);
            }
        });

        // Task Creation Form Submission
        $('#createTaskForm').on('submit', function(e) {
            e.preventDefault();
            clearValidationErrors();

            var formData = new FormData(this);

            // Client-side validation
            let isValid = true;

            // Type validation
            if (!formData.get('type')) {
                $('#type').addClass('is-invalid');
                $('#type_error').text('The type field is required.');
                isValid = false;
            }

            // Title validation
            if (!formData.get('title')) {
                $('#title').addClass('is-invalid');
                $('#title_error').text('The title field is required.');
                isValid = false;
            }

            // Due Date validation
            if (!formData.get('due_date')) {
                $('#due_date').addClass('is-invalid');
                $('#due_date_error').text('The due date field is required.');
                isValid = false;
            }

            // Assigned To validation (conditional)
            if (formData.get('type') === 'client_based' && !formData.get('assigned_to')) {
                $('#assigned_to').addClass('is-invalid');
                $('#assigned_to_error').text(
                    'The assigned to field is required for client-based tasks.');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            // AJAX Submission
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
                    showToast(response.message || 'Task created successfully.', 'success');
                    $('#createTaskForm')[0].reset(); // Reset the form
                    tasksTable.ajax.reload(); // Reload DataTables
                    new bootstrap.Tab(document.getElementById('list-tab')).show();
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else {
                        showToast(xhr.responseJSON.message || 'Error creating task.',
                            'danger');
                    }
                }
            });
        });

        let taskIdToDelete;

        $('#tasks-table').on('click', '.delete-task', function() {
            taskIdToDelete = $(this).data('id');
            $.ajax({
                url: '/tasks/' + taskIdToDelete,
                method: 'GET',
                success: function(data) {
                    let message = 'Are you sure you want to delete this task?';
                    if (data.type === 'client_based') {
                        message =
                            'This is a service task. Deleting it will also clear the assigned service engineers from the associated service. Are you sure you want to proceed?';
                    }
                    $('#deleteTaskModal .modal-body').text(message);
                    $('#deleteTaskModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching task details for deletion confirmation:',
                        error);
                    showToast('Error preparing for task deletion.', 'danger');
                }
            });
        });

        $('#confirmDeleteBtn').on('click', function() {
            let url = "{{ route('tasks.destroy', ['task' => ':id']) }}";
            url = url.replace(':id', taskIdToDelete);
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#deleteTaskModal').modal('hide');
                    tasksTable.ajax.reload();
                    showToast('Task deleted successfully.', 'success');
                },
                error: function(response) {
                    console.error("Error deleting task:", response);
                    $('#deleteTaskModal').modal('hide');
                    let errorMessage = 'Error deleting task.';
                    if (response.responseJSON && response.responseJSON.message) {
                        errorMessage = response.responseJSON.message;
                    } else if (response.responseText) {
                        errorMessage = response.responseText;
                    }
                    showToast(errorMessage, 'danger');
                }
            });
        });

        // Task Action Buttons (Start, Pause, Resume, Stop)
        $('#tasks-table').on('click', '.start-task, .pause-task, .stop-task, .resume-task', function() {
            var taskId = $(this).data('id');
            var action = $(this).hasClass('start-task') ? 'start' :
                ($(this).hasClass('pause-task') ? 'pause' :
                    ($(this).hasClass('stop-task') ? 'stop' : 'resume'));

            // Check if clocked in
            if (!window.isClockedIn) {
                showToast('You must be clocked in to perform this action.', 'warning');
                return;
            }

            let url = "{{ url('tasks') }}/" + taskId + "/" + action;

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    tasksTable.ajax.reload();
                    if (window.updateGlobalTimer) {
                        window.updateGlobalTimer(); // Update global timer
                    }
                },
                error: function(xhr) {
                    console.error('Error performing task action:', xhr);
                    let errorMessage = 'Error performing task action.';
                    if (xhr.status === 409) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showToast(errorMessage, 'danger');
                }
            });
        });

        // View Task Details

        $('#tasks-table').on('change', '.task-status-select', function() {
            var taskId = $(this).data('id');
            var newStatus = $(this).val();
            var $selectElement = $(this);
            var originalStatus = $selectElement.data('current-status');

            $.ajax({
                url: `/tasks/${taskId}/update-status`,
                method: 'POST',
                data: {
                    status: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    tasksTable.ajax.reload();
                },
                error: function(xhr) {
                    showToast('Error updating status: ' + (xhr.responseJSON.message || ''),
                        'danger');
                    $selectElement.val(originalStatus);
                }
            });
        });





        $('#tasks-table').on('click', '.view-task', function() {
            // Reset modal content immediately to avoid showing previous task data
            $('#task-title').text('Loading...');
            $('#task-description').text('');
            setTaskStatusBadge(null);
            $('#task-timer-modal').text('N/A');
            $('#task-due-date').text('N/A');
            $('#task-location').text('N/A');
            $('#task-amount-to-be-collected').text('0.00');
            $('#client-name, #client-email, #client-phone').text('N/A');
            $('#service-name, #service-description, #product-name, #product-model, #model-series, #service-price').text('N/A');
            $('#fsr-assessment, #fsr-cause, #fsr-actions, #fsr-status, #fsr-payment-status').text('N/A');
            $('#client-details, #service-product-details, #fsr-details-section, #fsr-images-container').hide();
            $('#fsr-images-list').empty();
            $('#task-map-display').hide();
            $('#route-map-button-container').hide();
            $('#followups-list').html('<div class="text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            
            $('#viewTaskModal').modal('show');

            var taskId = $(this).data('id');
            $.ajax({
                url: '/tasks/' + taskId,
                method: 'GET',
                success: function(data) {

                    $('#task-title').text(data.title || 'N/A');
                    $('#task-description').text(data.description || 'N/A');
                    setTaskStatusBadge(data.status);
                    $('#task-location').text('N/A');
                    $('#task-amount-to-be-collected').text('0.00');
                    $('#task-due-date').text('N/A');
                    $('#client-name, #client-email, #client-phone').text('N/A');
                    $('#service-name, #service-description, #product-name, #product-model, #model-series, #service-price').text('N/A');
                    $('#client-details, #service-product-details, #fsr-details-section').hide();

                    // Timer logic for modal
                    const timerElementModal = $('#task-timer-modal');
                    if (window.modalTaskTimer) {
                        clearInterval(window.modalTaskTimer);
                    }

                    if (data.status === 'in_progress' || data.status === 'hold' || data
                        .status === 'stopped' || data.status === 'completed') {
                        let elapsedSeconds = data.task_elapsed_time ? data
                            .task_elapsed_time : 0;

                        const formatTime = (seconds) => {
                            let h = Math.floor(seconds / 3600).toString().padStart(2,
                                '0');
                            let m = Math.floor((seconds % 3600) / 60).toString()
                                .padStart(2, '0');
                            let s = (seconds % 60).toString().padStart(2, '0');
                            return `${h}:${m}:${s}`;
                        };

                        timerElementModal.html(formatTime(elapsedSeconds));

                        if (data.status === 'in_progress') {
                            window.modalTaskTimer = setInterval(function() {
                                elapsedSeconds++;
                                timerElementModal.html(formatTime(elapsedSeconds));
                            }, 1000);
                        }
                    } else {
                        timerElementModal.html('N/A');
                    }

                    if (data.due_date) {
                        const date = new Date(data.due_date);
                        const formattedDate = ('0' + date.getDate()).slice(-2) + '-' + (
                                '0' + (date.getMonth() + 1)).slice(-2) + '-' + date
                            .getFullYear();
                        $('#task-due-date').text(formattedDate);
                    } else {
                        $('#task-due-date').text('N/A');
                    }
                    $('#task-location').text(data.location ?? 'N/A');
                    $('#task-amount-to-be-collected').text(data.amount_to_be_collected ? data.amount_to_be_collected : '0.00');

                    const mapDisplayDiv = $('#task-map-display');
                    const routeMapButtonContainer = $('#route-map-button-container');
                    const viewRouteMapBtn = $('#view-route-map-btn');

                    if (data.latitude && data.longitude) {
                        mapDisplayDiv.show();
                        initTaskMap(data.latitude, data.longitude);
                        routeMapButtonContainer.show();
                        viewRouteMapBtn.attr('href', '/tasks/' + data.id + '/route-map');
                    } else {
                        mapDisplayDiv.hide();
                        routeMapButtonContainer.hide();
                        viewRouteMapBtn.attr('href', '#');
                    }

                    if (data.type === 'client_based') {
                        let entryData = data.is_service ? data.entry : data.lead;

                        if (entryData) {
                            if (entryData.client) {
                                $('#client-name').text(entryData.client.name);
                                $('#client-email').text(entryData.client.email);
                                $('#client-phone').text(entryData.client.phone || entryData.client.phone_number);
                                $('#client-details').show();
                            } else if (!data.is_service && entryData.name) {
                                // For leads, name/email/phone might be directly on the lead
                                $('#client-name').text(entryData.name);
                                $('#client-email').text(entryData.email || 'N/A');
                                $('#client-phone').text(entryData.phone_number || 'N/A');
                                $('#client-details').show();
                            }

                            $('#service-name').text(data.is_service ? entryData.name : 'Lead: ' + entryData.name);
                            $('#service-description').text(data.is_service ? entryData.description : entryData.remarks);
                            $('#service-price').text(data.is_service ? entryData.price : 'N/A');

                            if (entryData.product) {
                                $('#product-name').text(entryData.product.name);
                            }
                            if (entryData.product_model) {
                                $('#product-model').text(entryData.product_model.name);
                            }
                            if (entryData.model_series) {
                                $('#model-series').text(entryData.model_series.name);
                            }
                            $('#service-product-details').show();
                        }
                    } else {
                        $('#client-details').hide();
                        $('#service-product-details').hide();
                    }

                    // FSR Details Population
                    if (data.fsr_report) {
                        $('#fsr-assessment').text(data.fsr_report.on_site_assessment || 'N/A');
                        $('#fsr-cause').text(data.fsr_report.analysis_of_cause || 'N/A');
                        $('#fsr-actions').text(data.fsr_report.actions_taken || 'N/A');
                        $('#fsr-status').text(data.fsr_report.status ? data.fsr_report.status.toUpperCase() : 'N/A');
                        $('#fsr-payment-status').text(data.fsr_report.payment_status ? data.fsr_report.payment_status.toUpperCase() : 'N/A');
                        
                        $('#fsr-details-section').show();

                        if (data.fsr_report.images && data.fsr_report.images.length > 0) {
                            var imagesHtml = '';
                            data.fsr_report.images.forEach(function(image) {
                                var url = '/storage/' + image;
                                imagesHtml += `<a href="${url}" target="_blank">
                                                    <img src="${url}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                                </a>`;
                            });
                            $('#fsr-images-list').html(imagesHtml);
                            $('#fsr-images-container').show();
                        } else {
                            $('#fsr-images-container').hide();
                        }
                    } else {
                        $('#fsr-details-section').hide();
                    }

                    // Display follow-ups
                    var followupsHtml = '';
                    if (data.followups && data.followups.length > 0) {
                        data.followups.forEach(function(followup) {
                            followupsHtml += `
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">By ${followup.user.name} on ${new Date(followup.created_at).toLocaleString()}</h6>
                                        <p class="card-text">${followup.notes}</p>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        followupsHtml = '<p>No follow-ups yet.</p>';
                    }
                    $('#followups-list').html(followupsHtml);

                    // Add other fields as needed
                },
                error: function(error) {
                    console.error('Error fetching task details:', error);
                    showToast('Error fetching task details.', 'danger');
                }
            });
        });

        $('#viewTaskModal').on('hidden.bs.modal', function() {
            if (window.modalTaskTimer) {
                clearInterval(window.modalTaskTimer);
                window.modalTaskTimer = null;
            }
            $(this).find('#task-title').text('');
            $(this).find('#task-description').text('');
            $(this).find('#task-status').text('N/A');
            $(this).find('#task-status').removeClass('bg-warning bg-info bg-secondary bg-danger bg-success text-dark text-white').addClass('bg-light text-dark');
            $(this).find('#task-timer-modal').text('N/A');
            $(this).find('#task-due-date').text('N/A');
            $(this).find('#task-location').text('N/A');
            $(this).find('#task-amount-to-be-collected').text('0.00');
            $(this).find('#client-name, #client-email, #client-phone').text('N/A');
            $(this).find('#task-map-display').hide(); // Hide map div
            $(this).find('#route-map-button-container').hide(); // Hide route map button container
            if (taskMarker) {
                taskMarker.setMap(null); // Remove marker from map
                taskMarker = null;
            }
            taskMap = null; // Clear map object

            $(this).find('#service-name').text('N/A');
            $(this).find('#service-description').text('N/A');

            $(this).find('#product-name').text('N/A');
            $(this).find('#product-model').text('N/A');
            $(this).find('#model-series').text('N/A');
            $(this).find('#service-price').text('N/A');
            $(this).find('#fsr-assessment, #fsr-cause, #fsr-actions, #fsr-status, #fsr-payment-status').text('N/A');
            $(this).find('#fsr-images-list').empty();
            $(this).find('#client-details, #service-product-details, #fsr-details-section, #fsr-images-container').hide();
        });

        // Confirmation Modal Logic
        var clockAction;
        $('#clock-in-btn, #clock-out-btn').on('click', function(e) {
            e.preventDefault();
            clockAction = $(this).attr('id');
            var modal = $('#confirmationModal');
            if (clockAction === 'clock-in-btn') {
                modal.find('.modal-title').text('Confirm Clock In');
                modal.find('#confirmation-message').text('Are you sure you want to clock in?');
                modal.find('#remarks-container').hide();
                modal.find('#confirmActionBtn').removeClass('btn-danger').addClass('btn-primary');
            } else {
                modal.find('.modal-title').text('Confirm Clock Out');
                modal.find('#confirmation-message').text('Are you sure you want to clock out?');
                modal.find('#remarks-container').show();
                modal.find('#remarks').val('');
                modal.find('#remarks').removeClass('is-invalid');
                modal.find('#remarks_error').text('');
                modal.find('#confirmActionBtn').removeClass('btn-primary').addClass('btn-danger');
            }
            modal.modal('show');
        });

        $('#confirmActionBtn').on('click', function() {
            var url = (clockAction === 'clock-in-btn') ? "{{ route('clock.in') }}" : "{{ route('clock.out') }}";
            var data = {};
            if (clockAction === 'clock-out-btn') {
                data.remarks = $('#remarks').val();
                if (!data.remarks) {
                    $('#remarks').addClass('is-invalid');
                    $('#remarks_error').text('Remarks are required to clock out.');
                    return;
                }
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {

                    $('#confirmationModal').modal('hide');
                    showToast(response.message, 'success');
                    if (clockAction === 'clock-in-btn') {
                        $('#clock-in-btn').addClass('d-none');
                        $('#clock-out-btn').removeClass('d-none');
                        window.isClockedIn = true; // Update global status
                    } else {
                        $('#clock-out-btn').addClass('d-none');
                        $('#clock-in-btn').removeClass('d-none');
                        window.isClockedIn = false; // Update global status
                    }
                    tasksTable.ajax
                        .reload(); // Reload DataTable to update button visibility
                },
                error: function(xhr) {

                    $('#confirmationModal').modal('hide');
                    showToast(xhr.responseJSON.message, 'danger');
                }
            });
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (e.target.id === 'create-tab') {
                if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && !map) {
                    initMap();
                } else if (map) {
                    google.maps.event.trigger(map, 'resize');
                    const currentPosition = marker ? marker.position : new google.maps.LatLng(
                        20.5937, 78.9629);
                    map.setCenter(currentPosition);
                }
            }
        });
    });
</script>
@endpush

@section('modal')
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmation-message">Are you sure you want to perform this action?</p>
                <div id="remarks-container" style="display: none;">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter remarks..."></textarea>
                    <div class="invalid-feedback" id="remarks_error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection
