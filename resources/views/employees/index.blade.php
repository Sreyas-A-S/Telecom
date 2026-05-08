@extends('layouts.admin')

@section('title', 'Employees')

@push('styles')
<style>
    .td-employee {
        white-space: normal !important;
        min-width: 200px;
        /* Adjust as needed */
    }

    .td-designation {
        white-space: normal !important;
        min-width: 150px;
        /* Adjust as needed */
    }

    #recentImportsTable tbody td,
    #recentImportsTable thead th {
        text-align: left !important;
    }
</style>
@endpush

@foreach ($zones as $zone)
{{-- {{ $zone->name }} --}}
@endforeach

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Employees List</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Employees</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
@php

foreach ($zones as $key => $value) {
// echo $value->name;
}

@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs d-flex" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="view-tab" data-bs-toggle="tab" data-bs-target="#view"
                        type="button" role="tab" aria-controls="view" aria-selected="true">View
                        Employees</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create"
                        type="button" role="tab" aria-controls="create" aria-selected="false">Create
                        Employee</button>


                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import"
                        type="button" role="tab" aria-controls="import" aria-selected="false">Import
                        Employees</button>
                </li>
            </ul>
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        @if(!checkMenu(Session::get('role_id'), 4, 'read'))
                        <div class="alert alert-danger" role="alert">
                            You do not have permission to view the employees.
                        </div>
                        @else
                        <div class="tab-pane fade show active" id="view" role="tabpanel"
                            aria-labelledby="view-tab">
                            <div id="alert-container" class="mx-2"></div> {{-- Placeholder for alerts --}}

                            <div class="d-flex justify-content-end mb-3">
                                <a href="{{ route('employees.export') }}" class="btn btn-success">Export to Excel</a>
                            </div>
                            <div class="table-responsive">
                                <table class="display" id="employees-table">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Employee</th>
                                            <th>Designation & Role</th>
                                            <th>Dealership & Department</th>
                                            <th>Zone</th>
                                            <th>Contact</th>
                                            <th>Joined Date & Report To:</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded via AJAX --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                            @if(!checkMenu(Session::get('role_id'), 4, 'create'))
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to create a new employee.
                            </div>
                            @else
                            <form id="createEmployeeForm" enctype="multipart/form-data" class="theme-form">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Employee Details</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3 text-center">
                                                        <div class="profile-pic-upload-area" id="profile-dp">
                                                            <div class="position-relative d-inline-block">
                                                                <img id="create-profile-pic-preview"
                                                                    src="{{ asset('admin/assets/images/avtar/4.jpg') }}"
                                                                    alt="Profile Picture"
                                                                    class="img-fluid rounded-circle"
                                                                    style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #ddd; cursor: pointer;">
                                                                <div class="icon-wrapper position-absolute top-100 start-100 translate-middle bg-primary rounded-circle p-2 text-white"
                                                                    style="margin-top: -25px; margin-left: -25px; cursor: pointer;">
                                                                    <i class="icofont icofont-pencil-alt-5"></i>
                                                                </div>
                                                                <input class="form-control" type="file"
                                                                    id="createEmployeeProfilePicInput"
                                                                    accept="image/*" style="display: none;">
                                                                <input type="hidden"
                                                                    id="createEmployeeCroppedImage"
                                                                    name="profile_pic">
                                                            </div>
                                                        </div>
                                                        <p class="mt-2 text-primary" style="cursor: pointer;">
                                                            Profile Picture</p>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeName"
                                                                    class="form-label">Name
                                                                    <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeName" name="name"
                                                                    required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeEmail"
                                                                    class="form-label">Email
                                                                    <span class="text-danger">*</span></label>
                                                                <input type="email" class="form-control"
                                                                    id="createEmployeeEmail" name="email"
                                                                    required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeId"
                                                                    class="form-label">Employee ID
                                                                    <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeId" name="employee_id"
                                                                    required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeePassword"
                                                                    class="form-label">Password
                                                                    <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <input type="password" class="form-control"
                                                                        id="createEmployeePassword"
                                                                        name="password" required>
                                                                    <span class="input-group-text toggle-password"
                                                                        data-target="#createEmployeePassword"><i
                                                                            class="fa fa-eye"></i></span>
                                                                    <div class="invalid-feedback"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeePasswordConfirmation"
                                                                    class="form-label">Confirm Password
                                                                    <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <input type="password" class="form-control"
                                                                        id="createEmployeePasswordConfirmation"
                                                                        name="password_confirmation" required>
                                                                    <span class="input-group-text toggle-password"
                                                                        data-target="#createEmployeePasswordConfirmation"><i
                                                                            class="fa fa-eye"></i></span>
                                                                    <div class="invalid-feedback"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeMobile"
                                                                    class="form-label">Mobile</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeMobile" name="mobile">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeGender"
                                                                    class="form-label">Gender</label>
                                                                <select class="form-select"
                                                                    id="createEmployeeGender" name="gender">
                                                                    <option value="">Select Gender</option>
                                                                    <option value="Male">Male</option>
                                                                    <option value="Female">Female</option>
                                                                    <option value="Other">Other</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeDob"
                                                                    class="form-label">Date of
                                                                    Birth</label>
                                                                <div class="input-group">
                                                                    <input type="date" class="form-control"
                                                                        id="createEmployeeDob" name="dob"
                                                                        required>
                                                                    <div class="invalid-feedback"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeJoiningDate"
                                                                    class="form-label">Joining Date</label>
                                                                <div class="input-group">
                                                                    <input type="date" class="form-control"
                                                                        id="createEmployeeJoiningDate"
                                                                        name="joining_date" required>
                                                                    <div class="invalid-feedback"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeMaritalStatus"
                                                                    class="form-label">Marital Status</label>
                                                                <select class="form-select"
                                                                    id="createEmployeeMaritalStatus"
                                                                    name="marital_status">
                                                                    <option value="">Select Marital Status</option>
                                                                    <option value="Single">Single</option>
                                                                    <option value="Married">Married</option>
                                                                    <option value="Divorced">Divorced</option>
                                                                    <option value="Widowed">Widowed</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeEmergencyContact"
                                                                    class="form-label">Emergency Contact</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeEmergencyContact"
                                                                    name="emergency_contact">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeFatherName"
                                                                    class="form-label">Father's Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeFatherName"
                                                                    name="father_name">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeMotherName"
                                                                    class="form-label">Mother's Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeMotherName"
                                                                    name="mother_name">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeSpouseName"
                                                                    class="form-label">Spouse's Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeSpouseName"
                                                                    name="spouse_name">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeShirtSize"
                                                                    class="form-label">Shirt Size</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeShirtSize"
                                                                    name="shirt_size">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeTshirtSize"
                                                                    class="form-label">T-shirt Size</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeTshirtSize"
                                                                    name="tshirt_size">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeBloodGroup"
                                                                    class="form-label">Blood Group</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeBloodGroup"
                                                                    name="blood_group">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeBankName"
                                                                    class="form-label">Bank Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeBankName"
                                                                    name="bank_name">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeAccountNumber"
                                                                    class="form-label">Account Number</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeAccountNumber"
                                                                    name="account_number">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeIfscCode"
                                                                    class="form-label">IFSC Code</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeIfscCode"
                                                                    name="ifsc_code">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeePfNo"
                                                                    class="form-label">PF No</label>
                                                                <input type="text" class="form-control"
                                                                    name="pf_no">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeEsiNo"
                                                                    class="form-label">ESI No</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeEsiNo" name="esi_no">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeLwfNo"
                                                                    class="form-label">LWF No</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeLwfNo" name="lwf_no">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeeAadharNo"
                                                                    class="form-label">Aadhar No</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeeAadharNo"
                                                                    name="aadhar_no">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="createEmployeePanNo"
                                                                    class="form-label">PAN Number</label>
                                                                <input type="text" class="form-control"
                                                                    id="createEmployeePanNo" name="pan_no">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label for="createEmployeeAddress"
                                                            class="form-label">Address</label>
                                                        <textarea class="form-control" id="createEmployeeAddress" name="address" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Work Details</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createEmployeeDesignation"
                                                            class="form-label">Designation</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"
                                                                id="createEmployeeDesignation" name="designation"
                                                                list="designationsDatalist" autocomplete="off" required>
                                                            <datalist id="designationsDatalist">
                                                                @foreach ($designations as $designation)
                                                                <option value="{{ $designation->designation }}">
                                                                    @endforeach
                                                            </datalist>
                                                            <div class="invalid-feedback"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createEmployeeDepartmentId"
                                                            class="form-label">Department</label>
                                                        <select class="form-select"
                                                            id="createEmployeeDepartmentId" name="department_id">
                                                            <option value="">All</option>
                                                            @foreach ($departments as $department)
                                                            <option value="{{ $department->id }}">
                                                                {{ $department->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createEmployeeRoleDisplay" class="form-label">Role</label>
                                                        <input type="text" class="form-control"
                                                            id="createEmployeeRoleDisplay" readonly disabled
                                                            placeholder="Auto-generated from Designation">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createEmployeeDealershipId"
                                                            class="form-label">Dealership</label>
                                                        <select class="form-select"
                                                            id="createEmployeeDealershipId" name="dealership_id">
                                                            <option value="">All</option>
                                                            {{-- populate only the dealership where brand is true --}}
                                                            @foreach ($dealerships as $dealership)
                                                            @if($dealership->brand)
                                                            <option value="{{ $dealership->id }}">
                                                                {{ ucwords(str_replace('_','', $dealership->name)) }}
                                                            </option>
                                                            @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createEmployeeZoneId"
                                                            class="form-label">Zone</label>
                                                        <select class="form-select" id="createEmployeeZoneId"
                                                            name="zone_id">
                                                            <option value="">All</option>

                                                            @foreach ($zones as $zone)
                                                            <option value="{{ $zone->id }}">
                                                                {{ $zone->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createEmployeeReportingTo"
                                                            class="form-label">Reporting To</label>
                                                        <select class="form-select" id="createEmployeeReportingTo"
                                                            name="reporting_to">
                                                            <option value="">Select Manager</option>
                                                            {{-- Employees will be loaded via AJAX --}}
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createEmployeeIsAgent" class="form-label">Is
                                                            Agent</label>
                                                        <div class="form-check">
                                                            <input type="hidden" name="is_broker"
                                                                value="0"> <!-- Hidden field for unchecked -->
                                                            <input class="form-check-input" type="checkbox"
                                                                id="createEmployeeIsAgent" name="is_broker"
                                                                value="1">
                                                            <label class="form-check-label"
                                                                for="createEmployeeIsAgent">
                                                                Yes
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="createEmployeeBranch"
                                                            class="form-label">Branch</label>
                                                        <input type="text" class="form-control"
                                                            id="createEmployeeBranch" name="branch">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">Save Employee</button>
                                    </div>
                                </div>
                            </form>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
                            @if(!checkMenu(Session::get('role_id'), 4, 'create'))
                            <div class="alert alert-danger" role="alert">
                                You do not have permission to import employees.
                            </div>
                            @else
                            <p class="mt-3">Download a sample Excel template: <button type="button"
                                    id="downloadTemplateBtn" class="btn btn-sm btn-outline-primary">Download
                                    Template</button></p>
                            <form id="importEmployeeForm" method="POST" enctype="multipart/form-data" class="theme-form">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Import Employees</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label for="excel_file" class="form-label">Select Excel
                                                            File</label>
                                                        <input type="file" class="form-control"
                                                            id="excel_file" name="excel_file"
                                                            accept=".xlsx, .xls" required>

                                                        <div id="import-status"></div>
                                                        <div id="import-errors" class="text-danger"></div>
                                                        <div id="import-results" class="mt-3"></div>
                                                        <button type="button" id="closeImportResults" class="btn btn-sm btn-outline-secondary mt-2" style="display: none;">Close Results</button>
                                                        <div class="progress mt-3" style="display: none;">
                                                            <div id="import-progress-bar" class="progress-bar"
                                                                role="progressbar" style="width: 0%;"
                                                                aria-valuenow="0" aria-valuemin="0"
                                                                aria-valuemax="100">0%</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="submit" id="importEmployeeButton"
                                            class="btn btn-primary">Import Employees</button>
                                        <span id="import-spinner" class="spinner-border spinner-border-sm"
                                            role="status" aria-hidden="true" style="display: none;"></span>
                            </form>

                            <div id="recent-imports" class="mt-5">
                                <h5>Recent Imports</h5>
                                <table id="recentImportsTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Import Date</th>
                                            <th>Summary</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-imports-body">
                                        {{-- Recent imports will be loaded here --}}
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
</div>
</div>
</div>



<!-- Undo Import Confirmation Modal -->
<div class="modal fade" id="undoImportModal" tabindex="-1" role="dialog" aria-labelledby="undoImportModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="undoImportModalLabel">Confirm Undo Import</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to undo this import? This will delete all employees from this batch.
                <input type="hidden" id="undoImportId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmUndoImport">Undo Import</button>
            </div>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog"
    aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEmployeeModalLabel">View Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-3 text-center mb-4">
                        <img id="viewEmployeeProfilePic" src="" alt="Profile Picture" class="img-fluid rounded-circle mb-3"
                            style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #ddd;">
                        <h4 id="viewEmployeeName" class="mb-1"></h4>
                        <p class="text-muted" id="viewEmployeeDesignation"></p>
                    </div>
                    <div class="col-lg-9">
                        <ul class="nav nav-tabs" id="viewEmployeeTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab" aria-controls="basic-info" aria-selected="true">Basic Info</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal-info" type="button" role="tab" aria-controls="personal-info" aria-selected="false">Personal & Contact</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial-info" type="button" role="tab" aria-controls="financial-info" aria-selected="false">Financial & Statutory</button>
                            </li>
                        </ul>
                        <div class="tab-content p-3" id="viewEmployeeTabContent">
                            <!-- Basic Info Tab -->
                            <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="basic-tab">
                                <div class="row">
                                    <div class="col-md-6 mb-2"><strong>Email:</strong> <a href="mailto:" id="viewEmployeeEmail"></a></div>
                                    <div class="col-md-6 mb-2"><strong>Employee ID:</strong> <span class="badge bg-primary" id="viewEmployeeId"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Mobile:</strong> <a href="tel:" id="viewEmployeeMobile"></a></div>
                                    <div class="col-md-6 mb-2"><strong>Gender:</strong> <span class="badge bg-info" id="viewEmployeeGender"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Date of Birth:</strong> <span id="viewEmployeeDob"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Joining Date:</strong> <span id="viewEmployeeJoiningDate"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Department:</strong> <span class="badge bg-warning" id="viewEmployeeDepartment"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Role:</strong> <span class="badge bg-success" id="viewEmployeeRole"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Dealership:</strong> <span class="badge bg-primary" id="viewEmployeeDealership"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Zone:</strong> <span class="badge bg-info" id="viewEmployeeZone"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Reporting To:</strong> <span class="badge bg-secondary" id="viewEmployeeReportingTo"></span></div>
                                    <div class="col-md-12 mb-3"><strong>Address:</strong> <span id="viewEmployeeAddress"></span></div>
                                </div>
                            </div>

                            <!-- Personal & Contact Tab -->
                            <div class="tab-pane fade" id="personal-info" role="tabpanel" aria-labelledby="personal-tab">
                                <h6 class="mb-3 border-bottom pb-2">Family & Personal</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2"><strong>Marital Status:</strong> <span id="viewEmployeeMaritalStatus"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Father's Name:</strong> <span id="viewEmployeeFatherName"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Mother's Name:</strong> <span id="viewEmployeeMotherName"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Spouse's Name:</strong> <span id="viewEmployeeSpouseName"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Blood Group:</strong> <span id="viewEmployeeBloodGroup"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Shirt Size:</strong> <span id="viewEmployeeShirtSize"></span></div>
                                    <div class="col-md-6 mb-2"><strong>T-Shirt Size:</strong> <span id="viewEmployeeTshirtSize"></span></div>
                                </div>
                                <h6 class="mb-3 border-bottom pb-2 mt-3">Emergency Contact</h6>
                                <div class="row">
                                    <div class="col-md-12 mb-2"><strong>Contact Number:</strong> <span id="viewEmployeeEmergencyContact"></span></div>
                                </div>
                            </div>

                            <!-- Financial & Statutory Tab -->
                            <div class="tab-pane fade" id="financial-info" role="tabpanel" aria-labelledby="financial-tab">
                                <h6 class="mb-3 border-bottom pb-2">Bank Details</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2"><strong>Bank Name:</strong> <span id="viewEmployeeBankName"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Account Number:</strong> <span id="viewEmployeeAccountNumber"></span></div>
                                    <div class="col-md-6 mb-2"><strong>IFSC Code:</strong> <span id="viewEmployeeIfscCode"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Branch:</strong> <span id="viewEmployeeBranch"></span></div>
                                </div>
                                <h6 class="mb-3 border-bottom pb-2 mt-3">Statutory Details</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2"><strong>PF No:</strong> <span id="viewEmployeePfNo"></span></div>
                                    <div class="col-md-6 mb-2"><strong>ESI No:</strong> <span id="viewEmployeeEsiNo"></span></div>
                                    <div class="col-md-6 mb-2"><strong>LWF No:</strong> <span id="viewEmployeeLwfNo"></span></div>
                                    <div class="col-md-6 mb-2"><strong>Aadhar No:</strong> <span id="viewEmployeeAadharNo"></span></div>
                                    <div class="col-md-6 mb-2"><strong>PAN No:</strong> <span id="viewEmployeePanNo"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" role="dialog"
    aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(!checkMenu(Session::get('role_id'), 4, 'update'))
            <div class="alert alert-danger" role="alert">
                You do not have permission to update employee details.
            </div>
            @else
            <form id="editEmployeeForm" enctype="multipart/form-data" class="theme-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="editEmployeeId" name="id">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Employee Details</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <div class="profile-pic-upload-area" id="edit-profile-dp">
                                                <div class="position-relative d-inline-block">
                                                    <img id="edit-profile-pic-preview"
                                                        src="{{ asset('admin/assets/images/avtar/4.jpg') }}"
                                                        alt="Profile Picture" class="img-fluid rounded-circle"
                                                        style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #ddd; cursor: pointer;">
                                                    <div class="icon-wrapper position-absolute top-100 start-100 translate-middle bg-primary rounded-circle p-2 text-white"
                                                        style="margin-top: -25px; margin-left: -25px; cursor: pointer;">
                                                        <i class="icofont icofont-pencil-alt-5"></i>
                                                    </div>
                                                    <input class="form-control" type="file"
                                                        id="editEmployeeProfilePicInput" accept="image/*"
                                                        style="display: none;">
                                                    <input type="hidden" id="editEmployeeCroppedImage"
                                                        name="profile_pic">
                                                </div>
                                            </div>
                                            <p class="mt-2 text-primary" style="cursor: pointer;">Profile Picture
                                            </p>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeName" class="form-label">Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeName" name="name" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeEmail" class="form-label">Email <span
                                                            class="text-danger">*</span></label>
                                                    <input type="email" class="form-control"
                                                        id="editEmployeeEmail" name="email" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeUniqueId" class="form-label">Employee
                                                        ID <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeUniqueId" name="employee_id" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeePassword"
                                                        class="form-label">Password</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control"
                                                            id="editEmployeePassword" name="password">
                                                        <span class="input-group-text toggle-password"
                                                            data-target="#editEmployeePassword"><i
                                                                class="fa fa-eye"></i></span>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeePasswordConfirmation"
                                                        class="form-label">Confirm Password</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control"
                                                            id="editEmployeePasswordConfirmation"
                                                            name="password_confirmation">
                                                        <span class="input-group-text toggle-password"
                                                            data-target="#editEmployeePasswordConfirmation"><i
                                                                class="fa fa-eye"></i></span>
                                                        <div class="invalid-feedback"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeMobile"
                                                        class="form-label">Mobile</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeMobile" name="mobile">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeGender"
                                                        class="form-label">Gender</label>
                                                    <select class="form-select" id="editEmployeeGender"
                                                        name="gender">
                                                        <option value="">Select Gender</option>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeDob" class="form-label">Date of
                                                        Birth</label>
                                                    <input type="date" class="form-control"
                                                        id="editEmployeeDob" name="dob">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeJoiningDate"
                                                        class="form-label">Joining Date</label>
                                                    <input type="date" class="form-control"
                                                        id="editEmployeeJoiningDate" name="joining_date">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeStatus" class="form-label">Status</label>
                                                    <select class="form-select" id="editEmployeeStatus" name="status">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeMaritalStatus"
                                                        class="form-label">Marital Status</label>
                                                    <select class="form-select"
                                                        id="editEmployeeMaritalStatus" name="marital_status">
                                                        <option value="">Select Marital Status</option>
                                                        <option value="Single">Single</option>
                                                        <option value="Married">Married</option>
                                                        <option value="Divorced">Divorced</option>
                                                        <option value="Widowed">Widowed</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeEmergencyContact"
                                                        class="form-label">Emergency Contact</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeEmergencyContact"
                                                        name="emergency_contact">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeFatherName"
                                                        class="form-label">Father's Name</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeFatherName"
                                                        name="father_name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeMotherName"
                                                        class="form-label">Mother's Name</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeMotherName"
                                                        name="mother_name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeSpouseName"
                                                        class="form-label">Spouse's Name</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeSpouseName"
                                                        name="spouse_name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeShirtSize"
                                                        class="form-label">Shirt Size</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeShirtSize"
                                                        name="shirt_size">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeTshirtSize"
                                                        class="form-label">T-shirt Size</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeTshirtSize"
                                                        name="tshirt_size">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeBloodGroup"
                                                        class="form-label">Blood Group</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeBloodGroup"
                                                        name="blood_group">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeBankName"
                                                        class="form-label">Bank Name</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeBankName"
                                                        name="bank_name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeAccountNumber"
                                                        class="form-label">Account Number</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeAccountNumber"
                                                        name="account_number">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeIfscCode"
                                                        class="form-label">IFSC Code</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeIfscCode"
                                                        name="ifsc_code">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeePfNo"
                                                        class="form-label">PF No</label>
                                                    <input type="text" class="form-control"
                                                        name="pf_no">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeEsiNo"
                                                        class="form-label">ESI No</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeEsiNo" name="esi_no">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeLwfNo"
                                                        class="form-label">LWF No</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeLwfNo" name="lwf_no">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeeAadharNo"
                                                        class="form-label">Aadhar No</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeeAadharNo"
                                                        name="aadhar_no">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmployeePanNo"
                                                        class="form-label">PAN Number</label>
                                                    <input type="text" class="form-control"
                                                        id="editEmployeePanNo" name="pan_no">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="editEmployeeAddress" class="form-label">Address</label>
                                            <textarea class="form-control" id="editEmployeeAddress" name="address" rows="3" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Work Details</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        {{-- {{ dd($designations) }} --}}
                                        <div class="col-md-6 mb-3">
                                            <label for="editEmployeeDesignation"
                                                class="form-label">Designation</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="editEmployeeDesignation"
                                                    name="designation" list="editDesignationsDatalist" autocomplete="off">
                                                <datalist id="editDesignationsDatalist">
                                                    @foreach ($designations as $designation)
                                                    <option value="{{ $designation->designation }}">
                                                        @endforeach
                                                </datalist>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editEmployeeDepartmentId"
                                                class="form-label">Department</label>
                                            <select class="form-select" id="editEmployeeDepartmentId"
                                                name="department_id">
                                                <option value="">All</option>
                                                @foreach ($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editEmployeeRoleDisplay" class="form-label">Role</label>
                                            <input type="text" class="form-control"
                                                id="editEmployeeRoleDisplay" readonly disabled
                                                placeholder="Auto-generated from Designation">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editEmployeeDealershipId"
                                                class="form-label">Dealership</label>
                                            <select class="form-select" id="editEmployeeDealershipId"
                                                name="dealership_id">
                                                <option value="">All</option>
                                                @foreach ($dealerships as $dealership)
                                                @if(!$dealership->brand)
                                                <?php continue; ?>
                                                @endif
                                                <option value="{{ $dealership->id }}">{{ $dealership->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editEmployeeZoneId" class="form-label">Zone</label>
                                            <select class="form-select" id="editEmployeeZoneId" name="zone_id">
                                                <option value="">All</option>

                                                @foreach ($zones as $zone)
                                                egwyety
                                                <option value="{{ $zone->id }}">{{ $zone->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editEmployeeReportingTo" class="form-label">Reporting
                                                To</label>
                                            <select class="form-select" id="editEmployeeReportingTo"
                                                name="reporting_to">
                                                <option value="">Select Manager</option>
                                                {{-- Employees will be loaded via AJAX --}}
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editEmployeeIsAgent" class="form-label">Is Agent</label>
                                            <div class="form-check">
                                                <input type="hidden" name="is_broker" value="0">
                                                <!-- Hidden field for unchecked -->
                                                <input class="form-check-input" type="checkbox"
                                                    id="editEmployeeIsAgent" name="is_broker" value="1">
                                                <label class="form-check-label" for="editEmployeeIsAgent">
                                                    Yes
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editEmployeeBranch"
                                                class="form-label">Branch</label>
                                            <input type="text" class="form-control"
                                                id="editEmployeeBranch" name="branch">
                                        </div>
                                    </div>
                                </div>
                            </div>
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

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmployeeModalLabel">Delete Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(!checkMenu(Session::get('role_id'), 4, 'update'))
            <div class="alert alert-danger" role="alert">
                You do not have permission to delete employee details.
            </div>
            @else
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteEmployeeName"></strong>?</p>
                <input type="hidden" id="deleteEmployeeId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEmployee">Delete</button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" role="dialog" aria-labelledby="cropperModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropperModalLabel">Crop Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cropperLoader" style="text-align: center; padding: 20px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading image...</p>
                </div>
                <div class="img-container" style="display: none;">
                    <img id="imageToCrop" src="" alt="Picture">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropButton">Crop</button>
            </div>
        </div>
    </div>
</div>
@endsection



@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<!-- DataTables with Bootstrap 5 integration -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    var employeesTable; // Declare globally
    function displayValidationError(elementId, message) {
        var element = $('#' + elementId);
        // Find the feedback element within the same input-group
        var feedbackElement = element.closest('.input-group').find('.invalid-feedback');

        // Ensure the feedback element exists and update its content
        if (feedbackElement.length > 0) {
            feedbackElement.text(message).show();
        } else {
            // Fallback: if for some reason it doesn't exist, create and append it to the input-group
            feedbackElement = $('<div class="invalid-feedback"></div>');
            element.closest('.input-group').append(feedbackElement);
            feedbackElement.text(message).show();
        }
        element.addClass('is-invalid');
    }

    // Helper function to clear validation errors
    function clearValidationError(elementId) {
        var element = $('#' + elementId);
        element.removeClass('is-invalid');
        element.closest('.input-group').find('.invalid-feedback').hide().text('');
    }

    // Generic validation function for a single field
    function validateField(elementId, rules, formType = 'create') {
        var element = $('#' + elementId);
        var value = element.val();
        var isValid = true;
        var errorMessage = '';

        // Clear previous errors
        clearValidationError(elementId);

        // Handle required rule
        if (rules.includes('required')) {
            if (value === null || value.trim() === '') { // Check for null for select, empty string for text
                isValid = false;
                errorMessage = 'This field is required.';
            }
        }

        // Handle email rule
        if (isValid && rules.includes('email') && value.trim() !== '' && !/^[^S@]+@[^S@]+\.[^S@]+$/.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        }

        // Handle min length rule (e.g., min:8)
        var minMatch = rules.find(rule => rule.startsWith('min:'));
        if (isValid && minMatch) {
            var minLength = parseInt(minMatch.split(':')[1]);
            if (value.length < minLength) {
                isValid = false;
                errorMessage = `Must be at least ${minLength} characters.`;
            }
        }

        // Handle max length rule (e.g., max:255)
        var maxMatch = rules.find(rule => rule.startsWith('max:'));
        if (isValid && maxMatch) {
            var maxLength = parseInt(maxMatch.split(':')[1]);
            if (value.length > maxLength) {
                isValid = false;
                errorMessage = `Cannot exceed ${maxLength} characters.`;
            }
        }

        // Handle confirmed rule (for password confirmation)
        if (rules.includes('confirmed')) {
            var passwordFieldId = elementId.replace('Confirmation', ''); // Assuming naming convention
            var password = $('#' + passwordFieldId).val();
            if (value.trim() !== '' && value !== password) {
                isValid = false;
                errorMessage = 'Passwords do not match.';
            }
        }

        // Handle date validation
        if (isValid && rules.includes('date') && value.trim() !== '') {
            var dateValue = new Date(value);
            if (isNaN(dateValue.getTime())) {
                isValid = false;
                errorMessage = 'Please enter a valid date.';
            }
        }

        // Handle past_date rule (for DOB)
        if (isValid && rules.includes('past_date') && value.trim() !== '') {
            var dobDate = new Date(value);
            var today = new Date();
            today.setHours(0, 0, 0, 0); // Normalize today's date to compare only date part
            if (dobDate >= today) {
                isValid = false;
                errorMessage = 'Date of Birth must be in the past.';
            }
        }

        // Handle not_future_date rule (for Joining Date)
        if (isValid && rules.includes('not_future_date') && value.trim() !== '') {
            var parts = value.split('-');
            // new Date(year, month-1, day)
            var joiningDate = new Date(parts[0], parts[1] - 1, parts[2]);
            var today = new Date();
            today.setHours(0, 0, 0, 0); // Normalize today's date
            if (joiningDate > today) {
                isValid = false;
                errorMessage = 'Joining Date cannot be in the future.';
            }
        }

        // Handle after_dob rule (for Joining Date)
        if (isValid && rules.includes('after_dob') && value.trim() !== '') {
            var joiningDate = new Date(value);
            var dobDate = new Date($('#createEmployeeDob').val());

            if (!isNaN(dobDate.getTime()) && joiningDate < dobDate) {
                isValid = false;
                errorMessage = 'Joining Date cannot be before Date of Birth.';
            }
        }

        if (!isValid) {
            displayValidationError(elementId, errorMessage);
        }

        return isValid;
    }

    function validateForm(formType) {
        var isValid = true;
        var firstErrorField = null;

        // Define fields and their rules for create form
        var createFields = {
            'createEmployeeName': ['required', 'max:255'],
            'createEmployeeEmail': ['required', 'email', 'max:255'],
            'createEmployeeId': ['required'],
            'createEmployeePassword': ['required', 'min:8'],
            'createEmployeePasswordConfirmation': ['required', 'min:8', 'confirmed'],
            'createEmployeeDob': ['required', 'date', 'past_date'],
            'createEmployeeJoiningDate': ['required', 'date', 'not_future_date', 'after_dob'],
            'createEmployeeDesignation': ['required', 'max:255'],
            'createEmployeeAddress': ['required'],
            'createEmployeeMaritalStatus': [],
            'createEmployeeEmergencyContact': [],
            'createEmployeeFatherName': [],
            'createEmployeeMotherName': [],
            'createEmployeeSpouseName': [],
            'createEmployeeShirtSize': [],
            'createEmployeeTshirtSize': [],
            'createEmployeeBloodGroup': [],
            'createEmployeeBankName': [],
            'createEmployeeAccountNumber': [],
            'createEmployeeIfscCode': [],
            'createEmployeePfNo': [],
            'createEmployeeEsiNo': [],
            'createEmployeeLwfNo': [],
            'createEmployeeAadharNo': [],
            'createEmployeePanNo': [],
            'createEmployeeBranch': []
        };

        // Define fields and their rules for edit form
        var editFields = {
            'editEmployeeName': ['required', 'max:255'],
            'editEmployeeEmail': ['required', 'email', 'max:255'],
            'editEmployeeUniqueId': ['required'],
            'editEmployeePassword': ['min:8'], // Password is nullable on edit
            'editEmployeePasswordConfirmation': ['min:8', 'confirmed'],
            'editEmployeeAddress': ['required'],
            'editEmployeeMaritalStatus': [],
            'editEmployeeEmergencyContact': [],
            'editEmployeeFatherName': [],
            'editEmployeeMotherName': [],
            'editEmployeeSpouseName': [],
            'editEmployeeShirtSize': [],
            'editEmployeeTshirtSize': [],
            'editEmployeeBloodGroup': [],
            'editEmployeeBankName': [],
            'editEmployeeAccountNumber': [],
            'editEmployeeIfscCode': [],
            'editEmployeePfNo': [],
            'editEmployeeEsiNo': [],
            'editEmployeeLwfNo': [],
            'editEmployeeAadharNo': [],
            'editEmployeePanNo': [],
            'editEmployeeBranch': []
        };

        var fieldsToValidate = (formType === 'create') ? createFields : editFields;

        for (var fieldId in fieldsToValidate) {
            if (fieldsToValidate.hasOwnProperty(fieldId)) {
                var rules = fieldsToValidate[fieldId];
                // For password fields in edit form, only validate if they are not empty
                if (formType === 'edit' && (fieldId === 'editEmployeePassword' || fieldId ===
                        'editEmployeePasswordConfirmation')) {
                    if ($('#' + fieldId).val() === '') {
                        clearValidationError(fieldId); // Clear any previous errors if field is empty
                        continue; // Skip validation if password field is empty
                    }
                }
                if (!validateField(fieldId, rules, formType)) {
                    isValid = false;
                    if (firstErrorField === null) {
                        firstErrorField = fieldId;
                    }
                }
            }
        }

        if (firstErrorField) {
            $('#' + firstErrorField).focus();
            // Also scroll to it nicely just in case
            $('html, body').animate({
                scrollTop: $('#' + firstErrorField).offset().top - 100
            }, 500);
        }

        return isValid;
    }




    $('#createEmployeeName').on('keyup change blur', function() {
        validateField('createEmployeeName', ['required', 'max:255']);
    });
    $('#createEmployeeEmail').on('keyup change blur', function() {
        validateField('createEmployeeEmail', ['required', 'email', 'max:255']);
    });
    $('#createEmployeeId').on('keyup change blur', function() {
        validateField('createEmployeeId', ['required']);
    });
    $('#createEmployeePassword').on('keyup change blur', function() {
        validateField('createEmployeePassword', ['required', 'min:8']);
        validateField('createEmployeePasswordConfirmation', ['required', 'min:8', 'confirmed']);
    });
    $('#createEmployeePasswordConfirmation').on('keyup change blur', function() {
        validateField('createEmployeePasswordConfirmation', ['required', 'min:8', 'confirmed']);
    });
    $('#createEmployeeMobile').on('keyup change blur', function() {
        validateField('createEmployeeMobile', []);
    });
    $('#createEmployeeGender').on('change blur', function() {
        validateField('createEmployeeGender', []);
    });
    // $('#createEmployeeDob').on('change blur', function() { validateField('createEmployeeDob', ['required', 'date', 'past_date']); }); // Removed premature validation
    // $('#createEmployeeJoiningDate').on('change blur', function() { validateField('createEmployeeJoiningDate', ['required', 'date', 'not_future_date', 'after_dob']); }); // Removed premature validation
    $('#createEmployeeAddress').on('keyup change blur', function() {
        validateField('createEmployeeAddress', []);
    });
    $('#createEmployeeMaritalStatus').on('change blur', function() {
        validateField('createEmployeeMaritalStatus', []);
    });
    $('#createEmployeeEmergencyContact').on('keyup change blur', function() {
        validateField('createEmployeeEmergencyContact', []);
    });
    $('#createEmployeeFatherName').on('keyup change blur', function() {
        validateField('createEmployeeFatherName', []);
    });
    $('#createEmployeeMotherName').on('keyup change blur', function() {
        validateField('createEmployeeMotherName', []);
    });
    $('#createEmployeeSpouseName').on('keyup change blur', function() {
        validateField('createEmployeeSpouseName', []);
    });
    $('#createEmployeeShirtSize').on('keyup change blur', function() {
        validateField('createEmployeeShirtSize', []);
    });
    $('#createEmployeeTshirtSize').on('keyup change blur', function() {
        validateField('createEmployeeTshirtSize', []);
    });
    $('#createEmployeeBloodGroup').on('keyup change blur', function() {
        validateField('createEmployeeBloodGroup', []);
    });
    $('#createEmployeeBankName').on('keyup change blur', function() {
        validateField('createEmployeeBankName', []);
    });
    $('#createEmployeeAccountNumber').on('keyup change blur', function() {
        validateField('createEmployeeAccountNumber', []);
    });
    $('#createEmployeeIfscCode').on('keyup change blur', function() {
        validateField('createEmployeeIfscCode', []);
    });
    $('#createEmployeePfNo').on('keyup change blur', function() {
        validateField('createEmployeePfNo', []);
    });
    $('#createEmployeeEsiNo').on('keyup change blur', function() {
        validateField('createEmployeeEsiNo', []);
    });
    $('#createEmployeeLwfNo').on('keyup change blur', function() {
        validateField('createEmployeeLwfNo', []);
    });
    $('#createEmployeeAadharNo').on('keyup change blur', function() {
        validateField('createEmployeeAadharNo', []);
    });
    $('#createEmployeePanNo').on('keyup change blur', function() {
        validateField('createEmployeePanNo', []);
    });
    $('#createEmployeeDesignation').on('keyup change blur input', function() {
        validateField('createEmployeeDesignation', ['required', 'max:255']);
        $('#createEmployeeRoleDisplay').val($(this).val());
    });
    $('#createEmployeeDepartmentId').on('change blur', function() {
        validateField('createEmployeeDepartmentId', []);
    });
    // Role selection removed as it is auto-generated
    $('#createEmployeeDealershipId').on('change blur', function() {
        validateField('createEmployeeDealershipId', []);
    });
    $('#createEmployeeZoneId').on('change blur', function() {
        validateField('createEmployeeZoneId', []);
    });
    $('#createEmployeeReportingTo').on('change blur', function() {
        validateField('createEmployeeReportingTo', []);
    });

    $('#editEmployeeName').on('keyup change blur', function() {
        validateField('editEmployeeName', ['required', 'max:255'], 'edit');
    });
    $('#editEmployeeEmail').on('keyup change blur', function() {
        validateField('editEmployeeEmail', ['required', 'email', 'max:255'], 'edit');
    });
    $('#editEmployeeUniqueId').on('keyup change blur', function() {
        validateField('editEmployeeUniqueId', ['required'], 'edit');
    });
    $('#editEmployeePassword').on('keyup change blur', function() {
        validateField('editEmployeePassword', ['min:8'], 'edit');
        validateField('editEmployeePasswordConfirmation', ['min:8', 'confirmed'], 'edit');
    });
    $('#editEmployeePasswordConfirmation').on('keyup change blur', function() {
        validateField('editEmployeePasswordConfirmation', ['min:8', 'confirmed'], 'edit');
    });
    $('#editEmployeeMobile').on('keyup change blur', function() {
        validateField('editEmployeeMobile', [], 'edit');
    });
    $('#editEmployeeGender').on('change blur', function() {
        validateField('editEmployeeGender', [], 'edit');
    });
    $('#editEmployeeDob').on('change blur', function() {
        validateField('editEmployeeDob', [], 'edit');
    });
    $('#editEmployeeJoiningDate').on('change blur', function() {
        validateField('editEmployeeJoiningDate', [], 'edit');
    });
    $('#editEmployeeAddress').on('keyup change blur', function() {
        validateField('editEmployeeAddress', [], 'edit');
    });
    $('#editEmployeeDesignation').on('keyup change blur input', function() {
        validateField('editEmployeeDesignation', [], 'edit');
        $('#editEmployeeRoleDisplay').val($(this).val());
    });
    $('#editEmployeeDepartmentId').on('change blur', function() {
        validateField('editEmployeeDepartmentId', [], 'edit');
    });
    $('#editEmployeeDealershipId').on('change blur', function() {
        validateField('editEmployeeDealershipId', [], 'edit');
    });
    $('#editEmployeeZoneId').on('change blur', function() {
        validateField('editEmployeeZoneId', [], 'edit');
    });
    $('#editEmployeeReportingTo').on('change blur', function() {
        validateField('editEmployeeReportingTo', [], 'edit');
    });

    var recentImportsDataTable; // Declare globally

    function loadRecentImports() {
        if (recentImportsDataTable) {
            recentImportsDataTable.ajax.reload(null, false); // null for no reset paging, false for no reset ordering
            recentImportsDataTable.draw();
        }
    }

    $(document).ready(function() {

        loadRecentImports();

        $(document).on('click', '.undo-import', function() {
            var importId = $(this).data('import-id');
            $('#undoImportId').val(importId); // Set the import ID in the hidden input
            var undoImportModal = new bootstrap.Modal(document.getElementById('undoImportModal'));
            undoImportModal.show();
        });

        $('#confirmUndoImport').on('click', function() {
            var importId = $('#undoImportId').val(); // Get the import ID from the hidden input
            var undoImportModal = bootstrap.Modal.getInstance(document.getElementById(
                'undoImportModal'));
            undoImportModal.hide(); // Hide the modal

            $.ajax({
                url: '/employees/import/' + importId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    employeesTable.ajax.reload();
                    loadRecentImports();
                    clearImportResultsDisplay(); // Clear results after undo
                },
                error: function(error) {
                    showToast('Error undoing import.', 'danger');
                }
            });
        });


        // Joining Date Sort Filter functionality
        var savedJoiningDateSortOrder = localStorage.getItem('employeeJoiningDateSortOrder') || 'any';
        $('#joining-date-sort-filter').val(savedJoiningDateSortOrder);

        employeesTable = $('#employees-table').DataTable({
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
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    className: 'btn btn-sm btn-danger text-white',
                    action: function(e, dt, node, config) {
                        window.location.href = "{{ route('employees.export-all.pdf') }}";
                    }
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info text-white'
                }
            ],
            ajax: {
                url: "{{ route('employees.index') }}",
                cache: false,
                data: function(d) {
                    d.joining_date_sort_order = $('#joining-date-sort-filter').val();
                    // Clear default DataTables sorting for this column if custom sort is active
                    // This is important to prevent DataTables from sending its own sort parameters
                    // for this column, which would conflict with our custom sort.
                    if (d.order && d.order.length > 0) {
                        d.order = d.order.filter(function(item) {
                            // Assuming 'joining_date' is the 6th column (0-indexed)
                            return item.column !== 6;
                        });
                    }
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'employee_combined',
                    name: 'employee_combined',
                    orderable: false,
                    searchable: true,
                    className: 'td-employee',
                },
                {
                    data: 'designation_role_combined', // Combined Designation & Role
                    name: 'designation_role_combined',
                    orderable: false,
                    searchable: true,
                    className: 'td-designation',
                },
                {
                    data: 'dealership_department_combined', // Combined Dealership & Department
                    name: 'dealership_department_combined',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'zone.name',
                    name: 'zone.name',
                    orderable: false,
                    render: function(data, type, row) {
                        if (data) {
                            return data;
                        } else {
                            return '<span class="badge bg-secondary">All</span>';
                        }
                    }
                },
                {
                    data: 'contact_combined', // Combined Contact (Mobile & Email)
                    name: 'contact_combined',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'joining_date_combined', // Use combined data
                    name: 'joining_date', // Sort by joining_date
                    orderable: false, // Keep orderable if joining_date is orderable
                    searchable: true, // Keep searchable
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
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
                try {
                    feather.replace();
                } catch (e) {
                    console.error("Error in feather.replace() in drawCallback:", e);
                }
            }
        });



        $('#joining-date-sort-filter').on('change', function() {
            var selectedSortOrder = $(this).val();
            localStorage.setItem('employeeJoiningDateSortOrder', selectedSortOrder);
            employeesTable.ajax.reload();
        });

        // Function to display Bootstrap toasts
        function showToast(message, type) {
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

        // Function to load employees for Reporting To dropdown
        function loadReportingToEmployees(excludeEmployeeId = null, selectedManagerId = null) {
            $.ajax({
                url: '{{ route("employees.index") }}', // Assuming an endpoint to get all employees
                method: 'GET',
                data: {
                    draw: 0,
                    start: 0,
                    length: -1
                }, // Request all data, not paginated
                success: function(response) {
                    var select = $('#createEmployeeReportingTo, #editEmployeeReportingTo');
                    select.empty();
                    select.append('<option value="">Select Manager</option>');
                    $.each(response.data, function(index, employee) {
                        if (employee.id !== excludeEmployeeId) {
                            select.append('<option value="' + employee.user_id + '">' +
                                employee
                                .name + '</option>');
                        }
                    });
                    if (selectedManagerId) {
                        select.val(selectedManagerId);
                    }
                },
                error: function(error) {
                    console.error('Error loading reporting to employees:', error);
                }
            });
        }

        // Function to load zones based on dealership


        // Initial load for Reporting To dropdown
        loadReportingToEmployees();

        // Create Employee
        $('button[data-bs-target="#create"]').on('shown.bs.tab', function(e) {
            loadReportingToEmployees();
            // Clear zone dropdown when create tab is shown
            // $('#createEmployeeZoneId').empty().append('<option value="">All</option>');
        });

        // Handle change event for createEmployeeDealershipId
        $('#createEmployeeDealershipId').on('change', function() {
            var dealershipId = $(this).val();
        });

        $('#createEmployeeForm').on('submit', function(event) {
            event.preventDefault();
            var formValid = validateForm('create'); // Validate all fields

            if (!formValid) {
                return;
            }

            var formData = new FormData(this); // Use FormData for file uploads

            // The profile_pic is now in the hidden input, no need to append file input directly
            // formData.append('profile_pic', profilePicInput.files[0]); // REMOVED

            $.ajax({
                url: "{{ route('employees.store') }}",
                method: 'POST',
                data: formData,
                processData: false, // Important for FormData
                contentType: false, // Important for FormData
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    employeesTable.ajax.reload();
                    $('#createEmployeeForm')[0].reset();
                    // Clear validation errors for all fields in the form
                    var formFields = $('#createEmployeeForm').find(
                        'input, select, textarea');
                    formFields.each(function() {
                        clearValidationError($(this).attr('id'));
                    });
                    // Reset profile picture preview to default
                    $('#create-profile-pic-preview').attr('src', '{{ asset("admin/assets/images/avtar/4.jpg") }}');
                    $('#createEmployeeCroppedImage').val(
                        ''); // Clear hidden cropped image data

                    loadReportingToEmployees(); // Reload after creation
                    // Switch to view tab
                    var viewTab = new bootstrap.Tab(document.getElementById('view-tab'));
                    viewTab.show();
                },
                error: function(error) {
                    if (error.status === 422) {
                        var errors = error.responseJSON.errors;
                        var errorMessages = '';
                        $.each(errors, function(key, value) {
                            errorMessages += value[0] + '<br>';
                        });
                        showToast(errorMessages, 'danger');
                    } else {
                        console.error('Error creating employee:', error);
                        showToast('An unexpected error occurred.', 'danger');
                    }
                }
            });
        });

        // View Employee Helper
        window.loadAndShowViewModal = function(employeeId) {
            var modal = $('#viewEmployeeModal');

            $.ajax({
                url: '/employees/' + employeeId,
                method: 'GET',
                success: function(data) {

                    modal.find('#viewEmployeeName').text(data.name);

                    modal.find('#viewEmployeeDesignation').text(data.designation || 'All');
                    modal.find('#viewEmployeeEmail').text(data.email || 'N/A').attr('href',
                        'mailto:' + (data.email || ''));
                    modal.find('#viewEmployeeId').text(data.employee_id || 'All');
                    modal.find('#viewEmployeeMobile').text(data.mobile || 'N/A').attr(
                        'href', 'tel:' + (data.mobile || ''));
                    modal.find('#viewEmployeeGender').text(data.gender || 'All');
                    modal.find('#viewEmployeeJoiningDate').text(data.joining_date || 'N/A');
                    modal.find('#viewEmployeeDob').text(data.dob || 'N/A');
                    modal.find('#viewEmployeeMaritalStatus').text(data.marital_status || 'N/A');
                    modal.find('#viewEmployeeEmergencyContact').text(data.emergency_contact || 'N/A');
                    modal.find('#viewEmployeeFatherName').text(data.father_name || 'N/A');
                    modal.find('#viewEmployeeMotherName').text(data.mother_name || 'N/A');
                    modal.find('#viewEmployeeSpouseName').text(data.spouse_name || 'N/A');
                    modal.find('#viewEmployeeShirtSize').text(data.shirt_size || 'N/A');
                    modal.find('#viewEmployeeTshirtSize').text(data.tshirt_size || 'N/A');
                    modal.find('#viewEmployeeBloodGroup').text(data.blood_group || 'N/A');
                    modal.find('#viewEmployeeBankName').text(data.bank_name || 'N/A');
                    modal.find('#viewEmployeeAccountNumber').text(data.account_number || 'N/A');
                    modal.find('#viewEmployeeIfscCode').text(data.ifsc_code || 'N/A');
                    modal.find('#viewEmployeePfNo').text(data.pf_no || 'N/A');
                    modal.find('#viewEmployeeEsiNo').text(data.esi_no || 'N/A');
                    modal.find('#viewEmployeeLwfNo').text(data.lwf_no || 'N/A');
                    modal.find('#viewEmployeeAadharNo').text(data.aadhar_no || 'N/A');
                    modal.find('#viewEmployeePanNo').text(data.pan_no || 'N/A');
                    modal.find('#viewEmployeeBranch').text(data.branch || 'N/A');
                    modal.find('#viewEmployeeReportingTo').text(data.reporter2 ? data
                        .reporter2.name : 'N/A');
                    if (data.profile_pic) {
                        modal.find('#viewEmployeeProfilePic').attr('src', '/storage/' + data
                            .profile_pic + '?v=' + new Date().getTime()).show();
                    } else {
                        modal.find('#viewEmployeeProfilePic').attr('src',
                            'admin/assets/images/blog/12.png').show(); // Default image
                    }
                    modal.find('#viewEmployeeAddress').text(data.address || 'N/A');
                    modal.find('#viewEmployeeDepartment').text(data.department ? data
                        .department.name : 'All');
                    modal.find('#viewEmployeeRole').text(data.role ? data.role.role :
                        'All');
                    modal.find('#viewEmployeeDealership').text(data.dealership ? data
                        .dealership.name : 'All');
                    modal.find('#viewEmployeeZone').text(data.zone ? data.zone.name :
                        'All');

                    modal.modal('show');
                },
                error: function(error) {
                    console.error('Error fetching employee data:', error);
                    showToast('Error fetching employee data.', 'danger');
                }
            });
        };

        // View Employee Event Handler
        $(document).on('click', '.view a', function(e) {
            e.preventDefault();
            var employeeId = $(this).data('id');
            if (employeeId) {
                var modal = $('#viewEmployeeModal');
                populateViewModal(modal, employeeId);
                modal.modal('show');
            }
        });

        function populateViewModal(modal, employeeId) {
            $.ajax({
                url: '/employees/' + employeeId,
                method: 'GET',
                success: function(data) {
                    modal.find('#viewEmployeeName').text(data.name);

                    modal.find('#viewEmployeeDesignation').text(data.designation || 'All');
                    modal.find('#viewEmployeeEmail').text(data.email || 'N/A').attr('href',
                        'mailto:' + (data.email || ''));
                    modal.find('#viewEmployeeId').text(data.employee_id || 'All');
                    modal.find('#viewEmployeeMobile').text(data.mobile || 'N/A').attr(
                        'href', 'tel:' + (data.mobile || ''));
                    modal.find('#viewEmployeeGender').text(data.gender || 'All');
                    modal.find('#viewEmployeeJoiningDate').text(data.joining_date || 'N/A');
                    modal.find('#viewEmployeeDob').text(data.dob || 'N/A');
                    modal.find('#viewEmployeeMaritalStatus').text(data.marital_status || 'N/A');
                    modal.find('#viewEmployeeEmergencyContact').text(data.emergency_contact || 'N/A');
                    modal.find('#viewEmployeeFatherName').text(data.father_name || 'N/A');
                    modal.find('#viewEmployeeMotherName').text(data.mother_name || 'N/A');
                    modal.find('#viewEmployeeSpouseName').text(data.spouse_name || 'N/A');
                    modal.find('#viewEmployeeShirtSize').text(data.shirt_size || 'N/A');
                    modal.find('#viewEmployeeTshirtSize').text(data.tshirt_size || 'N/A');
                    modal.find('#viewEmployeeBloodGroup').text(data.blood_group || 'N/A');
                    modal.find('#viewEmployeeBankName').text(data.bank_name || 'N/A');
                    modal.find('#viewEmployeeAccountNumber').text(data.account_number || 'N/A');
                    modal.find('#viewEmployeeIfscCode').text(data.ifsc_code || 'N/A');
                    modal.find('#viewEmployeePfNo').text(data.pf_no || 'N/A');
                    modal.find('#viewEmployeeEsiNo').text(data.esi_no || 'N/A');
                    modal.find('#viewEmployeeLwfNo').text(data.lwf_no || 'N/A');
                    modal.find('#viewEmployeeAadharNo').text(data.aadhar_no || 'N/A');
                    modal.find('#viewEmployeePanNo').text(data.pan_no || 'N/A');
                    modal.find('#viewEmployeeBranch').text(data.branch || 'N/A');
                    modal.find('#viewEmployeeReportingTo').text(data.reporter2 ? data
                        .reporter2.name : 'N/A');
                    if (data.profile_pic) {
                        modal.find('#viewEmployeeProfilePic').attr('src', '/storage/' + data
                            .profile_pic + '?v=' + new Date().getTime()).show();
                    } else {
                        modal.find('#viewEmployeeProfilePic').attr('src',
                            'admin/assets/images/blog/12.png').show(); // Default image
                    }
                    modal.find('#viewEmployeeAddress').text(data.address || 'N/A');
                    modal.find('#viewEmployeeDepartment').text(data.department ? data
                        .department.name : 'All');
                    modal.find('#viewEmployeeRole').text(data.role ? data.role.role :
                        'All');
                    modal.find('#viewEmployeeDealership').text(data.dealership ? data
                        .dealership.name : 'All');
                    modal.find('#viewEmployeeZone').text(data.zone ? data.zone.name :
                        'All');
                },
                error: function(error) {
                    console.error('Error fetching employee data:', error);
                    showToast('Error fetching employee data.', 'danger');
                }
            });
        }

        // Public function for URL parametrs
        window.openViewEmployeeModal = function(employeeId) {
            var modal = $('#viewEmployeeModal');
            populateViewModal(modal, employeeId);
            modal.modal('show');
        };

        function populateEditModal(modal, employeeId) {
            $.ajax({
                url: '/employees/' + employeeId + '/edit',
                method: 'GET',
                success: function(data) {

                    modal.find('#editEmployeeId').val(data.employee.id);
                    modal.find('#editEmployeeName').val(data.employee.name);
                    modal.find('#editEmployeeEmail').val(data.employee.email);
                    modal.find('#editEmployeeUniqueId').val(data.employee
                        .employee_id); // Populate employee_id
                    modal.find('#editEmployeeDesignation').val(data.employee.designation);
                    modal.find('#editEmployeeDepartmentId').val(data.employee
                        .department_id);
                    modal.find('#editEmployeeRoleDisplay').val(data.employee.designation || '');
                    modal.find('#editEmployeeDealershipId').val(data.employee
                        .dealership_id);
                    modal.find('#editEmployeeCountry').val(data.employee.country);
                    modal.find('#editEmployeeMobile').val(data.employee.mobile);
                    modal.find('#editEmployeeGender').val(data.employee.gender);
                    modal.find('#editEmployeeJoiningDate').val(data.employee.joining_date);
                    modal.find('#editEmployeeStatus').val(data.employee.status ? '1' : '0');
                    modal.find('#editEmployeeDob').val(data.employee.dob);
                    modal.find('#editEmployeeAddress').val(data.employee.address);
                    modal.find('#editEmployeeMaritalStatus').val(data.employee.marital_status);
                    modal.find('#editEmployeeEmergencyContact').val(data.employee.emergency_contact);
                    modal.find('#editEmployeeFatherName').val(data.employee.father_name);
                    modal.find('#editEmployeeMotherName').val(data.employee.mother_name);
                    modal.find('#editEmployeeSpouseName').val(data.employee.spouse_name);
                    modal.find('#editEmployeeShirtSize').val(data.employee.shirt_size);
                    modal.find('#editEmployeeTshirtSize').val(data.employee.tshirt_size);
                    modal.find('#editEmployeeBloodGroup').val(data.employee.blood_group);
                    modal.find('#editEmployeeBankName').val(data.employee.bank_name);
                    modal.find('#editEmployeeAccountNumber').val(data.employee.account_number);
                    modal.find('#editEmployeeIfscCode').val(data.employee.ifsc_code);
                    modal.find('#editEmployeePfNo').val(data.employee.pf_no);
                    modal.find('#editEmployeeEsiNo').val(data.employee.esi_no);
                    modal.find('#editEmployeeLwfNo').val(data.employee.lwf_no);
                    modal.find('#editEmployeeAadharNo').val(data.employee.aadhar_no);
                    modal.find('#editEmployeePanNo').val(data.employee.pan_no);
                    modal.find('#editEmployeeBranch').val(data.employee.branch);
                    modal.find('#editEmployeeIsBroker').prop('checked', data.employee
                        .is_broker);
                    if (data.employee.profile_pic) {
                        modal.find('#edit-profile-pic-preview').attr('src', '/storage/' +
                            data.employee.profile_pic + '?v=' + new Date().getTime());
                    } else {
                        modal.find('#edit-profile-pic-preview').attr('src', '{{ asset("admin/assets/images/avtar/4.jpg") }}');
                    }
                    var zoneSelect = modal.find('#editEmployeeZoneId');
                    zoneSelect.empty();
                    zoneSelect.append('<option value="">All</option>');
                    $.each(data.dealership_zones, function(index, zone) {
                        zoneSelect.append('<option value="' + zone.id + '">' + zone
                            .name + '</option>');
                    });

                    zoneSelect.val(data.employee.zone_id);

                    // Load reporting to employees and pre-select
                    loadReportingToEmployees(employeeId, data.employee.reporting_to);
                },
                error: function(error) {
                    console.error('Error fetching employee data for edit:', error);
                    showToast('Error fetching employee data for edit.', 'danger');
                }
            });
        }

        // Edit Employee Event Handler
        $('#editEmployeeModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            if (button.length > 0) {
                var employeeId = button.data('id');
                var modal = $(this);
                if (employeeId) {
                    populateEditModal(modal, employeeId);
                }
            }
        });

        // Public function for URL parametrs
        window.openEditEmployeeModal = function(employeeId) {
            var modal = $('#editEmployeeModal');
            populateEditModal(modal, employeeId);
            modal.modal('show');
        };

        // Handle change event for editEmployeeDealershipId
        $('#editEmployeeDealershipId').on('change', function() {
            var dealershipId = $(this).val();
            var zoneSelect = $('#editEmployeeZoneId');
            zoneSelect.empty();
            zoneSelect.append('<option value="">All</option>');

            if (dealershipId) {
                $.ajax({
                    url: '/zones/by-dealership/' + dealershipId,
                    method: 'GET',
                    success: function(zones) {
                        $.each(zones, function(index, zone) {
                            zoneSelect.append('<option value="' + zone.id + '">' +
                                zone.name + '</option>');
                        });
                    },
                    error: function(error) {
                        console.error('Error loading zones:', error);
                    }
                });
            }
        });

        $('#edit-profile-dp .icon-wrapper').on('click', function(e) {
            e.stopPropagation(); // Prevent event bubbling
            $('#editEmployeeProfilePic').click();
        });



        $('#cropperModal').on('shown.bs.modal', function() {
            cropper = new Cropper(document.getElementById('imageToCrop'), {
                aspectRatio: 1, // For square profile pictures
                viewMode: 1, // Restrict the crop box to not exceed the canvas
                autoCropArea: 0.8, // Initial crop area percentage
            });
            $('#cropperLoader').hide(); // Hide internal loader
            $('.img-container').show(); // Show image container
        }).on('hidden.bs.modal', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            // Clear the image source and reset the file input
            $('#imageToCrop').attr('src', '');
            if (currentFileInput) {
                $(currentFileInput).val(''); // Clear the file input value
            }
        });

        $('#cropButton').on('click', function() {
            var canvas;
            $('#cropperModal').modal('hide');

            if (cropper) {
                canvas = cropper.getCroppedCanvas({
                    width: 250,
                    height: 250,
                });
                var croppedImageBase64 = canvas.toDataURL('image/jpeg'); // Get cropped image as Base64

                // Set the hidden input value with the cropped image data
                $(currentFileInput).nextAll('input[type="hidden"]').val(croppedImageBase64);

                // Update the preview image
                $(currentFileInput).prevAll('img').attr('src', croppedImageBase64);
            }
        });

        $('#editEmployeeForm').on('submit', function(event) {
            event.preventDefault();
            var formValid = validateForm('edit'); // Validate all fields



            if (!formValid) {
                return;
            }


            if (!formValid) {
                return;
            }
            var employeeId = $('#editEmployeeId').val();
            var formData = new FormData(this);
            formData.append('_method', 'PUT');

            // The profile_pic is now in the hidden input, no need to append file input directly
            // var profilePicInput = $('#editEmployeeProfilePic')[0]; // REMOVED
            // if (profilePicInput.files.length > 0) { // REMOVED
            //     formData.append('profile_pic', profilePicInput.files[0]); // REMOVED
            // } // REMOVED

            $.ajax({
                url: "{{ route('employees.update',['employee' => 'EMPLOYEE_ID_PLACEHOLDER']) }}".replace('EMPLOYEE_ID_PLACEHOLDER', employeeId),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#editEmployeeModal').modal('hide');
                    employeesTable.ajax.reload();
                    loadReportingToEmployees(); // Reload after update
                },
                error: function(xhr) {
                    console.error('Error updating employee:', xhr);
                    // Clear previous validation errors
                    $('#editEmployeeForm .form-control').removeClass('is-invalid');
                    $('#editEmployeeForm .invalid-feedback').text('');
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var firstErrorInput = null;
                        $.each(errors, function(field, messages) {
                            var input = $('#editEmployeeForm').find('[name="' + field + '"]');
                            if (input.length > 0) {
                                input.addClass('is-invalid');
                                // Find validation feedback container
                                var feedback = input.closest('.input-group').find('.invalid-feedback');
                                if (feedback.length === 0) {
                                    feedback = input.siblings('.invalid-feedback');
                                }
                                if (feedback.length > 0) {
                                    feedback.text(messages[0]);
                                }
                                if (!firstErrorInput) {
                                    firstErrorInput = input;
                                }
                            }
                        });
                        if (firstErrorInput) {
                            firstErrorInput.focus();
                        }
                        var errorMessage = xhr.responseJSON.message || 'Validation failed. Please check the form.';
                        showToast(errorMessage, 'danger');
                    } else {
                        showToast('Error updating employee.', 'danger');
                    }
                }
            });
        });

        // Delete Employee
        $('#deleteEmployeeModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var employeeId = button.data('id');
            var employeeName = button.data('employee-name');
            var employeeDesignation = button.closest('tr').find('td:nth-child(3) span:first').text();
            var modal = $(this);
            modal.find('#deleteEmployeeId').val(employeeId);
            if (employeeDesignation === 'N/A') {
                modal.find('#deleteEmployeeName').text(employeeName);
            } else {
                modal.find('#deleteEmployeeName').text(employeeName + ' (' + employeeDesignation + ')');
            }
        });

        $('#confirmDeleteEmployee').on('click', function() {
            var employeeId = $('#deleteEmployeeId').val();
            $.ajax({
                url: '/employees/' + employeeId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    $('#deleteEmployeeModal').modal('hide');
                    if (employeesTable) {
                        employeesTable.ajax.reload(null, false);
                    } else {
                        console.error('employeesTable is not defined or accessible.');
                    }
                    loadReportingToEmployees(); // Reload after deletion
                },
                error: function(error) {
                    console.error('Error deleting employee:', error);
                    showToast('Error deleting employee.', 'danger');
                }
            });
        });

        // Profile pic preview
        $('#profile-dp .icon-wrapper').on('click', function(e) {
            e.stopPropagation();
            $('#createEmployeeProfilePicInput').click();
        });

        $('#edit-profile-dp .icon-wrapper').on('click', function(e) {
            e.stopPropagation();
            $('#editEmployeeProfilePicInput').click();
        });

        $('#createEmployeeProfilePicInput, #editEmployeeProfilePicInput').on('change', function(event) {
            currentFileInput = this;
            var files = event.target.files;
            var reader;
            var file;
            if (files && files.length > 0) {
                $('#cropperModal').modal('show');
                $('#cropperLoader').show();
                $('.img-container').hide();
                file = files[0];
                reader = new FileReader();
                reader.onload = function(e) {
                    $('#imageToCrop').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Toggle password visibility
        $('.toggle-password').on('click', function() {
            var targetId = $(this).data('target');
            var passwordField = $(targetId);
            var icon = $(this).find('i');
            var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            icon.toggleClass('fa-eye fa-eye-slash');
        });

        // Check URL Params
        function checkUrlParamsForActions() {
            var urlParams = new URLSearchParams(window.location.search);
            var action = urlParams.get('action');
            var id = urlParams.get('id');
            if (action && id) {
                if (action === 'view') {
                    if (typeof window.openViewEmployeeModal === 'function') {
                        window.openViewEmployeeModal(id);
                    }
                } else if (action === 'edit') {
                    if (typeof window.openEditEmployeeModal === 'function') {
                        window.openEditEmployeeModal(id);
                    }
                }
                var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({
                    path: cleanUrl
                }, '', cleanUrl);
            }
        }

        checkUrlParamsForActions();

        $('#importEmployeeForm').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            if ($('#makeFieldsNullableCheckbox').is(':checked')) {
                formData.append('make_fields_nullable', true);
            }
            var button = $('#importEmployeeButton');
            var spinner = $('#import-spinner');
            var status = $('#import-status');
            var errors = $('#import-errors');
            var progressBar = $('#import-progress-bar');
            var progressContainer = $('.progress');
            var resultsContainer = $('#import-results');
            var closeResultsButton = $('#closeImportResults');
            button.hide();
            spinner.show();
            status.html('');
            errors.html('');
            resultsContainer.html('');
            closeResultsButton.hide();
            progressContainer.show();
            progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
            $.ajax({
                url: "{{ route('employees.import') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.css('width', percentComplete + '%').attr(
                                'aria-valuenow', percentComplete).text(percentComplete +
                                '%');
                            if (percentComplete === 100) {
                                progressBar.text('Processing data on server...');
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    // Do not show button/hide spinner yet - wait for polling to complete
                    $('#importEmployeeForm')[0].reset();

                    var importId = response.import_id;
                    if (importId) {
                        startPollingProgress(importId);
                    } else {
                        // If no importId, it was a direct small import
                        spinner.hide();
                        button.show();
                        showToast(response.message, 'success');
                        progressContainer.hide();
                        recentImportsDataTable.ajax.reload();
                        employeesTable.ajax.reload();
                    }
                },
                error: function(response) {
                    spinner.hide();
                    button.show();
                    progressContainer.hide(); // Hide progress bar on error
                    let errorMessage = 'An unexpected error occurred during import.';

                    if (response.responseJSON && response.responseJSON.message) {
                        errorMessage = response.responseJSON.message;

                        // Check for SQLSTATE[23000] Integrity constraint violation
                        const regex =
                            /SQLSTATE\[23000\]: Integrity constraint violation: 1062 Duplicate entry '(.*?)' for key '(.*?)'/;
                        const match = errorMessage.match(regex);

                        if (match && match.length > 2) {
                            const duplicateEntry = match[1];
                            const key = match[2];
                            errorMessage =
                                `Duplicate entry found: "${duplicateEntry}" for key "${key}". Please ensure all employee emails and IDs are unique.`;
                        }
                    } else if (response.responseJSON && response.responseJSON.error) {
                        errorMessage = response.responseJSON.error;
                    }

                    errors.html('<div class="alert alert-danger">' + errorMessage + '</div>');
                }
            });
        });

        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route('employees.import.template') }}";
        });

        var progressInterval;

        function startPollingProgress(importId) {
            var progressBar = $('#import-progress-bar');
            var progressContainer = $('.progress');
            var status = $('#import-status');
            var errors = $('#import-errors');
            var resultsContainer = $('#import-results');
            var closeResultsButton = $('#closeImportResults');

            progressInterval = setInterval(function() {
                $.ajax({
                    url: "{{ route('employees.import.progress',':importId') }}".replace(':importId', importId),
                    method: 'GET',
                    success: function(data) {
                        progressBar.css('width', data.percentage + '%').attr('aria-valuenow', data.percentage).text(data.percentage + '%');
                        status.html('<div class="alert alert-info">Status: ' + data.status + ' (' + data
                            .processed_rows + '/' + data.total_rows + ')</div>').show();

                        if (data.status === 'completed') {
                            clearInterval(progressInterval);
                            showToast('Import completed successfully.', 'success');
                            recentImportsDataTable.ajax.reload();
                            employeesTable.ajax.reload();

                            setTimeout(function() {
                                progressContainer.fadeOut();
                                status.text('Import Finished');
                            }, 2000);

                            var results = data.results;
                            var successful = results.filter(r => r.status === 'success').length;
                            var failed = results.filter(r => r.status === 'failed').length;
                            var skipped = results.filter(r => r.status === 'skipped').length;
                            var total = results.length;

                            var summary = '<div class="alert alert-success">' +
                                '<strong>Import Summary:</strong><br>' +
                                'Total Records: ' + total + '<br>' +
                                'Successful: ' + successful + '<br>' +
                                'Failed: ' + failed + '<br>' +
                                'Skipped: ' + skipped +
                                '</div>';

                            var table = '<table class="table table-bordered mt-3">' +
                                '<thead>' +
                                '<tr>' +
                                '<th>Row Number</th>' +
                                '<th>Status</th>' +
                                '<th>Reason</th>' +
                                '</tr>' +
                                '</thead>' +
                                '<tbody>';

                            if (successful > 0) {
                                table += '<tr class="table-success">' +
                                    '<td>-</td>' +
                                    '<td>' + successful + ' rows</td>' +
                                    '<td>Successfully imported</td>' +
                                    '</tr>';
                            }

                            results.forEach(function(result) {
                                if (result.status === 'failed' || result.status === 'skipped') {
                                    var statusClass = result.status === 'failed' ? 'table-danger' : 'table-warning';
                                    table += '<tr class="' + statusClass + '">' +
                                        '<td>' + result.row_number + '</td>' +
                                        '<td>' + result.status + '</td>' +
                                        '<td>' + result.reason + '</td>' +
                                        '</tr>';
                                }
                            });

                            table += '</tbody></table>';

                            resultsContainer.html(summary + table).show(); // Show results container
                            closeResultsButton.show(); // Show close button when results are displayed

                            // Restore UI state
                            $('#importEmployeeButton').show();
                            $('#import-spinner').hide();
                            status.html('').hide();

                        } else if (data.status === 'failed') {
                            clearInterval(progressInterval);
                            progressContainer.hide();

                            // Restore UI state
                            $('#importEmployeeButton').show();
                            $('#import-spinner').hide();
                            let errorMessages = '';
                            if (data.errors && data.errors.length > 0) {
                                errorMessages = data.errors.join('<br>');
                            }
                            errors.html('<div class="alert alert-danger">' + errorMessages + '</div>').show(); // Show errors container
                            showToast('Import failed.', 'danger');
                            status.html(''); // Clear status message
                            closeResultsButton.show(); // Show close button even on failure
                        }
                    },
                    error: function(xhr, statusText, errorThrown) {
                        clearInterval(progressInterval);
                        progressContainer.hide();

                        // Restore UI state
                        $('#importEmployeeButton').show();
                        $('#import-spinner').hide();

                        errors.html('<div class="alert alert-danger">Error polling import progress: ' +
                            errorThrown + '</div>').show(); // Show errors container
                        showToast('Error polling import progress.', 'danger');
                        status.html(''); // Clear status message
                        closeResultsButton.show(); // Show close button on error
                    }
                });
            }, 2000); // Poll every 2 seconds
        }
        // Clear results function
        function clearImportResultsDisplay() {
            $('#import-results').hide().html(''); // Hide and clear content
            $('#import-status').hide().html(''); // Hide and clear content
            $('#import-errors').hide().html(''); // Hide and clear content
            $('#closeImportResults').hide();
        }

        // Event listener for tab switch
        $('button[data-bs-target="#import"]').on('hidden.bs.tab', function(e) {
            clearImportResultsDisplay();
        });

        // Event listener for clear results button
        $('#closeImportResults').on('click', function() {
            clearImportResultsDisplay();
        });


        var recentImportsDataTable;

        $(document).ready(function() {
            recentImportsDataTable = $('#recentImportsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('employees.import.recent') }}",
                    dataSrc: function(json) {
                        return json;
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        className: 'text-start'
                    },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleString();
                        },
                        className: 'text-start'
                    },
                    {
                        data: null,
                        render: function(data) {
                            var summary = '<strong>' + data.employees_count + ' Employees</strong><br>';
                            if (data.employees && data.employees.length > 0) {
                                var names = data.employees.map(function(e) { return e.name; });
                                summary += '<small class="text-muted">' + names.join(', ');
                                if (data.employees_count > 5) {
                                    summary += '...';
                                }
                                summary += '</small>';
                            }
                            return summary;
                        },
                        className: 'text-start'
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return '<button class="btn btn-sm btn-danger undo-import" data-import-id="' +
                                data + '">Undo</button>';
                        },
                        className: 'text-start'
                    }
                ],                order: [
                    [1, 'desc']
                ], // Order by date descending
                paging: true,
                searching: false,
                lengthChange: false,
                info: false,
                language: {
                    emptyTable: "No recent imports found."
                }
            });

            // Handle Undo Import Button Click
            $(document).on('click', '.undo-import', function() {
                var importId = $(this).data('import-id');
                $('#undoImportId').val(importId);
                $('#undoImportModal').modal('show');
            });

            // Handle Confirm Undo Import
            $('#confirmUndoImport').off('click').on('click', function() {
                var importId = $('#undoImportId').val();
                var button = $(this);

                // Prevent double clicking
                if (button.prop('disabled')) {
                    return;
                }

                button.prop('disabled', true).text('Undoing...');

                $.ajax({
                    url: "{{ route('employees.import.undo', ':importId') }}".replace(':importId', importId),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message, 'success');
                        $('#undoImportModal').modal('hide');
                        if (recentImportsDataTable) {
                            recentImportsDataTable.ajax.reload();
                        }
                        if (employeesTable) {
                            employeesTable.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error undoing import:', xhr);
                        let msg = 'Error undoing import.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Undo Import');
                    }
                });
            });

        });
    });
</script>
@endpush
