@extends('layouts.pdf')

@section('title', 'Visit Audit Report')

@section('header-right')
Visit Audit Report
@endsection

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Visit Audit Report'])

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Best support for Rupee and special characters in DomPDF */
        }
    </style>

    @php
        $embedImageDataUri = function ($imgPath) {
            $imgPath = ltrim((string) $imgPath, '/');
            if ($imgPath === '') return null;

            $candidates = [
                public_path($imgPath),
                public_path('storage/' . $imgPath),
            ];

            $realPath = null;
            foreach ($candidates as $candidate) {
                if (is_file($candidate)) {
                    $realPath = $candidate;
                    break;
                }
            }

            if (!$realPath) return null;

            $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };

            $bytes = @file_get_contents($realPath);
            if ($bytes === false) return null;

            return "data:{$mime};base64," . base64_encode($bytes);
        };
    @endphp

    @if(count($data) === 1)
        @php $row = $data[0]; @endphp
        
        <style>
            .summary-container {
                display: table;
                width: 100%;
                margin-bottom: 20px;
                background-color: #fcfcfc;
                border: 1px solid #eee;
                padding: 15px;
            }
            .summary-left, .summary-right {
                display: table-cell;
                width: 50%;
                vertical-align: top;
            }
            .info-label {
                font-size: 11px;
                color: #666;
                margin-bottom: 2px;
            }
            .info-value {
                font-size: 13px;
                font-weight: bold;
                color: #1d3557;
                margin-bottom: 10px;
            }
            .stats-row {
                display: table;
                width: 100%;
                margin: 20px 0;
                border-top: 1px solid #eee;
                border-bottom: 1px solid #eee;
                padding: 10px 0;
            }
            .stat-box {
                display: table-cell;
                width: 16.6%;
                text-align: center;
                border-right: 1px solid #eee;
            }
            .stat-box:last-child { border-right: none; }
            .stat-label { font-size: 10px; color: #777; text-transform: uppercase; }
            .stat-value { font-size: 14px; font-weight: bold; color: #1d3557; }
            
            .points-table {
                width: 100%;
                margin-top: 20px;
                font-size: 12px;
            }
            .points-table th { background-color: #f1f4f8; padding: 10px; }
            .points-table td { padding: 12px 10px; border-bottom: 1px solid #eee; }
            .point-marker { color: #e63946; font-weight: bold; }
            .img-link { color: #457b9d; text-decoration: none; font-size: 10px; }
            .section-title { font-size: 16px; font-weight: bold; color: #1d3557; margin-top: 30px; margin-bottom: 10px; border-left: 4px solid #e63946; padding-left: 10px; }
        </style>

        <div class="summary-container">
            <div class="summary-left">
                <div class="info-label">Employee</div>
                <div class="info-value">{{ $row['user_name'] }} ({{ $row['employee_code'] }})</div>
                
                <div class="info-label">Organization Context</div>
                <div class="info-value">{{ $row['designation'] }} | {{ $row['department'] }} | {{ $row['dealership'] }}</div>
                
                <div class="info-label">Reporting To</div>
                <div class="info-value">{{ $row['manager'] }}</div>
            </div>
            <div class="summary-right">
                <div class="info-label">Visit Date</div>
                <div class="info-value">{{ $row['date'] }}</div>
                
                <div class="info-label">Task Type(s)</div>
                <div class="info-value">
                    @foreach(explode(', ', $row['task_type']) as $task)
                        <div style="margin-bottom: 2px;">• {{ $task }}</div>
                    @endforeach
                </div>
                
                <div class="info-label">Clients</div>
                <div class="info-value">{{ $row['client_name'] }}</div>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-label">Start Time</div>
                <div class="stat-value">{{ $row['started_time'] }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">End Time</div>
                <div class="stat-value">{{ $row['ended_time'] }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Duration</div>
                <div class="stat-value">{{ $row['time_spent'] }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Task Time</div>
                <div class="stat-value">{{ $row['task_duration'] }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Distance</div>
                <div class="stat-value">{{ $row['kms_travelled'] }} km</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Expense</div>
                <div class="stat-value">Rs. {{ number_format((float) ($row['travel_expense'] ?? 0), 2) }}</div>
            </div>
        </div>

        <div class="section-title">Points of Interest (Visited Points)</div>
        <p style="font-size: 11px; color: #666; margin-bottom: 15px;">A point visit is recorded when a user stays in a specific location for more than 5 minutes.</p>

        <table class="points-table">
            <thead>
                <tr>
                    <th style="width: 8%">#</th>
                    <th style="width: 15%">Time</th>
                    <th style="width: 20%">Location</th>
                    <th>Stationary Activity / Remarks</th>
                    <th style="width: 15%">Images</th>
                </tr>
            </thead>
            <tbody>
                @forelse($row['halt_points'] as $index => $hp)
                <tr>
                    <td class="point-marker">{{ $index + 1 }}</td>
                    <td>{{ $hp['start_time'] }} - {{ $hp['end_time'] }}</td>
                    <td style="font-size: 10px;">{{ $hp['lat'] }}, {{ $hp['lng'] }}</td>
                    <td>
                        @if($hp['active_tasks'])
                            <strong>Tasks:</strong> {{ $hp['active_tasks'] }}<br>
                        @endif
                        {{ $hp['remarks'] ?: 'No specific remarks recorded.' }}
                    </td>
                    <td>
                        @php
                            $images = $hp['images'] ?? collect();
                            $maxImages = 4;
                        @endphp

                        @if($images instanceof \Illuminate\Support\Collection && $images->isNotEmpty())
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @foreach($images->take($maxImages) as $img)
                                    @php $dataUri = $embedImageDataUri($img); @endphp
                                    @if($dataUri)
                                        <img src="{{ $dataUri }}" alt="Visit Image" style="width: 70px; height: 52px; object-fit: cover; border: 1px solid #e5e7eb; border-radius: 4px;">
                                    @else
                                        <span class="img-link">{{ $loop->iteration }}.</span>
                                    @endif
                                @endforeach
                            </div>
                            @if($images->count() > $maxImages)
                                <div style="margin-top: 4px; font-size: 10px; color: #6b7280;">+{{ $images->count() - $maxImages }} more</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No specific points of interest recorded for this route.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Final Visit Remarks</div>
        <div style="font-size: 12px; padding: 10px; background: #f9f9f9; border: 1px solid #eee;">
            {{ $row['visit_remarks'] }}<br>
            @if($row['remarks'] && $row['remarks'] !== 'N/A')
                <div style="margin-top: 5px; color: #555;"><strong>Note:</strong> {{ $row['remarks'] }}</div>
            @endif
        </div>

    @else
        <style>
            table {
                font-size: 10px; 
                table-layout: fixed;
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                padding: 6px 3px;
                word-wrap: break-word;
                vertical-align: top;
                border-bottom: 1px solid #eee;
            }
            th { background-color: #f8f9fa; font-weight: bold; }
            .text-nowrap { white-space: nowrap; }
            .small-text { font-size: 9px; color: #666; display: block; margin-top: 2px; }
            
            .col-date { width: 65px; }
            .col-emp { width: 110px; }
            .col-org { width: 110px; }
            .col-task { width: 100px; }
            .col-points { width: 130px; }
            .col-timing { width: 95px; }
            .col-dist { width: 90px; }
            .col-client { width: 120px; }
            .col-remarks { width: auto; }
            
            .secondary-row td {
                background-color: #fafbfc;
                padding: 4px 10px 8px 10px;
                border-bottom: 2px solid #ccc;
            }
            .main-row td {
                border-bottom: none;
                padding-bottom: 2px;
            }
            .lead-service-info {
                display: block;
                border-left: 2px solid #1d3557;
                padding-left: 8px;
                margin-top: 2px;
            }
        </style>

        <table>
            <thead>
                <tr>
                    <th class="col-date">Date</th>
                    <th class="col-emp">Employee (Code)</th>
                    <th class="col-org">Org. Context</th>
                    <th class="col-task">Task & Vehicle</th>
                    <th class="col-points">Points</th>
                    <th class="col-timing">Timing & Duration</th>
                    <th class="col-dist">Dist & Exp</th>
                    <th class="col-client">Client & Location</th>
                    <th class="col-remarks">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr class="main-row">
                    <td class="text-nowrap">{{ $row['date'] }}</td>
                    <td>
                        <strong>{{ $row['user_name'] }}</strong>
                        <span class="small-text">({{ $row['employee_code'] }})</span>
                    </td>
                    <td>
                        {{ $row['designation'] }}
                        <span class="small-text">{{ $row['department'] }} | {{ $row['dealership'] }}</span>
                    </td>
                    <td>
                        {{ $row['task_type'] }}
                        <span class="small-text">Via: {{ $row['vehicle_type'] }}</span>
                    </td>
                    <td>
                        <strong>{{ $row['point_count'] }} Points</strong>
                        <span class="small-text">{{ $row['point_info'] }}</span>
                    </td>
                    <td class="text-nowrap">
                        {{ $row['started_time'] }} - {{ $row['ended_time'] }}
                        <span class="small-text">Dur: {{ $row['time_spent'] }}</span>
                        <span class="small-text">Task: {{ $row['task_duration'] }}</span>
                    </td>
                    <td>
                        {{ $row['kms_travelled'] }} km
                        <span class="small-text">Exp: Rs. {{ number_format((float) ($row['travel_expense'] ?? 0), 2) }}</span>
                        <span class="small-text">TA: Rs. {{ number_format((float) ($row['call_ta'] ?? 0), 2) }}</span>
                    </td>
                    <td>
                        {{ $row['client_name'] }}
                        <span class="small-text">{{ $row['location'] }}</span>
                    </td>
                    <td>
                        {{ $row['visit_remarks'] }}
                        @if($row['remarks'] && $row['remarks'] !== 'N/A')
                            <span class="small-text">Note: {{ $row['remarks'] }}</span>
                        @endif
                    </td>
                </tr>
                <tr class="secondary-row">
                    <td colspan="9">
                        <div class="lead-service-info">
                            <strong>Client Info:</strong> {{ $row['client_full_info'] }}<br>
                            <strong>Details:</strong><br>
                            {!! nl2br(e($row['lead_service_summary'])) !!}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
