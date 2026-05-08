@extends('layouts.pdf')

@section('title', 'Performance Review')

@section('header-right')
Performance Review
@endsection

@push('styles')
<style>
    .details, .evaluation {
        margin-bottom: 15px;
    }

    .details table,
    .evaluation table {
        width: 100%;
        border-collapse: collapse;
    }

    .details th,
    .details td,
    .evaluation th,
    .evaluation td {
        border: 1px solid #ddd;
        padding: 4px;
        text-align: left;
    }

    .details th {
        width: 30%;
    }

    .evaluation th {
        background-color: #f2f2f2;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Performance Review'])

    <div class="details">
        <h2 style="font-size: 14px; color: #1d3557;">Review Details</h2>
        <table>
            <tr>
                <th>Employee Name:</th>
                <td>{{ $review->employee->employee->name ??'N/A' }}</td>
            </tr>
            <tr>
                <th>Reviewer Name:</th>
                <td>{{ $review->reviewer->employee->name ??'N/A' }}</td>
            </tr>
            <tr>
                <th>Review Date:</th>
                <td>{{ $review->review_date }}</td>
            </tr>
            <tr>
                <th>Review Period:</th>
                <td>{{ $review->review_period }}</td>
            </tr>
        </table>
    </div>

    <div class="evaluation">
        <h2 style="font-size: 14px; color: #1d3557;">Comments / Feedback</h2>
        @forelse($review->comments as $comment)
        <div style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <div style="font-weight: bold; margin-bottom: 4px; color: #1d3557;">{{ $comment->user->name }} <span style="font-weight: normal; font-size: 10px; color: #666;">({{ $comment->created_at->format('d M Y h:i A') }})</span></div>
            <div style="margin-top: 5px;">{{ $comment->comment }}</div>
        </div>
        @empty
        <p>No comments recorded.</p>
        @endforelse
    </div>
@endsection
