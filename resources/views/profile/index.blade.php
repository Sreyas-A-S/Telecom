@extends('layouts.admin')

@section('title', 'My Profile')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/image-cropper.css') }}">
<style>
    .show-hide {
        position: absolute;
        right: 10px;
        /* Adjust as needed */
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        z-index: 2;
    }

    .form-input input.form-control {
        padding-right: 60px;
        /* Increased padding to accommodate 'Show/Hide' text */
    }

    .profile-picture-container {
        position: relative;
        display: inline-block;
    }

    .profile-picture-overlay {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .profile-picture-container:hover .profile-picture-overlay {
        opacity: 1;
    }

    /* Cropper container styles */
    .img-container {
        max-height: 500px;
    }

    .img-container img {
        max-width: 100%;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="profile-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile"
                                role="tab" aria-controls="profile" aria-selected="true">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="edit-profile-tab" data-bs-toggle="tab" href="#edit-profile"
                                role="tab" aria-controls="edit-profile" aria-selected="false">Edit Profile</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="profile-tabs-content">
                        <div class="tab-pane fade show active" id="profile" role="tabpanel"
                            aria-labelledby="profile-tab">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <div class="profile-picture-container">
                                        <img id="profile-pic-preview-main"
                                            src="{{ $user->profile_pic ? asset('storage/'. $user->profile_pic) : asset('admin/assets/images/dashboard/profile.png') }}"
                                            class="img-fluid rounded-circle" alt="Profile Picture" width="200">
                                    </div>
                                    <h4 class="mt-3">{{ $user->name }}</h4>
                                    <p class="text-muted">{{ ucfirst($user->user_type) }}</p>
                                </div>
                                <div class="col-md-8">
                                    <h5>Profile Details</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Name:</strong> {{ $user->name }}</p>
                                            <p><strong>Email:</strong> <a href="mailto:{{ $user->email }}">{{ $user->email }}</a></p>
                                            @if($user->employee)
                                            <p><strong>Mobile:</strong> <a href="tel:{{ $user->employee->mobile }}">{{ $user->employee->mobile }}</a></p>
                                            <p><strong>Address:</strong> {{ $user->employee->address }}</p>
                                            <p><strong>Employee ID:</strong> {{ $user->employee->employee_id }}</p>
                                            <p><strong>Gender:</strong> {{ $user->employee->gender }}</p>
                                            <p><strong>Joining Date:</strong> {{ $user->employee->joining_date }}</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            @if($user->employee)
                                            <p><strong>Designation:</strong> {{ $user->employee->designation }}</p>
                                            <p><strong>Department:</strong> {{ $user->employee->department ? $user->employee->department->name :'N/A' }}</p>
                                            <p><strong>Role:</strong> {{ $user->employee->role ? $user->employee->role->role :'N/A' }}</p>
                                            <p><strong>Dealership:</strong> {{ $user->employee->dealership ? $user->employee->dealership->name :'N/A' }}</p>
                                            <p><strong>Zone:</strong> {{ $user->employee->zone ? $user->employee->zone->name :'N/A' }}</p>
                                            <p><strong>Reporting To:</strong> {{ $user->employee->reporter2 ? $user->employee->reporter2->name :'N/A' }}</p>
                                            <p><strong>Date of Birth:</strong> {{ $user->employee->dob }}</p>
                                            <!-- <p><strong>User ID:</strong> {{ $user->employee->user_id }}</p> -->
                                            @endif

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
                            <h5 class="mb-4">Profile Picture</h5>
                            <form id="profile-form" enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-4">
                                    <div class="col-md-12 text-center">
                                        <div class="mb-3">
                                            <img id="edit-profile-pic-preview"
                                                src="{{ $user->profile_pic ? asset('storage/'. $user->profile_pic) : asset('admin/assets/images/dashboard/profile.png') }}"
                                                class="img-fluid rounded-circle" alt="Profile Picture" width="150" style="object-fit: cover; height: 150px; width: 150px;">
                                        </div>
                                        <div>
                                            <label for="edit_profile_pic" class="btn btn-outline-primary btn-sm">
                                                <i class="fa fa-camera me-2"></i> Change Picture
                                            </label>
                                            <input type="file" class="d-none" id="edit_profile_pic" name="profile_pic" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <hr class="mb-4">
                                <h5 class="mb-4">Reset Password</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <div class="form-input position-relative">
                                                <input type="password" class="form-control" id="current_password" name="current_password">
                                                <div class="show-hide"><i class="fa fa-eye"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-none d-md-block"></div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password" class="form-label">New Password</label>
                                            <div class="form-input position-relative">
                                                <input type="password" class="form-control" id="password" name="password">
                                                <div class="show-hide"><i class="fa fa-eye"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                            <div class="form-input position-relative">
                                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                                <div class="show-hide"><i class="fa fa-eye"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Picture Modal -->


<!-- Crop Modal -->
<div class="modal fade" id="crop-modal" tabindex="-1" aria-labelledby="crop-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crop-modal-label">Crop Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <img id="image-to-crop" src="" alt="Picture">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="crop-and-save">Crop & Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')


<script>
    $(document).ready(function() {
        let cropper;
        let croppedBlob = null;
        const image = document.getElementById('image-to-crop');

        // Handle URL hash for tabs
        var hash = window.location.hash;
        if (hash) {
            var triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]') || document.querySelector('a[href="' + hash + '"]');
            if (triggerEl) {
                var tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        // Profile picture preview in modal (Main Profile Tab)


        // Edit Profile tab picture preview with Cropper
        // Edit Profile tab picture preview with Cropper
        $('#edit_profile_pic').on('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    image.src = e.target.result;
                    $('#crop-modal').data('trigger', 'edit-tab');
                    $('#crop-modal').modal('show');
                };
                reader.readAsDataURL(file);
            }
        });

        $('#crop-modal').on('shown.bs.modal', function() {
            if (typeof Cropper !== 'undefined') {
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                });
            } else {
                console.error('Cropper is not defined. Please ensure the script is loaded.');
            }
        }).on('hidden.bs.modal', function() {
            cropper.destroy();
            cropper = null;
            // Reset file input if cancelled without cropping? 
            // Maybe not necessary, but good UX if they want to re-select same file
            $('#edit_profile_pic').val('');
        });

        $('#crop-and-save').on('click', function() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                });

                canvas.toBlob(function(blob) {
                    croppedBlob = blob;
                    const url = URL.createObjectURL(blob);

                    $('#edit-profile-pic-preview').attr('src', url);

                    $('#crop-modal').modal('hide');
                });
            }
        });



        // Normalize show-hide controls: ensure exactly one per .form-input
        // $('.form-input').each(function() {
        //     var $fi = $(this);
        //     var $toggles = $fi.find('.show-hide');
        //     if ($toggles.length > 1) {
        //         // keep the first, remove duplicates
        //         $toggles.not(':first').remove();
        //     } else if ($toggles.length === 0) {
        //         // add one if missing
        //         $fi.append('<div class=\"show-hide\"><span class=\"show\">Show</span></div>');
        //     }
        // });

        // Delegate click so any dynamically added/kept toggle works
        $(document).on('click', '.show-hide', function(e) {
            e.preventDefault();
            var $btn = $(this);
            // find the nearest input inside the same .form-input container
            var $input = $btn.closest('.form-input').find('input');
            var $icon = $btn.find('i');



            if ($input.length === 0) return;

            var currentType = $input.attr('type') || $input.prop('type');
            if (currentType === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Save profile form
        $('#profile-form').on('submit', function(e) {
            e.preventDefault();

            let isValid = true;

            // Clear previous errors
            $('.password-error').remove();
            $('#current_password').removeClass('is-invalid');
            $('#password').removeClass('is-invalid');
            $('#password_confirmation').removeClass('is-invalid');

            const currentPassword = $('#current_password').val();
            const newPassword = $('#password').val();
            const confirmNewPassword = $('#password_confirmation').val();

            // Validate current password if new password fields are filled
            if (newPassword || confirmNewPassword) {
                if (!currentPassword) {
                    $('#current_password').addClass('is-invalid').after(
                        '<div class="text-danger password-error">Current password is required.</div>'
                    );
                    isValid = false;
                }
            }

            // Validate new password
            if (newPassword) {
                if (newPassword.length < 8) {
                    $('#password').addClass('is-invalid').after(
                        '<div class="text-danger password-error">New password must be at least 8 characters.</div>'
                    );
                    isValid = false;
                }
            } else if (currentPassword || confirmNewPassword) {
                // If current password or confirm new password is provided, new password is required
                $('#password').addClass('is-invalid').after(
                    '<div class="text-danger password-error">New password is required.</div>');
                isValid = false;
            }

            // Validate confirm new password
            if (confirmNewPassword) {
                if (newPassword !== confirmNewPassword) {
                    $('#password_confirmation').addClass('is-invalid').after(
                        '<div class="text-danger password-error">New password and confirmation do not match.</div>'
                    );
                    isValid = false;
                }
            }

            if (!isValid) {
                showToast('Please correct the password fields.', 'error');
                return;
            }

            var formData = new FormData(this);
            if (croppedBlob) {
                formData.set('profile_pic', croppedBlob, 'profile_pic.png');
            }

            $.ajax({
                url: '{{ route('my-profile.update') }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    showToast(response.message, 'success');

                    // Update profile pictures if a new one was selected
                    if (croppedBlob) {
                        const newPicUrl = $('#edit-profile-pic-preview').attr('src');
                        $('#profile-pic-preview-main').attr('src', newPicUrl);
                        $('.profile-media img').attr('src', newPicUrl);
                    }

                    // Clear password fields
                    $('#current_password').val('');
                    $('#password').val('');
                    $('#password_confirmation').val('');

                    // Reset croppedBlob
                    croppedBlob = null;
                },
                error: function(response) {
                    let errors = response.responseJSON.errors;
                    let errorMessage = '';
                    $.each(errors, function(key, value) {
                        errorMessage += value[0] + '\n';
                    });

                    showToast(errorMessage, 'error');
                }
            });
        });
    });
</script>
@endpush