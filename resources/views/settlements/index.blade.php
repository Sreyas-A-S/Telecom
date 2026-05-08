@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Settlements List</h5>
                    <div>
                        @if(checkMenu(Session::get('role_id'), 24, 'read'))
                        <a href="{{ route('settlements.notifications') }}" class="btn btn-info btn-sm me-2">Notification Logs</a>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportSettlementModal">Export to Excel</button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <ul class="nav nav-tabs" id="settlementsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="view-settlements-tab" data-bs-toggle="tab" data-bs-target="#view-settlements" type="button" role="tab" aria-controls="view-settlements" aria-selected="true">View Settlements</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="create-settlements-tab" data-bs-toggle="tab" data-bs-target="#create-settlements" type="button" role="tab" aria-controls="create-settlements" aria-selected="false">Create Settlement</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="settlementsTabContent">
                        <div class="tab-pane fade show active" id="view-settlements" role="tabpanel" aria-labelledby="view-settlements-tab">
                            <h5 class="mb-3 mt-3">All Settlements</h5>
                            <div class="table-responsive">
                                <table id="settlements-datatable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Employee Code</th>
                                            <th>Employee Name</th>
                                            <th>Department</th>
                                            <th>Date of Joining</th>
                                            <th>Date of Resignation</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Datatable will populate this --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="create-settlements" role="tabpanel" aria-labelledby="create-settlements-tab">
                            <h5 class="mb-3 mt-3">Create New Settlement</h5>
                            <form id="createSettlementForm" action="{{ route('settlements.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="employee_code" class="form-label">Employee Code</label>
                                        <select class="form-control" id="employee_code" name="employee_code" required></select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="employee_name" class="form-label">Name of Employee</label>
                                        <input type="text" class="form-control" id="employee_name" name="employee_name" value="{{ old('employee_name') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="age" class="form-label">Age</label>
                                        <input type="number" class="form-control" id="age" name="age" value="{{ old('age') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control" id="department" name="department" value="{{ old('department') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="head_office_branch" class="form-label">Head Office/Branch</label>
                                        <input type="text" class="form-control" id="head_office_branch" name="head_office_branch" value="{{ old('head_office_branch') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="designation" class="form-label">Designation</label>
                                        <input type="text" class="form-control" id="designation" name="designation" value="{{ old('designation') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="date_of_joining" class="form-label">Date of Joining</label>
                                        <input type="date" class="form-control" id="date_of_joining" name="date_of_joining" value="{{ old('date_of_joining') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="date_of_resignation" class="form-label">Date of Resignation</label>
                                        <input type="date" class="form-control" id="date_of_resignation" name="date_of_resignation" value="{{ old('date_of_resignation') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="notice_period_end_date" class="form-label">Notice Period End Date (Calculated)</label>
                                        <input type="date" class="form-control" id="notice_period_end_date" readonly>
                                        <small class="text-muted">Based on {{ $noticePeriodDuration ?? 0 }} month(s) notice period</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="reason_for_resignation" class="form-label">Reason for Resignation</label>
                                        <textarea class="form-control" id="reason_for_resignation" name="reason_for_resignation" rows="3">{{ old('reason_for_resignation') }}</textarea>
                                    </div>
                                </div>
                                <h5 class="mt-4 mb-3">No Dues from departments</h5>
                                <div class="table-responsive border-bottom">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th>Name</th>
                                                <th>Designation</th>
                                                <th>Remark</th>
                                                <th>File</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $departments = [
                                            'Service Department',
                                            'Parts Department',
                                            'Sales Department',
                                            'Accounts Department',
                                            'HR Department',
                                            'Work Shop',
                                            'Business Head',
                                            'General Manager'
                                            ];
                                            @endphp
                                            @foreach ($departments as $index => $dept)
                                            <tr>
                                                <td>
                                                    {{ $dept }}
                                                    <input type="hidden" name="remarks[{{ $index }}][department]" value="{{ $dept }}">
                                                </td>
                                                <td><select class="form-control manager-select" name="remarks[{{ $index }}][manager_name]" id="remark_name_{{ $index }}" disabled></select></td>
                                                <td><input type="text" class="form-control manager-designation" name="remarks[{{ $index }}][designation]" id="remark_designation_{{ $index }}"></td>
                                                <td><input type="text" class="form-control" name="remarks[{{ $index }}][remark]"></td>
                                                <td><input type="file" class="form-control" name="remarks[{{ $index }}][file]"></td>

                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div id="formSuccessMessage" class="alert alert-success d-none mt-3"></div>
                                <div class="mt-3 pt-2">
                                    <button type="submit" class="btn btn-primary">Submit Settlement</button>
                                    <button type="button" id="resetCreateForm" class="btn btn-warning">Reset</button>
                                    <button type="button" id="exportPdfButton" class="btn btn-info" style="display: none;">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                        <span class="button-text">Export to PDF</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable for View Settlements tab
        $('#settlements-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('settlements.data') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'employee_code',
                    name: 'employee_code'
                },
                {
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'department',
                    name: 'department'
                },
                {
                    data: 'date_of_joining',
                    name: 'date_of_joining'
                },
                {
                    data: 'date_of_resignation',
                    name: 'date_of_resignation'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Handle tab switching to reload datatable if needed
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            if ($(e.target).attr('id') === 'view-settlements-tab') {
                $('#settlements-datatable').DataTable().ajax.reload();
            }
        });

        // Initialize the main employee search
        $('#employee_code').select2({
            placeholder: 'Search for an employee or enter a new code',
            tags: true,
            ajax: {
                url: "{{ route('employees.searchEmployee') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) {
                    return data.text;
                }
                return data.text;
            },
            templateSelection: function(data) {
                return data.id || data.text;
            }
        }).on('select2:select', function(e) {
            var data = e.params.data;
            if (data.name) {
                $('#employee_name').val(data.name);
                if (data.dob) {
                    const dob = new Date(data.dob);
                    const today = new Date();
                    let age = today.getFullYear() - dob.getFullYear();
                    const m = today.getMonth() - dob.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                        age--;
                    }
                    $('#age').val(age);
                } else {
                    $('#age').val('');
                }
                $('#department').val(data.department ? data.department.name : '');
                $('#head_office_branch').val(data.branch ? data.branch : '');
                $('#designation').val(data.designation || '');
                $('#date_of_joining').val(data.joining_date || '');
            }

            // Fetch and populate department managers
            $.ajax({
                url: "{{ route('employees.getDepartmentManagers') }}",
                type: 'GET',
                success: function(managers) {
                    var departments = @json($departments);
                    departments.forEach(function(deptName, index) {
                        var select = $('#remark_name_' + index);
                        var designationInput = $('#remark_designation_' + index);
                        var departmentManagers = managers[deptName];

                        select.empty(); // Clear previous options

                        if (departmentManagers && departmentManagers.length > 0) {
                            // If managers are found, populate the select with them
                            departmentManagers.forEach(function(manager) {
                                var option = new Option(manager.name, manager.name, false, false);
                                select.append(option);
                            });
                            select.val(departmentManagers[0].name).trigger('change'); // Select the first one by default
                            designationInput.val(departmentManagers[0].designation).prop('readonly', true);
                            select.prop('disabled', false); // Enable for selection if multiple
                            select.select2({
                                placeholder: 'Select a manager',
                                minimumResultsForSearch: Infinity // Hide search box if only one option
                            }).on('change', function() {
                                var selectedName = $(this).val();
                                var selectedManager = departmentManagers.find(m => m.name === selectedName);
                                designationInput.val(selectedManager ? selectedManager.designation : '').prop('readonly', true);
                            });
                        } else {
                            // If no managers are found, enable for manual search
                            select.prop('disabled', false);
                            select.val(null).trigger('change');
                            designationInput.val('').prop('readonly', false);
                            select.select2({
                                placeholder: 'Search for an employee',
                                ajax: {
                                    url: "{{ route('employees.searchEmployee') }}",
                                    dataType: 'json',
                                    delay: 250,
                                    data: function(params) {
                                        return {
                                            id: employee.name,
                                            text: employee.text,
                                            designation: employee.designation
                                        }


                                    }
                                },
                                cache: true
                            }).on('select2:select', function(e) {
                                var data = e.params.data;
                                $(this).closest('tr').find('.manager-designation').val(data.designation || '').prop('readonly', true);
                            }).on('select2:unselect', function(e) {
                                $(this).closest('tr').find('.manager-designation').val('').prop('readonly', false);
                            });
                        }
                    });
                }
            });
        }).on('select2:unselect', function(e) {
            // Clear main form fields
            $('#employee_name').val('');
            $('#age').val('');
            $('#department').val('');
            $('#head_office_branch').val('');
            $('#designation').val('');
            $('#date_of_joining').val('');
            // Clear manager fields and make them disabled again
            @json($departments).forEach(function(deptName, index) {
                var select = $('#remark_name_' + index);
                var designationInput = $('#remark_designation_' + index);
                select.empty().prop('disabled', true).val(null).trigger('change');
                if (select.data('select2')) { // Check if Select2 is initialized
                    select.select2('destroy');
                }
                designationInput.val('').prop('readonly', true);
            });
        });

        // Handle form submission via AJAX
        $('#createSettlementForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            var formData = $(this).serialize(); // Serialize form data

            // Clear previous messages
            $('#formSuccessMessage').addClass('d-none').text('');
            $('.alert-danger').remove(); // Remove any previous error alerts

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#formSuccessMessage').removeClass('d-none').text(response.message);
                        // Do not reset form fields automatically
                        // Do not switch tabs
                        // Store the settlement ID for PDF export
                        if (response.settlement_id) {
                            $('#exportPdfButton').data('settlement-id', response.settlement_id).prop('disabled', false).show(); // Show and enable
                        } else {
                            $('#exportPdfButton').prop('disabled', true).hide(); // Hide and disable
                        }
                        // Optionally, reload the view settlements datatable in the background
                        $('#settlements-datatable').DataTable().ajax.reload(null, false); // 'null, false' to keep current paging
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorHtml = '<div class="alert alert-danger"><ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value + '</li>';
                    });
                    errorHtml += '</ul></div>';
                    $('#createSettlementForm').prepend(errorHtml); // Display errors at the top of the form
                }
            });
        });

        // Handle Reset button click
        $('#resetCreateForm').on('click', function() {
            $('#createSettlementForm')[0].reset(); // Clear form fields
            // Re-initialize select2 fields
            $('#employee_code').val(null).trigger('change');
            $('.manager-select').val(null).trigger('change');
            // Clear messages
            $('#formSuccessMessage').addClass('d-none').text('');
            $('.alert-danger').remove();
            // Hide and disable export PDF button
            $('#exportPdfButton').prop('disabled', true).hide();
        });
        // Handle Export to PDF button click
        $('#exportPdfButton').on('click', function() {
            var $this = $(this);
            var settlementId = $this.data('settlement-id');

            if (settlementId) {
                // Show loader and disable button
                $this.prop('disabled', true);
                $this.find('.spinner-border').show();
                $this.find('.button-text').hide();

                // Navigate to PDF export URL
                window.location.href = "{{ url('settlements') }}/" + settlementId + "/export-pdf";

                // Note: Since window.location.href navigates away,
                // the spinner will disappear with the page unload.
                // If it were an AJAX call, we'd re-enable/hide spinner in success/error.
            } else {
                alert('Please submit the settlement first to enable PDF export.');
            }
        });


        // Handle delete settlement button click
        $(document).on('click', '.delete-settlement-btn', function() {
            var settlementId = $(this).data('id');
            var settlementName = $(this).data('name');
            var url = '/settlements/' + settlementId;

            // Update confirmation modal for deletion
            $('#confirmationModalBody').text('Are you sure you want to delete the settlement for ' + settlementName + '?');

            // Set action for confirm button
            $('#confirmActionButton').off('click').on('click', function() {
                $.ajax({
                    url: url,
                    method: 'POST', // Use POST with _method field for DELETE
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#settlements-datatable').DataTable().ajax.reload();
                            showToast(response.message, 'success');
                        }
                        $('#confirmationModal').modal('hide');
                    },
                    error: function(xhr) {
                        showToast('Error deleting settlement.', 'danger');
                        $('#confirmationModal').modal('hide');
                    }
                });
            });

            // Show the confirmation modal
            $('#confirmationModal').modal('show');
        });

        // Handle Edit Settlement button click to populate modal
        $(document).on('click', '.edit-settlement-btn', function() {
            var settlementId = $(this).data('id');
            $('#edit_settlement_id').val(settlementId); // Set hidden ID

            // Clear previous messages
            $('#editFormSuccessMessage').addClass('d-none').text('');
            $('#editFormErrorMessage').addClass('d-none').text('');

            $.ajax({
                url: '/settlements/' + settlementId + '/edit-data',
                method: 'GET',
                success: function(data) {
                    $('#edit_employee_code').val(data.employee_code).trigger('change');
                    $('#edit_employee_name').val(data.employee_name);
                    $('#edit_age').val(data.age);
                    $('#edit_department').val(data.department);
                    $('#edit_head_office_branch').val(data.head_office_branch);
                    $('#edit_designation').val(data.designation);
                    $('#edit_date_of_joining').val(data.date_of_joining);
                    $('#edit_date_of_resignation').val(data.date_of_resignation).trigger('change');
                    $('#edit_reason_for_resignation').val(data.reason_for_resignation);

                    // Initialize Select2 for employee_code in edit modal
                    $('#edit_employee_code').select2({
                        placeholder: 'Search for an employee or enter a new code',
                        tags: true,
                        ajax: {
                            url: "{{ route('employees.searchEmployee') }}",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data.results
                                };
                            },
                            cache: true
                        },
                        templateResult: function(data) {
                            if (data.loading) {
                                return data.text;
                            }
                            return data.text;
                        },
                        templateSelection: function(data) {
                            return data.id || data.text;
                        }
                    }).on('select2:select', function(e) {
                        var data = e.params.data;
                        if (data.name) {
                            // Populate other fields based on selected employee
                            $('#edit_employee_name').val(data.name);
                            if (data.dob) {
                                const dob = new Date(data.dob);
                                const today = new Date();
                                let age = today.getFullYear() - dob.getFullYear();
                                const m = today.getMonth() - dob.getMonth();
                                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                                    age--;
                                }
                                $('#edit_age').val(age);
                            } else {
                                $('#edit_age').val('');
                            }
                            $('#edit_department').val(data.department ? data.department.name : '');
                            $('#edit_head_office_branch').val(data.branch ? data.branch : '');
                            $('#edit_designation').val(data.designation || '');
                            $('#edit_date_of_joining').val(data.joining_date || '');
                        }
                    }).on('select2:unselect', function(e) {
                        // Clear fields if employee is unselected
                        $('#edit_employee_name').val('');
                        $('#edit_age').val('');
                        $('#edit_department').val('');
                        $('#edit_head_office_branch').val('');
                        $('#edit_designation').val('');
                        $('#edit_date_of_joining').val('');
                    });

                    // If employee_code has a value, ensure it's selected in Select2
                    if (data.employee_code) {
                        var newOption = new Option(data.employee_code, data.employee_code, true, true);
                        $('#edit_employee_code').append(newOption).trigger('change');
                    }

                    $('#editSettlementModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error fetching settlement data:', xhr);
                    showToast('Error fetching settlement data.', 'danger');
                }
            });
        });

        // Handle Edit Settlement form submission via AJAX (without additional confirmation)
        $('#editSettlementForm').on('submit', function(e) {
            e.preventDefault();

            var settlementId = $('#edit_settlement_id').val();
            var formData = $(this).serialize();

            // Clear previous messages
            $('#editFormSuccessMessage').addClass('d-none').text('');
            $('#editFormErrorMessage').addClass('d-none').text('');

            $.ajax({
                url: '/settlements/' + settlementId,
                method: 'POST', // Use POST for Laravel's @method('PUT')
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editFormSuccessMessage').removeClass('d-none').text(response.message);
                        $('#settlements-datatable').DataTable().ajax.reload(); // Reload datatable
                        setTimeout(function() {
                            $('#editSettlementModal').modal('hide');
                        }, 1500);
                        showToast(response.message, 'success');
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorHtml = '<ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value + '</li>';
                    });
                    errorHtml += '</ul>';
                    $('#editFormErrorMessage').removeClass('d-none').html(errorHtml);
                    showToast('Error updating settlement.', 'danger');
                }
            });
        });
        // Calculate Notice Period End Date for Create Form
        $('#date_of_resignation').on('change', function() {
            const resignationDate = $(this).val();
            const noticePeriodMonths = parseInt({{ $noticePeriodDuration ?? 0 }});

            if (resignationDate && noticePeriodMonths > 0) {
                const date = new Date(resignationDate);
                date.setMonth(date.getMonth() + noticePeriodMonths);

                // Format to YYYY-MM-DD
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                $('#notice_period_end_date').val(`${year}-${month}-${day}`);
            } else {
                $('#notice_period_end_date').val('');
            }
        });

        // Add similar calculation for Edit Modal
        $(document).on('change', '#edit_date_of_resignation', function() {
            const resignationDate = $(this).val();
            const noticePeriodMonths = parseInt({{ $noticePeriodDuration ?? 0 }});
            
            // Check if we need to show/add an end date field in edit modal too
            if (resignationDate && noticePeriodMonths > 0) {
                const date = new Date(resignationDate);
                date.setMonth(date.getMonth() + noticePeriodMonths);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                
                // If there's a field for it, update it. For now, let's just make sure create works perfectly.
                $('#edit_notice_period_end_date').val(`${year}-${month}-${day}`);
            }
        });
    });
</script>
@endpush
@endsection

@section('modal')
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmationModalBody">
                <!-- Confirmation message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmActionButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Settlement Modal -->
<div class="modal fade" id="editSettlementModal" tabindex="-1" aria-labelledby="editSettlementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSettlementModalLabel">Edit Settlement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSettlementForm" method="POST">
                    @csrf
                    @method('PUT') {{-- Use PUT method for updates --}}
                    <input type="hidden" id="edit_settlement_id" name="settlement_id">
                    <div id="editFormSuccessMessage" class="alert alert-success d-none"></div>
                    <div id="editFormErrorMessage" class="alert alert-danger d-none"></div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_employee_code" class="form-label">Employee Code</label>
                            <select class="form-control" id="edit_employee_code" name="employee_code" required></select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_employee_name" class="form-label">Name of Employee</label>
                            <input type="text" class="form-control" id="edit_employee_name" name="employee_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="edit_age" name="age">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="edit_department" name="department">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_head_office_branch" class="form-label">Head Office/Branch</label>
                            <input type="text" class="form-control" id="edit_head_office_branch" name="head_office_branch">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_designation" class="form-label">Designation</label>
                            <input type="text" class="form-control" id="edit_designation" name="designation">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_date_of_joining" class="form-label">Date of Joining</label>
                            <input type="date" class="form-control" id="edit_date_of_joining" name="date_of_joining" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_date_of_resignation" class="form-label">Date of Resignation</label>
                            <input type="date" class="form-control" id="edit_date_of_resignation" name="date_of_resignation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_notice_period_end_date" class="form-label">Notice Period End Date (Calculated)</label>
                            <input type="date" class="form-control" id="edit_notice_period_end_date" readonly>
                            <small class="text-muted">Based on {{ $noticePeriodDuration ?? 0 }} month(s) notice period</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_reason_for_resignation" class="form-label">Reason for Resignation</label>
                            <textarea class="form-control" id="edit_reason_for_resignation" name="reason_for_resignation" rows="3"></textarea>
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
</div>

<!-- Export Settlement Modal -->
<div class="modal fade" id="exportSettlementModal" tabindex="-1" role="dialog" aria-labelledby="exportSettlementModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportSettlementModalLabel">Export Settlements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('settlements.export') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_from_date" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="export_from_date" name="from_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_to_date" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="export_to_date" name="to_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Export Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection