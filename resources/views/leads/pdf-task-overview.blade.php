@extends('layouts.pdf')

@section('title', 'Task Overview Report')

@section('header-right')
Task Overview Report
@endsection

@push('styles')
<style>
    .section-title {
        background-color: #f2f2f2;
        padding: 10px;
        font-weight: bold;
        margin-top: 20px;
        border-bottom: 1px solid #ccc;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f9f9f9;
    }

    .label {
        font-weight: bold;
        width: 150px;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Task Overview Report', 'subtitle' => 'Task: ' . $task->title])

    <div class="section-title">Task Details</div>
    <table>
        <tr>
            <td class="label">Description:</td>
            <td>{{ $task->description }}</td>
        </tr>
        <tr>
            <td class="label">Assigned To:</td>
            <td>{{ $task->assignedEmployee->name ?? 'Unassigned' }}</td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td>{{ ucwords(str_replace('_', ' ', $task->status)) }}</td>
        </tr>
        <tr>
            <td class="label">Due Date:</td>
            <td>{{ $task->due_date ? $task->due_date->format('d-m-Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Total Time:</td>
            <td>{{ $totalTime }}</td>
        </tr>
    </table>

    @if($task->fsrReport)
    <div class="section-title">Field Service Report (FSR)</div>
    <table>
        <tr>
            <td class="label">FSR Status:</td>
            <td>{{ ucfirst($task->fsrReport->status ?? 'pending') }}</td>
        </tr>
        @if($task->fsrReport->payment_status)
        <tr>
            <td class="label">Payment Status:</td>
            <td>{{ ucfirst($task->fsrReport->payment_status) }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">On-site Assessment:</td>
            <td>{{ $task->fsrReport->on_site_assessment ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Analysis of Cause:</td>
            <td>{{ $task->fsrReport->analysis_of_cause ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Actions Taken:</td>
            <td>{{ $task->fsrReport->actions_taken ?? 'N/A' }}</td>
        </tr>
    </table>

    @if($task->fsrReport->partQuotations->count() > 0)
    <div class="section-title">Parts Quotation</div>
    <table>
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
                <td>{{ ucfirst($quotation->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endif

    <div class="section-title">Activity Log</div>
    <table>
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Action</th>
                <th>Performed By</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($taskLogs as $log)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ ucfirst($log->action_type) }}</td>
                <td>{{ $log->employee->name ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($log->action_time)->format('d M Y, h:i A') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Follow-up History</div>
    <table>
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Notes</th>
                <th>By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($task->followups as $followup)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $followup->notes }}</td>
                <td>{{ $followup->user->name ?? 'Unknown' }}</td>
                <td>{{ $followup->created_at->format('d M Y, h:i A') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
