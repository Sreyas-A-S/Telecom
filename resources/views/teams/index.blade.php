@extends('layouts.admin')

@section('title', 'Teams')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h1>Teams</h1>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                            Create Team
                        </button>
                    </div>
                    <div class="card-body">
                        <table class="table" id="teams-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Parent Team</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Team Modal -->
    <div class="modal fade" id="createTeamModal" tabindex="-1" role="dialog" aria-labelledby="createTeamModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTeamModalLabel">Create Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('teams.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="parent_id">Parent Team</label>
                            <select name="parent_id" id="parent_id" class="form-control">
                                <option value="">Select Parent Team</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="zones">Zones</label>
                            <select name="zones[]" id="zones" class="form-control" multiple>
                                @foreach ($zones as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="relationship_type">Relationship Type</label>
                            <select name="relationship_type" id="relationship_type" class="form-control">
                                <option value="">Select Relationship Type</option>
                                <option value="parent">Parent</option>
                                <option value="child">Child</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Team Modal -->
    <div class="modal fade" id="editTeamModal" tabindex="-1" role="dialog" aria-labelledby="editTeamModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTeamModalLabel">Edit Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTeamForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_name">Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_parent_id">Parent Team</label>
                            <select name="parent_id" id="edit_parent_id" class="form-control">
                                <option value="">Select Parent Team</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_zones">Zones</label>
                            <select name="zones[]" id="edit_zones" class="form-control" multiple>
                                @foreach ($zones as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_relationship_type">Relationship Type</label>
                            <select name="relationship_type" id="edit_relationship_type" class="form-control">
                                <option value="">Select Relationship Type</option>
                                <option value="parent">Parent</option>
                                <option value="child">Child</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Show Team Modal -->
    <div class="modal fade" id="showTeamModal" tabindex="-1" role="dialog" aria-labelledby="showTeamModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showTeamModalLabel">Team Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <td id="show_id"></td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td id="show_name"></td>
                            </tr>
                            <tr>
                                <th>Parent Team</th>
                                <td id="show_parent_name"></td>
                            </tr>
                            <tr>
                                <th>Zones</th>
                                <td id="show_zones"></td>
                            </tr>
                        </tbody>
                    </table>
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
            $('#teams-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('teams.datatable') }}',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'parent_name',
                        name: 'parent_name'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        $('#editTeamModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var teamId = button.data('id')
            var modal = $(this)
            $.get('/teams/' + teamId, function(data) {
                modal.find('#edit_name').val(data.team.name)
                modal.find('#edit_parent_id').val(data.team.parent_id)
                modal.find('#editTeamForm').attr('action', '/teams/' + teamId)

                // Populate zones and relationship type
                var selectedZones = [];
                var relationshipType = '';
                if (data.team.zones && data.team.zones.length > 0) {
                    relationshipType = data.team.zones[0].pivot.relationship_type; // Assuming one type for all for now
                    $.each(data.team.zones, function(index, zone) {
                        selectedZones.push(zone.id);
                    });
                }
                modal.find('#edit_zones').val(selectedZones);
                modal.find('#edit_relationship_type').val(relationshipType);
            })
        })

        $('#showTeamModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var teamId = button.data('id')
            var modal = $(this)
            $.get('/teams/' + teamId, function(data) {
                modal.find('#show_id').text(data.team.id)
                modal.find('#show_name').text(data.team.name)
                modal.find('#show_parent_name').text(data.parent_name)

                var zonesList = '';
                if (data.team.zones && data.team.zones.length > 0) {
                    $.each(data.team.zones, function(index, zone) {
                        zonesList += zone.name + ' (' + zone.pivot.relationship_type + ')<br>';
                    });
                } else {
                    zonesList = 'N/A';
                }
                modal.find('#show_zones').html(zonesList);
            })
        })
    </script>
@endpush