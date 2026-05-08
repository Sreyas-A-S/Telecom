@extends('layouts.pdf')

@section('title', 'Field Service Report (FSR) - ' . $task->title)

@section('header-right')
Field Service Report
@endsection

@push('styles')
<style>
    .section-title {
        background-color: #f8f9fa;
        padding: 8px 12px;
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 10px;
        border-left: 4px solid #1d3557;
        color: #1d3557;
        text-transform: uppercase;
        font-size: 13px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .info-table td {
        padding: 8px;
        vertical-align: top;
        border: 1px solid #eee;
    }

    .label {
        font-weight: bold;
        color: #555;
        width: 30%;
        background-color: #fcfcfc;
    }

    .value {
        width: 70%;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .data-table th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
        padding: 8px;
        border: 1px solid #ddd;
        text-align: left;
        font-size: 11px;
    }

    .data-table td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: left;
        font-size: 11px;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }
    
    .status-badge {
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: bold;
        display: inline-block;
        text-transform: uppercase;
    }
    
    .status-approved { background-color: #d4edda; color: #155724; }
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-rejected { background-color: #f8d7da; color: #721c24; }

    .summary-box {
        display: table;
        width: 100%;
        margin-bottom: 20px;
    }

    .summary-item {
        display: table-cell;
        width: 33.33%;
        padding: 10px;
        text-align: center;
        border: 1px solid #eee;
    }

    .summary-label {
        display: block;
        font-size: 10px;
        color: #777;
        margin-bottom: 5px;
    }

    .summary-value {
        display: block;
        font-size: 16px;
        font-weight: bold;
        color: #1d3557;
    }

    .image-grid {
        width: 100%;
    }

    .image-item {
        display: inline-block;
        width: 31%;
        margin-right: 2%;
        margin-bottom: 10px;
        text-align: center;
    }

    .fsr-image {
        max-width: 100%;
        height: 150px;
        object-fit: cover;
        border: 1px solid #ddd;
        padding: 2px;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Field Service Report (FSR)', 'subtitle' => 'Task: ' . $task->title])

    <div class="section-title">General Information</div>
    <table class="info-table">
        <tr>
            <td class="label">Task Title:</td>
            <td class="value">{{ $task->title }}</td>
        </tr>
        <tr>
            <td class="label">Task Type:</td>
            <td class="value">{{ $task->task_type_label }}</td>
        </tr>
        <tr>
            <td class="label">Assigned To:</td>
            <td class="value">{{ $task->assignedEmployee->name ?? 'Unassigned' }}</td>
        </tr>
        <tr>
            <td class="label">Dealership:</td>
            <td class="value">{{ $task->dealership->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Location:</td>
            <td class="value">{{ $task->location ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Client Name:</td>
            <td class="value">
                @if($task->lead_id)
                    {{ $task->lead->client->name ?? $task->lead->name ?? 'N/A' }}
                @else
                    {{ $task->entry->client->name ?? $task->entry->name ?? 'N/A' }}
                @endif
            </td>
        </tr>
    </table>

    @if($task->fsrReport)
        <div class="section-title">Assessment & Actions</div>
        <table class="info-table">
            <tr>
                <td class="label">On-Site Assessment:</td>
                <td class="value">{!! nl2br(e($task->fsrReport->on_site_assessment)) !!}</td>
            </tr>
            <tr>
                <td class="label">Analysis of Cause:</td>
                <td class="value">{!! nl2br(e($task->fsrReport->analysis_of_cause)) !!}</td>
            </tr>
            <tr>
                <td class="label">Actions Taken:</td>
                <td class="value">{!! nl2br(e($task->fsrReport->actions_taken)) !!}</td>
            </tr>
        </table>

        @if($task->lead_id && $task->lead && $task->lead->items->isNotEmpty())
        <div class="section-title">Lead Products Info</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Model</th>
                    <th>Series</th>
                    <th>Serial Numbers</th>
                    <th class="text-center">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($task->lead->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td>{{ $item->productModel->name ?? 'N/A' }}</td>
                    <td>{{ $item->modelSeries->name ?? 'N/A' }}</td>
                    <td>
                        @if($item->machine_serial_number) M: {{ $item->machine_serial_number }} @endif
                        @if($item->engine_serial_number) | E: {{ $item->engine_serial_number }} @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($task->lead_id && $task->fsrReport->partQuotations->isNotEmpty())
        <div class="section-title">Parts Quotation</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Part Number</th>
                    <th>Material Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($task->fsrReport->partQuotations as $quotation)
                <tr>
                    <td>{{ $quotation->part->part_number ?? 'N/A' }}</td>
                    <td>{{ $quotation->part->material_description ?? 'N/A' }}</td>
                    <td class="text-center">{{ $quotation->quoted_quantity }}</td>
                    <td class="text-right">{{ number_format($quotation->quoted_unit_price, 2) }}</td>
                    <td class="text-center">
                        @php
                            $statusClass = 'status-pending';
                            if($quotation->status === 'approved') $statusClass = 'status-approved';
                            elseif($quotation->status === 'rejected') $statusClass = 'status-rejected';
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $quotation->status }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="section-title">Payment & Collections</div>
        @php
            $totalPrice = $task->lead_id ? ($task->lead->lead_value ?? 0) : ($task->entry->price ?? 0);
            $priceLabel = $task->lead_id ? 'Lead Value' : 'Service Price';
            $totalCollected = $task->fsrReport->paymentHistory->sum('amount');
            $balanceDue = $totalPrice - $totalCollected;
        @endphp

        <div class="summary-box">
            <div class="summary-item">
                <span class="summary-label">{{ $priceLabel }}</span>
                <span class="summary-value">{{ number_format($totalPrice, 2) }}</span>
            </div>
            <div class="summary-item" style="background-color: #f8fff9;">
                <span class="summary-label">Total Collected</span>
                <span class="summary-value" style="color: #2d6a4f;">{{ number_format($totalCollected, 2) }}</span>
            </div>
            <div class="summary-item" style="background-color: #fff8f8;">
                <span class="summary-label">Balance Due</span>
                <span class="summary-value" style="color: #a4161a;">{{ number_format($balanceDue, 2) }}</span>
            </div>
        </div>

        @if($task->fsrReport->paymentHistory->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-right">Amount</th>
                    <th class="text-center">Mode</th>
                    <th>Collected By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($task->fsrReport->paymentHistory as $payment)
                <tr>
                    <td>{{ $payment->collected_at->format('d M Y, H:i') }}</td>
                    <td class="text-right font-bold">{{ number_format($payment->amount, 2) }}</td>
                    <td class="text-center">{{ ucfirst($payment->payment_mode) }}</td>
                    <td>{{ $payment->collectedBy->name ?? 'N/A' }}</td>
                    <td>{{ $payment->remarks ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div style="margin-top: 10px; margin-bottom: 20px;">
            <table class="info-table" style="margin-bottom: 0;">
                <tr>
                    <td class="label">FSR Status:</td>
                    <td class="value font-bold" style="color: {{ $task->fsrReport->status === 'approved' ? '#2d6a4f' : '#856404' }};">
                        {{ strtoupper($task->fsrReport->status) }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Payment Status:</td>
                    <td class="value font-bold" style="color: {{ $task->fsrReport->payment_status === 'paid' ? '#2d6a4f' : '#856404' }};">
                        {{ strtoupper($task->fsrReport->payment_status) }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Submitted By:</td>
                    <td class="value">{{ $task->fsrReport->submittedBy->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        @if(!empty($task->fsrReport->images) && count($task->fsrReport->images) > 0)
        <div class="section-title">Captured Images</div>
        <div class="image-grid">
            @foreach($task->fsrReport->images as $imagePath)
                <div class="image-item">
                    <img src="{{ public_path('storage/' . $imagePath) }}" class="fsr-image">
                </div>
            @endforeach
        </div>
        @endif
    @endif
@endsection
