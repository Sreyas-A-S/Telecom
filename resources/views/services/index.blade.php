@extends('layouts.admin')

@php
$userHasDealership = Auth::user()->employee && Auth::user()->employee->dealership_id;
@endphp

@section('title', 'Service Entries')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Entries</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Entries</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Client History Styles */
    #clientHistoryTimeline {
        scrollbar-width: thin;
        scrollbar-color: #0d6efd #f1f1f1;
    }

    #clientHistoryTimeline::-webkit-scrollbar {
        width: 6px;
    }

    #clientHistoryTimeline::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    #clientHistoryTimeline::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 10px;
    }

    .history-timeline {
        position: relative;
        padding-left: 30px;
    }

    .history-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
        border-radius: 2px;
    }

    .history-item {
        position: relative;
        margin-bottom: 2rem;
    }

    .history-item::before {
        content: '';
        position: absolute;
        left: -34px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #0d6efd;
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        z-index: 1;
    }

    .history-item:last-child {
        margin-bottom: 0;
    }

    .history-content {
        background: #fff;
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s ease;
    }

    .history-content:hover {
        transform: translateX(5px);
        border-color: #0d6efd;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .history-date {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6c757d;
        background: #f8fafc;
        padding: 2px 8px;
        border-radius: 5px;
    }

    .history-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .history-sub {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 8px;
    }

    .history-status-badge {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 20px;
    }

    .history-followups {
        border-top: 1px dashed #eee;
        padding-top: 8px;
        margin-top: 8px;
    }

    .followup-item {
        font-size: 0.8rem;
        color: #495057;
        padding: 4px 0;
        display: flex;
        align-items: flex-start;
    }

    .followup-item i {
        font-size: 0.7rem;
        margin-top: 3px;
        margin-right: 8px;
        color: #0d6efd;
    }

    #viewEntryModal .modal-dialog {
        max-width: min(1280px, 96vw);
    }

    #viewEntryModal .modal-content {
        border-radius: 10px;
    }

    .simple-view-row {
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f7;
    }

    .simple-view-row:last-child {
        border-bottom: 0;
    }

    .simple-view-label {
        font-size: 12px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 3px;
    }

    .simple-view-value {
        font-size: 14px;
        color: #1f2937;
        word-break: break-word;
    }

    /* Skeleton Loading Styles */
    .skeleton-text {
        height: 12px;
        background: #e9ecef;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
        margin: 8px 0;
    }

    .skeleton-input {
        height: 38px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        position: relative;
        overflow: hidden;
    }

    .skeleton-animate::after {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        transform: translateX(-100%);
        background-image: linear-gradient(90deg,
                rgba(255, 255, 255, 0) 0,
                rgba(255, 255, 255, 0.2) 20%,
                rgba(255, 255, 255, 0.5) 60%,
                rgba(255, 255, 255, 0));
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        100% {
            transform: translateX(100%);
        }
    }

    .loading-field {
        opacity: 0.6;
        pointer-events: none;
        background-image: url("data:image/svg+xml,%3Csvgxmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 50 50'%3E%3Cpath fill='%230d6efd' d='M25,5A20,20,0,1,0,45,25A20,20,0,0,0,25,5ZM25,41A16,16,0,1,1,41,25A16,16,0,0,1,25,41Z' opacity='.25'/%3E%3Cpath fill='%230d6efd' d='M25,5A20,20,0,0,1,45,25h-4A16,16,0,0,0,25,9V5Z'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 25 25' to='360 25 25' dur='0.8s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 10px center !important;
        background-size: 16px 16px !important;
    }

    .select2-loading+.select2-container .select2-selection {
        opacity: 0.6;
        pointer-events: none;
        position: relative;
    }

    .select2-loading+.select2-container .select2-selection::after {
        content: "";
        position: absolute;
        right: 35px;
        top: 50%;
        margin-top: -8px;
        width: 16px;
        height: 16px;
        border: 2px solid #ccc;
        border-top-color: #0d6efd;
        border-radius: 50%;
        animation: select2-spinner 0.6s linear infinite;
    }

    @keyframes select2-spinner {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs d-flex" id="entryTab" role="tablist">
                @if(checkMenu(Session::get('role_id'), 18, 'read'))
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned"
                        type="button" role="tab" aria-controls="assigned" aria-selected="true">Assigned
                        Services</button>
                </li>
                @endif
                @if(checkMenu(Session::get('role_id'), 17, 'read'))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="unassigned-tab" data-bs-toggle="tab" data-bs-target="#unassigned"
                        type="button" role="tab" aria-controls="unassigned" aria-selected="false">Unassigned
                        Services</button>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create"
                        type="button" role="tab" aria-controls="create" aria-selected="false">Create Entry</button>
                </li>
                @if(checkMenu(Session::get('role_id'), 18, 'create'))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import"
                        type="button" role="tab" aria-controls="import" aria-selected="false">Import
                        Services</button>
                </li>
                @endif
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="entryTabContent">
                        <div class="tab-pane fade show active" id="assigned" role="tabpanel"
                            aria-labelledby="assigned-tab">
                            @if(checkMenu(Session::get('role_id'), 18, 'read'))
                            @if($showDealershipColumn)
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="assignedDealershipFilter" class="form-label">Filter by Dealership</label>
                                    <select id="assignedDealershipFilter" class="form-select dealership-filter" {{ $userHasDealership ? 'disabled' : '' }}>
                                        <option value="">All Dealerships</option>
                                        @foreach($dealerships as $dealership)
                                        @if($dealership->brand == 1)
                                        <option value="{{ $dealership->id }}" {{ ($userHasDealership && Auth::user()->employee->dealership_id == $dealership->id) ? 'selected' : '' }}>{{ $dealership->name }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="assignedZoneFilter" class="form-label">Filter by Zone</label>
                                    <select id="assignedZoneFilter" class="form-select zone-filter">
                                        <option value="">All Zones</option>
                                        @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="assignedSortBy" class="form-label">Sort By</label>
                                    <select id="assignedSortBy" class="form-select sort-by-filter">
                                        <option value="date" selected>Assigned Date</option>
                                        <option value="customer">Customer Name</option>
                                        <option value="complaint">Complaint Title</option>
                                    </select>
                                </div>
                            </div>
                            @endif
                            <div class="table-responsive">
                                <table class="display" id="assigned-entries-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Complaint Info</th>
                                            <th>Dealership</th>
                                            <th>Customer Name</th>
                                            <th>Product Info</th>
                                            <th>Location</th>
                                            <th>Machine Status</th>
                                            <th>Status</th>
                                            <th>Assigned Engineer</th>
                                            <th>Nature of Complaints</th>
                                            <th>Follow-ups</th>
                                            <th>Assigned Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to view assigned services.
                            </div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="unassigned" role="tabpanel" aria-labelledby="unassigned-tab">
                            @if(checkMenu(Session::get('role_id'), 17, 'read'))
                            @if($showDealershipColumn)
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="unassignedDealershipFilter" class="form-label">Filter by Dealership</label>
                                    <select id="unassignedDealershipFilter" class="form-select dealership-filter" {{ $userHasDealership ? 'disabled' : '' }}>
                                        <option value="">All Dealerships</option>
                                        @foreach($dealerships as $dealership)
                                        <option value="{{ $dealership->id }}" {{ ($userHasDealership && Auth::user()->employee->dealership_id == $dealership->id) ? 'selected' : '' }}>{{ $dealership->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="unassignedZoneFilter" class="form-label">Filter by Zone</label>
                                    <select id="unassignedZoneFilter" class="form-select zone-filter">
                                        <option value="">All Zones</option>
                                        @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="unassignedSortBy" class="form-label">Sort By</label>
                                    <select id="unassignedSortBy" class="form-select sort-by-filter">
                                        <option value="date" selected>Created Date</option>
                                        <option value="customer">Customer Name</option>
                                        <option value="complaint">Complaint Title</option>
                                    </select>
                                </div>
                            </div>
                            @endif
                            <div class="table-responsive">
                                <table class="display" id="unassigned-entries-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Complaint Info</th>
                                            <th>Dealership</th>
                                            <th>Customer Name</th>
                                            <th>Product Info</th>
                                            <th>Location</th>
                                            <th>Machine Status</th>
                                            <th>Status</th>
                                            <th>Assigned Engineer</th>
                                            <th>Nature of Complaints</th>
                                            <th>Follow-ups</th>
                                            <th>Created Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to view unassigned services.
                            </div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                            <form id="createEntryForm" class="theme-form" action="{{ route('entries.store') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="createDealership" class="form-label">Dealership <span class="text-danger">*</span></label>
                                        <select class="form-select" id="createDealership" name="dealership_id" required {{ $userHasDealership ? 'disabled' : '' }}>
                                            <option value="">Select Dealership</option>
                                            @foreach($dealerships as $dealership)
                                            <option value="{{ $dealership->id }}" {{ ($userHasDealership && Auth::user()->employee && Auth::user()->employee->dealership_id == $dealership->id) ? 'selected' : '' }}>{{ $dealership->name }}</option>
                                            @endforeach
                                        </select>
                                        @if($userHasDealership && Auth::user()->employee)
                                        <input type="hidden" name="dealership_id" value="{{ Auth::user()->employee->dealership_id }}">
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createZone" class="form-label">Zone</label>
                                        <select class="form-select" id="createZone" name="zone_id">
                                            <option value="">Select Zone</option>
                                            @foreach($zones as $zone)
                                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createClient" class="form-label">Search Customer by Name or Phone <span class="text-danger">*</span></label>
                                        <select class="form-control" id="createClient" name="client_id" required>
                                            {{-- Clients will be loaded dynamically via AJAX --}}
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3" id="clientHistorySection" style="display: none;">
                                        <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e9ecef !important; background-color: #fcfcfc;">
                                            <div class="card-body p-3">
                                                <div id="clientHistoryLoader" class="text-center py-2">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                </div>

                                                <div id="clientHistorySummary" style="display: none;">
                                                    <div class="row g-3 text-center align-items-center">
                                                        <div class="col-3">
                                                            <div class="fw-bold h4 mb-0 text-primary" id="hist_leads">0</div>
                                                            <div class="text-muted" style="font-size: 11px; font-weight: 600;">LEADS</div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="fw-bold h4 mb-0 text-success" id="hist_services">0</div>
                                                            <div class="text-muted" style="font-size: 11px; font-weight: 600;">SERVICES</div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="fw-bold h4 mb-0 text-warning" id="hist_products">0</div>
                                                            <div class="text-muted" style="font-size: 11px; font-weight: 600;">PRODUCTS</div>
                                                        </div>
                                                        <div class="col-3 border-start">
                                                            <a id="viewClientProfileBtn" href="#" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill border-0 fw-bold">
                                                                <i class="fa fa-user me-1"></i> Profile
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createProduct" class="form-label">Product <span class="text-danger">*</span></label>
                                        <select class="form-select product-select2" id="createProduct" name="product_id" required>
                                            <option value="">Select Product</option>
                                            {{-- Products will be loaded dynamically --}}
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createProductModel" class="form-label">Product Model</label>
                                        <select class="form-select product-select2" id="createProductModel" name="product_model_id">
                                            <option value="">Select Product Model</option>
                                            {{-- Product Models will be loaded dynamically --}}
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createModelSeries" class="form-label">Product Serial Number</label>
                                        <select class="form-select product-select2" id="createModelSeries" name="model_series_id">
                                            <option value="">Select Serial Number</option>
                                            {{-- Model Series will be loaded dynamically --}}
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createMachineStatus" class="form-label">Machine Status</label>
                                        <select class="form-select" id="createMachineStatus" name="machine_status">
                                            <option value="">Select Machine Status</option>
                                            <option value="warranty">Warranty</option>
                                            <option value="extended_warranty">Extended Warranty</option>
                                            <option value="post_warranty">Post Warranty</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createTypeOfService" class="form-label">Type of Service</label>
                                        <select class="form-select" id="createTypeOfService" name="type_of_service">
                                            <option value="">Select Type of Service</option>
                                            <option value="warranty_free_service">Warranty Free Service</option>
                                            <option value="warranty_claimable">Warranty Claimable</option>
                                            <option value="warranty_mandatory">Warranty Mandatory</option>
                                            <option value="amc">AMC</option>
                                            <option value="paid_service">Paid Service</option>
                                            <option value="goodwill">Goodwill</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createContactInfo" class="form-label">Contact Info</label>
                                        <input type="text" class="form-control" id="createContactInfo"
                                            name="contact_info">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createContactPerson" class="form-label">Contact Person</label>
                                        <input type="text" class="form-control" id="createContactPerson" name="contact_person">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createEngineModel" class="form-label">Engine Model</label>
                                        <input type="text" class="form-control" id="createEngineModel" name="engine_model">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createDOC" class="form-label">DOC (Date of Commissioning)</label>
                                        <input type="text" class="form-control datepicker" id="createDOC" name="doc">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createFailureDate" class="form-label">Failure Date</label>
                                        <input type="text" class="form-control datepicker" id="createFailureDate" name="failure_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createFailureHMR" class="form-label">Failure HMR</label>
                                        <input type="number" class="form-control" id="createFailureHMR" name="failure_hmr" min="0" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createDueDate1" class="form-label">Due Date 1</label>
                                        <input type="date" class="form-control" id="createDueDate1" name="due_date_1">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createDueDate2" class="form-label">Due Date 2</label>
                                        <input type="date" class="form-control" id="createDueDate2" name="due_date_2">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createPrice" class="form-label">Revenue</label>
                                        <input type="number" class="form-control" id="createPrice"
                                            name="price" min="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createCallStatus" class="form-label">Call Status</label>
                                        <select class="form-select" id="createCallStatus" name="call_status">
                                            <option value="opened">Opened</option>
                                            <option value="closed">Closed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createName" class="form-label">Complaint Title</label>
                                        <input type="text" class="form-control" id="createName" name="name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createCallRemarks" class="form-label">Call Remarks</label>
                                        <textarea class="form-control" id="createCallRemarks" name="call_remarks" rows="1"></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="createDescription" class="form-label">Nature of Complaints</label>
                                        <textarea class="form-control" id="createDescription" name="description" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createMapLocation" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="createMapLocation"
                                            name="requested_location">
                                        <div class="invalid-feedback" id="createMapLocation_error"></div>
                                        <input type="hidden" name="latitude" id="createLatitude">
                                        <input type="hidden" name="longitude" id="createLongitude">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div id="createEntryMap" style="height: 400px; width: 100%;"></div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Entry</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
                            @if(!checkMenu(Session::get('role_id'), 18, 'create'))
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to import.
                            </div>
                            @else
                            <p class="mt-3">Download a sample Excel template: <a href="{{ route('services.import.template') }}" class="btn btn-sm btn-outline-primary">Download Template</a></p>
                            <form id="importServiceForm" method="POST" enctype="multipart/form-data" class="theme-form">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Import Services</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label for="excel_file" class="form-label">Select Excel File</label>
                                                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx, .xls" required>

                                                        <div id="import-status"></div>
                                                        <div id="import-errors" class="text-danger"></div>
                                                        <div id="import-results" class="mt-3"></div>
                                                        <button type="button" id="closeImportResults" class="btn btn-sm btn-outline-secondary mt-2" style="display: none;">Close Results</button>
                                                        <div class="progress mt-3" style="display: none;">
                                                            <div id="import-progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="submit" id="importServiceButton" class="btn btn-primary">Import Services</button>
                                        <span id="import-spinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                    </div>
                                </div>
                            </form>

                            <div id="recent-imports" class="mt-5">
                                <h5>Recent Imports</h5>
                                <table id="recentImportsTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Import Date</th>
                                            <th>Summary</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-imports-body">
                                        {{-- Recent imports will be loaded here --}}
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Entry Modal (Placeholder) -->
<div class="modal fade" id="editEntryModal" tabindex="-1" aria-labelledby="editEntryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEntryModalLabel">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEntryForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editEntryId" name="id">
                <input type="hidden" id="editServiceEngineerId" name="service_engineer_id">
                <input type="hidden" id="editServiceEngineerId2" name="service_engineer_id_2">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="editDealership" class="form-label">Dealership <span class="text-danger">*</span></label>
                            <select class="form-select" id="editDealership" name="dealership_id" required {{ $userHasDealership ? 'disabled' : '' }}>
                                <option value="">Select Dealership</option>
                                @foreach($dealerships as $dealership)
                                <option value="{{ $dealership->id }}" {{ ($userHasDealership && Auth::user()->employee && Auth::user()->employee->dealership_id == $dealership->id) ? 'selected' : '' }}>{{ $dealership->name }}</option>
                                @endforeach
                            </select>
                            @if($userHasDealership && Auth::user()->employee)
                            <input type="hidden" name="dealership_id" id="editDealershipHidden" value="{{ Auth::user()->employee->dealership_id }}">
                            @endif
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editZone" class="form-label">Zone</label>
                            <select class="form-select" id="editZone" name="zone_id">
                                <option value="">Select Zone</option>
                                @foreach($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editClient" class="form-label">Search Customer by Name or Phone <span class="text-danger">*</span></label>
                            <select class="form-control" id="editClient" name="client_id" required>
                                <option value="">Select Customer</option>
                                {{-- Clients will be loaded dynamically --}}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProduct" class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select" id="editProduct" name="product_id" required>
                                <option value="">Select Product</option>
                                {{-- Products will be loaded dynamically --}}
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editProductModel" class="form-label">Product Model</label>
                            <select class="form-select" id="editProductModel" name="product_model_id">
                                <option value="">Select Product Model</option>
                                {{-- Product Models will be loaded dynamically --}}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editModelSeries" class="form-label">Product Serial Number</label>
                            <select class="form-select" id="editModelSeries" name="model_series_id">
                                <option value="">Select Serial Number</option>
                                {{-- Model Series will be loaded dynamically --}}
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editMachineStatus" class="form-label">Machine Status</label>
                            <select class="form-select" id="editMachineStatus" name="machine_status">
                                <option value="">Select Machine Status</option>
                                <option value="warranty">Warranty</option>
                                <option value="extended_warranty">Extended Warranty</option>
                                <option value="post_warranty">Post Warranty</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editTypeOfService" class="form-label">Type of Service</label>
                            <select class="form-select" id="editTypeOfService" name="type_of_service">
                                <option value="">Select Type of Service</option>
                                <option value="warranty_free_service">Warranty Free Service</option>
                                <option value="warranty_claimable">Warranty Claimable</option>
                                <option value="warranty_mandatory">Warranty Mandatory</option>
                                <option value="amc">AMC</option>
                                <option value="paid_service">Paid Service</option>
                                <option value="goodwill">Goodwill</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editContactInfo" class="form-label">Contact Info</label>
                            <input type="text" class="form-control" id="editContactInfo" name="contact_info">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editContactPerson" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="editContactPerson" name="contact_person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editEngineModel" class="form-label">Engine Model</label>
                            <input type="text" class="form-control" id="editEngineModel" name="engine_model">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDOC" class="form-label">DOC (Date of Commissioning)</label>
                            <input type="text" class="form-control datepicker" id="editDOC" name="doc">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editFailureDate" class="form-label">Failure Date</label>
                            <input type="text" class="form-control datepicker" id="editFailureDate" name="failure_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editFailureHMR" class="form-label">Failure HMR</label>
                            <input type="number" class="form-control" id="editFailureHMR" name="failure_hmr" min="0" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDueDate1" class="form-label">Due Date 1</label>
                            <input type="date" class="form-control" id="editDueDate1" name="due_date_1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDueDate2" class="form-label">Due Date 2</label>
                            <input type="date" class="form-control" id="editDueDate2" name="due_date_2">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editPrice" class="form-label">Revenue</label>
                            <input type="number" class="form-control" id="editPrice" name="price" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCallStatus" class="form-label">Call Status</label>
                            <select class="form-select" id="editCallStatus" name="call_status">
                                <option value="opened">Opened</option>
                                <option value="closed">Closed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editName" class="form-label">Complaint Title</label>
                            <input type="text" class="form-control" id="editName" name="name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCallRemarks" class="form-label">Call Remarks</label>
                            <textarea class="form-control" id="editCallRemarks" name="call_remarks" rows="1"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editRequestedLocation" class="form-label">Location</label>
                            <input type="text" class="form-control" id="editRequestedLocation" name="requested_location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editReferralId" class="form-label">Referral ID</label>
                            <input type="text" class="form-control" id="editReferralId" name="referral_id" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="editDescription" class="form-label">Nature of Complaints</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Entry Modal (Placeholder) -->
<div class="modal fade" id="deleteEntryModal" tabindex="-1" role="dialog" aria-labelledby="deleteEntryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEntryModalLabel">Delete Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the complaint titled <strong id="deleteEntryName"></strong>?</p>
                <input type="hidden" id="deleteEntryId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEntry">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- View Entry Modal -->
<div class="modal fade" id="viewEntryModal" tabindex="-1" aria-labelledby="viewEntryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEntryModalLabel">View Service Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills nav-primary mb-4" id="view-pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="view-pills-general-tab" data-bs-toggle="pill" href="#view-pills-general" role="tab" aria-selected="true">General Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="view-pills-technical-tab" data-bs-toggle="pill" href="#view-pills-technical" role="tab" aria-selected="false">Technical Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="view-pills-assignment-tab" data-bs-toggle="pill" href="#view-pills-assignment" role="tab" aria-selected="false">Assignment & Revenue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="view-pills-history-tab" data-bs-toggle="pill" href="#view-pills-history" role="tab" aria-selected="false">Notes & Timestamps</a>
                    </li>
                </ul>

                <div class="tab-content" id="view-pills-tabContent">
                    <!-- General Info Tab -->
                    <div class="tab-pane fade show active" id="view-pills-general" role="tabpanel">
                        <div class="simple-view-row">
                            <div class="simple-view-label">Complaint Title</div>
                            <div class="simple-view-value fw-bold" id="viewEntryName"></div>
                        </div>
                        <div class="row simple-view-row">
                            <div class="col-md-3">
                                <div class="simple-view-label">Referral ID</div>
                                <div class="simple-view-value" id="viewReferralId"></div>
                            </div>
                            <div class="col-md-3">
                                <div class="simple-view-label">Zone</div>
                                <div class="simple-view-value" id="viewZone"></div>
                            </div>
                            <div class="col-md-3">
                                <div class="simple-view-label">Machine Status</div>
                                <div class="simple-view-value" id="viewMachineStatus"></div>
                            </div>
                            <div class="col-md-3">
                                <div class="simple-view-label">Type of Service</div>
                                <div class="simple-view-value" id="viewTypeOfService"></div>
                            </div>
                        </div>
                        <div class="row simple-view-row">
                            <div class="col-md-6">
                                <div class="simple-view-label">Customer Name</div>
                                <div class="simple-view-value" id="viewClientName"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="simple-view-label">Contact Person</div>
                                <div class="simple-view-value" id="viewContactPerson"></div>
                            </div>
                        </div>
                        <div class="row simple-view-row border-bottom-0">
                            <div class="col-md-6">
                                <div class="simple-view-label">Contact Info</div>
                                <div class="simple-view-value" id="viewContactInfo"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="simple-view-label">Location</div>
                                <div class="simple-view-value" id="viewRequestedLocation"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Details Tab -->
                    <div class="tab-pane fade" id="view-pills-technical" role="tabpanel">
                        <div class="row simple-view-row">
                            <div class="col-md-6">
                                <div class="simple-view-label">Product</div>
                                <div class="simple-view-value" id="viewProductName"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="simple-view-label">Product Model</div>
                                <div class="simple-view-value" id="viewProductModelName"></div>
                            </div>
                        </div>
                        <div class="row simple-view-row">
                            <div class="col-md-6">
                                <div class="simple-view-label">Product Serial Number</div>
                                <div class="simple-view-value" id="viewModelSeriesName"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="simple-view-label">Engine Model</div>
                                <div class="simple-view-value" id="viewEngineModel"></div>
                            </div>
                        </div>
                        <div class="row simple-view-row border-bottom-0">
                            <div class="col-md-6">
                                <div class="simple-view-label">DOC</div>
                                <div class="simple-view-value" id="viewDOC"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Tab -->
                    <div class="tab-pane fade" id="view-pills-assignment" role="tabpanel">
                        <div class="row simple-view-row">
                            <div class="col-md-6">
                                <div class="simple-view-label">Service Engineer 1</div>
                                <div class="simple-view-value" id="viewServiceEngineerName"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="simple-view-label">Due Date 1</div>
                                <div class="simple-view-value" id="viewDueDate1"></div>
                            </div>
                        </div>
                        <div class="row simple-view-row">
                            <div class="col-md-6">
                                <div class="simple-view-label">Service Engineer 2</div>
                                <div class="simple-view-value" id="viewServiceEngineerName2"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="simple-view-label">Due Date 2</div>
                                <div class="simple-view-value" id="viewDueDate2"></div>
                            </div>
                        </div>
                        <div class="row simple-view-row border-bottom-0">
                            <div class="col-md-4">
                                <div class="simple-view-label">Task Status</div>
                                <div class="simple-view-value" id="viewStatus"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="simple-view-label">Call Status</div>
                                <div class="simple-view-value" id="viewCallStatus"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="simple-view-label">Revenue</div>
                                <div class="simple-view-value" id="viewPrice"></div>
                            </div>
                        </div>
                    </div>

                    <!-- History & Notes Tab -->
                    <div class="tab-pane fade" id="view-pills-history" role="tabpanel">
                        <div class="simple-view-row">
                            <div class="simple-view-label">Nature of Complaints</div>
                            <div class="simple-view-value" id="viewDescription"></div>
                        </div>
                        <div class="simple-view-row">
                            <div class="simple-view-label">Call Remarks</div>
                            <div class="simple-view-value" id="viewCallRemarks"></div>
                        </div>
                        <div class="row simple-view-row border-bottom-0">
                            <div class="col-md-6">
                                <div class="simple-view-label">Created At</div>
                                <div class="simple-view-value" id="viewCreatedAt"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="simple-view-label">Last Modified</div>
                                <div class="simple-view-value" id="viewUpdatedAt"></div>
                            </div>
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

<!-- Assign Engineer Modal -->
<div class="modal fade" id="assignEngineerModal" tabindex="-1" aria-labelledby="assignEngineerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignEngineerModalLabel">Edit Assign Engineers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignEngineerForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="assignEntryId" name="entry_id">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="assignDealershipFilter" class="form-label">Filter by Dealership</label>
                            <select class="form-select" id="assignDealershipFilter" {{ $userHasDealership ? 'disabled' : '' }}>
                                <option value="all">All Dealerships</option>
                                {{-- Dealerships will be loaded dynamically --}}
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="assignServiceEngineer" class="form-label">Service Engineer 1</label>
                            <select class="form-select" id="assignServiceEngineer" name="service_engineer_id">
                                <option value="">Select Service Engineer</option>
                                {{-- Service Engineers will be loaded dynamically --}}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assignDueDate1" class="form-label">Due Date 1</label>
                            <input type="date" class="form-control" id="assignDueDate1" name="due_date_1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="assignServiceEngineer2" class="form-label">Service Engineer 2</label>
                            <select class="form-select" id="assignServiceEngineer2" name="service_engineer_id_2">
                                <option value="">Select Service Engineer</option>
                                {{-- Service Engineers will be loaded dynamically --}}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assignDueDate2" class="form-label">Due Date 2</label>
                            <input type="date" class="form-control" id="assignDueDate2" name="due_date_2">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Service Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="mapModalLocationText" class="mb-2 fw-bold"></p>
                <div id="mapModalLoader" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading map...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading map and resolving location...</p>
                </div>
                <div id="serviceLocationMap" style="height: 450px; width: 100%; border-radius: 8px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Undo Import Confirmation Modal -->
<div class="modal fade" id="undoImportModal" tabindex="-1" role="dialog" aria-labelledby="undoImportModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="undoImportModalLabel">Confirm Undo Import</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to undo this import? This will delete all services from this batch.
                <input type="hidden" id="undoImportId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmUndoImport">Undo Import</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        var currentUserRole = "{{ $userRole }}";
        var showDealershipColumn = {{ $showDealershipColumn ? 'true' : 'false' }};
        var userDealershipId = "{{ Auth::user()->employee->dealership_id ?? 'all' }}";
        var assignedEntriesTable;
        var unassignedEntriesTable;
        var isFiltering = false;

        $(document).on('change', '.dealership-filter', function() {
            var $this = $(this);
            $this.addClass('loading-field');
            $('.loader-wrapper').fadeIn('fast');
            if ($this.attr('id') === 'assignedDealershipFilter') {
                assignedEntriesTable.ajax.reload(function() {
                    $this.removeClass('loading-field');
                    $('.loader-wrapper').fadeOut('slow');
                });
            } else {
                unassignedEntriesTable.ajax.reload(function() {
                    $this.removeClass('loading-field');
                    $('.loader-wrapper').fadeOut('slow');
                });
            }
        });

        $(document).on('change', '.sort-by-filter', function() {
            var $this = $(this);
            $this.addClass('loading-field');
            $('.loader-wrapper').fadeIn('fast');
            if ($this.attr('id') === 'assignedSortBy') {
                assignedEntriesTable.ajax.reload(function() {
                    $this.removeClass('loading-field');
                    $('.loader-wrapper').fadeOut('slow');
                });
            } else {
                unassignedEntriesTable.ajax.reload(function() {
                    $this.removeClass('loading-field');
                    $('.loader-wrapper').fadeOut('slow');
                });
            }
        });

        $(document).on('change', '.zone-filter', function() {
            var $this = $(this);
            $this.addClass('loading-field');
            $('.loader-wrapper').fadeIn('fast');
            if ($this.attr('id') === 'assignedZoneFilter') {
                assignedEntriesTable.ajax.reload(function() {
                    $this.removeClass('loading-field');
                    $('.loader-wrapper').fadeOut('slow');
                });
            } else {
                unassignedEntriesTable.ajax.reload(function() {
                    $this.removeClass('loading-field');
                    $('.loader-wrapper').fadeOut('slow');
                });
            }
        });

        // Initialize Select2 for Service Engineers
        $('#assignServiceEngineer, #assignServiceEngineer2').select2({
            dropdownParent: $('#assignEngineerModal'),
            width: '100%',
            placeholder: 'Select Service Engineer',
            allowClear: true
        });

        function formatSelect2Item(item) {
            if (!item.id) return item.text;

            var isOwned = $(item.element).data('owned');
            if (isOwned) {
                return $('<div style="background-color: #d1e7dd; color: #0f5132; font-weight: bold; padding: 8px 12px; margin: -6px -12px; border-left: 4px solid #198754;"><i class="fa fa-check-circle"></i> ' + item.text + ' (Owned)</div>');
            }
            return item.text;
        }

        $('.product-select2').select2({
            width: '100%',
            templateResult: formatSelect2Item,
            templateSelection: formatSelect2Item
        });

        function handleExport(status, format, node) {
            var $btn = $(node);
            var originalHtml = $btn.html();
            var icon = format === 'xlsx' ? 'excel-o' : (format === 'csv' ? 'text-o' : 'pdf-o');
            var btnText = format.toUpperCase();

            $btn.html('<i class="fa fa-spinner fa-spin"></i> ' + btnText).addClass('disabled');

            var dealershipId = status === 'assigned' ? $('#assignedDealershipFilter').val() : $('#unassignedDealershipFilter').val();
            var zoneId = status === 'assigned' ? $('#assignedZoneFilter').val() : $('#unassignedZoneFilter').val();
            var sortBy = status === 'assigned' ? $('#assignedSortBy').val() : $('#unassignedSortBy').val();

            var url = "{{ route('entries.export') }}?assignment_status=" + status + "&format=" + format;
            if (dealershipId) url += "&dealership_id=" + dealershipId;
            if (zoneId) url += "&zone_id=" + zoneId;
            if (sortBy) url += "&sort_by=" + sortBy;

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Export failed');

                    // Try to get filename from Content-Disposition header
                    var filename = "Services_" + status + "_" + new Date().toISOString().split('T')[0] + "." + (format === 'xlsx' ? 'xlsx' : format);
                    var disposition = response.headers.get('Content-Disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        var matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1].replace(/['"]/g, '');
                        }
                    }

                    return response.blob().then(blob => ({
                        blob,
                        filename
                    }));
                })
                .then(({
                    blob,
                    filename
                }) => {
                    var blobUrl = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(blobUrl);
                    $btn.html(originalHtml).removeClass('disabled');
                })
                .catch(error => {
                    console.error('Export error:', error);
                    showToast('Export failed. Please try again.', 'danger');
                    $btn.html(originalHtml).removeClass('disabled');
                });
        }

        function initializeDataTables() {
            assignedEntriesTable = $('#assigned-entries-table').DataTable({
                processing: true,
                serverSide: true,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                        text: '<i class="fa fa-file-excel-o"></i> Excel',
                        className: 'btn btn-sm btn-success text-white',
                        action: function(e, dt, node, config) {
                            handleExport('assigned', 'xlsx', node);
                        }
                    },
                    {
                        text: '<i class="fa fa-file-text"></i> CSV',
                        className: 'btn btn-sm btn-primary text-white',
                        action: function(e, dt, node, config) {
                            handleExport('assigned', 'csv', node);
                        }
                    },
                    {
                        text: '<i class="fa fa-file-pdf-o"></i> PDF',
                        className: 'btn btn-sm btn-danger text-white',
                        action: function(e, dt, node, config) {
                            handleExport('assigned', 'pdf', node);
                        }
                    },
                    {
                        text: '<i class="fa fa-print"></i> Print',
                        className: 'btn btn-sm btn-info text-white',
                        action: function(e, dt, node, config) {
                            var status = dt.ajax.params().assignment_status;
                            var d_id = status === 'assigned' ? $('#assignedDealershipFilter').val() : $('#unassignedDealershipFilter').val();
                            var z_id = status === 'assigned' ? $('#assignedZoneFilter').val() : $('#unassignedZoneFilter').val();
                            var s_by = status === 'assigned' ? $('#assignedSortBy').val() : $('#unassignedSortBy').val();
                            
                            var printUrl = "{{ route('entries.export') }}?assignment_status=" + status + "&format=pdf&action=stream";
                            if (d_id) printUrl += "&dealership_id=" + d_id;
                            if (z_id) printUrl += "&zone_id=" + z_id;
                            if (s_by) printUrl += "&sort_by=" + s_by;

                            window.open(printUrl, '_blank');
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('entries.datatable') }}",
                    data: function(d) {
                        d.assignment_status = 'assigned';
                        d.sort_by = $('#assignedSortBy').val();
                        if (showDealershipColumn) {
                            d.dealership_id = $('#assignedDealershipFilter').val();
                            d.zone_id = $('#assignedZoneFilter').val();
                        }
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'service_name',
                        name: 'service_name',
                        searchable: true,
                        render: function(data, type, row) {
                            var referralIdDisplay = row.referral_id ?
                                '<br><span class="badge bg-info"> <b>Ref ID: </b>' + row
                                .referral_id + '</span>' : '';
                            return (data || '') + referralIdDisplay;
                        }
                    },
                    {
                        data: 'dealership_name',
                        name: 'dealerships.name',
                        searchable: true,
                        visible: showDealershipColumn
                    },
                    {
                        data: 'client_name',
                        name: 'clients.name',
                        searchable: true
                    },
                    {
                        data: 'product_name',
                        name: 'product_name',
                        searchable: true,
                        render: function(data, type, row) {
                            var productBadge = '<span class="badge bg-primary">' + data +
                                '</span><br>';
                            var productModelBadge = row.product_model_name ?
                                ' <span class="badge bg-secondary mt-1">' + row
                                .product_model_name + '</span><br>' : '';
                            var modelSeriesBadge = row.model_series_name ?
                                ' <span class="badge bg-light text-dark mt-1 border">S/N: ' + row
                                .model_series_name + '</span>' : '';
                            return productBadge + productModelBadge + modelSeriesBadge;
                        }
                    },

                    {
                        data: 'requested_location',
                        name: 'services.requested_location',
                        searchable: true,
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            return '<button class="btn btn-sm btn-outline-primary view-map-btn" data-location="' + data + '" data-lat="' + (row.latitude || '') + '" data-lng="' + (row.longitude || '') + '"><i class="fa fa-map-marker"></i> Maps</button>';
                        }
                    },
                    {
                        data: 'status_and_service',
                        name: 'status_and_service',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'service_engineer_name',
                        name: 'eng1.name',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'description',
                        name: 'services.description',
                        searchable: true,
                        render: function(data, type, row) {
                            if (type === 'display' && data != null && data.length > 50) {
                                var truncated = data.substr(0, 50);
                                var full = data;
                                return '<span class="description-truncated">' + truncated +
                                    '...</span>' +
                                    '<span class="description-full" style="display: none;">' +
                                    full + '</span>' +
                                    '<a href="#" class="read-more-toggle">Read More</a>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'followups',
                        name: 'followups',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'assigned_date',
                        name: 'services.assigned_at',
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }

                ]
            });

            unassignedEntriesTable = $('#unassigned-entries-table').DataTable({
                processing: true,
                serverSide: true,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                        text: '<i class="fa fa-file-excel-o"></i> Excel',
                        className: 'btn btn-sm btn-success text-white',
                        action: function(e, dt, node, config) {
                            handleExport('unassigned', 'xlsx', node);
                        }
                    },
                    {
                        text: '<i class="fa fa-file-text"></i> CSV',
                        className: 'btn btn-sm btn-primary text-white',
                        action: function(e, dt, node, config) {
                            handleExport('unassigned', 'csv', node);
                        }
                    },
                    {
                        text: '<i class="fa fa-file-pdf-o"></i> PDF',
                        className: 'btn btn-sm btn-danger text-white',
                        action: function(e, dt, node, config) {
                            handleExport('unassigned', 'pdf', node);
                        }
                    },
                    {
                        text: '<i class="fa fa-print"></i> Print',
                        className: 'btn btn-sm btn-info text-white',
                        action: function(e, dt, node, config) {
                            var status = dt.ajax.params().assignment_status;
                            var d_id = status === 'assigned' ? $('#assignedDealershipFilter').val() : $('#unassignedDealershipFilter').val();
                            var z_id = status === 'assigned' ? $('#assignedZoneFilter').val() : $('#unassignedZoneFilter').val();
                            var s_by = status === 'assigned' ? $('#assignedSortBy').val() : $('#unassignedSortBy').val();
                            
                            var printUrl = "{{ route('entries.export') }}?assignment_status=" + status + "&format=pdf&action=stream";
                            if (d_id) printUrl += "&dealership_id=" + d_id;
                            if (z_id) printUrl += "&zone_id=" + z_id;
                            if (s_by) printUrl += "&sort_by=" + s_by;

                            window.open(printUrl, '_blank');
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('entries.datatable') }}",
                    data: function(d) {
                        d.assignment_status = 'unassigned';
                        d.sort_by = $('#unassignedSortBy').val();
                        if (showDealershipColumn) {
                            d.dealership_id = $('#unassignedDealershipFilter').val();
                            d.zone_id = $('#unassignedZoneFilter').val();
                        }
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'service_name',
                        name: 'service_name',
                        searchable: true,
                        render: function(data, type, row) {
                            var referralIdDisplay = row.referral_id ?
                                '<br><span class="badge bg-info"> <b>Ref ID: </b>' + row
                                .referral_id + '</span>' : '';
                            return (data || '') + referralIdDisplay;
                        }
                    },
                    {
                        data: 'dealership_name',
                        name: 'dealerships.name',
                        searchable: true,
                        visible: showDealershipColumn
                    },
                    {
                        data: 'client_name',
                        name: 'clients.name',
                        searchable: true
                    },
                    {
                        data: 'product_name',
                        name: 'product_name',
                        searchable: true,
                        render: function(data, type, row) {
                            var productBadge = '<span class="badge bg-primary">' + data +
                                '</span><br>';
                            var productModelBadge = row.product_model_name ?
                                ' <span class="badge bg-secondary mt-1">' + row
                                .product_model_name + '</span><br>' : '';
                            var modelSeriesBadge = row.model_series_name ?
                                ' <span class="badge bg-light text-dark mt-1 border">S/N: ' + row
                                .model_series_name + '</span>' : '';
                            return productBadge + productModelBadge + modelSeriesBadge;
                        }
                    },

                    {
                        data: 'requested_location',
                        name: 'services.requested_location',
                        searchable: true,
                        render: function(data, type, row) {
                            if (!data) return 'N/A';
                            return '<button class="btn btn-sm btn-outline-primary view-map-btn" data-location="' + data + '" data-lat="' + (row.latitude || '') + '" data-lng="' + (row.longitude || '') + '"><i class="fa fa-map-marker"></i> Maps</button>';
                        }
                    },
                    {
                        data: 'status_and_service',
                        name: 'status_and_service',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'service_engineer_name',
                        name: 'service_engineer_name',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'description',
                        name: 'services.description',
                        searchable: true,
                        render: function(data, type, row) {
                            if (type === 'display' && data != null && data.length > 50) {
                                var truncated = data.substr(0, 50);
                                var full = data;
                                return '<span class="description-truncated">' + truncated +
                                    '...</span>' +
                                    '<span class="description-full" style="display: none;">' +
                                    full + '</span>' +
                                    '<a href="#" class="read-more-toggle">Read More</a>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'followups',
                        name: 'followups',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_date',
                        name: 'services.created_at',
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }

                ]
            });
        }

        initializeDataTables(); // Call this function on document ready

        // Handle "View on Map" button click
        $(document).on('click', '.view-map-btn', function() {
            var locationName = $(this).data('location');
            var lat = $(this).data('lat');
            var lng = $(this).data('lng');

            $('#mapModalLocationText').text(locationName);
            $('#serviceLocationMap').hide();
            $('#mapModalLoader').show();
            $('#mapModal').modal('show');

            // Initialize map after modal is shown
            $('#mapModal').on('shown.bs.modal', function() {
                var mapOptions = {
                    zoom: 15,
                    center: {
                        lat: 20.5937,
                        lng: 78.9629
                    }, // Default center
                    mapId: "DEMO_MAP_ID",
                };

                var map = new google.maps.Map(document.getElementById('serviceLocationMap'), mapOptions);
                var geocoder = new google.maps.Geocoder();

                function finishMapLoading() {
                    $('#mapModalLoader').hide();
                    $('#serviceLocationMap').show();
                    google.maps.event.trigger(map, 'resize');
                }

                if (lat && lng) {
                    var position = {
                        lat: parseFloat(lat),
                        lng: parseFloat(lng)
                    };
                    map.setCenter(position);
                    new google.maps.marker.AdvancedMarkerElement({
                        position: position,
                        map: map,
                        title: locationName
                    });
                    finishMapLoading();
                } else {
                    // Try to geocode the location string if lat/lng are missing
                    geocoder.geocode({
                        'address': locationName
                    }, function(results, status) {
                        if (status === 'OK') {
                            map.setCenter(results[0].geometry.location);
                            new google.maps.marker.AdvancedMarkerElement({
                                map: map,
                                position: results[0].geometry.location,
                                title: locationName
                            });
                        } else {
                            showToast('Could not find location on map: ' + status, 'warning');
                        }
                        finishMapLoading();
                    });
                }

                // Unbind the event to prevent multiple initializations
                $(this).off('shown.bs.modal');
            });
        });

        let createEntryMap;
        let createEntryMarker;
        let createEntryGeocoder;
        let createEntryAutocomplete;

        function initCreateEntryMap() {
            const defaultLatLng = {
                lat: 20.5937,
                lng: 78.9629
            }; // Centered on India
            createEntryMap = new google.maps.Map(document.getElementById('createEntryMap'), {
                zoom: 5,
                center: defaultLatLng,
                mapId: "DEMO_MAP_ID",
            });
            createEntryGeocoder = new google.maps.Geocoder();

            createEntryMarker = new google.maps.marker.AdvancedMarkerElement({
                map: createEntryMap,
                position: defaultLatLng,
                gmpDraggable: true,
            });

            const locationInput = document.getElementById('createMapLocation');
            createEntryAutocomplete = new google.maps.places.Autocomplete(locationInput);
            createEntryAutocomplete.bindTo('bounds', createEntryMap);

            createEntryAutocomplete.addListener('place_changed', function() {
                const place = createEntryAutocomplete.getPlace();
                if (!place.geometry) {
                    showToast('No details available for input: ' + place.name, 'warning');
                    return;
                }

                if (place.geometry.viewport) {
                    createEntryMap.fitBounds(place.geometry.viewport);
                } else {
                    createEntryMap.setCenter(place.geometry.location);
                    createEntryMap.setZoom(17);
                }

                createEntryMarker.position = place.geometry.location;
                $('#createMapLocation').val(place.formatted_address);
                $('#createLatitude').val(place.geometry.location.lat());
                $('#createLongitude').val(place.geometry.location.lng());
            });

            createEntryMarker.addListener('gmp-dragend', function() {
                const latlng = createEntryMarker.position;
                createEntryGeocoder.geocode({
                    'location': latlng
                }, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            $('#createMapLocation').val(results[0].formatted_address);
                            $('#createLatitude').val(latlng.lat());
                            $('#createLongitude').val(latlng.lng());
                        }
                    } else {
                        showToast('Reverse geocoding failed: ' + status, 'danger');
                    }
                });
            });

            createEntryMap.addListener('click', function(event) {
                createEntryMarker.position = event.latLng;
                createEntryGeocoder.geocode({
                    'location': event.latLng
                }, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            $('#createMapLocation').val(results[0].formatted_address);
                            $('#createLatitude').val(event.latLng.lat());
                            $('#createLongitude').val(event.latLng.lng());
                        }
                    } else {
                        showToast('Reverse geocoding failed: ' + status, 'danger');
                    }
                });
            });
        }

        // Prevent form submission on Enter key in createMapLocation input
        $('#createMapLocation').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // Load Clients on tab show
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (e.target.id === 'assigned-tab') {
                assignedEntriesTable.ajax.reload();
            } else if (e.target.id === 'unassigned-tab') {
                unassignedEntriesTable.ajax.reload();
            } else if (e.target.id === 'create-tab') {
                // loadClients(); // Don't load all clients for create tab anymore
                // Initialize map for create entry form
                // Initialize map for create entry form
                if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && !
                    createEntryMap) {
                    setTimeout(function() {
                        initCreateEntryMap();
                    }, 100); // Small delay to ensure tab content is rendered
                } else if (createEntryMap) {
                    google.maps.event.trigger(createEntryMap, 'resize');
                    const currentPosition = createEntryMarker ? createEntryMarker.position :
                        new google.maps.LatLng(20.5937, 78.9629);
                    createEntryMap.setCenter(currentPosition);
                }
            }
        });

        function loadClients() {
            $.ajax({
                url: "{{ route('entries.clients') }}", // New route to fetch clients
                method: 'GET',
                success: function(response) {
                    var clientSelect = $('#editClient'); // Only update edit modal client list
                    // var clientSelect = $('#createClient, #editClient'); 
                    clientSelect.empty().append('<option value="">Select Customer</option>');
                    $.each(response.clients, function(index, client) {
                        clientSelect.append('<option value="' + client.id + '">' + client
                            .name + '</option>');
                    });
                },
                error: function(error) {
                    console.error('Error loading clients:', error);
                    showToast('Error loading clients.', 'danger');
                }
            });
        }

        var allServiceEngineers = []; // Global variable to store all engineers

        function fetchDealerships() {
            $.ajax({
                url: "{{ route('entries.dealerships') }}",
                method: 'GET',
                success: function(response) {
                    var dealershipSelect = $('#assignDealershipFilter');
                    dealershipSelect.empty().append('<option value="all">All Dealerships</option>');
                    $.each(response.dealerships, function(index, dealership) {
                        dealershipSelect.append('<option value="' + dealership.id + '">' + dealership.name + '</option>');
                    });

                    // Pre-select if user is tied to a dealership
                    if (userDealershipId !== 'all') {
                        dealershipSelect.val(userDealershipId);
                    }
                },
                error: function(error) {
                    console.error('Error loading dealerships:', error);
                }
            });
        }

        function fetchAndPopulateServiceEngineers(selectedEngineerId1 = null, selectedEngineerId2 = null) {
            var dealershipId = $('#assignDealershipFilter').val() || 'all';

            // Show loading state
            $('#assignServiceEngineer, #assignServiceEngineer2').prop('disabled', true).addClass('select2-loading');

            $.ajax({
                url: "{{ route('entries.service-engineers') }}",
                method: 'GET',
                data: {
                    dealership_id: dealershipId
                },
                success: function(response) {
                    allServiceEngineers = response.service_engineers;
                    populateServiceEngineerDropdowns(selectedEngineerId1, selectedEngineerId2);
                },
                error: function(error) {
                    console.error('Error loading service engineers:', error);
                    showToast('Error loading service engineers.', 'danger');
                    $('#assignServiceEngineer, #assignServiceEngineer2').prop('disabled', false).removeClass('select2-loading');
                }
            });
        }

        // Initialize dealerships on page load
        fetchDealerships();

        // Refetch engineers when dealership filter changes
        $('#assignDealershipFilter').on('change', function() {
            fetchAndPopulateServiceEngineers($('#assignServiceEngineer').val(), $('#assignServiceEngineer2').val());
        });

        function populateServiceEngineerDropdowns(selectedEngineerId1 = null, selectedEngineerId2 = null) {
            var assignServiceEngineer1 = $('#assignServiceEngineer');
            var assignServiceEngineer2 = $('#assignServiceEngineer2');

            // Re-enable the dropdowns and remove loading state
            assignServiceEngineer1.prop('disabled', false).removeClass('select2-loading');
            assignServiceEngineer2.prop('disabled', false).removeClass('select2-loading');

            assignServiceEngineer1.empty().append('<option value="">Select Service Engineer</option>');
            assignServiceEngineer2.empty().append('<option value="">Select Service Engineer</option>');

            $.each(allServiceEngineers, function(index, engineer) {
                assignServiceEngineer1.append('<option value="' + engineer.id + '">' + engineer.name +
                    '</option>');
                // Only add to second dropdown if not already selected in the first
                if (engineer.id != selectedEngineerId1) {
                    assignServiceEngineer2.append('<option value="' + engineer.id + '">' + engineer
                        .name + '</option>');
                }
            });

            // Set selected values after populating
            assignServiceEngineer1.val(selectedEngineerId1 || '');
            assignServiceEngineer2.val(selectedEngineerId2 || '');

            // Now, filter the second dropdown based on the initial selection of the first
            filterServiceEngineerDropdowns();
        }

        function filterServiceEngineerDropdowns() {
            if (isFiltering) return;
            isFiltering = true;

            var selected1 = $('#assignServiceEngineer').val();
            var selected2 = $('#assignServiceEngineer2').val();

            var assignServiceEngineer1 = $('#assignServiceEngineer');
            var assignServiceEngineer2 = $('#assignServiceEngineer2');

            // Re-populate second dropdown based on first selection
            assignServiceEngineer2.empty().append('<option value="">Select Service Engineer</option>');
            $.each(allServiceEngineers, function(index, engineer) {
                if (engineer.id != selected1) {
                    assignServiceEngineer2.append('<option value="' + engineer.id + '">' + engineer
                        .name + '</option>');
                }
            });
            assignServiceEngineer2.val(selected2).trigger('change'); // Restore previous selection if still valid

            // Re-populate first dropdown based on second selection
            assignServiceEngineer1.empty().append('<option value="">Select Service Engineer</option>');
            $.each(allServiceEngineers, function(index, engineer) {
                if (engineer.id != selected2) {
                    assignServiceEngineer1.append('<option value="' + engineer.id + '">' + engineer
                        .name + '</option>');
                }
            });
            assignServiceEngineer1.val(selected1).trigger('change'); // Restore previous selection if still valid

            isFiltering = false;
        }

        function loadModelSeries(productModelId, targetSelectId, selectedModelSeriesId = null, clientId = null) {
            var modelSeriesSelect = $('#' + targetSelectId);
            modelSeriesSelect.addClass('select2-loading').empty().append('<option value="">Select Serial Number</option>');

            if (productModelId) {
                $.ajax({
                    url: "{{ route('entries.model-series') }}",
                    method: 'GET',
                    data: {
                        product_model_id: productModelId,
                        client_id: clientId
                    },
                    success: function(response) {
                        modelSeriesSelect.removeClass('select2-loading');
                        // De-dupe by the underlying serial number, not the full label
                        // (labels may differ only by "(Eng: ...)" suffix).
                        function serialKeyFromLabel(label) {
                            if (!label) return '';
                            var base = String(label).split(/\s*\(eng:/i)[0];
                            base = base.replace(/\s+/g, ' ').trim().toLowerCase();
                            return base;
                        }

                        var uniqueBySerial = new Map(); // key -> series
                        $.each(response.model_series, function(index, series) {
                            var label = series.name || '';
                            var key = serialKeyFromLabel(label) || label.trim().toLowerCase();

                            if (!uniqueBySerial.has(key)) {
                                uniqueBySerial.set(key, series);
                                return;
                            }

                            var existing = uniqueBySerial.get(key);
                            // If we have a selected value (edit flow), always keep it.
                            if (selectedModelSeriesId && String(series.id) === String(selectedModelSeriesId)) {
                                uniqueBySerial.set(key, series);
                                return;
                            }
                            if (selectedModelSeriesId && existing && String(existing.id) === String(selectedModelSeriesId)) {
                                return;
                            }
                            var existingLabel = (existing && existing.name) ? String(existing.name) : '';
                            var existingHasEng = /\(eng:/i.test(existingLabel);
                            var newHasEng = /\(eng:/i.test(label);

                            // Prefer the option that includes engine serial (more informative).
                            if (!existingHasEng && newHasEng) {
                                uniqueBySerial.set(key, series);
                                return;
                            }

                            // If still tied, prefer owned, then manual.
                            if (!!existing.is_owned !== !!series.is_owned) {
                                if (series.is_owned) uniqueBySerial.set(key, series);
                                return;
                            }
                            if (!!existing.is_manual !== !!series.is_manual) {
                                if (series.is_manual) uniqueBySerial.set(key, series);
                                return;
                            }
                        });

                        modelSeriesSelect.empty().append('<option value="">Select Serial Number</option>');
                        uniqueBySerial.forEach(function(series) {
                            var label = series.name || '';
                            var ownedAttr = series.is_owned ? 'data-owned="true"' : '';
                            modelSeriesSelect.append('<option value="' + series.id + '" ' + ownedAttr + '>' +
                                label + '</option>');
                        });
                        if (selectedModelSeriesId) {
                            modelSeriesSelect.val(selectedModelSeriesId);
                            $('#editEntryModal').removeData('model_series_id');
                        }
                        modelSeriesSelect.trigger('change');
                    },
                    error: function(error) {
                        modelSeriesSelect.removeClass('select2-loading');
                        console.error('Error loading model series:', error);
                        showToast('Error loading model series.', 'danger');
                    }
                });
            } else {
                modelSeriesSelect.removeClass('select2-loading');
            }
        }

        // Load Products when client or dealership is selected
        $('#createClient, #createDealership').on('change', function() {
            var clientId = $('#createClient').val();
            var dealershipId = $('#createDealership').val();
            var productSelect = $('#createProduct');
            var productModelSelect = $('#createProductModel');
            var modelSeriesSelect = $('#createModelSeries');

            productSelect.empty().append('<option value="">Select Product</option>');
            productModelSelect.empty().append('<option value="">Select Product Model</option>');
            modelSeriesSelect.empty().append('<option value="">Select Serial Number</option>');

            if (clientId || dealershipId) {
                $.ajax({
                    url: "{{ route('entries.products') }}", // New route to fetch products
                    method: 'GET',
                    data: {
                        client_id: clientId,
                        dealership_id: dealershipId
                    },
                    success: function(response) {
                        var seenLabels = new Set();
                        $.each(response.products, function(index, product) {
                            var categoryInfo = product.category ? ' (' + product.category.name + ')' : '';
                            var label = product.name + categoryInfo;
                            var compareLabel = label.trim().toLowerCase();
                            if (!seenLabels.has(compareLabel)) {
                                var ownedAttr = product.is_owned ? 'data-owned="true"' : '';
                                productSelect.append('<option value="' + product.id + '" ' + ownedAttr + '>' + label + '</option>');
                                seenLabels.add(compareLabel);
                            }
                        });
                        productSelect.trigger('change');
                    },
                    error: function(error) {
                        console.error('Error loading products:', error);
                        showToast('Error loading products.', 'danger');
                    }
                });
            }
        });

        // Load Product Models when product is selected
        $('#createProduct').on('change', function() {
            var productId = $(this).val();
            var clientId = $('#createClient').val();
            var productModelSelect = $('#createProductModel');
            var modelSeriesSelect = $('#createModelSeries');

            productModelSelect.empty().append('<option value="">Select Product Model</option>');
            modelSeriesSelect.empty().append('<option value="">Select Serial Number</option>');

            if (productId) {
                $.ajax({
                    url: "{{ route('entries.product-models') }}", // New route to fetch product models
                    method: 'GET',
                    data: {
                        product_id: productId,
                        client_id: clientId
                    },
                    success: function(response) {
                        var seenLabels = new Set();
                        $.each(response.product_models, function(index, model) {
                            var compareLabel = (model.name || '').trim().toLowerCase();
                            if (!seenLabels.has(compareLabel)) {
                                var ownedAttr = model.is_owned ? 'data-owned="true"' : '';
                                productModelSelect.append('<option value="' + model.id + '" ' + ownedAttr + '>' + model.name + '</option>');
                                seenLabels.add(compareLabel);
                            }
                        });
                        productModelSelect.trigger('change');
                    },
                    error: function(error) {
                        console.error('Error loading product models:', error);
                        showToast('Error loading product models.', 'danger');
                    }
                });
            }
        });

        // Load Model Series when product model is selected
        $('#createProductModel').on('change', function() {
            var productModelId = $(this).val();
            var clientId = $('#createClient').val();
            loadModelSeries(productModelId, 'createModelSeries', null, clientId);
        });

        // Auto-populate asset details when serial number is selected
        $('#createModelSeries').on('change', function() {
            var modelSeriesId = $(this).val();
            var clientId = $('#createClient').val();
            var productId = $('#createProduct').val();
            var productModelId = $('#createProductModel').val();

            if (modelSeriesId && clientId && productId && productModelId) {
                $.ajax({
                    url: "{{ route('entries.product-details') }}",
                    method: 'GET',
                    data: {
                        client_id: clientId,
                        product_id: productId,
                        product_model_id: productModelId,
                        model_series_id: modelSeriesId
                    },
                    success: function(response) {
                        if (response) {
                            if (response.doc) {
                                $('#createDOC').val(response.doc);
                            }
                            if (response.engine_model) {
                                $('#createEngineModel').val(response.engine_model);
                            }
                            if (response.engine_serial_number) {
                                $('#createEngineSerialNumber').val(response.engine_serial_number);
                            }
                        }
                    },
                    error: function(error) {
                        console.error('Error fetching product details:', error);
                    }
                });
            }
        });

        // Type of Service mapping based on Machine Status
        const serviceTypesMapping = {
            "warranty": [{
                    value: "free_service",
                    label: "Free Service"
                },
                {
                    value: "warranty_claimable",
                    label: "Warranty Claimable"
                },
                {
                    value: "warranty_coupon_service",
                    label: "Warranty Coupon Service"
                },
                {
                    value: "campaign",
                    label: "Campaign"
                },
                {
                    value: "paid_service",
                    label: "Paid Service"
                }
            ],
            "extended_warranty": [{
                    value: "free_service",
                    label: "Free Service"
                },
                {
                    value: "coupon_service",
                    label: "Coupon Service"
                },
                {
                    value: "amc",
                    label: "AMC"
                },
                {
                    value: "campaign",
                    label: "Campaign"
                },
                {
                    value: "paid_service",
                    label: "Paid Service"
                }
            ],
            "post_warranty": [{
                    value: "free_service",
                    label: "Free Service"
                },
                {
                    value: "amc",
                    label: "AMC"
                },
                {
                    value: "campaign",
                    label: "Campaign"
                },
                {
                    value: "paid_service",
                    label: "Paid Service"
                }
            ]
        };

        function updateServiceTypes(statusId, serviceTypeId, selectedValue = null) {
            const status = $(`#${statusId}`).val();
            const serviceTypeSelect = $(`#${serviceTypeId}`);
            serviceTypeSelect.empty().append('<option value="">Select Type of Service</option>');

            if (status && serviceTypesMapping[status]) {
                serviceTypesMapping[status].forEach(type => {
                    serviceTypeSelect.append(`<option value="${type.value}">${type.label}</option>`);
                });
                if (selectedValue) {
                    serviceTypeSelect.val(selectedValue);
                }
            }
        }

        $('#createMachineStatus').on('change', function() {
            updateServiceTypes('createMachineStatus', 'createTypeOfService');
        });

        $('#editMachineStatus').on('change', function() {
            updateServiceTypes('editMachineStatus', 'editTypeOfService');
        });

        // Load Products when client or dealership is selected in Edit Modal
        $('#editClient, #editDealership').on('change', function() {
            var data = $('#editClient').select2('data')[0];
            var clientId = $('#editClient').val();
            var dealershipId = $('#editDealership').val();
            var phone = data ? (data.phone_number || data.phone || '') : '';
            var name = data ? (data.name || data.text || '') : '';

            // If the name from Select2 data is the combined "Name (Phone)", try to extract just the name
            if (name && name.includes(' (')) {
                name = name.split(' (')[0];
            }

            var productSelect = $('#editProduct');
            var productModelSelect = $('#editProductModel');
            var modelSeriesSelect = $('#editModelSeries');

            productSelect.addClass('select2-loading').empty().append('<option value="">Select Product</option>').prop('disabled', true);
            productModelSelect.empty().append('<option value="">Select Product Model</option>').prop('disabled', true);
            modelSeriesSelect.empty().append('<option value="">Select Serial Number</option>').prop('disabled', true);

            if ((clientId && clientId !== 'Loading...') || $('#editEntryModal').data('product_id') || dealershipId) {
                // Optionally update contact info if it's currently empty or has the initial value
                if (!$('#editContactInfo').val() || $('#editContactInfo').val() === 'Loading...' || (!$('#editContactInfo').val() && phone)) {
                    $('#editContactInfo').val(phone);
                }
                if (!$('#editContactPerson').val() || $('#editContactPerson').val() === 'Loading...' || (!$('#editContactPerson').val() && name)) {
                    $('#editContactPerson').val(name && name !== 'Select Customer' ? name : '');
                }

                $.ajax({
                    url: "{{ route('entries.products') }}",
                    method: 'GET',
                    data: {
                        client_id: clientId,
                        dealership_id: dealershipId
                    },
                    success: function(response) {
                        productSelect.removeClass('select2-loading').prop('disabled', false); // Re-enable
                        var seenLabels = new Set();
                        $.each(response.products, function(index, product) {
                            var categoryInfo = product.category ? ' (' + product.category.name + ')' : '';
                            var label = product.name + categoryInfo;
                            var compareLabel = label.trim().toLowerCase();
                            if (!seenLabels.has(compareLabel)) {
                                productSelect.append('<option value="' + product.id +
                                    '">' + label + '</option>');
                                seenLabels.add(compareLabel);
                            }
                        });
                        // Set selected product if available from fetched data
                        var storedProductId = $('#editEntryModal').data('product_id');
                        if (storedProductId) {
                            productSelect.val(storedProductId);
                            productSelect.trigger('change'); // Trigger change to load product models
                            $('#editEntryModal').removeData('product_id'); // Clear stored data
                        }
                    },
                    error: function(error) {
                        productSelect.removeClass('select2-loading');
                        console.error('Error loading products for edit:', error);
                        showToast('Error loading products for edit.', 'danger');
                    }
                });
            } else {
                productSelect.removeClass('select2-loading');
            }
        });

        // Load Product Models when product is selected in Edit Modal
        $('#editProduct').on('change', function() {
            var productId = $(this).val();
            var clientId = $('#editClient').val();
            var productModelSelect = $('#editProductModel');
            var modelSeriesSelect = $('#editModelSeries');

            productModelSelect.addClass('select2-loading').empty().append('<option value="">Select Product Model</option>').prop('disabled', true);
            modelSeriesSelect.empty().append('<option value="">Select Serial Number</option>').prop('disabled', true);

            if (productId && productId !== 'Loading...') {
                $.ajax({
                    url: "{{ route('entries.product-models') }}",
                    method: 'GET',
                    data: {
                        product_id: productId,
                        client_id: clientId
                    },
                    success: function(response) {
                        productModelSelect.removeClass('select2-loading').prop('disabled', false); // Re-enable

                        var storedProductModelId = $('#editEntryModal').data('product_model_id');
                        var seenLabels = new Set();
                        $.each(response.product_models, function(index, model) {
                            var compareLabel = (model.name || '').trim().toLowerCase();
                            // Always append if it strictly matches the stored ID, to guarantee it survives deduplication.
                            if (!seenLabels.has(compareLabel) || (storedProductModelId && String(model.id) === String(storedProductModelId))) {
                                productModelSelect.append('<option value="' + model.id + '">' + model.name + '</option>');
                                seenLabels.add(compareLabel);
                            }
                        });
                        // Set selected product model if available from fetched data
                        var storedProductModelId = $('#editEntryModal').data(
                            'product_model_id');
                        if (storedProductModelId) {
                            productModelSelect.val(storedProductModelId);
                            // Only trigger change if a product model was actually set
                            if (productModelSelect.val() == storedProductModelId) {
                                productModelSelect.trigger(
                                    'change'); // Trigger change to load model series
                            }
                            $('#editEntryModal').removeData(
                                'product_model_id'); // Clear stored data
                        }
                    },
                    error: function(error) {
                        productModelSelect.removeClass('select2-loading');
                        console.error('Error loading product models for edit:', error);
                        showToast('Error loading product models for edit.', 'danger');
                    }
                });
            } else {
                productModelSelect.removeClass('select2-loading');
            }
        });

        // Load Model Series when product model is selected in Edit Modal
        $('#editProductModel').on('change', function() {
            var productModelId = $(this).val();
            var clientId = $('#editClient').val();
            var modelSeriesSelect = $('#editModelSeries');
            modelSeriesSelect.addClass('select2-loading').empty().append('<option value="">Select Serial Number</option>').prop('disabled', true);

            if (productModelId && productModelId !== 'Loading...') {
                modelSeriesSelect.prop('disabled', false); // Re-enable if it was disabled
                var storedModelSeriesId = $('#editEntryModal').data('model_series_id');
                loadModelSeries(productModelId, 'editModelSeries', storedModelSeriesId, clientId);
                $('#editEntryModal').removeData('model_series_id'); // Clear after use
            } else {
                modelSeriesSelect.removeClass('select2-loading');
            }
        });

        // Auto-populate asset details when serial number is selected in Edit Modal
        $('#editModelSeries').on('change', function() {
            var modelSeriesId = $(this).val();
            var clientId = $('#editClient').val();
            var productId = $('#editProduct').val();
            var productModelId = $('#editProductModel').val();

            if (modelSeriesId && clientId && productId && productModelId) {
                $('#editDOC, #editEngineModel').addClass('loading-field');
                $.ajax({
                    url: "{{ route('entries.product-details') }}",
                    method: 'GET',
                    data: {
                        client_id: clientId,
                        product_id: productId,
                        product_model_id: productModelId,
                        model_series_id: modelSeriesId
                    },
                    success: function(response) {
                        $('#editDOC, #editEngineModel').removeClass('loading-field');
                        if (response) {
                            if (response.doc) {
                                $('#editDOC').val(response.doc);
                            }
                            if (response.engine_model) {
                                $('#editEngineModel').val(response.engine_model);
                            }
                        }
                    },
                    error: function(error) {
                        $('#editDOC, #editEngineModel').removeClass('loading-field');
                        console.error('Error fetching product details for edit:', error);
                    }
                });
            }
        });



        // Handle Create Entry Form Submission
        $('#createEntryForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous validation feedback
            $('.form-control').removeClass('is-invalid');
            $('.form-select').removeClass('is-invalid');

            // Client-side validation
            var clientId = $('#createClient').val();
            var productId = $('#createProduct').val();
            var name = $('#createName').val();

            var isValid = true;

            if (!clientId) {
                $('#createClient').addClass('is-invalid');
                showToast('Please select a client.', 'danger');
                isValid = false;
            }
            if (!productId) {
                $('#createProduct').addClass('is-invalid');
                showToast('Please select a machine.', 'danger');
                isValid = false;
            }

            if (!isValid) {
                return false; // Stop submission if validation fails
            }

            var formElement = document.getElementById('createEntryForm');
            var formData = new FormData(formElement);

            fetch($(this).attr('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    return response.json().then(data => {
                        if (!response.ok) {
                            if (response.status === 422) {
                                return { errors: data.errors, message: data.message };
                            }
                            throw new Error(data.message || 'Server error');
                        }
                        return data;
                    }).catch(err => {
                        if (err instanceof SyntaxError) {
                            throw new Error('Server returned an unexpected response. Please check the logs.');
                        }
                        throw err;
                    });
                })
                .then(data => {
                    if (data.errors) {
                        displayValidationErrors(data.errors);
                        showToast(data.message || 'Validation error.', 'danger');
                    } else if (data.message) {
                        showToast(data.message, 'success');
                        unassignedEntriesTable.ajax.reload(); // Reload unassigned table
                        $('#createEntryForm')[0].reset();
                        var unassignedTab = new bootstrap.Tab(document.getElementById(
                            'unassigned-tab'));
                        unassignedTab.show(); // Switch to unassigned tab
                    }
                })
                .catch(error => {
                    console.error('Error creating entry:', error);
                    showToast(error.message || 'Error creating entry.', 'danger');
                });
        });

        function displayValidationErrors(errors) {
            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            $.each(errors, function(key, value) {
                // Map database fields to form IDs if they differ
                var fieldId = key;
                if (key === 'client_id') fieldId = 'createClient';
                if (key === 'product_id') fieldId = 'createProduct';
                if (key === 'product_model_id') fieldId = 'createProductModel';
                if (key === 'model_series_id') fieldId = 'createModelSeries';
                if (key === 'name') fieldId = 'createName';
                if (key === 'description') fieldId = 'createDescription';
                if (key === 'requested_location') fieldId = 'createMapLocation';
                if (key === 'machine_status') fieldId = 'createMachineStatus';
                if (key === 'type_of_service') fieldId = 'createTypeOfService';
                if (key === 'contact_info') fieldId = 'createContactInfo';
                if (key === 'contact_person') fieldId = 'createContactPerson';
                if (key === 'doc') fieldId = 'createDOC';
                if (key === 'failure_date') fieldId = 'createFailureDate';
                if (key === 'failure_hmr') fieldId = 'createFailureHMR';
                if (key === 'price') fieldId = 'createPrice';
                if (key === 'due_date_1') fieldId = 'createDueDate1';
                if (key === 'due_date_2') fieldId = 'createDueDate2';
                if (key === 'call_status') fieldId = 'createCallStatus';
                if (key === 'call_remarks') fieldId = 'createCallRemarks';
                if (key === 'zone_id') fieldId = 'createZone';

                var $field = $('#' + fieldId);
                $field.addClass('is-invalid');
                
                // If it's a Select2 field, we might need to add the class to the container
                if ($field.hasClass('select2-hidden-accessible')) {
                    $field.next('.select2-container').find('.select2-selection').addClass('is-invalid');
                }

                $('#' + fieldId + '_error').text(value[0]);
            });
        }

        // Handle Edit Button Click (for DataTable)
        $('#assigned-entries-table, #unassigned-entries-table').on('click', '.edit-entry-btn', function() {
            var entryId = $(this).data('id');

            // Show modal immediately
            $('#editEntryModal').modal('show');

            // Reset form and show loading state
            var $form = $('#editEntryForm');
            $form[0].reset();
            $form.find('input, textarea').addClass('loading-field');
            // Only target dynamic selects for the 'Loading...' placeholder to avoid destroying static options
            $form.find('select:not(#editMachineStatus):not(#editTypeOfService):not(#editCallStatus):not(#editDealership):not(#editZone)').addClass('select2-loading').empty().append('<option>Loading...</option>').prop('disabled', true);
            $form.find('#editMachineStatus, #editTypeOfService, #editCallStatus, #editZone').prop('disabled', true);

            $.ajax({
                url: '/entries/' + entryId + '/edit',
                method: 'GET',
                success: function(data) {
                    // Remove loading states from text/input fields
                    $form.find('input, textarea').removeClass('loading-field');
                    $form.find('.select2-loading').removeClass('select2-loading');

                    $('#editEntryId').val(data.id);
                    $('#editName').val(data.name);
                    $('#editDealership').val(data.dealership_id);
                    $('#editZone').val(data.zone_id).prop('disabled', false);
                    $('#editDescription').val(data.description);
                    $('#editRequestedLocation').val(data.requested_location);
                    $('#editReferralId').val(data.referral_id);
                    $('#editMachineStatus').val(data.machine_status).prop('disabled', false);
                    updateServiceTypes('editMachineStatus', 'editTypeOfService', data.type_of_service);
                    $('#editTypeOfService').prop('disabled', false);
                    $('#editContactInfo').val(data.contact_info || (data.client ? data.client.phone_number : ''));
                    $('#editContactPerson').val(data.contact_person || (data.client ? data.client.name : ''));
                    $('#editMachineSerialNumber').val(data.machine_serial_number);
                    $('#editEngineModel').val(data.engine_model);
                    $('#editDOC').val(data.doc);
                    $('#editDueDate1').val(data.due_date_1);
                    $('#editDueDate2').val(data.due_date_2);
                    $('#editFailureDate').val(data.failure_date);
                    $('#editFailureHMR').val(data.failure_hmr);
                    $('#editPrice').val(data.price);
                    $('#editCallStatus').val(data.call_status || 'opened').prop('disabled', false);
                    $('#editCallRemarks').val(data.call_remarks);
                    $('#editServiceEngineerId').val(data.service_engineer_id);
                    $('#editServiceEngineerId2').val(data.service_engineer_id_2);

                    // Populate client Select2 using already loaded data
                    var clientSelect = $('#editClient');
                    clientSelect.empty().append('<option value="">Select Customer</option>');

                    if (data.client) {
                        var clientText = data.client.name + ' (' + (data.client.phone_number || '') + ')';
                        var newOption = new Option(clientText, data.client.id, true, true);
                        // Add data to the option element so Select2 and the change listener can find it
                        $(newOption).data('phone_number', data.client.phone_number || '');
                        $(newOption).data('name', data.client.name || '');
                        clientSelect.append(newOption);
                    }

                    // Store IDs and trigger change chain
                    $('#editEntryModal').data('product_id', data.product_id);
                    $('#editEntryModal').data('product_model_id', data.product_model_id);
                    $('#editEntryModal').data('model_series_id', data.model_series_id);

                    clientSelect.prop('disabled', false).trigger('change');
                },
                error: function(error) {
                    $form.find('input, textarea').removeClass('loading-field');
                    $form.find('.select2-loading').removeClass('select2-loading');
                    console.error('Error fetching entry data:', error);
                    showToast('Error fetching entry data.', 'danger');
                    $('#editEntryModal').modal('hide');
                }
            });
        });

        // Handle Edit Entry Form Submission
        $('#editEntryForm').on('submit', function(e) {
            e.preventDefault();
            var entryId = $('#editEntryId').val();

            if (!entryId) {
                showToast('Entry ID missing.', 'danger');
                return;
            }

            var formData = $(this).serialize();

            $.ajax({
                url: window.baseUrl + '/entries/' + entryId,
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    assignedEntriesTable.ajax.reload(null, false); // Reload assigned table, keep pagination
                    unassignedEntriesTable.ajax.reload(null, false); // Reload unassigned table
                    $('#editEntryModal').modal('hide');
                },
                error: function(xhr) {
                    console.error('Error updating entry:', xhr);
                    var message = 'Error updating entry.';
                    if (xhr.status === 419) {
                        message = 'Session expired or CSRF token mismatch. Please refresh the page.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast(message, 'danger');
                }
            });
        });

        // Handle Delete Button Click (for DataTable)
        $('#assigned-entries-table, #unassigned-entries-table').on('click', '.delete-entry-btn', function() {
            var entryId = $(this).data('id');
            var entryName = $(this).data('name');
            $('#deleteEntryId').val(entryId);
            $('#deleteEntryName').text(entryName);
            $('#deleteEntryModal').modal('show');
        });

        // Handle Delete Confirmation
        $('#confirmDeleteEntry').on('click', function() {
            var entryId = $('#deleteEntryId').val();
            $.ajax({
                url: window.baseUrl + '/entries/' + entryId,
                method: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    assignedEntriesTable.ajax.reload(); // Reload assigned table
                    unassignedEntriesTable.ajax.reload(); // Reload unassigned table
                    $('#deleteEntryModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting entry:', error);
                    showToast('Error deleting entry.', 'danger');
                }
            });
        });

        // Handle View Button Click (for DataTable)
        $('#assigned-entries-table, #unassigned-entries-table').on('click', '.view-entry-btn', function() {
            var entryId = $(this).data('id');

            // Show modal immediately
            $('#viewEntryModal').modal('show');

            // Show skeleton/loading state
            $('.simple-view-value').html('<div class="skeleton-text skeleton-animate"></div>');

            $.ajax({
                url: '/entries/' + entryId + '/edit',
                method: 'GET',
                success: function(data) {
                    var displayOrNA = function(value) {
                        return value !== null && value !== undefined && value !== '' ? value : 'N/A';
                    };
                    var formatDate = function(value) {
                        return value ? moment(value).format('DD MMM, YYYY') : 'N/A';
                    };
                    var formatDateTime = function(value) {
                        return value ? moment(value).format('DD MMM, YYYY HH:mm') : 'N/A';
                    };
                    var humanize = function(value) {
                        if (!value) return 'N/A';
                        return String(value).replace(/_/g, ' ').replace(/\b\w/g, function(c) {
                            return c.toUpperCase();
                        });
                    };
                    // ... (rest of helper functions) ...
                    var formatPrice = function(value) {
                        if (value === null || value === undefined || value === '') return 'N/A';
                        var num = Number(value);
                        return Number.isFinite(num) ? num.toLocaleString() : value;
                    };

                    var client = data.client || data.Client;
                    var product = data.product || data.Product;
                    var productModel = data.product_model || data.productModel;
                    var modelSeries = data.model_series || data.modelSeries;
                    var serviceEngineer1 = data.service_engineer || data.serviceEngineer;
                    var serviceEngineer2 = data.service_engineer2 || data.service_engineer_2 || data.serviceEngineer2;

                    $('#viewClientName').text(client ? client.name : 'N/A');
                    $('#viewProductName').text(product ? product.name : 'N/A');
                    $('#viewProductModelName').text(productModel ? productModel.name : 'N/A');
                    $('#viewModelSeriesName').text(modelSeries ? modelSeries.name : 'N/A');
                    $('#viewEngineModel').text(displayOrNA(data.engine_model));

                    $('#viewEntryName').text(displayOrNA(data.name));
                    $('#viewReferralId').text(displayOrNA(data.referral_id));
                    $('#viewZone').text(data.zone ? data.zone.name : 'N/A');
                    $('#viewRequestedLocation').text(displayOrNA(data.requested_location));
                    $('#viewMachineStatus').text(humanize(data.machine_status));
                    $('#viewTypeOfService').text(humanize(data.type_of_service));
                    $('#viewStatus').html('<span class="badge bg-info text-white">' + humanize(data.status || 'open') + '</span>');
                    $('#viewServiceEngineerName').text(serviceEngineer1 ? serviceEngineer1.name : 'N/A');
                    $('#viewDueDate1').text(formatDate(data.due_date_1));
                    $('#viewServiceEngineerName2').text(serviceEngineer2 ? serviceEngineer2.name : 'N/A');
                    $('#viewDueDate2').text(formatDate(data.due_date_2));
                    $('#viewContactInfo').text(data.contact_info ? data.contact_info : (client ? client.phone_number : 'N/A'));
                    $('#viewContactPerson').text(data.contact_person ? data.contact_person : (client ? client.name : 'N/A'));
                    $('#viewDOC').text(formatDate(data.doc));
                    $('#viewFailureDate').text(formatDate(data.failure_date));
                    $('#viewFailureHMR').text(displayOrNA(data.failure_hmr));
                    $('#viewPrice').text(formatPrice(data.price));
                    $('#viewCallStatus').html('<span class="badge bg-secondary">' + humanize(data.call_status || 'opened') + '</span>');
                    $('#viewCallRemarks').text(displayOrNA(data.call_remarks));
                    $('#viewDescription').text(displayOrNA(data.description));
                    $('#viewCreatedAt').text(formatDateTime(data.created_at));
                    $('#viewUpdatedAt').text(formatDateTime(data.updated_at));
                },
                error: function(error) {
                    console.error('Error fetching entry data:', error);
                    showToast('Error fetching entry data.', 'danger');
                    $('#viewEntryModal').modal('hide');
                }
            });
        });

        // Handle Read More/Read Less toggle
        $('#assigned-entries-table, #unassigned-entries-table').on('click', '.read-more-toggle', function(e) {
            e.preventDefault();
            var $this = $(this);
            var $truncated = $this.siblings('.description-truncated');
            var $full = $this.siblings('.description-full');

            if ($truncated.is(':visible')) {
                $truncated.hide();
                $full.show();
                $this.text('Read Less');
            } else {
                $truncated.show();
                $full.hide();
                $this.text('Read More');
            }
        });

        // Handle Assign Engineer Button Click
        $('#assigned-entries-table, #unassigned-entries-table').on('click', '.assign-engineer-btn', function() {
            var entryId = $(this).data('id');
            $('#assignEntryId').val(entryId);

            // Show modal immediately
            $('#assignEngineerModal').modal('show');

            // Set loading state for dropdowns
            $('#assignServiceEngineer, #assignServiceEngineer2')
                .addClass('select2-loading')
                .empty().append('<option>Loading engineers...</option>').prop('disabled', true);
            $('#assignDueDate1, #assignDueDate2').addClass('loading-field').val('Loading...');

            // Fetch the current service details to get the assigned engineers
            $.ajax({
                url: '/entries/' + entryId + '/edit',
                method: 'GET',
                success: function(data) {
                    $('#assignDueDate1').removeClass('loading-field').val(data.due_date_1 || '');
                    $('#assignDueDate2').removeClass('loading-field').val(data.due_date_2 || '');

                    // Set the dealership filter based on the current entry
                    $('#assignDealershipFilter').val(data.dealership_id || 'all');

                    // Now fetch and populate the dropdowns, pre-selecting the current engineers
                    fetchAndPopulateServiceEngineers(data.service_engineer_id, data
                        .service_engineer_id_2);
                },
                error: function(error) {
                    console.error('Error fetching entry data for assignment:', error);
                    showToast('Error fetching service details.', 'danger');
                    $('#assignEngineerModal').modal('hide');
                    $('#assignServiceEngineer, #assignServiceEngineer2').removeClass('select2-loading');
                }
            });
        });

        // Handle Assign Engineer Form Submission
        $('#assignEngineerForm').on('submit', function(e) {
            e.preventDefault();

            var serviceEngineer1 = $('#assignServiceEngineer').val();
            var serviceEngineer2 = $('#assignServiceEngineer2').val();

            if (serviceEngineer1 && serviceEngineer2 && serviceEngineer1 === serviceEngineer2) {
                showToast('Service Engineer 1 and Service Engineer 2 cannot be the same.', 'danger');
                return; // Prevent form submission
            }

            var entryId = $('#assignEntryId').val();
            var formData = $(this).serialize();

            $.ajax({
                url: window.baseUrl + '/entries/' + entryId + '/assign-engineer',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    assignedEntriesTable.ajax.reload(null, false); // Reload assigned table, keep pagination
                    unassignedEntriesTable.ajax.reload(null, false); // Reload unassigned table
                    $('#assignEngineerModal').modal('hide');
                },
                error: function(xhr) {
                    console.error('Error assigning engineer:', xhr);
                    var message = 'Error assigning engineer.';
                    if (xhr.status === 419) {
                        message = 'Session expired or CSRF token mismatch. Please refresh the page.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast(message, 'danger');
                }
            });
        });

        // Add change event listeners to filter dropdowns
        $('#assignServiceEngineer').on('change', filterServiceEngineerDropdowns);
        $('#assignServiceEngineer2').on('change', filterServiceEngineerDropdowns);


        // Initialize Select2 for Edit Client with AJAX
        $('#editClient').select2({
            dropdownParent: $('#editEntryModal'),
            placeholder: 'Search for a client by name or phone number',
            ajax: {
                url: "{{ route('leads.search-clients-by-phone') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(client) {
                            return {
                                id: client.id,
                                text: client.name + ' (' + client.phone_number + ')',
                                phone_number: client.phone_number,
                                name: client.name
                            };
                        })
                    };
                },
                cache: true
            }
        });

        // Initialize Select2 for Create Client with AJAX
        $('#createClient').select2({
            placeholder: 'Search for a client by name or phone number',
            tags: true,
            ajax: {
                url: "{{ route('entries.search-clients') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(client) {
                            return {
                                id: client.id,
                                text: client.name + ' (' + client.phone_number + ')',
                                salutation: client.salutation,
                                name: client.name,
                                email: client.email,
                                phone_number: client.phone_number,
                                leads_count: client.leads_count,
                                existing: true
                            };
                        })
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            var data = e.params.data;
            if (data.existing) {
                // Populate contact info if available (assuming phone number as default)
                $('#createContactInfo').val(data.phone_number);
                $('#createContactPerson').val(data.name);

                // Display history count
                // $('#clientHistoryCount').text(data.leads_count || 0); // Not used in this layout

                // Set the profile link
                var profileUrl = "{{ route('clients.show', ':id') }}".replace(':id', data.id);
                $('#viewClientProfileBtn').attr('href', profileUrl);

                $('#clientHistorySection').stop(true, true).slideDown();
                $('#clientHistoryLoader').show();
                $('#clientHistorySummary').hide();

                // Fetch history
                $.ajax({
                    url: "{{ route('leads.client-history', ':id') }}".replace(':id', data.id),
                    method: 'GET',
                    success: function(history) {
                        $('#clientHistoryLoader').hide();

                        $('#hist_leads').text(history.total_leads || 0);
                        $('#hist_services').text(history.total_services || 0);
                        $('#hist_products').text(history.total_products || 0);

                        $('#clientHistorySummary').fadeIn();
                    },
                    error: function() {
                        $('#clientHistoryLoader').hide();
                        $('#clientHistorySection').slideUp();
                    }
                });

                $('#createClient').next('.select2-container').find('.select2-selection--single').css(
                    'background-color', '#d4edda');

                // Trigger change to load products
                // Note: The original 'change' handler for #createClient will still trigger
                // because Select2 triggers a native change event.
            } else {
                $('#clientHistorySection').stop(true, true).slideUp();
            }
        });

        // Service Import Logic
        var recentImportsDataTable = $('#recentImportsTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('services.import.recent') }}",
                dataSrc: ''
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        return new Date(data).toLocaleString();
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        var summary = '<strong>' + data.services_count + ' Services</strong><br>';
                        if (data.services && data.services.length > 0) {
                            var names = data.services.map(function(s) { return s.name; });
                            summary += '<small class="text-muted">' + names.join(', ');
                            if (data.services_count > 5) {
                                summary += '...';
                            }
                            summary += '</small>';
                        }
                        return summary;
                    }
                },
                {
                    data: 'id',
                    render: function(data) {
                        return '<button class="btn btn-sm btn-danger undo-import-btn" data-id="' +
                            data + '">Undo</button>';
                    }
                }
            ],
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            language: {
                emptyTable: "No recent imports found."
            }
        });

        $('#importServiceForm').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            var button = $('#importServiceButton');
            var spinner = $('#import-spinner');
            var status = $('#import-status');
            var errors = $('#import-errors');
            var progressBar = $('#import-progress-bar');
            var progressContainer = $('.progress');

            button.prop('disabled', true);
            spinner.show();
            status.text('Uploading...');
            errors.html('');
            progressContainer.show();
            progressBar.css('width', '0%').text('0%');

            $.ajax({
                url: "{{ route('services.import') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    status.text('Import started. ID: ' + response.import_id);
                    startPollingProgress(response.import_id);
                },
                error: function(xhr) {
                    button.prop('disabled', false);
                    spinner.hide();
                    status.text('Error: ' + xhr.responseText);
                }
            });
        });

        function startPollingProgress(importId) {
            var progressBar = $('#import-progress-bar');
            var status = $('#import-status');
            var resultsContainer = $('#import-results');
            var closeResultsButton = $('#closeImportResults');
            var spinner = $('#import-spinner');
            var button = $('#importServiceButton');
            var progressContainer = $('.progress');

            var pollInterval = setInterval(function() {
                $.ajax({
                    url: '/services/import/progress/' + importId,
                    method: 'GET',
                    success: function(data) {
                        var percent = data.percentage;
                        progressBar.css('width', percent + '%').text(percent + '%');
                        status.text('Status: ' + data.status + ' | Processed: ' + data.processed_rows + ' / ' + data.total_rows + ' | New Clients Created: ' + (data.new_clients_count || 0));

                        if (data.status === 'completed' || data.status === 'failed') {
                            clearInterval(pollInterval);
                            button.prop('disabled', false);
                            spinner.hide();

                            if (data.status === 'completed') {
                                showToast('Import completed successfully', 'success');
                                if (assignedEntriesTable) assignedEntriesTable.ajax.reload();
                                if (unassignedEntriesTable) unassignedEntriesTable.ajax.reload();
                                setTimeout(function() {
                                    progressContainer.fadeOut();
                                    status.text('Import Finished');
                                }, 2000);
                            } else {
                                status.html('<div class="alert alert-danger">Import failed.</div>');
                            }

                            var resultsHtml = '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
                            var summary = '<div class="alert alert-info">' +
                                '<strong>Import Summary:</strong><br>' +
                                'Total Records: ' + (data.total_rows || data.processed_rows) + '<br>' +
                                'Successful: ' + (data.success_count || 0) + '<br>' +
                                'Failed: ' + (data.failed_count || 0) + '<br>' +
                                'New Clients Created: ' + (data.new_clients_count || 0) +
                                '</div>';
                            resultsHtml += summary;

                            if (data.results && data.results.length > 0) {
                                resultsHtml += '<table class="table table-bordered table-striped table-sm">';
                                resultsHtml += '<thead class="table-light"><tr><th>Row</th><th>Service Name</th><th>Status</th><th>Details</th></tr></thead>';
                                resultsHtml += '<tbody>';

                                $.each(data.results, function(index, result) {
                                    var rowClass = result.status === 'failed' ? 'table-danger' : 'table-warning';
                                    var statusBadge = '<span class="badge bg-' + (result.status === 'failed' ? 'danger' : 'warning') + '">' + result.status + '</span>';
                                    var warningsHtml = '';
                                    if (result.warnings && result.warnings.length > 0) {
                                        $.each(result.warnings, function(i, w) {
                                            warningsHtml += '<div class="text-danger small"><i class="fa fa-exclamation-circle me-1"></i>' + w + '</div>';
                                        });
                                    } else {
                                        warningsHtml = '<span class="text-muted small">' + (result.reason || '') + '</span>';
                                    }

                                    resultsHtml += '<tr class="' + rowClass + '">';
                                    resultsHtml += '<td>' + result.row_number + '</td>';
                                    resultsHtml += '<td>' + (result.service_name || 'N/A') + '</td>';
                                    resultsHtml += '<td>' + statusBadge + '</td>';
                                    resultsHtml += '<td>' + warningsHtml + '</td>';
                                    resultsHtml += '</tr>';
                                });
                                resultsHtml += '</tbody></table>';
                            }
                            resultsHtml += '</div>';
                            resultsContainer.html(resultsHtml).show();
                            closeResultsButton.show();
                            if (data.errors && data.errors.length > 0) {
                                var errorHtml = '<div class="alert alert-danger"><ul>';
                                $.each(data.errors, function(index, error) {
                                    errorHtml += '<li>' + error + '</li>';
                                });
                                errorHtml += '</ul></div>';
                                $('#import-errors').html(errorHtml);
                            }
                            loadRecentImports();
                        }
                    },
                    error: function() {
                        clearInterval(pollInterval);
                        spinner.hide();
                        button.prop('disabled', false);
                        status.text('Error polling progress.');
                    }
                });
            }, 1000);
        }

        function loadRecentImports() {
            if ($.fn.DataTable.isDataTable('#recentImportsTable')) {
                $('#recentImportsTable').DataTable().ajax.reload();
            }
        }

        $('#closeImportResults').on('click', function() {
            $('#import-results').html('');
            $(this).hide();
            $('#import-status').html('');
            $('#import-errors').html('');
        });

        $(document).on('click', '.undo-import-btn', function() {
            var importId = $(this).data('id');
            $('#undoImportId').val(importId);
            $('#undoImportModal').modal('show');
        });

        $('#confirmUndoImport').on('click', function() {
            var importId = $('#undoImportId').val();
            var button = $(this);
            button.prop('disabled', true).text('Undoing...');

            $.ajax({
                url: '/services/import/' + importId,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#undoImportModal').modal('hide');
                    showToast(response.message, 'success');
                    loadRecentImports();
                    if (assignedEntriesTable) assignedEntriesTable.ajax.reload();
                    if (unassignedEntriesTable) unassignedEntriesTable.ajax.reload();
                },
                error: function(xhr) {
                    showToast('Error undoing import: ' + (xhr.responseJSON.message || xhr.statusText), 'danger');
                },
                complete: function() {
                    button.prop('disabled', false).text('Undo Import');
                }
            });
        });

        // Make date inputs open picker on click anywhere in the field
        $(document).on('click', 'input[type="date"]', function() {
            if (this.showPicker) {
                this.showPicker();
            }
        });

    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,marker"></script>
@endpush
