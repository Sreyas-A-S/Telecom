@extends('layouts.admin')

@section('title', 'Lead Profile')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Lead Profile</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Simple minimalist styling */
    .profile-info-container {
        background: #fff;
        padding: 1rem;
    }

    .profile-section-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #333;
        text-transform: uppercase;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 5px;
        margin-bottom: 15px;
        margin-top: 10px;
    }

    .info-grid {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .info-item {
        width: 33.33%;
        padding: 8px 15px;
        display: flex;
        flex-direction: column;
        border-bottom: 1px solid #f8f9fa;
    }

    .info-label {
        color: #888;
        font-size: 0.75rem;
        margin-bottom: 2px;
    }

    .info-value {
        color: #333;
        font-size: 0.85rem;
        font-weight: 600;
    }

    #profileRemarks {
        background: #fdfdfd;
        border: 1px solid #f0f0f0;
        padding: 12px;
        font-size: 0.85rem;
        border-radius: 4px;
        color: #555;
    }

    .profile-map {
        height: 300px;
        border-radius: 6px;
        overflow: hidden;
    }

    @media (max-width: 991px) {
        .info-item {
            width: 50%;
        }
    }

    @media (max-width: 575px) {
        .info-item {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5>Lead Details</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs d-flex" id="leadProfileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                            type="button" role="tab" aria-controls="profile" aria-selected="true">Detailed Profile
                            View</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="followup-tab" data-bs-toggle="tab" data-bs-target="#followup"
                            type="button" role="tab" aria-controls="followup" aria-selected="false">Follow Up
                            Data</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="task-tab" data-bs-toggle="tab" data-bs-target="#task"
                            type="button" role="tab" aria-controls="task" aria-selected="false">Tasks</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fsr-tab" data-bs-toggle="tab" data-bs-target="#fsr"
                            type="button" role="tab" aria-controls="fsr" aria-selected="false">FSR</button>
                    </li>
                </ul>
                <div class="tab-content" id="leadProfileTabContent">
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <!-- Detailed Profile View Content will go here -->
                        <div id="profileLoader" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading Profile Details...</p>
                        </div>
                        <div class="mt-3 profile-info-container" id="profileContent" style="display: none;">
                            <div class="row">
                                <div class="col-lg-8">
                                    <!-- Unified Info Section -->
                                    <h6 class="profile-section-title">Lead Information</h6>
                                    <div class="info-grid">
                                        <div class="info-item"><span class="info-label">Full Name</span><span class="info-value" id="profileName"></span></div>
                                        <div class="info-item"><span class="info-label">Email</span><span class="info-value" id="profileEmail"></span></div>
                                        <div class="info-item"><span class="info-label">Phone</span><span class="info-value" id="profilePhone"></span></div>
                                        <div class="info-item"><span class="info-label">Lead Value</span><span class="info-value" id="profileLeadValue"></span></div>
                                        <div class="info-item"><span class="info-label">Agent</span><span class="info-value" id="profileAgent"></span></div>
                                        <div class="info-item"><span class="info-label">Dealership</span><span class="info-value" id="profileDealership"></span></div>
                                        <div class="info-item"><span class="info-label">Status</span><span class="info-value" id="profileStatus"></span></div>
                                        <div class="info-item"><span class="info-label">Location</span><span class="info-value" id="profileLocation"></span></div>
                                        <div class="info-item"><span class="info-label">Source</span><span class="info-value" id="profileLeadSource"></span></div>
                                        <div class="info-item">
                                            <span class="info-label">Chance of Success</span>
                                            <div id="profileChanceOfSuccessContainer"></div>
                                        </div>
                                    </div>

                                    <h6 class="profile-section-title mt-4">Interested Machines/Products</h6>
                                    <div class="table-responsive pb-3 mb-4 border-bottom">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Machine</th>
                                                    <th>Series/Model</th>
                                                    <th>Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody id="profileProductsList">
                                                <!-- Products will be appended here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <h6 class="profile-section-title mt-4">Internal Remarks</h6>
                                    <div id="profileRemarks" class="mb-4"></div>
                                </div>

                                <div class="col-lg-4 border-start">
                                    <h6 class="profile-section-title">Location Map</h6>
                                    <div id="profileMap" class="profile-map mb-2"></div>
                                    <div class="mt-2 small text-muted">
                                        <div><strong>Address:</strong> <span id="profileMapLocation"></span></div>
                                        <div class="d-flex gap-3">
                                            <span><strong>Lat:</strong> <span id="profileLatitude"></span></span>
                                            <span><strong>Lng:</strong> <span id="profileLongitude"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($followUpPermissions['can_read'] && checkMenu(Session::get('role_id'), 11, 'read'))
                    <div class="tab-pane fade" id="followup" role="tabpanel" aria-labelledby="followup-tab">
                        <!-- Follow Up Data Content will go here -->
                        <div class="mt-3">
                            @if($followUpPermissions['can_create'] && checkMenu(Session::get('role_id'), 11, 'create'))
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal"
                                data-bs-target="#addFollowUpModal">Add Follow Up</button>
                            @else
                            <button class="btn btn-primary mb-3" disabled>Add Follow Up</button>
                            @endif
                            <table class="table table-bordered table-striped" id="followup-table">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Next Follow Up Date</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Created Date</th>
                                        @if($followUpPermissions['can_update'] || $followUpPermissions['can_delete'])
                                        <th>Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Follow up data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    <div class="tab-pane fade" id="task" role="tabpanel" aria-labelledby="task-tab">
                        <div class="mt-3">
                            <table class="table table-bordered table-striped" id="profile-tasks-table">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Title</th>
                                        <th>Assigned To</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Task data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="fsr" role="tabpanel" aria-labelledby="fsr-tab">
                        <div class="mt-3">
                            <table class="table table-bordered table-striped" id="profile-fsr-table">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Task Title</th>
                                        <th>Submitted By</th>
                                        <th>Assessment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- FSR data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>

<!-- Add Follow Up Modal -->
@if($followUpPermissions['can_create'] && checkMenu(Session::get('role_id'), 11, 'create'))
<div class="modal fade" id="addFollowUpModal" tabindex="-1" aria-labelledby="addFollowUpModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFollowUpModalLabel">Add New Follow Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addFollowUpForm">
                @csrf
                <input type="hidden" id="followUpLeadId" name="lead_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nextFollowUpDate" class="form-label">Next Follow Up Date</label>
                        <input type="date" class="form-control" id="nextFollowUpDate"
                            name="next_follow_up_date">
                    </div>
                    <div class="mb-3">
                        <label for="nextFollowUpTime" class="form-label">Next Follow Up Time</label>
                        <div class="row">
                            <div class="col-4">
                                <select class="form-select" id="nextFollowUpTimeHour"
                                    name="next_follow_up_time_hour">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                            <div class="col-4">
                                <select class="form-select" id="nextFollowUpTimeMinute"
                                    name="next_follow_up_time_minute">
                                    @for ($i = 0; $i < 60; $i +=5)
                                        <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                            <div class="col-4">
                                <select class="form-select" id="nextFollowUpTimeAmPm"
                                    name="next_follow_up_time_ampm">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">New Status</label>
                        <select class="form-select" id="newStatus" name="new_status">
                            <option value="pending">Pending</option>
                            <option value="in progress">In Progress</option>
                            <option value="win">Win</option>
                            <option value="lost">Lost</option>
                            <option value="positive">Positive</option>
                        </select>
                    </div>
                    <div class="mb-3" id="addLossReasonDiv" style="display: none;">
                        <label for="addLossReason" class="form-label">Reason for Loss <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="addLossReason" name="reason" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Follow Up</button>
                </div>
            </form>
        </div>
    </div>
</div>
@else
<div class="modal fade" id="addFollowUpModal" tabindex="-1" aria-labelledby="addFollowUpModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFollowUpModalLabel">Add New Follow Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to add follow ups.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Edit Follow Up Modal -->
@if($followUpPermissions['can_update'] && checkMenu(Session::get('role_id'), 11, 'update'))
<div class="modal fade" id="editFollowUpModal" tabindex="-1" aria-labelledby="editFollowUpModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFollowUpModalLabel">Edit Follow Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editFollowUpForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editFollowUpId" name="id">
                <input type="hidden" id="editFollowUpLeadId" name="lead_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editNextFollowUpDate" class="form-label">Next Follow Up Date</label>
                        <input type="date" class="form-control" id="editNextFollowUpDate"
                            name="next_follow_up_date">
                    </div>
                    <div class="mb-3">
                        <label for="editNextFollowUpTime" class="form-label">Next Follow Up Time</label>
                        <div class="row">
                            <div class="col-4">
                                <select class="form-select" id="editNextFollowUpTimeHour"
                                    name="edit_next_follow_up_time_hour">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                            <div class="col-4">
                                <select class="form-select" id="editNextFollowUpTimeMinute"
                                    name="edit_next_follow_up_time_minute">
                                    @for ($i = 0; $i < 60; $i +=5)
                                        <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                            <div class="col-4">
                                <select class="form-select" id="editNextFollowUpTimeAmPm"
                                    name="edit_next_follow_up_time_ampm">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editNewStatus" class="form-label">New Status</label>
                        <select class="form-select" id="editNewStatus" name="new_status">
                            <option value="pending">Pending</option>
                            <option value="in progress">In Progress</option>
                            <option value="win">Win</option>
                            <option value="lost">Lost</option>
                            <option value="positive">Positive</option>
                        </select>
                    </div>
                    <div class="mb-3" id="editLossReasonDiv" style="display: none;">
                        <label for="editLossReason" class="form-label">Reason for Loss <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="editLossReason" name="reason" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editRemarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="editRemarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@else
<div class="modal fade" id="editFollowUpModal" tabindex="-1" aria-labelledby="editFollowUpModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFollowUpModalLabel">Edit Follow Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to update follow ups.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Delete Follow Up Modal -->
@if($followUpPermissions['can_delete'] && checkMenu(Session::get('role_id'), 11, 'delete'))
<div class="modal fade" id="deleteFollowUpModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteFollowUpModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFollowUpModalLabel">Delete Follow Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this follow up?</p>
                <input type="hidden" id="deleteFollowUpId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFollowUp">Delete</button>
            </div>
        </div>
    </div>
</div>
@else
<div class="modal fade" id="deleteFollowUpModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteFollowUpModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFollowUpModalLabel">Delete Follow Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to delete follow ups.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Task Follow Up Modal -->
<div class="modal fade" id="taskFollowupsModal" tabindex="-1" aria-labelledby="taskFollowupsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskFollowupsModalLabel">Task Follow-ups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="task-followups-dt">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Notes</th>
                                <th>By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Task Analytics Modal -->
<div class="modal fade" id="taskAnalyticsModal" tabindex="-1" aria-labelledby="taskAnalyticsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskAnalyticsModalLabel">Task Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Total Time Contributed:</strong> <span id="analyticsTotalTime"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="analytics-logs-table">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>By Employee</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="analyticsLogsBody">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Follow Up Modal -->
<div class="modal fade" id="viewFollowUpModal" tabindex="-1" aria-labelledby="viewFollowUpModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewFollowUpModalLabel">View Follow Up Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Next Follow Up Date:</strong> <span id="viewNextFollowUpDate"></span></p>
                <p><strong>New Status:</strong> <span id="viewNewStatus"></span></p>
                <p><strong>Remarks:</strong> <span id="viewRemarks"></span></p>
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
    $(document).ready(function() {
        var leadId = {{ $lead->id }};
        // Assuming $lead is passed from controller
        $('#followUpLeadId').val(leadId);

        // Store active tab on change
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            localStorage.setItem('activeLeadProfileTab', $(e.target).attr('id'));
        });

        // Restore active tab on load
        var activeTabId = localStorage.getItem('activeLeadProfileTab');
        if (activeTabId) {
            var tabTrigger = document.querySelector('#' + activeTabId);
            if (tabTrigger) {
                var tab = new bootstrap.Tab(tabTrigger);
                tab.show();
            }
        }

        // Toggle Reason for Loss field in Add Modal
        $('#newStatus').on('change', function() {
            if ($(this).val() === 'lost') {
                $('#addLossReasonDiv').show();
                $('#addLossReason').prop('required', true);
            } else {
                $('#addLossReasonDiv').hide();
                $('#addLossReason').prop('required', false);
            }
        });

        // Toggle Reason for Loss field in Edit Modal
        $('#editNewStatus').on('change', function() {
            if ($(this).val() === 'lost') {
                $('#editLossReasonDiv').show();
                $('#editLossReason').prop('required', true);
            } else {
                $('#editLossReasonDiv').hide();
                $('#editLossReason').prop('required', false);
            }
        });

        // Toggle Reason for Loss field in Add Modal
        $('#newStatus').on('change', function() {
            if ($(this).val() === 'lost') {
                $('#addLossReasonDiv').show();
                $('#addLossReason').prop('required', true);
            } else {
                $('#addLossReasonDiv').hide();
                $('#addLossReason').prop('required', false);
            }
        });

        // Toggle Reason for Loss field in Edit Modal
        $('#editNewStatus').on('change', function() {
            if ($(this).val() === 'lost') {
                $('#editLossReasonDiv').show();
                $('#editLossReason').prop('required', true);
            } else {
                $('#editLossReasonDiv').hide();
                $('#editLossReason').prop('required', false);
            }
        });

        // Fetch lead details for profile tab
        $.ajax({
            url: '/leads/' + leadId,
            method: 'GET',
            searchable: true,
            beforeSend: function() {
                $('#profileLoader').show();
                $('#profileContent').hide();
            },
            success: function(data) {
                $('#profileLoader').hide();
                $('#profileContent').show();

                $('#profileSalutation').text(data.salutation || 'N/A');
                $('#profileName').text(data.name || 'N/A');
                $('#profileEmail').text(data.email || 'N/A');
                $('#profilePhone').text(data.phone_number || 'N/A');
                $('#profileAlternateContact').text(data.alternate_contact_number || 'N/A');
                $('#profileAgent').text(data.agent ? data.agent.name : 'N/A');
                $('#profileAgentType').text(data.agent && data.agent.type ? data.agent.type :
                    'N/A');
                if (data.agent && data.agent.type === 'Employee' && data.agent.employee_code) {
                    $('#profileEmployeeCode').text(data.agent.employee_code);
                } else {
                    $('#profileEmployeeCode').text('N/A');
                }
                $('#profileLeadSource').text(data.lead_source ? data.lead_source.name : 'N/A');
                $('#profileLeadCategory').text(data.lead_category ? data.lead_category.name :
                    'N/A');
                $('#profileLeadValue').text(data.lead_value || 'N/A');
                $('#profileAllowFollowUp').text(data.allow_follow_up ? 'Yes' : 'No');
                $('#profileStatus').text(data.status || 'N/A');
                
                // Populate Products List
                $('#profileProductsList').empty();
                if (data.items && data.items.length > 0) {
                    data.items.forEach(function(item) {
                        $('#profileProductsList').append(`
                            <tr>
                                <td>${item.product_name || 'N/A'}</td>
                                <td>${item.product_model_name || 'N/A'} ${item.model_series_name ? '(' + item.model_series_name + ')' : ''}</td>
                                <td>${item.quantity || 1}</td>
                            </tr>
                        `);
                    });
                } else if (data.product) {
                    // Fallback for leads that might only have the single product fields populated
                    $('#profileProductsList').append(`
                        <tr>
                            <td>${data.product.name || 'N/A'}</td>
                            <td>${data.product_model ? data.product_model.name : 'N/A'} ${data.model_series ? '(' + data.model_series.name + ')' : ''}</td>
                            <td>${data.quantity || 1}</td>
                        </tr>
                    `);
                } else {
                    $('#profileProductsList').append('<tr><td colspan="3" class="text-center text-muted">No machines/products linked to this lead.</td></tr>');
                }

                $('#profileLocation').text(data.location || 'N/A');
                $('#profileQuantity').text(data.quantity || 'N/A');
                $('#profileFinancier').text(data.financier || 'N/A');
                $('#profileType').text(data.type || 'N/A');
                $('#profileLoginStatus').text(data.login_status || 'N/A');
                $('#profileStage').text(data.stage || 'N/A');
                $('#profileRemarks').text(data.remarks || 'N/A');
                $('#profileDealership').text(data.dealership ? data.dealership.name : 'N/A');

                // Populate GPS data and initialize map if available
                if (data.latitude && data.longitude) {
                    $('#profileMapLocation').text(data.map_location || 'N/A');
                    $('#profileLatitude').text(data.latitude);
                    $('#profileLongitude').text(data.longitude);
                    initProfileMap(data.latitude, data.longitude);
                } else {
                    $('#profileMapLocation').text('N/A');
                    $('#profileLatitude').text('N/A');
                    $('#profileLongitude').text('N/A');
                    $('#profileMap').html(`
                        <div class="d-flex flex-column align-items-center justify-content-center text-center p-4 bg-light border-0 rounded-3 shadow-sm h-100" style="min-height: 350px;">
                            <div class="bg-white p-4 rounded-circle mb-3 shadow-sm border">
                                <i class="fa fa-map-marker-alt text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Location Not Available</h5>
                            <p class="text-muted small px-lg-5 mb-0">No GPS coordinates or map location data has been captured for this lead yet.</p>
                        </div>
                    `).css({
                        'min-height': '350px',
                        'display': 'block',
                        'border': 'none',
                        'background': 'transparent'
                    });
                }

                // Render Chance of Success Progress Bar
                var percentage = data.chance_of_success || 0;
                var progressBarClass = 'bg-primary';
                if (percentage < 30) {
                    progressBarClass = 'bg-danger';
                } else if (percentage < 70) {
                    progressBarClass = 'bg-warning';
                } else {
                    progressBarClass = 'bg-success';
                }

                var progressBarHtml = `
                    <div class="d-flex align-items-center gap-2 mt-2 w-100">
                        <div class="progress flex-grow-1" style="height: 8px; background-color: #e9ecef; border-radius: 10px; overflow: hidden;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated ${progressBarClass}"
                                role="progressbar" style="width: ${percentage}%; transition: width 1s ease-in-out;"
                                aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <span class="fw-bold text-dark small">${percentage}%</span>
                    </div>
                `;
                $('#profileChanceOfSuccessContainer').html(progressBarHtml);
            },
            error: function(error) {
                console.error('Error fetching lead profile:', error);
            }
        });

        let profileMap;
        let profileMarker;

        function initProfileMap(lat, lng) {
            // Guard: if Google Maps failed to load, show a friendly message
            if (typeof window.google === 'undefined' || !google.maps) {
                $('#profileMap').html(
                    '<div class="alert alert-warning" style="height:100%; display:flex; align-items:center; justify-content:center;">Map is unavailable — Google Maps failed to load.</div>'
                );
                return;
            }

            const latLng = {
                lat: parseFloat(lat),
                lng: parseFloat(lng)
            };
            profileMap = new google.maps.Map(document.getElementById('profileMap'), {
                zoom: 13,
                center: latLng,
                mapId: "DEMO_MAP_ID",
            });

            profileMarker = new google.maps.marker.AdvancedMarkerElement({
                position: latLng,
                map: profileMap,
            });
        }

        // Initialize Follow Up DataTable
        var followUpTable = $('#followup-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
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
            ajax: '/leads/' + leadId + '/followups',
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'next_follow_up_date',
                    name: 'next_follow_up_date',
                    render: function(data, type, row) {
                        if (!data) {
                            return 'N/A';
                        }
                        var dateTime = new Date(data);
                        var day = String(dateTime.getDate()).padStart(2, '0');
                        var month = String(dateTime.getMonth() + 1).padStart(2,
                            '0'); // Month is 0-indexed
                        var year = dateTime.getFullYear();
                        var datePart = `${day}/${month}/${year}`;
                        var timePart = dateTime.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        return `<div>${datePart}</div><span class="badge bg-info">${timePart}</span>`;
                    }
                },
                {
                    data: 'new_status',
                    name: 'new_status',
                    render: function(data, type, row) {
                        var statusColors = {
                            'pending': 'bg-warning',
                            'in progress': 'bg-info',
                            'win': 'bg-success',
                            'lost': 'bg-danger',
                            'positive': 'bg-primary'
                        };
                        var colorClass = statusColors[data] || 'bg-secondary';
                        return '<span class="badge rounded-pill ' + colorClass + '">' + data +
                            '</span>';
                    }
                },
                {
                    data: 'remarks',
                    name: 'remarks'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data, type, row) {
                        if (!data) {
                            return 'N/A';
                        }
                        var dateTime = new Date(data);
                        var day = String(dateTime.getDate()).padStart(2, '0');
                        var month = String(dateTime.getMonth() + 1).padStart(2,
                            '0'); // Month is 0-indexed
                        var year = dateTime.getFullYear();
                        var datePart = `${day}/${month}/${year}`;
                        var timePart = dateTime.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        return `<div>${datePart}</div><span class="badge bg-info">${timePart}</span>`;
                    }
                }, // Moved Created Date column
                // Conditionally add the Actions column
                @if($followUpPermissions['can_update'] || $followUpPermissions['can_delete']) {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var btn =
                            '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                        btn +=
                            '<li class="view"><a href="javascript:void(0)" title="View" data-id="' +
                            row.id +
                            '" class="view-followup-btn"><i class="icon-eye"></i></a></li>'; // Added view button
                        @if($followUpPermissions['can_update'])
                        btn +=
                            '<li class="edit"><a href="javascript:void(0)" title="Edit" data-id="' +
                            row.id +
                            '" class="edit-followup-btn"><i class="icon-pencil-alt"></i></a></li>';
                        @endif
                        @if($followUpPermissions['can_delete'])
                        btn +=
                            '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' +
                            row.id +
                            '" class="delete-followup-btn"><i class="icon-trash"></i></a></li>';
                        @endif
                        btn += '</ul>';
                        return btn;
                    }
                }
                @endif
            ]
        });

        // Handle Add Follow Up Form Submission
        $('#addFollowUpForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            // Get time components from dropdowns
            var hour = $('#nextFollowUpTimeHour').val();
            var minute = $('#nextFollowUpTimeMinute').val();
            var ampm = $('#nextFollowUpTimeAmPm').val();

            // Convert to 24-hour format
            var fullHour = parseInt(hour);
            if (ampm === 'PM' && fullHour !== 12) {
                fullHour += 12;
            } else if (ampm === 'AM' && fullHour === 12) {
                fullHour = 0;
            }
            var formattedHour = String(fullHour).padStart(2, '0');

            // Combine date and time
            var nextFollowUpDate = $('#nextFollowUpDate').val();
            var nextFollowUpDateTime = nextFollowUpDate + ' ' + formattedHour + ':' + minute + ':00';

            // Update formData with the combined datetime
            formData.set('next_follow_up_date', nextFollowUpDateTime);
            formData.delete('next_follow_up_time_hour');
            formData.delete('next_follow_up_time_minute');
            formData.delete('next_follow_up_time_ampm');

            // Log FormData for debugging
            for (var pair of formData.entries()) {
                console.log(pair[0] + ', ' + pair[1]);
            }

            // Log FormData for debugging
            for (var pair of formData.entries()) {
                console.log(pair[0] + ', ' + pair[1]);
            }

            $.ajax({
                url: '/leads/' + leadId + '/followups',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    followUpTable.ajax.reload();
                    $('#addFollowUpModal').modal('hide');
                    $('#addFollowUpForm')[0].reset();
                },
                error: function(error) {
                    console.error('Error adding follow up:', error);
                    showToast('Error adding follow up.', 'danger');
                }
            });
        });

        // Edit Follow Up
        $('#followup-table').on('click', '.edit-followup-btn', function() {
            var followupId = $(this).data('id');
            $('#editFollowUpLeadId').val(leadId);
            $.ajax({
                url: '/leads/' + leadId + '/followups/' + followupId + '/edit',
                method: 'GET',
                success: function(data) {
                    $('#editFollowUpId').val(data.id);
                    $('#editNextFollowUpDate').val(data.next_follow_up_date ? data
                        .next_follow_up_date.substring(0, 10) : ''); // Extract date part
                    if (data.next_follow_up_date) {
                        var timePart = data.next_follow_up_date.substring(11, 16); // HH:MM
                        var hour = parseInt(timePart.substring(0, 2));
                        var minute = parseInt(timePart.substring(3, 5));
                        var ampm = hour >= 12 ? 'PM' : 'AM';

                        // Convert to 12-hour format
                        hour = hour % 12;
                        hour = hour ? hour : 12; // the hour '0' should be '12'

                        $('#editNextFollowUpTimeHour').val(String(hour).padStart(2, '0'));
                        
                        // Round minute to nearest 5 if needed or just use it if it matches the options
                        var minuteStr = String(minute).padStart(2, '0');
                        if ($('#editNextFollowUpTimeMinute option[value="' + minuteStr + '"]').length > 0) {
                            $('#editNextFollowUpTimeMinute').val(minuteStr);
                        } else {
                            var roundedMinute = Math.round(minute / 5) * 5;
                            if (roundedMinute === 60) roundedMinute = 55;
                            $('#editNextFollowUpTimeMinute').val(String(roundedMinute).padStart(2, '0'));
                        }
                        
                        $('#editNextFollowUpTimeAmPm').val(ampm);
                    } else {
                        $('#editNextFollowUpTimeHour').val('12'); // Default to 12 AM
                        $('#editNextFollowUpTimeMinute').val('00');
                        $('#editNextFollowUpTimeAmPm').val('AM');
                    }
                    $('#editNewStatus').val(data.new_status);
                    $('#editRemarks').val(data.remarks);
                    $('#editFollowUpModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching follow up data:', error);
                    showToast('Error fetching follow up data.', 'danger');
                }
            });
        });

        // Handle Edit Follow Up Form Submission
        $('#editFollowUpForm').on('submit', function(e) {
            e.preventDefault();
            var followupId = $('#editFollowUpId').val();
            var formData = new FormData(this);

            // Get time components from dropdowns
            var hour = $('#editNextFollowUpTimeHour').val();
            var minute = $('#editNextFollowUpTimeMinute').val();
            var ampm = $('#editNextFollowUpTimeAmPm').val();

            // Convert to 24-hour format
            var fullHour = parseInt(hour);
            if (ampm === 'PM' && fullHour !== 12) {
                fullHour += 12;
            } else if (ampm === 'AM' && fullHour === 12) {
                fullHour = 0;
            }
            var formattedHour = String(fullHour).padStart(2, '0');

            // Combine date and time
            var editNextFollowUpDate = $('#editNextFollowUpDate').val();
            var editNextFollowUpDateTime = editNextFollowUpDate + ' ' + formattedHour + ':' + minute +
                ':00';

            // Update formData with the combined datetime
            formData.set('next_follow_up_date', editNextFollowUpDateTime);
            formData.delete('edit_next_follow_up_time_hour');
            formData.delete('edit_next_follow_up_time_minute');
            formData.delete('edit_next_follow_up_time_ampm');

            $.ajax({
                url: '/leads/' + leadId + '/followups/' + followupId,
                method: 'POST', // Use POST for PUT/PATCH with _method
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    followUpTable.ajax.reload();
                    $('#editFollowUpModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating follow up:', error);
                    showToast('Error updating follow up.', 'danger');
                }
            });
        });

        // Delete Follow Up
        $('#followup-table').on('click', '.delete-followup-btn', function() {
            var followupId = $(this).data('id');
            $('#deleteFollowUpId').val(followupId);
            $('#deleteFollowUpModal').modal('show');
        });

        // Handle Delete Follow Up Confirmation
        $('#confirmDeleteFollowUp').on('click', function() {
            var followupId = $('#deleteFollowUpId').val();
            $.ajax({
                url: '/leads/' + leadId + '/followups/' + followupId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    followUpTable.ajax.reload();
                    $('#deleteFollowUpModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting follow up:', error);
                    showToast('Error deleting follow up.', 'danger');
                }
            });
        });

        // View Follow Up
        $('#followup-table').on('click', '.view-followup-btn', function() {
            var followupId = $(this).data('id');
            $.ajax({
                url: '/leads/' + leadId + '/followups/' + followupId +
                    '/edit', // Re-using edit endpoint for data
                method: 'GET',
                success: function(data) {
                    var nextFollowUpDateTime = data.next_follow_up_date;
                    if (nextFollowUpDateTime) {
                        var dateTime = new Date(nextFollowUpDateTime);
                        var datePart = dateTime.toLocaleDateString();
                        var timePart = dateTime.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        $('#viewNextFollowUpDate').html(
                            `<div>${datePart}</div><span class="badge bg-info">${timePart}</span>`
                        );
                    } else {
                        $('#viewNextFollowUpDate').text('N/A');
                    }
                    $('#viewNewStatus').text(data.new_status);
                    $('#viewRemarks').text(data.remarks);
                    $('#viewFollowUpModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching follow up data for view:', error);
                    showToast('Error fetching follow up data for view.', 'danger');
                }
            });
        });



        // Initialize Tasks DataTable
        var tasksTable = $('#profile-tasks-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: '/leads/' + leadId + '/tasks',
            columns: [{
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
                    data: 'assigned_to',
                    name: 'assigned_to'
                },
                {
                    data: 'due_date',
                    name: 'due_date'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        // The 'action' column data from controller currently contains an empty <ul>.
                        // We will prepend the Overview button to it, or just replace it if you want only the button.
                        // Based on user request, let's put the button here.
                        // We can also fetch the link from the 'latest_followup' field if it was passed, 
                        // but since we are removing that column, we should reconstruct the link or use the data provided in 'action' if updated.

                        // Use the row data to construct the link manually since we have the ID and leadId is globally available or in URL
                        // However, simpler is to rely on what the controller sends.
                        // Let's assume the controller will send the button in 'action' column as requested?
                        // OR we construct it here.

                        var overviewBtn = '<a href="/leads/' + leadId + '/task-overview/' + row.id + '" class="btn btn-sm btn-info text-white">View Overview</a>';
                        return overviewBtn;
                    }
                }
            ]
        });

        // Initialize FSR DataTable
        var fsrTable = $('#profile-fsr-table').DataTable({
            processing: true,
            serverSide: false, // getFsrReports returns a simple collection
            responsive: true,
            autoWidth: false,
            ajax: {
                url: '/leads/' + leadId + '/fsr-reports',
                dataSrc: 'data'
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'task.title',
                    name: 'task.title',
                    render: function(data, type, row) {
                        return data || 'N/A';
                    }
                },
                {
                    data: 'submitted_by.name',
                    name: 'submitted_by.name',
                    render: function(data, type, row) {
                        return data || 'N/A';
                    }
                },
                {
                    data: 'on_site_assessment',
                    name: 'on_site_assessment',
                    render: function(data, type, row) {
                        if (!data) return 'N/A';
                        return data.length > 50 ? data.substr(0, 50) + '...' : data;
                    }
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        return '<a href="/fsr/' + data + '" class="btn btn-sm btn-primary">View FSR</a>';
                    }
                }
            ]
        });

    });

    // Handle "View Follow-ups" button click
    $('#profile-tasks-table').on('click', '.view-task-followups-btn', function() {
        var taskId = $(this).data('id');
        $('#taskFollowupsModal').modal('show');

        // Check if table is already initialized
        if ($.fn.DataTable.isDataTable('#task-followups-dt')) {
            $('#task-followups-dt').DataTable().destroy();
        }

        $('#task-followups-dt').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: '/tasks/' + taskId + '/followups/data',
            columns: [{
                    data: 'id',
                    name: 'id'
                }, // Or use a counter if preferred
                {
                    data: 'notes',
                    name: 'notes'
                },
                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                }
            ],
            order: [
                [3, 'desc']
            ] // Sort by Date desc
        });


    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,marker"></script>
@endpush