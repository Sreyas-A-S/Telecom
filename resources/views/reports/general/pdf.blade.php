@extends('layouts.pdf')

@section('title', 'General Report - ' . ucfirst($type))

@section('header-right')
{{ ucfirst($type) }} Requests Report
@endsection

@push('styles')
<style>
    .section {
        margin-bottom: 25px;
    }

    .badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        display: inline-block;
    }

    /* Status Badges */
    .status-approved,
    .status-completed {
        background-color: #d4edda;
        color: #155724;
    }

    .status-rejected,
    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => ucfirst($type) . ' Requests Report'])

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    @if($type == 'leave')
                    <th>Type</th>
                    <th>Date Range</th>
                    <th>Reason</th>
                    @elseif($type == 'expense')
                    <th>Type</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Amount</th>
                    @elseif($type == 'legacy_expense')
                    <th>Type</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Approved Amount</th>
                    @elseif($type == 'document')
                    <th>Document Type</th>
                    <th>Requested Date</th>
                    <th>Remarks</th>
                    @elseif($type == 'loan')
                    <th>Requested On</th>
                    <th>Amount</th>
                    @endif
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td><strong>{{ $row->user->name ?? 'N/A' }}</strong></td>

                    @if($type == 'leave')
                    <td>{{ $row->leave_type }}</td>
                    <td>
                        {{ $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('d M Y') : '' }} -
                        {{ $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('d M Y') : '' }}
                    </td>
                    <td>{{ $row->reason }}</td>
                    @elseif($type == 'expense')
                    <td>{{ $row->expense_type }}</td>
                    <td>{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '' }}</td>
                    <td>{{ $row->description }}</td>
                    <td><strong>{{ number_format($row->amount, 2) }}</strong></td>
                    @elseif($type == 'legacy_expense')
                    <td>{{ $row->expense_type }}</td>
                    <td>{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '' }}</td>
                    <td>{{ $row->description }}</td>
                    <td><strong>{{ number_format($row->amount, 2) }}</strong></td>
                    <td><strong>{{ number_format($row->approved_amount ?? 0, 2) }}</strong></td>
                    @elseif($type == 'document')
                    <td>{{ $row->documentType->name ?? 'N/A' }}</td>
                    <td>{{ $row->requested_date ? \Carbon\Carbon::parse($row->requested_date)->format('d M Y') : '' }}</td>
                    <td>{{ $row->remarks }}</td>
                    @elseif($type == 'loan')
                    <td>{{ $row->requested_on ? \Carbon\Carbon::parse($row->requested_on)->format('d M Y') : '' }}</td>
                    <td><strong>{{ number_format($row->amount, 2) }}</strong></td>
                    @endif

                    <td>
                        @php
                        $statusClass = 'status-pending';
                        if(in_array(strtolower($row->status), ['approved', 'completed', 'paid'])) {
                        $statusClass = 'status-approved';
                        } elseif(in_array(strtolower($row->status), ['rejected', 'cancelled', 'declined'])) {
                        $statusClass = 'status-rejected';
                        }
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ ucfirst($row->status) }}</span>
                    </td>
                    <td>{{ $row->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #6c757d; padding: 20px;">No records found matching the criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
