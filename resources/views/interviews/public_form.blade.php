@extends('layouts.login')

@section('title')
Apply for Position
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="row m-0">
        <div class="col-12 p-0">
            <div class="login-card login-dark">
                <div>
                    <div class="login-main" style="width: auto; max-width: 800px; padding: 40px;">
                        <div class="text-center mb-4">
                            <img width="150" class="img-fluid for-dark" src="{{ asset('admin/assets/images/logo/logo.png') }}" alt="logo">
                            <img width="150" class="img-fluid for-light" src="{{ asset('admin/assets/images/logo/logo_dark.png') }}" alt="logo">
                            <h4 class="mt-4">Job Application</h4>
                            @if($interview->post_applied_for)
                            <p class="text-muted">Position: <strong>{{ $interview->post_applied_for }}</strong></p>
                            @endif
                        </div>

                        @if(session('success'))
                        <div class="alert alert-success text-center">
                            {{ session('success') }}
                            <p class="mt-2">Thank you for your application.</p>
                        </div>
                        @else

                        <form class="theme-form" action="{{ route('interviews.public.update', $interview->uuid) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Candidate Name <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="candidate_name" value="{{ old('candidate_name', $interview->candidate_name) }}" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="contact_number" value="{{ old('contact_number', $interview->contact_number) }}" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Email ID <span class="text-danger">*</span></label>
                                    <input class="form-control" type="email" name="email_id" value="{{ old('email_id', $interview->email_id) }}" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Educational Qualification</label>
                                    <input class="form-control" type="text" name="educational_qualification" value="{{ old('educational_qualification', $interview->educational_qualification) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Years of Experience</label>
                                    <input class="form-control" type="number" name="years_of_experience" value="{{ old('years_of_experience', $interview->years_of_experience) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Current Employer</label>
                                    <input class="form-control" type="text" name="current_employer" value="{{ old('current_employer', $interview->current_employer) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Last/Current CTC</label>
                                    <input class="form-control" type="number" step="0.01" name="last_current_ctc" value="{{ old('last_current_ctc', $interview->last_current_ctc) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Expected CTC</label>
                                    <input class="form-control" type="number" step="0.01" name="expected_ctc" value="{{ old('expected_ctc', $interview->expected_ctc) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Notice Period</label>
                                    <input class="form-control" type="text" name="notice_period" value="{{ old('notice_period', $interview->notice_period) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Location</label>
                                    <input class="form-control" type="text" name="location" value="{{ old('location', $interview->location) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="col-form-label">Resume Attachment (PDF/DOC/DOCX)</label>
                                    <input class="form-control" type="file" name="resume" accept=".pdf,.doc,.docx">
                                    @if($interview->resume)
                                    <small class="text-success">Current resume already attached. Upload new to replace.</small>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group mt-4 mb-0">
                                <button class="btn btn-primary btn-block w-100" type="submit">Submit Application</button>
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