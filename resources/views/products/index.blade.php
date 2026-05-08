@extends('layouts.admin')

@section('title', 'Products')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Products List</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Products</li>
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
            <ul class="nav nav-tabs d-flex" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="view-tab" data-bs-toggle="tab" data-bs-target="#view"
                        type="button" role="tab" aria-controls="view" aria-selected="true">View
                        Products</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create"
                        type="button" role="tab" aria-controls="create" aria-selected="false">Create
                        Product</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import"
                        type="button" role="tab" aria-controls="import" aria-selected="false">Import
                        Products</button>
                </li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="view" role="tabpanel"
                            aria-labelledby="view-tab">
                            <div class="table-responsive">
                                <table class="display" id="products-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>

                                            <th>Machine Model</th>
                                            <th>Machine</th>
                                            <th>Brand</th>
                                            <th>Category & Sub Category</th>
                                            <th>HSN/SAC</th>
                                            <th>Price / Unit</th>
                                            <th>Tax</th>
                                            <th>Brochure</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                            <form id="createProductForm" class="theme-form" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Machine Details</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductName" class="form-label">Machine
                                                            <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="createProductName" name="name" required>
                                                            <option value="">Select Machine</option>
                                                            @foreach ($dealerships as $dealership)
                                                                @php $dName = ucfirst($dealership->name); @endphp
                                                                <option value="{{ $dName }}" {{ $userDealershipName == $dName ? 'selected' : '' }}>{{ $dName }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductModel" class="form-label">Machine Model</label>
                                                        <div class="input-group">
                                                            <input list="model-list" class="form-control" id="createProductModel" name="model" autocomplete="off">
                                                            <datalist id="model-list">
                                                                @foreach($productModels as $model)
                                                                <option value="{{ $model->name }}">
                                                                    @endforeach
                                                            </datalist>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductPrice" class="form-label">Price
                                                            <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control"
                                                            id="createProductPrice" name="price" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductCategory" class="form-label">Category</label>
                                                        <div class="input-group">
                                                            <input list="category-list" class="form-control" id="createProductCategory" autocomplete="off" name="category">
                                                            <datalist id="category-list">
                                                                @foreach ($categories as $category)
                                                                <option value="{{ $category->name }}">
                                                                    @endforeach
                                                            </datalist>
                                                            <button class="btn btn-primary" type="button" id="addCategoryBtn">+</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductSubCategory" class="form-label">Sub Category</label>
                                                        <div class="input-group">
                                                            <input list="sub-category-list" class="form-control" id="createProductSubCategory" autocomplete="off" name="sub_category">
                                                            <datalist id="sub-category-list">
                                                                @foreach ($subCategories as $subCategory)
                                                                <option value="{{ $subCategory->name }}">
                                                                    @endforeach
                                                            </datalist>
                                                            <button class="btn btn-primary" type="button" id="addSubCategoryBtn">+</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductBrand" class="form-label">Brand Category</label>
                                                        <select class="form-select" id="createProductBrand" name="brand">
                                                            <option value="">None</option>
                                                            <option value="Linde">Linde</option>
                                                            <option value="OM">OM</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductHsnSac" class="form-label">HSN/SAC</label>
                                                        <input type="text" class="form-control"
                                                            id="createProductHsnSac" name="hsn_sac">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductUnitType" class="form-label">Unit Type</label>
                                                        <select class="form-select" id="createProductUnitType" name="unit_type">
                                                            <option value="PCS">PCS</option>
                                                            <option value="KG">KG</option>
                                                            <option value="Liter">Liter</option>
                                                            <option value="Dozen">Dozen</option>
                                                            <option value="Meter">Meter</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductTax" class="form-label">Tax</label>
                                                        <div class="input-group">
                                                            <input list="tax-list" class="form-control" id="createProductTax" name="tax" autocomplete="off">
                                                            <datalist id="tax-list">
                                                                @foreach ($taxes as $tax)
                                                                <option value="{{ $tax->name }}">
                                                                    @endforeach
                                                            </datalist>
                                                            <button class="btn btn-primary" type="button" id="addTaxBtn">+</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createProductBrochure" class="form-label">Brochure (PDF)</label>
                                                        <input type="file" class="form-control" id="createProductBrochure" name="brochure" accept="application/pdf">
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label for="createProductDescription" class="form-label">Description</label>
                                                        <textarea class="form-control" id="createProductDescription" name="description" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">Save Product</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Import Products</h4>
                                </div>
                                <div class="card-body">
                                    <p class="mt-3">Download a sample Excel template: <button type="button"
                                            id="downloadTemplateBtn" class="btn btn-sm btn-outline-primary">Download
                                            Template</button></p>
                                    <form id="importProductForm" enctype="multipart/form-data" class="theme-form">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="importFile" class="form-label">Upload Excel File</label>
                                            <input class="form-control" type="file" id="importFile" name="excel_file" accept=".xlsx, .xls, .csv" required>
                                        </div>
                                        <button type="submit" id="importProductButton" class="btn btn-primary">Import Products</button>
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


<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewProductModalLabel">Machine Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">

                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Machine Model:</strong> <span id="viewProductModel"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Machine:</strong> <span id="viewProductName"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Price:</strong> <span id="viewProductPrice"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>HSN/SAC:</strong> <span id="viewProductHsnSac"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Unit Type:</strong> <span id="viewProductUnitType"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Category:</strong> <span id="viewProductCategory"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Sub Category:</strong> <span id="viewProductSubCategory"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Tax:</strong> <span id="viewProductTax"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Brochure:</strong> <span id="viewProductBrochure"></span>
                    </div>
                    <div class="col-md-12 mb-3">
                        <strong>Description:</strong> <span id="viewProductDescription"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Machine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editProductId" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editProductName" class="form-label">Machine</label>
                            <select class="form-select" id="editProductName" name="name" required>
                                <option value="">Select Machine</option>
                                @foreach ($dealerships as $dealership)
                                    <option value="{{ ucfirst($dealership->name) }}">{{ ucfirst($dealership->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductModel" class="form-label">Machine Model</label>
                            <input list="edit-model-list" class="form-control" id="editProductModel" name="model" autocomplete="off">
                            <datalist id="edit-model-list">
                                @foreach($productModels as $model)
                                <option value="{{ $model->name }}">
                                    @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductPrice" class="form-label">Price</label>
                            <input type="number" class="form-control" id="editProductPrice" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductHsnSac" class="form-label">HSN/SAC</label>
                            <input type="text" class="form-control" id="editProductHsnSac" name="hsn_sac">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductUnitType" class="form-label">Unit Type</label>
                            <select class="form-select" id="editProductUnitType" name="unit_type">
                                <option value="PCS">PCS</option>
                                <option value="KG">KG</option>
                                <option value="Liter">Liter</option>
                                <option value="Dozen">Dozen</option>
                                <option value="Meter">Meter</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductCategory" class="form-label">Category</label>
                            <select class="form-select" id="editProductCategory" name="category_id">
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductSubCategory" class="form-label">Sub Category</label>
                            <select class="form-select" id="editProductSubCategory" name="sub_category_id">
                                @foreach($subCategories as $subCategory)
                                <option value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductBrand" class="form-label">Brand Category</label>
                            <select class="form-select" id="editProductBrand" name="brand">
                                <option value="">None</option>
                                <option value="Linde">Linde</option>
                                <option value="OM">OM</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductTax" class="form-label">Tax</label>
                            <select class="form-select" id="editProductTax" name="tax_id">
                                @foreach($taxes as $tax)
                                <option value="{{ $tax->id }}">{{ $tax->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProductBrochure" class="form-label">Brochure (PDF)</label>
                            <input type="file" class="form-control" id="editProductBrochure" name="brochure" accept="application/pdf">
                            <div id="currentBrochureLink" class="mt-2"></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="editProductDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editProductDescription" name="description"></textarea>
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

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductModalLabel">Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>
                <input type="hidden" id="deleteProductId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteProduct">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    $(document).ready(function() {
        var productsTable = $('#products-table').DataTable({
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
            ajax: "{{ route('products.index') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },

                {
                    data: 'machine_model',
                    name: 'machine_model',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'brand',
                    name: 'brand',
                    defaultContent: 'None'
                },
                {
                    data: null,
                    name: 'category_sub_category',
                    orderable: false,
                    searchable: true,
                    render: function(data, type, row) {
                        var categoryBadge = row.category_name ? '<span class="badge bg-primary mb-2">' + row.category_name + '</span>' : '';
                        var subCategoryBadge = row.sub_category_name ? '<span class="badge bg-info">' + row.sub_category_name + '</span>' : '';
                        return '<div class="d-flex align-items-start">' + categoryBadge + subCategoryBadge + '</div>';
                    }
                },
                {
                    data: 'hsn_sac',
                    name: 'hsn_sac'
                },
                {
                    data: null,
                    name: 'price_unit',
                    orderable: true,
                    searchable: true,
                    render: function(data, type, row) {
                        var priceVal = parseFloat(row.product_price);
                        if (isNaN(priceVal)) priceVal = 0;
                        var price = priceVal.toFixed(2);
                        var unitType = row.unit_type ? ' / ' + row.unit_type : ' / PCS';
                        return price + unitType;
                    }
                },
                {
                    data: 'tax',
                    name: 'tax',
                    defaultContent: ''
                },
                {
                    data: 'brochure',
                    name: 'brochure',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (data) {
                            return '<a href="/' + data + '" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf-o"></i> Download</a>';
                        }
                        return '';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            "drawCallback": function(settings) {
                // feather.replace(); // Removed as we are using custom icons
            }
        });

        $('#createProductForm').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: "{{ route('products.store') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    productsTable.ajax.reload();
                    $('#createProductForm')[0].reset();
                    // Switch to view tab
                    var viewTab = new bootstrap.Tab(document.getElementById('view-tab'));
                    viewTab.show();
                },
                error: function(error) {
                    console.error('Error creating product:', error);
                    showAlert('Error creating product.', 'danger');
                }
            });
        });

        function showAlert(message, type) {
            var toastContainer = $('#toast-container');
            if (toastContainer.length === 0) {
                toastContainer = $('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
                $('body').append(toastContainer);
            }

            var toastHtml = '<div class="toast align-items-center text-white bg-' + type +
                ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                '<div class="d-flex">' +
                '<div class="toast-body">' +
                message +
                '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div>' +
                '</div>';

            var toastElement = $(toastHtml);
            toastContainer.append(toastElement);

            var toast = new bootstrap.Toast(toastElement[0]);
            toast.show();
        }

        $('#addCategoryBtn').on('click', function() {
            var categoryName = $('#createProductCategory').val();
            if (categoryName) {
                $.ajax({
                    url: "{{ route('categories.store') }}",
                    method: 'POST',
                    data: {
                        name: categoryName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#category-list').append('<option value="' + response.name + '">');
                        $('#createProductCategory').val(response.name);
                        showAlert('Category added successfully', 'success');
                    },
                    error: function(error) {
                        showAlert('Error adding category.', 'danger');
                    }
                });
            }
        });

        $('#addSubCategoryBtn').on('click', function() {
            var subCategoryName = $('#createProductSubCategory').val();
            if (subCategoryName) {
                $.ajax({
                    url: "{{ route('sub-categories.store') }}",
                    method: 'POST',
                    data: {
                        name: subCategoryName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#sub-category-list').append('<option value="' + response.name + '">');
                        $('#createProductSubCategory').val(response.name);
                        showAlert('Sub Category added successfully', 'success');
                    },
                    error: function(error) {
                        showAlert('Error adding sub category.', 'danger');
                    }
                });
            }
        });

        $('#addTaxBtn').on('click', function() {
            var taxName = $('#createProductTax').val();
            if (taxName) {
                $.ajax({
                    url: "{{ route('taxes.store') }}",
                    method: 'POST',
                    data: {
                        name: taxName,
                        rate: 0,
                        _token: '{{ csrf_token() }}'
                    }, // Set rate to 0 or a default
                    success: function(response) {
                        $('#tax-list').append('<option value="' + response.name + '">');
                        $('#createProductTax').val(response.name);
                        showAlert('Tax added successfully', 'success');
                    },
                    error: function(error) {
                        showAlert('Error adding tax.', 'danger');
                    }
                });
            }
        });

        // View Product Modal
        $('#products-table').on('click', '.view-product-btn', function() {
            var productId = $(this).data('id');
            var modelId = $(this).data('model-id');
            $.ajax({
                url: '/products/' + productId + '?model_id=' + modelId,
                method: 'GET',
                success: function(data) {
                    $('#viewProductName').text(data.product.name);
                    $('#viewProductPrice').text(data.product.price);
                    $('#viewProductHsnSac').text(data.product.hsn_sac || 'N/A');
                    $('#viewProductDescription').text(data.product.description || 'N/A');
                    $('#viewProductUnitType').text(data.product.unit_type || 'N/A');
                    $('#viewProductCategory').text(data.product.category ? data.product.category.name : 'N/A');
                    $('#viewProductSubCategory').text(data.product.sub_category ? data.product.sub_category.name : 'N/A');
                    $('#viewProductTax').text(data.product.tax ? data.product.tax.name : 'N/A');
                    $('#viewProductModel').text(data.model || 'N/A');


                    if (data.product.brochure) {
                        $('#viewProductBrochure').html('<a href="/' + data.product.brochure + '" target="_blank" class="text-danger"><i class="fa fa-file-pdf-o"></i> Download Brochure</a>');
                    } else {
                        $('#viewProductBrochure').text('N/A');
                    }

                    var viewProductModal = new bootstrap.Modal(document.getElementById('viewProductModal'));
                    viewProductModal.show();
                },
                error: function(error) {
                    console.error('Error fetching product data:', error);
                    showAlert('Error fetching product data.', 'danger');
                }
            });
        });

        // Edit Product Modal
        $('#products-table').on('click', '.edit-product-btn', function() {
            var productId = $(this).data('id');
            var modelId = $(this).data('model-id');
            $.ajax({
                url: '/products/' + productId + '?model_id=' + modelId,
                method: 'GET',
                success: function(data) {
                    $('#editProductId').val(data.product.id);
                    $('#editProductName').val(data.product.name);
                    $('#editProductPrice').val(data.product.price);
                    $('#editProductHsnSac').val(data.product.hsn_sac);
                    $('#editProductDescription').val(data.product.description);
                    $('#editProductUnitType').val(data.product.unit_type);
                    $('#editProductCategory').val(data.product.category_id);
                    $('#editProductSubCategory').val(data.product.sub_category_id);
                    $('#editProductBrand').val(data.product.brand);
                    $('#editProductTax').val(data.product.tax_id);
                    $('#editProductModel').val(data.model);


                    if (data.product.brochure) {
                        $('#currentBrochureLink').html('<small>Current Brochure: <a href="/' + data.product.brochure + '" target="_blank">View PDF</a></small>');
                    } else {
                        $('#currentBrochureLink').html('');
                    }

                    // Populate category, sub-category, and tax dropdowns
                    var categoryOptions = '';
                    $.each(data.categories, function(index, category) {
                        categoryOptions += '<option value="' + category.id + '" ' + (data.product.category_id == category.id ? 'selected' : '') + '>' + category.name + '</option>';
                    });
                    $('#editProductCategory').html(categoryOptions);

                    var subCategoryOptions = '';
                    $.each(data.subCategories, function(index, subCategory) {
                        subCategoryOptions += '<option value="' + subCategory.id + '" ' + (data.product.sub_category_id == subCategory.id ? 'selected' : '') + '>' + subCategory.name + '</option>';
                    });
                    $('#editProductSubCategory').html(subCategoryOptions);

                    var taxOptions = '';
                    $.each(data.taxes, function(index, tax) {
                        taxOptions += '<option value="' + tax.id + '" ' + (data.product.tax_id == tax.id ? 'selected' : '') + '>' + tax.name + '</option>';
                    });
                    $('#editProductTax').html(taxOptions);

                    var editProductModal = new bootstrap.Modal(document.getElementById('editProductModal'));
                    editProductModal.show();
                },
                error: function(error) {
                    console.error('Error fetching product data for edit:', error);
                    showAlert('Error fetching product data for edit.', 'danger');
                }
            });
        });

        // Handle Edit Product Form Submission
        $('#editProductForm').on('submit', function(event) {
            event.preventDefault();
            var productId = $('#editProductId').val();
            var formData = new FormData(this); // Use FormData for file upload

            $.ajax({
                url: '/products/' + productId,
                method: 'POST', // Use POST for PUT/PATCH requests with _method
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    $('#editProductModal').modal('hide');
                    productsTable.ajax.reload();
                },
                error: function(error) {
                    console.error('Error updating product:', error);
                    showAlert('Error updating product.', 'danger');
                }
            });
        });

        // Delete Product Modal
        $('#products-table').on('click', '.delete-product-btn', function() {
            var productId = $(this).data('id');
            var productName = $(this).data('product-name');
            $('#deleteProductId').val(productId);
            $('#deleteProductName').text(productName);
            var deleteProductModal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
            deleteProductModal.show();
        });

        // Handle Delete Product Confirmation
        $('#confirmDeleteProduct').on('click', function() {
            var productId = $('#deleteProductId').val();
            $.ajax({
                url: '/products/' + productId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    $('#deleteProductModal').modal('hide');
                    productsTable.ajax.reload();
                },
                error: function(error) {
                    console.error('Error deleting product:', error);
                    showAlert('Error deleting product.', 'danger');
                }
            });
        });

        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route('products.import.template') }}";
        });

        $('#importProductForm').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            var button = $('#importProductButton');
            var spinner = $('#import-spinner');
            var progressBar = $('#import-progress-bar');
            var progressContainer = $('.progress');
            var status = $('#import-status');
            var errors = $('#import-errors');
            var resultsContainer = $('#import-results');
            var closeResultsButton = $('#closeImportResults');

            button.hide();
            spinner.show();
            status.html('');
            errors.html('');
            resultsContainer.html('').hide();
            closeResultsButton.hide();
            progressContainer.show();
            progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');

            $.ajax({
                url: "{{ route('products.import') }}",
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

                    var importId = response.import_id;
                    if (importId) {
                        startPollingProgress(importId);
                    } else {
                        showAlert(response.message, 'success');
                        progressContainer.hide();
                        loadRecentImports();
                        productsTable.ajax.reload();
                    }
                },
                error: function(response) {
                    spinner.hide();
                    button.show();
                    progressContainer.hide();
                    var message = 'An error occurred during import.';
                    if (response.responseJSON && response.responseJSON.message) {
                        message = response.responseJSON.message;
                    }
                    showAlert(message, 'danger');
                }
            });
        });

        var progressInterval;

        function startPollingProgress(importId) {
            var progressBar = $('#import-progress-bar');
            var progressContainer = $('.progress');
            var status = $('#import-status');
            var errors = $('#import-errors');
            var resultsContainer = $('#import-results');
            var closeResultsButton = $('#closeImportResults');

            progressInterval = setInterval(function() {
                $.ajax({
                    url: '/products/import/progress/' + importId,
                    method: 'GET',
                    success: function(data) {
                        progressBar.css('width', data.percentage + '%').attr('aria-valuenow', data.percentage).text(data.percentage + '%');
                        status.html('<div class="alert alert-info">Status: ' + data.status + ' (' + data.processed_rows + '/' + data.total_rows + ')</div>');

                        if (data.status === 'completed') {
                            clearInterval(progressInterval);
                            progressContainer.hide();
                            showAlert('Import completed successfully.', 'success');
                            loadRecentImports();
                            productsTable.ajax.reload();
                            status.html('');

                            var resultsHtml = '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
                            resultsHtml += '<table class="table table-bordered table-striped table-sm">';
                            resultsHtml += '<thead class="table-light"><tr><th>Row</th><th>Product Name</th><th>Price</th><th>HSN/SAC</th><th>Status</th><th>Details / Warnings</th></tr></thead>';
                            resultsHtml += '<tbody>';

                            $.each(data.results, function(index, result) {
                                var rowClass = '';
                                var statusBadge = '';

                                if (result.status === 'success') {
                                    rowClass = '';
                                    statusBadge = '<span class="badge bg-success">Success</span>';
                                } else if (result.status === 'success_with_warnings') {
                                    rowClass = '';
                                    statusBadge = '<span class="badge bg-success">Succeeded with Warnings</span>';
                                } else if (result.status === 'skipped') {
                                    rowClass = 'table-secondary';
                                    statusBadge = '<span class="badge bg-secondary">Skipped</span>';
                                } else {
                                    rowClass = 'table-danger';
                                    statusBadge = '<span class="badge bg-danger">Failed</span>';
                                }

                                var warningsHtml = '';
                                if (result.warnings && result.warnings.length > 0) {
                                    warningsHtml += '<div class="d-flex flex-column text-start">';
                                    $.each(result.warnings, function(i, warning) {
                                        warningsHtml += '<div class="text-danger small mb-1" style="font-size: 0.85rem;"><i class="fa fa-exclamation-circle me-2"></i>' + warning + '</div>';
                                    });
                                    warningsHtml += '</div>';
                                } else {
                                    warningsHtml = '<span class="text-muted small">' + result.reason + '</span>';
                                }

                                resultsHtml += '<tr class="' + rowClass + '">';
                                resultsHtml += '<td>' + result.row_number + '</td>';
                                resultsHtml += '<td>' + (result.product_name || 'N/A') + '</td>';
                                resultsHtml += '<td>' + (result.price || '0.00') + '</td>';
                                resultsHtml += '<td>' + (result.hsn_sac || 'N/A') + '</td>';
                                resultsHtml += '<td>' + statusBadge + '</td>';
                                resultsHtml += '<td>' + warningsHtml + '</td>';
                                resultsHtml += '</tr>';
                            });
                            resultsHtml += '</tbody></table></div>';
                            resultsContainer.html(resultsHtml).show();
                            closeResultsButton.show();

                        } else if (data.status === 'failed') {
                            clearInterval(progressInterval);
                            progressContainer.hide();
                            errors.html('<div class="alert alert-danger">Import failed.</div>');
                            showAlert('Import failed.', 'danger');
                        }
                    },
                    error: function() {
                        clearInterval(progressInterval);
                        progressContainer.hide();
                        showAlert('Error polling progress.', 'danger');
                    }
                });
            }, 2000);
        }

        $('#closeImportResults').on('click', function() {
            $('#import-results').hide().html('');
            $('#import-status').html('');
            $(this).hide();
        });

        var recentImportsDataTable;

        function loadRecentImports() {
            if (recentImportsDataTable) {
                recentImportsDataTable.ajax.reload();
            } else {
                recentImportsDataTable = $('#recentImportsTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: "{{ route('products.import.recent') }}",
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
                                var summary = '<strong>' + data.products_count + ' Products</strong><br>';
                                if (data.products && data.products.length > 0) {
                                    var names = data.products.map(function(p) { return p.name; });
                                    summary += '<small class="text-muted">' + names.join(', ');
                                    if (data.products_count > 5) {
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
                                return '<button class="btn btn-sm btn-danger undo-import" data-id="' + data + '">Undo</button>';
                            }
                        }
                    ],
                    order: [
                        [1, 'desc']
                    ],
                    searching: false,
                    lengthChange: false,
                    info: false
                });
            }
        }

        loadRecentImports();

        $(document).on('click', '.undo-import', function() {
            if (!confirm('Are you sure you want to undo this import? This will delete all products imported in this batch.')) {
                return;
            }
            var importId = $(this).data('id');
            $.ajax({
                url: '/products/import/' + importId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    loadRecentImports();
                    productsTable.ajax.reload();
                },
                error: function(error) {
                    showAlert('Error undoing import.', 'danger');
                }
            });
        });
    });
</script>
@endpush