@extends('layouts.admin')

@section('title', 'Models')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Models</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Models</li>
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
            <ul class="nav nav-tabs d-flex" id="modelTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="view-tab" data-bs-toggle="tab" data-bs-target="#view"
                        type="button" role="tab" aria-controls="view" aria-selected="true">View Models</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create"
                        type="button" role="tab" aria-controls="create" aria-selected="false">Create Model</button>
                </li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="modelTabContent">
                        <div class="tab-pane fade show active" id="view" role="tabpanel" aria-labelledby="view-tab">
                            @if(!checkMenu(Session::get('role_id'), 9, 'read'))
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to view models.
                            </div>
                            @else
                            <div class="table-responsive">
                                <table class="display" id="models-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Name</th>
                                            <th>Dealership</th>
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
                                You do not have permission to create a new model.
                            </div>
                            @else
                            <form id="createModelForm" class="theme-form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="createName" class="form-label">Model Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="createName" name="name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="createDealershipId" class="form-label">Dealership <span class="text-danger">*</span></label>
                                        <select class="form-select" id="createDealershipId" name="dealership_id" required>
                                            <option value="">Select Dealership</option>
                                            @foreach ($dealerships as $dealership)
                                            <option value="{{ $dealership->id }}">{{ $dealership->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">Save Model</button>
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

<!-- Edit Model Modal -->
<div class="modal fade" id="editModelModal" tabindex="-1" aria-labelledby="editModelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModelModalLabel">Edit Model</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editModelForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editModelId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editName" class="form-label">Model Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDealershipId" class="form-label">Dealership <span class="text-danger">*</span></label>
                            <select class="form-select" id="editDealershipId" name="dealership_id" required>
                                <option value="">Select Dealership</option>
                                @foreach ($dealerships as $dealership)
                                <option value="{{ $dealership->id }}">{{ $dealership->name }}</option>
                                @endforeach
                            </select>
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

<!-- Delete Model Modal -->
<div class="modal fade" id="deleteModelModal" tabindex="-1" role="dialog" aria-labelledby="deleteModelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModelModalLabel">Delete Model</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this model?</p>
                <input type="hidden" id="deleteModelId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteModel">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var modelsTable = $('#models-table').DataTable({
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
            ajax: "{{ route('models.datatable') }}",
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
                    data: 'dealership.name',
                    name: 'dealership.name'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Handle Create Form Submission
        $('#createModelForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "{{ route('models.store') }}",
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    modelsTable.ajax.reload();
                    $('#createModelForm')[0].reset();
                    // Optionally switch to view tab
                    var viewTab = new bootstrap.Tab(document.getElementById('view-tab'));
                    viewTab.show();
                },
                error: function(error) {
                    console.error('Error creating model:', error);
                    showToast('Error creating model.', 'danger');
                }
            });
        });

        // Handle Edit Button Click
        $('#models-table').on('click', '.edit', function() {
            var id = $(this).data('id');
            $.ajax({
                url: '/models/' + id + '/edit',
                method: 'GET',
                success: function(data) {
                    $('#editModelId').val(data.id);
                    $('#editName').val(data.name);
                    $('#editDealershipId').val(data.dealership_id);
                    $('#editModelModal').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching model data:', error);
                    showToast('Error fetching model data.', 'danger');
                }
            });
        });

        // Handle Edit Form Submission
        $('#editModelForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#editModelId').val();
            var formData = $(this).serialize();

            $.ajax({
                url: '/models/' + id,
                method: 'POST', // Use POST for PUT/PATCH with _method
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    modelsTable.ajax.reload();
                    $('#editModelModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error updating model:', error);
                    showToast('Error updating model.', 'danger');
                }
            });
        });

        // Handle Delete Button Click
        $('#models-table').on('click', '.delete', function() {
            var id = $(this).data('id');
            $('#deleteModelId').val(id);
            $('#deleteModelModal').modal('show');
        });

        // Handle Delete Confirmation
        $('#confirmDeleteModel').on('click', function() {
            var id = $('#deleteModelId').val();
            $.ajax({
                url: '/models/' + id,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    modelsTable.ajax.reload();
                    $('#deleteModelModal').modal('hide');
                },
                error: function(error) {
                    console.error('Error deleting model:', error);
                    showToast('Error deleting model.', 'danger');
                }
            });
        });
    });
</script>
@endpush