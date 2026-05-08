<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('admin/assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('admin/assets/images/favicon.png') }}" type="image/x-icon">
    <title>{{ $vacancy->title }}</title>

    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;300;400;500;600;700;800&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/font-awesome.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/icofont.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/themify.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/flag-icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/feather-icon.css') }}">

    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/bootstrap.css') }}">

    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/responsive.css') }}">

    <style>
        /* Custom tweaks for public view */
        body {
            background-color: #f6f7fb;
        }

        .page-wrapper.compact-wrapper .page-body-wrapper .page-body {
            margin-left: 0 !important;
            padding-top: 0;
        }

        .job-card-container {
            margin-top: 50px;
            position: relative;
            z-index: 10;
        }

        .job-card {
            border: none;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
        }

        .job-card .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f0f0f0;
            padding: 30px;
        }

        .job-card .card-body {
            padding: 40px;
        }

        /* Typography improvements for dynamic content */
        .job-description {
            font-size: 16px;
            line-height: 1.8;
            color: #595959;
            font-family: 'Montserrat', sans-serif;
        }

        .job-description h1,
        .job-description h2,
        .job-description h3,
        .job-description h4 {
            color: #2b2b2b;
            margin-top: 25px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .job-description ul,
        .job-description ol {
            padding-left: 20px;
            margin-bottom: 20px;
        }

        .job-description li {
            margin-bottom: 10px;
        }

        .job-description p {
            margin-bottom: 20px;
        }

        /* Custom Button Style */
        .btn-apply {
            background: linear-gradient(135deg, var(--theme-deafult) 0%, var(--theme-secondary) 100%);
            border: none;
            color: white !important;
            padding: 14px 45px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 8px;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-apply:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, var(--theme-deafult) 0%, var(--theme-secondary) 100%);
        }

        .badge-custom {
            padding: 8px 15px;
            font-size: 12px;
            letter-spacing: 1px;
            border-radius: 30px;
        }

        .text-primary {
            color: var(--theme-deafult) !important;
        }
    </style>
</head>

<body>
    <!-- loader starts-->
    <div class="loader-wrapper">
        <div class="loader" style="display: flex; justify-content: center; align-items: center; height: 100%;">
            <img class="img-fluid" width="110" src="{{ asset('admin/assets/images/logo/svhe.png') }}" alt="">
        </div>
    </div>
    <!-- loader ends-->
    <!-- Tap on Top -->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>

    <!-- Page Wrapper -->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">

        <!-- Hero Section Removed -->



        <!-- Page Body Start -->
        <div class="page-body-wrapper horizontal-menu">
            <div class="page-body">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-sm-12 col-md-10 col-lg-8 job-card-container">

                            <div class="mb-4">
                                <!-- Back to Home removed -->
                            </div>

                            <div class="card job-card">
                                <div class="card-header">
                                    <div class="text-center mb-4">
                                        <a href="/" class="d-inline-block transition-hover">
                                            <img class="img-fluid" src="{{ asset('admin/assets/images/logo/svhe.png') }}" alt="logo" style="max-height: 50px;">
                                        </a>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-md-9">
                                            <h2 class="mb-2 fw-bold text-dark">{{ $vacancy->title }}</h2>
                                            <div class="d-flex align-items-center flex-wrap gap-3">
                                                <p class="mb-0 text-muted d-flex align-items-center">
                                                    <i data-feather="calendar" class="me-2 text-primary"></i>
                                                    Posted {{ $vacancy->created_at->format('M d, Y') }}
                                                </p>
                                                <span class="text-muted">|</span>
                                                <p class="mb-0 text-muted d-flex align-items-center">
                                                    <i data-feather="eye" class="me-2 text-primary"></i>
                                                    {{ $vacancy->views_count }} Views
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                            @if($vacancy->status === 'Open')
                                            <span class="badge badge-success badge-custom">OPEN POSITION</span>
                                            @else
                                            <span class="badge badge-danger badge-custom">CLOSED</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="product-page-details">
                                        <h5 class="f-w-600 mb-4 text-primary">Job Description</h5>
                                        <div class="job-description">
                                            {!! $vacancy->description !!}
                                        </div>
                                    </div>

                                    <hr class="mt-5 mb-5" style="border-top: 1px solid #eee;">

                                    <div id="apply-section">
                                        @if($vacancy->status === 'Open')
                                        <div class="product-page-details mb-4">
                                            <h5 class="f-w-600 mb-2 text-primary">Apply for this Position</h5>
                                            <p class="text-muted small">Please fill out the form below to submit your application.</p>
                                        </div>

                                        @if(session('success'))
                                        <div class="alert alert-success text-center p-4">
                                            <i class="fa fa-check-circle fa-2x mb-2 d-block"></i>
                                            <h5>Success!</h5>
                                            <p class="mb-0">{{ session('success') }}</p>
                                        </div>
                                        @else
                                        <!-- Form with ID for AJAX -->
                                        <form id="jobApplicationForm" action="{{ route('job-vacancies.apply.submit', $vacancy->slug) }}" method="POST" class="theme-form" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="ref" value="{{ request()->query('ref') }}">

                                            <div class="row">
                                                @if($vacancy->form_fields && count($vacancy->form_fields) > 0)
                                                @foreach($vacancy->form_fields as $field)
                                                @php
                                                $label = strtolower(trim($field['label']));
                                                $normalizedLabel = preg_replace('/[^a-z0-9]+/', ' ', $label);
                                                $normalizedLabel = trim(preg_replace('/\s+/', ' ', $normalizedLabel));
                                                $fieldName = 'custom_' . \Illuminate\Support\Str::slug($field['label'], '_');

                                                // Map standard/common fields to expected database columns
                                                if (in_array($normalizedLabel, ['full name', 'candidate name', 'name', 'your name'])) {
                                                $fieldName = 'candidate_name';
                                                } elseif (in_array($normalizedLabel, ['email', 'email address', 'email id'])) {
                                                $fieldName = 'email_id';
                                                } elseif (in_array($normalizedLabel, ['contact', 'contact number', 'phone', 'mobile', 'phone number'])) {
                                                $fieldName = 'contact_number';
                                                } elseif (in_array($normalizedLabel, ['qualification', 'highest qualification', 'education', 'degree'])) {
                                                $fieldName = 'educational_qualification';
                                                } elseif (in_array($normalizedLabel, ['experience', 'total experience', 'years of experience'])) {
                                                $fieldName = 'years_of_experience';
                                                } elseif (in_array($normalizedLabel, ['current employer', 'employer', 'company', 'current company'])) {
                                                $fieldName = 'current_employer';
                                                }

                                                $isRequired = isset($field['required']) && $field['required'];
                                                @endphp

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">{{ $field['label'] }} @if($isRequired) <span class="text-danger">*</span> @endif</label>

                                                    @if(isset($field['type']) && $field['type'] === 'textarea')
                                                    <textarea class="form-control" name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }} rows="3" placeholder="Enter {{ $field['label'] }}">{{ old($fieldName) }}</textarea>

                                                    @elseif(isset($field['type']) && $field['type'] === 'select')
                                                    <select class="form-control" name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }}>
                                                        <option value="">Select Option</option>
                                                        @if(isset($field['options']))
                                                        @foreach(explode(',', $field['options']) as $opt)
                                                        <option value="{{ trim($opt) }}" {{ old($fieldName) == trim($opt) ? 'selected' : '' }}>{{ trim($opt) }}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>

                                                    @elseif(isset($field['type']) && $field['type'] === 'radio')
                                                    <div class="mt-1">
                                                        @if(isset($field['options']))
                                                        @foreach(explode(',', $field['options']) as $opt)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="{{ $fieldName }}" id="{{ $fieldName }}_{{ $loop->index }}" value="{{ trim($opt) }}" {{ old($fieldName) == trim($opt) ? 'checked' : '' }} {{ $isRequired ? 'required' : '' }}>
                                                            <label class="form-check-label" for="{{ $fieldName }}_{{ $loop->index }}">{{ trim($opt) }}</label>
                                                        </div>
                                                        @endforeach
                                                        @endif
                                                    </div>

                                                    @elseif(isset($field['type']) && $field['type'] === 'checkbox')
                                                    <div class="mt-1">
                                                        @if(isset($field['options']))
                                                        @foreach(explode(',', $field['options']) as $opt)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="{{ $fieldName }}[]" id="{{ $fieldName }}_{{ $loop->index }}" value="{{ trim($opt) }}" {{ is_array(old($fieldName)) && in_array(trim($opt), old($fieldName)) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="{{ $fieldName }}_{{ $loop->index }}">{{ trim($opt) }}</label>
                                                        </div>
                                                        @endforeach
                                                        @endif
                                                    </div>

                                                    @elseif(isset($field['type']) && $field['type'] === 'file')
                                                    <input class="form-control" type="file" name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }} accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                    <small class="text-muted f-10 d-block mt-1">Accepted formats: PDF, DOC, Image. Max size: 1MB.</small>

                                                    @else
                                                    <input class="form-control" type="{{ $field['type'] ?? 'text' }}" name="{{ $fieldName }}" value="{{ old($fieldName) }}" {{ $isRequired ? 'required' : '' }} placeholder="Enter {{ $field['label'] }}">
                                                    @endif
                                                    <div class="invalid-feedback" id="error-{{ $fieldName }}"></div>
                                                </div>
                                                @endforeach
                                                @else
                                                <div class="col-12 text-center py-5">
                                                    <p class="text-muted">No application form fields have been configured for this position.</p>
                                                </div>
                                                @endif
                                            </div>

                                            <div class="text-center mt-4">
                                                <button class="btn btn-primary" type="submit" id="submitBtn">
                                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="submitSpinner"></span>
                                                    Submit Application
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Success Message Container (Hidden by default) -->
                                        <div id="successMessage" class="d-none">
                                            <div class="card border-0 shadow-sm text-center py-4">
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <i class="fa fa-check-circle text-success" style="font-size: 3rem;"></i>
                                                    </div>
                                                    <h4 class="fw-bold text-success mb-2">Success!</h4>
                                                    <p class="text-muted mb-4">Your application has been submitted successfully!</p>

                                                    <a href="{{ url()->current() }}" class="btn btn-outline-primary btn-sm px-4">Submit Another Response</a>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @else
                                        <div class="alert alert-light-warning text-center" role="alert">
                                            <i data-feather="alert-circle" class="me-2"></i> This position is currently closed for applications.
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer bg-dark text-center py-3">
                                    <div class="mb-2">
                                        <img src="{{ asset('admin/assets/images/logo/logo-white.png') }}" alt="Korps" style="height: 30px; opacity: 0.9;">
                                    </div>
                                    <p class="mb-0 text-white-50 f-12">&copy; {{ date('Y') }} Logiprompt. All rights reserved.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- latest jquery-->
    <script src="{{ asset('admin/assets/js/jquery-3.5.1.min.js') }}"></script>
    <!-- Bootstrap js-->
    <script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('admin/assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- scrollbar js-->
    <script src="{{ asset('admin/assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('admin/assets/js/scrollbar/custom.js') }}"></script>
    <!-- Sidebar jquery-->
    <script src="{{ asset('admin/assets/js/config.js') }}"></script>
    <!-- Plugins JS start-->
    <script src="{{ asset('admin/assets/js/tooltip-init.js') }}"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{ asset('admin/assets/js/script.js') }}"></script>
    <!-- AJAX Script -->
    <script>
        $(document).ready(function() {
            $('#jobApplicationForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let btn = $('#submitBtn');
                let spinner = $('#submitSpinner');
                let formData = new FormData(this);

                // Clear errors
                $('.invalid-feedback').text('').hide();
                $('.form-control').removeClass('is-invalid');

                // Loading State
                btn.prop('disabled', true);
                spinner.removeClass('d-none');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Hide form and show success message
                        form.hide();
                        $('#successMessage').removeClass('d-none');
                        // Scroll to success message
                        $('html, body').animate({
                            scrollTop: $("#successMessage").offset().top - 100
                        }, 500);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        spinner.addClass('d-none');

                        if (xhr.status === 422) { // Validation Error
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                // For array fields like checkboxes, key might be 'custom_field.0', so we target 'custom_field'
                                // But here we use fieldName directly.
                                let fieldName = key;
                                let input = form.find('[name="' + fieldName + '"], [name="' + fieldName + '[]"]');

                                if (input.length > 0) {
                                    input.addClass('is-invalid');
                                    let errorDiv = form.find('#error-' + fieldName);
                                    if (errorDiv.length === 0) {
                                        // Try finding closest div
                                        errorDiv = input.closest('.col-md-6').find('.invalid-feedback');
                                    }
                                    errorDiv.text(value[0]).show();
                                }
                            });
                            // Scroll to first error
                            $('html, body').animate({
                                scrollTop: $(".is-invalid").first().offset().top - 100
                            }, 500);
                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
