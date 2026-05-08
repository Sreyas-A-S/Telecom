@extends('layouts.pdf')

@section('title', 'Loss Order Details - ' . $lossOrder->id)

@section('header-right')
Loss Order Details
@endsection

@push('styles')
<style>
    .section {
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 10px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 5px;
        color: #1d3557;
    }

    table th {
        width: 35%;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Loss Order Details'])

    <div class="section">
        <div class="section-title">Order Information</div>
        <table>
            <tr>
                <th>Dealership</th>
                <td>{{ $lossOrder->dealership->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Month</th>
                <td>{{ $lossOrder->month }}</td>
            </tr>
            <tr>
                <th>Product Name</th>
                <td>{{ $lossOrder->product_name }}</td>
            </tr>
            <tr>
                <th>Tonnage</th>
                <td>{{ $lossOrder->tonnage }}</td>
            </tr>
            <tr>
                <th>Product Model Name</th>
                <td>{{ $lossOrder->product_model_name }}</td>
            </tr>
            <tr>
                <th>Model Series Name</th>
                <td>{{ $lossOrder->model_series_name }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Customer & Loss Details</div>
        <table>
            <tr>
                <th>Customer</th>
                <td>{{ $lossOrder->customer }}</td>
            </tr>
            <tr>
                <th>Segment</th>
                <td>
                    @if($lossOrder->segment == 'Rented')
                    Rented
                    @elseif($lossOrder->segment == 'Captive')
                    Captive
                    @else
                    {{ $lossOrder->segment }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Application</th>
                <td>{{ $lossOrder->application }}</td>
            </tr>
            <tr>
                <th>Financier</th>
                <td>{{ $lossOrder->financier }}</td>
            </tr>
            <tr>
                <th>District</th>
                <td>{{ $lossOrder->district }}</td>
            </tr>
            <tr>
                <th>Category</th>
                <td>{{ $lossOrder->category }}</td>
            </tr>
            <tr>
                <th>Participation</th>
                <td>{{ $lossOrder->participation }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Additional Information</div>
        <table>
            <tr>
                <th>Reasons for Loss</th>
                <td>{{ $lossOrder->reasons_for_loss }}</td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td>{{ $lossOrder->remarks }}</td>
            </tr>
            <tr>
                <th>Engineer Name</th>
                <td>{{ $lossOrder->engineer_name }}</td>
            </tr>
        </table>
    </div>
@endsection
