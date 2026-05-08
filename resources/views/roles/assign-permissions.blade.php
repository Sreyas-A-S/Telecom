@extends('layouts.admin')

@section('title', 'Assign Permissions')

<style>
    #menu_13_read,
    label[for="menu_13_read"],
    #menu_13_update,
    label[for="menu_13_update"],
    #menu_13_delete,
    label[for="menu_13_delete"] {
        display: none;
    }

    .check-all-label {
        font-weight: bold;
        color: #0056b3;
        /* A distinct blue color */
    }

    /* Better visibility for unchecked state */
    .table .form-check-input {
        border: 2px solid #9ca3af;
        /* Increased contrast for un-checked state */
        width: 1.2rem;
        height: 1.2rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .table .form-check-input:hover {
        border-color: #9ca3af;
    }

    /* Custom Checkbox Colors */
    .table .form-check-input-create:checked {
        background-color: #1e40af;
        border-color: #1e40af;
        box-shadow: 0 2px 4px rgba(30, 64, 175, 0.4);
    }

    .table .form-check-input-read:checked {
        background-color: #0f766e;
        border-color: #0f766e;
        box-shadow: 0 2px 4px rgba(15, 118, 110, 0.4);
    }

    .table .form-check-input-update:checked {
        background-color: #b45309;
        border-color: #b45309;
        box-shadow: 0 2px 4px rgba(180, 83, 9, 0.4);
    }

    .table .form-check-input-delete:checked {
        background-color: #be123c;
        border-color: #be123c;
        box-shadow: 0 2px 4px rgba(190, 18, 60, 0.4);
    }
</style>


@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Assign Permissions</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active">Assign Permissions</li>
                    <li class="breadcrumb-item">{{ ucwords(str_replace("_", " ", $role->role)) }}</li>
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
                    <h5>Assign Permissions to Role: {{ ucwords(str_replace("_", " ", $role->role)) }}</h5>
                    <div class="mt-2">
                        <form action="{{ route('roles.import-permissions', $role->id) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap gap-3 align-items-center">
                            @csrf
                            <div style="max-width: 250px; flex: 1 1 200px;">
                                <input type="file" name="file" class="form-control form-control-sm" required accept=".xlsx,.xls,.csv">
                            </div>
                            <button type="submit" class="btn btn-sm btn-info text-nowrap px-4 py-1">Import Permissions</button>
                            <a href="{{ route('roles.export-template', $role->id) }}" class="btn btn-sm btn-secondary text-nowrap px-4 py-1">Download Template</a>
                        </form>
                    </div>
                </div>
                <div id="alert-container" class="mx-2"></div> {{-- Placeholder for alerts --}}
                <div class="card-body">
                    @if(checkMenu(Session::get('role_id'), 3, 'update') == false)
                    <div class="alert alert-danger" role="alert">
                        You cannot assign permissions unless you are permitted to access the Roles page.
                    </div>

                    @else

                    @if(checkMenu(Session::get('role_id'), 15, 'update'))
                    <div class="table-responsive border-bottom">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Menu</th>
                                    <th>Permissions</th>
                                </tr>
                            </thead>
                            @if(checkMenu(Session::get('role_id'), 15, 'read'))
                            <tbody>
                                @php $moduleGroupIndex = 0; @endphp {{-- Initialize module group counter --}}
                                @foreach ($menuGroups as $menuGroup)
                                @php
                                $moduleGroupIndex++; // Increment module group counter
                                $firstMenuInGroup = true;
                                $menuCount = $menuGroup->menus->count();
                                $moduleGroupBgClass = ($moduleGroupIndex % 2 !== 0) ? 'bg-light' : ''; // Only bg-light for odd module groups
                                @endphp
                                @foreach ($menuGroup->menus as $menu)
                                <tr class="{{ $moduleGroupBgClass }} fw-bold ">
                                    @if($firstMenuInGroup)
                                    <td rowspan="{{ $menuCount }}" class="py-2">{{ $menuGroup->name }}</td>
                                    @php $firstMenuInGroup = false; @endphp
                                    @endif
                                    <td>{{ $menu->name }}</td>
                                    <td>
                                        @php
                                        // Find the permission record for this menu
                                        $permission = $menu->permission; // This assumes a menu has one permission record
                                        @endphp
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input permission-checkbox form-check-input-create" type="checkbox"
                                                data-role-id="{{ $role->id }}"
                                                data-permission-id="{{ $permission->id }}"

                                                data-menu-id="{{ $menu->id }}"
                                                data-menu-id="{{ $menu->id }}"
                                                data-action="create"
                                                id="menu_{{ $menu->id }}_create"
                                                {{ $permission->can_create ?'checked':'' }}>
                                            <label class="form-check-label" for="menu_{{ $menu->id }}_create">Create</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input permission-checkbox form-check-input-read" type="checkbox"
                                                data-role-id="{{ $role->id }}"
                                                data-permission-id="{{ $permission->id }}"

                                                data-menu-id="{{ $menu->id }}"
                                                data-menu-id="{{ $menu->id }}"
                                                data-action="read"
                                                id="menu_{{ $menu->id }}_read"
                                                {{ $permission->can_read ?'checked':'' }}>
                                            <label class="form-check-label" for="menu_{{ $menu->id }}_read">Read</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input permission-checkbox form-check-input-update" type="checkbox"
                                                data-role-id="{{ $role->id }}"
                                                data-permission-id="{{ $permission->id }}"

                                                data-menu-id="{{ $menu->id }}"
                                                data-menu-id="{{ $menu->id }}"
                                                data-action="update"
                                                id="menu_{{ $menu->id }}_update"
                                                {{ $permission->can_update ?'checked':'' }}>
                                            <label class="form-check-label" for="menu_{{ $menu->id }}_update">Update</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input permission-checkbox form-check-input-delete" type="checkbox"
                                                data-role-id="{{ $role->id }}"
                                                data-permission-id="{{ $permission->id }}"

                                                data-menu-id="{{ $menu->id }}"
                                                data-menu-id="{{ $menu->id }}"
                                                data-action="delete"
                                                id="menu_{{ $menu->id }}_delete"
                                                {{ $permission->can_delete ?'checked':'' }}>
                                            <label class="form-check-label" for="menu_{{ $menu->id }}_delete">Delete</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input check-all-menu-permissions" id="check_all_menu_{{ $menu->id }}">
                                            <label class="form-check-label check-all-label" for="check_all_menu_{{ $menu->id }}">Check All</label>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @endforeach
                            <tbody>
                                @else
                                @php
                                abort(403, 'Unauthorized action.');
                                @endphp
                                @endif
                        </table>
                    </div>
                    @else
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to update permissions.
                    </div>
                    @endif
                    @endif

                    <a href="{{ route('roles.index') }}" class="btn btn-secondary mt-3">Back to Roles</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Set initial state of "Check All" checkboxes on page load
        $('.check-all-menu-permissions').each(function() {
            var menuId = $(this).attr('id').replace('check_all_menu_', '');
            var allChecked = true;
            $('[data-menu-id="' + menuId + '"].permission-checkbox').each(function() {
                if (!$(this).is(':checked')) {
                    allChecked = false;
                    return false; // break the loop
                }
            });
            $(this).prop('checked', allChecked);
        });

        // Function to display Bootstrap toasts
        function showAlert(message, type) {
            var toastContainer = $('#toast-container');
            if (toastContainer.length === 0) {
                toastContainer = $('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
                $('body').append(toastContainer);
            }

            // Limit the number of toasts
            var maxToasts = 3;
            var toasts = toastContainer.children('.toast');
            if (toasts.length >= maxToasts) {
                // Remove the oldest toast
                toasts.first().remove();
            }

            var toastHtml = '<div class="toast align-items-center text-white bg-' + type + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
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

        $('.permission-checkbox').on('change', function() {
            var menuId = $(this).data('menu-id');
            var allChecked = true;
            $('[data-menu-id="' + menuId + '"].permission-checkbox').each(function() {
                if (!$(this).is(':checked')) {
                    allChecked = false;
                    return false; // break the loop
                }
            });
            $('#check_all_menu_' + menuId).prop('checked', allChecked);

            var roleId = "{{ $role->id }}";
            var permissionId = $(this).data('permission-id');
            var menuGroupId = $(this).data('menu-group-id'); // Get menu_group_id
            var action = $(this).data('action'); // Get action type
            var status = $(this).is(':checked'); // true if checked, false if unchecked

            $.ajax({
                url: "{{ route('roles.toggle-permission') }}",
                method: 'POST',
                data: {
                    role_id: roleId,
                    permission_id: permissionId,
                    menu_group_id: menuGroupId, // Send menu_group_id
                    menu_id: menuId, // Send menu_id
                    action: action, // Send action type
                    status: status ? 1 : 0,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showAlert(response.message, 'success');
                },
                error: function(error) {
                    console.error('Error toggling permission:', error);
                    showAlert('Error toggling permission.', 'danger');
                }
            });
        });

        // Handle "Check All" for a specific menu
        $(document).on('change', '.check-all-menu-permissions', function() {
            var menuId = $(this).attr('id').replace('check_all_menu_', '');
            var isChecked = $(this).is(':checked');

            // Find all permission checkboxes for this menu and set their checked state
            // and trigger the change event to update the backend
            $('[data-menu-id="' + menuId + '"].permission-checkbox').prop('checked', isChecked).trigger('change');
        });
    });
</script>
@endpush