@extends('layouts.admin')

@section('title', 'About Products')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>About Products</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">About Products</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
@if(checkMenu(Session::get('role_id'), 7, 'read'))
<div class="container-fluid">
    @else
    <div class="container-fluid">
        <div class="alert alert-danger" role="alert">
            You do not have permission to view About Products.
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs d-flex" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="category-tab" data-bs-toggle="tab" data-bs-target="#category"
                        type="button" role="tab" aria-controls="category" aria-selected="true">Categories</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sub-category-tab" data-bs-toggle="tab" data-bs-target="#sub-category"
                        type="button" role="tab" aria-controls="sub-category" aria-selected="false">Sub Categories</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax"
                        type="button" role="tab" aria-controls="tax" aria-selected="false">Taxes</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="product-model-tab" data-bs-toggle="tab" data-bs-target="#product-model"
                        type="button" role="tab" aria-controls="product-model" aria-selected="false">Product Models</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="model-series-tab" data-bs-toggle="tab" data-bs-target="#model-series"
                        type="button" role="tab" aria-controls="model-series" aria-selected="false">Model Series</button>
                </li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="category" role="tabpanel"
                            aria-labelledby="category-tab">
                            @if(checkMenu(Session::get('role_id'), 7, 'create'))
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createCategoryModal">Add New Category</button>
                            @else
                            <button class="btn btn-primary mb-3" disabled>Add New Category</button>
                            @endif
                            <div class="table-responsive">
                                <table class="display" id="categories-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sub-category" role="tabpanel" aria-labelledby="sub-category-tab">
                            @if(checkMenu(Session::get('role_id'), 7, 'create'))
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createSubCategoryModal">Add New Sub Category</button>
                            @else
                            <button class="btn btn-primary mb-3" disabled>Add New Sub Category</button>
                            @endif
                            <div class="table-responsive">
                                <table class="display" id="sub-categories-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Category</th>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tax" role="tabpanel" aria-labelledby="tax-tab">
                            @if(checkMenu(Session::get('role_id'), 7, 'create'))
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createTaxModal">Add New Tax</button>
                            @else
                            <button class="btn btn-primary mb-3" disabled>Add New Tax</button>
                            @endif
                            <div class="table-responsive">
                                <table class="display" id="taxes-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Name</th>
                                            <th>Rate</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="product-model" role="tabpanel" aria-labelledby="product-model-tab">
                            @if(checkMenu(Session::get('role_id'), 7, 'create'))
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createProductModelModal">Add New Product Model</button>
                            @else
                            <button class="btn btn-primary mb-3" disabled>Add New Product Model</button>
                            @endif
                            <div class="table-responsive">
                                <table class="display" id="product-models-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Product</th>
                                            <th>Model Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="model-series" role="tabpanel" aria-labelledby="model-series-tab">
                            @if(checkMenu(Session::get('role_id'), 7, 'create'))
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModelSeriesModal">Add New Model Series</button>
                            @else
                            <button class="btn btn-primary mb-3" disabled>Add New Model Series</button>
                            @endif
                            <div class="table-responsive">
                                <table class="display" id="model-series-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Product</th>
                                            <th>Product Model</th>
                                            <th>Series Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
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

{{-- Modals --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCategoryModalLabel">Create New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCategoryForm">
                @csrf
                @if(checkMenu(Session::get('role_id'), 7, 'create'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to create categories.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createSubCategoryModal" tabindex="-1" aria-labelledby="createSubCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSubCategoryModalLabel">Create New Sub Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createSubCategoryForm">
                @csrf
                @if(checkMenu(Session::get('role_id'), 7, 'create'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subCategoryName" class="form-label">Sub Category Name</label>
                        <input type="text" class="form-control" id="subCategoryName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="parentCategory" class="form-label">Parent Category</label>
                        <select class="form-select" id="parentCategory" name="category_id" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Sub Category</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to create sub categories.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createTaxModal" tabindex="-1" aria-labelledby="createTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTaxModalLabel">Create New Tax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTaxForm">
                @csrf
                @if(checkMenu(Session::get('role_id'), 7, 'create'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="taxName" class="form-label">Tax Name</label>
                        <input type="text" class="form-control" id="taxName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="taxRate" class="form-label">Tax Rate (%)</label>
                        <input type="number" step="0.01" class="form-control" id="taxRate" name="rate" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Tax</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to create taxes.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createProductModelModal" tabindex="-1" aria-labelledby="createProductModelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createProductModelModalLabel">Create New Product Model</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createProductModelForm">
                @csrf
                @if(checkMenu(Session::get('role_id'), 7, 'create'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="productModelName" class="form-label">Model Name</label>
                        <input type="text" class="form-control" id="productModelName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="productModelProduct" class="form-label">Product</label>
                        <select class="form-select" id="productModelProduct" name="product_id" required>
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Product Model</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to create product models.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createModelSeriesModal" tabindex="-1" aria-labelledby="createModelSeriesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModelSeriesModalLabel">Create New Model Series</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createModelSeriesForm">
                @csrf
                @if(checkMenu(Session::get('role_id'), 7, 'create'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modelSeriesName" class="form-label">Series Name</label>
                        <input type="text" class="form-control" id="modelSeriesName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="modelSeriesProduct" class="form-label">Product</label>
                        <select class="form-select" id="modelSeriesProduct" name="product_id" required>
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modelSeriesProductModel" class="form-label">Product Model</label>
                        <select class="form-select" id="modelSeriesProductModel" name="product_model_id" required>
                            <option value="">Select Product Model</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Model Series</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to create model series.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editCategoryId" name="id">
                @if(checkMenu(Session::get('role_id'), 7, 'update'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to update categories.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteCategoryName"></strong>?</p>
                <input type="hidden" id="deleteCategoryId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                @if(checkMenu(Session::get('role_id'), 7, 'delete'))
                <button type="button" class="btn btn-danger" id="confirmDeleteCategory">Delete</button>
                @else
                <button type="button" class="btn btn-danger" disabled>Delete</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- View Category Modal -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCategoryModalLabel">Category Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="viewCategoryName"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Sub Category Modal -->
<div class="modal fade" id="editSubCategoryModal" tabindex="-1" aria-labelledby="editSubCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubCategoryModalLabel">Edit Sub Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSubCategoryForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editSubCategoryId" name="id">
                @if(checkMenu(Session::get('role_id'), 7, 'update'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editSubCategoryName" class="form-label">Sub Category Name</label>
                        <input type="text" class="form-control" id="editSubCategoryName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editParentCategory" class="form-label">Parent Category</label>
                        <select class="form-select" id="editParentCategory" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to update sub categories.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Delete Sub Category Modal -->
<div class="modal fade" id="deleteSubCategoryModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteSubCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSubCategoryModalLabel">Delete Sub Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteSubCategoryName"></strong>?</p>
                <input type="hidden" id="deleteSubCategoryId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                @if(checkMenu(Session::get('role_id'), 7, 'delete'))
                <button type="button" class="btn btn-danger" id="confirmDeleteSubCategory">Delete</button>
                @else
                <button type="button" class="btn btn-danger" disabled>Delete</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- View Sub Category Modal -->
<div class="modal fade" id="viewSubCategoryModal" tabindex="-1" aria-labelledby="viewSubCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubCategoryModalLabel">Sub Category Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="viewSubCategoryName"></span></p>
                <p><strong>Parent Category:</strong> <span id="viewSubCategoryParentCategory"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Tax Modal -->
<div class="modal fade" id="editTaxModal" tabindex="-1" aria-labelledby="editTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTaxModalLabel">Edit Tax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTaxForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editTaxId" name="id">
                @if(checkMenu(Session::get('role_id'), 7, 'update'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTaxName" class="form-label">Tax Name</label>
                        <input type="text" class="form-control" id="editTaxName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTaxRate" class="form-label">Tax Rate (%)</label>
                        <input type="number" step="0.01" class="form-control" id="editTaxRate" name="rate" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to update taxes.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Delete Tax Modal -->
<div class="modal fade" id="deleteTaxModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaxModalLabel">Delete Tax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteTaxName"></strong>?</p>
                <input type="hidden" id="deleteTaxId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                @if(checkMenu(Session::get('role_id'), 7, 'delete'))
                <button type="button" class="btn btn-danger" id="confirmDeleteTax">Delete</button>
                @else
                <button type="button" class="btn btn-danger" disabled>Delete</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- View Tax Modal -->
<div class="modal fade" id="viewTaxModal" tabindex="-1" aria-labelledby="viewTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTaxModalLabel">Tax Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="viewTaxName"></span></p>
                <p><strong>Rate:</strong> <span id="viewTaxRate"></span>%</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Model Modal -->
<div class="modal fade" id="editProductModelModal" tabindex="-1" aria-labelledby="editProductModelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModelModalLabel">Edit Product Model</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductModelForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editProductModelId" name="id">
                @if(checkMenu(Session::get('role_id'), 7, 'update'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editProductModelName" class="form-label">Model Name</label>
                        <input type="text" class="form-control" id="editProductModelName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editProductModelProduct" class="form-label">Product</label>
                        <select class="form-select" id="editProductModelProduct" name="product_id" required>
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to update product models.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Delete Product Model Modal -->
<div class="modal fade" id="deleteProductModelModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteProductModelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductModelModalLabel">Delete Product Model</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteProductModelName"></strong>?</p>
                <input type="hidden" id="deleteProductModelId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                @if(checkMenu(Session::get('role_id'), 7, 'delete'))
                <button type="button" class="btn btn-danger" id="confirmDeleteProductModel">Delete</button>
                @else
                <button type="button" class="btn btn-danger" disabled>Delete</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- View Product Model Modal -->
<div class="modal fade" id="viewProductModelModal" tabindex="-1" aria-labelledby="viewProductModelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewProductModelModalLabel">Product Model Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Product:</strong> <span id="viewProductModelProduct"></span></p>
                <p><strong>Model Name:</strong> <span id="viewProductModelName"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Model Series Modal -->
<div class="modal fade" id="editModelSeriesModal" tabindex="-1" aria-labelledby="editModelSeriesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModelSeriesModalLabel">Edit Model Series</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editModelSeriesForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editModelSeriesId" name="id">
                @if(checkMenu(Session::get('role_id'), 7, 'update'))
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editModelSeriesName" class="form-label">Series Name</label>
                        <input type="text" class="form-control" id="editModelSeriesName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editModelSeriesProduct" class="form-label">Product</label>
                        <select class="form-select" id="editModelSeriesProduct" name="product_id" required>
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editModelSeriesProductModel" class="form-label">Product Model</label>
                        <select class="form-select" id="editModelSeriesProductModel" name="product_model_id" required>
                            <option value="">Select Product Model</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to update model series.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Delete Model Series Modal -->
<div class="modal fade" id="deleteModelSeriesModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteModelSeriesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModelSeriesModalLabel">Delete Model Series</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteModelSeriesName"></strong>?</p>
                <input type="hidden" id="deleteModelSeriesId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                @if(checkMenu(Session::get('role_id'), 7, 'delete'))
                <button type="button" class="btn btn-danger" id="confirmDeleteModelSeries">Delete</button>
                @else
                <button type="button" class="btn btn-danger" disabled>Delete</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- View Model Series Modal -->
<div class="modal fade" id="viewModelSeriesModal" tabindex="-1" aria-labelledby="viewModelSeriesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModelSeriesModalLabel">Model Series Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Product:</strong> <span id="viewModelSeriesProduct"></span></p>
                <p><strong>Product Model:</strong> <span id="viewModelSeriesProductModel"></span></p>
                <p><strong>Series Name:</strong> <span id="viewModelSeriesName"></span></p>
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
        // Categories DataTable
        var categoriesTable = $('#categories-table').DataTable({
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
            ajax: "{{ route('categories.index') }}", // Assuming a route for categories index
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
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Sub Categories DataTable
        var subCategoriesTable = $('#sub-categories-table').DataTable({
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
            ajax: "{{ route('sub-categories.index') }}", // Assuming a route for sub categories index
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'category.name',
                    name: 'category.name',
                    render: function(data, type, row) {
                        return row.category ? row.category.name : 'N/A'; // Display 'N/A' if category is null
                    }
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Taxes DataTable
        var taxesTable = $('#taxes-table').DataTable({
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
            ajax: "{{ route('taxes.index') }}", // Assuming a route for taxes index
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
                    data: 'rate',
                    name: 'rate'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Function to display Bootstrap toasts
        function showAlert(message, type) {
            var toastContainer = $('#toast-container');
            if (toastContainer.length === 0) {
                toastContainer = $(
                    '<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>'
                );
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

        // Handle Category Form Submission
        $('#createCategoryForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('categories.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    categoriesTable.ajax.reload();
                    $('#createCategoryModal').modal('hide');
                    $('#createCategoryForm')[0].reset();
                },
                error: function(error) {
                    showAlert('Error creating category.', 'danger');
                }
            });
        });

        $('#createSubCategoryModal').on('show.bs.modal', function() {
            $.ajax({
                url: "{{ route('categories.get') }}",
                method: 'GET',
                success: function(data) {
                    var parentCategorySelect = $('#parentCategory');
                    parentCategorySelect.empty();
                    parentCategorySelect.append('<option value="">Select Category</option>');
                    $.each(data, function(index, category) {
                        parentCategorySelect.append('<option value="' + category.id + '">' + category.name + '</option>');
                    });
                },
                error: function(error) {
                    console.error('Error fetching categories:', error);
                }
            });
        });

        // Handle Sub Category Form Submission
        $('#createSubCategoryForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('sub-categories.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    subCategoriesTable.ajax.reload();
                    $('#createSubCategoryModal').modal('hide');
                    $('#createSubCategoryForm')[0].reset();
                },
                error: function(error) {
                    showAlert('Error creating sub category.', 'danger');
                }
            });
        });

        // Handle Tax Form Submission
        $('#createTaxForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('taxes.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    taxesTable.ajax.reload();
                    $('#createTaxModal').modal('hide');
                    $('#createTaxForm')[0].reset();
                },
                error: function(error) {
                    showAlert('Error creating tax.', 'danger');
                }
            });
        });

        // Edit Category Modal
        $('#categories-table').on('click', '.edit-category-btn', function() {
            var categoryId = $(this).data('id');
            $.ajax({
                url: '/categories/' + categoryId, // Changed from /categories/{id}/edit to /categories/{id}
                method: 'GET',
                success: function(data) {
                    $('#editCategoryId').val(data.id);
                    $('#editCategoryName').val(data.name);
                    $('#editCategoryModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching category data for edit:', error);
                    showAlert('Error fetching category data for edit.', 'danger');
                }
            });
        });

        // View Category Modal
        $('#categories-table').on('click', '.view-category-btn', function() {
            var categoryId = $(this).data('id');
            $.ajax({
                url: '/categories/' + categoryId,
                method: 'GET',
                success: function(data) {
                    $('#viewCategoryName').text(data.name);
                    $('#viewCategoryModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching category data for view:', error);
                    showAlert('Error fetching category data for view.', 'danger');
                }
            });
        });

        // Handle Edit Category Form Submission
        $('#editCategoryForm').on('submit', function(e) {
            e.preventDefault();
            var categoryId = $('#editCategoryId').val();
            $.ajax({
                url: '/categories/' + categoryId,
                method: 'POST', // Use POST for PUT/PATCH with _method
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    categoriesTable.ajax.reload();
                    $('#editCategoryModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating category:', error);
                    showAlert('Error updating category.', 'danger');
                }
            });
        });

        // Delete Category Modal
        $('#categories-table').on('click', '.delete-category-btn', function() {
            var categoryId = $(this).data('id');
            var categoryName = $(this).data('category-name');
            $('#deleteCategoryId').val(categoryId);
            $('#deleteCategoryName').text(categoryName);
            var deleteCategoryModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
            deleteCategoryModal.show();
        });

        // Handle Delete Category Confirmation
        $('#confirmDeleteCategory').on('click', function() {
            var categoryId = $('#deleteCategoryId').val();
            $.ajax({
                url: '/categories/' + categoryId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    categoriesTable.ajax.reload();
                    $('#deleteCategoryModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting category:', error);
                    showAlert('Error deleting category.', 'danger');
                }
            });
        });

        // Edit Sub Category Modal
        $('#sub-categories-table').on('click', '.edit-sub-category-btn', function() {
            var subCategoryId = $(this).data('id');
            $.ajax({
                url: '/sub-categories/' + subCategoryId, // Changed from /sub-categories/{id}/edit to /sub-categories/{id}
                method: 'GET',
                success: function(data) {
                    $('#editSubCategoryId').val(data.id);
                    $('#editSubCategoryName').val(data.name);
                    $('#editParentCategory').val(data.category_id);
                    $('#editSubCategoryModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching sub category data for edit:', error);
                    showAlert('Error fetching sub category data for edit.', 'danger');
                }
            });
        });

        // View Sub Category Modal
        $('#sub-categories-table').on('click', '.view-sub-category-btn', function() {
            var subCategoryId = $(this).data('id');
            $.ajax({
                url: '/sub-categories/' + subCategoryId,
                method: 'GET',
                success: function(data) {
                    $('#viewSubCategoryName').text(data.name);
                    $('#viewSubCategoryParentCategory').text(data.category ? data.category.name : 'N/A');
                    $('#viewSubCategoryModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching sub category data for view:', error);
                    showAlert('Error fetching sub category data for view.', 'danger');
                }
            });
        });

        // Handle Edit Sub Category Form Submission
        $('#editSubCategoryForm').on('submit', function(e) {
            e.preventDefault();
            var subCategoryId = $('#editSubCategoryId').val();
            $.ajax({
                url: '/sub-categories/' + subCategoryId,
                method: 'POST', // Use POST for PUT/PATCH with _method
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    subCategoriesTable.ajax.reload();
                    $('#editSubCategoryModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating sub category:', error);
                    showAlert('Error updating sub category.', 'danger');
                }
            });
        });

        // Delete Sub Category Modal
        $('#sub-categories-table').on('click', '.delete-sub-category-btn', function() {
            var subCategoryId = $(this).data('id');
            var subCategoryName = $(this).data('sub-category-name');
            $('#deleteSubCategoryId').val(subCategoryId);
            $('#deleteSubCategoryName').text(subCategoryName);
            var deleteSubCategoryModal = new bootstrap.Modal(document.getElementById('deleteSubCategoryModal'));
            deleteSubCategoryModal.show();
        });

        // Handle Delete Sub Category Confirmation
        $('#confirmDeleteSubCategory').on('click', function() {
            var subCategoryId = $('#deleteSubCategoryId').val();
            $.ajax({
                url: '/sub-categories/' + subCategoryId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    subCategoriesTable.ajax.reload();
                    $('#deleteSubCategoryModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting sub category:', error);
                    showAlert('Error deleting sub category.', 'danger');
                }
            });
        });

        // Edit Tax Modal
        $('#taxes-table').on('click', '.edit-tax-btn', function() {
            var taxId = $(this).data('id');
            $.ajax({
                url: '/taxes/' + taxId, // Changed from /taxes/{id}/edit to /taxes/{id}
                method: 'GET',
                success: function(data) {
                    $('#editTaxId').val(data.id);
                    $('#editTaxName').val(data.name);
                    $('#editTaxRate').val(data.rate);
                    $('#editTaxModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching tax data for edit:', error);
                    showAlert('Error fetching tax data for edit.', 'danger');
                }
            });
        });

        // View Tax Modal
        $('#taxes-table').on('click', '.view-tax-btn', function() {
            var taxId = $(this).data('id');
            $.ajax({
                url: '/taxes/' + taxId,
                method: 'GET',
                success: function(data) {
                    $('#viewTaxName').text(data.name);
                    $('#viewTaxRate').text(data.rate);
                    $('#viewTaxModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching tax data for view:', error);
                    showAlert('Error fetching tax data for view.', 'danger');
                }
            });
        });

        // Handle Edit Tax Form Submission
        $('#editTaxForm').on('submit', function(e) {
            e.preventDefault();
            var taxId = $('#editTaxId').val();
            $.ajax({
                url: '/taxes/' + taxId,
                method: 'POST', // Use POST for PUT/PATCH with _method
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    taxesTable.ajax.reload();
                    $('#editTaxModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating tax:', error);
                    showAlert('Error updating tax.', 'danger');
                }
            });
        });

        // Delete Tax Modal
        $('#taxes-table').on('click', '.delete-tax-btn', function() {
            var taxId = $(this).data('id');
            var taxName = $(this).data('tax-name');
            $('#deleteTaxId').val(taxId);
            $('#deleteTaxName').text(taxName);
            var deleteTaxModal = new bootstrap.Modal(document.getElementById('deleteTaxModal'));
            deleteTaxModal.show();
        });

        // Handle Delete Tax Confirmation
        $('#confirmDeleteTax').on('click', function() {
            var taxId = $('#deleteTaxId').val();
            $.ajax({
                url: '/taxes/' + taxId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    taxesTable.ajax.reload();
                    $('#deleteTaxModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting tax:', error);
                    showAlert('Error deleting tax.', 'danger');
                }
            });
        });

        // Product Models DataTable
        var productModelsTable = $('#product-models-table').DataTable({
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
            ajax: "{{ route('about-products.product-models.data') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'product.name',
                    name: 'product.name'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Handle Product Model Form Submission
        $('#createProductModelForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('about-products.product-models.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    productModelsTable.ajax.reload();
                    $('#createProductModelModal').modal('hide');
                    $('#createProductModelForm')[0].reset();
                },
                error: function(error) {
                    showAlert('Error creating product model.', 'danger');
                }
            });
        });

        // Edit Product Model Modal
        $('#product-models-table').on('click', '.edit-product-model-btn', function() {
            var productModelId = $(this).data('id');
            $.ajax({
                url: '/about-products/product-models/' + productModelId + '/edit',
                method: 'GET',
                success: function(data) {
                    $('#editProductModelId').val(data.id);
                    $('#editProductModelName').val(data.name);
                    $('#editProductModelProduct').val(data.product_id);
                    $('#editProductModelModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching product model data for edit:', error);
                    showAlert('Error fetching product model data for edit.', 'danger');
                }
            });
        });

        // View Product Model Modal
        $('#product-models-table').on('click', '.view-product-model-btn', function() {
            var productModelId = $(this).data('id');
            $.ajax({
                url: '/about-products/product-models/' + productModelId,
                method: 'GET',
                success: function(data) {
                    $('#viewProductModelName').text(data.name);
                    $('#viewProductModelProduct').text(data.product ? data.product.name : 'N/A');
                    $('#viewProductModelModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching product model data for view:', error);
                    showAlert('Error fetching product model data for view.', 'danger');
                }
            });
        });

        // Handle Edit Product Model Form Submission
        $('#editProductModelForm').on('submit', function(e) {
            e.preventDefault();
            var productModelId = $('#editProductModelId').val();
            $.ajax({
                url: '/about-products/product-models/' + productModelId,
                method: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    productModelsTable.ajax.reload();
                    $('#editProductModelModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating product model:', error);
                    showAlert('Error updating product model.', 'danger');
                }
            });
        });

        // Delete Product Model Modal
        $('#product-models-table').on('click', '.delete-product-model-btn', function() {
            var productModelId = $(this).data('id');
            var productModelName = $(this).data('product-model-name');
            $('#deleteProductModelId').val(productModelId);
            $('#deleteProductModelName').text(productModelName);
            var deleteProductModelModal = new bootstrap.Modal(document.getElementById('deleteProductModelModal'));
            deleteProductModelModal.show();
        });

        // Handle Delete Product Model Confirmation
        $('#confirmDeleteProductModel').on('click', function() {
            var productModelId = $('#deleteProductModelId').val();
            $.ajax({
                url: '/about-products/product-models/' + productModelId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    productModelsTable.ajax.reload();
                    $('#deleteProductModelModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting product model:', error);
                    showAlert('Error deleting product model.', 'danger');
                }
            });
        });

        // Model Series DataTable
        var modelSeriesTable = $('#model-series-table').DataTable({
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
            ajax: "{{ route('about-products.model-series.data') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'product_model.product.name',
                    name: 'productModel.product.name'
                },
                {
                    data: 'product_model.name',
                    name: 'productModel.name'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Handle Model Series Form Submission
        $('#createModelSeriesForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('about-products.model-series.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    modelSeriesTable.ajax.reload();
                    $('#createModelSeriesModal').modal('hide');
                    $('#createModelSeriesForm')[0].reset();
                },
                error: function(error) {
                    showAlert('Error creating model series.', 'danger');
                }
            });
        });

        // Edit Model Series Modal
        $('#model-series-table').on('click', '.edit-model-series-btn', function() {
            var modelSeriesId = $(this).data('id');
            $.ajax({
                url: '/about-products/model-series/' + modelSeriesId + '/edit',
                method: 'GET',
                success: function(data) {
                    $('#editModelSeriesId').val(data.id);
                    $('#editModelSeriesName').val(data.name);
                    $('#editModelSeriesProduct').val(data.product_model.product_id);
                    // Trigger change to load product models
                    $('#editModelSeriesProduct').trigger('change');
                    // Set product model after models are loaded
                    setTimeout(function() {
                        $('#editModelSeriesProductModel').val(data.product_model_id);
                    }, 500); // Small delay to ensure product models are loaded
                    $('#editModelSeriesModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching model series data for edit:', error);
                    showAlert('Error fetching model series data for edit.', 'danger');
                }
            });
        });

        // View Model Series Modal
        $('#model-series-table').on('click', '.view-model-series-btn', function() {
            var modelSeriesId = $(this).data('id');
            $.ajax({
                url: '/about-products/model-series/' + modelSeriesId + '/edit', // Using edit route to get full data
                method: 'GET',
                success: function(data) {
                    $('#viewModelSeriesName').text(data.name);
                    $('#viewModelSeriesProduct').text(data.product_model.product.name || 'N/A');
                    $('#viewModelSeriesProductModel').text(data.product_model.name || 'N/A');
                    $('#viewModelSeriesModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching model series data for view:', error);
                    showAlert('Error fetching model series data for view.', 'danger');
                }
            });
        });

        // Handle Edit Model Series Form Submission
        $('#editModelSeriesForm').on('submit', function(e) {
            e.preventDefault();
            var modelSeriesId = $('#editModelSeriesId').val();
            $.ajax({
                url: '/about-products/model-series/' + modelSeriesId,
                method: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    showAlert(response.message, 'success');
                    modelSeriesTable.ajax.reload();
                    $('#editModelSeriesModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating model series:', error);
                    showAlert('Error updating model series.', 'danger');
                }
            });
        });

        // Delete Model Series Modal
        $('#model-series-table').on('click', '.delete-model-series-btn', function() {
            var modelSeriesId = $(this).data('id');
            var modelSeriesName = $(this).data('model-series-name');
            $('#deleteModelSeriesId').val(modelSeriesId);
            $('#deleteModelSeriesName').text(modelSeriesName);
            var deleteModelSeriesModal = new bootstrap.Modal(document.getElementById('deleteModelSeriesModal'));
            deleteModelSeriesModal.show();
        });

        // Handle Delete Model Series Confirmation
        $('#confirmDeleteModelSeries').on('click', function() {
            var modelSeriesId = $('#deleteModelSeriesId').val();
            $.ajax({
                url: '/about-products/model-series/' + modelSeriesId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                    modelSeriesTable.ajax.reload();
                    $('#deleteModelSeriesModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting model series:', error);
                    showAlert('Error deleting model series.', 'danger');
                }
            });
        });

        // Dynamic loading of Product Models based on Product selection
        function loadProductModels(productId, targetSelectId, selectedProductModelId = null) {
            var targetSelect = $(targetSelectId);
            targetSelect.empty().append('<option value="">Select Product Model</option>');
            if (productId) {
                $.ajax({
                    url: "{{ route('about-products.product-models.by-product') }}",
                    method: 'GET',
                    data: {
                        product_id: productId
                    },
                    success: function(response) {
                        $.each(response, function(index, model) {
                            targetSelect.append('<option value="' + model.id + '">' + model.name + '</option>');
                        });
                        if (selectedProductModelId) {
                            targetSelect.val(selectedProductModelId);
                        }
                    },
                    error: function(error) {
                        console.error('Error loading product models:', error);
                    }
                });
            }
        }

        // Event listener for Product selection in Create Model Series Modal
        $('#createModelSeriesModal').on('change', '#modelSeriesProduct', function() {
            var productId = $(this).val();
            loadProductModels(productId, '#modelSeriesProductModel');
        });

        // Event listener for Product selection in Edit Model Series Modal
        $('#editModelSeriesModal').on('change', '#editModelSeriesProduct', function() {
            var productId = $(this).val();
            loadProductModels(productId, '#editModelSeriesProductModel');
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("data-bs-target");
            switch (target) {
                case '#category':
                    categoriesTable.ajax.reload();
                    break;
                case '#sub-category':
                    subCategoriesTable.ajax.reload();
                    break;
                case '#tax':
                    taxesTable.ajax.reload();
                    break;
                case '#product-model':
                    productModelsTable.ajax.reload();
                    break;
                case '#model-series':
                    modelSeriesTable.ajax.reload();
                    break;
            }
        });
    });
</script>
@endpush