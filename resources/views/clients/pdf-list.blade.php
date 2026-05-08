@extends('layouts.pdf')

@section('title', 'Clients List')

@section('header-right')
Clients List Report
@endsection

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Clients List'])

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">Name</th>
                <th width="15%">Email</th>
                <th width="10%">Phone</th>
                <th width="20%">Address</th>
                <th width="10%">Dealership</th>
                <th width="20%">Products</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $index => $client)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $client->salutation }} {{ $client->name }}</td>
                <td>{{ $client->email }}</td>
                <td>{{ $client->phone_number }}</td>
                <td>{{ $client->address }}</td>
                <td>{{ $client->dealership->name ?? 'N/A' }}</td>
                <td>
                    {{ $client->leads->whereIn('status', ['win', 'converted_to_client'])->pluck('product.name')->filter()->unique()->implode(', ') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
