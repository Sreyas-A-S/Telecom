@extends('layouts.admin')

@section('title', 'Parts')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Parts</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Parts</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
@if(checkMenu(Session::get('role_id'), 20, 'read'))
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="part-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="view-tab" data-bs-toggle="tab" href="#view"
                                role="tab" aria-controls="view" aria-selected="true">View Parts</a>
                        </li>
                        @if($permissions['can_create'])
                        <li class="nav-item">
                            <a class="nav-link" id="create-tab" data-bs-toggle="tab" href="#create"
                                role="tab" aria-controls="create" aria-selected="false">Create</a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" id="import-tab" data-bs-toggle="tab" href="#import"
                                role="tab" aria-controls="import" aria-selected="false">Import Parts</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="part-tabs-content">
                        <div class="tab-pane fade show active" id="view" role="tabpanel"
                            aria-labelledby="view-tab">
                            <div class="table-responsive">
                                <table class="display datatables" id="parts-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Part Number</th>
                                            <th>Material Description</th>
                                            <th>Machine</th>
                                            <th>Machine Model/Brand Category</th>
                                            <th>Unit Price</th>
                                            <th>HSN</th>
                                            <th>Bin</th>
                                            <th>Stock Quantity</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                            @if($permissions['can_create'])
                            <form id="partForm" class="p-3">
                                @csrf
                                <input type="hidden" id="partId" name="id">

                                <!-- Part Information -->
                                <h5 class="mb-3">Part Information</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="part_number" class="form-label">Part Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="part_number" name="part_number" placeholder="Enter Part Number" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="material_description" class="form-label">Material Description</label>
                                        <input type="text" class="form-control" id="material_description" name="material_description" placeholder="Enter Description">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="machine" class="form-label">Machine</label>
                                        <input type="text" class="form-control" id="machine" name="machine" placeholder="Machine Name">
                                    </div>
                                </div>

                                <hr class="mt-2 mb-4">

                                <!-- Product & Model Association -->
                                <h5 class="mb-3">Machine Details & Model Association</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="product_id" class="form-label">Machine Associated</label>
                                        <select class="form-select" id="product_id" name="products[]" multiple="multiple">
                                            <option value="">Select Machine</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="product_models" class="form-label">Machine Models</label>
                                        <select class="form-select" id="product_models" name="product_models[]" multiple="multiple">
                                        </select>
                                    </div>

                                </div>

                                <hr class="mt-2 mb-4">

                                <!-- Pricing & Tax -->
                                <h5 class="mb-3">Pricing & Tax</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="unit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tax_id" class="form-label">Tax</label>
                                        <select class="form-select" id="tax_id" name="tax_id">
                                            <option value="">Select Tax</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="hsn" class="form-label">HSN Code</label>
                                        <input type="text" class="form-control" id="hsn" name="hsn" placeholder="HSN Code">
                                    </div>
                                </div>

                                <hr class="mt-2 mb-4">

                                <!-- Inventory & Location -->
                                <h5 class="mb-3">Inventory & Location</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" placeholder="0" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="dealer" class="form-label">Dealer</label>
                                        <input type="text" class="form-control" id="dealer" name="dealer" placeholder="Dealer Name">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="bin" class="form-label">Bin / Location</label>
                                        <input type="text" class="form-control" id="bin" name="bin" placeholder="Bin Location">
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                            <label class="form-check-label" for="is_active">Is Active?</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 text-end">
                                    <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('partForm').reset()">Reset</button>
                                    <button type="submit" class="btn btn-primary">Save Part</button>
                                </div>
                            </form>
                            @else
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to create parts.
                            </div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Import Parts</h4>
                                </div>
                                <div class="card-body">
                                    <p class="mt-3">Download a sample Excel template: <button type="button"
                                            id="downloadTemplateBtn" class="btn btn-sm btn-outline-primary">Download
                                            Template</button></p>
                                    <form id="importPartForm" enctype="multipart/form-data" class="theme-form">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="importFile" class="form-label">Upload Excel File</label>
                                            <input class="form-control" type="file" id="importFile" name="excel_file" accept=".xlsx, .xls, .csv" required>
                                        </div>
                                        <button type="submit" id="importPartButton" class="btn btn-primary">Import Parts</button>
                                        <div id="import-spinner" class="spinner-border text-primary" role="status" style="display: none;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </form>

                                    <div class="progress mt-3" style="height: 25px; display: none;">
                                        <div id="import-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">Loading...</div>
                                    </div>
                                    <div id="import-status" class="mt-3"></div>
                                    <div id="import-errors" class="mt-3"></div>
                                    <div id="import-results" class="mt-3" style="display: none; max-height: 300px; overflow-y: auto;"></div>
                                    <button id="closeImportResults" class="btn btn-secondary mt-3" style="display: none;">Close Results</button>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Recent Imports</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="recentImportsTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Date</th>
                                                    <th>Summary</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Recent imports will be loaded here --}}
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
@else
<div class="alert alert-danger" role="alert">
    You do not have permission to view parts.
</div>
@endif


<!-- Edit Part Modal -->
<div class="modal fade" id="editPartModal" tabindex="-1" aria-labelledby="editPartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPartModalLabel">Edit Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPartForm">
                @csrf
                @method('PUT') {{-- Use PUT method for updates --}}
                <input type="hidden" id="editPartId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_part_number" class="form-label">Part Number</label>
                                <input type="text" class="form-control" id="edit_part_number" name="part_number"
                                    required>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_material_description" class="form-label">Material Description</label>
                                <input type="text" class="form-control" id="edit_material_description" name="material_description">
                            </div>
                        </div>
                                                <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="edit_machine" class="form-label">Machine</label>
                                                                <input type="text" class="form-control" id="edit_machine" name="machine">
                                                            </div>
                                                        </div>                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_product_id" class="form-label">Machine Associated</label>
                                <select class="form-select" id="edit_product_id" name="products[]"
                                    multiple="multiple">
                                    <option value="">Select Machine</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_product_models" class="form-label">Machine Models</label>
                                <select class="form-select" id="edit_product_models" name="product_models[]"
                                    multiple="multiple">
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_tax_id" class="form-label">Tax</label>
                                <select class="form-select" id="edit_tax_id" name="tax_id">
                                    <option value="">Select Tax</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_unit_price" class="form-label">Unit Price</label>
                                <input type="number" step="0.01" class="form-control" id="edit_unit_price"
                                    name="unit_price" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_hsn" class="form-label">HSN</label>
                                <input type="text" class="form-control" id="edit_hsn" name="hsn">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_bin" class="form-label">Bin</label>
                                <input type="text" class="form-control" id="edit_bin" name="bin">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="edit_stock_quantity"
                                    name="stock_quantity" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active"
                                    value="1">
                                <label class="form-check-label" for="edit_is_active">
                                    Is Active
                                </label>
                            </div>
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

<!-- View Part Modal -->
<div class="modal fade" id="viewPartModal" tabindex="-1" aria-labelledby="viewPartModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPartModalLabel">Part Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th>Part Number</th>
                            <td id="viewPartNumber"></td>
                        </tr>

                        <tr>
                            <th>Material Description</th>
                            <td id="viewMaterialDescription"></td>
                        </tr>
                        <tr>
                            <th>Machine</th>
                            <td id="viewMachine"></td>
                        </tr>
                        <tr>
                            <th>Machine Associated</th>
                            <td id="viewProduct"></td>
                        </tr>
                        <tr>
                            <th>Machine Model/Brand Category</th>
                            <td id="viewProductModels"></td>
                        </tr>

                        <tr>
                            <th>Tax</th>
                            <td id="viewTax"></td>
                        </tr>
                        <tr>
                            <th>Unit Price</th>
                            <td id="viewUnitPrice"></td>
                        </tr>
                        <tr>
                            <th>HSN</th>
                            <td id="viewHsn"></td>
                        </tr>
                        <tr>
                            <th>Dealer</th>
                            <td id="viewDealer"></td>
                        </tr>
                        <tr>
                            <th>Bin</th>
                            <td id="viewBin"></td>
                        </tr>
                        <tr>
                            <th>Stock Quantity</th>
                            <td id="viewStockQuantity"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="viewStatus"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Part Modal -->
<div class="modal fade" id="deletePartModal" tabindex="-1" role="dialog" aria-labelledby="deletePartModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePartModalLabel">Delete Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the part <strong id="deletePartName"></strong>?</p>
                <input type="hidden" id="deletePartId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePart">Delete</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
    var partsTable;

    $(function() {
        $('#product_id').select2({
            placeholder: "Select Machines",
            allowClear: true
        });

        $('#product_models').select2({
            placeholder: "Select Machine Models",
            allowClear: true
        });
        $('#tax_id').select2();

        // Initialize Select2 for edit modal dropdowns
        $('#edit_product_id').select2({
            placeholder: "Select Machines",
            allowClear: true
        });
        $('#edit_product_models').select2({
            placeholder: "Select Machine Models",
            allowClear: true
        });

        $('#edit_tax_id').select2();


        partsTable = $('#parts-table').DataTable({
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
            ajax: "{{ route('parts.datatable') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'part_number',
                    name: 'part_number'
                },
                {
                    data: 'material_description',
                    name: 'material_description',
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        return '<div style="max-width: 250px; white-space: normal;">' + data + '</div>';
                    }
                },
                {
                    data: 'dealer',
                    name: 'dealer'
                },
                 {
                    data: 'machine',
                    name: 'machine'
                },
                // {
                //     data: 'product_models_info',
                //     name: 'product_models_info',
                //     orderable: false,
                //     searchable: false
                // },
                {
                    data: 'unit_price',
                    name: 'unit_price'
                },
                {
                    data: 'hsn',
                    name: 'hsn'
                },
                {
                    data: 'bin',
                    name: 'bin'
                },
                {
                    data: 'stock_quantity',
                    name: 'stock_quantity'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var buttons =
                            '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                        @if(checkMenu(Session::get('role_id'), 20, 'update'))
                        buttons +=
                            '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' +
                            row.id +
                            '" class="edit-part-btn"><i class="icon-pencil"></i></a></li>';
                        @endif
                        @if(checkMenu(Session::get('role_id'), 20, 'delete'))
                        buttons +=
                            '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' +
                            row.id + '" data-name="' + row.part_number +
                            '" class="delete-part-btn"><i class="icon-trash"></i></a></li>';
                        @endif
                        @if(checkMenu(Session::get('role_id'), 20, 'read'))
                        buttons +=
                            '<li class="view"><a title="View" href="javascript:void(0)" data-id="' +
                            row.id +
                            '" class="view-part-btn"><i class="icon-eye"></i></a></li>';
                        @endif
                        buttons += '</ul>';
                        return buttons;

                    }

                },
            ]
        });

        function loadProductModels(productIds, selectedProductModelIds = []) {
            var productModelsSelect = $('#product_models');
            productModelsSelect.empty();

            if (!Array.isArray(productIds)) {
                productIds = productIds ? [productIds] : [];
            }

            if (productIds.length === 0) return;

            $.ajax({
                url: '{{ route('products.get-models') }}',
                method: 'GET',
                data: {
                    'product_ids[]': productIds
                },
                success: function(response) {
                    productModelsSelect.empty();
                    $.each(response.models, function(index, model) {
                        productModelsSelect.append(new Option(model.name, model.id, false, false));
                    });
                    if (selectedProductModelIds.length > 0) {
                        productModelsSelect.val(selectedProductModelIds).trigger('change');
                    }
                },
                error: function(error) {
                    console.error('Error loading product models:', error);
                    showToast('Error loading product models.', 'danger');
                }
            });
        }

        function loadProducts(selectedProductIds = []) {
            $.ajax({
                url: '{{ route('products.list') }}',
                method: 'GET',
                success: function(response) {
                    var productSelect = $('#product_id');
                    productSelect.empty();
                    $.each(response.data, function(index, product) {
                        productSelect.append('<option value="' + product.id + '">' + product.name + '</option>');
                    });
                    if (selectedProductIds.length > 0) {
                        productSelect.val(selectedProductIds).trigger('change');
                    }
                },
                error: function(error) {
                    console.error('Error loading products:', error);
                    showToast('Error loading products.', 'danger');
                }
            });
        }

        function loadTaxes(selectedTaxId = null) {
            $.ajax({
                url: '{{ route('taxes.index') }}',
                method: 'GET',
                success: function(response) {
                    var taxSelect = $('#tax_id');
                    taxSelect.empty().append('<option value="">Select Tax</option>');
                    $.each(response.data, function(index, tax) {
                        taxSelect.append('<option value="' + tax.id + '">' + tax.name + ' (' + tax.rate + '%)</option>');
                    });
                    if (selectedTaxId) taxSelect.val(selectedTaxId).trigger('change');
                },
                error: function(error) {
                    console.error('Error loading taxes:', error);
                    showToast('Error loading taxes.', 'danger');
                }
            });
        }

        function loadProductsForEdit(selectedProductIds = [], callback = null) {
            var productSelect = $('#edit_product_id');
            if (productSelect.data('select2')) productSelect.select2('destroy');
            productSelect.empty();

            $.ajax({
                url: '{{ route('products.list') }}',
                method: 'GET',
                success: function(response) {
                    $.each(response.data, function(index, product) {
                        productSelect.append('<option value="' + product.id + '">' + product.name + '</option>');
                    });
                    productSelect.select2({
                        placeholder: "Select Machines",
                        allowClear: true
                    });
                    if (selectedProductIds.length > 0) productSelect.val(selectedProductIds).trigger('change');
                    if (callback) callback();
                }
            });
        }

        function loadTaxesForEdit(selectedTaxId = null) {
            $.ajax({
                url: '{{ route('taxes.index') }}',
                method: 'GET',
                success: function(response) {
                    var taxSelect = $('#edit_tax_id');
                    taxSelect.empty().append('<option value="">Select Tax</option>');
                    $.each(response.data, function(index, tax) {
                        taxSelect.append('<option value="' + tax.id + '">' + tax.name + ' (' + tax.rate + '%)</option>');
                    });
                    if (selectedTaxId) taxSelect.val(selectedTaxId).trigger('change');
                }
            });
        }

        function loadProductModelsForEdit(productIds, selectedProductModelIds = [], callback = null) {
            var productModelsSelect = $('#edit_product_models');
            if (productModelsSelect.data('select2')) productModelsSelect.select2('destroy');
            productModelsSelect.empty();

            if (!Array.isArray(productIds)) productIds = productIds ? [productIds] : [];
            if (productIds.length === 0) {
                productModelsSelect.select2({
                    placeholder: "Select Machine Models",
                    allowClear: true
                });
                if (callback) callback();
                return;
            }

            $.ajax({
                url: '{{ route('products.get-models') }}',
                method: 'GET',
                data: {
                    'product_ids[]': productIds
                },
                success: function(response) {
                    $.each(response.models, function(index, model) {
                        productModelsSelect.append(new Option(model.name, model.id, false, false));
                    });
                    productModelsSelect.select2({
                        placeholder: "Select Product Models",
                        allowClear: true
                    });
                    if (selectedProductModelIds.length > 0) productModelsSelect.val(selectedProductModelIds).trigger('change');
                    if (callback) callback();
                }
            });
        }

        $('#product_id').on('change', function() {
            var ids = $(this).val();
            loadProductModels(ids);
        });

        $('#edit_product_id').on('change', function() {
            var ids = $(this).val();
            loadProductModelsForEdit(ids);
        });

        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route('parts.import.template') }}";
        });

        $('#importPartForm').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            var button = $('#importPartButton');
            var spinner = $('#import-spinner');
            var progressBar = $('#import-progress-bar');
            var progressContainer = $('.progress');
            var status = $('#import-status');
            var resultsContainer = $('#import-results');
            var closeResultsButton = $('#closeImportResults');

            button.hide();
            spinner.show();
            status.html('');
            resultsContainer.html('').hide();
            closeResultsButton.hide();
            progressContainer.show();
            progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');

            $.ajax({
                url: "{{ route('parts.import') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    spinner.hide();
                    button.show();
                    if (response.import_id) {
                        startPollingProgress(response.import_id);
                    } else {
                        progressContainer.hide();
                        loadRecentImports();
                        partsTable.ajax.reload();
                    }
                },
                error: function(response) {
                    spinner.hide();
                    button.show();
                    progressContainer.hide();
                    showToast(response.responseJSON?.message || 'Error occurred', 'danger');
                }
            });
        });

        var progressInterval;

        function startPollingProgress(importId) {
            var progressBar = $('#import-progress-bar');
            var progressContainer = $('.progress');
            var status = $('#import-status');
            var resultsContainer = $('#import-results');
            var closeResultsButton = $('#closeImportResults');

            if (progressInterval) clearInterval(progressInterval);

            progressInterval = setInterval(function() {
                $.ajax({
                    url: '/parts/import/progress/' + importId,
                    method: 'GET',
                    success: function(data) {
                        progressBar.css('width', data.percentage + '%').attr('aria-valuenow', data.percentage).text(data.percentage + '%');
                        status.html('<div class="alert alert-info">Status: ' + data.status + ' (' + data.processed_rows + '/' + data.total_rows + ')</div>');

                        if (data.status === 'completed') {
                            clearInterval(progressInterval);
                            progressInterval = null;
                            progressContainer.hide();
                            showToast('Import completed successfully.', 'success');
                            loadRecentImports();
                            partsTable.ajax.reload();
                            status.html('');

                            var resultsHtml = '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
                            var summary = '<div class="alert alert-success">' +
                                '<strong>Import Summary:</strong><br>' +
                                'Total Records: ' + (data.total_rows || data.processed_rows) + '<br>' +
                                'Successful: ' + (data.success_count || 0) + '<br>' +
                                'Failed: ' + (data.failed_count || 0) +
                                '</div>';
                            resultsHtml += summary;
                            resultsHtml += '<table class="table table-bordered table-striped table-sm">';
                            resultsHtml += '<thead class="table-light"><tr><th>Row</th><th>Part Number</th><th>Price</th><th>HSN</th><th>Machine Model/Brand Category</th><th>Status</th><th>Details</th></tr></thead><tbody>';

                            $.each(data.results, function(index, result) {
                                var rowClass = '';
                                if (result.status === 'failed') {
                                    rowClass = 'table-danger';
                                } else if (result.status === 'success_with_warnings') {
                                    rowClass = 'table-warning';
                                }
                                
                                var statusClass = 'success';
                                if (result.status === 'failed') statusClass = 'danger';
                                if (result.status === 'success_with_warnings') statusClass = 'warning';
                                
                                var statusBadge = '<span class="badge bg-' + statusClass + '">' + result.status + '</span>';
                                
                                var details = '';
                                if (result.status === 'failed') {
                                    details = '<span class="text-danger">' + (result.reason || 'Unknown error') + '</span>';
                                } else if (result.warnings && result.warnings.length > 0) {
                                    details = '<ul class="mb-0 ps-3 text-muted small">';
                                    $.each(result.warnings, function(i, w) {
                                        details += '<li>' + w + '</li>';
                                    });
                                    details += '</ul>';
                                } else {
                                    details = '<span class="text-muted small">OK</span>';
                                }

                                resultsHtml += '<tr class="' + rowClass + '">';
                                resultsHtml += '<td>' + result.row_number + '</td>';
                                resultsHtml += '<td>' + (result.part_name || 'N/A') + '</td>';
                                resultsHtml += '<td>' + (result.unit_price || '0.00') + '</td>';
                                resultsHtml += '<td>' + (result.hsn || 'N/A') + '</td>';
                                resultsHtml += '<td>' + (result.machine || 'N/A') + '</td>';
                                resultsHtml += '<td>' + statusBadge + '</td>';
                                resultsHtml += '<td>' + details + '</td>';
                                resultsHtml += '</tr>';
                            });
                            resultsHtml += '</tbody></table></div>';
                            resultsContainer.html(resultsHtml).show();
                            closeResultsButton.show();
                        } else if (data.status === 'failed') {
                            clearInterval(progressInterval);
                            progressInterval = null;
                            progressContainer.hide();
                            showToast('Import failed.', 'danger');
                        }
                    },
                    error: function() {
                        clearInterval(progressInterval);
                        progressInterval = null;
                    }
                });
            }, 2000);
        }

        $('#closeImportResults').on('click', function() {
            $('#import-results').hide();
            $(this).hide();
            $('#import-status').html('');
        });

        function loadRecentImports() {
            $.ajax({
                url: "{{ route('parts.import.recent') }}",
                method: 'GET',
                success: function(data) {
                    var tbody = $('#recentImportsTable tbody');
                    tbody.empty();
                    $.each(data, function(index, r) {
                        var summary = '<strong>' + r.parts_count + ' Parts</strong><br>';
                        if (r.parts && r.parts.length > 0) {
                            var names = r.parts.map(function(p) { return p.material_description; });
                            summary += '<small class="text-muted">' + names.join(', ');
                            if (r.parts_count > 5) {
                                summary += '...';
                            }
                            summary += '</small>';
                        }
                        tbody.append('<tr><td>' + (index + 1) + '</td><td>' + new Date(r.created_at).toLocaleString() + '</td><td>' + summary + '</td><td><button class="btn btn-sm btn-outline-danger undo-import-btn" data-id="' + r.id + '">Undo</button></td></tr>');
                    });
                }
            });
        }

        $(document).on('click', '.undo-import-btn', function() {
            if (!confirm('Are you sure?')) return;
            var importId = $(this).data('id');
            $.ajax({
                url: '/parts/import/' + importId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    loadRecentImports();
                    partsTable.ajax.reload();
                }
            });
        });

        $('#parts-table').on('click', '.edit-part-btn', function() {
            var partId = $(this).data('id');
            $.ajax({
                url: '/parts/' + partId + '/edit',
                method: 'GET',
                success: function(response) {
                    var data = response.part;
                    $('#editPartId').val(data.id);
                    $('#edit_part_number').val(data.part_number);
                    $('#edit_material_description').val(data.material_description);
                    $('#edit_machine').val(data.machine);
                    $('#edit_unit_price').val(data.unit_price);
                    $('#edit_hsn').val(data.hsn);
                    $('#edit_bin').val(data.bin);
                    $('#edit_stock_quantity').val(data.stock_quantity);
                    $('#edit_is_active').prop('checked', data.is_active);
                    loadProductsForEdit(data.products.map(p => p.id), function() {
                        loadProductModelsForEdit(data.products.map(p => p.id), data.product_models.map(m => m.id));
                    });
                    loadTaxesForEdit(data.tax_id);
                    $('#editPartModal').modal('show');
                }
            });
        });

        $('#editPartForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            if (!$('#edit_is_active').is(':checked')) formData += '&is_active=0';
            $.ajax({
                url: '/parts/' + $('#editPartId').val(),
                method: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    partsTable.ajax.reload();
                    $('#editPartModal').modal('hide');
                }
            });
        });

        $('#partForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            if (!$('#is_active').is(':checked')) formData += '&is_active=0';
            $.ajax({
                url: '{{ route('parts.store') }}',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    partsTable.ajax.reload();
                    $('#partForm')[0].reset();
                    $('#product_id').val(null).trigger('change');
                }
            });
        });

        $('#parts-table').on('click', '.delete-part-btn', function() {
            var id = $(this).data('id');
            $('#deletePartId').val(id);
            $('#deletePartName').text($(this).data('name'));
            $('#deletePartModal').modal('show');
        });

        $('#confirmDeletePart').on('click', function() {
            $.ajax({
                url: '/parts/' + $('#deletePartId').val(),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    partsTable.ajax.reload();
                    $('#deletePartModal').modal('hide');
                }
            });
        });

        $('#parts-table').on('click', '.view-part-btn', function() {
            $.ajax({
                url: '/parts/' + $(this).data('id'),
                method: 'GET',
                success: function(data) {
                    $('#viewPartNumber').text(data.part_number);
                    $('#viewMaterialDescription').text(data.material_description);
                    $('#viewMachine').text(data.machine);
                    $('#viewProduct').text(data.products.map(p => p.name).join(', '));
                    $('#viewProductModels').text(data.product_models.map(m => m.name).join(', '));
                    $('#viewTax').text(data.tax ? data.tax.name : 'N/A');
                    $('#viewUnitPrice').text(data.unit_price);
                    $('#viewHsn').text(data.hsn);
                    $('#viewDealer').text(data.dealer);
                    $('#viewBin').text(data.bin);
                    $('#viewStockQuantity').text(data.stock_quantity);
                    $('#viewStatus').text(data.is_active ? 'Active' : 'Inactive');
                    $('#viewPartModal').modal('show');
                }
            });
        });

        loadProducts();
        loadTaxes();
        if ($('#import-tab').hasClass('active')) loadRecentImports();
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (e.target.id === 'import-tab') loadRecentImports();
        });
    });
</script>
@endpush

@endsection