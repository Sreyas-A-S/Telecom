@extends('layouts.admin')

@section('content')
<div class="container-fluid">


    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Organization Details</h3>
                </div>
                <div class="card-body">
                    <div id="ajaxMessage"></div>

                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form id="organizationSettingsForm" action="{{ route('organization-settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="organization_name">Organization Name</label>
                            <input type="text" class="form-control" id="organization_name" name="organization_name" value="{{ $settings['organization_name'] }}" required>
                        </div>
                        <div class="form-group">
                            <label for="organization_address">Address</label>
                            <textarea class="form-control" id="organization_address" name="organization_address" rows="3" required>{{ $settings['organization_address'] }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="organization_phone">Phone Number</label>
                            <input type="text" class="form-control" id="organization_phone" name="organization_phone" value="{{ $settings['organization_phone'] }}" required>
                        </div>
                        <div class="form-group">
                            <label for="organization_website">Website</label>
                            <input type="text" class="form-control" id="organization_website" name="organization_website" value="{{ $settings['organization_website'] }}">
                        </div>
                        <div class="form-group">
                            <label for="organization_logo">Organization Logo</label>
                            <input type="file" class="form-control" id="organization_logo" name="organization_logo" accept=".jpg,.jpeg,.png,.webp">
                            <small class="text-muted">Recommended: PNG/JPG/WEBP up to 2MB.</small>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2 mt-4" id="saveBtn">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Preview</h3>
                </div>
                <div class="card-body">


                    <div class="mt-4 p-3 border rounded bg-light">
                        @if(!empty($settings['organization_logo']))
                        <img id="preview_logo" src="{{ asset($settings['organization_logo']) }}" alt="Organization Logo" style="max-height: 60px; margin-bottom: 10px;">
                        @else
                        <img id="preview_logo" src="" alt="Organization Logo" style="max-height: 60px; margin-bottom: 10px; display:none;">
                        @endif
                        <h5 class="font-weight-bold text-dark mb-1" id="preview_name">{{ $settings['organization_name'] }}</h5>
                        <p class="mb-0 text-muted small" id="preview_address">{{ $settings['organization_address'] }}</p>
                        <p class="mb-0 text-muted small">Phone: <span id="preview_phone">{{ $settings['organization_phone'] }}</span></p>
                        <p class="mb-0 text-muted small" id="preview_website">{{ $settings['organization_website'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Simple live preview script
        $('#organization_name').on('input', function() {
            $('#preview_name').text($(this).val());
        });
        $('#organization_address').on('input', function() {
            $('#preview_address').text($(this).val());
        });
        $('#organization_phone').on('input', function() {
            $('#preview_phone').text($(this).val());
        });
        $('#organization_website').on('input', function() {
            $('#preview_website').text($(this).val());
        });
        $('#organization_logo').on('change', function() {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview_logo').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        });

        // AJAX Form Submission
        $('#organizationSettingsForm').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var btn = $('#saveBtn');
            var originalBtnText = btn.text();

            // Clear previous messages
            $('#ajaxMessage').html('');
            $('.alert-success').remove(); // Remove PHPs session flash if exists

            // Loading state
            btn.prop('disabled', true).text('Saving...');
            var formData = new FormData(form[0]);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#ajaxMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    } else {
                        $('#ajaxMessage').html('<div class="alert alert-danger">Error saving changes.</div>');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Optional: show field-specific errors if needed, but simple alert is usually fine for this context
                        errorMsg += ' Please check the inputs.';
                    }
                    $('#ajaxMessage').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                },
                complete: function() {
                    btn.prop('disabled', false).text(originalBtnText);
                }
            });
        });
    });
</script>
@endpush
@endsection
