@extends('layouts.admin')

@section('title', 'Job Vacancy Analytics')

@section('breadcrumb')
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card header-card" style="border: none; background: transparent; margin-bottom: 25px; padding: 20px 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1" style="font-weight: 700; color: #2b2b2b;">Vacancy Performance</h3>
                        <p class="text-muted mb-0">Detailed engagement metrics for: <strong>{{ $vacancy->title }}</strong></p>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-{{ $vacancy->status == 'Open' ? 'success' : 'secondary' }} p-2 px-3" style="font-size: 0.9rem;">{{ $vacancy->status }}</div>
                        <div class="mt-2 small text-muted">Created: {{ $vacancy->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 box-col-6">
            <div class="card widget-1">
                <div class="card-body">
                    <div class="widget-content">
                        <div class="widget-round primary">
                            <div class="bg-round">
                                <svg class="svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-form') }}"></use>
                                </svg>
                                <svg class="half-circle svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#halfcircle') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="count-up" data-count="{{ count($applications) }}">0</h4><span class="f-light">Total Applications</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 box-col-6">
            <div class="card widget-1">
                <div class="card-body">
                    <div class="widget-content">
                        <div class="widget-round info">
                            <div class="bg-round">
                                <svg class="svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-job-search') }}"></use>
                                </svg>
                                <svg class="half-circle svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#halfcircle') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="count-up" data-count="{{ $vacancy->views_count }}">0</h4><span class="f-light">Total Views</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 box-col-6">
            <div class="card widget-1">
                <div class="card-body">
                    <div class="widget-content">
                        <div class="widget-round success">
                            <div class="bg-round">
                                <svg class="svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#rate') }}"></use>
                                </svg>
                                <svg class="half-circle svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#halfcircle') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="count-up" data-count="{{ $uniqueViews }}">0</h4><span class="f-light">Unique Views</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 box-col-6">
            <div class="card widget-1">
                <div class="card-body">
                    <div class="widget-content">
                        <div class="widget-round warning">
                            <div class="bg-round">
                                <svg class="svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-bookmark') }}"></use>
                                </svg>
                                <svg class="half-circle svg-fill">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#halfcircle') }}"></use>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="count-up" data-count="{{ $totalCopies }}">0</h4><span class="f-light">Link Copies</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs tab-card-header mb-4" id="vacancyTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="applications-tab" data-bs-toggle="tab" href="#applications" role="tab" aria-controls="applications" aria-selected="true">Submitted Applications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="analytics-tab" data-bs-toggle="tab" href="#analytics" role="tab" aria-controls="analytics" aria-selected="false">Analytics & Engagement</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="vacancyTabContent">
                        <div class="tab-pane fade show active" id="applications" role="tabpanel" aria-labelledby="applications-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="applicationsTable" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Candidate Name</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Experience</th>
                                            <th>Applied Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($applications as $app)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $app->candidate_name }}</td>
                                            <td>{{ $app->email_id }}</td>
                                            <td>{{ $app->contact_number }}</td>
                                            <td>{{ $app->years_of_experience ?? 0 }} Years</td>
                                            <td>{{ $app->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <a href="{{ route('job-vacancies.applications.show', $app->id) }}" class="btn btn-sm btn-info" target="_blank">View Application</a>
                                                    <select class="form-select form-select-sm status-change-dropdown @if($app->status == 'Shortlisted') bg-success text-white @elseif($app->status == 'Rejected') bg-danger text-white @else bg-light @endif" data-app-id="{{ $app->id }}" style="width: 130px; font-size: 11px;">
                                                        <option value="Applied" {{ $app->status == 'Applied' ? 'selected' : '' }}>Applied</option>
                                                        <option value="Shortlisted" {{ $app->status == 'Shortlisted' ? 'selected' : '' }}>Shortlisted</option>
                                                        <option value="Rejected" {{ $app->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                                        <option value="On Hold" {{ $app->status == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Referral Performance</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="referralTable" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Sl No</th>
                                                    <th>Referrer Name</th>
                                                    <th>Views Generated</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topReferrers as $ref)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $ref->referrer ? $ref->referrer->name : 'Unknown User' }}</td>
                                                    <td>{{ $ref->count }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="mb-3">Recent "Copy Link" Activity</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="copyActivityTable" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Sl No</th>
                                                    <th>User</th>
                                                    <th>Date & Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($copiers as $activity)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if($activity->user)
                                                        {{ $activity->user->name }}
                                                        @else
                                                        Unknown
                                                        @endif
                                                    </td>
                                                    <td>{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Count up animation
        $('.count-up').each(function() {
            var $this = $(this);
            var countTo = $this.attr('data-count');
            $({
                countNum: $this.text()
            }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(this.countNum);
                }
            });
        });



        var referralTable = $('#referralTable').DataTable({
            "order": [
                [2, "desc"]
            ],
            "pageLength": 10,
            "language": {
                "emptyTable": "No referrals tracked yet."
            },
            "columnDefs": [{
                "orderable": false,
                "targets": 0
            }]
        });

        var copyActivityTable = $('#copyActivityTable').DataTable({
            "order": [
                [2, "desc"]
            ],
            "pageLength": 10,
            "language": {
                "emptyTable": "No activity recorded."
            },
            "columnDefs": [{
                "orderable": false,
                "targets": 0
            }]
        });

        var applicationsTable = $('#applicationsTable').DataTable({
            "order": [
                [5, "desc"]
            ],
            "pageLength": 10,
            "language": {
                "emptyTable": "No applications submitted yet."
            },
            "columnDefs": [{
                "orderable": false,
                "targets": [0, 6]
            }]
        });

        // Fix DataTable layout on tab switch
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            referralTable.columns.adjust().draw();
            copyActivityTable.columns.adjust().draw();
            applicationsTable.columns.adjust().draw();
        });

        $(document).on('change', '.status-change-dropdown', function() {
            var $this = $(this);
            var appId = $this.data('app-id');
            var status = $this.val();

            // Show loading state or confirm if Shortlisted
            if (status === 'Shortlisted') {
                if (!confirm('Changing status to Shortlisted will automatically create an interview record for this candidate. Do you want to proceed?')) {
                    location.reload(); // Reset to previous state
                    return;
                }
            }

            // Remove old classes
            $this.removeClass('bg-success bg-danger bg-light text-white');

            // Add new class based on status
            if (status === 'Shortlisted') {
                $this.addClass('bg-success text-white');
            } else if (status === 'Rejected') {
                $this.addClass('bg-danger text-white');
            } else {
                $this.addClass('bg-light');
            }

            $.ajax({
                url: "{{ url('job-vacancies/applications') }}/" + appId + "/update-status",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    status: status
                },
                success: function(response) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.success);
                    } else {
                        alert(response.success);
                    }

                    if (status === 'Shortlisted') {
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to update status.');
                    } else {
                        alert('Failed to update status.');
                    }
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endpush