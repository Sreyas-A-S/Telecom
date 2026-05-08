@extends('layouts.admin')

@section('title', 'Task Details')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Task Details</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">Reports</li>
                    <li class="breadcrumb-item"><a href="{{ route('task-reports.index') }}">Task Reports</a></li>
                    <li class="breadcrumb-item active">Task Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid mb-5 pb-5">
    <div class="row">
        <!-- Task Overview -->
        <div class="col-sm-12">
            <div class="card shadow-sm border-0">
                <div class="card-header pb-0 border-0 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-tasks me-2 text-primary"></i>Task: <span class="text-muted">{{ $task->title }}</span></h5>
                        <div>
                            @php
                                $status = strtolower($task->derived_status);
                                $badgeClass = 'bg-light text-dark';
                                if ($status === 'ongoing') $badgeClass = 'bg-primary';
                                else if (str_contains($status, 'settled')) $badgeClass = 'bg-success';
                                else if (str_contains($status, 'approved')) $badgeClass = 'bg-success';
                                else if (str_contains($status, 'awaiting approval')) $badgeClass = 'bg-warning';
                                else if (str_contains($status, 'rejected')) $badgeClass = 'bg-danger';
                                else if ($status === 'pending') $badgeClass = 'bg-warning text-dark';
                                else if ($status === 'hold') $badgeClass = 'bg-secondary';
                                else if (str_starts_with($status, 'completed')) $badgeClass = 'bg-success';
                                else if ($status === 'cancelled') $badgeClass = 'bg-danger';
                            @endphp
                            <span class="badge {{ $badgeClass }} p-2 px-3 rounded-pill">{{ strtoupper($task->derived_status) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body mt-2">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100 bg-light-subtle">
                                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fa fa-info-circle me-2"></i>General Information</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th class="text-muted fw-normal" width="40%">Type:</th>
                                        <td class="fw-bold">{{ $task->task_type_label }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted fw-normal">Created Date:</th>
                                        <td>{{ $task->created_at->format('d M, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted fw-normal">Due Date:</th>
                                        <td>
                                            @if($task->due_date)
                                                <span class="{{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed' ? 'text-danger fw-bold' : '' }}">
                                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d M, Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted fw-normal">Elapsed Time:</th>
                                        <td class="text-info fw-bold">{{ $task->getFormattedElapsedTime() }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100 bg-light-subtle">
                                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fa fa-user me-2"></i>Assignment</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th class="text-muted fw-normal" width="40%">Current Employee:</th>
                                        <td class="fw-bold">{{ $task->assignedEmployee ? $task->assignedEmployee->name : 'Unassigned' }}</td>
                                    </tr>
                                    @php
                                        $assignedLogs = $task->taskLogs->where('action_type', 'assigned')->sortBy('action_time');
                                        $uniqueEngineers = $assignedLogs->pluck('employee.name')->unique()->filter(function($name) use ($task) {
                                            return $name !== ($task->assignedEmployee->name ?? '');
                                        });
                                    @endphp
                                    @if($uniqueEngineers->count() > 0)
                                        <tr>
                                            <th class="text-muted fw-normal">Previous Engineers:</th>
                                            <td class="small">
                                                @foreach($uniqueEngineers as $name)
                                                    <span class="d-block text-muted">{{ $name }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th class="text-muted fw-normal">Dealership:</th>
                                        <td>{{ $task->dealership ? $task->dealership->name : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted fw-normal">Location:</th>
                                        <td><i class="fa fa-map-marker-alt text-danger me-1"></i> {{ $task->location ?: 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 h-100 bg-light-subtle">
                                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fa fa-link me-2"></i>Related Entry</h6>
                                @if($task->lead_id && $task->lead)
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Client Name</small>
                                        <span class="fw-bold text-primary">{{ $task->lead->client->name ?? $task->lead->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Lead Name</small>
                                        <span class="fw-bold">{{ $task->lead->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Phone</small>
                                        <span class="fw-bold"><i class="fa fa-phone me-1 text-success"></i> {{ $task->lead->phone_number ?? 'N/A' }}</span>
                                        @if($task->lead->alternate_contact_number)
                                            <span class="d-block small text-muted mt-1"><i class="fa fa-phone me-1"></i> {{ $task->lead->alternate_contact_number }} (Alt)</span>
                                        @endif
                                    </div>
                                @elseif($task->is_service && $task->entry)
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Client Name</small>
                                        <span class="fw-bold text-primary">{{ $task->entry->client->name ?? $task->entry->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Service Complaint</small>
                                        <span class="fw-bold">{{ $task->entry->name ?? $task->entry->referral_id ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Contact Info</small>
                                        <span class="fw-bold"><i class="fa fa-phone me-1 text-success"></i> {{ $task->entry->contact_info ?? 'N/A' }}</span>
                                    </div>
                                @else
                                    <p class="text-muted">No related entry</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($task->fsrReport)
                        <div id="fsr-details" class="mt-4 p-4 rounded-3 border" style="background-color: #f0f7ff;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0 text-dark d-flex align-items-center">
                                    <span class="bg-primary text-white p-2 rounded-3 me-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-clipboard-check"></i>
                                    </span>
                                    Field Service Report (FSR) Details
                                </h5>
                                <div>
                                    <a href="{{ route('task-reports.export-fsr-pdf', $task->id) }}" class="btn btn-sm btn-outline-danger d-print-none me-2">
                                        <i class="fa fa-file-pdf me-1"></i> Download FSR (PDF)
                                    </a>
                                    <button onclick="window.print()" class="btn btn-sm btn-outline-primary d-print-none">
                                        <i class="fa fa-print me-1"></i> Print View
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="bg-white p-3 rounded shadow-sm h-100">
                                        <h6 class="text-muted small text-uppercase fw-bold"><i class="fa fa-search me-2"></i>On-Site Assessment</h6>
                                        <p class="mb-0 text-dark" style="white-space: pre-wrap;">{{ $task->fsrReport->on_site_assessment }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="bg-white p-3 rounded shadow-sm h-100">
                                        <h6 class="text-muted small text-uppercase fw-bold"><i class="fa fa-lightbulb me-2"></i>Analysis of Cause</h6>
                                        <p class="mb-0 text-dark" style="white-space: pre-wrap;">{{ $task->fsrReport->analysis_of_cause }}</p>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="bg-white p-3 rounded shadow-sm">
                                        <h6 class="text-muted small text-uppercase fw-bold"><i class="fa fa-wrench me-2"></i>Actions Taken</h6>
                                        <p class="mb-0 text-dark" style="white-space: pre-wrap;">{{ $task->fsrReport->actions_taken }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($task->lead_id && $task->lead && $task->lead->items->isNotEmpty())
                            <!-- Lead Products Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="bg-white p-3 rounded shadow-sm mb-3">
                                        <h6 class="text-muted small text-uppercase fw-bold mb-3"><i class="fa fa-cubes me-2"></i>Lead Products Info</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Model</th>
                                                        <th>Series</th>
                                                        <th>Serial Numbers</th>
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-end">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($task->lead->items as $item)
                                                        <tr>
                                                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                                                            <td>{{ $item->productModel->name ?? 'N/A' }}</td>
                                                            <td>{{ $item->modelSeries->name ?? 'N/A' }}</td>
                                                            <td class="small">
                                                                @if($item->machine_serial_number) <div><strong>M:</strong> {{ $item->machine_serial_number }}</div> @endif
                                                                @if($item->engine_serial_number) <div><strong>E:</strong> {{ $item->engine_serial_number }}</div> @endif
                                                            </td>
                                                            <td class="text-center">{{ $item->quantity }}</td>
                                                            <td class="text-end">{{ $item->price ? number_format($item->price, 2) : 'N/A' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Parts Quotation Section -->
                            @if($task->lead_id)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="bg-white p-3 rounded shadow-sm mb-3">
                                        <h6 class="text-muted small text-uppercase fw-bold mb-3"><i class="fa fa-boxes me-2"></i>Parts Quotation</h6>
                                        @if($task->fsrReport->partQuotations->isNotEmpty())
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Part Number</th>
                                                            <th>Material Description</th>
                                                            <th class="text-center">Qty</th>
                                                            <th class="text-end">Unit Price</th>
                                                            <th class="text-center">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($task->fsrReport->partQuotations as $quotation)
                                                            <tr>
                                                                <td class="fw-bold">{{ $quotation->part->part_number ?? 'N/A' }}</td>
                                                                <td>{{ $quotation->part->material_description ?? 'N/A' }}</td>
                                                                <td class="text-center">{{ $quotation->quoted_quantity }}</td>
                                                                <td class="text-end">{{ number_format($quotation->quoted_unit_price, 2) }}</td>
                                                                <td class="text-center">
                                                                    <span class="badge {{ $quotation->status === 'approved' ? 'bg-success' : ($quotation->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}-subtle text-{{ $quotation->status === 'approved' ? 'success' : ($quotation->status === 'rejected' ? 'danger' : 'warning') }} border px-2">
                                                                        {{ ucfirst($quotation->status) }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted italic mb-0">No parts quoted for this service.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Payment & Collections Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="bg-white p-3 rounded shadow-sm mb-3">
                                        <h6 class="text-muted small text-uppercase fw-bold mb-3"><i class="fa fa-money-bill-wave me-2"></i>Payment & Collections</h6>
                                        
                                        <div class="row mb-4 text-center g-3">
                                            @php
                                                $totalPrice = $task->lead_id ? ($task->lead->lead_value ?? 0) : ($task->entry->price ?? 0);
                                                $priceLabel = $task->lead_id ? 'Lead Value' : 'Service Price';
                                            @endphp
                                            <div class="col-md-4">
                                                <div class="p-2 border rounded bg-light">
                                                    <small class="text-muted d-block">{{ $priceLabel }}</small>
                                                    <span class="h5 mb-0 fw-bold">{{ number_format($totalPrice, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="p-2 border rounded bg-success-subtle">
                                                    <small class="text-success d-block">Total Collected</small>
                                                    <span class="h5 mb-0 fw-bold text-success">{{ number_format($task->fsrReport->paymentHistory->sum('amount'), 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="p-2 border rounded bg-danger-subtle">
                                                    <small class="text-danger d-block">Balance Due</small>
                                                    <span class="h5 mb-0 fw-bold text-danger">{{ number_format($totalPrice - $task->fsrReport->paymentHistory->sum('amount'), 2) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if($task->fsrReport->paymentHistory->isNotEmpty())
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th class="text-end">Amount</th>
                                                            <th class="text-center">Mode</th>
                                                            <th>Collected By</th>
                                                            <th>Remarks</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($task->fsrReport->paymentHistory as $payment)
                                                            <tr>
                                                                <td class="small">{{ $payment->collected_at->format('d M Y, H:i') }}</td>
                                                                <td class="text-end fw-bold">{{ number_format($payment->amount, 2) }}</td>
                                                                <td class="text-center">
                                                                    <span class="badge bg-secondary-subtle text-secondary border px-2">{{ ucfirst($payment->payment_mode) }}</span>
                                                                </td>
                                                                <td class="small">{{ $payment->collectedBy->name ?? 'N/A' }}</td>
                                                                <td class="small text-muted">{{ $payment->remarks ?: '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted italic mb-0">No payment records found.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2 align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex gap-4">
                                        <div>
                                            <small class="text-muted d-block">FSR Status</small>
                                            @php
                                                $fsrStatus = $task->fsrReport->status;
                                                $fsrBadge = $fsrStatus === 'approved' ? 'bg-success' : ($fsrStatus === 'rejected' ? 'bg-danger' : 'bg-warning');
                                            @endphp
                                            <span class="badge {{ $fsrBadge }} rounded-pill">{{ strtoupper($fsrStatus) }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Payment Status</small>
                                            <span class="badge {{ $task->fsrReport->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }} rounded-pill">{{ strtoupper($task->fsrReport->payment_status) }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Submitted By</small>
                                            <span class="fw-bold"><i class="fa fa-user-check me-1"></i> {{ $task->fsrReport->submittedBy->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($task->fsrReport->images) && count($task->fsrReport->images) > 0)
                                    <div class="col-md-12 mt-4">
                                        <h6 class="text-muted small text-uppercase fw-bold mb-3"><i class="fa fa-images me-2"></i>Captured Images</h6>
                                        <div class="d-flex flex-wrap gap-3">
                                            @foreach($task->fsrReport->image_urls as $url)
                                                <div class="image-wrapper position-relative">
                                                    <a href="{{ $url }}" target="_blank" class="d-block">
                                                        <img src="{{ $url }}" class="img-thumbnail hover-shadow" style="width: 120px; height: 120px; object-fit: cover; transition: all 0.3s;">
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($task->description)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="p-3 bg-light border-start border-primary border-4 rounded">
                                    <h6 class="fw-bold mb-1">Task Description</h6>
                                    <p class="text-muted mb-0">{{ $task->description }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Follow-ups -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-white pb-0">
                    <h5 class="mb-0 text-primary"><i class="fa fa-history me-2"></i>Work History / Follow-ups</h5>
                </div>
                <div class="card-body">
                    @if($task->followups->count() > 0)
                        <div class="timeline-v2 mt-2">
                            @foreach($task->followups->sortByDesc('created_at') as $followup)
                                <div class="timeline-item pb-4 position-relative">
                                    <div class="timeline-marker position-absolute rounded-circle bg-primary" style="width: 12px; height: 12px; left: -6px; top: 5px; border: 2px solid #fff; box-shadow: 0 0 0 2px var(--bs-primary);"></div>
                                    <div class="ms-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-dark">{{ $followup->created_at->format('d M, Y H:i') }}</span>
                                            <span class="badge bg-light text-muted border">{{ $followup->user->name ?? 'Unknown' }}</span>
                                        </div>
                                        <div class="p-3 bg-light rounded-3 shadow-sm-hover transition-all">
                                            <p class="mb-0 text-muted">{{ $followup->notes }}</p>
                                            @if($followup->images && is_array($followup->images))
                                                <div class="mt-3 d-flex gap-2 flex-wrap">
                                                    @foreach($followup->images as $image)
                                                        <a href="{{ asset($image) }}" target="_blank" class="d-inline-block">
                                                            <img src="{{ asset($image) }}" class="img-thumbnail shadow-sm" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px;">
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-comment-slash fa-3x text-light mb-3"></i>
                            <p class="text-muted">No follow-ups recorded for this task.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Task Logs -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 bg-white pb-0">
                    <h5 class="mb-0 text-primary"><i class="fa fa-list-alt me-2"></i>Task Activity Logs</h5>
                </div>
                <div class="card-body">
                    @if($task->taskLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Time</th>
                                        <th class="border-0">Action</th>
                                        <th class="border-0">Performed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($task->taskLogs->sortByDesc('action_time') as $log)
                                        <tr>
                                            <td class="small text-muted">{{ $log->action_time->format('d M, H:i') }}</td>
                                            <td>
                                                @php
                                                    $action = str_replace('_', ' ', $log->action_type);
                                                    $actionColor = match($log->action_type) {
                                                        'created' => 'primary',
                                                        'started', 'resumed' => 'success',
                                                        'paused', 'stopped' => 'warning',
                                                        'completed' => 'info',
                                                        'assigned' => 'dark',
                                                        'updated' => 'secondary',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $actionColor }}-subtle text-{{ $actionColor }} border border-{{ $actionColor }} text-capitalize px-2">
                                                    {{ $action }}
                                                </span>
                                            </td>
                                            <td class="fw-bold small">{{ $log->employee->name ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-clipboard-list fa-3x text-light mb-3"></i>
                            <p class="text-muted">No activity logs found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-primary-subtle { background-color: rgba(var(--bs-primary-rgb), 0.1) !important; }
    .bg-success-subtle { background-color: rgba(var(--bs-success-rgb), 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(var(--bs-warning-rgb), 0.1) !important; }
    .bg-info-subtle { background-color: rgba(var(--bs-info-rgb), 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(var(--bs-danger-rgb), 0.1) !important; }
    .bg-dark-subtle { background-color: rgba(33, 37, 41, 0.1) !important; }
    
    .text-primary { color: var(--bs-primary) !important; }
    .text-success { color: var(--bs-success) !important; }
    .text-warning { color: var(--bs-warning) !important; }
    .text-info { color: var(--bs-info) !important; }
    .text-danger { color: var(--bs-danger) !important; }
    
    .hover-shadow:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        z-index: 5;
    }
    
    .timeline-v2 {
        border-left: 2px solid #eef0f2;
        margin-left: 10px;
    }
    
    .timeline-item:last-child {
        border-left: none;
    }
    
    .shadow-sm-hover:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
        background-color: #fff !important;
    }
    
    .transition-all {
        transition: all 0.3s ease;
    }
    
    .btn-pill {
        border-radius: 50px;
    }
    
    .bg-light-subtle {
        background-color: #fcfdfe;
    }
    
    .card {
        transition: transform 0.2s;
    }
</style>
@endpush
