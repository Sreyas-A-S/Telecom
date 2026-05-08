@extends('layouts.admin')

@section('title', 'Timeline')

@push('styles')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <style>
        .legend-color {
            width: 12px;
            height: 12px;
            display: inline-block;
            margin-right: 8px;
            border-radius: 2px;
        }

        .timeline-wrapper {
            padding: 30px 20px 45px;
            background: #ffffff;
            border-radius: 20px;
            margin-top: 15px;
            border: 1px solid rgba(0, 0, 0, 0.03);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #6366f1 #f1f5f9;
        }

        #horizontal-timeline {
            display: flex;
            align-items: center;
            min-height: 80px;
            position: relative;
            padding: 20px 10px;
            z-index: 1;
        }

        #horizontal-timeline::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #f1f5f9;
            z-index: 0;
            transform: translateY(-50%);
        }

        .timeline-segment {
            display: flex;
            flex-direction: row;
            align-items: center;
            position: relative;
            z-index: 2;
            padding: 0 10px;
            min-width: 120px;
        }

        .segment-title {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0;
            margin-right: 8px;
            white-space: nowrap;
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
        }

        .timeline-group-pill {
            flex: 1;
            height: 6px;
            border-radius: 10px;
            position: relative;
            background: #e2e8f0;
            margin: 0 5px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
            min-width: 100px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            padding: 0 8px;
            gap: 12px;
            overflow: visible;
        }

        .sub-marker-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 0 4px;
            transition: transform 0.2s ease;
        }

        .sub-marker-wrapper:hover {
            transform: translateY(-2px);
            z-index: 10;
        }

        .sub-marker-circle {
            width: 14px;
            height: 14px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: inherit;
            font-size: 7px;
            font-weight: 800;
            border: 1.2px solid currentColor;
            z-index: 3;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .sub-marker-time {
            font-size: 6.5px;
            font-weight: 700;
            color: #64748b;
            margin-top: 4px;
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.8);
            padding: 1px 3px;
            border-radius: 4px;
            pointer-events: none;
        }

        .timeline-date-marker {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 15px;
            position: relative;
            z-index: 5;
        }

        .date-badge {
            background: #1e293b;
            color: white;
            font-size: 8px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }

        .timeline-connector {
            display: flex;
            flex-direction: row;
            align-items: center;
            position: relative;
            z-index: 1;
            padding: 0 8px;
            min-width: 100px;
        }

        .connector-line {
            height: 1px;
            flex: 1;
            border-top: 1px dashed #cbd5e1;
            margin-bottom: 0;
            min-width: 30px;
            opacity: 0.5;
        }

        .connector-line.travel {
            border-top: 2px dashed #6366f1;
            opacity: 0.6;
        }

        .marker-point {
            display: flex;
            flex-direction: row;
            align-items: center;
            z-index: 5;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .marker-icon {
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }

        .segment-label {
            font-size: 9px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0;
            margin-right: 8px;
        }

        .segment-time {
            font-size: 9px;
            font-weight: 600;
            color: #94a3b8;
            white-space: nowrap;
            margin-left: 12px;
            flex-shrink: 0;
        }

        .timeline-marker-transition {
            z-index: 5;
            background: white;
            padding: 4px;
            border-radius: 50%;
            border: 2px solid #f1f5f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: relative;
            flex-shrink: 0;
        }

        .transition-icon {
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline-segment:hover .timeline-tooltip,
        .timeline-connector:hover .timeline-tooltip {
            display: block;
        }

        .timeline-tooltip {
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 11px;
            display: none;
            white-space: nowrap;
            pointer-events: none;
            z-index: 100;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        /* AJAX Loader Styles */
        .ajax-loader-wrapper {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 100;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
        }

        .ajax-loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #7366ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Premium Design Improvements */
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 0.75rem 1rem;
        }

        .card-header h5 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0;
            color: #334155;
        }

        .static-top-widget-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .static-top-widget-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .static-top-widget-card h6 {
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            color: #64748b !important;
        }

        .static-top-widget-card h5 {
            font-size: 0.72rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }

        .static-top-widget-card .small {
            font-size: 0.65rem !important;
        }

        .widget-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--bs-primary-rgb), 0.08);
        }

        #visits-table {
            font-size: 0.8rem;
        }

        #visits-table tbody tr {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        #visits-table tbody tr:hover {
            background-color: rgba(39, 190, 255, 0.03);
        }

        #visits-table tbody tr.active-visit {
            background-color: rgba(39, 190, 255, 0.08) !important;
        }

        #visits-table thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.04em;
            padding: 10px 8px;
            border-top: none;
        }

        .btn-info {
            background-color: #27BEFF;
            border-color: #27BEFF;
            color: #fff;
        }

        .map-legend {
            border-radius: 12px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            background: rgba(255, 255, 255, 0.96);
            width: 220px;
            max-width: calc(100vw - 32px);
            margin: 12px;
        }

        .map-legend h6 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .map-legend-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .map-legend-toggle {
            border: 0;
            background: #f8fafc;
            color: #475569;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .map-legend-toggle:hover {
            background: #e2e8f0;
        }

        .map-legend .small {
            font-size: 0.7rem;
        }

        .map-legend-body {
            margin-top: 0.75rem;
        }

        .map-legend.is-collapsed .map-legend-body {
            display: none;
        }

        .map-legend-section-title {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .map-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .map-legend-icon {
            width: 14px;
            height: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .image-location-marker {
            width: 22px;
            height: 22px;
            border: 3px solid #ef4444;
            border-radius: 50%;
            background: transparent;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.9);
            position: relative;
        }

        .image-location-marker::after {
            content: '';
            position: absolute;
            inset: 5px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.2);
        }

        /* Hide Google Maps InfoWindow default scrollbars */
        .gm-style-iw.gm-style-iw-c {
            max-height: none !important;
            padding: 0 !important;
        }

        .gm-style-iw-d {
            overflow: hidden !important;
            max-height: none !important;
        }
    </style>
@endpush

@section('breadcrumb')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h3>Timeline</h3>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Timeline</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Visit Details Section -->
        <div class="row mb-2" id="visit-details-container">
            <div class="col-sm-6 col-xl-3">
                <div class="card static-top-widget-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="widget-icon me-3">
                            <i data-feather="clock" class="text-primary font-primary"
                                style="width: 30px; height: 30px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Started Time</h6>
                            <h5 id="started-time" class="mb-0 mt-1"></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card static-top-widget-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="widget-icon me-3">
                            <i data-feather="check-circle" class="text-secondary font-secondary"
                                style="width: 30px; height: 30px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Ended Time</h6>
                            <h5 id="ended-time" class="mb-0 mt-1"></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card static-top-widget-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="widget-icon me-3">
                            <i data-feather="map-pin" class="text-info font-info" style="width: 30px; height: 30px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Distance Covered</h6>
                            <h5 id="distance-covered" class="mb-0 mt-1"></h5>
                            <div class="mt-2 d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-1">
                                    <i data-feather="dollar-sign" class="text-warning" style="width: 14px; height: 14px;"></i>
                                    <span class="text-muted" style="font-size: 11px;">Calculated TA:</span>
                                    <span id="travel-expense" class="fw-bold text-warning" style="font-size: 13px;">N/A</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <i data-feather="phone-call" class="text-success" style="width: 14px; height: 14px;"></i>
                                    <span class="text-muted" style="font-size: 11px;">Call TA:</span>
                                    <span id="call-ta" class="fw-bold text-success" style="font-size: 13px;">N/A</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card static-top-widget-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="widget-icon me-3">
                            <i data-feather="watch" class="text-success font-success"
                                style="width: 30px; height: 30px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Time Taken</h6>
                            <h5 id="time-taken" class="mb-0 mt-1"></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relation Details Section -->
        <div class="row mb-2" id="relation-details-container" style="display: none;">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e9ecef !important;">
                    <div class="card-body py-3">
                        <h6 class="mb-3 text-primary d-flex align-items-center">
                            <i data-feather="link" class="me-2" style="width: 16px; height: 16px;"></i>
                            Related Information
                        </h6>
                        <div class="row" id="relation-content">
                            <!-- Dynamic Content -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Map Column -->
            <div class="col-lg-8 col-md-12 order-1 order-lg-2">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Visit Route Map</h5>
                        <div class="d-flex align-items-center">
                            <!-- Single Visit Export Buttons -->
                            <div id="single-visit-export-container" class="me-3" style="display: none;">
                                <div class="btn-group">
                                    <button id="single-export-btn" class="btn btn-success btn-xs" title="Export this visit to Excel">
                                        <i class="fa fa-file-excel me-1"></i> Excel
                                    </button>
                                    <button id="single-export-pdf-btn" class="btn btn-danger btn-xs" title="Export this visit to PDF">
                                        <i class="fa fa-file-pdf me-1"></i> PDF
                                    </button>
                                </div>
                            </div>

                            <!-- Route Optimization Settings (Collapsible) -->
                            <div class="d-flex align-items-center">
                                <div id="route-settings-collapse" class="d-flex align-items-center bg-light border rounded px-3 py-1 me-2" style="display: none !important; gap: 15px;">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 text-muted fw-bold" style="font-size: 10px; text-transform: uppercase;">Closest</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="closestModeSwitch"
                                                style="cursor: pointer; width: 2.2em; height: 1.1em;">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 text-muted fw-bold" style="font-size: 10px; text-transform: uppercase;">Smoothing</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="smoothingModeSwitch"
                                                style="cursor: pointer; width: 2.2em; height: 1.1em;" checked>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="toggleRouteSettings" class="btn btn-outline-primary btn-xs" title="Route Optimization Settings">
                                    <i data-feather="sliders" style="width: 14px; height: 14px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="position: relative;">
                        <div id="map" style="height: 600px; width: 100%; border-radius: 8px;"></div>

                        <!-- Sparse Data Warning -->
                        <div id="sparse-data-warning" class="alert alert-warning py-1 px-2 position-absolute"
                            style="top: 10px; left: 50%; transform: translateX(-50%); z-index: 10; display: none; font-size: 0.7rem; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            <i class="fa fa-exclamation-triangle me-1"></i>
                            <strong>Note:</strong> GPS data is sparse. Smoothing might be less accurate.
                        </div>

                        <!-- Map Legend -->
                        <div id="map-legend" class="map-legend p-3 rounded shadow-lg">
                            <div class="map-legend-header border-bottom pb-2">
                                <h6 class="small fw-bold mb-0">Map Legend</h6>
                                <button type="button" id="mapLegendToggle" class="map-legend-toggle" aria-expanded="true"
                                    aria-controls="mapLegendBody" title="Collapse legend">
                                    <i data-feather="chevron-up" style="width: 14px; height: 14px;"></i>
                                </button>
                            </div>
                            <div id="mapLegendBody" class="map-legend-body">
                                <div class="map-legend-section-title">Route Signals</div>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <div class="map-legend-item me-2">
                                        <span class="legend-color"
                                            style="background: #f59e0b; width: 8px; height: 8px; border-radius: 50%;"></span>
                                        <span class="small ms-1" style="font-size: 10px;">Bike</span>
                                    </div>
                                    <div class="map-legend-item me-2">
                                        <span class="legend-color"
                                            style="background: #3b82f6; width: 8px; height: 8px; border-radius: 50%;"></span>
                                        <span class="small ms-1" style="font-size: 10px;">Car</span>
                                    </div>
                                    <div class="map-legend-item me-2">
                                        <span class="legend-color"
                                            style="background: #10b981; width: 8px; height: 8px; border-radius: 50%;"></span>
                                        <span class="small ms-1" style="font-size: 10px;">Bus</span>
                                    </div>
                                    <div class="map-legend-item">
                                        <span class="legend-color"
                                            style="background: #6366f1; width: 8px; height: 8px; border-radius: 50%;"></span>
                                        <span class="small ms-1" style="font-size: 10px;">Train</span>
                                    </div>
                                    <div class="map-legend-item me-2">
                                        <span class="legend-color"
                                            style="background: #8b5e34; width: 8px; height: 8px; border-radius: 50%;"></span>
                                        <span class="small ms-1" style="font-size: 10px;">Walking</span>
                                    </div>
                                    <div class="map-legend-item">
                                        <span class="legend-color"
                                            style="background: #94a3b8; width: 8px; height: 8px; border-radius: 50%;"></span>
                                        <span class="small ms-1" style="font-size: 10px;">Unknown Vehicle</span>
                                    </div>
                                </div>

                                <div class="map-legend-section-title">Map Markers</div>
                                <div class="map-legend-item mb-2">
                                    <span class="map-legend-icon rounded-circle"
                                        style="background: #00E676; border: 1px solid #00C853;"></span>
                                    <span class="small">Start Point</span>
                                </div>
                                <div class="map-legend-item mb-2">
                                    <span class="map-legend-icon rounded-circle"
                                        style="background: #FF5252; border: 1px solid #D32F2F;"></span>
                                    <span class="small">End Point</span>
                                </div>
                                <div class="map-legend-item mb-2">
                                    <span
                                        class="d-inline-flex align-items-center justify-content-center bg-danger rounded-circle"
                                        style="width: 14px; height: 14px; border: 1px solid white;">
                                        <div style="width: 8px; height: 2px; background: white;"></div>
                                    </span>
                                    <span class="small">Halt Point</span>
                                </div>
                                <div class="map-legend-item mb-3">
                                    <span class="map-legend-icon rounded-circle"
                                        style="background: #FF0000; border: 1px solid #FFFFFF;"></span>
                                    <span class="small">Image Location</span>
                                </div>

                                <div class="map-legend-section-title">Task Color Spectrum</div>
                                <div class="d-flex flex-wrap gap-1 mb-1">
                                    <span class="legend-color" style="background: #E91E63; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 1"></span>
                                    <span class="legend-color" style="background: #9C27B0; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 2"></span>
                                    <span class="legend-color" style="background: #673AB7; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 3"></span>
                                    <span class="legend-color" style="background: #B71C1C; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 4"></span>
                                    <span class="legend-color" style="background: #1A237E; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 5"></span>
                                    <span class="legend-color" style="background: #006064; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 6"></span>
                                    <span class="legend-color" style="background: #FF00FF; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 7"></span>
                                    <span class="legend-color" style="background: #00B8D4; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 8"></span>
                                    <span class="legend-color" style="background: #C2185B; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 9"></span>
                                    <span class="legend-color" style="background: #4A148C; width: 14px; height: 14px; border-radius: 2px;" title="Task Color 10"></span>
                                </div>
                                <div class="small text-muted" style="font-size: 9px;">Assigned Task Variations</div>
                            </div>
                        </div>

                        <div id="timeline-container" class="mt-2" style="display: none;">
                            <h5 class="mb-1">Visit Timeline</h5>
                            <div class="timeline-wrapper">
                                <div id="horizontal-timeline" class="d-flex align-items-center" style="position: relative;">
                                    <!-- Timeline items will be injected here -->
                                </div>
                                <div id="timeline-axis" class="d-none">
                                    <!-- Hidden old axis -->
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mt-2">Select a visit from the table below to view its route.</p>
                    </div>
                </div>
            </div>

            <!-- Table Column -->
            <div class="col-lg-4 col-md-12 order-2 order-lg-1">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Recorded Visits</h5>
                    </div>
                    <div class="card-body p-0" style="position: relative;">
                        <div class="ajax-loader-wrapper" id="ajax-loader">
                            <div class="d-flex flex-column align-items-center">
                                <div class="ajax-loader"></div>
                                <div id="loader-text" class="mt-3 fw-bold text-primary" style="font-size: 0.85rem; letter-spacing: 0.5px;">Processing...</div>
                            </div>
                        </div>
                        <div class="p-4 border-bottom bg-light-soft">
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="dealership-filter" class="form-label small fw-bold text-muted mb-1">Dealership</label>
                                    <select id="dealership-filter" class="form-select form-select-sm" {{ $isRestricted && $currentDealershipId ? 'disabled' : '' }}>
                                        <option value="">All Dealerships</option>
                                        @foreach($dealerships as $dealership)
                                            <option value="{{ $dealership->id }}" {{ ($currentDealershipId == $dealership->id) ? 'selected' : '' }}>{{ $dealership->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="department-filter" class="form-label small fw-bold text-muted mb-1">Department</label>
                                    <select id="department-filter" class="form-select form-select-sm" {{ $isRestricted && $currentDepartmentId ? 'disabled' : '' }}>
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ ($currentDepartmentId == $department->id) ? 'selected' : '' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="employeeFilterContainer">
                                {{-- Employee filter will be appended here --}}
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="start-date" class="form-label small fw-bold text-muted mb-1">Start
                                        Date</label>
                                    <input type="text" id="start-date"
                                        class="form-control form-control-sm datepicker shadow-none border-2"
                                        placeholder="Start" autocomplete="off">
                                </div>
                                <div class="col-6">
                                    <label for="end-date" class="form-label small fw-bold text-muted mb-1">End Date</label>
                                    <input type="text" id="end-date"
                                        class="form-control form-control-sm datepicker shadow-none border-2"
                                        placeholder="End" autocomplete="off">
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="d-grid gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <button class="btn btn-outline-secondary btn-sm flex-grow-1" type="button" id="clear-date-filter">
                                                <i class="fa fa-undo-alt me-1"></i> Clear Dates
                                            </button>
                                            <div class="form-check form-switch m-0" title="When ON, split the same Visit ID into separate rows per calendar date.">
                                                <input class="form-check-input" type="checkbox" id="split-by-date-toggle">
                                                <label class="form-check-label small text-muted" for="split-by-date-toggle">By date</label>
                                            </div>
                                        </div>
                                        <div class="btn-group w-100">
                                            <button id="export-btn" class="btn btn-success btn-sm">
                                                <i class="fa fa-file-excel me-1"></i> Excel
                                            </button>
                                            <button id="export-pdf-btn" class="btn btn-danger btn-sm">
                                                <i class="fa fa-file-pdf me-1"></i> PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive p-3">
                            <table class="table table-hover align-middle mb-0" id="visits-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>User</th>
                                        <th>Date</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this trace (Visit ID: <span id="modalVisitId"></span>)?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Progress Modal -->
        <div class="modal fade" id="exportProgressModal" tabindex="-1" aria-labelledby="exportProgressModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportProgressModalLabel">Preparing Export</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="export-progress-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small text-muted" id="export-progress-status">Starting...</div>
                            <div class="small fw-bold text-primary"><span id="export-progress-done">0</span>/<span id="export-progress-total">0</span> routes</div>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="export-progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="border rounded p-2 bg-white" style="max-height: 220px; overflow: auto;">
                            <div class="small fw-bold text-muted mb-2">Route Log</div>
                            <div id="export-progress-log" class="small"></div>
                        </div>
                        <div class="text-danger small mt-2 d-none" id="export-progress-error"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" id="export-cancel-btn">Cancel</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="export-close-btn">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&libraries=geometry,marker&loading=async"
        async defer></script>

    <script>
        let map, userPath, borderPath, hitPath, markers = [],
            mapInitialized = false,
            segmentPolylines = [];
        let animationId = null;
        let infoWindow, pathData = [];
        let hoverTimeout = null;

        const taskColors = [
            "#E91E63", // Pink
            "#9C27B0", // Purple
            "#673AB7", // Deep Purple
            "#B71C1C", // Deep Red
            "#1A237E", // Midnight Blue
            "#006064", // Dark Cyan
            "#FF00FF", // Magenta
            "#00B8D4", // Vibrant Cyan
            "#C2185B", // Vivid Rose
            "#4A148C"  // Plum
        ];

        let loaderTimeout = null;

        function showLoader(message = 'Processing...') {
            if (loaderTimeout) clearTimeout(loaderTimeout);
            $("#loader-text").text(message);
            $("#ajax-loader").stop(true, true).css('display', 'flex').css('opacity', 1);
            // Failsafe: auto-hide after 60 seconds (smoothing can be slow)
            loaderTimeout = setTimeout(hideLoader, 60000);
        }

        function updateLoader(message) {
            $("#loader-text").text(message);
        }

        function hideLoader() {
            if (loaderTimeout) {
                clearTimeout(loaderTimeout);
                loaderTimeout = null;
            }
            $("#ajax-loader").fadeOut('slow');
        }

        function getTaskColor(id, index) {
            if (typeof index !== 'undefined' && index !== null) {
                return taskColors[index % taskColors.length];
            }
            if (!id) return "#adb5bd";
            return taskColors[Math.abs(id) % taskColors.length];
        }

        function getVehicleDesign(type) {
            const t = (type || 'other').toLowerCase();
            if (t === 'bike' || t === 'two_wheeler') return {
                color: '#f59e0b',
                icon: 'fa-motorcycle',
                label: 'Bike',
                feather: 'zap'
            };
            if (t === 'car' || t === 'four_wheeler') return {
                color: '#3b82f6',
                icon: 'fa-car',
                label: 'Car',
                feather: 'truck'
            };
            if (t === 'bus') return {
                color: '#10b981',
                icon: 'fa-bus',
                label: 'Bus',
                feather: 'users'
            };
            if (t === 'train') return {
                color: '#6366f1',
                icon: 'fa-train',
                label: 'Train',
                feather: 'fast-forward'
            };
            if (t === 'walk') return {
                color: '#8b5e34',
                icon: 'fa-walking',
                label: 'Walking',
                feather: 'user'
            };
            return {
                color: '#94a3b8',
                icon: 'fa-route',
                label: 'Unknown Vehicle',
                feather: 'navigation'
            };
        }

        function formatCurrency(value) {
            const amount = parseFloat(value);
            if (Number.isNaN(amount)) return 'N/A';
            return `₹${amount.toFixed(2)}`;
        }

        function adjustColor(color, percent) {
            var num = parseInt(color.replace("#", ""), 16),
                amt = Math.round(2.55 * percent),
                R = (num >> 16) + amt,
                B = (num >> 8 & 0x00FF) + amt,
                G = (num & 0x0000FF) + amt;
            return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 + (B < 255 ? B < 1 ? 0 : B : 255) * 0x100 + (G < 255 ? G < 1 ? 0 : G : 255)).toString(16).slice(1);
        }

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 5,
                center: {
                    lat: 20.5937,
                    lng: 78.9629
                },
                mapId: 'DEMO_MAP_ID', // Required for AdvancedMarkerElement
            });

            initializeMapLegend();

            userPath = new google.maps.Polyline({
                geodesic: true,
                strokeColor: "#27BEFF",
                strokeOpacity: 1.0,
                strokeWeight: 6,
                zIndex: 2,
                map: map
            });

            borderPath = new google.maps.Polyline({
                geodesic: true,
                strokeColor: "#1100FE",
                strokeOpacity: 1.0,
                strokeWeight: 8,
                zIndex: 1,
                map: map
            });

            // Invisible wider path to make hovering much easier
            hitPath = new google.maps.Polyline({
                geodesic: true,
                strokeColor: "#000",
                strokeOpacity: 0.0,
                strokeWeight: 25,
                zIndex: 3,
                map: map,
                cursor: 'pointer'
            });

            infoWindow = new google.maps.InfoWindow({
                disableAutoPan: true
            });

            let lastClosestId = null;

            // Add Hover Tooltip Listener to the wider hit area
            hitPath.addListener('mousemove', function (event) {
                if (pathData.length === 0) return;

                let closest = null;
                let minDist = Infinity;

                // Find the closest recorded point
                pathData.forEach(function (point) {
                    const dist = google.maps.geometry.spherical.computeDistanceBetween(
                        event.latLng,
                        new google.maps.LatLng(point.lat, point.lng)
                    );
                    if (dist < minDist) {
                        minDist = dist;
                        closest = point;
                    }
                });

                if (closest) {
                    // Only update content if we moved to a new point to avoid unnecessary DOM updates
                    const currentId = `${closest.lat}-${closest.lng}-${closest.time}`;
                    
                    if (currentId !== lastClosestId) {
                        let taskInfo = '';
                        if (closest.taskTitle) {
                            taskInfo = `
                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #eee;">
                                    <div style="color: #6c757d; font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Task</div>
                                    <div style="font-size: 13px; font-weight: 600; color: ${closest.color || '#333'};">
                                        ${closest.taskTitle}
                                    </div>
                                </div>
                            `;
                        }

                        infoWindow.setContent(`
                            <div style="padding: 10px; font-family: 'Poppins', sans-serif; min-width: 150px; overflow: hidden;">
                                <div style="color: #6c757d; font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Time Recorded</div>
                                <div style="font-size: 14px; font-weight: 500; color: #2b2b2b;">
                                    <i data-feather="clock" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 5px;"></i>
                                    ${new Date(closest.time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
                                </div>
                                <div style="font-size: 12px; color: #888; margin-top: 2px;">
                                    ${new Date(closest.time).toLocaleDateString()}
                                </div>
                                ${taskInfo}
                            </div>
                        `);
                        lastClosestId = currentId;
                        if (window.feather) window.feather.replace();
                    }

                    // Move the infoWindow to the point (snapping effect) without closing it
                    infoWindow.setPosition(new google.maps.LatLng(closest.lat, closest.lng));
                    
                    // Only open if not already open
                    if (!infoWindow.getMap()) {
                        infoWindow.open(map);
                    }
                }
            });

            hitPath.addListener('mouseout', function () {
                infoWindow.close();
                lastClosestId = null;
            });

            mapInitialized = true;
        }

        let isLocating = false;
        let hasLocatedRoute = false;
        // Global variables for Closest Mode
        let isClosestMode = false;
        let isSmoothingMode = true;
        let isMapLegendCollapsed = false;
        let selectedVisitId = null;
        let selectedVisitDate = null;
        let selectedVisitKey = null;
        let isSplitByDate = false;
        const SPLIT_BY_DATE_STORAGE_KEY = 'timeline.splitByDate';
        let imageLocationMarker = null;
        let haltLocationMarker = null;
        const MAP_LEGEND_STORAGE_KEY = 'timeline.mapLegendCollapsed';
        let storedTraces = [];
        let storedDistance = null;
        let storedTaskLogs = [];

        function attachMapLegendControl() {
            const legend = document.getElementById('map-legend');
            if (!legend || !map || legend.dataset.attached === 'true') return;

            map.controls[google.maps.ControlPosition.TOP_RIGHT].push(legend);
            legend.dataset.attached = 'true';
        }

        function syncMapLegendState() {
            const legend = document.getElementById('map-legend');
            const toggle = document.getElementById('mapLegendToggle');
            if (!legend || !toggle) return;

            legend.classList.toggle('is-collapsed', isMapLegendCollapsed);
            toggle.setAttribute('aria-expanded', String(!isMapLegendCollapsed));
            toggle.setAttribute('title', isMapLegendCollapsed ? 'Expand legend' : 'Collapse legend');
            toggle.innerHTML = isMapLegendCollapsed
                ? '<i data-feather="chevron-down" style="width: 14px; height: 14px;"></i>'
                : '<i data-feather="chevron-up" style="width: 14px; height: 14px;"></i>';

            try {
                localStorage.setItem(MAP_LEGEND_STORAGE_KEY, isMapLegendCollapsed ? '1' : '0');
            } catch (error) {
                // Ignore storage issues and keep the in-memory state only.
            }

            if (window.feather) feather.replace();
        }

        function initializeMapLegend() {
            attachMapLegendControl();

            try {
                isMapLegendCollapsed = localStorage.getItem(MAP_LEGEND_STORAGE_KEY) === '1';
            } catch (error) {
                // Ignore storage issues and keep the default state.
            }

            const toggle = document.getElementById('mapLegendToggle');
            if (toggle && !toggle.dataset.bound) {
                toggle.addEventListener('click', function () {
                    isMapLegendCollapsed = !isMapLegendCollapsed;
                    syncMapLegendState();
                });
                toggle.dataset.bound = 'true';
            }

            syncMapLegendState();
        }

        function resetAnalyticsCards() {
            $('#started-time').text('N/A');
            $('#ended-time').text('N/A');
            $('#time-taken').text('N/A');
            $('#distance-covered').text('N/A');
            $('#travel-expense').text('N/A');
            $('#call-ta').text('N/A');
            $('#visit-details-container').hide();
        }

        // ... (existing helper functions) ...

        function sortByClosest(traces) {
            if (!traces || traces.length < 2) return traces || [];

            // Filter out invalid points first
            let validTraces = traces.filter(t => t.latitude && t.longitude);
            if (validTraces.length === 0) return [];

            let sorted = [validTraces[0]]; // Start with the first point (entry point)
            let remaining = validTraces.slice(1);

            while (remaining.length > 0) {
                const lastPoint = sorted[sorted.length - 1];
                const lastLatLng = new google.maps.LatLng(parseFloat(lastPoint.latitude), parseFloat(lastPoint.longitude));

                let nearestIndex = -1;
                let minDistance = Infinity;

                for (let i = 0; i < remaining.length; i++) {
                    const point = remaining[i];
                    const currentLatLng = new google.maps.LatLng(parseFloat(point.latitude), parseFloat(point.longitude));
                    const distance = google.maps.geometry.spherical.computeDistanceBetween(lastLatLng, currentLatLng);

                    if (distance < minDistance) {
                        minDistance = distance;
                        nearestIndex = i;
                    }
                }

                if (nearestIndex !== -1) {
                    sorted.push(remaining[nearestIndex]);
                    remaining.splice(nearestIndex, 1);
                } else {
                    break;
                }
            }
            return sorted;
        }


        function snapToRoads(traces, callback) {
            if (!traces || traces.length < 2) {
                callback(traces);
                return;
            }

            const apiKey = "{{ env('GOOGLE_MAPS_API_KEY') }}";
            const maxPointsPerRequest = 100;

            // 1. Filter valid points and thin them out (skip points too close together)
            let rawTraces = traces.filter(t => t.latitude && t.longitude && parseFloat(t.latitude) !== 0);
            if (rawTraces.length < 2) {
                callback(traces);
                return;
            }

            let validTraces = [rawTraces[0]];
            const minMoveThreshold = 3; // meters

            for (let i = 1; i < rawTraces.length; i++) {
                const p1 = validTraces[validTraces.length - 1];
                const p2 = rawTraces[i];
                const d = google.maps.geometry.spherical.computeDistanceBetween(
                    new google.maps.LatLng(p1.latitude, p1.longitude),
                    new google.maps.LatLng(p2.latitude, p2.longitude)
                );
                if (d > minMoveThreshold) {
                    validTraces.push(p2);
                }
            }

            if (validTraces.length < 2) {
                callback(traces);
                return;
            }

            // 2. Densify sparse points to avoid 'too sparse' warning
            const densifiedTraces = [];
            const thresholdMeters = 100; // Reduced from 200m for better road follow quality

            for (let i = 0; i < validTraces.length - 1; i++) {
                const p1 = validTraces[i];
                const p2 = validTraces[i + 1];
                densifiedTraces.push(p1);

                const lat1 = parseFloat(p1.latitude);
                const lng1 = parseFloat(p1.longitude);
                const lat2 = parseFloat(p2.latitude);
                const lng2 = parseFloat(p2.longitude);

                const dist = google.maps.geometry.spherical.computeDistanceBetween(
                    new google.maps.LatLng(lat1, lng1),
                    new google.maps.LatLng(lat2, lng2)
                );

                if (dist > thresholdMeters) {
                    const pointsToInject = Math.floor(dist / thresholdMeters);
                    const safeInject = Math.min(pointsToInject, 50); // limit to avoid excessive points

                    const time1 = new Date(p1.created_at || p1.recorded_at).getTime();
                    const time2 = new Date(p2.created_at || p2.recorded_at).getTime();

                    for (let j = 1; j <= safeInject; j++) {
                        const ratio = j / (safeInject + 1);
                        const interpLat = lat1 + (lat2 - lat1) * ratio;
                        const interpLng = lng1 + (lng2 - lng1) * ratio;
                        const interpTime = new Date(time1 + (time2 - time1) * ratio).toISOString();

                        densifiedTraces.push({
                            latitude: interpLat,
                            longitude: interpLng,
                            created_at: interpTime,
                            recorded_at: interpTime,
                            task_id: p1.task_id,
                            task: p1.task,
                            client_id: p1.client_id,
                            vehicle_type: p1.vehicle_type,
                            remarks: p1.remarks,
                            status: 'injected'
                        });
                    }
                }
            }
            densifiedTraces.push(validTraces[validTraces.length - 1]);

            showLoader('Smoothing route: 0%');
            let allSnappedPoints = [];

            function processBatch(startIndex) {
                const progress = Math.round((startIndex / densifiedTraces.length) * 100);
                updateLoader(`Smoothing route: ${progress}%`);
                
                const batch = densifiedTraces.slice(startIndex, startIndex + maxPointsPerRequest);
                const pathParam = batch.map(t => `${t.latitude},${t.longitude}`).join('|');

                $.ajax({
                    url: `https://roads.googleapis.com/v1/snapToRoads?path=${pathParam}&interpolate=true&key=${apiKey}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.snappedPoints && response.snappedPoints.length > 0) {
                            if (response.warningMessage && response.warningMessage.includes('too sparse')) {
                                $('#sparse-data-warning').fadeIn();
                                setTimeout(() => $('#sparse-data-warning').fadeOut(), 5000);
                            }

                            const snappedBatch = [];
                            response.snappedPoints.forEach((sp, idx) => {
                                const originalIdx = sp.originalIndex !== undefined ? sp.originalIndex : -1;
                                const fallbackOriginal = idx > 0
                                    ? snappedBatch[idx - 1]
                                    : (allSnappedPoints.length > 0 ? allSnappedPoints[allSnappedPoints.length - 1] : batch[0]);
                                const original = originalIdx !== -1 ? batch[originalIdx] : fallbackOriginal;

                                snappedBatch.push({
                                    latitude: sp.location.latitude,
                                    longitude: sp.location.longitude,
                                    recorded_at: original ? (original.recorded_at || original.created_at) : null,
                                    created_at: original ? (original.created_at || original.recorded_at) : null,
                                    task_id: original ? original.task_id : null,
                                    task: original ? original.task : null,
                                    client_id: original ? original.client_id : null,
                                    vehicle_type: original ? original.vehicle_type : null,
                                    remarks: original ? original.remarks : null,
                                    status: original ? original.status : 'snapped'
                                });
                            });
                            allSnappedPoints = allSnappedPoints.concat(snappedBatch);
                        }

                        if (startIndex + maxPointsPerRequest < densifiedTraces.length) {
                            processBatch(startIndex + maxPointsPerRequest - 1);
                        } else {
                            callback(allSnappedPoints.length >= 2 ? allSnappedPoints : traces);
                        }
                    },
                    error: function (err) {
                        console.error("Roads API Error:", err);
                        // If one batch fails, we try to proceed with what we have so far
                        // or fallback to original if nothing was snapped yet.
                        if (startIndex + maxPointsPerRequest < densifiedTraces.length) {
                            processBatch(startIndex + maxPointsPerRequest);
                        } else {
                            callback(allSnappedPoints.length >= 2 ? allSnappedPoints : traces);
                        }
                    }
                });
            }
            processBatch(0);
        }

        function calculatePathDistance(path) {
            let totalDist = 0;
            for (let i = 0; i < path.length - 1; i++) {
                const p1 = new google.maps.LatLng(path[i].lat, path[i].lng);
                const p2 = new google.maps.LatLng(path[i + 1].lat, path[i + 1].lng);
                totalDist += google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
            }
            return totalDist;
        }

        function renderMap(gpsTraces, distanceCovered, taskLogs = [], animate = true) {
            if (!mapInitialized) {
                hideLoader();
                return;
            }

            // Clean up previous map objects
            if (typeof segmentPolylines !== 'undefined') {
                segmentPolylines.forEach(p => p.setMap(null));
            }
            segmentPolylines = [];
            if (userPath) userPath.setMap(null); // Hide old path if exists
            borderPath.setPath([]);
            hitPath.setPath([]);
            markers.forEach(m => m.map = null);
            markers = [];

            // Store for toggle (logic from original code)
            storedTraces = gpsTraces;
            storedDistance = distanceCovered;
            if (taskLogs && taskLogs.length > 0) {
                storedTaskLogs = taskLogs;
            }

            cancelAnimationFrame(animationId);

            updateLoader('Drawing route on map...');
            setTimeout(() => {
                renderMapWithData(gpsTraces, distanceCovered, taskLogs, animate);
                hideLoader();
            }, 50);
        }

        function renderMapWithData(tracesToProcess, distanceCovered, taskLogs = [], animate = true) {
            // Process data into path points with task info
            pathData = tracesToProcess
                .filter(t => t.latitude && t.longitude)
                .map(t => {
                    const tTime = new Date(t.created_at || t.recorded_at).getTime();
                    let appliedColor = null;
                    let appliedTaskTitle = null;
                    let appliedTaskId = t.task_id;

                    // Find if this trace falls within any task log's time range for coloring
                    if (taskLogs && taskLogs.length > 0) {
                        taskLogs.forEach((log, idx) => {
                            const start = new Date(log.start_time).getTime();
                            const end = log.end_time ? new Date(log.end_time).getTime() : new Date().getTime();
                            if (tTime >= start && tTime <= end) {
                                appliedColor = getTaskColor(log.task_id, idx);
                                appliedTaskTitle = log.task ? log.task.title : 'Task';
                                appliedTaskId = log.task_id;
                            }
                        });
                    }

                    return {
                        lat: parseFloat(t.latitude),
                        lng: parseFloat(t.longitude),
                        time: t.created_at || t.recorded_at,
                        taskId: appliedTaskId,
                        taskTitle: appliedTaskTitle || (t.task ? t.task.title : null),
                        color: appliedColor || (t.task_id ? getTaskColor(t.task_id) : getVehicleDesign(t.vehicle_type).color)
                    };
                });

            if (pathData.length === 0) {
                $('#distance-covered').text('N/A');
                hideLoader();
                return;
            }

            // Create segments based on connectivity and color/task
            let segments = [];
            if (pathData.length > 0) {
                let currentSegment = {
                    color: pathData[0].color,
                    points: [pathData[0]]
                };

                for (let i = 1; i < pathData.length; i++) {
                    let p = pathData[i];
                    // If colored differently, start new segment
                    if (p.color !== currentSegment.color) {
                        segments.push(currentSegment);
                        currentSegment = {
                            color: p.color,
                            points: [pathData[i - 1], p]
                        };
                    } else {
                        currentSegment.points.push(p);
                    }
                }
                segments.push(currentSegment);
            }

            // Draw segments
            segments.forEach(seg => {
                const polyLine = new google.maps.Polyline({
                    path: seg.points,
                    geodesic: true,
                    strokeColor: seg.color,
                    strokeOpacity: 1.0,
                    strokeWeight: 6,
                    zIndex: 2,
                    map: map
                });
                segmentPolylines.push(polyLine);
            });

            // Set up the Hit Path (invisible wide path for hovering) containing all points
            const allCoords = pathData.map(p => ({
                lat: p.lat,
                lng: p.lng
            }));
            hitPath.setPath(allCoords);

            // Optional: Draw a border path underneath
            borderPath.setOptions({
                strokeColor: '#cad4e0',
                strokeOpacity: 0.5,
                zIndex: 1
            });
            borderPath.setPath(allCoords);

            // Add Task markers
            if (taskLogs && taskLogs.length > 0) {
                const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                taskLogs.forEach((log, idx) => {
                    const logStart = new Date(log.start_time).getTime();
                    // Find nearest trace point
                    let nearestTrace = null;
                    let minDiff = Infinity;
                    tracesToProcess.forEach(t => {
                        const tTime = new Date(t.created_at || t.recorded_at).getTime();
                        const diff = Math.abs(tTime - logStart);
                        if (diff < minDiff && diff < 300000) { // dentro 5 min
                            minDiff = diff;
                            nearestTrace = t;
                        }
                    });

                    if (nearestTrace) {
                        const color = getTaskColor(log.task_id, idx);
                        const letter = alphabet[idx] || '';
                        const label = (log.task ? log.task.title : 'Task') + ' ' + letter;

                        const taskMarker = new google.maps.marker.AdvancedMarkerElement({
                            position: {
                                lat: parseFloat(nearestTrace.latitude),
                                lng: parseFloat(nearestTrace.longitude)
                            },
                            map,
                            title: label,
                            content: new google.maps.marker.PinElement({
                                glyph: letter,
                                glyphColor: 'white',
                                background: color,
                                borderColor: 'white'
                            }),
                            zIndex: 15
                        });

                        const markerContent = `<div class="p-2" style="overflow: hidden;"><strong>${label}</strong><br><small>${new Date(log.start_time).toLocaleString()}</small></div>`;

                        taskMarker.addListener('click', () => {
                            infoWindow.setContent(markerContent);
                            infoWindow.open(map, taskMarker);
                        });
                        markers.push(taskMarker);
                    }
                });
            }

            // Add Start/End Markers
            if (allCoords.length > 0) {
                const startMarker = new google.maps.marker.AdvancedMarkerElement({
                    position: allCoords[0],
                    map,
                    title: 'Start',
                    content: new google.maps.marker.PinElement({
                        background: '#00E676',
                        borderColor: '#00C853',
                        glyphColor: 'white'
                    })
                });
                markers.push(startMarker);


                // Add Halt Markers
                tracesToProcess.forEach(t => {
                    const isHalt = t.status === 'halt' || (t.remarks && t.remarks.toLowerCase().includes('halt'));

                    if (isHalt) {
                        const title = t.remarks ? "Halt: " + t.remarks : "Halt Point";

                        // Create custom halt icon as DOM element
                        const haltIcon = document.createElement('div');
                        haltIcon.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2z M17 13H7v-2h10V13z" fill="#ef4444" stroke="#ffffff" stroke-width="2"/>
                        </svg>`;

                        const haltMarker = new google.maps.marker.AdvancedMarkerElement({
                            position: {
                                lat: parseFloat(t.latitude),
                                lng: parseFloat(t.longitude)
                            },
                            map,
                            title: title,
                            content: haltIcon,
                            zIndex: 10
                        });

                        const haltContent = `<div class="p-2" style="overflow: hidden;"><strong>${title}</strong><br><small>${new Date(t.created_at || t.recorded_at).toLocaleTimeString()}</small></div>`;

                        haltMarker.addListener('click', () => {
                            infoWindow.setContent(haltContent);
                            infoWindow.open(map, haltMarker);
                        });

                        markers.push(haltMarker);
                    }
                });

                addEndMarker(allCoords);
            }

            // The distance is now calculated on the backend and passed in.
            let distVal = parseFloat(distanceCovered);
            let distText = 'N/A';
            if (!isNaN(distVal)) {
                distText = (distVal >= 1 ? `${distVal.toFixed(3)} km` : `${Math.round(distVal * 1000)} m`);
            }
            $('#distance-covered').text(distText);
        }

        function addEndMarker(path) {
            if (path.length > 1) {
                const endMarker = new google.maps.marker.AdvancedMarkerElement({
                    position: path[path.length - 1],
                    map,
                    title: 'End',
                    content: new google.maps.marker.PinElement({
                        background: '#FF5252',
                        borderColor: '#D32F2F',
                        glyphColor: 'white'
                    })
                });
                markers.push(endMarker);
            }
        }

        let lastTimelineDate = null;

        function renderDateMarker(time, container) {
            const d = new Date(time);
            const dateStr = d.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            if (lastTimelineDate !== dateStr) {
                container.append(`
                    <div class="timeline-date-marker">
                        <div class="date-badge">${dateStr}</div>
                    </div>
                `);
                lastTimelineDate = dateStr;
            }
        }

        function renderTimeline(taskLogs, startTime, endTime, traces, taskFollowups) {
            const container = $('#horizontal-timeline');
            container.empty();
            lastTimelineDate = null;

            if ((!taskLogs || taskLogs.length === 0) && (!traces || traces.length === 0)) {
                container.append('<div class="w-100 text-center text-muted">No activities recorded for this visit.</div>');
                return;
            }

            const start = new Date(startTime).getTime();
            const end = new Date(endTime).getTime();
            const totalDuration = end - start;

            if (totalDuration <= 0) return;

            $('#timeline-container').show();

            renderDateMarker(start, container);

            let lastEndTime = start;
            const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            // 1. Baseline and filter task logs: only show parts that overlap the visit
            const sortedLogs = taskLogs.map(log => {
                const lS = new Date(log.start_time).getTime();
                const lE = log.end_time ? new Date(log.end_time).getTime() : Infinity;

                // If it ends before visit starts or starts after visit ends, skip
                if (lE <= start || lS >= end) return null;

                return {
                    ...log
                }; // Keep original, we'll cap effective times during render
            })
                .filter(l => l !== null)
                .sort((a, b) => new Date(a.start_time) - new Date(b.start_time));

            const filteredTraces = (traces || []).filter(t => {
                const tTime = new Date(t.created_at || t.recorded_at).getTime();
                return tTime >= start;
            });

            // Group sequential logs by Task ID AND Effective Date
            const taskGroups = [];
            if (sortedLogs.length > 0) {
                // Use effective start date for grouping (capped at visit start)
                const getEffectiveDate = (l) => {
                    const sTime = Math.max(new Date(l.start_time).getTime(), start);
                    return new Date(sTime).toLocaleDateString();
                };

                let currentGroup = {
                    taskId: sortedLogs[0].task_id,
                    task: sortedLogs[0].task,
                    logs: [sortedLogs[0]],
                    groupDate: getEffectiveDate(sortedLogs[0])
                };
                for (let i = 1; i < sortedLogs.length; i++) {
                    let logDate = getEffectiveDate(sortedLogs[i]);
                    if (sortedLogs[i].task_id === currentGroup.taskId && logDate === currentGroup.groupDate) {
                        currentGroup.logs.push(sortedLogs[i]);
                    } else {
                        taskGroups.push(currentGroup);
                        currentGroup = {
                            taskId: sortedLogs[i].task_id,
                            task: sortedLogs[i].task,
                            logs: [sortedLogs[i]],
                            groupDate: logDate
                        };
                    }
                }
                taskGroups.push(currentGroup);
            }

            taskGroups.forEach((group, groupIndex) => {
                const firstLog = group.logs[0];
                // Cap group start at visit start
                const groupStart = Math.max(new Date(firstLog.start_time).getTime(), start);

                const lastLog = group.logs[group.logs.length - 1];
                let rawEnd;
                if (lastLog.end_time) {
                    rawEnd = new Date(lastLog.end_time).getTime();
                } else if (groupIndex < taskGroups.length - 1) {
                    rawEnd = new Date(taskGroups[groupIndex + 1].logs[0].start_time).getTime();
                } else {
                    rawEnd = end;
                }

                // Strictly cap at visit end
                const groupEnd = Math.min(rawEnd, end);

                // 1. Travel gap before this task group
                if (groupStart > lastEndTime) {
                    renderConnector(lastEndTime, groupStart, filteredTraces, totalDuration, container);
                }

                // Date marker check if midnight crossed during a task
                renderDateMarker(groupStart, container);

                // 2. Transition Marker
                container.append(`
                                    <div class="timeline-marker-transition">
                                        <div class="transition-icon"><i data-feather="flag" style="width: 12px; height: 12px;"></i></div>
                                    </div>
                                `);

                // 3. Render Unified Group Track
                const groupDuration = Math.min(groupEnd, end) - Math.max(groupStart, start);
                if (groupDuration >= 0) {
                    const groupPercentage = (groupDuration / totalDuration) * 100;
                    const groupWidth = Math.max(groupPercentage, 18);
                    const color = getTaskColor(group.taskId, groupIndex);
                    const title = (group.task ? group.task.title : 'Task');
                    const label = title;

                    const startTimeStr = new Date(groupStart).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });

                    // Display end time should also be capped
                    const displayEndTime = Math.min(
                        lastLog.end_time ? new Date(lastLog.end_time).getTime() : end,
                        end
                    );
                    const endTimeStr = (lastLog.end_time || groupEnd < end) ? new Date(displayEndTime).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }) : 'Active';

                    let subMarkers = '';
                    group.logs.forEach((log, lIdx) => {
                        const rawLStart = new Date(log.start_time).getTime();
                        const displayLStart = Math.max(rawLStart, start);
                        const lTime = new Date(displayLStart).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        });

                        // Determine Icon and Label based on action_type
                        let icon = 'play';
                        let actionLabel = 'Started';
                        const aType = (log.action_type || '').toLowerCase();

                        if (aType.includes('pause')) {
                            icon = 'pause';
                            actionLabel = 'Paused';
                        } else if (aType.includes('resume')) {
                            icon = 'refresh-cw';
                            actionLabel = 'Resumed';
                        } else if (aType.includes('stop')) {
                            icon = 'square';
                            actionLabel = 'Stopped';
                        } else if (aType.includes('created')) {
                            icon = 'plus-circle';
                            actionLabel = 'Created';
                        } else if (aType.includes('completed')) {
                            icon = 'check-circle';
                            actionLabel = 'Completed';
                        } else if (aType.includes('hold')) {
                            icon = 'pause-circle';
                            actionLabel = 'Hold';
                        } else if (aType.includes('partial')) {
                            icon = 'minus-circle';
                            actionLabel = 'Partial';
                        } else if (lIdx > 0) {
                            icon = 'refresh-cw';
                            actionLabel = 'Resumed';
                        }

                        subMarkers += `
                            <div class="sub-marker-wrapper">
                                <div class="sub-marker-circle" style="color: ${color};" title="${actionLabel}">
                                    <i data-feather="${icon}" style="width: 10px; height: 10px;"></i>
                                </div>
                                <div class="sub-marker-time">${actionLabel}<br>${lTime}</div>
                            </div>`;
                    });

                    // Add Follow-up Markers if any for this task group
                    if (taskFollowups) {
                        const groupFollowups = taskFollowups.filter(f =>
                            f.task_id == group.taskId &&
                            new Date(f.created_at).getTime() >= groupStart &&
                            new Date(f.created_at).getTime() <= groupEnd
                        );
                        groupFollowups.forEach(f => {
                            const fTime = new Date(f.created_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true
                            });
                            subMarkers += `
                                <div class="sub-marker-wrapper">
                                    <div class="sub-marker-circle" style="color: #6f42c1;" title="Follow-up">
                                        <i data-feather="message-square" style="width: 10px; height: 10px;"></i>
                                    </div>
                                    <div class="sub-marker-time">Follow-up<br>${fTime}</div>
                                </div>`;
                        });
                    }

                    container.append(`
                                        <div class="timeline-segment" style="width: ${groupWidth}%">
                                            <div class="segment-title" style="border-left: 3px solid ${color}">${label}</div>
                                            <div class="timeline-group-pill" style="background: ${color}15; border: 1px solid ${color}30;">
                                                ${subMarkers}
                                            </div>
                                            <div class="segment-time text-muted small">${startTimeStr} - ${endTimeStr}</div>
                                            <div class="timeline-tooltip">
                                                <strong>${label}</strong><br>
                                                <span class="opacity-75">Range: ${startTimeStr} - ${endTimeStr}</span><br>
                                                <span class="opacity-75">Actions: ${group.logs.length}</span>
                                            </div>
                                        </div>
                                    `);
                }
                lastEndTime = groupEnd;
            });

            // 4. Final transition flag and trailing gap
            if (lastEndTime < end) {
                renderDateMarker(lastEndTime, container);
                container.append(`
                                                                                <div class="timeline-marker-transition">
                                                                                    <div class="transition-icon"><i data-feather="flag" style="width: 12px; height: 12px;"></i></div>
                                                                                </div>
                                                                            `);
                renderConnector(lastEndTime, end, filteredTraces, totalDuration, container);
            }
            if (window.feather) feather.replace();
        }

        function renderTaskPause(fromTime, toTime, totalDuration, container) {
            renderDateMarker(fromTime, container);
            const duration = toTime - fromTime;
            const width = Math.max((duration / totalDuration) * 100, 8);

            const startTimeStr = new Date(fromTime).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            const endTimeStr = new Date(toTime).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            container.append(`
                            <div class="timeline-connector" style="width: ${width}%">
                                <div class="marker-point">
                                    <div class="marker-icon" style="color: #f59e0b;">
                                        <i data-feather="pause" style="width: 10px; height: 10px;"></i>
                                    </div>
                                </div>
                                <div class="connector-line" style="border-top-color: #f59e0b;"></div>
                                <div class="segment-time text-muted small">${startTimeStr} - ${endTimeStr}</div>
                                <div class="timeline-tooltip">
                                    <strong>Task Pause</strong><br>
                                    <span class="opacity-75">Duration: ${Math.round(duration / 60000)} mins</span>
                                </div>
                            </div>
                        `);
        }

        function renderConnector(fromTime, toTime, traces, totalDuration, container) {
            renderDateMarker(fromTime, container);
            const duration = toTime - fromTime;
            const width = Math.max((duration / totalDuration) * 100, 8);

            // Scan traces for halts and vehicle types
            const periodTraces = (traces || []).filter(t => {
                const tTime = new Date(t.created_at || t.recorded_at).getTime();
                return tTime >= fromTime && tTime <= toTime;
            });

            const hasHalt = periodTraces.some(t => t.status === 'halt' || (t.remarks && t.remarks.toLowerCase().includes('halt')));

            // Pick the most common vehicle type in this segment or default to first
            let vType = 'other';
            if (periodTraces.length > 0) {
                vType = periodTraces[0].vehicle_type || 'other';
            }

            let design = getVehicleDesign(vType);
            let label = design.label;
            let color = design.color;
            let icon = `<i class="fa ${design.icon}" style="font-size: 10px;"></i>`;

            if (hasHalt) {
                label = 'Unplanned Halt';
                color = '#ef4444';
                icon = `<i data-feather="alert-octagon" style="width: 11px; height: 11px;"></i>`;
            }

            container.append(`
                            <div class="timeline-connector" style="width: ${width}%">
                                <div class="marker-point">
                                    <div class="marker-icon" style="color: ${color}; border-color: ${color}40;">
                                        ${icon}
                                    </div>
                                </div>
                                <div class="connector-line travel" style="border-top-color: ${color}; opacity: 0.8;"></div>
                                <div class="segment-time text-muted small">${new Date(fromTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true })}</div>
                                <div class="timeline-tooltip">
                                    <strong>${label}</strong><br>
                                    <span class="opacity-75">Vehicle: ${design.label}</span><br>
                                    <span class="opacity-75">Duration: ${Math.round(duration / 60000)} mins</span>
                                </div>
                            </div>
                        `);
        }

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
            $('#start-date, #end-date').datepicker('setDate', new Date());

            try {
                isSplitByDate = localStorage.getItem(SPLIT_BY_DATE_STORAGE_KEY) === '1';
            } catch (error) {
                isSplitByDate = false;
            }
            $('#split-by-date-toggle').prop('checked', isSplitByDate);

            function fetchEmployees() {
                if ($('#employee-filter').length) {
                    const start = $('#start-date').val();
                    const end = $('#end-date').val();
                    const dealer = $('#dealership-filter').val();
                    const dept = $('#department-filter').val();

                    showLoader();

                    $.ajax({
                        url: "{{ route('live-location.employees') }}",
                        data: {
                            start_date: start,
                            end_date: end,
                            dealership_id: dealer,
                            department_id: dept,
                            all: 1
                        },
                        success: function (res) {
                            const currentVal = $('#employee-filter').val();
                            $('#employee-filter').empty().append('<option value="">All Employees</option>');
                            res.forEach(e => $('#employee-filter').append(`<option value="${e.id}">${e.name}</option>`));
                            $('#employee-filter').val(currentVal).trigger('change.select2');
                            if (!$('#employee-filter').data('select2')) {
                                $('#employee-filter').select2({
                                    placeholder: "Select Employee",
                                    allowClear: true
                                });
                            }
                        },
                        complete: function () {
                            if (!isLocating) hideLoader();
                        }
                    });
                }
            }

            function buildUrl() {
                let url = "{{ route('timeline.data') }}";
                let params = [];
                const emp = $('#employee-filter').val();
                const start = $('#start-date').val();
                const end = $('#end-date').val();
                const dealer = $('#dealership-filter').val();
                const dept = $('#department-filter').val();

                if (emp) params.push(`user_id=${emp}`);
                if (start) params.push(`start_date=${start}`);
                if (end) params.push(`end_date=${end}`);
                if (dealer) params.push(`dealership_id=${dealer}`);
                if (dept) params.push(`department_id=${dept}`);
                if (isSplitByDate) params.push(`split_by_date=1`);

                return url + (params.length ? '?' + params.join('&') : '');
            }

            function fetchStats() {
                // If locating a specific visit, don't override with global stats
                if (isLocating) return;

                const start = $('#start-date').val();
                const end = $('#end-date').val();
                const emp = $('#employee-filter').val();
                const dealer = $('#dealership-filter').val();
                const dept = $('#department-filter').val();

                $.ajax({
                    url: "{{ route('live-location.stats') }}",
                    data: {
                        start_date: start,
                        end_date: end,
                        user_id: emp,
                        dealership_id: dealer,
                        department_id: dept,
                        closest_mode: isClosestMode ? 1 : 0
                    },
                    success: function (res) {
                        if (isLocating || res.started_time === 'N/A') return;

                        $('#started-time').text(res.started_time);
                        $('#ended-time').text(res.ended_time);
                        $('#time-taken').text(res.time_taken);
                        $('#distance-covered').text(res.total_distance);
                        $('#travel-expense').text(formatCurrency(res.total_travel_expense));
                        $('#call-ta').text(formatCurrency(res.total_call_ta));
                        $('#visit-details-container').slideDown();
                    }
                });
            }

            const filterHtml = `
                                                                            @if(Auth::user()->user_type === 'admin' || Auth::user()->employee?->subordinates->count() > 0)
                                                                                <div class="form-group mb-3">
                                                                                    <label for="employee-filter" class="small">Filter by Employee</label>
                                                                                    <select id="employee-filter" class="form-control form-control-sm">
                                                                                        <option value="">All Employees</option>
                                                                                    </select>
                                                                                </div>
                                                                            @endif
                                                                        `;
            $('#employeeFilterContainer').html(filterHtml);

            fetchEmployees();

            function getVisitKey(data) {
                return `${String(data.visit_id)}|${String(data.visit_date || '')}`;
            }

            var table = $('#visits-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: buildUrl(),
                    type: 'GET'
                },
                rowCallback: function (row, data) {
                    const isActive = selectedVisitKey && getVisitKey(data) === String(selectedVisitKey);
                    $(row).toggleClass('active-visit', !!isActive);
                },
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'travel_expense',
                    name: 'travel_expense',
                    visible: false
                },
                {
                    data: 'engineer_rate',
                    name: 'engineer_rate',
                    visible: false
                },
                {
                    data: 'task_title',
                    name: 'task_title',
                    visible: false
                },
                {
                    data: 'task_id',
                    name: 'task_id',
                    visible: false
                },
                {
                    data: 'client_name',
                    name: 'client_name',
                    visible: false
                },
                {
                    data: 'client_phone',
                    name: 'client_phone',
                    visible: false
                },
                {
                    data: 'visit_remarks',
                    name: 'visit_remarks',
                    visible: false
                },
                {
                    data: 'visit_image',
                    name: 'visit_image',
                    visible: false
                },
                {
                    data: 'image_latitude',
                    name: 'image_latitude',
                    visible: false
                },
                {
                    data: 'image_longitude',
                    name: 'image_longitude',
                    visible: false
                }
                ],
                order: [
                    [2, 'desc']
                ]
            });

            table.on('preXhr.dt', function () {
                showLoader();
            }).on('draw.dt', function () {
                // Background redraws from filters or row updates should not 
                // hide the loader if we're actively locating a route.
                if (!isLocating) {
                    hideLoader();
                }
            });

            $('#employee-filter, #dealership-filter, #department-filter').on('change', function () {
                isLocating = false; // Reset locating mode on filter change
                hasLocatedRoute = false;
                selectedVisitId = null;
                selectedVisitDate = null;
                selectedVisitKey = null;
                resetAnalyticsCards();
                
                if ($(this).attr('id') !== 'employee-filter') {
                    fetchEmployees();
                }
                
                table.ajax.url(buildUrl()).load();
                fetchStats();
            });

            // Fix: Use 'change' or bind via datepicker options if changeDate is fickle, 
            // but 'changeDate' is the official event for bootstrap-datepicker.
            // We removed the redundant .datepicker() call which was resetting the object.
            $('#start-date, #end-date').on('changeDate', function () {
                isLocating = false;
                hasLocatedRoute = false;
                selectedVisitId = null;
                selectedVisitDate = null;
                selectedVisitKey = null;
                resetAnalyticsCards();
                fetchStats();
                fetchEmployees();
                table.ajax.url(buildUrl()).load();
            });

            // Also handle manual input change just in case
            $('#start-date, #end-date').on('change', function () {
                if (!$(this).data('datepicker').picker.is(':visible')) {
                    isLocating = false;
                    hasLocatedRoute = false;
                    selectedVisitId = null;
                    selectedVisitDate = null;
                    selectedVisitKey = null;
                    resetAnalyticsCards();
                    fetchStats();
                    fetchEmployees();
                    table.ajax.url(buildUrl()).load();
                }
            });

            $('#clear-date-filter').on('click', function () {
                isLocating = false;
                hasLocatedRoute = false;
                selectedVisitId = null;
                selectedVisitDate = null;
                selectedVisitKey = null;
                resetAnalyticsCards();
                $('#start-date, #end-date').datepicker('setDate', new Date());
                table.ajax.url(buildUrl()).load();
                fetchStats();
                fetchEmployees();
            });

            $('#split-by-date-toggle').on('change', function () {
                isSplitByDate = $(this).is(':checked');
                try {
                    localStorage.setItem(SPLIT_BY_DATE_STORAGE_KEY, isSplitByDate ? '1' : '0');
                } catch (error) {
                    // Ignore storage issues and keep the in-memory state only.
                }

                isLocating = false;
                hasLocatedRoute = false;
                selectedVisitId = null;
                selectedVisitDate = null;
                selectedVisitKey = null;
                resetAnalyticsCards();
                table.ajax.url(buildUrl()).load();
                fetchStats();
                fetchEmployees();
            });

            function escapeHtml(value) {
                return $('<div/>').text(value ?? '').html();
            }

            function renderRelationInfo(row) {
                let relationHtml = '';

                if (row.task_title) {
                    relationHtml += `
                        <div class="col-md-6 mb-2">
                            <span class="text-muted small d-block">Related Task</span>
                            <div class="d-flex align-items-center mt-1">
                                <span class="fw-bold fs-6 text-dark me-2">${escapeHtml(row.task_title)}</span>
                                <a href="/tasks/${row.task_id}" target="_blank" class="btn btn-xs btn-outline-primary" style="padding: 1px 6px; font-size: 10px;">
                                    <i class="fa fa-external-link-alt"></i> View
                                </a>
                            </div>
                        </div>
                    `;
                }

                if (row.client_name) {
                    relationHtml += `
                        <div class="col-md-6 mb-2">
                            <span class="text-muted small d-block">Related Client</span>
                            <div class="mt-1">
                                <span class="fw-bold fs-6 text-dark">${escapeHtml(row.client_name)}</span>
                                ${row.client_phone ? `<div class="text-muted small mt-1"><i data-feather="phone" style="width:12px; height: 12px; vertical-align: text-bottom;"></i> ${escapeHtml(row.client_phone)}</div>` : ''}
                            </div>
                        </div>
                    `;
                }

                if (row.visit_remarks) {
                    relationHtml += `
                        <div class="col-md-12 mb-2">
                            <span class="text-muted small d-block">Visit Remarks</span>
                            <div class="mt-1">
                                <span class="fs-6 text-dark" style="white-space: pre-wrap;">${escapeHtml(row.visit_remarks)}</span>
                            </div>
                        </div>
                    `;
                }

                if (row.visit_image) {
                    // We'll populate the image location button after the traces are loaded (need coords)
                    relationHtml += `
                        <div class="col-md-12 mb-2">
                            <span class="text-muted small d-block">Visit Image</span>
                            <div class="mt-2">
                                <a href="/storage/${row.visit_image}" target="_blank">
                                    <img src="/storage/${row.visit_image}" alt="Visit Image" class="img-fluid rounded shadow-sm" style="max-height: 150px; max-width: 100%;">
                                </a>
                                <span id="visit-image-location-placeholder"></span>
                            </div>
                        </div>
                    `;
                }

                const tasks = Array.isArray(row.visit_tasks) ? row.visit_tasks : [];
                if (tasks.length) {
                    const items = tasks.map(t => {
                        const status = (t?.status || 'N/A').toString();
                        const badgeClass = status === 'completed' ? 'bg-success' : (status === 'partial' ? 'bg-warning text-dark' : 'bg-secondary');
                        const title = escapeHtml(t?.title || 'Task');
                        return `
                            <div class="list-group-item d-flex align-items-center justify-content-between">
                                <div class="me-2">
                                    <div class="fw-bold">${title}</div>
                                    <div class="small text-muted">#${escapeHtml(t?.id)}</div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge ${badgeClass}" style="text-transform: uppercase; letter-spacing: .5px;">${escapeHtml(status)}</span>
                                    <a href="/tasks/${t.id}" target="_blank" class="btn btn-xs btn-outline-primary" style="padding: 1px 6px; font-size: 10px;">View</a>
                                </div>
                            </div>
                        `;
                    }).join('');

                    relationHtml += `
                        <div class="col-md-12 mb-2">
                            <span class="text-muted small d-block">Tasks In This Visit (${tasks.length})</span>
                            <div class="list-group list-group-flush mt-2 border rounded">
                                ${items}
                            </div>
                        </div>
                    `;
                }

                const halts = Array.isArray(row.halt_points) ? row.halt_points : [];
                if (halts.length) {
                    const items = halts.map((hp, idx) => {
                        const duration = escapeHtml(hp?.duration || 'N/A');
                        const start = escapeHtml(hp?.start_time || 'N/A');
                        const end = escapeHtml(hp?.end_time || 'N/A');
                        const activeTasks = escapeHtml(hp?.active_tasks || 'N/A');
                        const remarks = escapeHtml(hp?.remarks || '');
                        const imagesCount = Array.isArray(hp?.images) ? hp.images.length : 0;
                        return `
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">Halt #${idx + 1}</div>
                                    <button class="btn btn-xs btn-outline-info view-halt-location" data-lat="${hp?.lat}" data-lng="${hp?.lng}" style="padding: 1px 6px; font-size: 10px;">
                                        <i data-feather="map-pin" style="width: 12px; height: 12px;"></i> Map
                                    </button>
                                </div>
                                <div class="small text-muted mt-1">${start} - ${end} • ${duration}</div>
                                <div class="small mt-1"><span class="text-muted">Tasks:</span> <span class="fw-bold">${activeTasks}</span></div>
                                ${remarks ? `<div class="small mt-1"><span class="text-muted">Remarks:</span> ${remarks}</div>` : ''}
                                ${imagesCount ? `<div class="small mt-1"><span class="text-muted">Images:</span> ${imagesCount}</div>` : ''}
                            </div>
                        `;
                    }).join('');

                    relationHtml += `
                        <div class="col-md-12 mb-2">
                            <span class="text-muted small d-block">Halt Points (${halts.length})</span>
                            <div class="list-group list-group-flush mt-2 border rounded">
                                ${items}
                            </div>
                        </div>
                    `;
                }

                if (!relationHtml) {
                    relationHtml = '<div class="col-12 text-muted fst-italic">No related information available for this visit.</div>';
                }

                $('#relation-content').html(relationHtml);
                $('#relation-details-container').slideDown();
                $('#single-visit-export-container').fadeIn();
                if (window.feather) feather.replace();
            }

            $('#visits-table').on('click', '.select-visit', function (e) {
                e.preventDefault();
                const vid = $(this).data('visit-id');
                const vdate = $(this).data('visit-date') || null;
                const row = table.row($(this).closest('tr')).data();

                // Highlight the selected row
                selectedVisitId = vid;
                const normalizeVisitDate = (val) => {
                    if (!val) return null;
                    const str = String(val);
                    if (str.length >= 10) return str.substring(0, 10);
                    return str;
                };

                // In "By date" mode, always derive a YYYY-MM-DD visit_date for precise trace fetching.
                // Prefer the explicit `data-visit-date`, then backend-provided `visit_date`, then fallback to the displayed `date` column.
                selectedVisitDate = isSplitByDate
                    ? normalizeVisitDate(vdate || row?.visit_date || row?.date)
                    : null;
                selectedVisitKey = `${String(vid)}|${String(selectedVisitDate || '')}`;
                table.rows().invalidate().draw(false);

                isLocating = true; // Enter locating mode
                hasLocatedRoute = false;
                resetAnalyticsCards();

                // Update Time Taken / Start / End Widgets manually from row data?
                // The row data has 'date', but not duration/end time explicitly per row in current Controller?
                // Controller getTimelineDataTableData: 'date' => firstTrace->created_at.
                // Does not pass end time or time taken.
                // Ideally we should pass these if we want to update widgets.
                // For now, let's just clear specific widgets or set them to 'N/A' if accurate data isn't available, or leave them as is (which might show Global stats, confusing).
                // Better: 'time_taken' is NOT in the row data. We can fetch it in the traces ajax call.

                renderRelationInfo(row);

                // Toggle buttons: Hide Locate, Show Unlocate for this row
                $('.select-visit').show();
                $('.unlocate-visit').hide();
                $(this).hide();
                $(this).siblings('.unlocate-visit').show();

                showLoader('Fetching visit data...');

                // Check if we already have the traces and expense data cached locally
                if (
                    row.traces &&
                    row._cachedVisitKey === selectedVisitKey &&
                    row.travel_expense !== null &&
                    row.visit_tasks &&
                    row.halt_points
                ) {
                    renderLocateView(row, vid);
                    return;
                }

                $.ajax({
                    url: `/track-visits/${vid}/traces`,
                    data: (function () {
                        const payload = {
                        start_date: $('#start-date').val(),
                        end_date: $('#end-date').val(),
                        smoothing_mode: isSmoothingMode ? 1 : 0,
                        closest_mode: isClosestMode ? 1 : 0
                        };
                        if (selectedVisitDate) payload.visit_date = selectedVisitDate;
                        return payload;
                    })(),
                    success: function (res) {
                        // Cache the results in the row object
                        row._cachedVisitKey = selectedVisitKey;
                        row.traces = res.traces;
                        row.started_time = res.started_time;
                        row.ended_time = res.ended_time;
                        row.time_taken = res.time_taken;
                        row.travel_expense = res.travel_expense;
                        row.engineer_rate = res.engineer_rate;
                        row.distance_covered = res.distance_covered;
                        row.task_logs = res.task_logs;
                        row.task_followups = res.task_followups;
                        row.visit_tasks = res.visit_tasks;
                        row.halt_points = res.halt_points;

                        renderRelationInfo(row);

                        renderLocateView(row, vid);

                        // Update the row data in DataTables cache so the expense stays visible
                        const tr = $(`.select-visit[data-visit-id="${vid}"]`).closest('tr');
                        table.row(tr).data(row).draw(false);
                    }
                });
            });

            function renderLocateView(data, vid) {
                const coords = data.traces.filter(t => t.latitude && t.longitude).map(t => ({
                    lat: parseFloat(t.latitude),
                    lng: parseFloat(t.longitude)
                }));

                hasLocatedRoute = coords.length > 0;

                if (hasLocatedRoute) {
                    $('#started-time').text(data.started_time || 'N/A');
                    $('#ended-time').text(data.ended_time || 'N/A');
                    $('#time-taken').text(data.time_taken || 'N/A');
                    $('#distance-covered').text(data.distance_covered ? `${parseFloat(data.distance_covered).toFixed(3)} km` : 'N/A');
                    $('#travel-expense').text(formatCurrency(data.travel_expense));
                    $('#call-ta').text(formatCurrency(data.engineer_rate));
                    $('#visit-details-container').slideDown();

                    // Handle image location if available in traces
                    const traceWithImage = data.traces.find(t => t.image_path && t.image_latitude && t.image_longitude);
                    if (traceWithImage) {
                        $('#visit-image-location-placeholder').html(`
                            <button class="btn btn-outline-info btn-xs ms-2 view-image-location" 
                                data-lat="${traceWithImage.image_latitude}" 
                                data-lng="${traceWithImage.image_longitude}"
                                style="padding: 2px 6px; font-size: 10px;">
                                <i data-feather="map-pin" style="width: 10px; height: 10px;"></i> View Location
                            </button>
                        `);
                        if (window.feather) feather.replace();
                    }
                } else {
                    resetAnalyticsCards();
                }

                renderMap(data.traces, data.distance_covered, data.task_logs);
                renderTimeline(data.task_logs, data.started_time, data.ended_time, data.traces, data.task_followups);

                if (coords.length > 0) {
                    const bounds = new google.maps.LatLngBounds();
                    coords.forEach(c => bounds.extend(c));
                    map.fitBounds(bounds);
                }
            }

            $('#visits-table').on('click', '.unlocate-visit', function (e) {
                e.preventDefault();

                // Toggle buttons back
                $(this).hide();
                $(this).siblings('.select-visit').show();

                // Clear highlights
                selectedVisitId = null;
                selectedVisitDate = null;
                selectedVisitKey = null;
                table.rows().invalidate().draw(false);

                // Reset map and data
                isLocating = false;
                hasLocatedRoute = false;
                storedTraces = [];
                storedDistance = null;
                storedTaskLogs = [];

                // Clear Map Objects
                if (typeof segmentPolylines !== 'undefined') {
                    segmentPolylines.forEach(p => p.setMap(null));
                }
                segmentPolylines = [];
                if (borderPath) borderPath.setPath([]);
                if (hitPath) hitPath.setPath([]);
                markers.forEach(m => m.map = null);
                markers = [];

                // Hide timeline
                $('#timeline-container').hide();
                $('#horizontal-timeline').empty();

                // Hide relation details
                resetAnalyticsCards();
                $('#single-visit-export-container').fadeOut();
                if (infoWindow) infoWindow.close();

                // Center map to default
                map.setCenter({
                    lat: 20.5937,
                    lng: 78.9629
                });
                map.setZoom(5);
            });

            // Make the whole row clickable for better UX
            $('#visits-table').on('click', 'tbody tr', function (e) {
                if ($(e.target).closest('button, a').length) return;
                $(this).find('.select-visit').click();
            });

            $('#export-btn').on('click', function () {
                startChunkedTimelineExport('excel');
            });

            $('#export-pdf-btn').on('click', function () {
                startChunkedTimelineExport('pdf');
            });

            let exportCancelRequested = false;
            let exportToken = null;
            let exportInProgress = false;

            function resetExportProgressUI() {
                $('#export-progress-status').text('Starting...');
                $('#export-progress-done').text('0');
                $('#export-progress-total').text('0');
                $('#export-progress-bar').css('width', '0%');
                $('#export-progress-log').empty();
                $('#export-progress-error').addClass('d-none').text('');
            }

            function setExportError(message) {
                $('#export-progress-error').removeClass('d-none').text(message || 'Something went wrong.');
            }

            function setExportStatus(message) {
                $('#export-progress-status').text(message || '');
            }

            function setExportProgress(done, total) {
                $('#export-progress-done').text(String(done));
                $('#export-progress-total').text(String(total));
                const percent = total > 0 ? Math.round((done / total) * 100) : 0;
                $('#export-progress-bar').css('width', percent + '%');
            }

            function appendExportLog(line) {
                const safe = (line || '').toString();
                $('#export-progress-log').append($('<div class="mb-1"></div>').text(safe));
                const el = document.getElementById('export-progress-log');
                if (el) el.parentElement.scrollTop = el.parentElement.scrollHeight;
            }

            function getExportFilters(exportType) {
                return {
                    export_type: exportType,
                    user_id: $('#employee-filter').val() || '',
                    start_date: $('#start-date').val() || '',
                    end_date: $('#end-date').val() || '',
                    dealership_id: $('#dealership-filter').val() || '',
                    department_id: $('#department-filter').val() || '',
                    closest_mode: isClosestMode ? 1 : 0,
                    smoothing_mode: isSmoothingMode ? 1 : 0,
                    split_by_date: isSplitByDate ? 1 : 0
                };
            }

            async function startChunkedTimelineExport(exportType) {
                if (exportInProgress) return;

                exportInProgress = true;
                exportCancelRequested = false;
                exportToken = null;
                resetExportProgressUI();

                const title = exportType === 'pdf' ? 'Preparing PDF Export' : 'Preparing Excel Export';
                $('#exportProgressModalLabel').text(title);
                setExportStatus('Fetching routes...');

                const modal = new bootstrap.Modal(document.getElementById('exportProgressModal'));
                modal.show();

                $('#export-btn, #export-pdf-btn').prop('disabled', true);

                try {
                    const manifest = await $.ajax({
                        url: "{{ route('timeline.export.manifest') }}",
                        method: 'GET',
                        data: getExportFilters(exportType)
                    });

                    exportToken = manifest.token;

                    const visits = Array.isArray(manifest.visits)
                        ? manifest.visits
                        : (Array.isArray(manifest.visit_ids) ? manifest.visit_ids.map(v => ({ visit_id: v })) : []);

                    const total = visits.length;

                    setExportProgress(0, total);

                    if (total === 0) {
                        setExportStatus('No routes found for the selected filters.');
                        exportInProgress = false;
                        $('#export-btn, #export-pdf-btn').prop('disabled', false);
                        return;
                    }

                    appendExportLog(`Found ${total} routes. Processing...`);

                    // Dynamically set batch size to keep progress bar updates smooth (about 10-20 steps) while keeping network overhead low
                    const batchSize = Math.max(2, Math.min(25, Math.ceil(total / 10)));
                    for (let i = 0; i < total; i += batchSize) {
                        if (exportCancelRequested) break;

                        const batch = visits.slice(i, i + batchSize);
                        const visitsBatch = batch.map(item => {
                            return {
                                visit_id: item.visit_id ?? item,
                                visit_date: item.visit_date ?? null
                            };
                        });

                        const labelStart = i + 1;
                        const labelEnd = Math.min(i + batchSize, total);
                        setExportStatus(`Processing routes ${labelStart} to ${labelEnd} of ${total}...`);

                        const res = await $.ajax({
                            url: "{{ route('timeline.export.process') }}",
                            method: 'POST',
                            data: {
                                token: exportToken,
                                visits: visitsBatch
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
                            }
                        });

                        const done = res.processed || labelEnd;
                        setExportProgress(done, total);

                        if (res.summary && (res.summary.user_name || res.summary.date)) {
                            appendExportLog(`#${done}/${total} Processed batch (Latest: ${res.summary.user_name || 'N/A'} ${res.summary.date || 'N/A'})`);
                        } else {
                            appendExportLog(`#${done}/${total} Batch processed`);
                        }
                    }

                    if (exportCancelRequested) {
                        setExportStatus('Cancelling...');
                        if (exportToken) {
                            await $.ajax({
                                url: "{{ route('timeline.export.cancel') }}",
                                method: 'POST',
                                data: { token: exportToken },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
                                }
                            });
                        }
                        appendExportLog('Export cancelled.');
                        setExportStatus('Cancelled.');
                        exportInProgress = false;
                        $('#export-btn, #export-pdf-btn').prop('disabled', false);
                        return;
                    }

                    setExportStatus('Finalizing export... Download will start shortly.');
                    appendExportLog('Generating file...');

                    const downloadUrl = "{{ route('timeline.export.download') }}" + '?' + $.param({ token: exportToken });
                    window.location.href = downloadUrl;

                    setTimeout(() => {
                        try { modal.hide(); } catch (e) {}
                    }, 1500);
                } catch (err) {
                    const message = err?.responseJSON?.message || err?.responseText || 'Export failed.';
                    setExportError(message);
                    appendExportLog('Export failed.');
                    setExportStatus('Error.');
                } finally {
                    exportInProgress = false;
                    $('#export-btn, #export-pdf-btn').prop('disabled', false);
                }
            }

            $('#export-cancel-btn').on('click', function () {
                exportCancelRequested = true;
                setExportStatus('Cancel requested...');
            });

            // Initialize handler for view-image-location ONLY if not already attached (or use delegation)
            // Delegation is better since content is dynamic
            $(document).on('click', '.view-image-location', function (e) {
                e.preventDefault();
                const lat = parseFloat($(this).data('lat'));
                const lng = parseFloat($(this).data('lng'));

                if (!isNaN(lat) && !isNaN(lng)) {
                    // Focus map on this location
                    const pos = {
                        lat: lat,
                        lng: lng
                    };
                    map.setCenter(pos);
                    map.setZoom(17);

                    if (imageLocationMarker) {
                        imageLocationMarker.map = null;
                    }

                    const imageMarkerContent = document.createElement('div');
                    imageMarkerContent.className = 'image-location-marker';

                    // Add a special marker for image location without hiding the route color underneath
                    imageLocationMarker = new google.maps.marker.AdvancedMarkerElement({
                        position: pos,
                        map: map,
                        title: 'Image Location',
                        content: imageMarkerContent,
                        zIndex: 20
                    });
                }
            });

            $(document).on('click', '.view-halt-location', function (e) {
                e.preventDefault();
                const lat = parseFloat($(this).data('lat'));
                const lng = parseFloat($(this).data('lng'));

                if (!isNaN(lat) && !isNaN(lng)) {
                    const pos = { lat, lng };
                    map.setCenter(pos);
                    map.setZoom(16);

                    if (haltLocationMarker) {
                        haltLocationMarker.map = null;
                    }

                    const markerContent = document.createElement('div');
                    markerContent.className = 'image-location-marker';

                    haltLocationMarker = new google.maps.marker.AdvancedMarkerElement({
                        position: pos,
                        map: map,
                        title: 'Halt Point',
                        content: markerContent,
                        zIndex: 19
                    });

                    if (window.feather) feather.replace();
                }
            });

            $('#toggleRouteSettings').on('click', function() {
                const $container = $('#route-settings-collapse');
                if ($container.is(':visible')) {
                    $container.attr('style', 'display: none !important; gap: 15px;');
                } else {
                    $container.attr('style', 'display: flex !important; gap: 15px;');
                }
            });

            $('#closestModeSwitch').on('change', function () {
                isClosestMode = $(this).is(':checked');
                if (storedTraces.length > 0) {
                    renderMap(storedTraces, storedDistance, storedTaskLogs, false);
                }
                fetchStats();
            });

            $('#smoothingModeSwitch').on('change', function () {
                isSmoothingMode = $(this).is(':checked');
                if (storedTraces.length > 0) {
                    renderMap(storedTraces, storedDistance, storedTaskLogs, false);
                }
            });

            $('#smoothingModeSwitch').prop('checked', isSmoothingMode);


            $('#visits-table').on('click', '.delete-trace', function (e) {
                e.preventDefault();
                const vid = $(this).data('visit-id');
                $('#modalVisitId').text(vid);
                $('#confirmDeleteBtn').data('visit-id', vid);
                $('#deleteConfirmationModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function () {
                const vid = $(this).data('visit-id');
                const $btn = $(this);
                $btn.prop('disabled', true).text('Deleting...');

                $.ajax({
                    url: `/track-visits/${vid}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        $('#deleteConfirmationModal').modal('hide');
                        table.ajax.reload(null, false);
                        fetchStats();
                        fetchEmployees();
                        showToast(res.message || 'Trace deleted successfully', 'success');
                    },
                    error: function (xhr) {
                        showToast('Error deleting trace', 'danger');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Delete');
                    }
                });
            });

            $('#single-export-btn').on('click', function () {
                if (!selectedVisitId) return;
                const $btn = $(this);
                const originalHtml = $btn.html();

                const paramObj = {
                    visit_id: selectedVisitId,
                    closest_mode: isClosestMode ? 1 : 0,
                    smoothing_mode: isSmoothingMode ? 1 : 0
                };
                if (selectedVisitDate) paramObj.visit_date = selectedVisitDate;
                const params = $.param(paramObj);

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Excel...');
                window.location.href = "{{ route('live-location.export') }}?" + params;

                // Reset button after 5 seconds
                setTimeout(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                }, 5000);
            });

            $('#single-export-pdf-btn').on('click', function () {
                if (!selectedVisitId) return;
                const $btn = $(this);
                const originalHtml = $btn.html();

                const paramObj = {
                    visit_id: selectedVisitId,
                    closest_mode: isClosestMode ? 1 : 0,
                    smoothing_mode: isSmoothingMode ? 1 : 0
                };
                if (selectedVisitDate) paramObj.visit_date = selectedVisitDate;
                const params = $.param(paramObj);

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>PDF...');
                window.location.href = "{{ route('timeline.export-pdf') }}?" + params;

                // Reset button after 5 seconds
                setTimeout(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                }, 5000);
            });

            resetAnalyticsCards();
            fetchStats();
        });
    </script>
@endpush
