@extends('layouts.login')

@section('title')
Apply for {{ $vacancy->title }}
@endsection

@push('styles')
<style>
    .login-main {
        width: 100% !important;
        max-width: 900px !important;
        padding: 40px !important;
    }

    .form-section-title {
        border-bottom: 2px solid var(--theme-deafult);
        padding-bottom: 5px;
        margin-bottom: 20px;
        color: var(--theme-deafult);
        font-weight: 700;
        font-size: 1.1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">
    <div class="row m-0">
        <div class="col-12 p-0">
            <div class="login-card login-dark">
                <div>
                    <div class="login-main">
                        <div class="text-center mb-5">
                            <img width="150" class="img-fluid" src="{{ asset('admin/assets/images/logo/svhe.png') }}" alt="logo">
                            <h4 class="mt-4">Job Application Form</h4>
                            <p class="text-muted">Position: <span class="text-primary fw-bold">{{ $vacancy->title }}</span></p>
                        </div>

                        @if(session('success'))
                        <div class="alert alert-success text-center p-4">
                            <i class="fa fa-check-circle fa-3x mb-3 d-block"></i>
                            <h5>Success!</h5>
                            <p>{{ session('success') }}</p>
                            <a href="{{ route('job-vacancies.public', $vacancy->slug) }}" class="btn btn-primary mt-3">Back to Job Details</a>
                        </div>
                        @else

                        <form class="theme-form" action="{{ route('job-vacancies.apply.submit', $vacancy->slug) }}" method="POST">
                            @csrf

                            <div class="form-section-title">Personal Information</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="candidate_name" value="{{ old('candidate_name') }}" required placeholder="Enter your full name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input class="form-control" type="email" name="email_id" value="{{ old('email_id') }}" required placeholder="email@example.com">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="contact_number" value="{{ old('contact_number') }}" required placeholder="+91 0000000000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current Location</label>
                                    <input class="form-control" type="text" name="location" value="{{ old('location') }}" placeholder="City, State">
                                </div>
                            </div>

                            <div class="form-section-title mt-4">Professional Details</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Highest Qualification</label>
                                    <input class="form-control" type="text" name="educational_qualification" value="{{ old('educational_qualification') }}" placeholder="e.g. B.Tech, MBA">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Experience (Years)</label>
                                    <input class="form-control" type="number" name="years_of_experience" value="{{ old('years_of_experience') }}" placeholder="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current/Last Employer</label>
                                    <input class="form-control" type="text" name="current_employer" value="{{ old('current_employer') }}" placeholder="Company Name">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Current CTC</label>
                                    <input class="form-control" type="number" step="0.01" name="last_current_ctc" value="{{ old('last_current_ctc') }}" placeholder="LPA">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Expected CTC</label>
                                    <input class="form-control" type="number" step="0.01" name="expected_ctc" value="{{ old('expected_ctc') }}" placeholder="LPA">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Notice Period</label>
                                    <input class="form-control" type="text" name="notice_period" value="{{ old('notice_period') }}" placeholder="e.g. 30 Days">
                                </div>
                            </div>

                            @if($vacancy->form_fields && count($vacancy->form_fields) > 0)
                            <div class="form-section-title mt-4">Additional Information</div>
                            <div class="row">
                                @foreach($vacancy->form_fields as $field)
                                @php
                                $fieldName = 'custom_' . \Illuminate\Support\Str::slug($field['label'], '_');
                                $isRequired = isset($field['required']) && $field['required'];
                                @endphp
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ $field['label'] }} @if($isRequired) <span class="text-danger">*</span> @endif</label>

                                    @if($field['type'] === 'textarea')
                                    <textarea class="form-control" name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }} rows="3" placeholder="Enter {{ $field['label'] }}">{{ old($fieldName) }}</textarea>

                                    @elseif($field['type'] === 'select')
                                    <select class="form-control" name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }}>
                                        <option value="">Select Option</option>
                                        @foreach(explode(',', $field['options']) as $opt)
                                        <option value="{{ trim($opt) }}" {{ old($fieldName) == trim($opt) ? 'selected' : '' }}>{{ trim($opt) }}</option>
                                        @endforeach
                                    </select>

                                    @elseif($field['type'] === 'radio')
                                    <div class="mt-2">
                                        @foreach(explode(',', $field['options']) as $opt)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="{{ $fieldName }}" id="{{ $fieldName }}_{{ $loop->index }}" value="{{ trim($opt) }}" {{ old($fieldName) == trim($opt) ? 'checked' : '' }} {{ $isRequired ? 'required' : '' }}>
                                            <label class="form-check-label" for="{{ $fieldName }}_{{ $loop->index }}">{{ trim($opt) }}</label>
                                        </div>
                                        @endforeach
                                    </div>

                                    @elseif($field['type'] === 'checkbox')
                                    <div class="mt-2">
                                        @foreach(explode(',', $field['options']) as $opt)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="{{ $fieldName }}[]" id="{{ $fieldName }}_{{ $loop->index }}" value="{{ trim($opt) }}" {{ is_array(old($fieldName)) && in_array(trim($opt), old($fieldName)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $fieldName }}_{{ $loop->index }}">{{ trim($opt) }}</label>
                                        </div>
                                        @endforeach
                                    </div>

                                    @else
                                    <input class="form-control" type="{{ $field['type'] }}" name="{{ $fieldName }}" value="{{ old($fieldName) }}" {{ $isRequired ? 'required' : '' }} placeholder="Enter {{ $field['label'] }}">
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif

                            <div class="form-group mt-5 mb-0 text-center">
                                <button class="btn btn-primary btn-lg px-5" type="submit">Submit Application</button>
                                <div class="mt-3">
                                    <a href="{{ route('job-vacancies.public', $vacancy->slug) }}" class="text-muted"><i class="fa fa-arrow-left me-1"></i> Cancel and go back</a>
                                </div>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection