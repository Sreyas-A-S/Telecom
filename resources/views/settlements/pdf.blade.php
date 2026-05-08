@extends('layouts.pdf')

@section('title', 'Settlement Report - ' . $settlement->employee_name)

@section('header-right')
Employer Settlement Form
@endsection

@push('styles')
<style>
    .details-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .details-table th, .details-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .details-table th {
        background-color: #f2f2f2;
    }
    .remarks-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .remarks-table th, .remarks-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .remarks-table th {
        background-color: #f2f2f2;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Employer Settlement Form', 'subtitle' => 'Employee Settlement Details'])

    <h2 style="font-size: 14px; color: #1d3557;">Employee Details</h2>
    <table class="details-table">
        <tr>
            <th>Employee Code:</th>
            <td>{{ $settlement->employee_code }}</td>
            <th>Employee Name:</th>
            <td>{{ $settlement->employee_name }}</td>
        </tr>
        <tr>
            <th>Age:</th>
            <td>{{ $settlement->age }}</td>
            <th>Department:</th>
            <td>{{ $settlement->department }}</td>
        </tr>
        <tr>
            <th>Head Office/Branch:</th>
            <td>{{ $settlement->head_office_branch }}</td>
            <th>Designation:</th>
            <td>{{ $settlement->designation }}</td>
        </tr>
        <tr>
            <th>Date of Joining:</th>
            <td>{{ \Carbon\Carbon::parse($settlement->date_of_joining)->format('d-M-Y') }}</td>
            <th>Date of Resignation:</th>
            <td>{{ $settlement->date_of_resignation ? \Carbon\Carbon::parse($settlement->date_of_resignation)->format('d-M-Y') :'N/A' }}</td>
        </tr>
        <tr>
            <th>Reason for Resignation:</th>
            <td colspan="3">{{ $settlement->reason_for_resignation ??'N/A' }}</td>
        </tr>
    </table>

    <h2 style="font-size: 14px; color: #1d3557;">No Dues from Departments</h2>
    @if($settlement->remarks->isNotEmpty())
        <table class="remarks-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Remark</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($settlement->remarks as $remark)
                    <tr>
                        <td>{{ $remark->department }}</td>
                        <td>{{ $remark->name ??'N/A' }}</td>
                        <td>{{ $remark->designation ??'N/A' }}</td>
                        <td>{{ $remark->remark ??'N/A' }}</td>
                        <td style="height: 50px; vertical-align: bottom; font-size: 10px; color: #888;">
                            (Marked as Signed)
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No remarks recorded for this settlement.</p>
    @endif

    <div class="signature-section" style="margin-top: 50px; page-break-inside: avoid;">
        <div style="width: 48%; float: left; text-align: center;">
            <p style="margin-bottom: 50px;">_________________________</p>
            <p>Managing Director</p>
        </div>
        <div style="width: 48%; float: right; text-align: center;">
            <p style="margin-bottom: 50px;">_________________________</p>
            <p>Branch Manager</p>
        </div>
        <div style="clear: both;"></div>
    </div>
@endsection
