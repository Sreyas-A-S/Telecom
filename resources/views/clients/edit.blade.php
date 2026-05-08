@extends('layouts.admin')

@section('title', 'Edit Client')

@section('breadcrumb')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Edit Client</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                        <li class="breadcrumb-item active">Edit Client</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form id="editClientForm" class="theme-form" action="{{ route('clients.update', $client->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Client Details</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="salutation" class="form-label">Salutation</label>
                                                    <select class="form-select" id="salutation" name="salutation">
                                                        <option value="Mr." {{ $client->salutation =='Mr.'?'selected':'' }}>Mr.</option>
                                                        <option value="Mrs." {{ $client->salutation =='Mrs.'?'selected':'' }}>Mrs.</option>
                                                        <option value="Ms." {{ $client->salutation =='Ms.'?'selected':'' }}>Ms.</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="clientName" class="form-label">Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="clientName" name="name" value="{{ $client->name }}" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="clientEmail" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="clientEmail" name="email" value="{{ $client->email }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="clientPhone" class="form-label">Phone Number</label>
                                                    <input type="text" class="form-control" id="clientPhone" name="phone_number" value="{{ $client->phone_number }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="address" class="form-label">Address</label>
                                                    <input type="text" class="form-control" id="address" name="address" value="{{ $client->address }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">Update Client</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
