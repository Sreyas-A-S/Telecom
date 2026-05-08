@extends('layouts.pdf')

@section('title', 'Expense Requests Report')

@section('header-right')
Expense Requests Report
@endsection

@push('styles')
<style>
    .meta-info {
        text-align: right;
        margin-bottom: 15px;
        font-style: italic;
        color: #777;
    }

    tr:nth-child(even) {
        background-color: #fcfcfc;
    }

    .status-badge {
        padding: 3px 6px;
        border-radius: 4px;
        color: white;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        display: inline-block;
    }

    .bg-success {
        background-color: #28a745;
    }

    .bg-warning {
        background-color: #ffc107;
        color: #333;
    }

    .bg-danger {
        background-color: #dc3545;
    }

    .bg-info {
        background-color: #17a2b8;
    }

    .bg-secondary {
        background-color: #6c757d;
    }

    .bg-primary {
        background-color: #007bff;
    }

    .amount {
        text-align: right;
    }

    .total-section {
        margin-top: 20px;
        text-align: right;
    }

    .total-box {
        display: inline-block;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        padding: 10px 20px;
        border-radius: 5px;
    }

    .total-label {
        font-weight: bold;
        color: #555;
    }

    .total-amount {
        font-size: 14px;
        color: #1d3557;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Expense Requests Report'])

    <div class="meta-info">
        Generated on: {{ date('d M Y, h:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="15%">Employee</th>
                <th width="10%">Type</th>
                <th width="10%">Amount</th>
                <th width="10%">Approved</th>
                <th width="12%">Date</th>
                <th width="13%">Status</th>
                <th width="25%">Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->id }}</td>
                <td>{{ $expense->user->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($expense->expense_type) }}</td>
                <td class="amount">{{ number_format($expense->amount, 2) }}</td>
                <td class="amount">
                    @if($expense->approved_amount)
                    {{ number_format($expense->approved_amount, 2) }}
                    @else
                    -
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</td>
                <td>
                    @php
                    $statusClass = match($expense->status) {
                    'approved' => 'bg-success',
                    'pending' => 'bg-warning',
                    'rejected' => 'bg-danger',
                    'processed' => 'bg-info',
                    'approved and forwarded' => 'bg-primary',
                    default => 'bg-secondary'
                    };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst($expense->status) }}
                    </span>
                    @if($expense->status === 'approved and forwarded' && $expense->forwardedToEmployee)
                    <br><small style="color: #666; font-size: 8px;">> {{ $expense->forwardedToEmployee->name }}</small>
                    @endif
                </td>
                <td>{{ \Illuminate\Support\Str::limit($expense->description, 50) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-box">
            <span class="total-label">Total Amount:</span>
            <span class="total-amount">{{ number_format($expenses->sum('amount'), 2) }}</span>
        </div>
    </div>
@endsection
