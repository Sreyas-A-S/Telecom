@extends('layouts.admin')

@section('title', '403 Access Denied')

{{-- push style --}}


@section('content')
<div class="tap-top"><i data-feather="chevrons-up"></i></div>
<!-- tap on tap ends-->
<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <!-- error-403 start-->
  <div class="error-wrapper">
    <div class="container"><img class="img-100" src="{{ asset('admin/assets/images/other-images/sad.png') }}" alt="">
      <div class="error-heading">
        <h2 class="headline font-success">403</h2>
      </div>
      <div class="col-md-8 offset-md-2">
        <p class="sub-content">Access Denied For This Page</p>
      </div>
      <div><a class="btn btn-success btn-lg" href="{{ route('dashboard') }}">BACK TO HOME PAGE</a></div>
    </div>
  </div>
  <!-- error-403 end-->
</div>
@endsection

@push('styles')
<style>
  .page-header {
    display: none;
  }
</style>
@endpush