@extends('layouts.admin')

@push('styles')
<style>
    .settings-grid-item {
        padding: 1.25rem;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f1f5f9;
    }

    .settings-grid-item:hover {
        background-color: #f8fafc;
    }

    @media (min-width: 768px) {
        .border-md-end {
            border-right: 1px solid #f1f5f9;
        }
    }

    .icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .compact-input-group {
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 4px 10px;
        width: 150px;
        flex-shrink: 0;
    }

    .compact-input-group:focus-within {
        border-color: #7366ff;
        box-shadow: 0 0 0 3px rgba(115, 102, 255, 0.1);
    }

    .compact-input-group input {
        border: none !important;
        background: transparent !important;
        padding: 2px 4px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        width: 100% !important;
        text-align: right;
        font-size: 0.9rem !important;
    }

    .compact-input-group .symbol {
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .setting-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 1px;
    }

    .setting-desc {
        font-size: 0.75rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }

    .section-header {
        background-color: #f8fafc;
        padding: 0.75rem 1.25rem;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
    }
</style>
@endpush

@section('title', 'Application Settings')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Application Settings</h1>

    <form id="settingsForm">
        @csrf
        <!-- Travel Allowance Card -->
        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="fa fa-route text-primary me-2"></i>
                    Travel Allowance Settings
                </h5>
            </div>
            <div class="card-body p-0">

                <!-- Travel Section -->
                <div class="section-header">
                    <i class="fa fa-route me-2"></i>Travel Reimbursement
                </div>
                <div class="row g-0">
                    <!-- Daily Limit -->
                    <div class="col-md-12 border-bottom">
                        <div class="settings-grid-item">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper bg-soft-info me-3">
                                    <i class="fa fa-calendar-day text-info"></i>
                                </div>
                                <div>
                                    <div class="setting-title">Daily Claim Limit</div>
                                    <div class="setting-desc">Maximum TA allowance per day</div>
                                </div>
                            </div>
                            <div class="compact-input-group">
                                <span class="symbol">₹</span>
                                <input type="number" id="travel_allowance_max_daily" name="travel_allowance_max_daily" value="{{ $settings['travel_allowance_max_daily']->value ?? '1000' }}" step="0.01" min="0">
                                <span class="symbol">/day</span>
                            </div>
                        </div>
                    </div>

                    <!-- Walk Rate -->
                    <div class="col-md-6 border-md-end">
                        <div class="settings-grid-item">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper bg-soft-info me-3">
                                    <i class="fa fa-walking text-info"></i>
                                </div>
                                <div>
                                    <div class="setting-title">Walk Rate</div>
                                    <div class="setting-desc">Walking travel rate</div>
                                </div>
                            </div>
                            <div class="compact-input-group">
                                <span class="symbol">₹</span>
                                <input type="number" id="travel_allowance_walk" name="travel_allowance_walk" value="{{ $settings['travel_allowance_walk']->value ?? '5' }}" step="0.01" min="0">
                                <span class="symbol">/km</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bike Rate -->
                    <div class="col-md-6 border-bottom">
                        <div class="settings-grid-item">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper bg-soft-warning me-3">
                                    <i class="fa fa-motorcycle text-warning"></i>
                                </div>
                                <div>
                                    <div class="setting-title">Bike Rate</div>
                                    <div class="setting-desc">Two-wheeler travel rate</div>
                                </div>
                            </div>
                            <div class="compact-input-group">
                                <span class="symbol">₹</span>
                                <input type="number" id="travel_allowance_bike" name="travel_allowance_bike" value="{{ $settings['travel_allowance_bike']->value ?? ($settings['travel_allowance_two_wheeler']->value ?? '5') }}" step="0.01" min="0">
                                <span class="symbol">/km</span>
                            </div>
                        </div>
                    </div>

                    <!-- Car Rate -->
                    <div class="col-md-6 border-md-end">
                        <div class="settings-grid-item">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper bg-soft-success me-3">
                                    <i class="fa fa-car text-success"></i>
                                </div>
                                <div>
                                    <div class="setting-title">Car Rate</div>
                                    <div class="setting-desc">Four-wheeler travel rate</div>
                                </div>
                            </div>
                            <div class="compact-input-group">
                                <span class="symbol">₹</span>
                                <input type="number" id="travel_allowance_car" name="travel_allowance_car" value="{{ $settings['travel_allowance_car']->value ?? ($settings['travel_allowance_four_wheeler']->value ?? '10') }}" step="0.01" min="0">
                                <span class="symbol">/km</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bus Rate -->
                    <div class="col-md-6 border-bottom">
                        <div class="settings-grid-item">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper bg-soft-primary me-3">
                                    <i class="fa fa-bus text-primary"></i>
                                </div>
                                <div>
                                    <div class="setting-title">Bus Rate</div>
                                    <div class="setting-desc">Public transport reimbursement</div>
                                </div>
                            </div>
                            <div class="compact-input-group">
                                <span class="symbol">₹</span>
                                <input type="number" id="travel_allowance_bus" name="travel_allowance_bus" value="{{ $settings['travel_allowance_bus']->value ?? '0' }}" step="0.01" min="0">
                                <span class="symbol">/km</span>
                            </div>
                        </div>
                    </div>

                    <!-- Train Rate -->
                    <div class="col-md-6 border-md-end border-bottom-0">
                        <div class="settings-grid-item border-bottom-0">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper bg-soft-danger me-3">
                                    <i class="fa fa-train text-danger"></i>
                                </div>
                                <div>
                                    <div class="setting-title">Train Rate</div>
                                    <div class="setting-desc">Railway travel reimbursement</div>
                                </div>
                            </div>
                            <div class="compact-input-group">
                                <span class="symbol">₹</span>
                                <input type="number" id="travel_allowance_train" name="travel_allowance_train" value="{{ $settings['travel_allowance_train']->value ?? '0' }}" step="0.01" min="0">
                                <span class="symbol">/km</span>
                            </div>
                        </div>
                    </div>

                    <!-- Engineer Rate per Call -->
                    <div class="col-md-6 border-bottom">
                        <div class="settings-grid-item">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper bg-soft-secondary me-3">
                                    <i class="fa fa-headphones text-secondary"></i>
                                </div>
                                <div>
                                    <div class="setting-title">Rate/Call (Engineers)</div>
                                    <div class="setting-desc">Per-call allowance for field engineers</div>
                                </div>
                            </div>
                            <div class="compact-input-group">
                                <span class="symbol">₹</span>
                                <input type="number" id="travel_allowance_engineer_rate_per_call" name="travel_allowance_engineer_rate_per_call" value="{{ $settings['travel_allowance_engineer_rate_per_call']->value ?? '0' }}" step="0.01" min="0">
                                <span class="symbol">/call</span>
                            </div>
                        </div>
                    </div>
                    <!-- Empty spacer for balance -->
                    <div class="col-md-6 border-bottom-0"></div>
                </div>
            </div>

            <!-- Human Resources Card -->
            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="fa fa-users text-primary me-2"></i>
                        Human Resources
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <div class="settings-grid-item border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-soft-primary me-3">
                                        <i class="fa fa-clock text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="setting-title">Notice Period</div>
                                        <div class="setting-desc">Default period for resignations</div>
                                    </div>
                                </div>
                                <div class="compact-input-group">
                                    <input type="number" id="notice_period_duration" name="notice_period_duration" value="{{ $settings['notice_period_duration']->value ?? '1' }}" min="0" required>
                                    <span class="symbol">Months</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <button type="submit" class="btn btn-primary px-5 shadow-sm py-2 fw-bold">
                    <i class="fa fa-save me-2"></i>Save All Settings
                </button>
            </div>
    </form>
</div>


</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Handle form submission
        $('#settingsForm').on('submit', function(e) {
            e.preventDefault();

            var settings = [{
                    key: 'travel_allowance_other',
                    name: 'Fallback Rate',
                    description: 'Fallback travel allowance rate per kilometer for unknown vehicle type',
                    value: 0
                },
                {
                    key: 'travel_allowance_rate',
                    name: 'Travel Allowance Rate (Legacy Default)',
                    description: 'Legacy default travel allowance rate per kilometer',
                    value: 0
                },
                {
                    key: 'travel_allowance_max_daily',
                    name: 'Daily Claim Limit',
                    description: 'Maximum travel allowance amount that can be claimed per day',
                    value: $('#travel_allowance_max_daily').val()
                },
                {
                    key: 'travel_allowance_walk',
                    name: 'Walk Rate',
                    description: 'Travel allowance rate for walk per kilometer',
                    value: $('#travel_allowance_walk').val()
                },
                {
                    key: 'travel_allowance_bike',
                    name: 'Bike Rate',
                    description: 'Travel allowance rate for bike per kilometer',
                    value: $('#travel_allowance_bike').val()
                },
                {
                    key: 'travel_allowance_car',
                    name: 'Car Rate',
                    description: 'Travel allowance rate for car per kilometer',
                    value: $('#travel_allowance_car').val()
                },
                {
                    key: 'travel_allowance_bus',
                    name: 'Bus Rate',
                    description: 'Travel allowance rate for bus per kilometer',
                    value: $('#travel_allowance_bus').val()
                },
                {
                    key: 'travel_allowance_train',
                    name: 'Train Rate',
                    description: 'Travel allowance rate for train per kilometer',
                    value: $('#travel_allowance_train').val()
                },
                {
                    key: 'travel_allowance_engineer_rate_per_call',
                    name: 'Engineer Rate Per Call',
                    description: 'Per-call travel allowance for engineers',
                    value: $('#travel_allowance_engineer_rate_per_call').val()
                },
                {
                    key: 'notice_period_duration',
                    name: 'Notice Period Duration (Months)',
                    description: 'Default notice period duration in months',
                    value: $('#notice_period_duration').val()
                }
            ];

            $.ajax({
                url: '{{ route("settings.update") }}',
                method: 'POST',
                data: {
                    settings: settings,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message || 'Settings updated successfully', 'success');
                },
                error: function(xhr) {
                    showToast('Error updating settings: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
                }
            });
        });
    });

    function showToast(message, type) {
        // Using your existing toast notification system
        var toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

        var toastContainer = $('.toast-container');
        if (toastContainer.length === 0) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
            toastContainer = $('.toast-container');
        }

        toastContainer.append(toastHtml);
        var toastElement = toastContainer.find('.toast').last();
        var toast = new bootstrap.Toast(toastElement[0]);
        toast.show();

        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
</script>
@endpush