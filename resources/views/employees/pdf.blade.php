@extends('layouts.pdf')

@section('title', 'Employee Details - ' . $employee->name)

@section('header-right')
Employee Profile
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

    .profile-pic {
        text-align: center;
        margin-bottom: 20px;
    }

    .profile-pic img {
        border-radius: 50%;
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 3px solid #eee;
    }

    table th {
        width: 35%;
    }
</style>
@endpush

@section('content')
@include('pdf.partials.report-header', ['title' => $employee->name, 'subtitle' => $employee->designation])

@if($employee->profile_pic)
<div class="profile-pic">
    {{-- Use public_path for correct PDF rendering --}}
    <img src="{{ public_path('storage/' . $employee->profile_pic) }}" alt="{{ $employee->name }}">
</div>
@endif

<div class="section">
    <div class="section-title">Personal Information</div>
    <table>
        <tr>
            <th>Name</th>
            <td>{{ $employee->name }}</td>
        </tr>
        <tr>
            <th>Employee ID</th>
            <td>{{ $employee->employee_id }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $employee->email }}</td>
        </tr>
        <tr>
            <th>Mobile</th>
            <td>{{ $employee->mobile }}</td>
        </tr>
        <tr>
            <th>Gender</th>
            <td>{{ $employee->gender }}</td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td>{{ $employee->dob }}</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>{{ $employee->address }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Employment Details</div>
    <table>
        <tr>
            <th>Joining Date</th>
            <td>{{ $employee->joining_date }}</td>
        </tr>
        <tr>
            <th>Designation</th>
            <td>{{ $employee->designation }}</td>
        </tr>
        <tr>
            <th>Department</th>
            <td>{{ $employee->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Role</th>
            <td>{{ $employee->role->role ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Dealership</th>
            <td>{{ $employee->dealership->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Zone</th>

            @if($employee->profile_pic)
            <div class="profile-pic">
                {{-- Use public_path for correct PDF rendering --}}
                <img src="{{ public_path('storage/' . $employee->profile_pic) }}" alt="{{ $employee->name }}">
            </div>
            @endif

            <div class="section">
                <div class="section-title">Personal Information</div>
                <table>
                    <tr>
                        <th>Name</th>
                        <td>{{ $employee->name }}</td>
                    </tr>
                    <tr>
                        <th>Employee ID</th>
                        <td>{{ $employee->employee_id }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $employee->email }}</td>
                    </tr>
                    <tr>
                        <th>Mobile</th>
                        <td>{{ $employee->mobile }}</td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td>{{ $employee->gender }}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{ $employee->dob }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $employee->address }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Employment Details</div>
                <table>
                    <tr>
                        <th>Joining Date</th>
                        <td>{{ $employee->joining_date }}</td>
                    </tr>
                    <tr>
                        <th>Designation</th>
                        <td>{{ $employee->designation }}</td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td>{{ $employee->department->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>{{ $employee->role->role ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Dealership</th>
                        <td>{{ $employee->dealership->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Zone</th>
                        <td>{{ $employee->zone->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Reporting To</th>
                        <td>{{ $employee->reporter2->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Is Agent</th>
                        <td>{{ $employee->is_broker ? 'Yes' : 'No' }}</td>
                    </tr>
                </table>
            </div>
            @endsection