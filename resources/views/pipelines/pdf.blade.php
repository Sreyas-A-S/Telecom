@extends('layouts.pdf')

@section('title', 'Pipeline Details')

@section('header-right')
Pipeline Details
@endsection

@push('styles')
<style>
    .section-title {
        font-size: 14px;
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 10px;
        border-bottom: 1px solid #000;
        padding-bottom: 5px;
        color: #1d3557;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Pipeline Details'])

    <div class="section-title">Customer Information</div>
    <table>
        <tr>
            <th width="20%">Name</th>
            <td width="30%">{{ $lead->salutation }} {{ $lead->name }}</td>
            <th width="20%">Email</th>
            <td width="30%">{{ $lead->email ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $lead->phone_number ?? 'N/A' }}</td>
            <th>Location</th>
            <td>{{ $lead->location ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Lead Details</div>
    <table>
        <tr>
            <th width="20%">Dealership</th>
            <td width="30%">{{ $lead->dealership->name ?? 'N/A' }}</td>
            <th width="20%">Source</th>
            <td width="30%">{{ $lead->leadSource->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Category</th>
            <td>{{ $lead->leadCategory->name ?? 'N/A' }}</td>
            <th>Product</th>
            <td>{{ $lead->product->name ?? 'N/A' }} {{ $lead->productModel ? '-' . $lead->productModel->name : '' }}
            </td>
        </tr>
        <tr>
            <th>Lead Value</th>
            <td>{{ $lead->lead_value ? '₹' . number_format($lead->lead_value, 2) : 'N/A' }}</td>
            <th>Chance of Success</th>
            <td>{{ $lead->chance_of_success }}%</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $lead->status }}</td>
            <th>Stage</th>
            <td>{{ $lead->stage ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Type</th>
            <td>{{ $lead->type ?? 'N/A' }}</td>
            <th>Billing</th>
            <td>{{ $lead->billing ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Financier</th>
            <td>{{ $lead->financier ?? 'N/A' }}</td>
            <th>Login Status</th>
            <td>{{ $lead->login_status ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Assigned Employee</th>
            <td colspan="3">{{ $lead->employee->name ?? 'Unassigned' }}</td>
        </tr>
    </table>

    <div class="section-title">Additional Information</div>
    <table>
        <tr>
            <th width="20%">Allow Follow-up</th>
            <td>{{ $lead->allow_follow_up ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <th>Remarks</th>
            <td>{{ $lead->remarks ?? 'N/A' }}</td>
        </tr>
    </table>
@endsection
