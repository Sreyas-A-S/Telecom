@extends('layouts.admin')

@section('title', 'Pipelines')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
@endpush

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Pipelines</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Pipelines</li>
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
            <ul class="nav nav-tabs d-flex" id="pipelineTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="view-tab" data-bs-toggle="tab" data-bs-target="#view"
                        type="button" role="tab" aria-controls="view" aria-selected="true">View Pipelines</button>
                </li>

            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="pipelineTabContent">
                        <div class="tab-pane fade show active" id="view" role="tabpanel" aria-labelledby="view-tab">
                            @if(!checkMenu(Session::get('role_id'), 10, 'read'))
                            <div class="alert alert-danger">You do not have permission to view pipelines.</div>
                            @else
                            <div class="row mb-3">
                                <div class="col-md-3 mb-2">
                                    <label for="filter_dealership">Dealership</label>
                                    <select class="form-select" id="filter_dealership" d
                                    @if(Auth::user()->dealership)
                                    disabled
                                    @endif
                                    >
                                        <option value="">All Dealerships</option>
                                        @foreach($dealerships as $dealership)
                                        @if($dealership->brand == 1)
                                        <option value="{{ $dealership->id }}" @if(Auth::user()->dealership == $dealership->id) selected @endif>{{ $dealership->name }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="filter_lead_source">Lead Source</label>
                                    <select class="form-select" id="filter_lead_source">
                                        <option value="">All Sources</option>
                                        @foreach($leadSources as $source)
                                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="filter_lead_category">Lead Category</label>
                                    <select class="form-select" id="filter_lead_category">
                                        <option value="">All Stages</option>
                                        @foreach($leadCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="filter_status">Status</label>
                                    <select class="form-select" id="filter_status">
                                        <option value="">All Statuses</option>
                                        <option value="New">New</option>
                                        <option value="Contacted">Contacted</option>
                                        <option value="Qualified">Qualified</option>
                                        <option value="Proposal Sent">Proposal Sent</option>
                                        <option value="Negotiation">Negotiation</option>
                                        <option value="Closed Won">Closed Won</option>
                                        <option value="Closed Lost">Closed Lost</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="filter_start_date">Start Date</label>
                                    <input type="date" class="form-control" id="filter_start_date">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="filter_end_date">End Date</label>
                                    <input type="date" class="form-control" id="filter_end_date">
                                </div>
                                <div class="col-md-3 mb-2 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" id="filter_apply">Apply Filters</button>
                                </div>
                                <div class="col-md-3 mb-2 d-flex align-items-end">
                                    <button class="btn btn-secondary w-100" id="filter_reset">Reset</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="display" id="pipelines-table">
                                    <thead>
                                        <tr>
                                            <th>SL NO</th>
                                            <th>Dealer & Source</th>
                                            <th>Customer Info</th>
                                            <th>Product Info</th>
                                            <th>Location</th>
                                            <th>Lead Value</th>
                                            <th>Allow Follow-up</th>
                                            <th>Lead Type</th>
                                            <th>Login Status</th>
                                            <th>Lead Stage</th>
                                            <th>Billing</th>
                                            <th>Remarks</th>
                                            <th>Assigned Employee</th>
                                            <th>Probability</th>
                                            <th>Financier</th>
                                            <th>Type</th>
                                            <th>Stage</th>
                                            <th>Status</th>
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

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Pipeline Modal -->
<div class="modal fade" id="editPipelineModal" tabindex="-1" aria-labelledby="editPipelineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPipelineModalLabel">Edit Pipeline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(!checkMenu(Session::get('role_id'), 10, 'update'))
            <div class="alert alert-danger m-3">You do not have permission to edit leads.</div>
            @else
            <form id="editPipelineForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editPipelineId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCustomerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="editCustomerName" name="customer_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPhoneNumber" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editLeadSource" class="form-label">Lead Source</label>
                            <select class="form-select" id="editLeadSource" name="lead_source_id" required>
                                <option value="">Select Lead Source</option>
                                @foreach ($leadSources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLeadCategory" class="form-label">Lead Category</label>
                            <select class="form-select" id="editLeadCategory" name="lead_category_id" required>
                                <option value="">Select Lead Category</option>
                                @foreach ($leadCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editLeadStatus" class="form-label">Status</label>
                                <select class="form-select" id="editLeadStatus" name="status" required>
                                    <option value="New">New</option>
                                    <option value="Contacted">Contacted</option>
                                    <option value="Qualified">Qualified</option>
                                    <option value="Unqualified">Unqualified</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editAssignedTo" class="form-label">Assigned To</label>
                                <select class="form-select" id="editAssignedTo" name="assigned_to" required>
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="editLeadNotes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="editLeadNotes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="editLeadValue" class="form-label">Lead Value</label>
                                    <input type="number" step="0.01" class="form-control" id="editLeadValue" name="lead_value" readonly style="background-color: #e9ecef;">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="editChanceOfSuccess" class="form-label">Chance of Success (%)</label>
                                    <input type="number" class="form-control" id="editChanceOfSuccess" name="chance_of_success" min="0" max="100">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="editProduct" class="form-label">Product</label>
                                    <select class="form-select" id="editProduct" name="product_id">
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="editDealershipId" class="form-label">Dealership</label>
                                    <select class="form-select" id="editDealershipId" name="dealership_id">
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
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Delete Pipeline Modal -->
<div class="modal fade" id="deletePipelineModal" tabindex="-1" role="dialog" aria-labelledby="deletePipelineModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePipelineModalLabel">Delete Pipeline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(!checkMenu(Session::get('role_id'), 10, 'delete'))
            <div class="alert alert-danger m-3">You do not have permission to delete leads.</div>
            @else
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deletePipelineName"></strong>?</p>
                <input type="hidden" id="deletePipelineId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteLead">Delete</button>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script>
    $(document).ready(function() {
        var pipelinesTable = $('#pipelines-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pipelines.datatable') }}",
                data: function(d) {
                    d.dealership_id = $('#filter_dealership').val();
                    d.lead_source_id = $('#filter_lead_source').val();
                    d.lead_category_id = $('#filter_lead_category').val();
                    d.status = $('#filter_status').val();
                    d.start_date = $('#filter_start_date').val();
                    d.end_date = $('#filter_end_date').val();
                }
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                    extend: 'csv',
                    className: 'btn btn-sm btn-primary text-white'
                },
                {
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-sm btn-success text-white',
                    action: function(e, dt, node, config) {
                        // Capture standard DataTables search if needed
                        var info = dt.page.info();
                        var params = $.param({
                            search_value: dt.search(),
                            start: info.start,
                            length: info.length
                        });
                        window.location.href = "{{ route('pipelines.export-excel') }}?" + params;
                    }
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

            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'dealer_info',
                    name: 'dealership.name'
                },
                {
                    data: 'customer_info',
                    name: 'customer_name'
                },
                {
                    data: 'product_info',
                    name: 'model'
                },
                {
                    data: 'location',
                    name: 'location'
                },
                {
                    data: 'lead_value_display',
                    name: 'lead_value'
                },
                {
                    data: 'allow_follow_up_display',
                    name: 'allow_follow_up'
                },
                {
                    data: 'lead_type',
                    name: 'type'
                },
                {
                    data: 'login_status_display',
                    name: 'login_status'
                },
                {
                    data: 'lead_stage',
                    name: 'stage'
                },
                {
                    data: 'billing_display',
                    name: 'billing'
                },
                {
                    data: 'remarks_display',
                    name: 'remarks'
                },
                {
                    data: 'assigned_employee_name',
                    name: 'employee.name'
                },
                {
                    data: 'probability_badge',
                    name: 'probability',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'financier',
                    name: 'financier'
                },
                {
                    data: 'type_badge',
                    name: 'type',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'stage_badge',
                    name: 'stage',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status_badge',
                    name: 'current_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                },
            ]
        });




        $('#filter_apply').on('click', function() {
            pipelinesTable.draw();
        });

        $('#filter_reset').on('click', function() {
            $('#filter_dealership').val('');
            $('#filter_lead_source').val('');
            $('#filter_lead_category').val('');
            $('#filter_status').val('');
            $('#filter_start_date').val('');
            $('#filter_end_date').val('');
            pipelinesTable.draw();
        });
        // Handle Edit Button Click
        $('#pipelines-table').on('click', '.edit', function() {
            var id = $(this).data('id');
            $.get('/leads/' + id + '/edit', function(data) {
                $('#editLeadId').val(data.id);
                $('#editLeadName').val(data.name);
                $('#editLeadEmail').val(data.email);
                $('#editLeadPhone').val(data.phone_number);
                $('#editLeadSource').val(data.lead_source_id);
                $('#editLeadCategory').val(data.lead_category_id);
                $('#editLeadStatus').val(data.status);
                $('#editAssignedTo').val(data.assigned_to);
                $('#editLeadNotes').val(data.notes);
                $('#editLeadValue').val(data.lead_value);
                $('#editChanceOfSuccess').val(data.chance_of_success);
                $('#editProduct').val(data.product_id);
                $('#editDealershipId').val(data.dealership_id);
                $('#editLeadModal').modal('show');
            });
        });

        // Handle Edit Form Submission
        $('#editLeadForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#editLeadId').val();
            var formData = $(this).serialize();
            $.ajax({
                url: '/leads/' + id,
                method: 'PUT',
                data: formData,
                success: function(response) {
                    showToast(response.message, 'success');
                    leadsTable.ajax.reload();
                    $('#editLeadModal').modal('hide');
                },
                error: function(error) {
                    showToast('Error updating lead record.', 'danger');
                }
            });
        });

        // Handle Delete Button Click
        $('#pipelines-table').on('click', '.delete', function() {
            var id = $(this).data('id');
            var leadName = $(this).data('lead-name');
            $('#deleteLeadId').val(id);
            $('#deleteLeadName').text(leadName);
            $('#deleteLeadModal').modal('show');
        });

        // Handle Delete Confirmation
        $('#confirmDeleteLead').on('click', function() {
            var id = $('#deleteLeadId').val();
            $.ajax({
                url: '/leads/' + id,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    pipelinesTable.ajax.reload();
                    $('#deleteLeadModal').modal('hide');
                },
                error: function(error) {
                    showToast('Error deleting lead record.', 'danger');
                }
            });
        });
        // Read More/Read Less functionality for remarks
        $('#pipelines-table').on('click', '.read-more-link', function() {
            var $this = $(this);
            var $truncated = $this.siblings('.remarks-truncated');
            var $full = $this.siblings('.remarks-full');

            if ($truncated.is(':visible')) {
                $truncated.toggle();
                $full.toggle();
                $this.text('Read Less');
            } else {
                $truncated.toggle();
                $full.toggle();
                $this.text('Read More');
            }
        });
    });
</script>
@endpush