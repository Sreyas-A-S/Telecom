@extends('layouts.admin')

@php
$userHasDealership = Auth::user()->employee && Auth::user()->employee->dealership_id;
@endphp

@section('title', 'Clients')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Client Management</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Clients</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <style>
            /* Custom Badge Gradients for DataTable */
            .badge-gradient-primary {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                /* Teal/Emerald for Email */
                color: white !important;
                border: none;
                box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
            }

            .badge-gradient-info {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
                /* Royal Blue for Phone */
                color: white !important;
                border: none;
                box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
            }

            .badge-gradient-secondary {
                background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
                /* Cool Gray for Alt Phone */
                color: white !important;
                border: none;
                box-shadow: 0 2px 4px rgba(107, 114, 128, 0.2);
            }

            .badge-gradient-warning {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
                /* Amber/Orange for Dealership */
                color: white !important;
                border: none;
                box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
            }

            .badge {
                font-weight: 400 !important;
                letter-spacing: 0.5px;
                padding: 6px 10px;
            }
        </style>
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="client-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="view-tab" data-bs-toggle="tab" href="#view" role="tab"
                                aria-controls="view" aria-selected="true">View Clients</a>
                        </li>
                        @if(checkMenu(Session::get('role_id'), 8, 'create'))
                        <li class="nav-item">
                            <a class="nav-link" id="create-tab" data-bs-toggle="tab" href="#create" role="tab"
                                aria-controls="create" aria-selected="false">Create Client</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="import-tab" data-bs-toggle="tab" href="#import" role="tab"
                                aria-controls="import" aria-selected="false">Import Clients</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="update-products-tab" data-bs-toggle="tab" href="#update-products" role="tab"
                                aria-controls="update-products" aria-selected="false">Update Products</a>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="client-tabs-content">
                        <!-- View Tab -->
                        <div class="tab-pane fade show active" id="view" role="tabpanel" aria-labelledby="view-tab">

                            @if($showDealershipColumn ?? false)
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Dealership</label>
                                    <select id="dealership_filter" class="form-select" {{ $userHasDealership ? 'disabled' : '' }}>
                                        <option value="">All Dealerships</option>
                                        @foreach($dealerships as $dealer)
                                        @if($dealer->brand == 1)
                                        <option value="{{ $dealer->id }}" {{ ($userHasDealership && Auth::user()->employee->dealership_id == $dealer->id) ? 'selected' : '' }}>{{ $dealer->name }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('clients.export.list.pdf') }}" id="export_pdf_btn" class="btn btn-danger" target="_blank">Export Filtered PDF</a>
                                </div>
                            </div>
                            @endif

                            <div class="table-responsive">
                                <table class="display" id="clients-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Address</th>
                                            <th>Name & Contact</th>
                                            <th>Agent</th>
                                            <th>Source</th>
                                            <th>Agent & Source</th>
                                            @if($showDealershipColumn ?? false)
                                            <th>Dealership</th>
                                            @endif
                                            <th>Products</th>
                                            <th>Product Models</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Create Tab -->
                        <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                            <form id="createClientForm" class="p-3" enctype="multipart/form-data">
                                @csrf
                                <h5 class="mb-3">Client Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="createProfilePic" class="form-label">Profile Picture</label>
                                        <input class="form-control" type="file" id="createProfilePic" name="profile_pic" accept="image/*">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createSalutation" class="form-label">Salutation</label>
                                        <select class="form-select" id="createSalutation" name="salutation">
                                            <option value="">None</option>
                                            <option value="Mr.">Mr.</option>
                                            <option value="Mrs.">Mrs.</option>
                                            <option value="Ms.">Ms.</option>
                                            <option value="Dr.">Dr.</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createDealership" class="form-label">Dealership <span class="text-danger">*</span></label>
                                        <select class="form-select" id="createDealership" name="dealership_id" required {{ $userHasDealership ? 'disabled' : '' }}>
                                            <option value="">Select Dealership</option>
                                            @foreach($dealerships as $dealer)
                                            <option value="{{ $dealer->id }}" {{ ($userHasDealership && Auth::user()->employee->dealership_id == $dealer->id) ? 'selected' : '' }}>{{ $dealer->name }}</option>
                                            @endforeach
                                        </select>
                                        @if($userHasDealership && Auth::user()->employee)
                                        <input type="hidden" name="dealership_id" value="{{ Auth::user()->employee->dealership_id }}">
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createName" class="form-label">Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="createName" name="name"
                                            required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="createEmail" name="email">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createPhone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="createPhone"
                                            name="phone_number" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createStateId" class="form-label">State <span class="text-danger">*</span></label>
                                        <select class="form-select" id="createStateId" name="state_id" required>
                                            <option value="">Select State</option>
                                            @foreach ($states as $state)
                                            <option value="{{ $state->id }}" data-name="{{ $state->name }}" {{ $state->name == 'Kerala' ? 'selected' : '' }}>{{ $state->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createDistrictId" class="form-label">District <span class="text-danger">*</span></label>
                                        <select class="form-select" id="createDistrictId" name="district_id" required>
                                            <option value="">Select District</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createLeadSourceId" class="form-label">Lead Source</label>
                                        <select class="form-select" id="createLeadSourceId" name="lead_source_id">
                                            <option value="">Select Source</option>
                                            @foreach ($leadSources as $source)
                                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="createAddress" class="form-label">Address</label>
                                        <textarea class="form-control" id="createAddress" name="address" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                        <h5 class="mb-0">Product Information</h5>
                                        <button type="button" class="btn btn-success btn-sm" id="addProduct">Add More Product</button>
                                    </div>
                                    <div class="card-body">
                                        <div id="productContainer">
                                            <div class="product-item border p-3 mb-3 position-relative rounded">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Product <span class="text-danger">*</span></label>
                                                        <select class="form-select product-select" name="products[0][product_id]" required>
                                                            <option value="">Select Product</option>
                                                            @foreach ($products as $product)
                                                            <option value="{{ $product->id }}" data-name="{{ $product->name }}">
                                                                {{ $product->name }}{{ $product->category ? ' (' . $product->category->name . ')' : '' }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Product Model</label>
                                                        <select class="form-select model-select" name="products[0][product_model_id]">
                                                            <option value="">Select Model</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Machine Serial Number</label>
                                                        <input type="text" class="form-control" name="products[0][machine_serial_number]">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Engine Serial Number</label>
                                                        <input type="text" class="form-control" name="products[0][engine_serial_number]">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Engine Model</label>
                                                        <input type="text" class="form-control" name="products[0][engine_model]">
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label">DOC (Date of Commissioning)</label>
                                                        <input type="date" class="form-control" name="products[0][doc]">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2"
                                        onclick="document.getElementById('createClientForm').reset(); $('#productContainer').find('.product-item:not(:first)').remove();">Reset</button>
                                    <button type="submit" class="btn btn-primary">Create Client</button>
                                </div>
                            </form>
                        </div>

                        <!-- Import Tab -->
                        <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Import Clients Solely</h4>
                                        </div>
                                        <div class="card-body">
                                            <p class="mt-3">Download a sample Excel template: <a
                                                    href="{{ route('clients.import.template') }}" class="btn btn-sm btn-outline-primary">Download
                                                    Template</a></p>
                                            <form id="importClientForm" enctype="multipart/form-data" class="theme-form">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="importFile" class="form-label">Upload Excel File</label>
                                                    <input class="form-control" type="file" id="importFile"
                                                        name="excel_file" accept=".xlsx, .xls, .csv" required>
                                                </div>
                                                <button type="submit" id="importClientButton" class="btn btn-primary">Import
                                                    Clients</button>
                                                <div id="import-spinner" class="spinner-border text-primary" role="status"
                                                    style="display: none;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Import Client Products</h4>
                                        </div>
                                        <div class="card-body">
                                            <p class="mt-3">Download a sample Excel template: <a
                                                    href="{{ route('clients.import-products.template') }}" class="btn btn-sm btn-outline-primary">Download
                                                    Template</a></p>
                                            <form id="importClientProductForm" enctype="multipart/form-data" class="theme-form">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="importProductFile" class="form-label">Upload Excel File</label>
                                                    <input class="form-control" type="file" id="importProductFile"
                                                        name="excel_file" accept=".xlsx, .xls, .csv" required>
                                                </div>
                                                <button type="submit" id="importClientProductButton" class="btn btn-primary">Import
                                                    Products</button>
                                                <div id="import-product-spinner" class="spinner-border text-primary" role="status"
                                                    style="display: none;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="progress" style="height: 25px; display: none;">
                                        <div id="import-progress-bar"
                                            class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                            aria-valuemax="100" style="width: 0%">0%</div>
                                    </div>
                                    <div id="import-status" class="mt-3"></div>
                                    <div id="import-errors" class="mt-3"></div>
                                    <div id="import-results" class="mt-3"
                                        style="display: none; max-height: 300px; overflow-y: auto;"></div>
                                    <button id="closeImportResults" class="btn btn-secondary mt-3"
                                        style="display: none;">Close Results</button>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Recent Client Imports</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="recentClientImportsTable">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Date</th>
                                                            <th>Summary</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Recent Product Imports</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered" id="recentProductImportsTable">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Date</th>
                                                            <th>Summary</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Products Tab -->
                    <div class="tab-pane fade" id="update-products" role="tabpanel" aria-labelledby="update-products-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Update Client Products</h4>
                                    </div>
                                    <div class="card-body">
                                        <p class="mt-3">Download a sample Excel template: <a
                                                href="{{ route('clients.update-products.template') }}" class="btn btn-sm btn-outline-primary">Download
                                                Template</a></p>
                                        <p class="text-muted">Upload an Excel sheet of already imported client-products to update the Machine Serial Number of existing products. The update is applied by matching other column values such as email, machine model, doc, engine model.</p>
                                        <form id="updateClientProductForm" enctype="multipart/form-data" class="theme-form">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="updateProductFile" class="form-label">Upload Excel File</label>
                                                <input class="form-control" type="file" id="updateProductFile"
                                                    name="excel_file" accept=".xlsx, .xls, .csv" required>
                                            </div>
                                            <button type="submit" id="updateClientProductButton" class="btn btn-primary">Update Products</button>
                                            <div id="update-product-spinner" class="spinner-border text-primary" role="status"
                                                style="display: none;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="progress update-progress" style="height: 25px; display: none;">
                                    <div id="update-progress-bar"
                                        class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100" style="width: 0%">0%</div>
                                </div>
                                <div id="update-status" class="mt-3"></div>
                                <div id="update-errors" class="mt-3"></div>
                                <div id="update-results" class="mt-3"
                                    style="display: none; max-height: 300px; overflow-y: auto;"></div>
                                <button id="closeUpdateResults" class="btn btn-secondary mt-3"
                                    style="display: none;">Close Results</button>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Recent Product Updates</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="recentProductUpdatesTable">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Date</th>
                                                        <th>Summary</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
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
</div>
</div>

<!-- View Client Modal -->
<div class="modal fade" id="viewClientModal" tabindex="-1" aria-labelledby="viewClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewClientModalLabel">Client Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3 text-center">
                        <div id="viewClientProfilePic"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Salutation:</strong> <span id="viewClientSalutation"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Name:</strong> <span id="viewClientName"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong> <span id="viewClientEmail"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone Number:</strong> <span id="viewClientPhone"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Address:</strong> <span id="viewClientAddress"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>GPS Location:</strong> <span id="viewClientGpsLocation"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClientModalLabel">Edit Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 8, 'update'))
            <form id="editClientForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editClientId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editProfilePic" class="form-label">Profile Picture</label>
                            <input class="form-control" type="file" id="editProfilePic" name="profile_pic" accept="image/*">
                            <div id="editProfilePicPreview" class="mt-2"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editSalutation" class="form-label">Salutation</label>
                            <select class="form-select" id="editSalutation" name="salutation">
                                <option value="">None</option>
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                                <option value="Dr.">Dr.</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDealership" class="form-label">Dealership <span class="text-danger">*</span></label>
                            <select class="form-select" id="editDealership" name="dealership_id" required {{ $userHasDealership ? 'disabled' : '' }}>
                                <option value="">Select Dealership</option>
                                @foreach($dealerships as $dealer)
                                <option value="{{ $dealer->id }}" {{ ($userHasDealership && Auth::user()->employee->dealership_id == $dealer->id) ? 'selected' : '' }}>{{ $dealer->name }}</option>
                                @endforeach
                            </select>
                            @if($userHasDealership && Auth::user()->employee)
                            <input type="hidden" name="dealership_id" id="editDealershipHidden" value="{{ Auth::user()->employee->dealership_id }}">
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editClientName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editClientName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editClientEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editClientEmail" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editClientPhone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="editClientPhone" name="phone_number">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="editAddress" name="address" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0">Product Information</h5>
                            <button type="button" class="btn btn-success btn-sm" id="addEditProduct">Add More Product</button>
                        </div>
                        <div class="card-body">
                            <div id="editProductContainer">
                                {{-- Products will be loaded dynamically --}}
                            </div>
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
                    You do not have permission to edit clients.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Client Modal -->
<div class="modal fade" id="deleteClientModal" tabindex="-1" role="dialog" aria-labelledby="deleteClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteClientModalLabel">Delete Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 8, 'delete'))
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteClientName"></strong>?</p>
                <input type="hidden" id="deleteClientId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteClient">Delete</button>
            </div>
            @else
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to delete clients.
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
</div>
</div>
</div>
</div>

<!-- Undo Import Modal -->
<div class="modal fade" id="undoImportModal" tabindex="-1" role="dialog" aria-labelledby="undoImportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="undoImportModalLabel">Undo Import</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to undo this import? This will delete all items imported in this batch.</p>
                <input type="hidden" id="undoImportId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmUndoImport">Undo Import</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}"></script>
<!-- DataTables with Bootstrap 5 integration -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        var clientsTable = $('#clients-table').DataTable({
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                    extend: 'csv',
                    className: 'btn btn-sm btn-primary text-white',
                    exportOptions: {
                        columns: '.export-col'
                    }
                },
                {
                    extend: 'excel',
                    className: 'btn btn-sm btn-success text-white',
                    exportOptions: {
                        columns: '.export-col'
                    }
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-danger text-white',
                    exportOptions: {
                        columns: '.export-col'
                    }
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info text-white',
                    exportOptions: {
                        columns: '.export-col'
                    }
                }
            ],
            ajax: {
                url: "{{ route('clients.index') }}",
                data: function(d) {
                    d.dealership_id = $('#dealership_filter').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'export-col'
                },
                {
                    data: 'name',
                    name: 'name',
                    visible: false,
                    className: 'export-col'
                },
                {
                    data: 'email',
                    name: 'email',
                    visible: false,
                    className: 'export-col'
                },
                {
                    data: 'phone_number',
                    name: 'phone_number',
                    visible: false,
                    className: 'export-col'
                },
                {
                    data: 'address',
                    name: 'address',
                    visible: false,
                    className: 'export-col'
                },
                {
                    data: 'name_contact',
                    name: 'name_contact',
                    render: function(data, type, row) {
                        var email = data.email ? '<div class="badge rounded-pill badge-gradient-primary mt-1">' + data.email + '</div>' : '';
                        var phone = data.phone_number ? '<a href="javascript:void(0);" class="badge rounded-pill badge-gradient-info mt-1 call-number" data-number="' + data.phone_number + '" title="Call Primary" style="text-decoration: none;"><i class="icon-mobile"></i> ' + data.phone_number + '</a>' : '';
                        var altPhone = data.alternate_contact_number ? '<a href="javascript:void(0);" class="badge rounded-pill badge-gradient-secondary mt-1 ms-1 call-number" data-number="' + data.alternate_contact_number + '" title="Call Alternate" style="text-decoration: none;"><i class="icon-mobile"></i> ' + data.alternate_contact_number + '</a>' : '';
                        var address = data.address ? '<div class="text-muted small">' + data.address + '</div>' : '';
                        var profilePic = data.profile_pic ? '<img src="/' + data.profile_pic + '" class="rounded-circle me-2" width="30" height="30" alt="Profile">' : '<div class="rounded-circle me-2 d-inline-block bg-secondary" style="width: 30px; height: 30px; vertical-align: middle;"></div>';
                        var salutation = (data.salutation && data.salutation !== 'null') ? data.salutation + ' ' : '';
                        return '<div class="d-flex align-items-center mb-1">' + profilePic + '<div>' + salutation + data.name + '</div></div>' + email + '<div>' + phone + altPhone + '</div>' + address;
                    }
                },
                {
                    data: 'agent_name',
                    name: 'agent_name',
                    visible: false,
                    className: 'export-col',
                    defaultContent: 'N/A'
                },
                {
                    data: 'source_name',
                    name: 'source_name',
                    visible: false,
                    className: 'export-col',
                    defaultContent: 'N/A'
                },
                {
                    data: 'agent_source',
                    name: 'agent_source',
                    render: function(data, type, row) {
                        var agentName = data.agent ? data.agent.name : 'N/A';
                        var sourceName = data.leadSource ? '<div class="text-muted fw-bold">' + data.leadSource.name + '</div>' : '';
                        return '<span>' + agentName + '</span>' + sourceName;
                    }
                },
                @if($showDealershipColumn ?? false) {
                    data: 'dealership_name',
                    name: 'dealership_name',
                    orderable: false,
                    searchable: false,
                    className: 'export-col',
                    render: function(data, type, row) {
                        return data && data !== 'N/A' ? '<span class="badge rounded-pill badge-gradient-warning">' + data + '</span>' : '<span class="text-muted">N/A</span>';
                    }
                },
                @endif {
                    data: 'products',
                    name: 'products',
                    orderable: false,
                    searchable: false,
                    className: 'export-col'
                },
                {
                    data: 'product_models',
                    name: 'product_models',
                    orderable: false,
                    searchable: false,
                    className: 'export-col'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                        btn += '<li class="view"><a href="/clients/' + row.id + '" title="View" class="view-client-link"><i class="icon-eye"></i></a></li>';
                        btn += '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' + row.id + '" class="edit-client-btn"><i class="icon-pencil"></i></a></li>';
                        btn += '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' + row.id + '" data-client-name="' + row.name + '" class="delete-client-btn"><i class="icon-trash"></i></a></li>';
                        btn += '</ul>';
                        return btn;
                    }
                }
            ]
        });

        $('#dealership_filter').change(function() {
            clientsTable.ajax.reload();
            var dealerId = $(this).val();
            var exportBtn = $('#export_pdf_btn');
            var baseUrl = "{{ route('clients.export.list.pdf') }}";
            exportBtn.attr('href', dealerId ? baseUrl + '?dealership_id=' + dealerId : baseUrl);
        });

        // Import Logic
        $('#importClientForm').on('submit', function(e) {
            e.preventDefault();
            submitImport("{{ route('clients.import') }}", '#importClientButton', '#import-spinner');
        });

        $('#importClientProductForm').on('submit', function(e) {
            e.preventDefault();
            submitImport("{{ route('clients.import-products') }}", '#importClientProductButton', '#import-product-spinner');
        });

        function submitImport(url, buttonSelector, spinnerSelector) {
            var formData = new FormData($(buttonSelector).closest('form')[0]);
            $('#importClientButton, #importClientProductButton').prop('disabled', true);
            $(spinnerSelector).show();
            $('.progress').show();
            $('#import-progress-bar').width('0%').text('0%');
            $('#import-status').text('Uploading...');
            $('#import-results').hide().empty();
            $('#closeImportResults').hide();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    checkImportProgress(response.import_id, buttonSelector, spinnerSelector);
                },
                error: function(xhr) {
                    $('#importClientButton, #importClientProductButton').prop('disabled', false);
                    $(spinnerSelector).hide();
                    showToast('Error: ' + xhr.responseText, 'danger');
                }
            });
        }

        function checkImportProgress(importId, buttonSelector, spinnerSelector) {
            var interval = setInterval(function() {
                $.get('/clients/import/progress/' + importId, function(data) {
                    var percent = data.percentage;
                    $('#import-progress-bar').css('width', percent + '%').text(percent + '%');
                    $('#import-status').text('Status: ' + data.status + ' | Processed: ' + data.processed_rows + ' / ' + data.total_rows);

                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(interval);
                        $('#importClientButton, #importClientProductButton').prop('disabled', false);
                        $(spinnerSelector).hide();

                        if (data.status === 'completed') {
                            showToast('Import completed successfully', 'success');
                            clientsTable.ajax.reload();
                            setTimeout(function() {
                                $('.progress').fadeOut();
                            }, 2000);
                        }

                        var summary = '<div class="alert alert-info"><strong>Summary:</strong><br>Total: ' + (data.total_rows || data.processed_rows) + ' | Success: ' + (data.success_count || 0) + ' | Failed: ' + (data.failed_count || 0) + '</div>';
                        var resultsHtml = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm">';
                        resultsHtml += '<thead class="table-light"><tr><th>Row</th><th>Client</th><th>Machine</th><th>Status</th><th>Details</th></tr></thead><tbody>';
                        $.each(data.results, function(index, result) {
                            var rowClass = '';
                            var statusBadge = '';

                            if (result.status === 'failed') {
                                rowClass = 'table-danger';
                                statusBadge = '<span class="badge bg-danger">Failed</span>';
                            } else if (result.status === 'success_with_warnings' || (result.warnings && result.warnings.length > 0)) {
                                rowClass = 'table-warning';
                                statusBadge = '<span class="badge bg-warning text-dark">Warning</span>';
                            } else if (result.status === 'skipped') {
                                rowClass = 'table-secondary';
                                statusBadge = '<span class="badge bg-secondary">Skipped</span>';
                            } else {
                                statusBadge = '<span class="badge bg-success">Success</span>';
                            }

                            var details = result.reason || '';
                            if (result.warnings && result.warnings.length > 0) {
                                details += '<ul class="mb-0 ps-3 small text-muted mt-1">';
                                $.each(result.warnings, function(i, w) {
                                    details += '<li>' + w + '</li>';
                                });
                                details += '</ul>';
                            }

                            resultsHtml += '<tr class="' + rowClass + '">';
                            resultsHtml += '<td>' + result.row_number + '</td>';
                            resultsHtml += '<td>' + (result.client_name || 'N/A') + '</td>';
                            resultsHtml += '<td>' + (result.machine || 'N/A') + '</td>';
                            resultsHtml += '<td>' + statusBadge + '</td>';
                            resultsHtml += '<td>' + details + '</td>';
                            resultsHtml += '</tr>';
                        });
                        resultsHtml += '</tbody></table></div>';
                        $('#import-results').html(summary + resultsHtml).show();
                        $('#closeImportResults').show();
                        loadRecentImports();
                    }
                });
            }, 1000);
        }

        $('#closeImportResults').click(function() {
            $('#import-results').hide();
            $(this).hide();
        });

        $('#updateClientProductForm').on('submit', function(e) {
            e.preventDefault();
            submitUpdate("{{ route('clients.update-products') }}", '#updateClientProductButton', '#update-product-spinner');
        });

        function submitUpdate(url, buttonSelector, spinnerSelector) {
            var formData = new FormData($(buttonSelector).closest('form')[0]);
            $(buttonSelector).prop('disabled', true);
            $(spinnerSelector).show();
            $('.update-progress').show();
            $('#update-progress-bar').width('0%').text('0%');
            $('#update-status').text('Uploading...');
            $('#update-results').hide().empty();
            $('#closeUpdateResults').hide();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    checkUpdateProgress(response.import_id, buttonSelector, spinnerSelector);
                },
                error: function(xhr) {
                    $(buttonSelector).prop('disabled', false);
                    $(spinnerSelector).hide();
                    showToast('Error: ' + xhr.responseText, 'danger');
                }
            });
        }

        function checkUpdateProgress(importId, buttonSelector, spinnerSelector) {
            var interval = setInterval(function() {
                $.get('/clients/import/progress/' + importId, function(data) {
                    var percent = data.percentage;
                    $('#update-progress-bar').css('width', percent + '%').text(percent + '%');
                    $('#update-status').text('Status: ' + data.status + ' | Processed: ' + data.processed_rows + ' / ' + data.total_rows);

                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(interval);
                        $(buttonSelector).prop('disabled', false);
                        $(spinnerSelector).hide();

                        if (data.status === 'completed') {
                            showToast('Update completed successfully', 'success');
                            clientsTable.ajax.reload();
                            setTimeout(function() {
                                $('.update-progress').fadeOut();
                            }, 2000);
                        }

                        var summary = '<div class="alert alert-info"><strong>Summary:</strong><br>Total: ' + (data.total_rows || data.processed_rows) + ' | Success: ' + (data.success_count || 0) + ' | Failed: ' + (data.failed_count || 0) + '</div>';
                        var resultsHtml = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm">';
                        resultsHtml += '<thead class="table-light"><tr><th>Row</th><th>Client</th><th>Machine</th><th>Status</th><th>Details</th></tr></thead><tbody>';
                        $.each(data.results, function(index, result) {
                            var rowClass = '';
                            var statusBadge = '';

                            if (result.status === 'failed') {
                                rowClass = 'table-danger';
                                statusBadge = '<span class="badge bg-danger">Failed</span>';
                            } else if (result.status === 'success_with_warnings' || (result.warnings && result.warnings.length > 0)) {
                                rowClass = 'table-warning';
                                statusBadge = '<span class="badge bg-warning text-dark">Warning</span>';
                            } else if (result.status === 'skipped') {
                                rowClass = 'table-secondary';
                                statusBadge = '<span class="badge bg-secondary">Skipped</span>';
                            } else {
                                statusBadge = '<span class="badge bg-success">Success</span>';
                            }

                            var details = result.reason || '';
                            if (result.warnings && result.warnings.length > 0) {
                                details += '<ul class="mb-0 ps-3 small text-muted mt-1">';
                                $.each(result.warnings, function(i, w) {
                                    details += '<li>' + w + '</li>';
                                });
                                details += '</ul>';
                            }

                            resultsHtml += '<tr class="' + rowClass + '">';
                            resultsHtml += '<td>' + result.row_number + '</td>';
                            resultsHtml += '<td>' + (result.client_name || 'N/A') + '</td>';
                            resultsHtml += '<td>' + (result.machine || 'N/A') + '</td>';
                            resultsHtml += '<td>' + statusBadge + '</td>';
                            resultsHtml += '<td>' + details + '</td>';
                            resultsHtml += '</tr>';
                        });
                        resultsHtml += '</tbody></table></div>';
                        $('#update-results').html(summary + resultsHtml).show();
                        $('#closeUpdateResults').show();
                        loadRecentImports();
                    }
                });
            }, 1000);
        }

        $('#closeUpdateResults').click(function() {
            $('#update-results').hide();
            $(this).hide();
        });

        function loadRecentImports() {
            $.get("{{ route('clients.import.recent') }}?type=client", function(data) {
                var rows = data.length ? data.map((r, i) => {
                    var summary = '<strong>' + r.items_count + ' Clients</strong><br>';
                    if (r.clients && r.clients.length > 0) {
                        var names = r.clients.map(function(c) { return c.name; });
                        summary += '<small class="text-muted">' + names.join(', ');
                        if (r.items_count > 5) summary += '...';
                        summary += '</small>';
                    }
                    return '<tr><td>' + (i + 1) + '</td><td>' + new Date(r.created_at).toLocaleString() + '</td><td>' + summary + '</td><td><button class="btn btn-sm btn-danger undo-import-btn" data-id="' + r.id + '">Undo</button></td></tr>';
                }).join('') : '<tr><td colspan="4" class="text-center">No recent client imports.</td></tr>';
                $('#recentClientImportsTable tbody').html(rows);
            });
            $.get("{{ route('clients.import.recent') }}?type=product", function(data) {
                var rows = data.length ? data.map((r, i) => {
                    var summary = '<strong>' + r.items_count + ' Products</strong><br>';
                    if (r.client_products && r.client_products.length > 0) {
                        var names = r.client_products.map(function(p) { return p.name; });
                        summary += '<small class="text-muted">' + names.join(', ');
                        if (r.items_count > 5) summary += '...';
                        summary += '</small>';
                    }
                    return '<tr><td>' + (i + 1) + '</td><td>' + new Date(r.created_at).toLocaleString() + '</td><td>' + summary + '</td><td><button class="btn btn-sm btn-danger undo-import-btn" data-id="' + r.id + '">Undo</button></td></tr>';
                }).join('') : '<tr><td colspan="4" class="text-center">No recent product imports.</td></tr>';
                $('#recentProductImportsTable tbody').html(rows);
            });
            $.get("{{ route('clients.import.recent') }}?type=update_product", function(data) {
                var rows = data.length ? data.map((r, i) => {
                    var summary = '<strong>' + r.items_count + ' Updates</strong><br>';
                    if (r.updated_client_products && r.updated_client_products.length > 0) {
                        var names = r.updated_client_products.map(function(p) { return p.name; });
                        summary += '<small class="text-muted">' + names.join(', ');
                        if (r.items_count > 5) summary += '...';
                        summary += '</small>';
                    }
                    return '<tr><td>' + (i + 1) + '</td><td>' + new Date(r.created_at).toLocaleString() + '</td><td>' + summary + '</td><td><button class="btn btn-sm btn-danger undo-import-btn" data-id="' + r.id + '">Undo</button></td></tr>';
                }).join('') : '<tr><td colspan="4" class="text-center">No recent product updates.</td></tr>';
                $('#recentProductUpdatesTable tbody').html(rows);
            });
        }

        $(document).on('click', '.undo-import-btn', function() {
            $('#undoImportId').val($(this).data('id'));
            $('#undoImportModal').modal('show');
        });

        $('#confirmUndoImport').on('click', function() {
            var id = $('#undoImportId').val();
            var btn = $(this);
            btn.prop('disabled', true).text('Undoing...');
            $.ajax({
                url: '/clients/import/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#undoImportModal').modal('hide');
                    loadRecentImports();
                    clientsTable.ajax.reload();
                },
                complete: function() {
                    btn.prop('disabled', false).text('Undo Import');
                }
            });
        });

        // Other basic logic (Edit, View, Delete, Create) remains same as before...
        // ... (simplified for brevity but keep existing logic functional)

        // View Client
        $('#clients-table').on('click', '.view-client-btn', function(e) {
            e.stopPropagation();
            window.location.href = '/clients/' + $(this).data('id');
        });

        $('#clients-table tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('.view-client-btn, .edit-client-btn, .delete-client-btn, .undo-import-btn, .call-number').length) {
                return;
            }
            var data = clientsTable.row(this).data();
            if (data) {
                window.location.href = '/clients/' + data.id;
            }
        });

        let editProductIndex = 0;

        function renderProductRow(container, index, data = null) {
            let productsHtml = '';
            @foreach($products as $product)
            productsHtml += `<option value="{{ $product->id }}" data-name="{{ $product->name }}" ${data && data.product_id == {{ $product->id }} ? 'selected' : ''}>
                    {{ $product->name }}{{ $product->category ? ' (' . $product->category->name . ')' : '' }}
                </option>`;
            @endforeach

            let html = `
                <div class="product-item border p-3 mb-3 position-relative rounded">
                    <button type="button" class="btn-close remove-product position-absolute top-0 end-0 m-2" aria-label="Close"></button>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select product-select" name="products[${index}][product_id]" required>
                                <option value="">Select Product</option>
                                ${productsHtml}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Model</label>
                            <select class="form-select model-select" name="products[${index}][product_model_id]">
                                <option value="">Select Model</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Machine Serial Number</label>
                            <input type="text" class="form-control" name="products[${index}][machine_serial_number]" value="${data ? (data.machine_serial_number || '') : ''}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Engine Serial Number</label>
                            <input type="text" class="form-control" name="products[${index}][engine_serial_number]" value="${data ? (data.engine_serial_number || '') : ''}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Engine Model</label>
                            <input type="text" class="form-control" name="products[${index}][engine_model]" value="${data ? (data.engine_model || '') : ''}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">DOC (Date of Commissioning)</label>
                            <input type="date" class="form-control" name="products[${index}][doc]" value="${data ? (data.doc || '') : ''}">
                        </div>
                    </div>
                </div>`;

            let $row = $(html);
            $(container).append($row);

            // If data exists, trigger product change to load models
            if (data && data.product_id) {
                let $modelSelect = $row.find('.model-select');
                $.ajax({
                    url: '/products/' + data.product_id + '/models',
                    type: 'GET',
                    success: function(response) {
                        $.each(response.product_models, function(key, model) {
                            $modelSelect.append(`<option value="${model.id}" ${data.product_model_id == model.id ? 'selected' : ''}>${model.name}</option>`);
                        });
                    }
                });
            }
        }

        // Edit Client
        $('#clients-table').on('click', '.edit-client-btn', function() {
            let clientId = $(this).data('id');
            $.get('/clients/' + clientId + '/edit', function(data) {
                $('#editClientModal').modal('show');
                $('#editClientId').val(data.id);
                $('#editSalutation').val(data.salutation);
                $('#editDealership').val(data.dealership_id);
                $('#editClientName').val(data.name);
                $('#editClientPhone').val(data.phone_number);
                $('#editClientEmail').val(data.email);
                $('#editAddress').val(data.address);

                // Clear and populate products
                $('#editProductContainer').empty();
                editProductIndex = 0;
                if (data.products && data.products.length > 0) {
                    data.products.forEach(product => {
                        renderProductRow('#editProductContainer', editProductIndex, product);
                        editProductIndex++;
                    });
                } else {
                    renderProductRow('#editProductContainer', editProductIndex);
                    editProductIndex++;
                }
            });
        });

        $('#addEditProduct').click(function() {
            renderProductRow('#editProductContainer', editProductIndex);
            editProductIndex++;
        });

        $('#editClientForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '/clients/' + $('#editClientId').val(),
                type: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function() {
                    clientsTable.ajax.reload();
                    $('#editClientModal').modal('hide');
                    showToast('Updated successfully', 'success');
                }
            });
        });

        $('#clients-table').on('click', '.delete-client-btn', function() {
            clientIdToDelete = $(this).data('id');
            $('#deleteClientName').text($(this).data('client-name'));
            $('#deleteClientModal').modal('show');
        });

        $('#confirmDeleteClient').on('click', function() {
            $.ajax({
                url: '/clients/' + clientIdToDelete,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    clientsTable.ajax.reload();
                    $('#deleteClientModal').modal('hide');
                    showToast('Deleted successfully', 'success');
                }
            });
        });

        $('#createClientForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('clients.store') }}",
                type: 'POST',
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function() {
                    showToast('Created successfully', 'success');
                    $('#createClientForm')[0].reset();
                    $('#productContainer').find('.product-item:not(:first)').remove();
                    clientsTable.ajax.reload();
                    bootstrap.Tab.getInstance(document.querySelector('#view-tab')).show();
                    // Re-trigger Kerala districts after reset if needed
                    if ($('#createStateId').val()) {
                        $('#createStateId').trigger('change');
                    }
                }
            });
        });

        let productIndex = 1;

        $('#addProduct').click(function() {
            let html = `
                <div class="product-item border p-3 mb-3 position-relative rounded">
                    <button type="button" class="btn-close remove-product position-absolute top-0 end-0 m-2" aria-label="Close"></button>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select product-select" name="products[${productIndex}][product_id]" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-name="{{ $product->name }}">
                                        {{ $product->name }}{{ $product->category ? ' (' . $product->category->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Model</label>
                            <select class="form-select model-select" name="products[${productIndex}][product_model_id]">
                                <option value="">Select Model</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Machine Serial Number</label>
                            <input type="text" class="form-control" name="products[${productIndex}][machine_serial_number]">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Engine Serial Number</label>
                            <input type="text" class="form-control" name="products[${productIndex}][engine_serial_number]">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Engine Model</label>
                            <input type="text" class="form-control" name="products[${productIndex}][engine_model]">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">DOC (Date of Commissioning)</label>
                            <input type="date" class="form-control" name="products[${productIndex}][doc]">
                        </div>
                    </div>
                </div>`;
            $('#productContainer').append(html);
            productIndex++;
        });

        $(document).on('click', '.remove-product', function() {
            $(this).closest('.product-item').remove();
        });

        // Handle State-District Change
        $('#createStateId').on('change', function() {
            var stateId = $(this).val();
            var stateName = $(this).find('option:selected').data('name');
            $('#createDistrictId').empty().append('<option value="">Select District</option>');

            if (stateId && stateName) {
                $.ajax({
                    url: '/districts/by-state/' + stateName,
                    type: 'GET',
                    success: function(data) {
                        $.each(data, function(key, district) {
                            $('#createDistrictId').append('<option value="' + district.id + '">' + district.name + '</option>');
                        });
                    }
                });
            }
        });

        // Handle Product-Model Change
        $(document).on('change', '.product-select', function() {
            var productId = $(this).val();
            var row = $(this).closest('.row');
            var modelSelect = row.find('.model-select');

            modelSelect.empty().append('<option value="">Select Model</option>');

            if (productId) {
                $.ajax({
                    url: '/products/' + productId + '/models',
                    type: 'GET',
                    success: function(data) {
                        $.each(data.product_models, function(key, model) {
                            modelSelect.append('<option value="' + model.id + '">' + model.name + '</option>');
                        });
                    }
                });
            }
        });

        // Make date inputs open picker on click anywhere in the field
        $(document).on('click', 'input[type="date"]', function() {
            if (this.showPicker) {
                this.showPicker();
            }
        });

        // Trigger district load for Kerala on page load
        if ($('#createStateId').val()) {
            $('#createStateId').trigger('change');
        }

        loadRecentImports();
    });
</script>
@endpush