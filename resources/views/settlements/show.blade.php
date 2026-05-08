@extends('layouts.admin')

@section('title', 'Settlement Details')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Settlement Details</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('settlements.index') }}">Settlements</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Settlement Details for {{ $settlement->employee_name }}</h4>
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

                    <h5 class="mb-3">Employee Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="mb-0"><strong>Employee Code:</strong> {{ $settlement->employee_code }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-0"><strong>Name:</strong> {{ $settlement->employee_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-0"><strong>Age:</strong> {{ $settlement->age }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-0"><strong>Department:</strong> {{ $settlement->department }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-0"><strong>Head Office/Branch:</strong> {{ $settlement->head_office_branch }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-0"><strong>Designation:</strong> {{ $settlement->designation }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-0"><strong>Date of Joining:</strong> {{ $settlement->date_of_joining }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-0"><strong>Date of Resignation:</strong> {{ $settlement->date_of_resignation }}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <p class="mb-0"><strong>Reason for Resignation:</strong> {{ $settlement->reason_for_resignation }}</p>
                        </div>
                    </div>



                    <hr class="my-4">

                    <h5 class="mb-3">No dues from departments</h5>
                    <div class="table-responsive border-bottom">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Remarks</th>
                                    <th>Signature</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $groupedRemarks = $settlement->remarks->groupBy('department');
                                @endphp
                                @forelse ($groupedRemarks as $department => $remarks)
                                <tr>
                                    <td>
                                        <strong>{{ $department }}</strong>
                                        @php $hasManager = false; @endphp
                                        @foreach ($remarks as $remark)
                                        @if($remark->manager)
                                        @php $hasManager = true; @endphp
                                        <div class="mt-2">
                                            <div>{{ $remark->manager->name }} ({{ $remark->manager->designation ??'No Designation' }})</div>
                                            <div class="text-muted">{{ $remark->manager->email }}</div>
                                        </div>
                                        @endif
                                        @endforeach

                                        @if(!$hasManager)
                                        <div class="mt-2 text-muted small italic">
                                            <i class="fa fa-exclamation-circle"></i> No manager assigned
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($remarks as $remark)
                                        <div class="mb-2">
                                            <p class="mb-1">{{ $remark->remark }}</p>
                                            @if($remark->file_path)
                                            <div class="mt-1">
                                                <a href="{{ asset('storage/' . $remark->file_path) }}" target="_blank" class="text-primary">
                                                    <i class="fa fa-paperclip"></i> View Attachment
                                                </a>
                                            </div>
                                            @else
                                            <form class="upload-remark-file-form mt-2" enctype="multipart/form-data" data-remark-id="{{ $remark->id }}">
                                                @csrf
                                                <div class="input-group input-group-sm">
                                                    <input type="file" class="form-control" name="file" required>
                                                    <button class="btn btn-outline-secondary" type="submit">Upload</button>
                                                </div>
                                            </form>
                                            @endif
                                        </div>
                                        @endforeach
                                    </td>
                                    <td>

                                        @php
                                        $allFilled = $remarks->every(fn($remark) => $remark->is_filled);
                                        @endphp
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input department-filled-checkbox" type="checkbox" role="switch" id="departmentFilled{{ $loop->index }}" data-settlement-id="{{ $settlement->id }}" data-department="{{ $department }}" {{ $allFilled ?'checked':'' }}>
                                            <label class="form-check-label" for="departmentFilled{{ $loop->index }}">Mark as Filled</label>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No remarks found for this settlement.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('settlements.index') }}" class="btn btn-secondary mt-3">Back to Settlements</a>
                    <a href="{{ route('settlements.exportPdf', $settlement->id) }}" class="btn btn-primary mt-3">Export to PDF</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var currentCheckbox = null; // To store the checkbox that triggered the modal

        $('.department-filled-checkbox').on('change', function() {
            currentCheckbox = $(this);
            var isFilled = currentCheckbox.is(':checked');
            var department = currentCheckbox.data('department');
            var confirmationMessage = isFilled ?
                `Are you sure you want to mark all dues for the ${department} department as filled?` :
                `Are you sure you want to mark all dues for the ${department} department as NOT filled?`;

            $('#confirmationModalBody').text(confirmationMessage);
            $('#confirmationModal').modal('show');
        });

        $('#confirmActionButton').on('click', function() {
            if (currentCheckbox) {
                var settlementId = currentCheckbox.data('settlement-id');
                var department = currentCheckbox.data('department');
                var isFilled = currentCheckbox.is(':checked');

                $.ajax({
                    url: `/settlements/${settlementId}/department-remarks/update-filled`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        department: department,
                        is_filled: isFilled ? 1 : 0
                    },
                    success: function(response) {
                        showToast(response.message, 'success');
                        currentCheckbox.data('confirmed', true); // Mark as confirmed
                        $('#confirmationModal').modal('hide');
                    },
                    error: function(xhr) {
                        console.error('Error updating department remark status:', xhr);
                        showToast('Error updating department remark status.', 'danger');
                        // Revert checkbox state on error
                        currentCheckbox.prop('checked', !isFilled);
                        currentCheckbox.data('confirmed', false); // Mark as not confirmed
                        $('#confirmationModal').modal('hide');
                    }
                });
            }
        });

        // Revert checkbox state if modal is dismissed without confirmation
        $('#confirmationModal').on('hidden.bs.modal', function() {
            if (currentCheckbox && !currentCheckbox.data('confirmed')) {
                // If the modal was hidden and the action was not confirmed, revert the checkbox state
                currentCheckbox.prop('checked', !currentCheckbox.is(':checked'));
            }
            if (currentCheckbox) {
                currentCheckbox.removeData('confirmed');
            }
        });

        // Handle File Upload Form Submission
        $('.upload-remark-file-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var remarkId = form.data('remark-id');
            var formData = new FormData(this);
            var fileInput = form.find('input[type="file"]');
            var submitButton = form.find('button[type="submit"]');

            // Disable button and show spinner (optional)
            submitButton.prop('disabled', true).text('Uploading...');

            $.ajax({
                url: `/settlements/remarks/${remarkId}/upload-file`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        // Replace form with View Attachment link
                        var linkHtml = `
                            <div class="mt-1">
                                <a href="${response.file_path}" target="_blank" class="text-primary">
                                    <i class="fa fa-paperclip"></i> View Attachment
                                </a>
                            </div>`;
                        form.replaceWith(linkHtml);
                    }
                },
                error: function(xhr) {
                    console.error('Error uploading file:', xhr);
                    var errorMessage = 'Error uploading file.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.file) {
                        errorMessage = xhr.responseJSON.errors.file[0];
                    }
                    showToast(errorMessage, 'danger');
                    submitButton.prop('disabled', false).text('Upload');
                }
            });
        });
    });
</script>
@endpush

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
@endsection