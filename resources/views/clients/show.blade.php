@extends('layouts.admin')

@section('title', 'Client Profile - ' . $client->name)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
<style>
    /* Premium Design System Overrides */
    .client-profile-container {
        background: #f8fafc url("data:image/svg+xml,%3Csvgwidth='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236366f1' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        padding: 30px;
        border-radius: 30px;
        min-height: 100vh;
        position: relative;
    }

    .client-profile-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 35px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-icon {
        width: 120px;
        height: 120px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 800;
        border: 2px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Vibrant Stats Cards - Refined */
    .vibrant-stats-card {
        border-radius: 24px !important;
        padding: 25px !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative;
        overflow: hidden;
        min-height: 120px;
        display: flex;
        align-items: center;
        border: none !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        cursor: default;
    }

    .vibrant-stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15) !important;
    }

    .vibrant-stats-card::before {
        content: '';
        position: absolute;
        top: -10%;
        left: -10%;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(20px);
    }

    .vibrant-stats-card::after {
        content: '';
        position: absolute;
        bottom: -20%;
        right: -10%;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        filter: blur(30px);
    }

    .vibrant-stats-card .stats-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        z-index: 2;
        color: white;
    }

    .vibrant-stats-card .stats-info {
        z-index: 2;
        position: relative;
    }

    .vs-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    }

    .vs-success {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
    }

    .vs-warning {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
    }

    .vs-info {
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
    }

    .stats-info h3 {
        font-size: 28px;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .hover-underline:hover {
        text-decoration: underline !important;
        cursor: pointer;
    }

    .ls-1 {
        letter-spacing: 1px;
    }

    .nav-tabs-custom {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        padding: 8px;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        display: inline-flex;
        gap: 8px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    .nav-tabs-custom .nav-link {
        border: none;
        border-radius: 14px;
        color: #64748b;
        font-weight: 700;
        padding: 12px 28px;
        transition: all 0.3s;
        font-size: 14px;
        display: flex;
        align-items: center;
    }

    .nav-tabs-custom .nav-link:hover {
        background: rgba(255, 255, 255, 0.8);
        color: #1e293b;
    }

    .nav-tabs-custom .nav-link.active i {
        color: #4f46e5;
    }

    .nav-tabs-custom .nav-link i {
        font-size: 16px;
        transition: transform 0.3s;
    }

    .nav-tabs-custom .nav-link:hover i {
        transform: scale(1.2);
    }

    .glass-table-card {
        background: #ffffff !important;
        border-radius: 28px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05) !important;
        overflow: hidden !important;
        padding: 0 !important;
        position: relative;
    }

    .table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1px;
        padding: 20px;
        border-bottom: 2px solid #f1f5f9;
    }

    .table tbody td {
        padding: 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #334155;
    }

    .status-badge {
        font-size: 10px;
        padding: 6px 14px;
        border-radius: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .lead-row:hover,
    .service-row:hover {
        background-color: #f8fafc;
        cursor: pointer;
    }

    .lead-row.expanded,
    .service-row.expanded {
        background-color: #f1f5f9 !important;
        border-left: 5px solid #4f46e5;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .read-more-link {
        color: #4f46e5;
        text-decoration: none;
        font-weight: 700;
        font-size: 12px;
        margin-left: 5px;
        cursor: pointer;
    }

    .read-more-link:hover {
        text-decoration: underline;
    }

    .text-wrapped {
        white-space: pre-wrap;
        word-break: break-word;
    }

    .blurred-product {
        filter: blur(4px) grayscale(80%);
        opacity: 0.5;
        pointer-events: none;
        user-select: none;
        transition: all 0.3s ease;
    }

    .blurred-product:hover {
        filter: blur(0px) grayscale(0%);
        opacity: 1;
    }
</style>
@endpush

@section('content')

<div class="client-profile-container">
    <!-- Header Section -->
    <div class="client-profile-header">
        <div class="row align-items-center">
            <div class="col-md-auto mb-3 mb-md-0">
                <div class="profile-icon">
                    @if($client->profile_pic)
                    <img src="{{ asset($client->profile_pic) }}" alt="{{ $client->name }}" class="w-100 h-100 rounded-3" style="object-fit: cover;">
                    @else
                    {{ substr($client->name, 0, 1) }}
                    @endif
                </div>
            </div>
            <div class="col-md">
                <h2 class="mb-2 fw-800 text-white" style="font-size: 32px;">{{ $client->salutation ? $client->salutation . ' ' : '' }}{{ $client->name }}</h2>
                <div class="d-flex flex-wrap gap-4 mt-3">
                    <span class="d-flex align-items-center text-white opacity-90">
                        <i class="fa fa-phone-alt me-2 text-info"></i>
                        <a href="tel:{{ $client->phone_number }}" class="text-white text-decoration-none hover-underline">{{ $client->phone_number }}</a>
                    </span>
                    <span class="d-flex align-items-center text-white opacity-90">
                        <i class="fa fa-envelope me-2 text-info"></i>
                        @if($client->email)
                        <a href="mailto:{{ $client->email }}" class="text-white text-decoration-none hover-underline">{{ $client->email }}</a>
                        @else
                        N/A
                        @endif
                    </span>
                    <span class="d-flex align-items-center text-white opacity-90"><i class="fa fa-map-marker-alt me-2 text-info"></i>
                        {{ $client->address ?? '' }}
                        @if($client->district || $client->state)
                        ({{ $client->district->name ?? '' }}{{ $client->district && $client->state ? ', ' : '' }}{{ $client->state->name ?? '' }})
                        @endif
                        @if(!$client->address && !$client->district && !$client->state)
                        N/A
                        @endif
                    </span>
                </div>
            </div>
            <div class="col-md-auto ms-auto d-flex flex-column gap-3">
                <a href="{{ route('clients.export.excel', $client->id) }}" class="btn btn-sm text-primary fw-800 border-0 shadow-sm px-4 py-2 rounded-pill">
                    <i class="fa fa-file-excel me-2"></i> EXPORT EXCEL
                </a>
                <a href="{{ route('clients.export.pdf', $client->id) }}" class="btn btn-sm text-danger fw-800 border-0 shadow-sm px-4 py-2 rounded-pill">
                    <i class="fa fa-file-pdf me-2"></i> EXPORT PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="vibrant-stats-card vs-primary text-white">
                <div class="stats-icon-wrapper">
                    <i class="fa fa-layer-group"></i>
                </div>
                <div class="stats-info">
                    <h3 class="mb-0 fw-800 text-white">{{ $client->leads->count() }}</h3>
                    <div class="small opacity-80 fw-700 uppercase ls-1" style="font-size: 11px;">Total Leads</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vibrant-stats-card vs-success text-white">
                <div class="stats-icon-wrapper">
                    <i class="fa fa-tools"></i>
                </div>
                <div class="stats-info">
                    <h3 class="mb-0 fw-800 text-white">{{ $services->count() }}</h3>
                    <div class="small opacity-80 fw-700 uppercase ls-1" style="font-size: 11px;">Total Services</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vibrant-stats-card vs-warning text-white">
                <div class="stats-icon-wrapper">
                    <i class="fa fa-box-open"></i>
                </div>
                <div class="stats-info">
                    <h3 class="mb-0 fw-800 text-white">{{ $uniqueProducts->count() }}</h3>
                    <div class="small opacity-80 fw-700 uppercase ls-1" style="font-size: 11px;">Total Machines</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="vibrant-stats-card vs-info text-white">
                <div class="stats-icon-wrapper">
                    <i class="fa fa-comments"></i>
                </div>
                <div class="stats-info">
                    @php
                    $totalFollowups = $client->leads->sum(function($lead) { return $lead->followups->count(); }) +
                    $services->sum(function($service) { return $service->tasks->sum(function($task) { return $task->followups->count(); }); });
                    @endphp
                    <h3 class="mb-0 fw-800 text-white">{{ $totalFollowups }}</h3>
                    <div class="small opacity-80 fw-700 uppercase ls-1" style="font-size: 11px;">Interactions</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="d-flex justify-content-center">
        <ul class="nav nav-tabs nav-tabs-custom" id="clientTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="leads-tab" data-bs-toggle="tab" href="#leads" role="tab">
                    <i class="fa fa-history me-2"></i>Leads History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="services-tab" data-bs-toggle="tab" href="#services" role="tab">
                    <i class="fa fa-concierge-bell me-2"></i>Services History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="products-tab" data-bs-toggle="tab" href="#products" role="tab">
                    <i class="fa fa-boxes me-2"></i>Owned Products
                </a>
            </li>
        </ul>
    </div>

    @php
    $userHasDealership = Auth::user()->employee && Auth::user()->employee->dealership_id;
    $userDealershipName = $userHasDealership ? (Auth::user()->employee->dealership->name ?? '') : '';
    @endphp

    <!-- Tabs Content -->
    <div class="tab-content glass-table-card" id="clientTabsContent">
        <!-- Leads Tab -->
        <div class="tab-pane fade show active" id="leads" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">Sl No</th>
                            <th>Lead ID</th>
                            <th>Date</th>
                            <th>Product/Model</th>
                            <th>Status</th>
                            <th>Value</th>
                            <th>Agent</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->leads as $lead)
                        @php
                        $isLeadBlurred = false;

                        if ($userHasDealership && !empty($userDealershipName)) {
                        $hasMatch = false;
                        if($lead->items->isNotEmpty()) {
                        foreach($lead->items as $item) {
                        if(stripos($item->product->name ?? '', $userDealershipName) !== false) {
                        $hasMatch = true;
                        break;
                        }
                        }
                        } elseif($lead->product) {
                        if(stripos($lead->product->name ?? '', $userDealershipName) !== false) {
                        $hasMatch = true;
                        }
                        }
                        if (!$hasMatch) {
                        $isLeadBlurred = true;
                        }
                        }
                        @endphp
                        <tr class="lead-row {{ $isLeadBlurred ? 'blurred-product' : '' }}" data-lead-id="{{ $lead->id }}">
                            <td class="text-center text-muted fw-800">{{ $loop->iteration }}</td>
                            @if($isLeadBlurred)
                            <td colspan="6" class="text-center text-muted i fs-12 py-3">
                                <i class="fa fa-lock me-2"></i> Private to other dealership
                            </td>
                            @else
                            <td class="fw-800 text-primary">
                                <i class="fa fa-chevron-right toggle-icon me-2 text-muted small"></i>
                                #{{ $lead->id }}
                            </td>
                            <td class="text-muted fw-600">{{ $lead->created_at->format('d M Y') }}</td>
                            <td>
                                @if($lead->items->isNotEmpty())
                                @foreach($lead->items as $item)
                                <div class="mb-2 pb-2 @if(!$loop->last) border-bottom border-light @endif">
                                    <div class="fw-800 text-dark">{{ $item->product->name ?? 'N/A' }}</div>
                                    <div class="small text-muted fw-500">
                                        {{ $item->productModel->name ?? '' }}
                                        @if($item->quantity > 1)
                                        <span class="badge bg-light text-primary border ms-1">Qty: {{ $item->quantity }}</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <div class="fw-800 text-dark">{{ $lead->product->name ?? 'N/A' }}</div>
                                <div class="small text-muted fw-500">{{ $lead->productModel->name ?? '' }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                $statusValue = $lead->status;
                                $displayStatus = str_replace('_', ' ', $lead->last_status_before_conversion ?? $lead->status);
                                $statusColor = 'bg-info';

                                if($statusValue == 'converted_to_client' || $statusValue == 'win' || $statusValue == 'won') {
                                $displayStatus = 'Win';
                                $statusColor = 'bg-success';
                                } else {
                                $checkStatus = strtolower($displayStatus);
                                if(str_contains($checkStatus, 'win') || str_contains($checkStatus, 'positive') || str_contains($checkStatus, 'concluded')) {
                                $statusColor = 'bg-success';
                                } elseif(str_contains($checkStatus, 'lost') || str_contains($checkStatus, 'dropped')) {
                                $statusColor = 'bg-danger';
                                } elseif(str_contains($checkStatus, 'hot')) {
                                $statusColor = 'bg-danger';
                                } elseif(str_contains($checkStatus, 'warm')) {
                                $statusColor = 'bg-warning';
                                } elseif(str_contains($checkStatus, 'cold')) {
                                $statusColor = 'bg-secondary';
                                }
                                }
                                @endphp
                                <span class="badge status-badge {{ $statusColor }} text-white">
                                    {{ $displayStatus }}
                                </span>
                            </td>
                            <td class="fw-800 text-dark">₹{{ number_format($lead->lead_value, 2) }}</td>
                            <td class="fw-600 text-muted">{{ $lead->agent->name ?? 'N/A' }}</td>
                            @endif
                            <td class="text-end">
                                @if(!$isLeadBlurred && $lead->status === 'converted_to_client' && checkMenu(Session::get('role_id'), 14, 'create'))
                                <button type="button" class="btn btn-xs btn-outline-warning revert-lead-btn"
                                    data-id="{{ $lead->id }}"
                                    data-is-primary="{{ $client->lead_id == $lead->id ? 'true' : 'false' }}"
                                    title="Revert Conversion">
                                    <i class="fa fa-undo"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @if(!$isLeadBlurred)
                        <tr id="followup-row-{{ $lead->id }}" class="followup-row d-none {{ $isLeadBlurred ? 'blurred-product' : '' }}">
                            <td colspan="8" class="p-0">
                                <div class="p-4 bg-light bg-opacity-50">
                                    <!-- Inner Tabs -->
                                    <ul class="nav nav-tabs inner-nav-tabs mb-4 px-0 bg-transparent" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#followups-{{ $lead->id }}" role="tab">
                                                <i class="fa fa-history me-2"></i>Follow-ups
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tasks-{{ $lead->id }}" role="tab">
                                                <i class="fa fa-tasks me-2"></i>Tasks
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content bg-transparent border-0 p-0">
                                        <!-- Follow-ups Content -->
                                        <div class="tab-pane fade show active" id="followups-{{ $lead->id }}" role="tabpanel">
                                            <div id="followup-content-{{ $lead->id }}" class="inner-scroll-container">
                                                <div class="text-center py-5">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                    <span class="ms-2 text-muted fw-medium">Retrieving interactions...</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tasks Content -->
                                        <div class="tab-pane fade" id="tasks-{{ $lead->id }}" role="tabpanel">
                                            <div id="tasks-content-{{ $lead->id }}" class="inner-scroll-container">
                                                <div class="text-center py-5">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                    <span class="ms-2 text-muted fw-medium">fetching task reports...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fa fa-folder-open fs-2 text-muted mb-3 d-block"></i>
                                <span class="text-muted fw-600">No lead history found for this client.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Services Tab -->
        <div class="tab-pane fade" id="services" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">Sl No</th>
                            <th>Service ID</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Service Type</th>
                            <th>Engineer</th>
                            <th>Status</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                        @php
                        $isServiceBlurred = false;
                        if ($userHasDealership && !empty($userDealershipName)) {
                        if (stripos($service->product->name ?? '', $userDealershipName) === false) {
                        $isServiceBlurred = true;
                        }
                        }
                        @endphp
                        <tr class="service-row {{ $isServiceBlurred ? 'blurred-product' : '' }}" data-service-id="{{ $service->id }}" style="cursor: pointer;">
                            <td class="text-center text-muted fw-800">{{ $loop->iteration }}</td>
                            @if($isServiceBlurred)
                            <td colspan="6" class="text-center text-muted i fs-12 py-3">
                                <i class="fa fa-lock me-2"></i> Private to other dealership
                            </td>
                            @else
                            <td class="fw-800 text-primary">
                                <i class="fa fa-chevron-right toggle-icon me-2 text-muted small"></i>
                                #{{ $service->id }}
                            </td>
                            <td class="text-muted fw-600">{{ $service->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="fw-800 text-dark">{{ $service->product->name ?? 'N/A' }}</div>
                                <div class="small text-muted fw-500">
                                    {{ $service->productModel->name ?? '' }}
                                    @if($service->modelSeries)
                                    <div class="mt-1"><span class="badge bg-light text-secondary border fw-600" style="font-size: 10px;">SN: {{ $service->modelSeries->name }}</span></div>
                                    @endif
                                </div>
                            </td>
                            <td class="fw-600">{{ str_replace('_', ' ', $service->type_of_service) }}</td>
                            <td class="fw-600 text-muted">{{ $service->serviceEngineer->name ?? 'N/A' }}</td>
                            <td>
                                @php $activeTask = $service->tasks->first(); @endphp
                                <span class="badge status-badge @if($activeTask && $activeTask->status == 'completed') bg-success @else bg-warning @endif text-white">
                                    {{ $activeTask->status ?? 'Requested' }}
                                </span>
                            </td>
                            @endif
                            <td class="fw-800 text-dark">₹{{ number_format($service->price, 2) }}</td>
                        </tr>
                        @if(!$isServiceBlurred)
                        <tr id="service-detail-row-{{ $service->id }}" class="service-detail-row d-none {{ $isServiceBlurred ? 'blurred-product' : '' }}">
                            <td colspan="8" class="p-0">
                                <div class="p-4 bg-light bg-opacity-50">
                                    <div class="mb-4">
                                        <h6 class="text-muted small uppercase fw-bold ls-1">Service Details & Tasks</h6>
                                        @if($service->description)
                                        <div class="mt-2 p-3 bg-white rounded shadow-sm border-start border-primary border-4">
                                            <strong class="text-dark">Description:</strong>
                                            <p class="mb-0 mt-1 text-muted">{{ $service->description }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    <div id="service-content-{{ $service->id }}" class="inner-scroll-container">
                                        <div class="text-center py-5">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                            <span class="ms-2 text-muted fw-medium">Loading task information...</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fa fa-concierge-bell fs-2 text-muted mb-3 d-block"></i>
                                <span class="text-muted fw-600">No service history found for this client.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Products Tab -->
        <div class="tab-pane fade" id="products" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">Sl No</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Model</th>
                            <th>Machine Serial Number</th>
                            <th>Engine Serial Number</th>
                            <th>Engine Model</th>
                            <th>DOC</th>
                            <th>Source</th>
                            <th>Date Acquired</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uniqueProducts as $p)
                        @php
                        $productName = $p['product']->name ?? ($p['product_name'] ?? '');
                        $isBlurred = false;

                        if ($userHasDealership && !empty($userDealershipName)) {
                        if (stripos($productName, $userDealershipName) === false) {
                        $isBlurred = true;
                        }
                        }
                        @endphp
                        <tr class="{{ $isBlurred ? 'blurred-product' : '' }}">
                            <td class="text-center text-muted fw-800">{{ $loop->iteration }}</td>
                            <td class="text-center">
                                @if($isBlurred)
                                <i class="fa fa-lock text-muted"></i>
                                @else
                                @php
                                $img = ($p['product'] && $p['product']->image) ? asset('storage/' . $p['product']->image) : 'https://placehold.co/50x50?text=Prod';
                                @endphp
                                <img src="{{ $img }}"
                                    class="rounded-3 shadow-sm" width="50" height="50" alt="Product"
                                    onerror="this.src='https://placehold.co/50x50?text=Prod'; this.onerror=null;">
                                @endif
                            </td>
                            @if($isBlurred)
                            <td colspan="8" class="text-center text-muted i fs-12 py-3">
                                <i class="fa fa-lock me-2"></i> Data hidden for other dealership
                            </td>
                            @else
                            <td>
                                <div class="fw-800 text-dark">{{ $p['product']->name ?? ($p['product_name'] ?? 'Unknown Product') }}</div>
                                <div class="small text-muted fw-600 uppercase ls-1">
                                    {{ $p['product']->category->name ?? ($p['category_name'] ?? ($p['product'] ? 'Category' : 'Unknown Category')) }}
                                </div>
                            </td>
                            <td class="fw-600">{{ $p['model']->name ?? ($p['product_model_name'] ?? 'N/A') }}</td>
                            <td class="fw-800 text-primary">{{ $p['machine_serial_number'] ?? 'N/A' }}</td>
                            <td class="fw-600">{{ $p['engine_serial_number'] ?? 'N/A' }}</td>
                            <td class="fw-600">{{ $p['engine_model'] ?? 'N/A' }}</td>
                            <td class="fw-600">{{ $p['doc'] ? (\Carbon\Carbon::parse($p['doc'])->format('d M Y')) : 'N/A' }}</td>
                            <td><span class="badge bg-light text-primary border border-primary border-opacity-10 px-3 py-2 fw-700">{{ $p['source'] }}</span></td>
                            @endif
                            <td class="text-muted fw-600">{{ isset($p['date']) ? ($p['date'] instanceof \Carbon\Carbon ? $p['date']->format('d M Y') : \Carbon\Carbon::parse($p['date'])->format('d M Y')) : 'N/A' }}</td>
                            <td class="text-end">
                                @if(!$isBlurred && isset($p['client_product_id']) && $p['client_product_id'])
                                <button type="button" class="btn btn-xs btn-outline-danger delete-client-product-btn"
                                    data-id="{{ $p['client_product_id'] }}"
                                    title="Delete Product">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="fa fa-boxes fs-2 text-muted mb-3 d-block"></i>
                                <span class="text-muted fw-600">No owned products found for this client.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
<!-- Revert Conversion Modal -->
<div class="modal fade" id="revertConversionModal" tabindex="-1" aria-labelledby="revertConversionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="revertConversionModalLabel">Confirm Revert Conversion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to revert the conversion for <strong>Lead #<span id="revertLeadIdDisplay"></span></strong>?</p>
                <div id="primaryLeadWarning" class="alert alert-warning d-none">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> This lead was the primary creator of this client record. Reverting it will <strong>delete</strong> this client profile and all associated product records.
                </div>
                <div id="secondaryLeadNote" class="alert alert-info d-none">
                    <i class="fa fa-info-circle me-2"></i>
                    This lead will be unlinked and restored to its previous status. The client record will remain.
                </div>
                <p class="text-danger fw-bold mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmRevertBtn">Revert Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteProductModalLabel"><i class="fa fa-exclamation-triangle me-2"></i>Confirm Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fs-6">Are you sure you want to delete this owned product?</p>
                <p class="text-danger fw-bold mb-0"><small>Note: This action removes the machine entirely from the client's asset list. This cannot be undone.</small></p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteProductBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        $(document).ready(function() {
            // Helper function to truncate text and add "Read More"
            function truncateText(text, limit = 150) {
                if (!text || text.length <= limit) return text || "";

                var truncated = text.substring(0, limit);
                var full = text;

                return `
                <span class="text-wrapped">
                    <span class="truncated-text">${truncated}...</span>
                    <span class="full-text d-none">${full}</span>
                    <a href="javascript:void(0);" class="read-more-link" data-expanded="false">Read More</a>
                </span>
            `;
            }

            // Global handler for Read More links
            $(document).on('click', '.read-more-link', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var $link = $(this);
                var $container = $link.closest('.text-wrapped');
                var isExpanded = $link.data('expanded') === true;

                if (isExpanded) {
                    $container.find('.truncated-text').removeClass('d-none');
                    $container.find('.full-text').addClass('d-none');
                    $link.text('Read More').data('expanded', false);
                } else {
                    $container.find('.truncated-text').addClass('d-none');
                    $container.find('.full-text').removeClass('d-none');
                    $link.text('Read Less').data('expanded', true);
                }
            });

            $('.lead-row').on('click', function(e) {
                if ($(e.target).closest('a').length) return;

                var leadId = $(this).data('lead-id');
                var $followupRow = $('#followup-row-' + leadId);
                var $leadRow = $(this);
                var $content = $('#followup-content-' + leadId);

                if ($followupRow.hasClass('d-none')) {
                    $('.followup-row').addClass('d-none').slideUp(0);
                    $('.lead-row').removeClass('expanded');

                    $followupRow.removeClass('d-none').hide().slideDown(400);
                    $leadRow.addClass('expanded');

                    if (!$content.hasClass('loaded')) {
                        // Load Follow-ups
                        $.ajax({
                            url: "{{ route('leads.followups.index', ':id') }}".replace(':id', leadId),
                            method: 'GET',
                            success: function(response) {
                                var html = '';
                                if (response.data && response.data.length > 0) {
                                    html = '<div class="inner-timeline">';
                                    response.data.forEach(function(item) {
                                        var userInfo = item.user_info || {
                                            profile_pic: "{{ asset('admin/assets/images/dashboard/profile.png') }}",
                                            name: "System User",
                                            department: "General"
                                        };

                                        // Status color mapping
                                        var statusClass = 'bg-light-primary text-primary';
                                        var status = (item.new_status || '').toLowerCase();

                                        if (status.includes('win') || status.includes('concluded')) statusClass = 'bg-light-success text-success';
                                        else if (status.includes('lost') || status.includes('dropped')) statusClass = 'bg-light-danger text-danger';
                                        else if (status.includes('hot')) statusClass = 'bg-light-danger text-danger';
                                        else if (status.includes('warm')) statusClass = 'bg-light-warning text-warning';
                                        else if (status.includes('cold')) statusClass = 'bg-light-secondary text-secondary';
                                        else if (status.includes('pending')) statusClass = 'bg-light-info text-info';

                                        html += '<div class="inner-timeline-item" data-agent="' + userInfo.name + '">';
                                        html += '   <div class="inner-timeline-marker" ';
                                        html += '        data-bs-toggle="tooltip" data-bs-placement="top" ';
                                        html += '        title="By: ' + userInfo.name + ' (' + userInfo.department + ')">';
                                        html += '       <img src="' + userInfo.profile_pic + '" ';
                                        html += '            onerror="this.src=\'{{ asset("admin/assets/images/dashboard/profile.png") }}\'; this.onerror=null;">';
                                        html += '   </div>';
                                        html += '   <div class="inner-timeline-card">';
                                        html += '       <div class="d-flex justify-content-between align-items-center mb-3">';
                                        html += '           <div>';
                                        html += '               <span class="badge ' + statusClass + ' px-3 py-2 rounded-pill fw-bold">' + item.new_status + '</span>';
                                        html += '               <span class="ms-2 text-muted small fw-medium">by ' + userInfo.name + '</span>';
                                        html += '           </div>';
                                        html += '           <span class="text-muted small bg-light px-2 py-1 rounded"><i class="fa fa-calendar-alt me-1"></i> ' + item.created_at + '</span>';
                                        html += '       </div>';
                                        html += '       <div class="followup-remarks">' + truncateText(item.remarks || "No remarks provided.") + '</div>';
                                        if (item.next_follow_up_date) {
                                            html += '       <div class="mt-3 p-3 bg-light-danger rounded-3 d-flex align-items-center gap-2 small text-danger fw-bold border border-danger border-opacity-10">';
                                            html += '           <i class="fa fa-clock fs-6"></i> Next Follow-up: ' + item.next_follow_up_date;
                                            html += '       </div>';
                                        }
                                        html += '   </div>';
                                        html += '</div>';
                                    });
                                    html += '</div>';
                                    $content.hide().html(html).addClass('loaded').fadeIn(500);


                                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                        return new bootstrap.Tooltip(tooltipTriggerEl);
                                    });
                                } else {
                                    $content.html('<div class="alert alert-light text-center py-4">No follow-up interactions found for this lead.</div>');
                                }
                            },
                            error: function() {
                                $content.html('<div class="alert alert-danger">Failed to retrieve follow-up history.</div>');
                            }
                        });

                        // Load Tasks
                        var $tasksContent = $('#tasks-content-' + leadId);
                        $.ajax({
                            url: "{{ route('leads.tasks.index', ':id') }}".replace(':id', leadId),
                            method: 'GET',
                            success: function(response) {
                                var html = '';
                                if (response.data && response.data.length > 0) {
                                    html = '<div class="table-responsive">';
                                    html += '<table class="table table-sm table-hover align-middle">';
                                    html += '<thead><tr class="text-muted small"><th>#</th><th>Title</th><th>Type</th><th>Assigned To</th><th>Status</th><th>Due Date</th></tr></thead>';
                                    html += '<tbody>';
                                    response.data.forEach(function(task, index) {
                                        var statusClass = 'bg-light-info text-info';
                                        if (task.status === 'completed') statusClass = 'bg-light-success text-success';
                                        else if (task.status === 'in_progress') statusClass = 'bg-light-warning text-warning';

                                        html += '<tr class="task-main-row" style="cursor:pointer;" onclick="$(this).next(\'.task-details-row\').toggleClass(\'d-none\')">';
                                        html += '<td>' + (index + 1) + '</td>';
                                        html += '<td><div class="fw-bold">' + task.title + '</div><div class="small text-muted">' + truncateText(task.description || '', 100) + '</div></td>';
                                        html += '<td><span class="badge bg-light text-dark border">' + task.type + '</span></td>';
                                        html += '<td>' + task.assigned_to_info + '</td>';
                                        html += '<td><span class="badge ' + statusClass + ' rounded-pill">' + task.status.charAt(0).toUpperCase() + task.status.slice(1) + '</span></td>';
                                        html += '<td>' + (task.due_date || 'N/A') + '</td>';
                                        html += '</tr>';

                                        // Accordion Details Row
                                        html += '<tr class="task-details-row d-none bg-light">';
                                        html += '<td colspan="6">';
                                        html += '<div class="p-3">';
                                        html += '<div class="row mb-3">';
                                        html += '<div class="col-md-4">';
                                        html += '<div class="text-muted small mb-1">Total Time Spent</div>';
                                        html += '<div class="h5 mb-0 text-primary"><i class="icon-timer me-1"></i>' + (task.time_spent || '00:00:00') + '</div>';
                                        html += '</div>';
                                        html += '</div>';

                                        html += '<div class="task-followups-section">';
                                        html += '<h6 class="border-bottom pb-2 mb-3">Task Follow-ups</h6>';
                                        if (task.task_followups) {
                                            var followups = Array.isArray(task.task_followups) ? task.task_followups : Object.values(task.task_followups);
                                            if (followups.length > 0) {
                                                html += '<div class="timeline-small">';
                                                followups.forEach(function(f) {
                                                    html += '<div class="timeline-item pb-3">';
                                                    html += '<div class="d-flex">';
                                                    html += '<div class="flex-grow-1 ms-2">';
                                                    html += '<div class="small text-muted">' + (f.created_at || '') + ' - ' + (f.user || 'N/A') + '</div>';
                                                    html += '<p class="mb-0">' + truncateText(f.notes || '', 100) + '</p>';

                                                    if (f.images && Array.isArray(f.images) && f.images.length > 0) {
                                                        html += '<div class="d-flex flex-wrap gap-1 mt-2">';
                                                        f.images.forEach(function(img) {
                                                            html += '<a href="/' + img + '" data-lightbox="task-followup-' + f.id + '" data-title="Follow-up Image">';
                                                            html += '<img src="/' + img + '" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">';
                                                            html += '</a>';
                                                        });
                                                        html += '</div>';
                                                    }
                                                    html += '</div></div></div>';
                                                });
                                                html += '</div>';
                                            } else {
                                                html += '<div class="text-muted small italic">No follow-ups recorded for this task.</div>';
                                            }
                                        } else {
                                            html += '<div class="text-muted small italic">No follow-ups recorded for this task.</div>';
                                        }
                                        html += '</div>';

                                        html += '</div></td></tr>';
                                    });
                                    html += '</tbody></table></div>';
                                    $tasksContent.hide().html(html).fadeIn(500);
                                } else {
                                    $tasksContent.html('<div class="alert alert-light text-center py-4">No tasks found for this lead.</div>');
                                }
                            }
                        });
                    }
                } else {
                    $followupRow.slideUp(300, function() {
                        $(this).addClass('d-none');
                    });
                    $leadRow.removeClass('expanded');
                }
            });

            // Service Row Toggle
            $('.service-row').on('click', function(e) {
                if ($(e.target).closest('a').length) return;

                var serviceId = $(this).data('service-id');
                var $detailRow = $('#service-detail-row-' + serviceId);
                var $serviceRow = $(this);
                var $content = $('#service-content-' + serviceId);

                if ($detailRow.hasClass('d-none')) {
                    $('.service-detail-row').addClass('d-none').slideUp(0);
                    $('.service-row').removeClass('expanded');

                    $detailRow.removeClass('d-none').hide().slideDown(400);
                    $serviceRow.addClass('expanded');

                    if (!$content.hasClass('loaded')) {
                        $.ajax({
                            url: "{{ route('entries.serviceFollowups', ':id') }}".replace(':id', serviceId),
                            method: 'GET',
                            success: function(response) {
                                var html = '';
                                if (response && response.length > 0) {
                                    response.forEach(function(task) {
                                        html += '<div class="card mb-3 border-0 shadow-sm overflow-hidden">';
                                        html += '   <div class="card-header bg-light border-0 py-3">';
                                        html += '       <div class="d-flex justify-content-between align-items-center">';
                                        html += '           <h6 class="mb-0 fw-bold text-primary">Task: ' + task.task_title + '</h6>';
                                        html += '           <span class="badge bg-white text-dark border">Assigned to: ' + task.assigned_to + '</span>';
                                        html += '       </div>';
                                        html += '   </div>';
                                        html += '   <div class="card-body p-0">';

                                        if (task.followups && task.followups.length > 0) {
                                            html += '<div class="inner-timeline p-4">';
                                            task.followups.forEach(function(f) {
                                                html += '<div class="inner-timeline-item">';
                                                html += '   <div class="inner-timeline-marker bg-white d-flex align-items-center justify-content-center">';
                                                html += '       <i class="fa fa-user text-muted small"></i>';
                                                html += '   </div>';
                                                html += '   <div class="inner-timeline-card">';
                                                html += '       <div class="d-flex justify-content-between align-items-center mb-2">';
                                                html += '           <span class="text-muted small">by ' + f.submitted_by + '</span>';
                                                html += '           <span class="text-muted small"><i class="fa fa-calendar-alt me-1"></i> ' + f.created_at + '</span>';
                                                html += '       </div>';
                                                html += '       <div class="followup-remarks mb-0">' + truncateText(f.notes || "No notes", 150) + '</div>';

                                                if (f.images && f.images.length > 0) {
                                                    html += '<div class="mt-3 d-flex flex-wrap gap-2">';
                                                    // Handle images if it's a string (JSON) or array
                                                    var images = f.images;
                                                    if (typeof images === 'string') {
                                                        try {
                                                            images = JSON.parse(images);
                                                        } catch (e) {
                                                            images = [];
                                                        }
                                                    }
                                                    if (Array.isArray(images)) {
                                                        images.forEach(function(img) {
                                                            html += '<a href="/' + img + '" target="_blank">';
                                                            html += '   <img src="/' + img + '" class="rounded" style="width:60px; height:60px; object-fit:cover; border:1px solid #ddd;">';
                                                            html += '</a>';
                                                        });
                                                    }
                                                    html += '</div>';
                                                }

                                                html += '   </div>';
                                                html += '</div>';
                                            });
                                            html += '</div>';
                                        } else {
                                            html += '<div class="p-4 text-center text-muted small italic">No follow-ups recorded for this task.</div>';
                                        }

                                        html += '   </div>';
                                        html += '</div>';
                                    });
                                    $content.hide().html(html).addClass('loaded').fadeIn(500);
                                } else {
                                    $content.html('<div class="alert alert-light text-center py-4">No tasks associated with this service.</div>');
                                }
                            },
                            error: function() {
                                $content.html('<div class="alert alert-danger">Failed to retrieve service task history.</div>');
                            }
                        });
                    }
                } else {
                    $detailRow.slideUp(300, function() {
                        $(this).addClass('d-none');
                    });
                    $serviceRow.removeClass('expanded');
                }
            });

            // Revert Conversion Logic
            var leadIdToRevert = null;
            $('.revert-lead-btn').on('click', function(e) {
                e.stopPropagation(); // Prevent row click
                leadIdToRevert = $(this).data('id');
                var isPrimary = $(this).data('is-primary');

                $('#revertLeadIdDisplay').text(leadIdToRevert);

                if (isPrimary === true || isPrimary === 'true') {
                    $('#primaryLeadWarning').removeClass('d-none');
                    $('#secondaryLeadNote').addClass('d-none');
                } else {
                    $('#primaryLeadWarning').addClass('d-none');
                    $('#secondaryLeadNote').removeClass('d-none');
                }

                $('#revertConversionModal').modal('show');
            });

            $('#confirmRevertBtn').on('click', function() {
                if (!leadIdToRevert) return;

                var $btn = $(this);
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Reverting...');

                $.ajax({
                    url: "{{ url('/leads') }}/" + leadIdToRevert + "/revert-conversion",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message, 'success');
                        setTimeout(function() {
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).text('Revert Now');
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred during reversion.';
                        showToast(errorMessage, 'danger');
                    }
                });
            });

            // Client Product Delete
            var productToDeleteId = null;
            var btnToDelete = null;

            $('.delete-client-product-btn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                productToDeleteId = $(this).data('id');
                btnToDelete = $(this);
                $('#deleteProductModal').modal('show');
            });

            $('#confirmDeleteProductBtn').on('click', function() {
                if (!productToDeleteId) return;
                var confirmBtn = $(this);
                confirmBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Deleting...');
                if (btnToDelete) btnToDelete.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ url('/clients/products') }}/" + productToDeleteId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#deleteProductModal').modal('hide');
                        if (typeof showToast === 'function') {
                            showToast(response.message || 'Product deleted successfully', 'success');
                        } else {
                            alert(response.message || 'Product deleted successfully');
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        confirmBtn.prop('disabled', false).html('Yes, Delete');
                        if (btnToDelete) btnToDelete.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.error || xhr.responseJSON.message : 'An error occurred while deleting the product.';
                        if (typeof showToast === 'function') {
                            showToast(errorMessage, 'danger');
                        } else {
                            alert(errorMessage);
                        }
                    }
                });
            });

        });
    </script>
    @endpush