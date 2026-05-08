@extends('layouts.pdf')

@section('title', 'Employee Profiles Export')

@section('header-right')
Employee Profiles
@endsection

@push('styles')
<style>
    .page-break {
        page-break-after: always;
    }

    .page-break:last-child {
        page-break-after: auto;
    }

    .container {
        width: 100%;
        margin-bottom: 20px;
    }

    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 5px 8px;
        vertical-align: middle;
        font-size: 11px;
    }

    /* The specific style from the image */
    .header-cell {
        background-color: #d9d9d9;
        font-weight: bold;
        text-align: center;
    }

    .label-cell {
        background-color: #d9d9d9;
        font-weight: bold;
        width: 120px;
    }

    .section-header {
        background-color: #d9d9d9;
        font-weight: bold;
        text-align: center;
        padding: 6px;
        border: 1px solid #000;
        border-bottom: none;
        /* Connects to table below */
        margin-top: 15px;
        font-size: 11px;
    }

    /* Profile Image Helper */
    .photo-cell {
        text-align: center;
        vertical-align: middle;
        width: 120px;
    }

    .profile-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 1px solid #000;
    }

    .avatar-placeholder {
        width: 100px;
        height: 100px;
        line-height: 100px;
        background: #eee;
        text-align: center;
        border: 1px solid #000;
        margin: 0 auto;
        font-size: 30px;
    }

    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }
</style>
@endpush

@section('content')
    @foreach($employees as $employee)
    <div class="container {{ !$loop->last ? 'page-break' : '' }}">

        <!-- Top Bar: Order # / Date equivalent -->
        <table>
            <tr>
                <td class="header-cell" style="width: 25%;">Employee ID</td>
                <td style="width: 25%;">{{ $employee->employee_id }}</td>
                <td class="header-cell" style="width: 25%;">Date of Joining</td>
                <td style="width: 25%;">{{ $employee->joining_date }}</td>
            </tr>
        </table>

        <!-- Main Info Block: "Shipped To" style -->
        <table style="margin-top: -1px;"> <!-- Negative margin to merge borders if needed, or just keep spacing -->
            <tr>
                <!-- Left Side: Basic Contact -->
                <td style="width: 60%; padding: 0; border: none; vertical-align: top;">
                    <table style="width: 100%; margin-bottom: 0;">
                        <tr>
                            <td colspan="2" class="header-cell" style="text-align: left;">Employee Details</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Name</td>
                            <td>{{ $employee->name }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Designation</td>
                            <td>{{ $employee->designation }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Department</td>
                            <td>{{ $employee->department->name ?? '-' }}</td>
                        </tr>

                        <tr>
                            <td class="label-cell">Phone</td>
                            <td>{{ $employee->mobile }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Email</td>
                            <td>{{ $employee->email }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Address</td>
                            <td>{{ $employee->address }}</td>
                        </tr>
                    </table>
                </td>

                <!-- Right Side: Photo & Role -->
                <td style="width: 40%; vertical-align: top; border-left: 1px solid #000; padding: 10px; text-align: center;">
                    <div style="font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Profile Photo</div>
                    @if($employee->profile_pic)
                    <img src="{{ public_path('storage/' . $employee->profile_pic) }}" class="profile-img">
                    @else
                    <div class="avatar-placeholder">{{ substr($employee->name, 0, 1) }}</div>
                    @endif
                    <div style="margin-top: 15px; text-align: left;">
                        <strong>Role:</strong> {{ $employee->role->role ?? '-' }}<br>
                        <strong>Zone:</strong> {{ $employee->zone->name ?? '-' }}<br>
                        <strong>Branch:</strong> {{ $employee->branch }}<br>
                        <strong>Dealership:</strong> {{ $employee->dealership->name ?? '-' }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Personal Details Strip -->
        <div class="section-header">PERSONAL INFORMATION</div>
        <table style="margin-top: -1px;">
            <tr>
                <td class="label-cell">Date of Birth</td>
                <td>{{ $employee->dob }}</td>
                <td class="label-cell">Gender</td>
                <td>{{ $employee->gender }}</td>
            </tr>
            <tr>
                <td class="label-cell">Marital Status</td>
                <td>{{ $employee->marital_status }}</td>
                <td class="label-cell">Blood Group</td>
                <td>{{ $employee->blood_group }}</td>
            </tr>
            <tr>
                <td class="label-cell">Shirt Size</td>
                <td>{{ $employee->shirt_size }}</td>
                <td class="label-cell">T-Shirt Size</td>
                <td>{{ $employee->tshirt_size }}</td>
            </tr>
        </table>

        <!-- Family Strip -->
        <div class="section-header">FAMILY & EMERGENCY</div>
        <table style="margin-top: -1px;">
            <tr>
                <td class="header-cell">Father's Name</td>
                <td class="header-cell">Mother's Name</td>
                <td class="header-cell">Spouse's Name</td>
                <td class="header-cell">Emergency Contact</td>
            </tr>
            <tr>
                <td style="text-align: center;">{{ $employee->father_name }}</td>
                <td style="text-align: center;">{{ $employee->mother_name }}</td>
                <td style="text-align: center;">{{ $employee->spouse_name }}</td>
                <td style="text-align: center;"><strong>{{ $employee->emergency_contact }}</strong></td>
            </tr>
        </table>

        <!-- Financial Strip -->
        <div class="section-header">FINANCIAL & STATUTORY</div>
        <table style="margin-top: -1px;">
            <tr>
                <td class="label-cell">Bank Name</td>
                <td>{{ $employee->bank_name }}</td>
                <td class="label-cell">Account No</td>
                <td>{{ $employee->account_number }}</td>
            </tr>
            <tr>
                <td class="label-cell">IFSC Code</td>
                <td colspan="3">{{ $employee->ifsc_code }}</td>
            </tr>
        </table>

        <!-- Statutory Grid -->
        <table style="margin-top: 10px;">
            <tr class="header-cell">
                <td>PAN Number</td>
                <td>Aadhar Number</td>
                <td>PF Number</td>
                <td>ESI Number</td>
                <td>LWF Number</td>
            </tr>
            <tr style="text-align: center;">
                <td>{{ $employee->pan_no }}</td>
                <td>{{ $employee->aadhar_no }}</td>
                <td>{{ $employee->pf_no }}</td>
                <td>{{ $employee->esi_no }}</td>
                <td>{{ $employee->lwf_no }}</td>
            </tr>
        </table>

        <!-- Footer Signature -->
        <div style="margin-top: 40px;">
            <table style="border: none;">
                <tr style="border: none;">
                    <td style="border: none; width: 60%; vertical-align: top;">
                        <strong>Comments:</strong><br>
                        ____________________________________________________<br><br>
                        ____________________________________________________
                    </td>
                    <td style="border: none; width: 40%; vertical-align: bottom; text-align: right;">
                        <br><br>
                        __________________________<br>
                        Authorized Signature
                    </td>
                </tr>
            </table>
        </div>

    </div>
    @endforeach
@endsection