@extends('layouts.pdf')

@section('title', 'FSR Quotation #' . $fsrReport->id)

@section('header-right')
FSR Quotation Review
@endsection

@push('styles')
<style>
    .details {
        margin-bottom: 20px;
    }
    .details table {
        width: 100%;
        margin-bottom: 0;
    }
    .details td {
        padding: 5px;
        vertical-align: top;
        border: none;
    }
    .quotations-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .quotations-table th, .quotations-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .quotations-table th {
        background-color: #f2f2f2;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'FSR Quotation Review', 'subtitle' => 'Report ID: #' . $fsrReport->id])

    <div class="details">
        <table>
            <tr>
                <td width="50%">
                    <strong>Task Title:</strong> {{ $fsrReport->task->title ?? 'N/A' }}<br>
                    <strong>Submitted By:</strong> {{ $fsrReport->submittedBy->name ?? 'N/A' }}<br>
                    <strong>Date:</strong> {{ $fsrReport->created_at->format('d M Y') }}
                </td>
                <td width="50%">
                    <strong>On-site Assessment:</strong> {{ $fsrReport->on_site_assessment }}<br>
                    <strong>Analysis of Cause:</strong> {{ $fsrReport->analysis_of_cause }}<br>
                    <strong>Actions Taken:</strong> {{ $fsrReport->actions_taken }}
                </td>
            </tr>
        </table>
    </div>

    <h3>Part Quotations</h3>
    <table class="quotations-table">
        <thead>
            <tr>
                <th>Part Number</th>
                <th>Part Description</th>
                <th>Quoted Qty</th>
                <th>Unit Price</th>
                <th>Approved Qty</th>
                <th>Total Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($fsrReport->partQuotations as $quotation)
            @php 
                $qty = $quotation->approved_quantity ?? $quotation->quoted_quantity;
                $total = $qty * $quotation->quoted_unit_price;
                if($quotation->status == 'approved' || $quotation->status == 'Approved') {
                    $grandTotal += $total;
                }
            @endphp
            <tr>
                <td>{{ $quotation->part->part_number ?? 'N/A' }}</td>
                <td>{{ $quotation->part->material_description ?? 'N/A' }}</td>
                <td>{{ $quotation->quoted_quantity }}</td>
                <td>{{ number_format($quotation->quoted_unit_price, 2) }}</td>
                <td>{{ $quotation->approved_quantity ?? '-' }}</td>
                <td>{{ number_format($total, 2) }}</td>
                <td>{{ ucfirst($quotation->status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Total Approved Value:</strong></td>
                <td colspan="2"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
@endsection
