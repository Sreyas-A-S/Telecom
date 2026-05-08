@extends('layouts.admin')

@section('title', 'Service Kits')

@section('breadcrumb')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Service Kits</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Service Kits</li>
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
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="package-kit-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="view-tab" data-bs-toggle="tab" href="#view" role="tab" aria-controls="view" aria-selected="true">View Service Kits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="create-tab" data-bs-toggle="tab" href="#create" role="tab" aria-controls="create" aria-selected="false">Create</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="package-kit-tabs-content">
                        <div class="tab-pane fade show active" id="view" role="tabpanel" aria-labelledby="view-tab">
                            <div class="table-responsive">
                                <table class="display datatables" id="package-kits-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Price</th>
                                            <th>Parts</th>
                                            <th>Features</th>
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
                            <form id="packageKitForm">
                                @csrf
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">Price</label>
                                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="features" class="form-label">Features</label>
                                                <div id="features-container">
                                                    <div class="input-group mb-2">
                                                        <input type="text" class="form-control" name="features[]">
                                                        <button class="btn btn-danger remove-feature-btn" type="button">Remove</button>
                                                    </div>
                                                </div>
                                                <button class="btn btn-success" type="button" id="add-feature-btn">Add Feature</button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="parts" class="form-label">Parts</label>
                                                <div id="parts-container">
                                                    <div class="row mb-2">
                                                        <div class="col-md-6">
                                                            <select class="form-select part-select" name="parts[][part_id]">
                                                                <option value="">Select Part</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="number" class="form-control" name="parts[][quantity]" placeholder="Quantity" min="1">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button class="btn btn-danger remove-part-btn" type="button">Remove</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="btn btn-success" type="button" id="add-part-btn">Add Part</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="is_active">
                                            Is Active
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Package Kit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Package Kit Modal -->
<div class="modal fade" id="viewPackageKitModal" tabindex="-1" aria-labelledby="viewPackageKitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPackageKitModalLabel">View Package Kit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="viewName"></span></p>
                <p><strong>Description:</strong> <span id="viewDescription"></span></p>
                <p><strong>Price:</strong> <span id="viewPrice"></span></p>
                <p><strong>Features:</strong> <span id="viewFeatures"></span></p>
                <p><strong>Status:</strong> <span id="viewStatus"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Package Kit Modal -->
<div class="modal fade" id="deletePackageKitModal" tabindex="-1" role="dialog" aria-labelledby="deletePackageKitModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePackageKitModalLabel">Delete Package Kit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deletePackageKitName"></strong>?</p>
                <input type="hidden" id="deletePackageKitId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePackageKit">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(function () {
        var packageKitsTable = $('#package-kits-table').DataTable({
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'csv', className: 'btn btn-sm btn-primary text-white' },
                    { extend: 'excel', className: 'btn btn-sm btn-success text-white' },
                    { extend: 'pdf', className: 'btn btn-sm btn-danger text-white' },
                    { extend: 'print', className: 'btn btn-sm btn-info text-white' }
                ],
            ajax: '{{ route('service-kits.datatable') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description' },
                { data: 'price', name: 'price' },
                { data: 'parts_list', name: 'parts_list', orderable: false, searchable: false }, // New column
                { data: 'features_list', name: 'features_list', orderable: false, searchable: false },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });

        // Handle Save Package Kit form submission
        $('#packageKitForm').on('submit', function(e) {
            e.preventDefault();

            // Client-side validation
            let isValid = true;
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Validate Name
            if ($('#name').val().trim() === '') {
                $('#name').addClass('is-invalid').after('<div class="invalid-feedback">Name is required.</div>');
                isValid = false;
            }

            // Validate Price
            var price = $('#price').val();
            if (price === '' || isNaN(price) || parseFloat(price) < 0) {
                $('#price').addClass('is-invalid').after('<div class="invalid-feedback">Valid price (>= 0) is required.</div>');
                isValid = false;
            }

            // Validate Parts
            var partsCount = $('#parts-container .row').length;
            if (partsCount > 0) {
                $('#parts-container .row').each(function() {
                    var partId = $(this).find('select[name="parts[][part_id]"]').val();
                    var quantity = $(this).find('input[name="parts[][quantity]"]').val();

                    if (!partId) {
                        $(this).find('select[name="parts[][part_id]"]').addClass('is-invalid').after('<div class="invalid-feedback">Part is required.</div>');
                        isValid = false;
                    }
                    if (quantity === '' || isNaN(quantity) || parseInt(quantity) <= 0) {
                        $(this).find('input[name="parts[][quantity]"]').addClass('is-invalid').after('<div class="invalid-feedback">Quantity (>= 1) is required.</div>');
                        isValid = false;
                    }
                });
            }

            if (!isValid) {
                showToast('Please correct the errors in the form.', 'danger');
                return;
            }

            var url = '{{ route('service-kits.store') }}';
            var method = 'POST';

            var formData = new FormData();
            // Append non-parts fields
            $(this).find('input, textarea, select').not('[name^="parts["]').each(function() {
                if (this.name) {
                    if (this.type === 'checkbox' || this.type === 'radio') {
                        if (this.checked) {
                            formData.append(this.name, this.value);
                        }
                    } else {
                        formData.append(this.name, $(this).val());
                    }
                }
            });

            var partsData = [];
            $('#parts-container .row').each(function() {
                var partId = $(this).find('select[name="parts[][part_id]"]').val();
                var quantity = $(this).find('input[name="parts[][quantity]"]').val();

                if (partId && quantity && parseInt(quantity) > 0) {
                    partsData.push({
                        part_id: partId,
                        quantity: quantity
                    });
                }
            });

            partsData.forEach(function(part, index) {
                formData.append('parts[' + index + '][part_id]', part.part_id);
                formData.append('parts[' + index + '][quantity]', part.quantity);
            });

            // Manually handle checkbox for is_active if not checked (if not already handled by .not('[name^="parts["]') )
            // Ensure 'is_active' is appended if it's not already.
            if (!$('#is_active').is(':checked')) {
                formData.append('is_active', 0);
            } else {
                // If it is checked, ensure it's appended with its value (1)
                // This handles cases where the checkbox might not be included by .not('[name^="parts["]')
                if (!formData.has('is_active')) {
                    formData.append('is_active', 1);
                }
            }

            $.ajax({
                url: url,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    packageKitsTable.ajax.reload();
                    $('#packageKitForm')[0].reset();
                    $('#package-kit-tabs a[href="#view"]').tab('show');
                },
                error: function(error) {
                    console.error('Error saving package kit:', error);
                    showToast('Error saving package kit.', 'danger');
                }
            });
        });

        // Handle Delete button click
        $('#package-kits-table').on('click', '.delete-package-kit-btn', function() {
            var packageKitId = $(this).data('id');
            var packageKitName = $(this).data('name');
            $('#deletePackageKitId').val(packageKitId);
            $('#deletePackageKitName').text(packageKitName);
            $('#deletePackageKitModal').modal('show');
        });

        // Handle Delete Confirmation
        $('#confirmDeletePackageKit').on('click', function() {
            var packageKitId = $('#deletePackageKitId').val();
            $.ajax({
                url: '/package-kits/' + packageKitId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    packageKitsTable.ajax.reload();
                    $('#deletePackageKitModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting package kit:', error);
                    showToast('Error deleting package kit.', 'danger');
                }
            });
        });

        // Handle View button click
        $('#package-kits-table').on('click', '.view-package-kit-btn', function() {
            var packageKitId = $(this).data('id');
            $.ajax({
                url: '/package-kits/' + packageKitId,
                method: 'GET',
                success: function(data) {
                    $('#viewName').text(data.name);
                    $('#viewDescription').text(data.description);
                    $('#viewPrice').text(data.price);
                    $('#viewFeatures').text(data.features ? data.features.join(', ') : 'N/A');
                    $('#viewStatus').text(data.is_active ? 'Active' : 'Inactive');
                    $('#viewPackageKitModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching package kit data:', error);
                    showToast('Error fetching package kit data.', 'danger');
                }
            });
        });

        // Add feature
        $('#add-feature-btn').on('click', function() {
            var featureInput = '<div class="input-group mb-2"><input type="text" class="form-control" name="features[]"><button class="btn btn-danger remove-feature-btn" type="button">Remove</button></div>';
            $('#features-container').append(featureInput);
        });

        // Remove feature
        $('#features-container').on('click', '.remove-feature-btn', function() {
            $(this).closest('.input-group').remove();
        });

        // Add part
        $('#add-part-btn').on('click', function() {
            var partInput = '<div class="row mb-2"><div class="col-md-6"><select class="form-select part-select" name="parts[][part_id]"><option value="">Select Part</option></select></div><div class="col-md-4"><input type="number" class="form-control" name="parts[][quantity]" placeholder="Quantity" min="1"></div><div class="col-md-2"><button class="btn btn-danger remove-part-btn" type="button">Remove</button></div></div>';
            $('#parts-container').append(partInput);
            var newSelect = $('#parts-container').find('.part-select:last');
            loadPartsIntoSelect(newSelect); // Populate only the new select
        });

        // Remove part
        $('#parts-container').on('click', '.remove-part-btn', function() {
            $(this).closest('.row').remove();
        });

        var allParts = []; // Global variable to store all parts

        // Function to load parts into select dropdowns
        function loadPartsIntoSelect(selectElement) {
            if (allParts.length === 0) { // Fetch parts only if not already fetched
                $.ajax({
                    url: '{{ route("parts.list") }}',
                    method: 'GET',
                    async: false, // Make it synchronous to ensure allParts is populated before proceeding
                    success: function(response) {
                        allParts = response.data; // Store fetched parts globally
                    },
                    error: function(error) {
                        console.error('Error loading parts:', error);
                        showToast('Error loading parts.', 'danger');
                    }
                });
            }

            // Populate the given selectElement or all empty ones if selectElement is not provided
            var targetSelects = selectElement ? $(selectElement) : $('#parts-container').find('select[name="parts[][part_id]"]').filter(function() {
                return $(this).children('option').length <= 1; // Only populate if empty or only has default option
            });

            targetSelects.each(function() {
                var currentSelect = $(this);
                currentSelect.empty().append('<option value="">Select Part</option>'); // Clear and add default
                $.each(allParts, function(index, part) {
                    currentSelect.append('<option value="' + part.id + '">' + part.part_number + '</option>');
                });
                currentSelect.select2(); // Initialize Select2
            });
        }

        loadPartsIntoSelect();
        $('.part-select').select2(); // Initialize Select2 for all part selects on page load
    });
</script>
@endpush