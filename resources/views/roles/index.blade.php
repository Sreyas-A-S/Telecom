@extends('layouts.admin')

@section('title', 'Roles')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">

            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Roles</li>
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
                    <h5>Roles List</h5>
                    <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal"
                        data-bs-target="#createRoleModal">
                        Create New Role
                    </button>
                </div>
                <div class="card-body">
                    <div id="alert-container" class="mx-2"></div>
                    <div class="table-responsive">
                        @if(checkMenu(Session::get('role_id'), 3, 'read'))
                        <table class="display" id="roles-table">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Role Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data will be loaded via AJAX --}}
                            </tbody>
                        </table>
                        @else
                        <div class="alert alert-danger" role="alert">
                            You do not have permission to view this content.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-labelledby="createRoleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRoleModalLabel">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 3, 'create'))
            <form id="createRoleForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="createRoleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="createRoleName" name="role" required>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="createRoleIsActive" name="is_active"
                            value="1" checked>
                        <label class="form-check-label" for="createRoleIsActive">
                            Status
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
            @else
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to create a new role.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- View Role Modal -->
<div class="modal fade" id="viewRoleModal" tabindex="-1" role="dialog" aria-labelledby="viewRoleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRoleModalLabel">View Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Role Name:</strong> <span id="viewRoleName"></span></p>
                <p><strong>Status:</strong> <span id="viewRoleIsActive"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-labelledby="editRoleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 3, 'update'))
            <form id="editRoleForm">
                @csrf
                @method('UPDATE')
                <div class="modal-body">
                    <input type="hidden" id="editRoleId" name="id">
                    <div class="mb-3">
                        <label for="editRoleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="editRoleName" name="role" required>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="editRoleIsActive" name="is_active" value="1">
                        <label class="form-check-label" for="editRoleIsActive">
                            Status
                        </label>
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
                    You do not have permission to edit this role.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Role Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" role="dialog" aria-labelledby="deleteRoleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRoleModalLabel">Delete Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 3, 'delete'))
            <div class="modal-body">
                <p>Are you sure you want to delete role: <strong id="deleteRoleName"></strong>?</p>
                <input type="hidden" id="deleteRoleId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteRole">Delete</button>
            </div>
            @else
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to delete this role.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        @if(checkMenu(Session::get('role_id'), 3, 'read'))
        var rolesTable = $('#roles-table').DataTable({
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
            ajax: "{{ route('roles.index') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, // Sl No
                {
                    data: 'role',
                    name: 'role',
                    render: function(data, type, row) {
                        return data.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

                    }
                },
                {
                    data: 'is_active',
                    name: 'Status',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return data ? '<span class="badge bg-success">Active</span>' :
                            '<span class="badge bg-danger">Inactive</span>';
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    width: '10%'
                }
            ],
            "drawCallback": function(settings) {
                feather.replace();
            }
        });
        @endif




        $('#createRoleForm').on('submit', function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "{{ route('roles.store') }}",
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#createRoleModal').modal('hide');
                    rolesTable.ajax.reload();
                    $('#createRoleForm')[0].reset();
                },
                error: function(error) {
                    console.error('Error creating role:', error);
                    showToast('Error creating role.', 'danger');
                }
            });
        });


        $('#viewRoleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var roleId = button.data('id');
            var modal = $(this);

            $.ajax({
                url: '/roles/' + roleId,
                method: 'GET',
                success: function(data) {
                    modal.find('#viewRoleId').text(data.id);
                    modal.find('#viewRoleName').text(data.role);
                    modal.find('#viewRoleIsActive').html(data.is_active ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-danger">Inactive</span>');

                },
                error: function(error) {
                    console.error('Error fetching role data:', error);
                    showToast('Error fetching role data.', 'danger');
                }
            });
        });

        $('#editRoleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var roleId = button.data('id');
            var modal = $(this);

            $.ajax({
                url: '/roles/' + roleId + '/edit',
                method: 'GET',
                success: function(data) {
                    modal.find('#editRoleId').val(data.id);
                    modal.find('#editRoleName').val(data.role);
                    modal.find('#editRoleIsActive').prop('checked', data.is_active);
                },
                error: function(error) {
                    console.error('Error fetching role data for edit:', error);
                    showToast('Error fetching role data for edit.',
                        'danger');
                }
            });
        });

        $('#editRoleForm').on('submit', function(event) {
            event.preventDefault();
            var roleId = $('#editRoleId').val();
            var formData = $(this).serialize();

            $.ajax({
                url: "{{ route('roles.update', ['role' => 'ROLE_ID_PLACEHOLDER']) }}".replace(
                    'ROLE_ID_PLACEHOLDER', roleId),
                method: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#editRoleModal').modal('hide');
                    rolesTable.ajax.reload();
                },
                error: function(error) {
                    console.error('Error updating role:', error);
                    showToast('Error updating role.', 'danger');
                }
            });
        });


        $('#deleteRoleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var roleId = button.data('id');
            var roleName = button.closest('tr').find('td:nth-child(2)')
                .text();
            var modal = $(this);

            modal.find('#deleteRoleId').val(roleId);
            modal.find('#deleteRoleName').text(roleName);
        });

        $('#confirmDeleteRole').on('click', function() {
            var roleId = $('#deleteRoleId').val();

            $.ajax({
                url: '/roles/' + roleId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#deleteRoleModal').modal('hide');
                    rolesTable.ajax.reload();
                },
                error: function(error) {
                    console.error('Error deleting role:', error);
                    showToast('Error deleting role.', 'danger');
                }
            });
        });
    });
</script>
@endpush