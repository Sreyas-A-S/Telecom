@extends('layouts.admin')

@section('title', 'Zones')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Zones List</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Zones</li>
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
                    <h5>Zones List</h5>
                    <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal"
                        data-bs-target="#createZoneModal">
                        Create New Zone
                    </button>
                </div>
                <div class="card-body">
                    <div id="alert-container" class="mx-2"></div> {{-- Placeholder for alerts --}}
                    <div class="table-responsive">
                        @if(checkMenu(Session::get('role_id'), 2, 'read'))
                        <table class="display" id="zones-table">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Zone Name</th>
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

<!-- Create Zone Modal -->
<div class="modal fade" id="createZoneModal" tabindex="-1" role="dialog" aria-labelledby="createZoneModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createZoneModalLabel">Create New Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 2, 'create'))
            <form id="createZoneForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="createZoneName" class="form-label">Zone Name</label>
                        <input type="text" class="form-control" id="createZoneName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Zone</button>
                </div>
            </form>
            @else
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to create a new zone.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- View Zone Modal -->
<div class="modal fade" id="viewZoneModal" tabindex="-1" role="dialog" aria-labelledby="viewZoneModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewZoneModalLabel">View Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Zone Name:</strong> <span id="viewZoneName"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Zone Modal -->
<div class="modal fade" id="editZoneModal" tabindex="-1" role="dialog" aria-labelledby="editZoneModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editZoneModalLabel">Edit Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 2, 'update'))
            <form id="editZoneForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editZoneId" name="id">
                    <div class="mb-3">
                        <label for="editZoneName" class="form-label">Zone Name</label>
                        <input type="text" class="form-control" id="editZoneName" name="name" required>
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
                    You do not have permission to edit this zone.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Zone Modal -->
<div class="modal fade" id="deleteZoneModal" tabindex="-1" role="dialog" aria-labelledby="deleteZoneModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteZoneModalLabel">Delete Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(checkMenu(Session::get('role_id'), 2, 'delete'))
            <div class="modal-body">
                <p>Are you sure you want to delete zone: <strong id="deleteZoneName"></strong>?</p>
                <input type="hidden" id="deleteZoneId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteZone">Delete</button>
            </div>
            @else
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    You do not have permission to delete this zone.
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
        @if(checkMenu(Session::get('role_id'), 2, 'read')) {
            var zonesTable = $('#zones-table').DataTable({
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
                ajax: "{{ route('zones.index') }}",
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
        }
        @endif



        // Create Zone
        $('#createZoneForm').on('submit', function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "{{ route('zones.store') }}",
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#createZoneModal').modal('hide');
                    zonesTable.ajax.reload();
                    $('#createZoneForm')[0].reset();
                },
                error: function(error) {
                    console.error('Error creating zone:', error);
                    showToast('Error creating zone.', 'danger');
                }
            });
        });

        // View Zone
        $('#viewZoneModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var zoneId = button.data('id');
            var modal = $(this);

            $.ajax({
                url: '/zones/' + zoneId,
                method: 'GET',
                success: function(data) {
                    modal.find('#viewZoneName').text(data.name);
                },
                error: function(error) {
                    console.error('Error fetching zone data:', error);
                    showToast('Error fetching zone data.', 'danger');
                }
            });
        });

        // Edit Zone
        $('#editZoneModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var zoneId = button.data('id');
            var modal = $(this);

            $.ajax({
                url: '/zones/' + zoneId + '/edit',
                method: 'GET',
                success: function(data) {
                    modal.find('#editZoneId').val(data.id);
                    modal.find('#editZoneName').val(data.name);
                },
                error: function(error) {
                    console.error('Error fetching zone data for edit:', error);
                    showToast('Error fetching zone data for edit.', 'danger');
                }
            });
        });

        $('#editZoneForm').on('submit', function(event) {
            event.preventDefault();
            var zoneId = $('#editZoneId').val();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route('zones.update', ['zone' => 'ZONE_ID_PLACEHOLDER']) }}'.replace('ZONE_ID_PLACEHOLDER', zoneId),
                method: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#editZoneModal').modal('hide');
                    zonesTable.ajax.reload();
                },
                error: function(error) {
                    console.error('Error updating zone:', error);
                    showToast('Error updating zone.', 'danger');
                }
            });
        });

        // Delete Zone
        $('#deleteZoneModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var zoneId = button.data('id');
            var zoneName = button.closest('tr').find('td:nth-child(2)').text();
            var modal = $(this);

            modal.find('#deleteZoneId').val(zoneId);
            modal.find('#deleteZoneName').text(zoneName);
        });

        $('#confirmDeleteZone').on('click', function() {
            var zoneId = $('#deleteZoneId').val();

            $.ajax({
                url: '/zones/' + zoneId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#deleteZoneModal').modal('hide');
                    zonesTable.ajax.reload();
                },
                error: function(error) {
                    console.error('Error deleting zone:', error);
                    showToast('Error deleting zone.', 'danger');
                }
            });
        });
    });
</script>
@endpush