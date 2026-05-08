@extends('layouts.admin')

@section('title', 'Dealerships')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Dealerships List</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Dealerships</li>
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
                    <h5>Dealerships List</h5>
                    <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal"
                        data-bs-target="#createDealershipModal">
                        Create New Dealership
                    </button>
                </div>
                <div class="card-body">
                    <div id="alert-container" class="mx-2"></div> {{-- Placeholder for alerts --}}
                    <div class="table-responsive">
                        @if(checkMenu(Session::get('role_id'), 1, 'read'))
                        <table class="display" id="dealerships-table">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Dealership Name</th>
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

    <!-- Create Dealership Modal -->
    <div class="modal fade" id="createDealershipModal" tabindex="-1" role="dialog" aria-labelledby="createDealershipModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createDealershipModalLabel">Create New Dealership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @if(checkMenu(Session::get('role_id'), 1, 'create'))
                <form id="createDealershipForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="createDealershipName" class="form-label">Dealership Name</label>
                            <input type="text" class="form-control" id="createDealershipName" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Dealership</button>
                    </div>
                </form>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to create a dealership.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- View Dealership Modal -->
    <div class="modal fade" id="viewDealershipModal" tabindex="-1" role="dialog" aria-labelledby="viewDealershipModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDealershipModalLabel">View Dealership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Dealership Name:</strong> <span id="viewDealershipName"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Dealership Modal -->
    <div class="modal fade" id="editDealershipModal" tabindex="-1" role="dialog" aria-labelledby="editDealershipModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDealershipModalLabel">Edit Dealership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @if(checkMenu(Session::get('role_id'), 1, 'update'))
                <form id="editDealershipForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="editDealershipId" name="id">
                        <div class="mb-3">
                            <label for="editDealershipName" class="form-label">Dealership Name</label>
                            <input type="text" class="form-control" id="editDealershipName" name="name" required>
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
                        You do not have permission to edit this dealership.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Dealership Modal -->
    <div class="modal fade" id="deleteDealershipModal" tabindex="-1" role="dialog" aria-labelledby="deleteDealershipModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDealershipModalLabel">Delete Dealership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @if(checkMenu(Session::get('role_id'), 1, 'delete'))
                <div class="modal-body">
                    <p>Are you sure you want to delete dealership: <strong id="deleteDealershipName"></strong>?</p>
                    <input type="hidden" id="deleteDealershipId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteDealership">Delete</button>
                </div>
                @else
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        You do not have permission to delete this dealership.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
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
            @if(checkMenu(Session::get('role_id'), 1, 'read'))
            var dealershipsTable = $('#dealerships-table').DataTable({
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
                ajax: "{{ route('dealerships.index') }}",
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

            // Function to display Bootstrap toasts (re-using from roles.assign-permissions.blade.php)
            function showAlert(message, type) {
                var toastContainer = $('#toast-container');
                if (toastContainer.length === 0) {
                    toastContainer = $('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
                    $('body').append(toastContainer);
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

            // Create Dealership
            $('#createDealershipForm').on('submit', function(event) {
                event.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('dealerships.store') }}",
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showAlert(response.message, 'success');
                        $('#createDealershipModal').modal('hide');
                        dealershipsTable.ajax.reload();
                        $('#createDealershipForm')[0].reset();
                    },
                    error: function(error) {
                        console.error('Error creating dealership:', error);
                        showAlert('Error creating dealership.', 'danger');
                    }
                });
            });

            // View Dealership
            $('#viewDealershipModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var dealershipId = button.data('id');
                var modal = $(this);

                $.ajax({
                    url: '/dealerships/' + dealershipId,
                    method: 'GET',
                    success: function(data) {
                        modal.find('#viewDealershipName').text(data.name);
                    },
                    error: function(error) {
                        console.error('Error fetching dealership data:', error);
                        showAlert('Error fetching dealership data.', 'danger');
                    }
                });
            });

            // Edit Dealership
            $('#editDealershipModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var dealershipId = button.data('id');
                var modal = $(this);

                $.ajax({
                    url: '/dealerships/' + dealershipId + '/edit',
                    method: 'GET',
                    success: function(data) {
                        modal.find('#editDealershipId').val(data.id);
                        modal.find('#editDealershipName').val(data.name);
                    },
                    error: function(error) {
                        console.error('Error fetching dealership data for edit:', error);
                        showAlert('Error fetching dealership data for edit.', 'danger');
                    }
                });
            });

            $('#editDealershipForm').on('submit', function(event) {
                event.preventDefault();
                var dealershipId = $('#editDealershipId').val();
                var formData = $(this).serialize();

                $.ajax({
                    url: '/dealerships/' + dealershipId,
                    method: 'PUT',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showAlert(response.message, 'success');
                        $('#editDealershipModal').modal('hide');
                        dealershipsTable.ajax.reload();
                    },
                    error: function(error) {
                        console.error('Error updating dealership:', error);
                        showAlert('Error updating dealership.', 'danger');
                    }
                });
            });

            // Delete Dealership
            $('#deleteDealershipModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var dealershipId = button.data('id');
                var dealershipName = button.closest('tr').find('td:nth-child(2)').text();
                var modal = $(this);

                modal.find('#deleteDealershipId').val(dealershipId);
                modal.find('#deleteDealershipName').text(dealershipName);
            });

            $('#confirmDeleteDealership').on('click', function() {
                var dealershipId = $('#deleteDealershipId').val();

                $.ajax({
                    url: '/dealerships/' + dealershipId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showAlert(response.message, 'success');
                        $('#deleteDealershipModal').modal('hide');
                        dealershipsTable.ajax.reload();
                    },
                    error: function(error) {
                        console.error('Error deleting dealership:', error);
                        showAlert('Error deleting dealership.', 'danger');
                    }
                });
            });
        });
    </script>
    @endpush