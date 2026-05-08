@extends('layouts.admin')

@section('title', 'Settlement Notifications')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Settlement Notifications</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">HR</li>
                    <li class="breadcrumb-item"><a href="{{ route('settlements.index') }}">Settlements</a></li>
                    <li class="breadcrumb-item active">Notifications</li>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Notification Logs</h5>
                    <a href="{{ route('settlements.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Settlements</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="notifications-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Settlement</th>
                                    <th>Recipient</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                <tr>
                                    <td>{{ $notification->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        @php
                                        $settlementId = $notification->data['settlement_id'] ?? null;
                                        $settlement = $settlementId ? ($settlements[$settlementId] ?? null) : null;
                                        @endphp

                                        @if($settlement)
                                        <a href="{{ route('settlements.show', $settlement->id) }}" class="text-primary fw-bold">
                                            {{ $settlement->employee_name }}
                                        </a>
                                        <div class="small text-muted">{{ $settlement->employee_code }}</div>
                                        @elseif($settlementId)
                                        <span class="text-danger">Deleted (ID: {{ $settlementId }})</span>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notification->user)
                                        {{ $notification->user->name }} <br>
                                        <small class="text-muted">{{ $notification->user->email }}</small>
                                        @else
                                        Unknown User (ID: {{ $notification->user_id }})
                                        @endif
                                    </td>
                                    <td>
                                        {{ $notification->message }}
                                    </td>
                                    <td>
                                        @if(isset($notification->data['status']))
                                        @if($notification->data['status'] === 'sent')
                                        <span class="badge badge-success">Sent</span>
                                        @else
                                        <span class="badge badge-danger">Failed</span>
                                        @endif
                                        @else
                                        <span class="badge badge-secondary">Unknown</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#notifications-table').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                emptyTable: "No notifications found."
            }
        });
    });
</script>
@endpush