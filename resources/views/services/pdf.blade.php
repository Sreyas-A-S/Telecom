@extends('layouts.pdf')

@section('title', 'Services Report')

@push('styles')
<style>
    .service-block {
        margin-bottom: 30px;
        page-break-inside: avoid;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .service-header {
        background-color: #f8f9fa;
        padding: 10px 15px;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
        color: #1d3557;
        font-size: 14px;
    }
    
    .service-body {
        padding: 15px;
    }
    
    .info-grid {
        display: table;
        width: 100%;
        margin-bottom: 10px;
    }
    
    .info-row {
        display: table-row;
    }
    
    .info-cell {
        display: table-cell;
        padding: 5px;
        vertical-align: top;
        width: 50%;
    }
    
    .label {
        font-weight: bold;
        color: #666;
        font-size: 10px;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    
    .value {
        font-size: 12px;
        color: #333;
    }
    
    .description-box {
        margin-top: 10px;
        padding: 10px;
        background-color: #fcfcfc;
        border: 1px dashed #eee;
        border-radius: 4px;
    }
    
    .badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .badge-primary { background-color: #e1f5fe; color: #01579b; }
    .badge-success { background-color: #e8f5e9; color: #1b5e20; }
    .badge-warning { background-color: #fff3e0; color: #e65100; }
    .badge-danger { background-color: #ffebee; color: #b71c1c; }
    
    .section-title {
        font-size: 11px;
        font-weight: bold;
        color: #1d3557;
        border-bottom: 1px solid #eee;
        margin-bottom: 8px;
        padding-bottom: 3px;
    }
</style>
@endpush

@section('content')
<div class="report-head">
    <h1 class="report-title">{{ ucfirst($assignmentStatus) }} Services Report</h1>
    <div class="report-subtitle">
        @if($dealership)
            Dealership: {{ $dealership->name }} | 
        @endif
        Total Records: {{ count($services) }} | 
        Generated: {{ now()->format('d M Y, h:i A') }}
    </div>
</div>

@foreach($services as $service)
<div class="service-block">
    <div class="service-header">
        <div style="float: right;">
            <span class="badge badge-primary">{{ $service->referral_id }}</span>
        </div>
        {{ $service->name ?: 'Service Entry #' . $loop->iteration }}
    </div>
    <div class="service-body">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="section-title">Customer & Location</div>
                    <div class="mb-10">
                        <div class="label">Customer Name</div>
                        <div class="value">{{ $service->client->name ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-10">
                        <div class="label">Zone</div>
                        <div class="value">{{ $service->zone->name ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-10">
                        <div class="label">Contact Person / Info</div>
                        <div class="value">
                            {{ $service->contact_person ?: ($service->client->name ?? 'N/A') }} <br>
                            {{ $service->contact_info ?: ($service->client->phone_number ?? 'N/A') }}
                        </div>
                    </div>
                    <div>
                        <div class="label">Requested Location</div>
                        <div class="value">{{ $service->requested_location ?: 'N/A' }}</div>
                    </div>
                </div>
                <div class="info-cell">
                    <div class="section-title">Product Details</div>
                    <div class="mb-10">
                        <div class="label">Product / Model</div>
                        <div class="value">
                            {{ $service->product->name ?? 'N/A' }} 
                            @if($service->productModel)
                                - {{ $service->productModel->name }}
                            @endif
                        </div>
                    </div>
                    <div class="mb-10">
                        <div class="label">Serial / Engine Number</div>
                        <div class="value">
                            S/N: {{ $service->modelSeries->name ?? 'N/A' }} <br>
                            E/N: {{ $service->engine_serial_number ?? 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <div class="label">Status & Type</div>
                        <div class="value">
                            <span class="badge badge-success">{{ str_replace('_', ' ', $service->machine_status) }}</span>
                            <span class="badge badge-primary">{{ str_replace('_', ' ', $service->type_of_service) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="section-title">Service Details</div>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell" style="width: 50%;">
                                <div class="label">DOC</div>
                                <div class="value">{{ $service->doc ?: 'N/A' }}</div>
                            </div>
                            <div class="info-cell" style="width: 50%;">
                                <div class="label">Failure Date</div>
                                <div class="value">{{ $service->failure_date ?: 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell" style="width: 50%;">
                                <div class="label">Failure HMR</div>
                                <div class="value">{{ $service->failure_hmr ?: 'N/A' }}</div>
                            </div>
                            <div class="info-cell" style="width: 50%;">
                                <div class="label">Revenue</div>
                                <div class="value">{{ $service->price ? number_format($service->price, 2) : '0.00' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="info-cell">
                    <div class="section-title">Assignments & Status</div>
                    <div class="mb-10">
                        <div class="label">Assigned Engineers</div>
                        <div class="value">
                            1. {{ $service->serviceEngineer->user->name ?? 'Not Assigned' }} <br>
                            2. {{ $service->serviceEngineer2->user->name ?? 'Not Assigned' }}
                        </div>
                    </div>
                    <div>
                        <div class="label">Call Status</div>
                        <div class="value">
                            <span class="badge {{ $service->call_status === 'closed' ? 'badge-success' : ($service->call_status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                                {{ strtoupper($service->call_status ?: 'OPENED') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($service->description)
        <div class="section-title">Nature of Complaints</div>
        <div class="description-box">
            <div class="value">{{ $service->description }}</div>
        </div>
        @endif

        @if($service->call_remarks)
        <div class="section-title" style="margin-top: 10px;">Call Remarks</div>
        <div class="value" style="padding: 5px;">{{ $service->call_remarks }}</div>
        @endif
    </div>
</div>
@endforeach
@endsection
