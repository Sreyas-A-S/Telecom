@extends('layouts.admin')

@section('title', 'Loss Orders')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Loss Orders</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Loss Orders</li>
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
            <ul class="nav nav-tabs d-flex" id="lossOrderTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="view-tab" data-bs-toggle="tab" data-bs-target="#view"
                        type="button" role="tab" aria-controls="view" aria-selected="true">View Loss
                        Orders</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create"
                        type="button" role="tab" aria-controls="create" aria-selected="false">Create Loss
                        Order</button>
                </li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="lossOrderTabContent">
                        <div class="tab-pane fade show active" id="view" role="tabpanel"
                            aria-labelledby="view-tab">
                            @if(!checkMenu(Session::get('role_id'), 9, 'read'))
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to view loss orders.
                            </div>
                            @else
                            <div class="table-responsive">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="filterMonthYear" class="form-label">Month & Year</label>
                                        <input type="month" class="form-control" id="filterMonthYear"
                                            value="{{ date('Y-m') }}">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="filterMonthYearAny">
                                            <label class="form-check-label" for="filterMonthYearAny">
                                                Any
                                            </label>
                                        </div>
                                    </div>
                                    @if(!Auth::user()->employee || !Auth::user()->employee->dealership_id)
                                    <div class="col-md-3">
                                        <label for="filterDealership" class="form-label">Dealership</label>
                                        <select class="form-select" id="filterDealership">
                                            <option value="">All Dealerships</option>
                                            @foreach ($dealerships as $dealership)
                                            <option value="{{ $dealership->id }}">{{ $dealership->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-md-3">
                                        <label for="filterProduct" class="form-label">Product</label>
                                        <input type="text" class="form-control" id="filterProduct"
                                            list="filterProductsDatalist" placeholder="Filter by Product">
                                        <datalist id="filterProductsDatalist">
                                            @foreach ($products as $product)
                                            <option value="{{ $product->name }}" data-id="{{ $product->id }}">
                                                @endforeach
                                        </datalist>
                                        <input type="hidden" id="filterProductId">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filterModel" class="form-label">Model</label>
                                        <input type="text" class="form-control" id="filterModel"
                                            list="filterModelsDatalist" placeholder="Filter by Model">
                                        <datalist id="filterModelsDatalist"></datalist>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="globalSearchInput" class="form-label">Search</label>
                                        <input type="search" class="form-control" id="globalSearchInput"
                                            placeholder="">
                                    </div>
                                </div>
                                <table class="display" id="loss-orders-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Month</th>
                                            <th>Dealership</th>
                                            <th>Product & Model</th>
                                            <th>Customer Location</th>
                                            <th>Segment</th>
                                            <th>Application</th>
                                            <th>Financier</th>
                                            <th>Category</th>
                                            <th>Participation</th>
                                            <th>Reasons for Loss</th>
                                            <th>Remarks</th>
                                            <th>Engineer Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                            @if(!checkMenu(Session::get('role_id'), 9, 'create'))
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to create a new loss order.
                            </div>
                            @else
                            <form id="createLossOrderForm" class="theme-form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="createMonth" class="form-label">Month <span
                                                class="text-danger">*</span></label>
                                        <input type="month" class="form-control" id="createMonth"
                                            name="month" required value="{{ date('Y-m') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createDealershipId" class="form-label">Dealership <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="createDealershipId"
                                            name="selected_dealership_id" required>
                                            <option value="">Select Dealership</option>
                                            @foreach ($dealerships as $dealership)
                                            <option value="{{ $dealership->id }}">{{ $dealership->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createProductId" class="form-label">Product <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="createProductId" name="product_id"
                                            required>
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createProductModelId" class="form-label">Product Model</label>
                                        <select class="form-select" id="createProductModelId" name="product_model_id">
                                            <option value="">Select Product Model</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createModelSeriesId" class="form-label">Model Series</label>
                                        <select class="form-select" id="createModelSeriesId" name="model_series_id">
                                            <option value="">Select Model Series</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createTonnage" class="form-label">Tonnage (Ton)</label>
                                        <input type="number" step="0.01" class="form-control"
                                            id="createTonnage" name="tonnage">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createCustomer" class="form-label">Customer</label>
                                        <input type="text" class="form-control" id="createCustomer"
                                            name="customer">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createSegment" class="form-label">Segment</label>
                                        <select class="form-select" id="createSegment" name="segment">
                                            <option value="">Select Segment</option>
                                            <option value="Rented">Rented</option>
                                            <option value="Captive">Captive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createApplication" class="form-label">Application</label>
                                        <input type="text" class="form-control" id="createApplication"
                                            name="application">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createFinancier" class="form-label">Financier</label>
                                        <input type="text" class="form-control" id="createFinancier"
                                            name="financier">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createDistrict" class="form-label">District <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="createDistrict" name="district" required>
                                            <option value="">Select District</option>
                                            @foreach ($keralaDistricts as $district)
                                            <option value="{{ $district->name }}">{{ $district->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createCategory" class="form-label">Category</label>
                                        <input type="text" class="form-control" id="createCategory"
                                            name="category">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createParticipation" class="form-label">Participation</label>
                                        <select class="form-select" id="createParticipation"
                                            name="participation">
                                            <option value="">Select Participation</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createEngineerName" class="form-label">Engineer Name</label>
                                        <input type="text" class="form-control" id="createEngineerName"
                                            name="engineer_name">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="createReasonsForLoss" class="form-label">Reasons for
                                            Loss</label>
                                        <textarea class="form-control" id="createReasonsForLoss" name="reasons_for_loss" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="createRemarks" class="form-label">Remarks</label>
                                        <textarea class="form-control" id="createRemarks" name="remarks" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">Save Loss Order</button>
                                    </div>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Loss Order Modal -->
<div class="modal fade" id="viewLossOrderModal" tabindex="-1" aria-labelledby="viewLossOrderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLossOrderModalLabel">View Loss Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="viewLossOrderContent">
                    <!-- Loss order details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Loss Order Modal -->
<div class="modal fade" id="editLossOrderModal" tabindex="-1" aria-labelledby="editLossOrderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editLossOrderModalLabel">Edit Loss Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(!checkMenu(Session::get('role_id'), 9, 'update'))
            <div class="alert alert-danger m-3" role="alert">
                You do not have permission to edit loss orders.
            </div>
            @else
            <form id="editLossOrderForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editLossOrderId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editMonth" class="form-label">Month <span
                                    class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="editMonth" name="month" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDealershipId" class="form-label">Dealership <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="editDealershipId" name="dealership_id" required>
                                <option value="">Select Dealership</option>
                                @foreach ($dealerships as $dealership)
                                <option value="{{ $dealership->id }}">{{ $dealership->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editTonnage" class="form-label">Tonnage</label>
                            <input type="number" step="0.01" class="form-control" id="editTonnage"
                                name="tonnage">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductId" class="form-label">Product <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="editProductId" name="product_id" required>
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductModelId" class="form-label">Product Model</label>
                            <select class="form-select" id="editProductModelId" name="product_model_id">
                                <option value="">Select Product Model</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editModelSeriesId" class="form-label">Model Series</label>
                            <select class="form-select" id="editModelSeriesId" name="model_series_id">
                                <option value="">Select Model Series</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCustomer" class="form-label">Customer</label>
                            <input type="text" class="form-control" id="editCustomer" name="customer">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editSegment" class="form-label">Segment</label>
                            <select class="form-select" id="editSegment" name="segment">
                                <option value="">Select Segment</option>
                                <option value="Rented">Rented</option>
                                <option value="Captive">Captive</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editApplication" class="form-label">Application</label>
                            <input type="text" class="form-control" id="editApplication" name="application">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editFinancier" class="form-label">Financier</label>
                            <input type="text" class="form-control" id="editFinancier" name="financier">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDistrict" class="form-label">District</label>
                            <input type="text" class="form-control" id="editDistrict" name="district">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="editCategory" name="category">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editParticipation" class="form-label">Participation</label>
                            <select class="form-select" id="editParticipation" name="participation">
                                <option value="">Select Participation</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editEngineerName" class="form-label">Engineer Name</label>
                            <input type="text" class="form-control" id="editEngineerName"
                                name="engineer_name">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editReasonsForLoss" class="form-label">Reasons for Loss</label>
                            <textarea class="form-control" id="editReasonsForLoss" name="reasons_for_loss" rows="3"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editRemarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="editRemarks" name="remarks" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Delete Loss Order Modal -->
<div class="modal fade" id="deleteLossOrderModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteLossOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLossOrderModalLabel">Delete Loss Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(!checkMenu(Session::get('role_id'), 9, 'delete'))
            <div class="alert alert-danger m-3" role="alert">
                You do not have permission to delete loss orders.
            </div>
            @else
            <div class="modal-body">
                <p>Are you sure you want to delete this loss order?</p>
                <input type="hidden" id="deleteLossOrderId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteLossOrder">Delete</button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Remarks Modal -->
<div class="modal fade" id="remarksModal" tabindex="-1" aria-labelledby="remarksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="remarksModalLabel">Full Remarks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="fullRemarksText"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
@endpush

@push('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#createProductId').select2({
            dropdownParent: $('#createLossOrderForm'),
            tags: true // Allow free text entry
        });

        $('#createModel').select2({
            dropdownParent: $('#createLossOrderForm'),
            tags: true // Allow free text entry
        });

        $('#createProductId').on('change', function() {
            var productId = $(this).val();
            var productModelSelect = $('#createProductModelId');
            var modelSeriesSelect = $('#createModelSeriesId');

            productModelSelect.empty().append('<option value="">Select Product Model</option>');
            modelSeriesSelect.empty().append('<option value="">Select Model Series</option>');

            if (productId) {
                $.ajax({
                    url: '/products/' + productId + '/models',
                    method: 'GET',
                    success: function(data) {
                        $.each(data.product_models, function(index, model) {
                            productModelSelect.append('<option value="' + model.id + '">' + model.name + '</option>');
                        });
                    },
                    error: function(error) {
                        console.error('Error fetching product models:', error);
                    }
                });
            }
        });

        $('#createProductModelId').on('change', function() {
            var productModelId = $(this).val();
            var modelSeriesSelect = $('#createModelSeriesId');

            modelSeriesSelect.empty().append('<option value="">Select Model Series</option>');

            if (productModelId) {
                $.ajax({
                    url: '/product-models/' + productModelId + '/model-series',
                    method: 'GET',
                    success: function(data) {
                        $.each(data.model_series, function(index, series) {
                            modelSeriesSelect.append('<option value="' + series.id + '">' + series.name + '</option>');
                        });
                    },
                    error: function(error) {
                        console.error('Error fetching model series:', error);
                    }
                });
            }
        });

        $('#editProductId').select2({
            dropdownParent: $('#editLossOrderModal')
        });
        var lossOrdersTable = $('#loss-orders-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-end'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                    extend: 'csv',
                    className: 'btn btn-sm btn-primary text-white',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                    }
                },
                {
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-sm btn-success text-white',
                    action: function(e, dt, node, config) {
                        var info = dt.page.info();
                        var params = $.param({
                            month_year: $('#filterMonthYear').val(),
                            dealership_id: $('#filterDealership').val(),
                            product_id: $('#filterProductId').val(),
                            model_name: $('#filterModel').val(),
                            search_value: $('#globalSearchInput').val(),
                            start: info.start,
                            length: info.length
                        });
                        window.location.href = "{{ route('loss-orders.export-excel') }}?" + params;
                    }
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-danger text-white'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info text-white',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                    }
                }
            ],

            order: [
                [0, 'desc']
            ], // Default sort by Sl No

            columns: [{
                    data: 'sl_no',
                    name: 'sl_no',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'month',
                    name: 'month',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            // Assuming data is in 'YYYY-MM' format
                            var parts = data.split('-');
                            if (parts.length === 2) {
                                var year = parts[0];
                                var monthNum = parseInt(parts[1], 10) -
                                    1; // Month is 0-indexed in JavaScript Date
                                var date = new Date(year, monthNum);
                                return date.toLocaleString('default', {
                                    month: 'long',
                                    year: 'numeric'
                                });
                            }
                        }
                        return data; // Return original data for other types (e.g., 'sort')
                    }
                },
                {
                    data: 'dealership_name',
                    name: 'dealership_name',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'product_info',
                    name: 'product_info',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'customer_location',
                    name: 'customer_location',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'segment_badge',
                    name: 'segment',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'application',
                    name: 'application',
                    orderable: false
                },
                {
                    data: 'financier',
                    name: 'financier',
                    orderable: false
                },
                {
                    data: 'category',
                    name: 'category',
                    orderable: false
                },
                {
                    data: 'participation_badge',
                    name: 'participation',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'reasons_for_loss',
                    name: 'reasons_for_loss',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'remarks_display',
                    name: 'remarks',
                    orderable: false,
                    searchable: false
                }, // Use remarks_display
                {
                    data: 'engineer_name',
                    name: 'loss_orders.engineer_name',
                    searchable: true
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Event listeners for filters
        $('#filterMonthYear, #filterDealership, #filterProduct, #filterModel').on('change', function() {
            lossOrdersTable.ajax.reload();
        });

        $('#globalSearchInput').on('keyup', function() {
            lossOrdersTable.ajax.reload();
        });

        // Store the last selected month/year value
        var lastFilterMonthYearValue = $('#filterMonthYear').val();

        // Handle 'Any' checkbox for Month & Year
        $('#filterMonthYearAny').on('change', function() {
            if ($(this).is(':checked')) {
                lastFilterMonthYearValue = $('#filterMonthYear')
                    .val(); // Store current value before clearing
                $('#filterMonthYear').val('').prop('disabled', true);
            } else {
                $('#filterMonthYear').val(lastFilterMonthYearValue).prop('disabled',
                    false); // Restore stored value
            }
            lossOrdersTable.ajax.reload();
        });

        // Product Datalist functionality
        $('#filterProduct').on('input', function() {
            var productName = $(this).val();
            var productId = '';
            $('#filterProductsDatalist option').each(function() {
                if ($(this).val() === productName) {
                    productId = $(this).data('id');
                    return false;
                }
            });
            $('#filterProductId').val(productId);
            // Reload DataTable if product is selected/cleared
            lossOrdersTable.ajax.reload();
        });

        // Dynamic Model Datalist based on Product selection
        $('#filterProduct').on('change', function() {
            var productId = $('#filterProductId').val();
            var modelsDatalist = $('#filterModelsDatalist');
            modelsDatalist.empty(); // Clear previous model options
            $('#filterModel').val(''); // Clear selected model

            if (productId) {
                $.ajax({
                    url: '/products/' + productId + '/models',
                    method: 'GET',
                    success: function(data) {
                        $.each(data, function(index, model) {
                            modelsDatalist.append('<option value="' + model.name +
                                '">');
                        });
                    },
                    error: function(error) {
                        console.error('Error fetching models:', error);
                    }
                });
            }
            lossOrdersTable.ajax.reload();
        });


        // Handle Create Loss Order Form Submission
        $('#createLossOrderForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serializeArray();

            // Get selected product name
            var productName = $('#createProductId option:selected').text();
            if (productName === 'Select Product') {
                productName = '';
            }
            formData.push({
                name: 'product_name',
                value: productName
            });

            // Get selected product model name
            var productModelName = $('#createProductModelId option:selected').text();
            if (productModelName === 'Select Product Model') {
                productModelName = '';
            }
            formData.push({
                name: 'product_model_name',
                value: productModelName
            });

            // Get selected model series name
            var modelSeriesName = $('#createModelSeriesId option:selected').text();
            if (modelSeriesName === 'Select Model Series') {
                modelSeriesName = '';
            }
            formData.push({
                name: 'model_series_name',
                value: modelSeriesName
            });

            // Remove the product_id, product_model_id, and model_series_id from formData
            formData = formData.filter(function(item) {
                return item.name !== 'product_id' && item.name !== 'product_model_id' && item.name !== 'model_series_id';
            });

            // Convert array to URL-encoded string
            formData = $.param(formData);

            $.ajax({
                url: '/loss-orders',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    lossOrdersTable.ajax.reload();
                    $('#createLossOrderForm')[0].reset(); // Reset the form
                    $('#createProductId').val('').trigger('change'); // Clear select2 product
                    $('#createProductModelId').val('').trigger('change'); // Clear select2 product model
                    $('#createModelSeriesId').val('').trigger('change'); // Clear select2 model series
                },
                error: function(error) {
                    console.error('Error creating loss order:', error);
                    showToast('Error creating loss order.', 'danger');
                }
            });
        });

        // Handle View Button Click
        $('#loss-orders-table').on('click', '.view', function() {
            var id = $(this).find('a').data('id');

            $.ajax({
                url: '/loss-orders/' + id,
                method: 'GET',
                success: function(data) {
                    var product_name = data.product_name || '';
                    var product_model_name = data.product_model_name || '';
                    var model_series_name = data.model_series_name || '';

                    var html = '<div class="row">' +
                        '<div class="col-md-6"><p><strong>Month:</strong> ' + data.month +
                        '</p></div>' +
                        '<div class="col-md-6"><p><strong>Dealership:</strong> ' + (data.dealership ? data.dealership.name : 'N/A') + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Product:</strong> ' +
                        product_name + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Product Model:</strong> ' + product_model_name +
                        '</p></div>' +
                        '<div class="col-md-6"><p><strong>Model Series:</strong> ' + model_series_name +
                        '</p></div>' +
                        '<div class="col-md-6"><p><strong>Tonnage:</strong> ' + data
                        .tonnage + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Customer:</strong> ' + data
                        .customer + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Segment:</strong> ' + data
                        .segment + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Application:</strong> ' + data
                        .application + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Financier:</strong> ' + data
                        .financier + '</p></div>' +
                        '<div class="col-md-6"><p><strong>District:</strong> ' + data
                        .district + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Category:</strong> ' + data
                        .category + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Participation:</strong> ' + data
                        .participation + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Engineer Name:</strong> ' + data
                        .engineer_name + '</p></div>' +
                        '<div class="col-md-12"><p><strong>Reasons for Loss:</strong> ' +
                        data.reasons_for_loss + '</p></div>' +
                        '<div class="col-md-12"><p><strong>Remarks:</strong> ' + data
                        .remarks + '</p></div>' +
                        '</div>';
                    $('#viewLossOrderContent').html(html);
                    $('#viewLossOrderModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching loss order data:', error);
                    showToast('Error fetching loss order data.', 'danger');
                }
            });
        });

        // Handle Edit Button Click
        $('#loss-orders-table').on('click', '.edit', function() {
            var id = $(this).find('a').data('id');

            $.ajax({
                url: '/loss-orders/' + id + '/edit',
                method: 'GET',
                success: function(data) {
                    $('#editLossOrderId').val(data.id);
                    $('#editMonth').val(data.month);
                    $('#editDealershipId').val(data.dealership_id);
                    $('#editProductId').data('selected', data.product_id); // Store selected product ID
                    $('#editProductModelId').data('selected', data.product_model_id);
                    $('#editModelSeriesId').data('selected', data.model_series_id);

                    // Trigger change to load models, which will then load series
                    $('#editProductId').val(data.product_id).trigger('change');

                    $('#editTonnage').val(data.tonnage);
                    $('#editCustomer').val(data.customer);
                    $('#editSegment').val(data.segment);
                    $('#editApplication').val(data.application);
                    $('#editFinancier').val(data.financier);
                    $('#editDistrict').val(data.district);
                    $('#editCategory').val(data.category);
                    $('#editParticipation').val(data.participation);
                    $('#editReasonsForLoss').val(data.reasons_for_loss);
                    $('#editRemarks').val(data.remarks);
                    $('#editEngineerName').val(data.engineer_name);
                    $('#editLossOrderModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching loss order data:', error);
                    showToast('Error fetching loss order data.', 'danger');
                }
            });
        });

        // Handle Edit Form Submission
        $('#editLossOrderForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#editLossOrderId').val();
            var formData = $(this).serializeArray();

            // Get selected product name
            var productName = $('#editProductId option:selected').text();
            if (productName === 'Select Product') {
                productName = '';
            }
            formData.push({
                name: 'product_name',
                value: productName
            });

            // Get selected product model name
            var productModelName = $('#editProductModelId option:selected').text();
            if (productModelName === 'Select Product Model') {
                productModelName = '';
            }
            formData.push({
                name: 'product_model_name',
                value: productModelName
            });

            // Get selected model series name
            var modelSeriesName = $('#editModelSeriesId option:selected').text();
            if (modelSeriesName === 'Select Model Series') {
                modelSeriesName = '';
            }
            formData.push({
                name: 'model_series_name',
                value: modelSeriesName
            });

            // Remove the product_id, product_model_id, and model_series_id from formData
            formData = formData.filter(function(item) {
                return item.name !== 'product_id' && item.name !== 'product_model_id' && item.name !== 'model_series_id';
            });

            // Convert array to URL-encoded string
            formData = $.param(formData);

            $.ajax({
                url: '/loss-orders/' + id,
                method: 'POST', // Use POST for PUT/PATCH with _method
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    lossOrdersTable.ajax.reload();
                    $('#editLossOrderModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating loss order:', error);
                    showToast('Error updating loss order.', 'danger');
                }
            });
        });

        // Handle Delete Button Click
        $('#loss-orders-table').on('click', '.delete', function() {
            var id = $(this).find('a').data('id');
            $('#deleteLossOrderId').val(id);
            $('#deleteLossOrderModal').modal('show');
        });

        // Handle Delete Confirmation
        $('#confirmDeleteLossOrder').on('click', function() {
            var id = $('#deleteLossOrderId').val();

            $.ajax({
                url: '/loss-orders/' + id,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    lossOrdersTable.ajax.reload();
                    $('#deleteLossOrderModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting loss order:', error);
                    showToast('Error deleting loss order.', 'danger');
                }
            });
        });

        $('#editProductId').select2({
            dropdownParent: $('#editLossOrderModal')
        });

        $('#editProductId').on('change', function() {
            var productId = $(this).val();
            var productModelSelect = $('#editProductModelId');
            var modelSeriesSelect = $('#editModelSeriesId');

            productModelSelect.empty().append('<option value="">Select Product Model</option>');
            modelSeriesSelect.empty().append('<option value="">Select Model Series</option>');

            if (productId) {
                $.ajax({
                    url: '/products/' + productId + '/models',
                    method: 'GET',
                    success: function(data) {
                        var selectedProductModel = productModelSelect.data('selected');
                        $.each(data.product_models, function(index, model) {
                            var option = '<option value="' + model.id + '">' + model.name + '</option>';
                            if (model.id == selectedProductModel) {
                                option = '<option value="' + model.id + '" selected>' + model.name + '</option>';
                            }
                            productModelSelect.append(option);
                        });
                        productModelSelect.trigger('change'); // Trigger change to load model series
                    },
                    error: function(error) {
                        console.error('Error fetching product models:', error);
                    }
                });
            }
        });

        $('#editProductModelId').on('change', function() {
            var productModelId = $(this).val();
            var modelSeriesSelect = $('#editModelSeriesId');

            modelSeriesSelect.empty().append('<option value="">Select Model Series</option>');

            if (productModelId) {
                $.ajax({
                    url: '/product-models/' + productModelId + '/model-series',
                    method: 'GET',
                    success: function(data) {
                        var selectedModelSeries = modelSeriesSelect.data('selected');
                        $.each(data.model_series, function(index, series) {
                            var option = '<option value="' + series.id + '">' + series.name + '</option>';
                            if (series.id == selectedModelSeries) {
                                option = '<option value="' + series.id + '" selected>' + series.name + '</option>';
                            }
                            modelSeriesSelect.append(option);
                        });
                    },
                    error: function(error) {
                        console.error('Error fetching model series:', error);
                    }
                });
            }
        });

        // Read More/Read Less functionality for remarks
        $('#loss-orders-table').on('click', '.read-more-link', function() {
            var $this = $(this);
            var $truncated = $this.siblings('.remarks-truncated');
            var $full = $this.siblings('.remarks-full');

            if ($truncated.is(':visible')) {
                $truncated.toggle();
                $full.toggle();
                $this.text('Read Less');
            } else {
                $truncated.toggle();
                $full.toggle();
                $this.text('Read More');
            }
        });

    });
</script>
@endpush