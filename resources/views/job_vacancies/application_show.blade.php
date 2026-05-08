@extends('layouts.admin')

@section('title', 'Application Details')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Application Details</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Hiring</li>
                    <li class="breadcrumb-item"><a href="{{ route('job-vacancies.list') }}">Job Vacancies</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('job-vacancies.analytics', $application->job_vacancy_id) }}">Analytics</a></li>
                    <li class="breadcrumb-item active">Application Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Conversion Confirmation Modal -->
<div class="modal fade" id="convertModal" tabindex="-1" aria-labelledby="convertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="convertModalLabel">Shortlist Candidate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to shortlist this application for an interview?
                <br><br>
                <small class="text-muted">This will change the status to <strong>Shortlisted</strong> and create a new interview record.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('job-vacancies.applications.convert', $application->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Yes, Shortlist</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <!-- Left Sidebar: Candidate Profile & Actions -->
        <div class="col-xl-3 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($application->candidate_name) }}&background=random&size=128"
                            alt="Candidate Avatar" class="rounded-circle img-thumbnail shadow-sm" width="100">
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $application->candidate_name }}</h5>
                    <p class="text-muted small mb-2">{{ $application->post_applied_for }}</p>

                    <div class="mb-3">
                        @if($application->status === 'Applied')
                        <span class="badge bg-warning text-dark px-3 py-2">Applied</span>
                        @elseif($application->status === 'Shortlisted')
                        <span class="badge bg-success px-3 py-2">Shortlisted</span>
                        @else
                        <span class="badge bg-secondary px-3 py-2">{{ $application->status }}</span>
                        @endif
                    </div>

                    <hr>

                    <div class="text-start">
                        <div class="mb-3">
                            <label class="text-muted small fw-bold d-block">Email Address</label>
                            <a href="mailto:{{ $application->email_id }}" class="text-decoration-none text-dark fw-bold text-break">
                                <i class="fa fa-envelope me-2 text-primary"></i>{{ $application->email_id }}
                            </a>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-bold d-block">Phone Number</label>
                            <a href="tel:{{ $application->contact_number }}" class="text-decoration-none text-dark fw-bold">
                                <i class="fa fa-phone me-2 text-primary"></i>{{ $application->contact_number }}
                            </a>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-bold d-block">Location</label>
                            <span class="fw-bold"><i class="fa fa-map-marker me-2 text-primary"></i>{{ $application->location ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small fw-bold d-block">Applied On</label>
                            <span class="fw-bold"><i class="fa fa-calendar me-2 text-primary"></i>{{ $application->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @if($application->referrer)
                        <div class="mb-3">
                            <label class="text-muted small fw-bold d-block">Referred By</label>
                            <span class="fw-bold text-info"><i class="fa fa-user me-2"></i>{{ $application->referrer->name }}</span>
                        </div>
                        @endif
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        @if($application->status === 'Applied')
                        <button type="button" class="btn btn-success w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#convertModal">
                            <i class="fa fa-check-circle me-2"></i>Shortlist
                        </button>
                        @endif
                        <a href="{{ route('job-vacancies.analytics', $application->job_vacancy_id) }}" class="btn btn-outline-secondary w-100">
                            <i class="fa fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Professional Details & Files -->
        <div class="col-xl-9 col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 text-primary fw-bold text-uppercase">Professional Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label class="text-muted small">Qualification</label>
                            <p class="fw-bold border-bottom pb-2">{{ $application->educational_qualification ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="text-muted small">Total Experience</label>
                            <p class="fw-bold border-bottom pb-2">{{ $application->years_of_experience ? $application->years_of_experience . ' Years' : 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="text-muted small">Current Employer</label>
                            <p class="fw-bold border-bottom pb-2">{{ $application->current_employer ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-4 mb-4">
                            <label class="text-muted small">Current CTC</label>
                            <p class="fw-bold border-bottom pb-2">{{ $application->last_current_ctc ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="text-muted small">Expected CTC</label>
                            <p class="fw-bold border-bottom pb-2">{{ $application->expected_ctc ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="text-muted small">Notice Period</label>
                            <p class="fw-bold border-bottom pb-2">{{ $application->notice_period ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Form Responses & Files -->
            @if($application->custom_form_responses && count($application->custom_form_responses) > 0)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 text-primary fw-bold text-uppercase">Additional Information & Files</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($application->custom_form_responses as $response)
                        <div class="col-md-6 mb-4">
                            <div class="p-3 bg-light rounded border h-100 position-relative">
                                <label class="text-muted small d-block mb-2 fw-bold text-uppercase">{{ $response['label'] }}</label>

                                @if(isset($response['value']) && is_string($response['value']) && (str_contains($response['value'], 'uploads/') || str_contains($response['value'], 'storage/')))
                                @php
                                $extension = pathinfo($response['value'], PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                $isPdf = strtolower($extension) === 'pdf';
                                @endphp

                                <div class="mt-2">
                                    @if($isImage)
                                    <div class="mb-2 text-center bg-white p-2 border rounded">
                                        <a href="{{ asset($response['value']) }}" target="_blank">
                                            <img src="{{ asset($response['value']) }}" alt="Attachment" class="img-fluid rounded" style="max-height: 120px;">
                                        </a>
                                    </div>
                                    <a href="{{ asset($response['value']) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="fa fa-eye me-1"></i> View Full Image
                                    </a>
                                    @elseif($isPdf)
                                    <div class="d-flex align-items-center mb-3 bg-white p-2 border rounded">
                                        <i class="fa fa-file-pdf-o text-danger fa-2x me-3"></i>
                                        <span class="text-dark fw-bold text-truncate" style="max-width: 200px;">{{ basename($response['value']) }}</span>
                                    </div>
                                    <a href="{{ asset($response['value']) }}" target="_blank" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="fa fa-file-pdf-o me-1"></i> View PDF
                                    </a>
                                    @else
                                    <div class="d-flex align-items-center mb-3 bg-white p-2 border rounded">
                                        <i class="fa fa-file-text-o text-secondary fa-2x me-3"></i>
                                        <span class="text-dark fw-bold text-truncate" style="max-width: 200px;">{{ basename($response['value']) }}</span>
                                    </div>
                                    <a href="{{ asset($response['value']) }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="fa fa-download me-1"></i> Download File
                                    </a>
                                    @endif
                                </div>
                                @else
                                <div class="bg-white p-2 border rounded">
                                    <span class="fw-bold text-dark fs-6">
                                        @if(is_array($response['value']))
                                        {{ implode(', ', $response['value']) }}
                                        @else
                                        {{ $response['value'] ?? 'N/A' }}
                                        @endif
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection