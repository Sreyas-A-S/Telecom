@extends('layouts.admin')

@section('title', 'Leads')

@push('styles')
<style>
    /* Custom fallback suggestions UI for edit modal (when native .pac-items aren't created) */
    .custom-pac-container {
        position: fixed;
        z-index: 200001;
        background: #fffbe6;
        /* light yellow to stand out */
        border: 2px solid #0d6efd;
        /* blue border for visibility */
        border-radius: 4px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        max-height: 240px;
        overflow: auto;
        width: 320px;
        display: block;
        pointer-events: auto;
    }

    .custom-pac-item {
        padding: 8px 12px;
        cursor: pointer;
    }

    .custom-pac-item:hover {
        background: #f1f1f1;
    }

    .td-agent-source {
        white-space: normal !important;
        min-width: 180px;
    }

    .td-product-value {
        white-space: normal !important;
        min-width: 150px;
        font-size: 0.85rem;
    }

    table.dataTable thead th {
        font-size: 0.85rem !important;
        padding: 10px 8px !important;
    }

    .td-category-dealership {
        white-space: normal !important;
        min-width: 180px;
    }

    .status-select {
        border: 1px solid rgba(0,0,0,0.1) !important;
        font-weight: 700 !important;
        padding: 2px 24px 2px 10px !important; /* Adjusted for dropdown arrow space if needed, though appearance is none */
        border-radius: 15px !important;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        text-align: center;
        text-transform: capitalize;
        font-size: 0.7rem !important;
        transition: all 0.3s ease;
        line-height: 1.2 !important;
    }

    .status-select:focus {
        box-shadow: none !important;
    }

    /* Custom Slider Styling */
    .custom-slider {
        height: 10px;
        border-radius: 5px;
        background: #e9ecef;
        outline: none;
        padding: 0;
        margin: 0;
    }

    .custom-slider::-webkit-slider-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0d6efd;
        cursor: pointer;
        border: 4px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    .custom-slider::-webkit-slider-thumb:hover {
        transform: scale(1.1);
        background: #0b5ed7;
    }

    .custom-slider::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0d6efd;
        cursor: pointer;
        border: 4px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: all 0.2s ease;
    }

    /* Force background colors for status chips */
    .bg-warning.text-dark { background-color: #ffc107 !important; color: #212529 !important; }
    .bg-info.text-white { background-color: #0dcaf0 !important; color: #fff !important; }
    .bg-success.text-white { background-color: #198754 !important; color: #fff !important; }
    .bg-danger.text-white { background-color: #dc3545 !important; color: #fff !important; }
    .bg-primary.text-white { background-color: #0d6efd !important; color: #fff !important; }
    .bg-secondary.text-white { background-color: #6c757d !important; color: #fff !important; }

    #map {
        height: 300px;
    }

    .product-item {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        transition: all 0.2s ease;
        position: relative;
    }

    .product-item:hover {
        border-color: #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    @media (max-width: 767.98px) {
        .product-item .col-md-1 {
            position: absolute;
            top: 10px;
            right: 10px;
            width: auto;
            margin-bottom: 0;
            padding: 0;
        }

        .product-item .remove-product-item {
            padding: 0.25rem 0.5rem;
        }
    }

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
</style>
@endpush

<template id="product-item-template">
    <div class="product-item row border-bottom mb-3 pb-3">
        <div class="col-md-3 mb-2">
            <label class="form-label">Product</label>
            <div class="input-group">
                <input list="product-list" class="form-control product-input" name="items[INDEX][product_name]" autocomplete="off" required>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <label class="form-label">Model</label>
            <input list="product-model-list-INDEX" class="form-control model-input" name="items[INDEX][product_model_name]" autocomplete="off">
            <datalist id="product-model-list-INDEX"></datalist>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control qty-input" name="items[INDEX][quantity]" value="1" min="1">
        </div>
        <div class="col-md-3 mb-2">
            <label class="form-label">Price</label>
            <div class="input-group">
                <span class="input-group-text">₹</span>
                <input type="number" step="0.01" class="form-control price-input" name="items[INDEX][price]" readonly>
            </div>
        </div>
        <div class="col-md-1 mb-2 d-flex align-items-end justify-content-end">
            <button type="button" class="btn btn-danger btn-sm remove-product-item">
                <i class="icon-trash"></i>
            </button>
        </div>
    </div>
</template>


@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Lead Management


                </h3>
            </div>

            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Leads</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <datalist id="product-list"></datalist>
    <datalist id="agent-list"></datalist>
    <datalist id="lead-source-list"></datalist>
    <datalist id="lead-category-list"></datalist>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs d-flex" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="assigned-leads-tab" data-bs-toggle="tab"
                        data-bs-target="#assigned-leads" type="button" role="tab" aria-controls="assigned-leads"
                        aria-selected="true">Assigned
                        Leads</button>
                </li>
                @if($showUnassignedLeadsTab)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="unassigned-leads-tab" data-bs-toggle="tab"
                        data-bs-target="#unassigned-leads" type="button" role="tab"
                        aria-controls="unassigned-leads" aria-selected="false">Unassigned
                        Leads</button>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create"
                        type="button" role="tab" aria-controls="create" aria-selected="false">Create
                        Lead</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="agents-tab" data-bs-toggle="tab" data-bs-target="#agents"
                        type="button" role="tab" aria-controls="agents" aria-selected="false">Agents</button>
                </li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="assigned-leads" role="tabpanel"
                            aria-labelledby="assigned-leads-tab">
                            @if(checkMenu(Session::get('role_id'), 5, 'read'))
                            <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse"
                                data-bs-target="#leads-filter-collapse" aria-expanded="false"
                                aria-controls="leads-filter-collapse">
                                <i class="fa fa-filter"></i> Toggle Filters
                            </button>
                            <div class="collapse" id="leads-filter-collapse">
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <label for="filter-status" class="form-label">Status</label>
                                        <select id="filter-status" class="form-select form-select-sm">
                                            <option value="">All</option>
                                            @foreach ($statuses as $status)
                                            <option value="{{ $status->status }}">{{ ucfirst($status->status) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="filter-followup" class="form-label">Follow Up</label>
                                        <select id="filter-followup" class="form-select form-select-sm">
                                            <option value="">Any</option>
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="filter-category" class="form-label">Category</label>
                                        <select id="filter-category" class="form-select form-select-sm">
                                            <option value="">All</option>
                                            @foreach ($leadCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="filter-source" class="form-label">Source</label>
                                        <select id="filter-source" class="form-select form-select-sm">
                                            <option value="">All</option>
                                            @foreach ($leadSources as $source)
                                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 <?php if (Auth::user()->user_type == 'employee' && Auth::user()->employee && Auth::user()->employee->dealership_id) {
                                                                echo 'd-none';
                                                            } ?>">
                                        <label for="filter-dealership" class="form-label">Dealership</label>
                                        <select id="filter-dealership" class="form-select form-select-sm">
                                            <option value="">All</option>
                                            @foreach ($dealerships as $dealership)
                                            <option value="{{ $dealership->id }}">{{ $dealership->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="filter-from-date" class="form-label">From Date</label>
                                        <input type="date" id="filter-from-date"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="filter-to-date" class="form-label">To Date</label>
                                        <input type="date" id="filter-to-date"
                                            class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">

                                <table class="display" id="leads-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Name & Contact</th>
                                            <th>Product & Value</th>
                                            <th>Category & Dealership</th>
                                            <th>Latest Follow Up</th>
                                            <th>Success Rate</th>
                                            <th>Follow Up</th>
                                            <th>Status</th>
                                            <th>Assigned Employee</th>
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
                                You do not have permission to view this page.
                            </div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="unassigned-leads" role="tabpanel"
                            aria-labelledby="unassigned-leads-tab">
                            @if(checkMenu(Session::get('role_id'), 12, 'read'))
                            <button class="btn btn-primary mb-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#unassigned-leads-filter-collapse"
                                aria-expanded="false" aria-controls="unassigned-leads-filter-collapse">
                                <i class="fa fa-filter"></i> Toggle Filters
                            </button>
                            <div class="collapse" id="unassigned-leads-filter-collapse">
                                <div class="row mb-3">
                                    {{-- <div class="col-md-2"> <label for="unassigned-filter-status" class="form-label">Status</label> <select id="unassigned-filter-status" class="form-select form-select-sm"> <option value="">All</option> @foreach ($statuses as $status) <option value="{{ $status->status }}">{{ ucfirst($status->status) }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="unassigned-filter-followup" class="form-label">Follow Up</label>
                                    <select id="unassigned-filter-followup" class="form-select form-select-sm">
                                        <option value="">Any</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div> --}}
                                <div class="col-md-2">
                                    <label for="unassigned-filter-category"
                                        class="form-label">Category</label>
                                    <select id="unassigned-filter-category"
                                        class="form-select form-select-sm">
                                        <option value="">All</option>
                                        @foreach ($leadCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="unassigned-filter-source" class="form-label">Source</label>
                                    <select id="unassigned-filter-source" class="form-select form-select-sm">
                                        <option value="">All</option>
                                        @foreach ($leadSources as $source)
                                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 <?php if (Auth::user()->user_type == 'employee' && Auth::user()->employee && Auth::user()->employee->dealership_id) {
                                                            echo 'd-none';
                                                        } ?>">
                                    <label for="unassigned-filter-dealership"
                                        class="form-label">Dealership</label>
                                    <select id="unassigned-filter-dealership"
                                        class="form-select form-select-sm">
                                        <option value="">All</option>
                                        @foreach ($dealerships as $dealership)
                                        <option value="{{ $dealership->id }}">{{ $dealership->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <div class="col-md-2"> <label for="unassigned-filter-from-date" class="form-label">From Date</label> <input type="date" id="unassigned-filter-from-date" class="form-control form-control-sm"> </div> {{-- <div class="col-md-2"> <label for="unassigned-filter-to-date" class="form-label">To Date</label> <input type="date" id="unassigned-filter-to-date" class="form-control form-control-sm"> </div> --}}
                                {{-- <div class="col-md-3"> <label for="globalSearchInputUnassigned" class="form-label">Search All</label> <input type="search" class="form-control" id="globalSearchInputUnassigned" placeholder="Search all columns"> </div> --}}
                            </div>
                        </div>
                        <div class="table-responsive">

                            <table class="display" id="unassigned-leads-table">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Name & Contact</th>
                                        <th>Product & Value</th>
                                        <th>Category & Dealership</th>
                                        <th>Status</th>
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
                            You do not have permission to view this page.
                        </div>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                        @if(checkMenu(Session::get('role_id'), 12, 'create'))
                        <form id="createLeadForm" class="theme-form">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Lead Details</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="clientSearch" class="form-label">Search Client
                                                        by Phone</label>
                                                    <select class="form-control" id="clientSearch"
                                                        name="phone_number"></select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="salutation"
                                                        class="form-label">Salutation</label>
                                                    <select class="form-select" id="salutation"
                                                        name="salutation">
                                                        <option value="Mr.">Mr.</option>
                                                        <option value="Mrs.">Mrs.</option>
                                                        <option value="Ms.">Ms.</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="leadName" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="leadName"
                                                        name="name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="leadCompany" class="form-label">Company</label>
                                                    <input type="text" class="form-control" id="leadCompany"
                                                        name="company">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="leadEmail" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="leadEmail"
                                                        name="email">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="leadAlternateContact" class="form-label">Alternate Contact Number</label>
                                                    <input type="text" class="form-control" id="leadAlternateContact"
                                                        name="alternate_contact_number">
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
                                                <input type="hidden" id="isExistingClient"
                                                    name="is_existing_client" value="0">

                                                <div class="col-md-6 mb-3">
                                                    <label for="agent" class="form-label">Agent</label>
                                                    <div class="input-group has-validation">
                                                        <input list="agent-list" class="form-control"
                                                            id="agent" name="agent_name"
                                                            autocomplete="off">
                                                        <datalist id="agent-list">
                                                            {{-- Agents will be loaded dynamically --}}
                                                        </datalist>
                                                        <button class="btn btn-primary" type="button"
                                                            id="addAgentBtn">+</button>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="leadSource" class="form-label">Lead
                                                        Source</label>
                                                    <div class="input-group">
                                                        <input list="lead-source-list" class="form-control"
                                                            id="leadSource" name="lead_source"
                                                            autocomplete="off">
                                                        <button class="btn btn-primary" type="button"
                                                            id="addLeadSourceBtn">+</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="leadCategory" class="form-label">Lead
                                                        Category</label>
                                                    <div class="input-group">
                                                        <input list="lead-category-list" class="form-control"
                                                            id="leadCategory" name="lead_category"
                                                            autocomplete="off">
                                                        <button class="btn btn-primary" type="button"
                                                            id="addLeadCategoryBtn">+</button>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="allowFollowUp" class="form-label">Allow
                                                        Follow-up</label>
                                                    <select class="form-select" id="allowFollowUp"
                                                        name="allow_follow_up">
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                </div>


                                                <div class="col-12 mb-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <label class="form-label mb-0">Products</label>
                                                        <button type="button" class="btn btn-primary btn-sm" id="add-product-item-btn">
                                                            <i class="icon-plus"></i> Add Product
                                                        </button>
                                                    </div>
                                                    <div id="product-items-container" class="mt-2">
                                                        <!-- Products will be added here -->
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label for="leadValue" class="form-label">Lead
                                                        Total Value</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            id="leadValue" name="lead_value">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="financier"
                                                        class="form-label">Financier</label>
                                                    <input type="text" class="form-control" id="financier"
                                                        name="financier">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="type" class="form-label">Type
                                                        (FTB/FTU/Retail/Strategic)</label>
                                                    <input type="text" class="form-control" id="editType"
                                                        name="type" list="edit-type-list">
                                                    <datalist id="edit-type-list">
                                                        <option value="FTB">
                                                        <option value="FTU">
                                                        <option value="Retail">
                                                        <option value="Strategic">
                                                    </datalist>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="loginStatus" class="form-label">Login
                                                        Status</label>
                                                    <input type="text" class="form-control"
                                                        id="loginStatus" name="login_status"
                                                        list="login-status-list" autocomplete="off"
                                                        autocorrect="off">
                                                    <datalist id="login-status-list">
                                                        <option value="Logged In">
                                                        <option value="Yet to Login">
                                                    </datalist>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="stage" class="form-label">Stage</label>
                                                    <input type="text" class="form-control" id="stage"
                                                        name="stage" list="stage-list" autocomplete="off"
                                                        autocorrect="off">
                                                    <datalist id="stage-list">
                                                        <option value="opportunity">
                                                        <option value="lead">
                                                        <option value="pending">
                                                    </datalist>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="billing" class="form-label">Billing Plan Month</label>
                                                    <input type="month" class="form-control" id="billing" name="billing">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="location" class="form-label">District</label>
                                                    <select class="form-select" id="location"
                                                        name="location">
                                                        <option value="">Select District</option>
                                                        @foreach ($keralaDistricts as $district)
                                                        <option value="{{ $district->name }}">
                                                            {{ $district->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="map_location"
                                                        class="form-label">Location</label>
                                                    <input type="text" name="map_location"
                                                        id="map_location" class="form-control">
                                                    <div class="invalid-feedback" id="map_location_error">
                                                    </div>
                                                    <input type="hidden" name="latitude" id="latitude">
                                                    <input type="hidden" name="longitude" id="longitude">
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <div id="map" style="height: 400px; width: 100%;">
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label for="remarks" class="form-label">Remarks</label>
                                                    <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                                                </div>
                                                @php
                                                $currentUser = Auth::user();
                                                $isEmployeeWithDealership = false;
                                                $employeeDealershipId = null;

                                                if (
                                                $currentUser &&
                                                $currentUser->user_type === 'employee'
                                                ) {
                                                $currentUser->load('employee'); // Ensure employee relationship is loaded
                                                if (
                                                $currentUser->employee &&
                                                $currentUser->employee->dealership_id !== null
                                                ) {
                                                $isEmployeeWithDealership = true;
                                                $employeeDealershipId =
                                                $currentUser->employee->dealership_id;
                                                }
                                                }
                                                @endphp

                                                <div class="col-md-6 mb-3" id="createDealershipField"
                                                    @if($isEmployeeWithDealership) style="display: none;" @endif>
                                                    <label for="dealership" class="form-label">Dealership
                                                        @if(!$isEmployeeWithDealership)
                                                        <span class="text-danger">*</span>
                                                        @endif
                                                    </label>
                                                    <select class="form-select" id="dealership"
                                                        name="dealership_id"
                                                        @if($isEmployeeWithDealership) disabled @endif>
                                                        <option value="">Select Dealership</option>
                                                        @foreach ($dealerships as $dealership)
                                                        @if($dealership->brand)
                                                        <option value="{{ $dealership->id }}"
                                                            @if($isEmployeeWithDealership && $dealership->id == $employeeDealershipId) selected @endif>
                                                            {{ $dealership->name }}
                                                        </option>
                                                        @endif
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="assignedEmployee" class="form-label">Assigned Employee</label>
                                                    <select class="form-select select2" id="assignedEmployee" name="employee_id">
                                                        <option value="">Select Employee</option>
                                                        @foreach ($employees as $employee)
                                                        <option value="{{ $employee->id }}" {{ (Auth::user()->employee && Auth::user()->employee->id == $employee->id) ?'selected':'' }}>
                                                            {{ $employee->name }} ({{ $employee->employee_id }})
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="leadDueDate" class="form-label">Due Date (Optional)</label>
                                                    <input type="date" class="form-control" id="leadDueDate" name="due_date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">Save Lead</button>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="alert alert-danger" role="alert">
                            You do not have permission to create leads.
                        </div>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="agents" role="tabpanel" aria-labelledby="agents-tab">
                        <div class="table-responsive">
                            @if(checkMenu(Session::get('role_id'), 16, 'read'))
                            <table class="display" id="agents-table">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            @else
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to view this page.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Edit Lead Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1" aria-labelledby="editLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLeadModalLabel">Edit Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLeadForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editLeadId" value="" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editSalutation" class="form-label">Salutation</label>
                            <select class="form-select" id="editSalutation" name="salutation">
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLeadName" class="form-label">Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editLeadName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLeadCompany" class="form-label">Company</label>
                            <input type="text" class="form-control" id="editLeadCompany" name="company">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLeadEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editLeadEmail" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLeadPhone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="editLeadPhone" name="phone_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLeadAlternateContact" class="form-label">Alternate Contact Number</label>
                            <input type="text" class="form-control" id="editLeadAlternateContact" name="alternate_contact_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAgent" class="form-label">Agent</label>
                            <div class="input-group">
                                <input list="edit-agent-list" class="form-control" id="editAgent" name="agent_name"
                                    autocomplete="off">
                                <datalist id="edit-agent-list">
                                    {{-- Agents will be loaded dynamically --}}
                                </datalist>
                                <button class="btn btn-primary" type="button" id="editAddAgentBtn">+</button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLeadSource" class="form-label">Lead Source</label>
                            <div class="input-group">
                                <input list="edit-lead-source-list" class="form-control" id="editLeadSource"
                                    name="lead_source" autocomplete="off">
                                <datalist id="edit-lead-source-list">
                                    {{-- Options will be loaded dynamically --}}
                                </datalist>
                                <button class="btn btn-primary" type="button" id="editAddLeadSourceBtn">+</button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLeadCategory" class="form-label">Lead Category</label>
                            <div class="input-group">
                                <input list="edit-lead-category-list" class="form-control" id="editLeadCategory"
                                    name="lead_category" autocomplete="off">
                                <datalist id="edit-lead-category-list">
                                    {{-- Options will be loaded dynamically --}}
                                </datalist>
                                <button class="btn btn-primary" type="button" id="editAddLeadCategoryBtn">+</button>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="editAllowFollowUp" class="form-label">Allow Follow-up</label>
                            <select class="form-select" id="editAllowFollowUp" name="allow_follow_up">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">Products</label>
                                <button type="button" class="btn btn-primary btn-sm" id="edit-add-product-item-btn">
                                    <i class="icon-plus"></i> Add Product
                                </button>
                            </div>
                            <div id="edit-product-items-container" class="mt-2">
                                <!-- Products will be added here -->
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editLeadValue" class="form-label">Lead Total Value</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control" id="editLeadValue"
                                    name="lead_value">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editFinancier" class="form-label">Financier</label>
                            <input type="text" class="form-control" id="editFinancier" name="financier">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editType1" class="form-label">Type (FTB/FTU/Retail/Strategic)</label>
                            <input type="text" class="form-control" id="editType1" name="type"
                                list="edit-type-list1">
                            <datalist id="edit-type-list1">
                                <option value="FTB">
                                <option value="FTU">
                                <option value="Retail">
                                <option value="Strategic">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLoginStatus" class="form-label">Login Status</label>
                            <input type="text" class="form-control" id="editLoginStatus" name="login_status"
                                list="edit-login-status-list" autocomplete="off" autocorrect="off">
                            <datalist id="edit-login-status-list">
                                <option value="Logged In">
                                <option value="Yet to Login">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editStage" class="form-label">Stage</label>
                            <input type="text" class="form-control" id="editStage" name="stage"
                                list="edit-stage-list" autocomplete="off" autocorrect="off">
                            <datalist id="edit-stage-list">
                                <option value="opportunity">
                                <option value="lead">
                                <option value="pending">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editBilling" class="form-label">Billing Plan Month</label>
                            <input type="month" class="form-control" id="editBilling" name="billing">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLocation" class="form-label">District</label>
                            <select class="form-select" id="editLocation" name="location">
                                <option value="">Select District</option>
                                @foreach ($keralaDistricts as $district)
                                <option value="{{ $district->name }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_map_location" class="form-label">Location</label>
                            <input type="text" name="map_location" id="edit_map_location" class="form-control">
                            <datalist id="edit_map_location_datalist"></datalist>
                            <div class="invalid-feedback" id="edit_map_location_error"></div>
                            <input type="hidden" name="latitude" id="edit_latitude">
                            <input type="hidden" name="longitude" id="edit_longitude">
                        </div>
                        <div class="col-md-12 mb-3">
                            <div id="editMap" style="height: 400px; width: 100%;"></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editRemarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="editRemarks" name="remarks" rows="3"></textarea>
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


<!-- Update Success Rate Modal -->
<div class="modal fade" id="updateSuccessRateModal" tabindex="-1" aria-labelledby="updateSuccessRateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateSuccessRateModalLabel">Update Success Rate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateSuccessRateForm">
                @csrf
                @method('PUT') {{-- Use PUT method for updating --}}
                <input type="hidden" id="successRateLeadId" name="lead_id">
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <label for="successRateInput" class="form-label d-block mb-3">Chance of Success: <span id="successRateValue" class="fw-bold text-primary fs-5">0%</span></label>
                        <input type="range" class="form-range custom-slider" id="successRateInput" name="chance_of_success"
                            min="0" max="100" step="1" value="0">
                        <div class="d-flex justify-content-between mt-2 small text-muted">
                            <span>0%</span>
                            <span>25%</span>
                            <span>50%</span>
                            <span>75%</span>
                            <span>100%</span>
                        </div>
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

<!-- Delete Lead Modal -->
@if($permissions['can_delete'])
<div class="modal fade" id="deleteLeadModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLeadModalLabel">Delete Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteLeadName"></strong>?</p>
                <input type="hidden" id="deleteLeadId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteLead">Delete</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- View Lead Modal -->
<div class="modal fade" id="viewLeadModal" tabindex="-1" aria-labelledby="viewLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLeadModalLabel">Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Salutation:</strong> <span id="viewLeadSalutation"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Name:</strong> <span id="viewLeadName"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Company:</strong> <span id="viewLeadCompany"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong> <span id="viewLeadEmail"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone Number:</strong> <span id="viewLeadPhone"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Alternate Contact Number:</strong> <span id="viewLeadAlternateContact"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Agent:</strong> <span id="viewLeadAgent"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Source:</strong> <span id="viewLeadSource"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Category:</strong> <span id="viewLeadCategory"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Value:</strong> <span id="viewLeadValue"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Allow Follow-up:</strong> <span id="viewAllowFollowUp"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong> <span id="viewStatus"></span>
                    </div>
                    <div class="col-12 mb-3">
                        <h6>Products</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                                                <tr>
                                                                    <th>Product</th>
                                                                    <th>Model</th>
                                                                    <th>Qty</th>
                                                                    <th>Price</th>
                                                                </tr>                                </thead>
                                <tbody id="viewLeadProducts">
                                </tbody>
                            </table>
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

<!-- Confirm Status Change Modal -->
<div class="modal fade" id="confirmStatusChangeModal" tabindex="-1" aria-labelledby="confirmStatusChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmStatusChangeModalLabel">Confirm Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="statusChangeMessage"></p>
                <div id="conversionFieldsContainer" class="d-none mt-3 border-top pt-3">
                    <h6>Conversion Details <span class="text-danger small">(Required for 'Win')</span></h6>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="stat_billing" class="form-label">Billing Plan Month <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="stat_billing" name="billing">
                            <div class="invalid-feedback">Please select a billing month.</div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="stat_doc" class="form-label">Date of Commissioning (DOC) <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="stat_doc" name="doc">
                            <div class="invalid-feedback">Please select a commissioning date.</div>
                        </div>
                        <div class="col-md-12">
                            <h6>Item Serial Numbers <span class="text-danger">*</span></h6>
                            <div id="stat_serial_numbers_container" class="bg-light text-dark p-2 rounded border mb-3">
                                <!-- Dynamic serial fields will be added here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div id="lossReasonContainer" class="d-none mt-3">
                    <label for="lossReason" class="form-label">Reason for Loss <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="lossReason" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusChangeBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Convert to Client Modal -->
<div class="modal fade" id="confirmConvertToClientModal" tabindex="-1"
    aria-labelledby="confirmConvertToClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmConvertToClientModalLabel">Confirm Conversion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="convertConfirmationMessage">Are you sure you want to convert this lead to a client? This will mark the lead status as 'Win' and set the progress to 100%.</p>
                <form id="conversionDetailsForm">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="conv_billing" class="form-label">Billing Plan Month <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="conv_billing" name="billing" required>
                            <div class="invalid-feedback">Please select a billing month.</div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="conv_doc" class="form-label">Date of Commissioning (DOC) <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="conv_doc" name="doc" required>
                            <div class="invalid-feedback">Please select a commissioning date.</div>
                        </div>
                        <div class="col-md-12">
                            <h6>Item Serial Numbers <span class="text-danger">*</span></h6>
                            <div id="conv_serial_numbers_container" class="bg-light text-dark p-2 rounded border mb-3">
                                <!-- Dynamic serial fields will be added here -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmConvertToClientBtn">Convert</button>
            </div>
        </div>
    </div>
</div>



<!-- Assign Employee Modal -->
<div class="modal fade" id="assignEmployeeModal" tabindex="-1" aria-labelledby="assignEmployeeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignEmployeeModalLabel">Assign Employee to Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignEmployeeForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="assignLeadId" name="lead_id">
                <div class="modal-body">
                    <div id="assignEmployeeMessage" class="alert alert-info d-none" role="alert"></div>
                    <div class="mb-3">
                        <label for="employeeSelect" class="form-label">Select Employee</label>
                        <select class="form-select" id="employeeSelect" name="employee_id" required>
                            <!-- Options will be loaded dynamically via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assignDueDate" class="form-label">Due Date (Optional)</label>
                        <input type="date" class="form-control" id="assignDueDate" name="due_date">
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

<!-- View Agent Modal -->
<div class="modal fade" id="viewAgentModal" tabindex="-1" aria-labelledby="viewAgentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAgentModalLabel">Agent Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Name:</strong> <span id="viewAgentName"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong> <span id="viewAgentEmail"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone Number:</strong> <span id="viewAgentPhone"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Type:</strong> <span id="viewAgentType"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Agent Modal -->
<div class="modal fade" id="editAgentModal" tabindex="-1" aria-labelledby="editAgentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAgentModalLabel">Edit Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 16, 'update'))
            <form id="editAgentForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editAgentId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editAgentName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editAgentName" name="name"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAgentEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editAgentEmail" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAgentPhone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="editAgentPhone" name="phone_number">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
            @else
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to edit agents.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {



        // Prevent form submission on Enter key in map_location input
        $('#map_location').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // Filter functionality


        var assignedLeadsTable;
        var unassignedLeadsTable;


        function initializeDataTables() {
            @if(checkMenu(Session::get('role_id'), 5, 'read'))
            assignedLeadsTable = $("#leads-table").DataTable({
                processing: true,
                serverSide: true,
                searching: true,
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
                        text: '<i class="fa fa-file-excel-o"></i> Full Excel',
                        className: 'btn btn-sm btn-info text-white',
                        action: function(e, dt, node, config) {
                            var params = dt.ajax.params();
                            var url = "{{ route('leads.export-excel') }}?" + $.param(params);
                            window.location.href = url;
                        }
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
                    url: "{{ route('leads.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.has_followup = $('#filter-followup').val();
                        d.lead_category_id = $('#filter-category').val();
                        d.lead_source_id = $('#filter-source').val();
                        d.dealership_id = $('#filter-dealership').val();
                        d.from_date = $('#filter-from-date').val(); // New
                        d.to_date = $('#filter-to-date').val(); // New
                        d.search.value = $('#leads-table_filter input').val();
                        d.employee_assignment_status =
                            'assigned'; // Always assigned for this table
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'td-name-contact',
                        orderable: true, // Set to true
                        render: function(data, type, row) {
                            var email = data.email ?
                                '<div class="badge rounded-pill bg-primary mt-1">' + data
                                .email +
                                '</div>' : '';
                            var phone = data.phone_number ?
                                '<div class="badge rounded-pill bg-info mt-1">' + data
                                .phone_number + '</div>' : '';
                            var clientBadge = row.is_client ?
                                '<div class="badge rounded-pill bg-success mt-1">Client</div>' :
                                '';
                            var company = data.company ?
                                '<div class="text-muted small mt-1">' + data.company + '</div>' :
                                '';
                            return '<div class="mb-1">' + data.salutation + ' ' + data
                                .name +
                                '</div>' + company + email + phone + clientBadge;
                        }
                    },

                    {
                        data: 'product',
                        name: 'product',
                        className: 'td-product-value',
                        orderable: true, // Set to true (for 'Value')
                        render: function(data, type, row) {
                            var leadValue = data.lead_value;
                            var items = data.items || [];
                            var productDisplay = '';

                            if (items.length > 0) {
                                var showItems = items.slice(0, 2);
                                showItems.forEach(function(item, index) {
                                    // Use product relation or product_name if available
                                    var productObj = item.product || item.product_name;
                                    var productName = 'N/A';
                                    if (typeof productObj === 'object' && productObj !== null) {
                                        productName = productObj.name || 'N/A';
                                    } else if (typeof productObj === 'string') {
                                        productName = productObj;
                                    }
                                    
                                    var modelObj = item.product_model || item.productModel || item.product_model_name;
                                    var modelName = '';
                                    if (typeof modelObj === 'object' && modelObj !== null) {
                                        modelName = ' - ' + (modelObj.name || '');
                                    } else if (typeof modelObj === 'string' && modelObj !== '') {
                                        modelName = ' - ' + modelObj;
                                    }
                                    
                                    productDisplay += '<div>' + productName + modelName + ' (x' + item.quantity + ')</div>';
                                });
                                if (items.length > 2) {
                                    productDisplay += '<div class="text-primary fw-bold">+' + (items.length - 2) + ' more</div>';
                                }
                            } else {
                                var product = data.primary_product;
                                var productModel = data.primary_product_model || row.product_model || row.productModel;
                                var productName = product ? product.name : 'N/A';
                                var productModelName = productModel ? ' - ' + productModel.name : '';
                                productDisplay = '<div>' + productName + productModelName + '</div>';
                            }

                            var formattedLeadValue = leadValue ?
                                '<div class="text-muted fw-bold">₹' +
                                leadValue + '</div>' : '';

                            return '<div class="text-center">' + productDisplay + formattedLeadValue + '</div>';
                        }
                    },
                    {
                        data: 'leadCategory.name',
                        name: 'leadCategory.name',
                        className: 'td-category-dealership',
                        orderable: false, // Set to false
                        render: function(data, type, row) {
                            var category = row.leadCategory ?
                                '<span class="badge bg-primary bg-opacity-50">' + row
                                .leadCategory
                                .name + '</span>' :
                                '<span class="badge bg-secondary bg-opacity-50">N/A</span>';
                            var dealership = row.dealership ?
                                '<span class="badge bg-info bg-opacity-50">' + row
                                .dealership.name +
                                '</span>' :
                                '<span class="badge bg-secondary bg-opacity-50">N/A</span>';
                            return '<div>' + category + '</div><div class="mt-1">' +
                                dealership +
                                '</div>';
                        }
                    },
                    {
                        data: 'latest_followup_date',
                        name: 'latest_followup_date',
                        orderable: true, // Set to true
                        render: function(data, type, row) {
                            if (data) {
                                var dateTime = new Date(data);
                                var day = String(dateTime.getDate()).padStart(2, '0');
                                var month = String(dateTime.getMonth() + 1).padStart(2,
                                    '0'); // Month is 0-indexed
                                var year = dateTime.getFullYear();
                                return `${day}/${month}/${year}`;
                            }
                            return 'N/A';
                        }
                    },
                    {
                        data: 'chance_of_success',
                        name: 'chance_of_success',
                        orderable: true, // Set to true
                        render: function(data, type, row) {
                            var percentage = data || 0; // Default to 0 if null
                            if (percentage === 0) {
                                return `<div class="success-rate-cell" data-id="${row.id}" data-percentage="${percentage}" style="cursor: pointer;"><span class="badge bg-secondary" style="cursor: pointer;" data-id="${row.id}" data-percentage="${percentage}">0%</span></div>`;
                            }
                            var progressBarClass = '';
                            if (percentage < 30) {
                                progressBarClass = 'bg-danger';
                            } else if (percentage < 70) {
                                progressBarClass = 'bg-warning';
                            } else {
                                progressBarClass = 'bg-success';
                            }
                            return `
                                <div class="success-rate-cell" data-id="${row.id}" data-percentage="${percentage}" style="cursor: pointer;">
                                    <div class="progress position-relative" style="height: 20px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progressBarClass}"
                                            role="progressbar" style="width: ${percentage}%;"
                                            aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                        <span class="justify-content-center d-flex position-absolute w-100 h-100 align-items-center text-dark" style="font-size: 0.8em;">${percentage}%</span>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'allow_follow_up',
                        name: 'allow_follow_up',
                        orderable: false, // Set to false
                        render: function(data) {
                            return data ? '<span class="badge bg-success">Yes</span>' :
                                '<span class="badge bg-danger">No</span>';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false, // Set to false
                        render: function(data, type, row) {
                            var statuses = ['pending', 'in progress', 'win', 'lost',
                                'positive'
                            ];
                            var statusColors = {
                                'pending': 'bg-warning text-dark',
                                'in progress': 'bg-info text-white',
                                'in_progress': 'bg-info text-white',
                                'win': 'bg-success text-white',
                                'lost': 'bg-danger text-white',
                                'positive': 'bg-primary text-white',
                                'converted_to_client': 'bg-success text-white'
                            };
                            var options = '';
                            $.each(statuses, function(index, status) {
                                options += '<option value="' + status + '" ' + (row
                                        .status === status ? 'selected' : '') +
                                    '>' +
                                    status + '</option>';
                            });


                            var currentStatus = (row.status || '').toLowerCase().trim();
                            var initialColorClass = statusColors[currentStatus] || statusColors[currentStatus.replace('_', ' ')] || 'bg-secondary text-white';
                            return '<select style="width: auto; min-width: 90px;" class="status-select ' +
                                initialColorClass + '" data-id="' + row.id +
                                '" data-current-status="' + row.status + '">' + options +
                                '</select>';
                        }
                    },
                    {
                        data: 'assigned_employee',
                        name: 'employee.name',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                            return '<div>' + data + '</div>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var viewBtn = '<a class="" title="View" href="/leads/' + row
                                .id +
                                '/profile"><i class="fa fa-eye text-success"></i></a>';
                            var editBtn = '';

                            editBtn =
                                '<a class="edit-lead-btn" href="javascript:void(0)" title="Edit" data-id="' +
                                row
                                .id + '"><i class="icon-pencil-alt text-success"></i></a>';

                            var deleteBtn = '';

                            deleteBtn =
                                '<a class="delete-lead-btn" href="javascript:void(0)" data-id="' +
                                row
                                .id + '" data-lead-name="' + row.name.name +
                                '"><i class="icon-trash text-danger"></i></a>';

                            var convertBtn = '';

                            @if(checkMenu(Session::get('role_id'), 13, 'create'))
                            if (row.status !== 'converted_to_client' && !row
                                .is_client) {
                                var isAssigned = row.assigned_employee && row
                                    .assigned_employee.trim() !== '';


                                convertBtn =
                                    '<a class="convert-to-client-btn" href="#" data-id="' +
                                    row.id +
                                    '" data-assigned="' + isAssigned +
                                    '"><i class="fa fa-user text-info"></i></a>';
                            }
                            @endif



                            var assignBtn = '';
                            // Only show assign button if the lead has a dealership_id
                            if (row.dealership_id) {
                                assignBtn =
                                    '<a class="assign-employee-btn" href="javascript:void(0)" title="Assign Employee" data-id="' +
                                    row.id + '" data-dealership-id="' + row.dealership_id +
                                    '"><i class="fa fa-user-plus text-primary"></i> </a>';
                            } else {
                                // If no dealership_id, allow assigning any employee
                                assignBtn =
                                    '<a class="assign-employee-btn" href="javascript:void(0)" title="Assign Employee" data-id="' +
                                    row.id +
                                    '" data-dealership-id=""><i class="fa fa-user-plus text-primary"></i> </a>';
                            }

                            return '<ul class="action d-flex justify-content-around list-unstyled gap-4">' +
                                convertBtn + viewBtn + editBtn + deleteBtn + '</ul>';
                        }
                    },
                ],
            });
            @endif

            @if(checkMenu(Session::get('role_id'), 12, 'read'))
            unassignedLeadsTable = $("#unassigned-leads-table").DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: "{{ route('leads.index') }}",
                    data: function(d) {
                        d.status = $('#unassigned-filter-status').val();
                        d.has_followup = $('#unassigned-filter-followup').val();
                        d.lead_category_id = $('#unassigned-filter-category').val();
                        d.lead_source_id = $('#unassigned-filter-source').val();
                        d.dealership_id = $('#unassigned-filter-dealership').val();
                        d.from_date = $('#unassigned-filter-from-date').val();
                        d.to_date = $('#unassigned-filter-to-date').val();
                        d.search.value = $('#unassigned-leads-table_filter input').val();
                        d.employee_assignment_status =
                            'unassigned'; // Always unassigned for this table
                    }
                },

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
                        text: '<i class="fa fa-file-excel-o"></i> Full Excel',
                        className: 'btn btn-sm btn-info text-white',
                        action: function(e, dt, node, config) {
                            var params = dt.ajax.params();
                            var url = "{{ route('leads.export-excel') }}?" + $.param(params);
                            window.location.href = url;
                        }
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
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'td-name-contact',
                        orderable: true, // Set to true
                        render: function(data, type, row) {
                            var email = data.email ?
                                '<div class="badge rounded-pill bg-primary mt-1">' + data
                                .email +
                                '</div>' : '';
                            var phone = data.phone_number ?
                                '<div class="badge rounded-pill bg-info mt-1">' + data
                                .phone_number + '</div>' : '';
                            var clientBadge = row.is_client ?
                                '<div class="badge rounded-pill bg-success mt-1">Client</div>' :
                                '';
                            return '<div class="mb-1">' + data.salutation + ' ' + data
                                .name +
                                '</div>' + email + phone + clientBadge;
                        }
                    },

                    {
                        data: 'product',
                        name: 'product',
                        className: 'td-product-value',
                        orderable: true, // Set to true (for 'Value')
                        render: function(data, type, row) {
                            var leadValue = data.lead_value;
                            var items = data.items || [];
                            var productDisplay = '';

                            if (items.length > 0) {
                                var showItems = items.slice(0, 2);
                                showItems.forEach(function(item, index) {
                                    // Use product relation or product_name if available
                                    var productObj = item.product || item.product_name;
                                    var productName = 'N/A';
                                    if (typeof productObj === 'object' && productObj !== null) {
                                        productName = productObj.name || 'N/A';
                                    } else if (typeof productObj === 'string') {
                                        productName = productObj;
                                    }
                                    
                                    var modelObj = item.product_model || item.productModel || item.product_model_name;
                                    var modelName = '';
                                    if (typeof modelObj === 'object' && modelObj !== null) {
                                        modelName = ' - ' + (modelObj.name || '');
                                    } else if (typeof modelObj === 'string' && modelObj !== '') {
                                        modelName = ' - ' + modelObj;
                                    }
                                    
                                    productDisplay += '<div>' + productName + modelName + ' (x' + item.quantity + ')</div>';
                                });
                                if (items.length > 2) {
                                    productDisplay += '<div class="text-primary fw-bold">+' + (items.length - 2) + ' more</div>';
                                }
                            } else {
                                var product = data.primary_product;
                                var productModel = data.primary_product_model || row.product_model || row.productModel;
                                var productName = product ? product.name : 'N/A';
                                var productModelName = productModel ? ' - ' + productModel.name : '';
                                productDisplay = '<div>' + productName + productModelName + '</div>';
                            }

                            var formattedLeadValue = leadValue ?
                                '<div class="text-muted fw-bold">₹' +
                                leadValue + '</div>' : '';

                            return '<div class="text-center">' + productDisplay + formattedLeadValue + '</div>';
                        }
                    },
                    {
                        data: 'leadCategory.name',
                        name: 'leadCategory.name',
                        className: 'td-category-dealership',
                        orderable: false, // Set to false
                        render: function(data, type, row) {
                            var category = row.leadCategory ?
                                '<span class="badge bg-primary bg-opacity-50">' + row
                                .leadCategory
                                .name + '</span>' :
                                '<span class="badge bg-secondary bg-opacity-50">N/A</span>';
                            var dealership = row.dealership ?
                                '<span class="badge bg-info bg-opacity-50">' + row
                                .dealership.name +
                                '</span>' :
                                '<span class="badge bg-secondary bg-opacity-50">N/A</span>';
                            return '<div>' + category + '</div><div class="mt-1">' +
                                dealership +
                                '</div>';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        render: function(data, type, row) {
                            var statusColors = {
                                'pending': 'bg-warning text-dark',
                                'in progress': 'bg-info text-white',
                                'in_progress': 'bg-info text-white',
                                'win': 'bg-success text-white',
                                'lost': 'bg-danger text-white',
                                'positive': 'bg-primary text-white',
                                'converted_to_client': 'bg-success text-white'
                            };
                            var currentStatus = (row.status || '').toLowerCase().trim();
                            var colorClass = statusColors[currentStatus] || statusColors[currentStatus.replace('_', ' ')] || 'bg-secondary text-white';
                            var statusText = row.status ? row.status.toUpperCase() : 'PENDING';
                            return '<span class="badge ' + colorClass + '">' + statusText + '</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var viewBtn = '<a class="" title="View" href="/leads/' + row
                                .id +
                                '/profile"><i class="icon-eye text-success"></i></a>';
                            var editBtn = '';

                            editBtn =
                                '<a class="edit-lead-btn" href="javascript:void(0)" title="Edit" data-id="' +
                                row
                                .id + '"><i class="icon-pencil-alt text-success"></i></a>';

                            var deleteBtn = '';

                            deleteBtn =
                                '<a class="delete-lead-btn" href="javascript:void(0)" data-id="' +
                                row
                                .id + '" data-lead-name="' + row.name.name +
                                '"><i class="icon-trash text-danger"></i></a>';

                            var convertBtn = '';

                            @if(checkMenu(Session::get('role_id'), 14, 'create'))
                            if (row.status !== 'converted_to_client' && !row
                                .is_client) {
                                var isAssigned = row.assigned_employee && row
                                    .assigned_employee.trim() !== '';
                                convertBtn =
                                    '<a class="convert-to-client-btn" href="#" data-id="' +
                                    row.id +
                                    '" data-assigned="' + isAssigned +
                                    '"><i class="fa fa-user text-info"></i></a>';
                            }
                            @endif

                            var assignBtn = '';
                            @if(checkMenu(Session::get('role_id'), 13, 'create'))
                            // Only show assign button if the lead has a dealership_id
                            if (row.dealership_id) {
                                assignBtn =
                                    '<a class="assign-employee-btn" href="javascript:void(0)" title="Assign Employee" data-id="' +
                                    row.id + '" data-dealership-id="' + row
                                    .dealership_id +
                                    '"><i class="fa fa-user-plus text-primary"></i> </a>';
                            } else {
                                // If no dealership_id, allow assigning any employee
                                assignBtn =
                                    '<a class="assign-employee-btn" href="javascript:void(0)" title="Assign Employee" data-id="' +
                                    row.id +
                                    '" data-dealership-id=""><i class="fa fa-user-plus text-primary"></i> </a>';
                            }
                            @endif

                            return '<ul class="action d-flex justify-content-around list-unstyled gap-4">' +
                                assignBtn + convertBtn + viewBtn + editBtn + deleteBtn +
                                '</ul>';
                        }
                    },
                ],
            });
            @endif
        }

        initializeDataTables(); // Call this function on document ready


        $('#clientSearch').select2({
            placeholder: 'Search for a client by phone number',
            tags: true,
            ajax: {
                url: "{{ route('leads.search-clients-by-phone') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        phone_number: params.term // search term
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
                $('#salutation').val(data.salutation).prop('disabled', true);
                $('#leadName').val(data.name).prop('disabled', true);
                $('#leadEmail').val(data.email).prop('disabled', true);
                $('#isExistingClient').val(1);

                // Display history count
                $('#clientHistoryCount').text(data.leads_count || 0);

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

                $('#clientSearch').next('.select2-container').find('.select2-selection--single').css(
                    'background-color', '#d4edda');
            } else {
                $('#clientHistorySection').stop(true, true).slideUp();
            }
        }).on('select2:selecting', function(e) {
            var data = e.params.args.data;
            // If the user is selecting something that isn't an existing client
            if (!data.existing) {
                $('#salutation').val('Mr.').prop('disabled', false);
                $('#leadName').val('').prop('disabled', false);
                $('#leadEmail').val('').prop('disabled', false);
                $('#isExistingClient').val(0);
                $('#clientHistorySection').stop(true, true).slideUp();
                $('#clientSearch').next('.select2-container').find('.select2-selection--single').css(
                    'background-color', '');
            }
        });




        $('#filter-status, #filter-followup, #filter-category, #filter-source, #filter-dealership, #filter-from-date, #filter-to-date, #unassigned-filter-status, #unassigned-filter-followup, #unassigned-filter-category, #unassigned-filter-source, #unassigned-filter-dealership, #unassigned-filter-from-date, #unassigned-filter-to-date')
            .on('change', function() {
                if ($('#assigned-leads-tab').hasClass('active')) {
                    assignedLeadsTable.ajax.reload();
                } else if ($('#unassigned-leads-tab').hasClass('active')) {
                    unassignedLeadsTable.ajax.reload();
                }
            });

        // Handle tab changes to reload DataTable
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (e.target.id === 'assigned-leads-tab') {
                assignedLeadsTable.ajax.reload();
            } else if (e.target.id === 'unassigned-leads-tab') {
                unassignedLeadsTable.ajax.reload();
            }
        });

        function populateSerialInputs(items, containerId) {
            var $container = $(containerId);
            $container.empty();
            
            if (!items || items.length === 0) {
                $container.append('<p class="text-muted small mb-0 p-2">No specific items found for this lead.</p>');
                return;
            }

            var allUnits = [];
            items.forEach(function(item) {
                var qty = parseInt(item.quantity) || 1;
                for (var i = 1; i <= qty; i++) {
                    var label = item.product_name;
                    if (item.product_model_name) label += ' - ' + item.product_model_name;
                    if (qty > 1) label += ' (Unit ' + i + ')';
                    
                    allUnits.push({
                        itemId: item.id,
                        unit: i,
                        label: label
                    });
                }
            });

            var totalUnits = allUnits.length;
            var now = new Date();
            var monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
            var monthYear = monthNames[now.getMonth()] + "-" + now.getFullYear();
            
            allUnits.forEach(function(unitData, index) {
                var isHidden = index === 0 ? '' : 'd-none';
                var html = `
                    <div class="unit-step ${isHidden}" data-step="${index}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="small fw-bold text-primary mb-0">${unitData.label}</h6>
                            <span class="badge bg-secondary-subtle text-secondary border small">Unit ${index + 1} of ${totalUnits}</span>
                        </div>
                        <div class="p-3 border rounded bg-white shadow-xs">
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold mb-1">Machine Serial Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm item-machine-serial" 
                                        data-item-id="${unitData.itemId}" data-unit="${unitData.unit}" 
                                        placeholder="Enter Machine Serial" value="${monthYear}" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold mb-1">Engine Serial Number</label>
                                    <input type="text" class="form-control form-control-sm item-engine-serial" 
                                        data-item-id="${unitData.itemId}" data-unit="${unitData.unit}" placeholder="Enter Engine Serial">
                                </div>
                                <div class="col-md-12">
                                    <label class="small fw-bold mb-1">Engine Model</label>
                                    <input type="text" class="form-control form-control-sm item-engine-model" 
                                        data-item-id="${unitData.itemId}" data-unit="${unitData.unit}" placeholder="Enter Engine Model">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $container.append(html);
            });

            if (totalUnits > 1) {
                var navHtml = `
                    <div class="d-flex justify-content-between mt-3 unit-nav">
                        <button type="button" class="btn btn-xs btn-outline-secondary prev-unit" disabled>
                            <i class="fa fa-chevron-left me-1"></i> Previous
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-primary next-unit">
                            Next <i class="fa fa-chevron-right ms-1"></i>
                        </button>
                    </div>
                `;
                $container.append(navHtml);
            }
        }

        // Unit Navigation Handlers
        $(document).on('click', '.next-unit', function() {
            var $container = $(this).closest('[id$="_serial_numbers_container"]');
            var $currentStep = $container.find('.unit-step:not(.d-none)');
            var nextStepIdx = parseInt($currentStep.data('step')) + 1;
            var $nextStep = $container.find(`.unit-step[data-step="${nextStepIdx}"]`);

            if ($nextStep.length) {
                $currentStep.addClass('d-none');
                $nextStep.removeClass('d-none');
                
                $container.find('.prev-unit').prop('disabled', false);
                if (!$container.find(`.unit-step[data-step="${nextStepIdx + 1}"]`).length) {
                    $(this).prop('disabled', true);
                }
            }
        });

        $(document).on('click', '.prev-unit', function() {
            var $container = $(this).closest('[id$="_serial_numbers_container"]');
            var $currentStep = $container.find('.unit-step:not(.d-none)');
            var prevStepIdx = parseInt($currentStep.data('step')) - 1;
            var $prevStep = $container.find(`.unit-step[data-step="${prevStepIdx}"]`);

            if ($prevStep.length) {
                $currentStep.addClass('d-none');
                $prevStep.removeClass('d-none');
                
                $container.find('.next-unit').prop('disabled', false);
                if (prevStepIdx === 0) {
                    $(this).prop('disabled', true);
                }
            }
        });

        // Updated manual convert click handlers
        $('#leads-table').on('click', '.convert-to-client-btn', function(e) {
            e.preventDefault();
            leadIdToConvert = $(this).data('id');
            
            $.get('/leads/' + leadIdToConvert, function(data) {
                var currentMonth = new Date().toISOString().substring(0, 7);
                $('#conv_billing').val(data.billing ? data.billing.substring(0, 7) : currentMonth);
                var today = new Date().toISOString().split('T')[0];
                $('#conv_doc').val(data.doc || today);
                populateSerialInputs(data.items, '#conv_serial_numbers_container');
                $('#convertConfirmationMessage').text("Are you sure you want to convert this lead to a client? This will mark the lead status as 'Win' and set the progress to 100%.");
                $('#confirmConvertToClientModal').modal('show');
            });
        });

        $('#unassigned-leads-table').on('click', '.convert-to-client-btn', function(e) {
            e.preventDefault();
            leadIdToConvert = $(this).data('id');
            var isAssigned = $(this).data('assigned');
            
            $.get('/leads/' + leadIdToConvert, function(data) {
                var currentMonth = new Date().toISOString().substring(0, 7);
                $('#conv_billing').val(data.billing ? data.billing.substring(0, 7) : currentMonth);
                var today = new Date().toISOString().split('T')[0];
                $('#conv_doc').val(data.doc || today);
                populateSerialInputs(data.items, '#conv_serial_numbers_container');
                
                var message = "Are you sure you want to convert this lead to a client? This will mark the lead status as 'Win' and set the progress to 100%.";
                if (isAssigned === false || isAssigned === 'false') {
                    message += ' This lead is currently unassigned. Please ensure it is assigned to an employee.';
                }
                $('#convertConfirmationMessage').text(message);
                $('#confirmConvertToClientModal').modal('show');
            });
        });

        $('#confirmConvertToClientBtn').on('click', function() {
            var billing = $('#conv_billing').val();
            var doc = $('#conv_doc').val();
            
            // --- Validation ---
            var isValid = true;
            $('#conv_billing, #conv_doc').removeClass('is-invalid');
            
            if (!billing) {
                $('#conv_billing').addClass('is-invalid');
                isValid = false;
            }
            if (!doc) {
                $('#conv_doc').addClass('is-invalid');
                isValid = false;
            }

            var itemDetails = {};
            $('#conv_serial_numbers_container .item-machine-serial').each(function() {
                $(this).removeClass('is-invalid');
                var mSerial = $(this).val().trim();
                var itemId = $(this).data('item-id');
                var unit = $(this).data('unit');
                
                if (!mSerial) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                }
                
                var eSerial = $(this).closest('.row').find('.item-engine-serial').val().trim();
                var eModel = $(this).closest('.row').find('.item-engine-model').val().trim();

                if (!itemDetails[itemId]) itemDetails[itemId] = [];
                itemDetails[itemId].push({
                    machine_serial: mSerial,
                    engine_serial: eSerial,
                    engine_model: eModel
                });
            });

            if (!isValid) return;
            // --- End Validation ---

            $.ajax({
                url: '/leads/' + leadIdToConvert + '/convert',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    billing: billing,
                    doc: doc,
                    item_details: itemDetails
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    if ($('#assigned-leads-tab').hasClass('active')) {
                        assignedLeadsTable.ajax.reload();
                    } else if ($('#unassigned-leads-tab').hasClass('active')) {
                        unassignedLeadsTable.ajax.reload();
                    }
                    $('#confirmConvertToClientModal').modal('hide');
                    $('#conversionDetailsForm')[0].reset();
                    $('#conv_serial_numbers_container').empty();
                },
                error: function(error) {
                    console.error('Error:', error);
                    var errorMessage = 'An unexpected error occurred.';
                    if (error.responseJSON && error.responseJSON.error) {
                        errorMessage = error.responseJSON.error;
                    } else if (error.responseText) {
                        errorMessage = error.responseText;
                    }
                    showToast(errorMessage, 'danger');
                    $('#confirmConvertToClientModal').modal('hide');
                }
            });
        });

        // Handle Lead Form Submission
        $('#createLeadForm').on('submit', function(e) {
            e.preventDefault();

            // Re-enable disabled fields before submission
            $('#salutation').prop('disabled', false);
            $('#leadName').prop('disabled', false);
            $('#leadEmail').prop('disabled', false);

            // --- Validation Start ---
            var isValid = true;
            $('.form-control, .form-select').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            // Name validation
            var leadName = $('#leadName').val().trim();
            if (leadName === '') {
                $('#leadName').addClass('is-invalid');
                $('#leadName').next('.invalid-feedback').text('Name is required.');
                isValid = false;
            }

            // Email validation
            var leadEmail = $('#leadEmail').val().trim();
            if (leadEmail !== '') {
                var emailRegex = /^[^@]+@[^@]+\.[^@]+$/;
                if (!emailRegex.test(leadEmail)) {
                    $('#leadEmail').addClass('is-invalid');
                    $('#leadEmail').next('.invalid-feedback').text(
                        'Please enter a valid email address.');
                    isValid = false;
                }
            }

            // Agent validation
            var agentName = $('#agent').val().trim();
            var selectedAgentId = '';
            $('#agent-list option').each(function() {
                if ($(this).val() === agentName) {
                    selectedAgentId = $(this).data('id');
                    return false;
                }
            });

            if (agentName !== '' && !selectedAgentId) {
                $('#agent').addClass('is-invalid');
                $('#agent').closest('.input-group').find('.invalid-feedback').text(
                    'Please select a valid agent from the list.');
                isValid = false;
            }

            if (!isValid) {
                var firstInvalid = $('.is-invalid').first();
                if (firstInvalid.length) {
                    $('html, body').animate({
                        scrollTop: firstInvalid.offset().top - 100
                    }, 500);
                    firstInvalid.focus();
                }
                return; // Stop submission if validation fails
            }
            // --- Validation End ---

            var formData = new FormData(this);
            var selectedAgentType = '';
            var agentInput = $('#agent').val();

            // Find the selected option in the datalist
            $('#agent-list option').each(function() {
                if ($(this).val() === agentInput) {
                    selectedAgentId = $(this).data('id');
                    selectedAgentType = $(this).data('type');
                    return false; // Exit the loop once found
                }
            });

            if (selectedAgentId && selectedAgentType) {
                formData.append('agent_id', selectedAgentId);
                formData.append('agent_type', selectedAgentType);
            } else if (agentName !== '') {
                // This case should be prevented by validation, but as a fallback
                $('#agent').addClass('is-invalid');
                $('#agent').closest('.input-group').find('.invalid-feedback').text(
                    'Invalid agent selected.');
                return;
            }

            $.ajax({
                url: "{{ route('leads.store') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    if ($('#assigned-leads-tab').hasClass('active')) {
                        assignedLeadsTable.ajax.reload();
                    } else if ($('#unassigned-leads-tab').hasClass('active')) {
                        unassignedLeadsTable.ajax.reload();
                    }
                    // Clear specific fields instead of resetting the entire form
                    $('#salutation').val('Mr.').prop('disabled', false);
                    $('#leadName').val('').prop('disabled', false);
                    $('#leadCompany').val('').prop('disabled', false);
                    $('#leadEmail').val('').prop('disabled', false);
                    $('#leadAlternateContact').val(''); // Reset alternate contact number
                    $('#isExistingClient').val(0);
                    $('#location').val('');
                    $('#agent').val('');
                    $('#leadSource').val('');
                    $('#leadCategory').val('');
                    $('#leadValue').val('');
                    $('#allowFollowUp').val('1');
                    $('#product').val('');
                    $('#productModel').val('');
                    $('#modelSeries').val('');
                    $('#quantity').val('1');
                    $('#financier').val('');
                    $('#editType').val('');
                    $('#loginStatus').val('');
                    $('#stage').val('');
                    $('#remarks').val('');
                    $('#dealership').val('');
                    $('#assignedEmployee').val(null).trigger('change');

                    // Reset Select2 for clientSearch
                    $('#clientSearch').val(null).trigger('change');
                    $('#clientSearch').next('.select2-container').find(
                        '.select2-selection--single').css('background-color', '');


                    $('.form-control, .form-select').removeClass(
                        'is-invalid'); // Clear validation classes on success
                    $('.invalid-feedback').text('');
                    var viewTab = new bootstrap.Tab(document.getElementById('view-tab'));
                    viewTab.show();
                },
                error: function(error) {
                    console.error('Error:', error);
                    // This part can now be simplified if we trust client-side validation,
                    // but it's good to keep for server-side error handling.
                    var errorMessage = 'An unexpected error occurred.';
                    if (error.responseJSON && error.responseJSON.errors) {
                        for (var key in error.responseJSON.errors) {
                            if (error.responseJSON.errors.hasOwnProperty(key)) {
                                var fieldName = key;
                                // Handle array field names if any (e.g., items[0][name]) - simple replacement for now
                                var errorText = error.responseJSON.errors[key][0];

                                // Find the input field
                                var inputField = $('#createLeadForm').find('[name="' + fieldName + '"]');
                                // Fallback for datalist inputs where id might match field name but name attribute is different or absent in direct search
                                if (inputField.length === 0) {
                                    inputField = $('#' + fieldName);
                                }

                                if (inputField.length > 0) {
                                    inputField.addClass('is-invalid');
                                    inputField.next('.invalid-feedback').text(errorText); // Assumes invalid-feedback is immediately after
                                    // Or try finding closest if next() fails or structure is differen
                                    if (inputField.next('.invalid-feedback').length === 0) {
                                        inputField.closest('.input-group').find('.invalid-feedback').text(errorText);
                                    }
                                } else {
                                    // If field not found, add to general error messages
                                    errorMessages.push(errorText);
                                }
                            }
                        }
                        if (errorMessages.length > 0) {
                            errorMessage = errorMessages.join('<br>');
                            showToast(errorMessage, 'danger');
                        }
                    } else if (error.responseText) {
                        // ... existing fallback ...
                        errorMessage = error.responseText;
                        showToast(errorMessage, 'danger');
                    } else {
                        showToast(errorMessage, 'danger');
                    }
                }
            });
        });

        // Dynamic Add buttons (placeholders for now)
        $('#addAgentBtn').on('click', function() {
            var agentName = $('#agent').val();
            if (agentName) {
                // Check if the agent already exists in the datalist (either as employee or non-employee agent)
                var existingOption = $('#agent-list option[value="' + agentName + '"]');
                if (existingOption.length > 0) {
                    showToast('Agent with this name already exists.', 'warning');
                    return;
                }

                // If not existing, create as a non-employee agent
                $.ajax({
                    url: "{{ route('agents.store') }}", // New route for storing non-employee agents
                    method: 'POST',
                    data: {
                        name: agentName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#agent-list').append('<option value="' + response.agent.name +
                            '" data-id="' + response.agent.id +
                            '" data-type="App\\Models\\Agent">');
                        $('#agent').val(response.agent.name);
                        showToast('Non-employee agent added successfully', 'success');
                    },
                    error: function(error) {
                        if (error.responseJSON && error.responseJSON.errors) {
                            var errorMessages = [];
                            for (var key in error.responseJSON.errors) {
                                if (error.responseJSON.errors.hasOwnProperty(key)) {
                                    errorMessages.push(error.responseJSON.errors[key][0]);
                                }
                            }
                            showToast(errorMessages.join('<br>'), 'danger');
                        } else {
                            showToast('Error adding non-employee agent.', 'danger');
                        }
                    }
                });
            }
        });

        $('#addLeadSourceBtn').on('click', function() {
            var sourceName = $('#leadSource').val();
            if (sourceName) {
                $.ajax({
                    url: "{{ route('lead-sources.store') }}",
                    method: 'POST',
                    data: {
                        name: sourceName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#lead-source-list').append('<option value="' + response.name +
                            '">');
                        $('#leadSource').val(response.name);
                        showToast('Lead Source added successfully', 'success');
                    },
                    error: function(error) {
                        if (error.responseJSON && error.responseJSON.errors) {
                            var errorMessages = [];
                            for (var key in error.responseJSON.errors) {
                                if (error.responseJSON.errors.hasOwnProperty(key)) {
                                    errorMessages.push(error.responseJSON.errors[key][0]);
                                }
                            }
                            showToast(errorMessages.join('<br>'), 'danger');
                        } else {
                            showToast('Error adding lead source.', 'danger');
                        }
                    }
                });
            }
        });

        $('#addLeadCategoryBtn').on('click', function() {
            var categoryName = $('#leadCategory').val();
            if (categoryName) {
                $.ajax({
                    url: "{{ route('lead-categories.store') }}",
                    method: 'POST',
                    data: {
                        name: categoryName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#lead-category-list').append('<option value="' + response.name +
                            '">');
                        $('#leadCategory').val(response.name);
                        showToast('Lead Category added successfully', 'success');
                    },
                    error: function(error) {
                        if (error.responseJSON && error.responseJSON.errors) {
                            var errorMessages = [];
                            for (var key in error.responseJSON.errors) {
                                if (error.responseJSON.errors.hasOwnProperty(key)) {
                                    errorMessages.push(error.responseJSON.errors[key][0]);
                                }
                            }
                            showToast(errorMessages.join('<br>'), 'danger');
                        } else {
                            showToast('Error adding lead category.', 'danger');
                        }
                    }
                });
            }
        });

        $('#addProductBtn').on('click', function() {
            var productName = $('#product').val();
            if (productName) {
                $.ajax({
                    url: "{{ route('products.store-dynamic') }}",
                    method: 'POST',
                    data: {
                        name: productName,
                        price: 0,
                        category: 'Default',
                        sub_category: 'Default',
                        _token: '{{ csrf_token() }}'
                    }, // Provide default values for required fields
                    success: function(response) {
                        $('#product-list').append('<option value="' + response.name + '">');
                        $('#product').val(response.name);
                        showToast('Product added successfully', 'success');
                    },
                    error: function(error) {
                        if (error.responseJSON && error.responseJSON.errors) {
                            var errorMessages = [];
                            for (var key in error.responseJSON.errors) {
                                if (error.responseJSON.errors.hasOwnProperty(key)) {
                                    errorMessages.push(error.responseJSON.errors[key][0]);
                                }
                            }
                            showToast(errorMessages.join('<br>'), 'danger');
                        } else {
                            showToast('Error adding product.', 'danger');
                        }
                    }
                });
            }
        });

        // Load Agents (brokers)
        function loadAgents() {
            var datalist = $('#agent-list');
            datalist.empty(); // Clear existing options

            $.ajax({
                url: "{{ route('agents.index') }}", // Use the web version route
                method: 'GET',
                success: function(response) {
                    $.each(response.data, function(index, agent) {
                        datalist.append('<option value="' + agent.display_name +
                            '" data-id="' +
                            agent.id + '" data-type="' + agent.type + '">' +
                            agent.display_name + '</option>');
                    });
                },
                error: function(error) {
                    console.error('Error loading agents:', error);
                }
            });
        }

        loadAgents(); // Initial load for Create Lead form

        // Load data for Create Lead form datalists
        loadLeadSources('#lead-source-list');
        loadLeadCategories('#lead-category-list');
        loadProducts('#product-list');

        let productItemIndex = 0;

        function addProductItem(container, data = null) {
            const template = document.getElementById('product-item-template');
            const clone = template.content.cloneNode(true);
            const index = productItemIndex++;

            // Update names and IDs with the index
            clone.querySelectorAll('[name*="INDEX"]').forEach(el => {
                el.name = el.name.replace('INDEX', index);
            });
            clone.querySelectorAll('[list*="INDEX"]').forEach(el => {
                el.setAttribute('list', el.getAttribute('list').replace('INDEX', index));
            });
            clone.querySelectorAll('[id*="INDEX"]').forEach(el => {
                el.id = el.id.replace('INDEX', index);
            });

            const itemRow = clone.querySelector('.product-item');
            $(container).append(itemRow);

            const $row = $(container).find('.product-item').last();

            if (data) {
                $row.find('.product-input').val(data.product_name || '');
                $row.find('.model-input').val(data.product_model_name || '');
                $row.find('.qty-input').val(data.quantity || 1);
                $row.find('.price-input').val(data.price || 0);

                if (data.product_id) {
                    loadProductModelsForItem($row, data.product_id, data.product_model_name);
                }
            }

            return $row;
        }

        function updateTotalLeadValue($form) {
            let total = 0;
            $form.find('.product-item').each(function() {
                const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                const price = parseFloat($(this).find('.price-input').val()) || 0;
                total += qty * price;
            });
            $form.find('[name="lead_value"]').val(total.toFixed(2));
        }

        function loadProductModelsForItem($row, productId, selectedValue = null) {
            const datalist = $row.find('.model-input').attr('list');
            const $datalist = $(`#${datalist}`);
            $datalist.empty();

            if (productId) {
                $.ajax({
                    url: "{{ route('about-products.product-models.by-product') }}",
                    method: 'GET',
                    data: {
                        product_id: productId
                    },
                    success: function(response) {
                        $.each(response, function(index, model) {
                            $datalist.append(`<option value="${model.name}" data-id="${model.id}" data-price="${model.price || 0}">`);
                        });
                        if (selectedValue) {
                            $row.find('.model-input').val(selectedValue);
                        }
                    }
                });
            }
        }

        $(document).on('click', '#add-product-item-btn', function() {
            addProductItem('#product-items-container');
        });

        $(document).on('click', '#edit-add-product-item-btn', function() {
            addProductItem('#edit-product-items-container');
        });

        $(document).on('click', '.remove-product-item', function() {
            const $form = $(this).closest('form');
            $(this).closest('.product-item').remove();
            updateTotalLeadValue($form);
        });

        $(document).on('change', '.product-input', function() {
            const $row = $(this).closest('.product-item');
            const productName = $(this).val();
            let productId = null;

            $('#product-list option').each(function() {
                if ($(this).val() === productName) {
                    productId = $(this).data('id');
                    return false;
                }
            });

            $row.find('.model-input').val('');
            $row.find('.price-input').val(0);
            loadProductModelsForItem($row, productId);
            updateTotalLeadValue($(this).closest('form'));
        });

        $(document).on('change', '.model-input', function() {
            const $row = $(this).closest('.product-item');
            const modelName = $(this).val();
            const datalistId = $(this).attr('list');
            let modelId = null;
            let price = 0;

            $(`#${datalistId} option`).each(function() {
                if ($(this).val() === modelName) {
                    modelId = $(this).data('id');
                    price = $(this).data('price');
                    return false;
                }
            });

            $row.find('.price-input').val(price);
            updateTotalLeadValue($(this).closest('form'));
        });

        $(document).on('input', '.qty-input', function() {
            updateTotalLeadValue($(this).closest('form'));
        });

        // Initialize with one empty product item for Create form
        addProductItem('#product-items-container');



        // Edit Lead Modal
        $('#leads-table').on('click', '.edit-lead-btn', function() {
            var leadId = $(this).data('id');
            $.ajax({
                url: '/leads/' + leadId,
                method: 'GET',
                success: function(data) {

                    $('#editLeadId').val(data.id);
                    // editLeadValue
                    $('#editLeadValue').val(data.lead_value || 0);
                    $('#editSalutation').val(data.salutation);
                    $('#editLeadName').val(data.name);
                    $('#editLeadCompany').val(data.company);
                    $('#editLeadEmail').val(data.email);
                    $('#editLeadPhone').val(data.phone_number);
                    $('#editLeadAlternateContact').val(data.alternate_contact_number);
                    $('#editAgent').val(data.agent ? data.agent.name : '');
                    $('#editLeadSource').val(data.lead_source ? data.lead_source.name : '');
                    $('#editLeadCategory').val(data.lead_category ? data.lead_category
                        .name : '');
                    $('#editAllowFollowUp').val(data.allow_follow_up ? 1 : 0);

                    // Handle multiple products in edit modal
                    $('#edit-product-items-container').empty();
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            addProductItem('#edit-product-items-container', {
                                product_id: item.product_id,
                                product_name: item.product_name,
                                product_model_id: item.product_model_id,
                                product_model_name: item.product_model_name,
                                quantity: item.quantity,
                                price: item.price
                            });
                        });
                    } else {
                        // Fallback to primary product columns if items are empty (backward compatibility)
                        addProductItem('#edit-product-items-container', {
                            product_id: data.product_id,
                            product_name: data.product ? data.product.name : '',
                            product_model_id: data.product_model_id,
                            product_model_name: data.product_model ? data.product_model.name : '',
                            quantity: data.quantity,
                            price: data.lead_value / (data.quantity || 1)
                        });
                    }

                    //editLocation input select menu set select by option name
                    if (data.location) {
                        $('#editLocation').val(data.location).trigger('change');
                    } else {
                        $('#editLocation').val('');
                    }
                    $('#editDealership').val(data.dealership_id);
                    $('#editQuantity').val(data.quantity);
                    $('#editFinancier').val(data.financier);
                    $('#editLoginStatus').val(data.login_status);
                    $('#editStage').val(data.stage);
                    $('#editType1').val(data.type);
                    $('#editBilling').val(data.billing);
                    $('#editRemarks').val(data.remarks);
                    // Initialize and set map for edit modal
                    var initialLat = parseFloat(data.latitude);
                    var initialLng = parseFloat(data.longitude);
                    var initialAddress = data.map_location || '';

                    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                        initEditMap(
                            isFinite(initialLat) ? initialLat : 20.5937,
                            isFinite(initialLng) ? initialLng : 78.9629,
                            initialAddress
                        );
                    }

                    $('#editLeadModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching lead data for edit:', error);
                    showToast('Error fetching lead data for edit.', 'danger');
                }
            });
        });



        // Edit Lead Modal for unassigned leads table
        $('#unassigned-leads-table').on('click', '.edit-lead-btn', function() {
            var leadId = $(this).data('id');
            $.ajax({
                url: '/leads/' + leadId,
                method: 'GET',
                success: function(data) {

                    if (data.location) {
                        $('#editLocation').val(data.location).trigger('change');
                    } else {
                        $('#editLocation').val('');
                    }
                    $('#editLeadId').val(data.id);
                    //editLeadValue
                    $('#editLeadValue').val(data.lead_value || 0);
                    $('#editLeadName').val(data.name ? data.name : '');
                    $('#editLeadEmail').val(data.email);
                    $('#editLeadPhone').val(data.phone_number);
                    $('#editLeadAlternateContact').val(data.alternate_contact_number);
                    $('#editAllowFollowUp').val(data.allow_follow_up ? 1 : 0);
                    $('#editDealership').val(data.dealership_id);
                    $('#editQuantity').val(data.quantity);
                    $('#editFinancier').val(data.financier);
                    $('#editLoginStatus').val(data.login_status);
                    $('#editStage').val(data.stage);
                    $('#editType1').val(data.type);
                    $('#editBilling').val(data.billing);
                    $('#editRemarks').val(data.remarks);

                    // Handle multiple products in edit modal for unassigned leads
                    $('#edit-product-items-container').empty();
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            addProductItem('#edit-product-items-container', {
                                product_id: item.product_id,
                                product_name: item.product_name,
                                product_model_id: item.product_model_id,
                                product_model_name: item.product_model_name,
                                quantity: item.quantity,
                                price: item.price
                            });
                        });
                    } else {
                        // Fallback to primary product columns if items are empty (backward compatibility)
                        addProductItem('#edit-product-items-container', {
                            product_id: data.product_id,
                            product_name: data.product ? data.product.name : '',
                            product_model_id: data.product_model_id,
                            product_model_name: data.product_model ? data.product_model.name : '',
                            quantity: data.quantity,
                            price: data.lead_value / (data.quantity || 1)
                        });
                    }

                    $('#editLocation').val(data.location ? data.location : '');

                    // Initialize and set map for edit modal: store initial values on modal so
                    // the map and autocomplete are initialized when modal is actually shown.
                    var initialLat = data.latitude || 20.5937;
                    var initialLng = data.longitude || 78.9629;
                    var initialAddress = data.map_location || '';

                    if (typeof google !== 'undefined' && typeof google.maps !==
                        'undefined') {
                        $('#editLeadModal').data('initLat', data.latitude);
                        $('#editLeadModal').data('initLng', data.longitude);
                        $('#editLeadModal').data('initAddress', initialAddress);
                    }

                    $('#editLeadModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching lead data for edit:', error);
                    showToast('Error fetching lead data for edit.', 'danger');
                }
            });
        });

        // Handle Edit Lead Form Submission
        $('#editLeadForm').on('submit', function(e) {
            e.preventDefault();
            var leadId = $('#editLeadId').val();

            var formData = new FormData(this);
            var selectedAgentId = '';
            var selectedAgentType = '';
            var agentInput = $('#editAgent').val();

            // Find the selected option in the datalist
            $('#edit-agent-list option').each(function() {
                if ($(this).val() === agentInput) {
                    selectedAgentId = $(this).data('id');
                    selectedAgentType = $(this).data('type');
                    return false; // Exit the loop once found
                }
            });

            if (selectedAgentId && selectedAgentType) {
                formData.append('agent_id', selectedAgentId);
                formData.append('agent_type', selectedAgentType);
            } else {
                formData.append('agent_id', '');
                formData.append('agent_type', '');
            }



            $.ajax({
                url: '/leads/' + leadId,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    if ($('#assigned-leads-tab').hasClass('active')) {
                        assignedLeadsTable.ajax.reload();
                    } else if ($('#unassigned-leads-tab').hasClass('active')) {
                        unassignedLeadsTable.ajax.reload();
                    }
                    $('#editLeadModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating lead:', error);
                    showToast('Error updating lead.', 'danger');
                }
            });
        });

        // Delete Lead Modal
        $('#leads-table').on('click', '.delete-lead-btn', function() {
            var leadId = $(this).data('id');
            var leadName = $(this).data('lead-name');
            $('#deleteLeadId').val(leadId);
            $('#deleteLeadName').text(leadName);
            $('#deleteLeadModal').modal('show');
        });

        // Delete Lead Modal for unassigned leads table
        $('#unassigned-leads-table').on('click', '.delete-lead-btn', function() {
            var leadId = $(this).data('id');
            var leadName = $(this).data('lead-name');
            $('#deleteLeadId').val(leadId);
            $('#deleteLeadName').text(leadName);
            $('#deleteLeadModal').modal('show');
        });

        // Handle Delete Lead Confirmation
        $('#confirmDeleteLead').on('click', function() {
            var leadId = $('#deleteLeadId').val();
            $.ajax({
                url: '/leads/' + leadId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    if ($('#assigned-leads-tab').hasClass('active')) {
                        assignedLeadsTable.ajax.reload();
                    } else if ($('#unassigned-leads-tab').hasClass('active')) {
                        unassignedLeadsTable.ajax.reload();
                    }
                    $('#deleteLeadModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting lead:', error);
                    showToast('Error deleting lead.', 'danger');
                }
            });
        });

        function loadAgentsforEdit(targetDatalistId, selectedValue = null) {
            var datalist = $(targetDatalistId);
            datalist.empty(); // Clear existing options

            // Fetch employee-brokers
            $.ajax({
                url: "{{ route('employees.all') }}",
                method: 'GET',
                success: function(response) {
                    $.each(response, function(index, employee) {
                        var display_name = employee.name;
                        if (employee.employee_id) {
                            display_name += ' (' + employee.employee_id + ')';
                        }
                        datalist.append('<option value="' + employee.name + '" data-id="' +
                            employee.id + '" data-type="App\\Models\\Employee">' +
                            display_name + '</option>');
                    });
                    if (selectedValue) {
                        // Check if the selectedValue matches an employee name
                        var selectedOption = datalist.find('option[value="' + selectedValue +
                            '"][data-type="App\\Models\\Employee"]');
                        if (selectedOption.length > 0) {
                            $(targetDatalistId).prev('input').val(selectedValue);
                        }
                    }
                },
                error: function(error) {
                    console.error('Error loading employee agents for edit:', error);
                }
            });

            // Fetch non-employee agents
            $.ajax({
                url: "{{ route('agents.list') }}",
                method: 'GET',
                success: function(response) {
                    $.each(response.data, function(index, agent) {
                        datalist.append('<option value="' + agent.name + '" data-id="' +
                            agent.id + '" data-type="App\\Models\\Agent">');
                    });
                    if (selectedValue) {
                        // Check if the selectedValue matches a non-employee agent name
                        var selectedOption = datalist.find('option[value="' + selectedValue +
                            '"][data-type="App\\Models\\Agent"]');
                        if (selectedOption.length > 0) {
                            $(targetDatalistId).prev('input').val(selectedValue);
                        }
                    }
                },
                error: function(error) {
                    console.error('Error loading non-employee agents for edit:', error);
                }
            });
        }

        // Helper functions to load dynamic data for edit modals
        function loadLeadSources(targetDatalistId, selectedValue = null) {
            $.ajax({
                url: "{{ route('lead-sources.index') }}",
                method: 'GET',
                success: function(response) {
                    var datalist = $(targetDatalistId);
                    datalist.empty();
                    $.each(response.data, function(index, source) {
                        datalist.append('<option value="' + source.name + '">');
                    });
                    if (selectedValue) {
                        $(targetDatalistId).prev('input').val(selectedValue);
                    }
                }
            });
        }

        function loadLeadCategories(targetDatalistId, selectedValue = null) {
            $.ajax({
                url: "{{ route('lead-categories.index') }}",
                method: 'GET',
                success: function(response) {
                    var datalist = $(targetDatalistId);
                    datalist.empty();
                    $.each(response.data, function(index, category) {
                        datalist.append('<option value="' + category.name + '">');
                    });
                    if (selectedValue) {
                        $(targetDatalistId).prev('input').val(selectedValue);
                    }
                }
            });
        }

        function loadProducts(targetDatalistId, selectedValue = null) {
            $.ajax({
                url: "{{ route('products.list') }}", // Changed to the new route
                method: 'GET',
                success: function(response) {
                    var datalist = $(targetDatalistId);
                    datalist.empty();
                    $.each(response.data, function(index, product) {
                        datalist.append('<option value="' + product.name + '" data-id="' +
                            product.id + '">');
                    });
                    if (selectedValue) {
                        $(targetDatalistId).prev('input').val(selectedValue);
                    }
                }
            });
        }

        function loadProductModels(targetDatalistId, selectedValue = null) {
            $.ajax({
                url: "{{ route('about-products.product-models.data') }}",
                method: 'GET',
                success: function(response) {
                    var datalist = $(targetDatalistId);
                    datalist.empty();
                    $.each(response.data, function(index, productModel) {
                        datalist.append('<option value="' + productModel.name +
                            '" data-id="' +
                            productModel.id + '">');
                    });
                    if (selectedValue) {
                        $(targetDatalistId).prev('input').val(selectedValue);
                    }
                }
            });
        }

        // Dynamic Add buttons for edit modals
        $('#editAddAgentBtn').on('click', function() {
            var agentName = $('#editAgent').val();
            if (agentName) {
                // Check if the agent already exists in the datalist (either as employee or non-employee agent)
                var existingOption = $('#edit-agent-list option[value="' + agentName + '"]');
                if (existingOption.length > 0) {
                    showToast('Agent with this name already exists.', 'warning');
                    return;
                }

                // If not existing, create as a non-employee agent
                $.ajax({
                    url: "{{ route('agents.store') }}", // New route for storing non-employee agents
                    method: 'POST',
                    data: {
                        name: agentName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#edit-agent-list').append('<option value="' + response.agent
                            .name + '" data-id="' + response.agent.id +
                            '" data-type="App\\Models\\Agent">');
                        $('#editAgent').val(response.agent.name);
                        showToast('Non-employee agent added successfully', 'success');
                    },
                    error: function(error) {
                        if (error.responseJSON && error.responseJSON.errors) {
                            var errorMessages = [];
                            for (var key in error.responseJSON.errors) {
                                if (error.responseJSON.errors.hasOwnProperty(key)) {
                                    errorMessages.push(error.responseJSON.errors[key][0]);
                                }
                            }
                            showToast(errorMessages.join('<br>'), 'danger');
                        } else {
                            showToast('Error adding non-employee agent.', 'danger');
                        }
                    }
                });
            }
        });

        $('#editAddLeadSourceBtn').on('click', function() {
            var sourceName = $('#editLeadSource').val();
            if (sourceName) {
                $.ajax({
                    url: "{{ route('lead-sources.store') }}",
                    method: 'POST',
                    data: {
                        name: sourceName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#edit-lead-source-list').append('<option value="' + response
                            .name + '">');
                        showToast('Lead Source added successfully', 'success');
                    },
                    error: function(error) {
                        if (error.responseJSON && error.responseJSON.errors) {
                            var errorMessages = [];
                            for (var key in error.responseJSON.errors) {
                                if (error.responseJSON.errors.hasOwnProperty(key)) {
                                    errorMessages.push(error.responseJSON.errors[key][0]);
                                }
                            }
                            showToast(errorMessages.join('<br>'), 'danger');
                        } else {
                            showToast('Error adding lead source.', 'danger');
                        }
                    }
                });
            }
        });

        $('#editAddLeadCategoryBtn').on('click', function() {
            var categoryName = $('#editLeadCategory').val();
            if (categoryName) {
                $.ajax({
                    url: "{{ route('lead-categories.store') }}",
                    method: 'POST',
                    data: {
                        name: categoryName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#edit-lead-category-list').append('<option value="' + response
                            .name + '">');
                        showToast('Lead Category added successfully', 'success');
                    },
                    error: function(error) {
                        if (error.responseJSON && error.responseJSON.errors) {
                            var errorMessages = [];
                            for (var key in error.responseJSON.errors) {
                                if (error.responseJSON.errors.hasOwnProperty(key)) {
                                    errorMessages.push(error.responseJSON.errors[key][0]);
                                }
                            }
                            showToast(errorMessages.join('<br>'), 'danger');
                        } else {
                            showToast('Error adding lead category.', 'danger');
                        }
                    }
                });
            }
        });

        $('#editAddProductBtn').on('click', function() {
            var productName = $('#editProduct').val();
            if (productName) {
                $.ajax({
                    url: "{{ route('products.store-dynamic') }}",
                    method: 'POST',
                    data: {
                        name: productName,
                        price: 0,
                        category: 'Default',
                        sub_category: 'Default',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#edit-product-list').append('<option value="' + response.name +
                            '">');
                        showToast('Product added successfully', 'success');
                    },
                    error: function(error) {
                        if (error.responseJSON && error.responseJSON.errors) {
                            var errorMessages = [];
                            for (var key in error.responseJSON.errors) {
                                if (error.responseJSON.errors.hasOwnProperty(key)) {
                                    errorMessages.push(error.responseJSON.errors[key][0]);
                                }
                            }
                            showToast(errorMessages.join('<br>'), 'danger');
                        } else {
                            showToast('Error adding product.', 'danger');
                        }
                    }
                });
            }
        });



        var pendingStatusChange = null;

        // Handle status change
        $('#leads-table').on('change', '.status-select', function() {
            var leadId = $(this).data('id');
            var newStatus = $(this).val();
            var $selectElement = $(this);
            var originalStatus = $selectElement.data('current-status');

            var confirmMessage = '';
            var isWin = newStatus === 'win';
            
            if (isWin) {
                confirmMessage = "Changing status to 'Win' will set the success rate to 100%. Do you want to convert this lead to a Client as well?";
            } else if (newStatus === 'lost') {
                confirmMessage = "Changing status to 'Lost' will set the success rate to 0% and generate a pipeline entry based on this. Do you want to proceed?";
            }

            if (confirmMessage) {
                // Store pending change info
                pendingStatusChange = {
                    type: 'status',
                    leadId: leadId,
                    newStatus: newStatus,
                    selectElement: $selectElement,
                    originalStatus: originalStatus,
                    convertToClient: false
                };
                
                if (isWin) {
                    Swal.fire({
                        title: 'Lead Won!',
                        text: confirmMessage,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Convert to Client',
                        cancelButtonText: 'No, Just Set to Win',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            pendingStatusChange.convertToClient = true;
                            $('#statusChangeMessage').text("Please provide the following details to complete the conversion to client:");
                            $('#lossReasonContainer').addClass('d-none');
                            $('#lossReason').prop('required', false);
                            $('#conversionFieldsContainer').removeClass('d-none');
                            
                            // Pre-fill fields
                            $.get('/leads/' + leadId, function(data) {
                                var today = new Date().toISOString().split('T')[0];
                                var currentMonth = new Date().toISOString().substring(0, 7);
                                $('#stat_billing').val(data.billing ? data.billing.substring(0, 7) : currentMonth);
                                $('#stat_doc').val(data.doc || today);
                                populateSerialInputs(data.items, '#stat_serial_numbers_container');
                                $('#confirmStatusChangeModal').modal('show');
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Just set to win without conversion
                            updateStatus(leadId, 'win', $selectElement, originalStatus);
                        } else {
                            // Cancelled - Revert select
                            $selectElement.val(originalStatus);
                        }
                    });
                } else {
                    $('#statusChangeMessage').text(confirmMessage);
                    $('#lossReasonContainer').removeClass('d-none');
                    $('#lossReason').prop('required', true);
                    $('#conversionFieldsContainer').addClass('d-none');
                    $('#confirmStatusChangeModal').modal('show');
                }
            } else {
                // No confirmation needed for other statuses, proceed directly
                updateStatus(leadId, newStatus, $selectElement, originalStatus);
            }
        });

        $('#confirmStatusChangeBtn').on('click', function() {
            if (pendingStatusChange) {
                var reason = $('#lossReason').val();
                var billing = $('#stat_billing').val();
                var doc = $('#stat_doc').val();

                var isValid = true;
                var isWin = (pendingStatusChange.newStatus === 'win' || pendingStatusChange.newSuccessRate == 100);

                // Reset validation states
                $('#lossReason, #stat_billing, #stat_doc').removeClass('is-invalid');
                $('#stat_serial_numbers_container .item-serial-input').removeClass('is-invalid');

                // Validate loss reason if applicable
                if (pendingStatusChange.newStatus === 'lost' && !reason) {
                    $('#lossReason').addClass('is-invalid');
                    isValid = false;
                }

                // Validate conversion fields if status is Win/100% and user opted to convert
                var itemDetails = {};
                if (isWin && pendingStatusChange.convertToClient) {
                    if (!billing) {
                        $('#stat_billing').addClass('is-invalid');
                        isValid = false;
                    }
                    if (!doc) {
                        $('#stat_doc').addClass('is-invalid');
                        isValid = false;
                    }

                    $('#stat_serial_numbers_container .item-machine-serial').each(function() {
                        var mSerial = $(this).val().trim();
                        var itemId = $(this).data('item-id');

                        if (!mSerial) {
                            $(this).addClass('is-invalid');
                            isValid = false;
                        }

                        var eSerial = $(this).closest('.row').find('.item-engine-serial').val().trim();
                        var eModel = $(this).closest('.row').find('.item-engine-model').val().trim();

                        if (!itemDetails[itemId]) itemDetails[itemId] = [];
                        itemDetails[itemId].push({
                            machine_serial: mSerial,
                            engine_serial: eSerial,
                            engine_model: eModel
                        });
                    });
                }

                if (!isValid) return;

                if (pendingStatusChange.type === 'status') {
                    // Re-apply the selection visually as we proceed
                    pendingStatusChange.selectElement.val(pendingStatusChange.newStatus);
                    updateStatus(pendingStatusChange.leadId, pendingStatusChange.newStatus, pendingStatusChange.selectElement, pendingStatusChange.originalStatus, reason, billing, doc, itemDetails, pendingStatusChange.convertToClient);
                } else if (pendingStatusChange.type === 'success_rate') {
                    updateSuccessRate(pendingStatusChange.leadId, pendingStatusChange.newSuccessRate, reason, billing, doc, itemDetails, pendingStatusChange.convertToClient);
                }
            }
            $('#confirmStatusChangeModal').modal('hide');
            // Reset modal fields
            $('#lossReason').val('');
            $('#stat_billing').val('');
            $('#stat_doc').val('');
            $('#stat_serial_numbers_container').empty();
            pendingStatusChange = null;
        });

        // Helper function for AJAX status update
        function updateStatus(leadId, newStatus, $selectElement, originalStatus, reason = null, billing = null, doc = null, itemDetails = null, convertToClient = true) {
            var data = {
                status: newStatus,
                convert_to_client: convertToClient,
                _method: 'PUT',
                _token: '{{ csrf_token() }}'
            };
            if (reason) {
                data.reason = reason;
            }
            if (billing) data.billing = billing;
            if (doc) data.doc = doc;
            if (itemDetails) data.item_details = itemDetails;

            $.ajax({
                url: '/leads/' + leadId + '/status',
                method: 'POST',
                data: data,
                success: function(response) {
                    var statusColors = {
                        'pending': 'bg-warning text-dark',
                        'in progress': 'bg-info text-white',
                        'in_progress': 'bg-info text-white',
                        'win': 'bg-success text-white',
                        'lost': 'bg-danger text-white',
                        'positive': 'bg-primary text-white',
                        'converted_to_client': 'bg-success text-white'
                    };
                    // Remove all possible status color classes
                    $selectElement.removeClass('bg-warning bg-info bg-success bg-danger bg-primary bg-secondary text-white text-dark');
                    
                    // Add the new color class
                    var newColorClass = statusColors[newStatus] || 'bg-secondary text-white';
                    $selectElement.addClass(newColorClass);
                    $selectElement.data('current-status', newStatus); // Update the data attribute

                    showToast(response.message, 'success');

                    // Reload table if status change might affect success rate display (to show updated progress bar)
                    // Reload table if status change might affect success rate display (to show updated progress bar)
                    if ($('#assigned-leads-tab').hasClass('active')) {
                        assignedLeadsTable.ajax.reload(null, false); // Reload without resetting paging
                    } else if ($('#unassigned-leads-tab').hasClass('active')) {
                        unassignedLeadsTable.ajax.reload(null, false);
                    }
                },
                error: function(error) {
                    console.error('Error updating status:', error);
                    showToast('Error updating status.', 'danger');
                    // Revert to original status on error
                    $selectElement.val(originalStatus);
                }
            });
        }

        // Update success rate display as slider moves
        $('#successRateInput').on('input', function() {
            $('#successRateValue').text($(this).val() + '%');
        });

        // Handle click on Success Rate cell or 0% button
        $('#leads-table, #unassigned-leads-table').on('click', '.success-rate-cell, .success-rate-cell button', function() {
            var leadId = $(this).data('id');
            var currentPercentage = $(this).data('percentage') || 0;

            $('#successRateLeadId').val(leadId);
            $('#successRateInput').val(currentPercentage);
            $('#successRateValue').text(currentPercentage + '%');
            $('#updateSuccessRateModal').modal('show');
        });

        // Handle Update Success Rate Form Submission
        $('#updateSuccessRateForm').on('submit', function(e) {
            e.preventDefault();
            var leadId = $('#successRateLeadId').val();
            var newChanceOfSuccess = $('#successRateInput').val();

            var confirmMessage = '';
            if (newChanceOfSuccess == 100) {
                confirmMessage = "Setting success rate to 100% will change the status to 'Win' and convert this lead to a Client. Do you want to proceed?";
            } else if (newChanceOfSuccess == 0) {
                confirmMessage = "Setting success rate to 0% will change the status to 'Lost' and generate a pipeline entry based on this. Do you want to proceed?";
            }

            // We use the modal now instead of confirm()
            if (confirmMessage) {
                pendingStatusChange = {
                    type: 'success_rate',
                    leadId: leadId,
                    newSuccessRate: newChanceOfSuccess,
                    convertToClient: false
                };
                
                if (newChanceOfSuccess == 100) {
                    Swal.fire({
                        title: 'Perfect Success!',
                        text: confirmMessage,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Convert to Client',
                        cancelButtonText: 'No, Just Set to 100%',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            pendingStatusChange.convertToClient = true;
                            $('#statusChangeMessage').text("Please provide the following details to complete the conversion to client:");
                            $('#lossReasonContainer').addClass('d-none');
                            $('#lossReason').prop('required', false);
                            $('#conversionFieldsContainer').removeClass('d-none');
                            
                            // Pre-fill fields
                            $.get('/leads/' + leadId, function(data) {
                                var today = new Date().toISOString().split('T')[0];
                                var currentMonth = new Date().toISOString().substring(0, 7);
                                $('#stat_billing').val(data.billing ? data.billing.substring(0, 7) : currentMonth);
                                $('#stat_doc').val(data.doc || today);
                                populateSerialInputs(data.items, '#stat_serial_numbers_container');
                                $('#confirmStatusChangeModal').modal('show');
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Just set to 100% without conversion
                            updateSuccessRate(leadId, 100);
                        }
                    });
                } else {
                    $('#statusChangeMessage').text(confirmMessage);
                    $('#lossReasonContainer').removeClass('d-none');
                    $('#lossReason').prop('required', true);
                    $('#conversionFieldsContainer').addClass('d-none');
                    $('#confirmStatusChangeModal').modal('show');
                }
            } else {
                updateSuccessRate(leadId, newChanceOfSuccess);
            }
        });

        function updateSuccessRate(leadId, newChanceOfSuccess, reason = null, billing = null, doc = null, itemDetails = null, convertToClient = true) {
            var data = {
                chance_of_success: newChanceOfSuccess,
                convert_to_client: convertToClient,
                _method: 'PUT',
                _token: '{{ csrf_token() }}'
            };
            if (reason) {
                data.reason = reason;
            }
            if (billing) data.billing = billing;
            if (doc) data.doc = doc;
            if (itemDetails) data.item_details = itemDetails;

            $.ajax({
                url: '/leads/' + leadId + '/update-chance-of-success',
                method: 'POST',
                data: data,
                success: function(response) {
                    showToast(response.message, 'success');
                    if ($('#assigned-leads-tab').hasClass('active')) {
                        assignedLeadsTable.ajax.reload(null, false);
                    } else if ($('#unassigned-leads-tab').hasClass('active')) {
                        unassignedLeadsTable.ajax.reload(null, false);
                    }
                    $('#updateSuccessRateModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating chance of success:', error);
                    showToast('Error updating success rate.', 'danger');
                }
            });
        }

        // Handle Assign Employee button click
        $('#leads-table').on('click', '.assign-employee-btn', function() {
            var leadId = $(this).data('id');
            var dealershipId = $(this).data(
                'dealership-id'); // This will be empty string if no dealership

            $('#assignLeadId').val(leadId);
            loadEmployeesForAssignment(dealershipId); // Load employees based on dealership
            $('#assignEmployeeModal').modal('show');
        });

        // Handle Assign Employee button click for unassigned leads table
        $('#unassigned-leads-table').on('click', '.assign-employee-btn', function() {
            var leadId = $(this).data('id');
            var dealershipId = $(this).data(
                'dealership-id'); // This will be empty string if no dealership

            $('#assignLeadId').val(leadId);
            loadEmployeesForAssignment(dealershipId); // Load employees based on dealership
            $('#assignEmployeeModal').modal('show');
        });

        // Handle Assign Employee Form Submission
        $('#assignEmployeeForm').on('submit', function(e) {
            e.preventDefault();
            var leadId = $('#assignLeadId').val();
            var employeeId = $('#employeeSelect').val();
            var dueDate = $('#assignDueDate').val();

            $.ajax({
                url: '/leads/' + leadId + '/assign-employee',
                method: 'POST',
                data: {
                    employee_id: employeeId,
                    due_date: dueDate,
                    _method: 'PUT',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    if ($('#assigned-leads-tab').hasClass('active')) {
                        assignedLeadsTable.ajax.reload();
                    } else if ($('#unassigned-leads-tab').hasClass('active')) {
                        unassignedLeadsTable.ajax.reload();
                    }
                    $('#assignEmployeeModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error assigning employee:', error);
                    var errorMessage = 'An unexpected error occurred.';
                    if (error.responseJSON && error.responseJSON.error) {
                        errorMessage = error.responseJSON.error;
                    } else if (error.responseText) {
                        errorMessage = error.responseText;
                    }
                    showToast(errorMessage, 'danger');
                }
            });
        });

        // Function to load employees for assignment
        function loadEmployeesForAssignment(dealershipId) {
            var employeeSelect = $('#employeeSelect');
            var assignEmployeeMessage = $('#assignEmployeeMessage');
            employeeSelect.empty(); // Clear existing options
            employeeSelect.append('<option value="">Select Employee</option>'); // Add default option
            assignEmployeeMessage.addClass('d-none').text(''); // Clear and hide previous messages

            var url = "{{ route('employees.assignable') }}";
            var data = {};
            // If dealershipId is provided, include it in the request
            if (dealershipId) {
                data.dealership_id = dealershipId;
            }


            $.ajax({
                url: url,
                method: 'GET',
                data: data, // Pass data object with dealership_id
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(index, employee) {
                            employeeSelect.append('<option value="' + employee.id + '">' +
                                employee.name + '</option>');
                        });
                        employeeSelect.prop('disabled',
                            false); // Enable select if options are loaded
                    } else {
                        // Display message if no employees are found or if there's a specific message from backend
                        assignEmployeeMessage.removeClass('d-none').text(response.message ||
                            'No assignable employees found.');
                        employeeSelect.prop('disabled', true); // Disable select if no options
                    }
                },
                error: function(error) {
                    console.error('Error loading employees:', error);
                    var errorMessage = 'An unexpected error occurred.';
                    if (error.responseJSON && error.responseJSON.message) {
                        errorMessage = error.responseJSON.message;
                    }
                    assignEmployeeMessage.removeClass('d-none').text(errorMessage);
                    employeeSelect.prop('disabled', true); // Disable select on error
                }
            });
        }

        // Agents DataTable
        var agentsTable;
        var agentsTableInitialized = false;

        $('#agents-tab').on('shown.bs.tab', function(e) {
            if (!agentsTableInitialized) {
                setTimeout(function() { // Add a small delay
                    agentsTable = $('#agents-table').DataTable({
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
                        ajax: "{{ route('agents.data') }}",
                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'email',
                                name: 'email'
                            },
                            {
                                data: 'phone_number',
                                name: 'phone_number'
                            },
                            {
                                data: 'type',
                                name: 'type'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });
                    agentsTableInitialized = true;
                }, 100); // 100ms delay
            }
        });

        // Handle view agent button click
        $('#agents-table').on('click', '.view-agent-btn', function() {
            var agentId = $(this).data('id');
            $.ajax({
                url: '/agents/' + agentId,
                method: 'GET',
                success: function(data) {
                    $('#viewAgentName').text(data.name);
                    $('#viewAgentEmail').text(data.email);
                    $('#viewAgentPhone').text(data.phone_number);
                    $('#viewAgentType').text(data.type);
                    $('#viewAgentModal').modal('show');
                }
            });
        });

        // Handle view lead button click (assuming this is the handler to be updated)
        // Note: The original document did not contain a 'view-lead-btn' handler.
        // This block is added based on the instruction to update a 'view-lead-btn' handler
        // and the provided code snippet's content which manipulates 'viewLeadProducts' and 'viewLeadModal'.
        $('#leads-table, #unassigned-leads-table').on('click', '.view-lead-btn', function() {
            var leadId = $(this).data('id');
            $.ajax({
                url: '/leads/' + leadId, // Assuming a leads endpoint
                method: 'GET',
                success: function(data) {
                    $('#viewLeadSalutation').text(data.salutation);
                    $('#viewLeadName').text(data.name);
                    $('#viewLeadCompany').text(data.company || '-');
                    $('#viewLeadEmail').text(data.email || '-');
                    $('#viewLeadPhone').text(data.phone_number || '-');
                    $('#viewLeadAlternateContact').text(data.alternate_contact_number || '-');
                    $('#viewLeadAgent').text(data.agent ? data.agent.name : '-');
                    $('#viewLeadSource').text(data.lead_source ? data.lead_source.name : '-');
                    $('#viewLeadCategory').text(data.lead_category ? data.lead_category.name : '-');
                    $('#viewLeadValue').text(data.lead_value || '-');
                    $('#viewAllowFollowUp').text(data.allow_follow_up ? 'Yes' : 'No');
                    $('#viewStatus').text(data.status);

                    // Handle multiple products in view modal
                    var productsHtml = '';
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(function(item) {
                            productsHtml += '<tr>' +
                                '<td>' + (item.product_name || '-') + '</td>' +
                                '<td>' + (item.product_model_name || '-') + (item.model_series_name ? ' (' + item.model_series_name + ')' : '') + '</td>' +
                                '<td>' + (item.quantity || 1) + '</td>' +
                                '<td>₹' + (item.price || 0) + '</td>' +
                                '</tr>';
                        });
                    } else {
                        // Fallback for primary product columns
                        productsHtml += '<tr>' +
                            '<td>' + (data.product ? data.product.name : '-') + '</td>' +
                            '<td>' + (data.product_model ? data.product_model.name : '-') + (data.product_variant ? ' (' + data.product_variant.name + ')' : '') + '</td>' +
                            '<td>' + (data.quantity || 0) + '</td>' +
                            '<td>₹' + (data.lead_value || 0) + '</td>' +
                            '</tr>';
                    }
                    $('#viewLeadProducts').html(productsHtml);

                    $('#viewLeadModal').modal('show');
                }
            });
        });

        // Handle edit agent button click
        $('#agents-table').on('click', '.edit-agent-btn', function() {
            var agentId = $(this).data('id');
            $.ajax({
                url: '/agents/' + agentId,
                method: 'GET',
                success: function(data) {
                    $('#editAgentId').val(data.id);
                    $('#editAgentName').val(data.name);
                    $('#editAgentEmail').val(data.email);
                    if (data.type == 'Employee') {
                        $('#editAgentPhone').val(data.mobile);
                    } else {
                        $('#editAgentPhone').val(data.phone_number);
                    }
                    $('#editAgentModal').modal('show');
                }
            });
        });

        // Handle edit agent form submission
        $('#editAgentForm').on('submit', function(e) {
            e.preventDefault();
            var agentId = $('#editAgentId').val();
            $.ajax({
                url: '/agents/' + agentId,
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editAgentModal').modal('hide');
                    agentsTable.ajax.reload();
                    showToast(response.message, 'success');
                },
                error: function(error) {
                    console.error('Error:', error);
                    var errorMessage = 'An unexpected error occurred.';
                    if (error.responseJSON && error.responseJSON.error) {
                        errorMessage = error.responseJSON.error;
                    } else if (error.responseText) {
                        errorMessage = error.responseText;
                    }
                    showToast(errorMessage, 'danger');
                }
            });
        });
    });
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,marker"></script>
<script>
    let map;
    let marker;
    let geocoder;
    let autocomplete;

    function initMap() {
        const defaultLatLng = {
            lat: 51.505,
            lng: -0.09
        }; // Default to London
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 8,
            center: defaultLatLng,
            mapId: "DEMO_MAP_ID",
        });
        geocoder = new google.maps.Geocoder();

        marker = new google.maps.marker.AdvancedMarkerElement({
            map: map,
            position: defaultLatLng,
            gmpDraggable: true,
        });

        const locationInput = document.getElementById('map_location');
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
            $('#map_location').val(place.formatted_address);
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
                        $('#map_location').val(results[0].formatted_address);
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
                        $('#map_location').val(results[0].formatted_address);
                        $('#latitude').val(event.latLng.lat());
                        $('#longitude').val(event.latLng.lng());
                    }
                }
            });
        });
    }

    let editMap;
    let editMarker;
    let editGeocoder;
    let editAutocomplete;

    function initEditMap(initialLat, initialLng, initialAddress = '') {
        const lat = parseFloat(initialLat);
        const lng = parseFloat(initialLng);
        const center = {
            lat: isFinite(lat) ? lat : 20.5937,
            lng: isFinite(lng) ? lng : 78.9629
        };

        editMap = new google.maps.Map(document.getElementById('editMap'), {
            zoom: 8,
            center: center,
            mapId: "DEMO_MAP_ID",
        });
        editGeocoder = new google.maps.Geocoder();

        editMarker = new google.maps.marker.AdvancedMarkerElement({
            map: editMap,
            position: center,
            gmpDraggable: true,
        });

        const editLocationInput = document.getElementById('edit_map_location');
        try {
            editAutocomplete = new google.maps.places.Autocomplete(editLocationInput);
            editAutocomplete.bindTo('bounds', editMap);

            editAutocomplete.addListener('place_changed', function() {
                const place = editAutocomplete.getPlace();

                if (!place || !place.geometry || !place.geometry.location) {
                    showToast('No geometry details available for input: ' + (place ? place.name : ''), 'warning');
                    return;
                }

                if (place.geometry.viewport) {
                    editMap.fitBounds(place.geometry.viewport);
                } else {
                    editMap.setCenter(place.geometry.location);
                    editMap.setZoom(17);
                }

                editMarker.position = place.geometry.location;
                $('#edit_map_location').val(place.formatted_address);
                $('#edit_latitude').val(place.geometry.location.lat());
                $('#edit_longitude').val(place.geometry.location.lng());
            });

        } catch (err) {
            console.error('Failed to initialize editAutocomplete:', err);
            editAutocomplete = null;
        }

        editMarker.addListener('gmp-dragend', function() {
            const latlng = editMarker.position;
            editGeocoder.geocode({
                'location': latlng
            }, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        $('#edit_map_location').val(results[0].formatted_address);
                        $('#edit_latitude').val(latlng.lat());
                        $('#edit_longitude').val(latlng.lng());
                    }
                }
            });
        });

        editMap.addListener('click', function(event) {
            editMarker.position = event.latLng;
            editGeocoder.geocode({
                'location': event.latLng
            }, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        $('#edit_map_location').val(results[0].formatted_address);
                        $('#edit_latitude').val(event.latLng.lat());
                        $('#edit_longitude').val(event.latLng.lng());
                    }
                }
            });
        });

        if (initialAddress) {
            $('#edit_map_location').val(initialAddress);
        }
    }

    $('#editLeadModal').on('shown.bs.modal', function() {
        var $modal = $(this);
        var rawLat = $modal.data('initLat');
        var rawLng = $modal.data('initLng');
        var initAddress = $modal.data('initAddress');

        var lat = parseFloat(rawLat);
        var lng = parseFloat(rawLng);

        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
            if (!editMap) {
                // Initialize the map now that the container is visible
                initEditMap(
                    isFinite(lat) ? lat : 20.5937,
                    isFinite(lng) ? lng : 78.9629,
                    initAddress || ''
                );
            } else {
                // Already initialized: trigger resize and recenter to provided coords
                google.maps.event.trigger(editMap, 'resize');
                var currentLat = isFinite(lat) ? lat : (editMarker ? editMarker.getPosition().lat() : 20.5937);
                var currentLng = isFinite(lng) ? lng : (editMarker ? editMarker.getPosition().lng() : 78.9629);
                var center = new google.maps.LatLng(currentLat, currentLng);
                if (editMarker) editMarker.setPosition(center);
                editMap.setCenter(center);
                editMap.setZoom(13);
                $('#edit_latitude').val(currentLat);
                $('#edit_longitude').val(currentLng);
                $('#edit_map_location').val(initAddress || $('#edit_map_location').val());
            }
        }
        // If autocomplete failed to initialize earlier, attempt to create it now.
        setTimeout(function() {
            try {
                if (!editAutocomplete && typeof google !== 'undefined' && typeof google.maps !==
                    'undefined') {
                    var editLocationInput = document.getElementById('edit_map_location');
                    editAutocomplete = new google.maps.places.Autocomplete(editLocationInput);
                    editAutocomplete.bindTo('bounds', editMap);
                    editAutocomplete.addListener('place_changed', function() {
                        const place = editAutocomplete.getPlace();
                        if (!place.geometry) {
                            showToast('No details available for input: ' + place.name,
                                'warning');
                            return;
                        }
                        if (place.geometry.viewport) {
                            editMap.fitBounds(place.geometry.viewport);
                        } else {
                            editMap.setCenter(place.geometry.location);
                            editMap.setZoom(17);
                        }
                        editMarker.setPosition(place.geometry.location);
                        $('#edit_map_location').val(place.formatted_address);
                        $('#edit_latitude').val(place.geometry.location.lat());
                        $('#edit_longitude').val(place.geometry.location.lng());
                    });

                }
            } catch (err) {
                console.error('Retry to init editAutocomplete failed:', err);
            }
        }, 300);
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if (e.target.id === 'create-tab') {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && !map) {
                initMap();
            } else if (map) {
                google.maps.event.trigger(map, 'resize');
                const currentPosition = marker ? marker.position : new google.maps.LatLng(51.505, -0.09);
                map.setCenter(currentPosition);
            }
        }
    });

    // Debug: log pac items count when typing/focusing in edit modal input
    $(document).on('input', '#edit_map_location', function() {
        var items = document.querySelectorAll('.pac-container .pac-item');

        // Also call AutocompleteService directly to see if Google returns predictions
        try {
            if (typeof google !== 'undefined' && google.maps && google.maps.places && google.maps.places
                .AutocompleteService) {
                var svc = new google.maps.places.AutocompleteService();
                var val = $(this).val();
                var inputEl = this; // capture for callback
                svc.getPlacePredictions({
                    input: val
                }, function(preds, status) {
                    if (preds && preds.length) {
                        // if native pac-items are not present, show custom dropdown
                        var nativeItems = document.querySelectorAll('.pac-container .pac-item');
                        if ((!nativeItems || nativeItems.length === 0) &&
                            typeof showCustomPredictions === 'function') {
                            showCustomPredictions(preds, inputEl);
                        }

                        // populate datalist fallback
                        try {
                            var dl = document.getElementById('edit_map_location_datalist');
                            if (dl) {
                                dl.innerHTML = '';
                                window._editPlaceMap = window._editPlaceMap || {};
                                preds.forEach(function(p) {
                                    var opt = document.createElement('option');
                                    opt.value = p.description;
                                    dl.appendChild(opt);
                                    window._editPlaceMap[p.description] = p.place_id;
                                });
                            }
                        } catch (e) {
                            console.error('datalist fallback error', e);
                        }
                    }
                });
            } else {

            }
        } catch (err) {
            console.error('AutocompleteService error:', err);
        }
    });

    // Fallback: render custom suggestions when native pac-items are not present
    var customPacEl = null;

    function showCustomPredictions(predictions, inputEl) {
        // remove existing
        if (customPacEl) customPacEl.remove();
        console.debug('showCustomPredictions called, count=', predictions.length);

        customPacEl = document.createElement('div');
        customPacEl.className = 'custom-pac-container';
        // ensure it's above modal overlays
        customPacEl.style.zIndex = 300000;
        document.body.appendChild(customPacEl);


        // add a small header so it's obvious on screen
        var header = document.createElement('div');
        header.style.padding = '6px 10px';
        header.style.fontWeight = '600';
        header.style.borderBottom = '1px solid rgba(0,0,0,0.08)';
        header.textContent = 'Suggestions';
        customPacEl.appendChild(header);

        // position it under the input (account for scroll and fixed containers)
        var rect = inputEl.getBoundingClientRect();
        var left = Math.round(rect.left + window.scrollX);
        var top = Math.round(rect.bottom + window.scrollY);
        var width = Math.max(300, Math.round(rect.width));

        // clamp to viewport so it doesn't go off-screen
        var maxLeft = Math.max(8, window.innerWidth - width - 8);
        if (left < 8) left = 8;
        if (left > maxLeft) left = maxLeft;
        var maxTop = Math.max(8, window.innerHeight + window.scrollY - 40);
        if (top > maxTop) top = maxTop;

        console.debug('showCustomPredictions rect:', rect, 'calculated left/top:', left, top, 'width:', width);

        customPacEl.style.left = left + 'px';
        customPacEl.style.top = top + 'px';
        customPacEl.style.minWidth = width + 'px';

        predictions.forEach(function(p) {
            var item = document.createElement('div');
            item.className = 'custom-pac-item';
            item.textContent = p.description;
            item.dataset.placeId = p.place_id;
            customPacEl.appendChild(item);
        });
    }

    // hide on click outside or blur
    document.addEventListener('click', function(e) {
        if (customPacEl && !customPacEl.contains(e.target) && e.target.id !== 'edit_map_location') {
            customPacEl.remove();
            customPacEl = null;
        }
    });

    // handle clicks on custom suggestions
    document.addEventListener('click', function(e) {
        var t = e.target;
        if (t && t.classList && t.classList.contains('custom-pac-item')) {
            var placeId = t.dataset.placeId;
            if (placeId && typeof google !== 'undefined' && google.maps && google.maps.places) {
                var ps = new google.maps.places.PlacesService(document.createElement('div'));
                ps.getDetails({
                    placeId: placeId
                }, function(place, status) {
                    if (status === google.maps.places.PlacesServiceStatus.OK && place && place.geometry && place.geometry.location) {
                        // Update map and inputs
                        if (!editMap) {
                            initEditMap(place.geometry.location.lat(), place.geometry.location.lng(),
                                place.formatted_address);
                        } else {
                            var latLng = place.geometry.location;
                            editMarker.setPosition(latLng);
                            editMap.setCenter(latLng);
                            editMap.setZoom(13);
                            $('#edit_latitude').val(latLng.lat());
                            $('#edit_longitude').val(latLng.lng());
                            $('#edit_map_location').val(place.formatted_address);
                        }
                        if (customPacEl) {
                            customPacEl.remove();
                            customPacEl = null;
                        }
                    } else {
                        console.error('getDetails failed', status);
                    }
                });
            }
        }
    });

    $(document).on('focus', '#edit_map_location', function() {
        var items = document.querySelectorAll('.pac-container .pac-item');

    });

    // datalist selection handler: when the user selects a value from the datalist, resolve it
    $(document).on('change', '#edit_map_location', function() {
        var val = $(this).val();
        if (window._editPlaceMap && window._editPlaceMap[val]) {
            var placeId = window._editPlaceMap[val];
            if (placeId && typeof google !== 'undefined' && google.maps && google.maps.places) {
                var ps = new google.maps.places.PlacesService(document.createElement('div'));
                ps.getDetails({
                    placeId: placeId
                }, function(place, status) {
                    if (status === google.maps.places.PlacesServiceStatus.OK && place && place.geometry && place.geometry.location) {
                        var latLng = place.geometry.location;
                        if (!editMap) {
                            initEditMap(latLng.lat(), latLng.lng(), place.formatted_address);
                        } else {
                            editMarker.setPosition(latLng);
                            editMap.setCenter(latLng);
                            editMap.setZoom(13);
                            $('#edit_latitude').val(latLng.lat());
                            $('#edit_longitude').val(latLng.lng());
                            $('#edit_map_location').val(place.formatted_address);
                        }
                    } else {
                        console.error('PlacesService.getDetails failed for datalist selection', status);
                    }
                });
            }
        }
    });
</script>
@endpush