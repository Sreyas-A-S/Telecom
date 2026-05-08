@extends('layouts.pdf')

@section('title', 'Client Profile - ' . $client->name)

@section('header-right')
Client Profile Report
@endsection

@push('styles')
<style>
    .section {
        margin-bottom: 25px;
    }

    .section-title {
        font-size: 14px;
        font-weight: bold;
        color: #1d3557;
        border-bottom: 2px solid #edeff2;
        padding-bottom: 5px;
        margin-bottom: 15px;
        text-transform: uppercase;
    }

    .info-grid {
        display: table;
        width: 100%;
        margin-bottom: 10px;
    }

    .info-row {
        display: table-row;
    }

    .info-label {
        display: table-cell;
        font-weight: bold;
        width: 120px;
        padding: 5px 0;
        color: #6c757d;
    }

    .info-value {
        display: table-cell;
        padding: 5px 0;
    }

    .sub-table {
        margin: 0;
        background-color: #fcfcfc;
        border: 1px solid #eee;
    }

    .sub-table th {
        background-color: #f1f3f5;
        font-size: 9px;
        padding: 5px 8px;
    }

    .sub-table td {
        font-size: 9px;
        padding: 4px 8px;
    }

    .badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        display: inline-block;
    }

    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-secondary {
        background-color: #e2e3e5;
        color: #383d41;
    }

    .stats-container {
        display: table;
        width: 100%;
        margin-bottom: 20px;
    }

    .stat-card {
        display: table-cell;
        width: 25%;
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: bold;
        color: #1d3557;
        display: block;
    }

    .stat-label {
        font-size: 10px;
        color: #6c757d;
        text-transform: uppercase;
    }

    .nest-group {
        margin-left: 20px;
        border-left: 2px solid #eee;
        padding-left: 10px;
        margin-bottom: 10px;
    }

    .nest-title {
        font-weight: bold;
        font-size: 9px;
        color: #457b9d;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => trim(($client->salutation ? $client->salutation . ' ' : '') . $client->name), 'subtitle' => 'Client Profile'])

    <div class="section">
        <div class="section-title">Contact Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $client->phone_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $client->email ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value">
                    {{ $client->address ?? '' }}
                    @if($client->district || $client->state)
                        ({{ $client->district->name ?? '' }}{{ $client->district && $client->state ? ', ' : '' }}{{ $client->state->name ?? '' }})
                    @endif
                    @if(!$client->address && !$client->district && !$client->state)
                        N/A
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Overview Statistics</div>
        <div class="stats-container">
            <div class="stat-card">
                <span class="stat-value">{{ $client->leads->count() }}</span>
                <span class="stat-label">Total Leads</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">{{ $services->count() }}</span>
                <span class="stat-label">Total Services</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">{{ $uniqueProducts->count() }}</span>
                <span class="stat-label">Total Products</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">{{ $totalInteractions }}</span>
                <span class="stat-label">Total Interactions</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Leads History & Interactions</div>
        @forelse($client->leads as $lead)
        <div style="background-color: #fcfcfc; border: 1px solid #edeff2; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
            <div style="margin-bottom: 10px;">
                <span style="font-weight: bold; font-size: 11px; color: #1d3557;">LEAD #{{ $lead->id }}</span>
                <span style="float: right; color: #6c757d;">{{ $lead->created_at->format('d M Y') }}</span>
            </div>

            <table style="margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th width="40%">Product/Model</th>
                        <th width="20%">Status</th>
                        <th width="20%">Value</th>
                        <th width="20%">Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $lead->product->name ?? 'N/A' }}</strong><br>
                            <small>{{ $lead->productModel->name ?? '' }}</small>
                        </td>
                        <td>
                            @php
                            $statusValue = $lead->status;
                            $displayStatus = str_replace('_', ' ', $lead->last_status_before_conversion ?? $lead->status);
                            $statusColor = 'badge-secondary';
                            
                            if($statusValue == 'converted_to_client' || $statusValue == 'win' || $statusValue == 'won' || str_contains(strtolower($displayStatus), 'win')) {
                            $statusColor = 'badge-success';
                            $displayStatus = 'Win';
                            } elseif(str_contains(strtolower($displayStatus), 'lost')) {
                            $statusColor = 'badge-danger';
                            } elseif(str_contains(strtolower($displayStatus), 'warm')) {
                            $statusColor = 'badge-warning';
                            } elseif(str_contains(strtolower($displayStatus), 'cold')) {
                            $statusColor = 'badge-info';
                            }
                            @endphp
                            <span class="badge {{ $statusColor }}">{{ $displayStatus }}</span>
                        </td>
                        <td>₹{{ number_format($lead->lead_value, 2) }}</td>
                        <td>{{ $lead->agent->name ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>

            @if($lead->followups->count() > 0)
            <div class="nest-group">
                <div class="nest-title">Follow-up History</div>
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th width="25%">Date</th>
                            <th width="15%">Status</th>
                            <th width="40%">Remarks</th>
                            <th width="20%">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lead->followups as $followup)
                        <tr>
                            <td>{{ $followup->created_at->format('d M Y H:i') }}</td>
                            <td>{{ str_replace('_', ' ', $followup->new_status) }}</td>
                            <td>{{ $followup->remarks ?? 'N/A' }}</td>
                            <td>{{ $followup->user->name ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($lead->tasks->count() > 0)
            <div class="nest-group">
                <div class="nest-title">Associated Tasks</div>
                @foreach($lead->tasks as $task)
                <div style="border: 1px solid #f1f3f5; padding: 8px; margin-bottom: 8px; background: white;">
                    <div style="margin-bottom: 5px;">
                        <strong style="color: #457b9d;">{{ $task->title }}</strong>
                        <span style="float: right;" class="badge {{ $task->status == 'completed' ? 'badge-success' : 'badge-warning' }}">{{ $task->status }}</span>
                    </div>
                    <div style="font-size: 8px; color: #6c757d; margin-bottom: 5px;">
                        Type: {{ ucfirst($task->type) }} | Assigned To: {{ $task->assignedEmployee->name ?? 'N/A' }} |
                        Due: {{ $task->due_date ? $task->due_date->format('d M Y') : 'N/A' }} | Time: {{ $task->getFormattedElapsedTime() }}
                    </div>

                    @if($task->followups->count() > 0)
                    <table class="sub-table">
                        <tbody>
                            @foreach($task->followups as $tf)
                            <tr>
                                <td width="20%" style="color: #6c757d;">{{ $tf->created_at->format('d M Y') }}</td>
                                <td width="60%">{{ $tf->notes }}</td>
                                <td width="20%" style="text-align: right; color: #6c757d;">{{ $tf->user->name ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <p style="text-align: center; color: #6c757d; padding: 20px;">No leads history found.</p>
        @endforelse
    </div>

    <div class="section" style="page-break-before: always;">
        <div class="section-title">Services History & Tasks</div>
        @forelse($services as $service)
        <div style="background-color: #fcfcfc; border: 1px solid #edeff2; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
            <div style="margin-bottom: 10px;">
                <span style="font-weight: bold; font-size: 11px; color: #1d3557;">SERVICE #{{ $service->id }}</span>
                <span style="float: right; color: #6c757d;">{{ $service->created_at->format('d M Y') }}</span>
            </div>

            <table style="margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th width="40%">Product/Model</th>
                        <th width="20%">Service Type</th>
                        <th width="20%">Engineer</th>
                        <th width="20%">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $service->product->name ?? 'N/A' }}</strong><br>
                            <small>{{ $service->productModel->name ?? '' }}</small>
                        </td>
                        <td>{{ str_replace('_', ' ', $service->type_of_service) }}</td>
                        <td>{{ $service->serviceEngineer->name ?? 'N/A' }}</td>
                        <td>₹{{ number_format($service->price, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            @if($service->tasks->count() > 0)
            <div class="nest-group">
                <div class="nest-title">Service Tasks</div>
                @foreach($service->tasks as $task)
                <div style="border: 1px solid #f1f3f5; padding: 8px; margin-bottom: 8px; background: white;">
                    <div style="margin-bottom: 5px;">
                        <strong style="color: #457b9d;">{{ $task->title }}</strong>
                        <span style="float: right;" class="badge {{ $task->status == 'completed' ? 'badge-success' : 'badge-warning' }}">{{ $task->status }}</span>
                    </div>
                    <div style="font-size: 8px; color: #6c757d; margin-bottom: 5px;">
                        Type: {{ ucfirst($task->type) }} | Assigned To: {{ $task->assignedEmployee->name ?? 'N/A' }} |
                        Due: {{ $task->due_date ? $task->due_date->format('d M Y') : 'N/A' }} | Time: {{ $task->getFormattedElapsedTime() }}
                    </div>

                    @if($task->followups->count() > 0)
                    <table class="sub-table">
                        <tbody>
                            @foreach($task->followups as $tf)
                            <tr>
                                <td width="20%" style="color: #6c757d;">{{ $tf->created_at->format('d M Y') }}</td>
                                <td width="60%">{{ $tf->notes }}</td>
                                <td width="20%" style="text-align: right; color: #6c757d;">{{ $tf->user->name ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <p style="text-align: center; color: #6c757d; padding: 20px;">No services history found.</p>
        @endforelse
    </div>

    <div class="section">
        <div class="section-title">Owned Products</div>
        <table>
            <thead>
                <tr>
                    <th width="25%">Product Name</th>
                    <th width="15%">Model</th>
                    <th width="15%">Series</th>
                    <th width="15%">Engine Model</th>
                    <th width="15%">DOC</th>
                    <th width="15%">Acquired</th>
                </tr>
            </thead>
            <tbody>
                @foreach($uniqueProducts as $p)
                <tr>
                    <td><strong>{{ $p['product']->name }}</strong></td>
                    <td>{{ $p['model']->name ?? 'N/A' }}</td>
                    <td>{{ $p['series']->name ?? 'N/A' }}</td>
                    <td>{{ $p['engine_model'] ?? 'N/A' }}</td>
                    <td>{{ $p['doc'] ? (\Carbon\Carbon::parse($p['doc'])->format('d M Y')) : 'N/A' }}</td>
                    <td>{{ $p['date']->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
