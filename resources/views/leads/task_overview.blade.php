@extends('layouts.admin')

@section('title', 'Task Overview')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
@endpush

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Task Overview: {{ $task->title }}</h3>
            </div>
            <div class="col-6">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('leads.task-overview.export-excel', ['lead' => $lead->id, 'task' => $task->id]) }}" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                    <a href="{{ route('leads.task-overview.export-pdf', ['lead' => $lead->id, 'task' => $task->id]) }}" class="btn btn-danger btn-sm" target="_blank"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
                    <ol class="breadcrumb mb-0 align-items-center">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                        @if(isset($lead))
                        <li class="breadcrumb-item"><a href="{{ route('leads.profile', $lead->id) }}">Lead Profile</a></li>
                        @elseif($task->lead_id)
                        <li class="breadcrumb-item"><a href="{{ route('leads.profile', $task->lead_id) }}">Lead Profile</a></li>
                        @endif
                        <li class="breadcrumb-item active">Task Overview</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="">
    <!-- Task Details Card -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Task Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Description:</strong> {{ $task->description }}</p>
                    <p><strong>Current Engineer:</strong> {{ $task->assignedEmployee->name ?? 'Unassigned' }}</p>
                    
                    @php
                        $assignedLogs = $task->taskLogs->where('action_type', 'assigned')->sortBy('action_time');
                        $uniqueEngineers = $assignedLogs->pluck('employee.name')->unique()->filter(function($name) use ($task) {
                            return $name !== ($task->assignedEmployee->name ?? '');
                        });
                    @endphp
                    
                    @if($uniqueEngineers->count() > 0)
                        <p><strong>Previous Engineers:</strong> 
                            <span class="text-muted small">
                                {{ $uniqueEngineers->implode(', ') }}
                            </span>
                        </p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong>
                        @php
                        $statusClass = match($task->status) {
                        'pending' => 'bg-warning',
                        'in_progress' => 'bg-info',
                        'completed' => 'bg-success',
                        'hold' => 'bg-danger',
                        'stopped' => 'bg-secondary',
                        default => 'bg-primary',
                        };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $task->status)) }}</span>
                    </p>
                    <p><strong>Due Date:</strong> {{ $task->due_date ? $task->due_date->format('d-m-Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FSR Details Card -->
    @if($task->fsrReport)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Field Service Report (FSR)</span>
            <div class="d-flex gap-2">
                @php
                $fsrStatusClass = match($task->fsrReport->status) {
                'pending' => 'bg-warning',
                'approved' => 'bg-success',
                'rejected' => 'bg-danger',
                default => 'bg-secondary',
                };
                $paymentStatusClass = $task->fsrReport->payment_status === 'paid' ? 'bg-success' : 'bg-warning';
                @endphp
                <span class="badge {{ $fsrStatusClass }}">FSR: {{ ucfirst($task->fsrReport->status ?? 'pending') }}</span>
                @if($task->fsrReport->payment_status)
                <span class="badge {{ $paymentStatusClass }}">Payment: {{ ucfirst($task->fsrReport->payment_status) }}</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>On-site Assessment:</strong></p>
                    <p class="text-muted">{{ $task->fsrReport->on_site_assessment ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Analysis of Cause:</strong></p>
                    <p class="text-muted">{{ $task->fsrReport->analysis_of_cause ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Actions Taken:</strong></p>
                    <p class="text-muted">{{ $task->fsrReport->actions_taken ?? 'N/A' }}</p>
                </div>
            </div>

            @if(!empty($task->fsrReport->images) && is_array($task->fsrReport->images))
            <hr>
            <p><strong>FSR Images:</strong></p>
            <div class="d-flex flex-wrap gap-2">
                @foreach($task->fsrReport->images as $img)
                <a href="{{ asset('storage/' . $img) }}" data-lightbox="fsr-{{ $task->fsrReport->id }}" data-title="FSR Image">
                    <img src="{{ asset('storage/' . $img) }}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Parts Quotation Card -->
    @if($task->fsrReport->partQuotations->count() > 0)
    <div class="card mb-4">
        <div class="card-header">Parts Quotation</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($task->fsrReport->partQuotations as $quotation)
                        <tr>
                            <td>{{ $quotation->part->part_number ?? 'N/A' }}</td>
                            <td>{{ $quotation->part->material_description ?? 'N/A' }}</td>
                            <td>{{ $quotation->quoted_quantity }}</td>
                            <td>{{ number_format($quotation->quoted_unit_price, 2) }}</td>
                            <td>
                                @php
                                $badgeClass = match($quotation->status) {
                                'pending' => 'bg-warning',
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger',
                                default => 'bg-secondary',
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($quotation->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Analytics & Activity Log -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">Task Analytics & Activity Log</div>
                <div class="card-body">
                    <div class="mb-3">
                        <h4>Total Time Contributed: <span class="text-primary">{{ $totalTime ?? '0 hrs 0 mins' }}</span></h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="activity-log-table">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Action</th>
                                    <th>Performed By</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taskLogs as $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ ucfirst($log->action_type) }}</td>
                                    <td>{{ $log->employee->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($log->action_time)->format('d M Y, h:i A') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No activity logs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-ups Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            Follow-up History
            <!-- View-Only: No Add Button here as it is an overview page -->
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="overview-followups-table">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Notes</th>
                            <th>By</th>
                            <th>Date</th>
                            <th>Images</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($task->followups as $followup)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $followup->notes }}</td>
                            <td>{{ $followup->user->name ?? 'Unknown' }}</td>
                            <td>{{ $followup->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                @if(!empty($followup->images) && is_array($followup->images))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($followup->images as $img)
                                    <a href="{{ asset($img) }}" data-lightbox="followup-{{ $followup->id }}" data-title="Follow-up Image">
                                        <img src="{{ asset($img) }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                    </a>
                                    @endforeach
                                </div>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No follow-ups recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Images Modal -->
<div class="modal fade" id="viewImagesModal" tabindex="-1" aria-labelledby="viewImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewImagesModalLabel">Follow-up Images</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="followupImagesCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner" id="carousel-inner-images"></div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#followupImagesCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#followupImagesCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>
    $(document).ready(function() {
        // Simple DataTable for logs if needed, or just basic styling
        // $('#activity-log-table').DataTable(); 

        // View Images button click handler
        $('.view-images-btn').on('click', function() {
            var images = $(this).data('images');

            var carouselInner = $('#carousel-inner-images');
            carouselInner.empty();

            if (images && images.length > 0) {
                images.forEach(function(image, index) {
                    var activeClass = index === 0 ? 'active' : '';
                    carouselInner.append(`
                        <div class="carousel-item ${activeClass}">
                            <img src="/${image}" class="d-block w-100 carousel-image" alt="Follow-up Image">
                            <a href="/${image}" target="_blank" class="btn btn-light btn-sm open-image-new-tab" title="Open in new tab">
                                <i class="icon-maximize"></i>
                            </a>
                        </div>
                    `);
                });
                $('#viewImagesModal').modal('show');
            } else {
                alert('No images available for this follow-up.');
            }
        });
    });
</script>
<style>
    .carousel-image {
        max-height: 500px;
        width: auto;
        object-fit: contain;
        margin: 0 auto;
    }

    .carousel-item {
        text-align: center;
        position: relative;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        filter: invert(1) grayscale(100%);
    }

    .open-image-new-tab {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }
</style>
@endpush