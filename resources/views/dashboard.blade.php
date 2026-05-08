@extends('layouts.admin')

@section('title')
Dashboard - KORPS
@endsection

@section('content')


@if(Auth::user()->user_type !== 'admin')
<!-- My Overview Section -->
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h4>My Overview</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb justify-content-end">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- My Total Tasks -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-project border-b-primary border-2">
                    <span class="f-light f-w-500 f-14">My Total Tasks</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $myTotalTasks }}</h2>
                            <span class="f-12 f-w-400">(All Time)</span>
                        </div>
                        <div class="product-sub bg-primary-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#color-swatch') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- My Pending Tasks -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Progress border-b-danger border-2">
                    <span class="f-light f-w-500 f-14">My Pending Tasks</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $myPendingTasks }}</h2>
                            <span class="f-12 f-w-400">(Current)</span>
                        </div>
                        <div class="product-sub bg-danger-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#tick-circle') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- My Completed Tasks -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Complete border-b-success border-2">
                    <span class="f-light f-w-500 f-14">My Completed Tasks</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $myCompletedTasks }}</h2>
                            <span class="f-12 f-w-400">(All Time)</span>
                        </div>
                        <div class="product-sub bg-success-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#tick-circle') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- My Attendance -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-upcoming border-b-secondary border-2">
                    <span class="f-light f-w-500 f-14">Attendance (This Month)</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">{{ $myAttendanceCount }}</h2>
                            <span class="f-12 f-w-400">Days Present</span>
                        </div>
                        <div class="product-sub bg-secondary-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#user-visitor') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- My Overview Charts -->
    <div class="row">
        <!-- Chart 1: Task Status -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Task Status</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="myTaskStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Chart 2: Weekly Attendance -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Weekly Attendance (Hours)</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="myWeeklyAttendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Chart 3: Monthly Performance -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Monthly Completion</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="myMonthlyPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Chart 4: Task Types -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Task Types</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="myTaskTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->user_type === 'admin' || $userDepartment === 'Sales' || $userDealershipId == null)
<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-6">
            <h4>Sales Dashboard</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card d-none">
                <div class="card-header">
                    <h5 class="card-title">
                        <a class="accordion-button collapsed" data-bs-toggle="collapse"
                            href="#topContributorsAccordion" role="button" aria-expanded="false"
                            aria-controls="topContributorsAccordion">
                            Top Contributors
                        </a>
                    </h5>
                </div>
                <div class="collapse" id="topContributorsAccordion">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <select class="form-select" id="topContributorsCategory">
                                    <option value="sales">Sales</option>
                                    <option value="service">Service</option>
                                    <option value="parts">Parts</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="topContributorsDateRange">
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="custom">Custom Date Period</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="customDateRangeContainer" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="date" class="form-control" id="startDate">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" class="form-control" id="endDate">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="topContributorsContent">
                            <div id="topContributorsLoader" class="text-center my-5" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading Top Contributors...</p>
                            </div>
                            <!-- Content will be loaded here via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Lead Stats -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-project border-b-primary border-2"><span
                        class="f-light f-w-500 f-14">Total Leads</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalLeadsLoader" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>
                                <span id="totalLeads" class="count-up-number"
                                    style="font-size: 2rem; font-weight: 600; display: none;">0</span>
                            </h2><span class="f-12 f-w-400">(All Time)</span>
                        </div>
                        <div class="product-sub bg-primary-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#color-swatch') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Progress border-b-warning border-2"> <span
                        class="f-light f-w-500 f-14">In Progress Leads</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="inProgressLeadsLoader" class="spinner-border spinner-border-sm"
                                    role="status" aria-hidden="true"></span>
                                <span id="inProgressLeads" class="count-up-number"
                                    style="font-size: 2rem; font-weight: 600; display: none;">0</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-warning-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#tick-circle') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Complete border-b-secondary border-2"><span
                        class="f-light f-w-500 f-14">Converted Leads</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="convertedLeadsLoader" class="spinner-border spinner-border-sm"
                                    role="status" aria-hidden="true"></span>
                                <span id="convertedLeads" class="count-up-number"
                                    style="font-size: 2rem; font-weight: 600; display: none;">0</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-secondary-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#add-square') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-upcoming"><span class="f-light f-w-500 f-14">Lost Leads</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="lostLeadsLoader" class="spinner-border spinner-border-sm"
                                    role="status" aria-hidden="true"></span>
                                <span id="lostLeads" class="count-up-number"
                                    style="font-size: 2rem; font-weight: 600; display: none;">0</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-light-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#edit-2') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>Top Sources</h4>
                </div>
                <div class="card-body">
                    <div id="topSourcesLoader" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Top Sources...</p>
                    </div>
                    <div id="leadSourceBreakdownChart"></div>
                </div>
            </div>
        </div>

        <!-- This Month's Sales Graph -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Sales Analytics</h4>
                        <div class="f-12 text-muted">
                            Revenue:
                            <strong id="salesRevenue">Rs. 0.00</strong>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 position-relative">
                        <select class="form-select form-select-sm chart-date-filter" style="width: 150px;" data-chart-type="sales">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_year">This Year</option>
                            <option value="custom">Custom</option>
                        </select>
                        <div class="custom-date-container d-none position-absolute bg-white p-3 shadow rounded" style="right: 0; z-index: 1000; top: 100%; border: 1px solid #ddd; width: 200px;">
                            <div class="mb-2">
                                <label class="form-label f-12">Start Date</label>
                                <input type="date" class="form-control form-control-sm start-date">
                            </div>
                            <div class="mb-2">
                                <label class="form-label f-12">End Date</label>
                                <input type="date" class="form-control form-control-sm end-date">
                            </div>
                            <button type="button" class="btn btn-primary btn-xs w-100 apply-custom-date">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="thisMonthsSalesLoader" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Sales Data...</p>
                    </div>
                    <canvas id="thisMonthsSalesChart"></canvas>
                </div>
            </div>
        </div>



        <!-- Top Clients -->
        <div class="col-lg-6 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header">
                    <h4 class="m-0">Top Clients</h4>
                </div>
                <div class="card-body">
                    <div id="topClientsLoader" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Top Clients...</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordernone" id="topClientsTable">
                            <tbody id="topClientsTableBody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Agents -->
        <div class="col-lg-6 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header">
                    <h4 class="m-0">Top Agents</h4>

                    <div class="card-body">
                        <div id="topAgentsLoader" class="text-center my-5" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading Top Agents...</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordernone" id="topAgentsTable">
                                <tbody id="topAgentsTableBody">
                                    <!-- Dynamic content will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>
@if(auth()->user()->dealership_id)
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sales Statistics</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <select class="form-control float-right" id="sales-filter">
                                <option value="this_month">This Month</option>
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="custom">Custom Date</option>
                            </select>
                            <div id="custom-date-range" style="display: none; margin-left: 10px;">
                                <input type="text" class="form-control" id="start_date"
                                    placeholder="Start Date" style="width: 120px; display: inline-block;">
                                <input type="text" class="form-control" id="end_date"
                                    placeholder="End Date"
                                    style="width: 120px; display: inline-block; margin-left: 5px;">
                            </div>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default" id="apply-filter"><i
                                        class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-center">
                                <strong>Sales</strong>
                            </p>
                            <div class="chart">
                                <canvas id="salesChart" height="180" style="height: 180px;"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p class="text-center">
                                <strong>Sales Details</strong>
                            </p>
                            <div id="sales-details">
                                <p>Total Sales: <span id="total-sales"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif
@endif
@if(auth()->user()->user_type === 'admin' || $userDepartment == 'Service' || $userDealershipId == null)
<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-6">
            <h4>Services Dashboard</h4>
        </div>
    </div>
    <div class="row">
        <!-- Service Stats -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-project border-b-primary border-2"><span
                        class="f-light f-w-500 f-14">Total Services</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalServicesLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="totalServices" class="count-up-number" style="font-size: 2rem; font-weight: 600; display: none;">{{ $totalServices }}</span>
                            </h2><span class="f-12 f-w-400">(All Time)</span>
                        </div>
                        <div class="product-sub bg-primary-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#color-swatch') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Progress border-b-warning border-2"> <span
                        class="f-light f-w-500 f-14">Service Engineers</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalServiceEngineersLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="totalServiceEngineers" class="count-up-number" style="font-size: 2rem; font-weight: 600; display: none;">{{ $totalServiceEngineers }}</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-warning-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#tick-circle') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Complete border-b-secondary border-2"><span
                        class="f-light f-w-500 f-14">Clients on Services</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalClientsOnServicesLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="totalClientsOnServices" class="count-up-number" style="font-size: 2rem; font-weight: 600; display: none;">{{ $totalClientsOnServices }}</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-secondary-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#add-square') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-upcoming border-b-info border-2"><span class="f-light f-w-500 f-14">Service Revenue</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalServiceRevenueLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="totalServiceRevenue" class="" style="font-size: 1.5rem; font-weight: 600; display: none;">Rs. 0.00</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-info-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#dollar-sign') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Top Products on Services -->
        <div class="col-lg-6 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header">
                    <h4 class="m-0">Top Products on Services</h4>
                </div>
                <div class="card-body">
                    <div id="topProductsLoader" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Top Products...</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordernone" id="topProductsTable">
                            <tbody id="topProductsTableBody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month's Services Line Graph -->
        <div class="col-lg-6 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Services Analytics</h4>
                        <div class="f-12 text-muted">
                            Revenue:
                            <strong id="servicesRevenue">Rs. 0.00</strong>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 position-relative">
                        <select class="form-select form-select-sm" style="width: 170px;" id="services-type-filter">
                            <option value="">All Service Types</option>
                            <option value="warranty_claimable">Warranty Claimable</option>
                            <option value="warranty_free_service">Warranty Free Service</option>
                            <option value="warranty_mandatory">Warranty Mandatory</option>
                            <option value="amc">AMC</option>
                            <option value="paid_service">Paid Service</option>
                            <option value="goodwill">Goodwill</option>
                        </select>
                        <select class="form-select form-select-sm chart-date-filter" style="width: 150px;" data-chart-type="services">
                            <option value="today" selected>Today</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_year">This Year</option>
                            <option value="custom">Custom</option>
                        </select>
                        <div class="custom-date-container d-none position-absolute bg-white p-3 shadow rounded" style="right: 0; z-index: 1000; top: 100%; border: 1px solid #ddd; width: 200px;">
                            <div class="mb-2">
                                <label class="form-label f-12">Start Date</label>
                                <input type="date" class="form-control form-control-sm start-date">
                            </div>
                            <div class="mb-2">
                                <label class="form-label f-12">End Date</label>
                                <input type="date" class="form-control form-control-sm end-date">
                            </div>
                            <button type="button" class="btn btn-primary btn-xs w-100 apply-custom-date">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="thisMonthsServicesLoader" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Services Data...</p>
                    </div>
                    <canvas id="thisMonthsServicesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Clients on Services -->
        <div class="col-lg-6 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header">
                    <h4 class="m-0">Top Clients on Services</h4>
                </div>
                <div class="card-body">
                    <div id="topClientsServicesLoader" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Top Clients...</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordernone" id="topClientsServicesTable">
                            <tbody id="topClientsServicesTableBody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Service Engineers -->
        <div class="col-lg-6 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header">
                    <h4 class="m-0">Top Service Engineers</h4>
                </div>
                <div class="card-body">
                    <div id="topServiceEngineersLoader" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Top Service Engineers...</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordernone" id="topServiceEngineersTable">
                            <tbody id="topServiceEngineersTableBody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif


@if(auth()->user()->user_type === 'admin' || $userDepartment == 'Parts' || $userDealershipId == null)
<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-6">
            <h4>Parts Dashboard</h4>
        </div>
    </div>
    <div class="row">
        <!-- Parts Stats -->
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-project border-b-primary border-2"><span
                        class="f-light f-w-500 f-14">Total Parts</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalPartsLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="totalParts" class="count-up-number" style="font-size: 2rem; font-weight: 600; display: none;">{{ $totalParts }}</span>
                            </h2><span class="f-12 f-w-400">(All Time)</span>
                        </div>
                        <div class="product-sub bg-primary-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#color-swatch') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Progress border-b-warning border-2"> <span
                        class="f-light f-w-500 f-14">Total Package Kits</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalPackageKitsLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="totalPackageKits" class="count-up-number" style="font-size: 2rem; font-weight: 600; display: none;">{{ $totalPackageKits }}</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-warning-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#tick-circle') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-Complete border-b-secondary border-2"><span
                        class="f-light f-w-500 f-14">Products With Parts</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalProductsWithPartsLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="totalProductsWithParts" class="count-up-number" style="font-size: 2rem; font-weight: 600; display: none;">{{ $totalProductsWithParts }}</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-secondary-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#add-square') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden small-widget">
                <div class="card-body total-upcoming"><span class="f-light f-w-500 f-14">Model Series With
                        Parts</span>
                    <div class="project-details">
                        <div class="project-counter">
                            <h2 class="f-w-600">
                                <span id="totalModelSeriesWithPartsLoader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span id="totalModelSeriesWithParts" class="count-up-number" style="font-size: 2rem; font-weight: 600; display: none;">{{ $totalModelSeriesWithParts }}</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
                        </div>
                        <div class="product-sub bg-light-light">
                            <svg class="invoice-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#edit-2') }}"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parts Analytics Charts -->
    <div class="row">
        <!-- Top Parts (In Package Kits) -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>Top Parts (In Package Kits)</h4>
                </div>
                <div class="card-body">
                    <canvas id="topPartsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Largest Package Kits -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>Largest Package Kits</h4>
                </div>
                <div class="card-body">
                    <canvas id="topPackageKitsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Parts by Product Model -->
        <!-- Parts Stock Status -->
        <div class="col-lg-6 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header">
                    <h4>Parts Stock Status</h4>
                </div>
                <div class="card-body">
                    <canvas id="stockStatusChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Parts Added This Month -->
        <div class="col-lg-6 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="m-0">Parts Added Analytics</h4>
                    <div class="d-flex align-items-center gap-2 position-relative">
                        <select class="form-select form-select-sm chart-date-filter" style="width: 150px;" data-chart-type="parts">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="custom">Custom</option>
                        </select>
                        <div class="custom-date-container d-none position-absolute bg-white p-3 shadow rounded" style="right: 0; z-index: 1000; top: 100%; border: 1px solid #ddd; width: 200px;">
                            <div class="mb-2">
                                <label class="form-label f-12">Start Date</label>
                                <input type="date" class="form-control form-control-sm start-date">
                            </div>
                            <div class="mb-2">
                                <label class="form-label f-12">End Date</label>
                                <input type="date" class="form-control form-control-sm end-date">
                            </div>
                            <button type="button" class="btn btn-primary btn-xs w-100 apply-custom-date">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="partsAddedChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@else
{{-- Default message for users with no specific department or no dealership_id --}}
{{-- <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <p>You are not associated with a Sales, Service, or Parts department, or your dealership
                                information is missing. Please contact the administrator.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
@endif

@push('scripts')
<script src="{{ asset('admin/assets/js/dashboard/dashboard_2.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Debugging: Log the data passed from the controller
        const myTaskStatusCounts = {!!json_encode($myTaskStatusCounts) !!};
        const myWeeklyAttendance = {!!json_encode($myWeeklyAttendance) !!};
        const myWeeklyAttendanceLabels = {!!json_encode($myWeeklyAttendanceLabels) !!};
        const myMonthlyCompletionData = {!!json_encode($myMonthlyCompletionData) !!};
        const myMonthlyCompletionLabels = {!!json_encode($myMonthlyCompletionLabels) !!};
        const myTaskTypeCounts = {!!json_encode($myTaskTypeCounts) !!};



        // My Task Status Chart
        const taskStatusCanvas = document.getElementById('myTaskStatusChart');
        if (taskStatusCanvas) {
            const taskStatusCtx = taskStatusCanvas.getContext('2d');
            if (Object.keys(myTaskStatusCounts).length > 0) {
                new Chart(taskStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(myTaskStatusCounts),
                        datasets: [{
                            data: Object.values(myTaskStatusCounts),
                            backgroundColor: ['#7366ff', '#f73164', '#51bb25', '#f8d62b', '#a927f9'],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            } else {
                // Show no data message
                taskStatusCanvas.style.display = 'none';
                taskStatusCanvas.parentElement.innerHTML = `
                        <div class="d-flex flex-column justify-content-center align-items-center h-100">
                            <svg class="stroke-icon mb-3" style="width: 48px; height: 48px; stroke: #c4c4c4;">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-task') }}"></use>
                            </svg>
                            <p class="text-muted mb-0 f-14">No task data available</p>
                        </div>`;
            }
        }

        // My Weekly Attendance Chart
        const weeklyAttendanceCanvas = document.getElementById('myWeeklyAttendanceChart');
        if (weeklyAttendanceCanvas) {
            const weeklyAttendanceCtx = weeklyAttendanceCanvas.getContext('2d');
            new Chart(weeklyAttendanceCtx, {
                type: 'bar',
                data: {
                    labels: myWeeklyAttendanceLabels,
                    datasets: [{
                        label: 'Hours Worked',
                        data: myWeeklyAttendance,
                        backgroundColor: '#51bb25',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // My Monthly Performance Chart
        const monthlyPerformanceCanvas = document.getElementById('myMonthlyPerformanceChart');
        if (monthlyPerformanceCanvas) {
            const monthlyPerformanceCtx = monthlyPerformanceCanvas.getContext('2d');
            new Chart(monthlyPerformanceCtx, {
                type: 'line',
                data: {
                    labels: myMonthlyCompletionLabels,
                    datasets: [{
                        label: 'Tasks Completed',
                        data: myMonthlyCompletionData,
                        borderColor: '#7366ff',
                        tension: 0.4,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // My Task Type Chart
        const taskTypeCanvas = document.getElementById('myTaskTypeChart');
        if (taskTypeCanvas) {
            const taskTypeCtx = taskTypeCanvas.getContext('2d');
            if (Object.keys(myTaskTypeCounts).length > 0) {
                new Chart(taskTypeCtx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(myTaskTypeCounts),
                        datasets: [{
                            data: Object.values(myTaskTypeCounts),
                            backgroundColor: ['#f8d62b', '#a927f9', '#7366ff', '#f73164', '#51bb25'],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            } else {
                // Show no data message
                taskTypeCanvas.style.display = 'none';
                taskTypeCanvas.parentElement.innerHTML = `
                        <div class="d-flex flex-column justify-content-center align-items-center h-100">
                            <svg class="stroke-icon mb-3" style="width: 48px; height: 48px; stroke: #c4c4c4;">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-layout') }}"></use>
                            </svg>
                            <p class="text-muted mb-0 f-14">No task type data available</p>
                        </div>`;
            }
        }

        $('#topContributorsDateRange').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#customDateRangeContainer').show();
            } else {
                $('#customDateRangeContainer').hide();
            }
        });

        function fetchTopContributors() {
            let category = $('#topContributorsCategory').val();
            let dateRange = $('#topContributorsDateRange').val();
            let startDate = $('#startDate').val();
            let endDate = $('#endDate').val();

            // If there's already content, slide it up before fetching new data.
            if ($('#topContributorsContent').children('table').length > 0) {
                $('#topContributorsContent').children('table').slideUp(300, function() {
                    loadTopContributors(category, dateRange, startDate, endDate);
                });
            } else {
                // If there's no content yet, just load it.
                loadTopContributors(category, dateRange, startDate, endDate);
            }
        }

        function loadTopContributors(category, dateRange, startDate, endDate) {
            $('#topContributorsLoader').show();
            $.ajax({
                url: "{{ route('dashboard.top-contributors') }}",
                type: 'GET',
                data: {
                    category: category,
                    date_range: dateRange,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    $('#topContributorsLoader').hide();
                    $('#topContributorsContent').html(response).children().hide().slideDown(300);
                },
                error: function(xhr) {
                    $('#topContributorsLoader').hide();

                    $('#topContributorsContent').html(
                        '<p class="text-danger">Error loading data.</p>');
                }
            });
        }

        $('#topContributorsCategory, #topContributorsDateRange').on('change', fetchTopContributors);
        $('#startDate, #endDate').on('change', function() {
            if ($('#topContributorsDateRange').val() === 'custom') {
                fetchTopContributors();
            }
        });

        // Initial fetch
        fetchTopContributors();

        if ($('#salesChart').length) {
            let salesChart;

            // Initialize Flatpickr
            flatpickr("#start_date", {
                dateFormat: "Y-m-d",
            });
            flatpickr("#end_date", {
                dateFormat: "Y-m-d",
            });

            function fetchSalesData(filter, startDate = null, endDate = null) {
                $.ajax({
                    url: "{{ route('dashboard.sales-statistics') }}",
                    type: 'GET',
                    data: {
                        filter: filter,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        $('#total-sales').text(response.sales);
                        updateSalesChart(response.sales);
                    },
                    error: function(xhr) {

                    }
                });
            }

            function updateSalesChart(sales) {
                const data = {
                    labels: ['Sales'],
                    datasets: [{
                        label: 'Sales',
                        data: [sales],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                        ],
                        borderWidth: 1
                    }]
                };

                if (salesChart) {
                    salesChart.destroy();
                }

                salesChart = new Chart(document.getElementById('salesChart'), {
                    type: 'bar',
                    data: data,
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Initial fetch
            fetchSalesData('this_month');

            $('#sales-filter').on('change', function() {
                const filter = $(this).val();
                if (filter === 'custom') {
                    $('#custom-date-range').show();
                } else {
                    $('#custom-date-range').hide();
                    fetchSalesData(filter);
                }
            });

            $('#apply-filter').on('click', function() {
                const filter = $('#sales-filter').val();
                if (filter === 'custom') {
                    const startDate = $('#start_date').val();
                    const endDate = $('#end_date').val();
                    fetchSalesData(filter, startDate, endDate);
                } else {
                    fetchSalesData(filter);
                }
            });
        }

        // Service Department Analytics
        @if($userDepartment === 'Service' || $userDealershipId == null)

        function fetchTopProductsOnServices() {
            $('#topProductsLoader').show();
            $.ajax({
                url: "{{ route('dashboard.top-products-on-services') }}",
                type: 'GET',
                success: function(response) {
                    $('#topProductsLoader').hide();
                    let tableBody = $('#topProductsTableBody');
                    tableBody.empty();
                    if (response.length > 0) {
                        // Add table header
                        tableBody.append(`
                                    <tr>
                                        <th class="f-light">Product Name</th>
                                        <th class="f-light">Service Count</th>
                                    </tr>
                                `);
                        $.each(response, function(index, item) {
                            tableBody.append(`
                                        <tr>
                                            <td>${item.name}</td>
                                            <td><span class="badge badge-light-primary">${item.count}</span></td>
                                        </tr>
                                    `);
                        });
                    } else {
                        tableBody.append(
                            '<tr><td colspan="2" class="text-center">No products on services found.</td></tr>'
                        );
                    }
                },
                error: function(xhr) {
                    $('#topProductsLoader').hide();
                    console.error('Error fetching top products on services:', xhr.responseText);
                    $('#topProductsTableBody').append(
                        '<tr><td colspan="2" class="text-center">Error loading data.</td></tr>'
                    );
                }
            });
        }

        function fetchTopServiceEngineers() {
            $('#topServiceEngineersLoader').show();
            $.ajax({
                url: "{{ route('dashboard.top-service-engineers') }}",
                type: 'GET',
                success: function(response) {
                    $('#topServiceEngineersLoader').hide();
                    let tableBody = $('#topServiceEngineersTableBody');
                    tableBody.empty();
                    if (response.length > 0) {
                        // Add table header
                        tableBody.append(`
                                        <tr>
                                            <th class="f-light">Engineer Name</th>
                                            <th class="f-light">Task Count</th>
                                        </tr>
                                    `);
                        $.each(response, function(index, item) {
                            tableBody.append(`
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1 ms-2">${item.name}</div>
                                                </div>
                                            </td>
                                            <td><span class="badge badge-light-primary">${item.count}</span></td>
                                        </tr>
                                    `);
                        });
                    } else {
                        tableBody.append(
                            '<tr><td colspan="2" class="text-center">No service engineers found.</td></tr>'
                        );
                    }
                },
                error: function(xhr) {
                    $('#topServiceEngineersLoader').hide();
                    console.error('Error fetching top service engineers:', xhr.responseText);
                    $('#topServiceEngineersTableBody').append(
                        '<tr><td colspan="2" class="text-center">Error loading data.</td></tr>'
                    );
                }
            });
        }

        // Fetch data on page load for Service Dashboard
        fetchTopProductsOnServices();
        fetchTopServiceEngineers();
        fetchTopClientsOnServices();



        function fetchTopClientsOnServices() {
            $('#topClientsServicesLoader').show();
            $.ajax({
                url: "{{ route('dashboard.top-clients-on-services') }}",
                type: 'GET',
                success: function(response) {
                    $('#topClientsServicesLoader').hide();
                    let tableBody = $('#topClientsServicesTableBody');
                    tableBody.empty();
                    if (response.length > 0) {
                        tableBody.append(`
                                        <tr>
                                            <th class="f-light">Client Name</th>
                                            <th class="f-light">Service Count</th>
                                        </tr>
                                    `);
                        $.each(response, function(index, item) {
                            tableBody.append(`
                                        <tr>
                                            <td>${item.name}</td>
                                            <td><span class="badge badge-light-secondary">${item.count}</span></td>
                                        </tr>
                                    `);
                        });
                    } else {
                        tableBody.append(
                            '<tr><td colspan="2" class="text-center">No clients on services found.</td></tr>'
                        );
                    }
                }
            });
        }


        @endif

        // Parts Dashboard Analytics
        @if($userDepartment == 'Parts' || $userDealershipId == null)
        const topPartsData = {!!json_encode($topSellingParts ?? []) !!};
        const topPackageKitsData = {!!json_encode($topPackageKits ?? []) !!};

        const stockStatusData = {!!json_encode($stockStatusCounts ?? []) !!};

        const partsAddedData = {!!json_encode($partsSalesData ?? []) !!};
        const partsAddedLabels = {!!json_encode($partsSalesLabels ?? []) !!};

        // Chart 1: Top Parts (In Package Kits)
        if (document.getElementById('topPartsChart')) {
            new Chart(document.getElementById('topPartsChart'), {
                type: 'bar',
                data: {
                    labels: topPartsData.map(d => d.name || d.part_number || 'N/A'),
                    datasets: [{
                        label: 'Package Kits Count',
                        data: topPartsData.map(d => d.package_kits_count),
                        backgroundColor: '#7366ff'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Chart 2: Largest Package Kits
        if (document.getElementById('topPackageKitsChart')) {
            new Chart(document.getElementById('topPackageKitsChart'), {
                type: 'bar',
                data: {
                    labels: topPackageKitsData.map(d => d.name || 'N/A'),
                    datasets: [{
                        label: 'Parts Count',
                        data: topPackageKitsData.map(d => d.parts_count),
                        backgroundColor: '#f73164'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Chart 3: Parts Stock Status
        if (document.getElementById('stockStatusChart')) {
            const totalStock = Object.values(stockStatusData).reduce((a, b) => a + b, 0);
            if (totalStock > 0) {
                new Chart(document.getElementById('stockStatusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(stockStatusData),
                        datasets: [{
                            data: Object.values(stockStatusData),
                            backgroundColor: ['#f73164', '#f8d62b', '#51bb25'], // Red, Yellow, Green
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            } else {
                document.getElementById('stockStatusChart').style.display = 'none';
                document.getElementById('stockStatusChart').closest('.card-body').innerHTML += '<div class="text-center p-4">No stock data available</div>';
            }
        }


        @endif

        let salesChartInstance = null;
        let servicesChartInstance = null;
        let partsChartInstance = null;

        $('.chart-date-filter').on('change', function() {
            const chartType = $(this).data('chart-type');
            const filter = $(this).val();

            if (filter === 'custom') {
                $(this).next('.custom-date-container').removeClass('d-none');
            } else {
                $(this).next('.custom-date-container').addClass('d-none');
                if (chartType === 'sales') fetchSalesAnalytics(filter);
                if (chartType === 'services') fetchServicesAnalytics(filter);
                if (chartType === 'parts') fetchPartsAnalytics(filter);
            }
        });

        $('.apply-custom-date').on('click', function() {
            const container = $(this).closest('.custom-date-container');
            const select = container.prev('.chart-date-filter');
            const chartType = select.data('chart-type');
            const startDate = container.find('.start-date').val();
            const endDate = container.find('.end-date').val();

            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }

            container.addClass('d-none');

            if (chartType === 'sales') fetchSalesAnalytics('custom', startDate, endDate);
            if (chartType === 'services') fetchServicesAnalytics('custom', startDate, endDate);
            if (chartType === 'parts') fetchPartsAnalytics('custom', startDate, endDate);
        });

        function fetchSalesAnalytics(filter = 'this_month', startDate = null, endDate = null) {
            $('#thisMonthsSalesLoader').show();
            $.ajax({
                url: '{{ route('dashboard.this-months-sales') }}',
                type: 'GET',
                data: {
                    filter,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    $('#thisMonthsSalesLoader').hide();
                    const ctx = document.getElementById('thisMonthsSalesChart');
                    if (!ctx) return;

                    if (typeof response.revenue !== 'undefined') {
                        const revenueValue = Number(response.revenue || 0);
                        $('#salesRevenue').text(
                            'Rs. ' + revenueValue.toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            })
                        );
                    }

                    if (salesChartInstance) salesChartInstance.destroy();

                    salesChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: response.labels,
                            datasets: [{
                                label: 'Sales',
                                data: response.salesData,
                                borderColor: '#7366ff',
                                tension: 0.4,
                                fill: false
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            });
        }

        function fetchServicesAnalytics(filter = 'today', startDate = null, endDate = null) {
            $('#thisMonthsServicesLoader').show();
            const serviceType = $('#services-type-filter').val();
            $.ajax({
                url: '{{ route('dashboard.this-months-services') }}',
                type: 'GET',
                data: {
                    filter,
                    start_date: startDate,
                    end_date: endDate,
                    service_type: serviceType
                },
                success: function(response) {
                    $('#thisMonthsServicesLoader').hide();
                    const ctx = document.getElementById('thisMonthsServicesChart');
                    if (!ctx) return;

                    if (typeof response.revenue !== 'undefined') {
                        const revenueValue = Number(response.revenue || 0);
                        $('#servicesRevenue').text(
                            'Rs. ' + revenueValue.toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            })
                        );
                    }

                    if (servicesChartInstance) servicesChartInstance.destroy();

                    servicesChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: response.labels,
                            datasets: [{
                                label: 'Services',
                                data: response.servicesData,
                                borderColor: '#7366ff',
                                tension: 0.4,
                                fill: false
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            });
        }

        $('#services-type-filter').on('change', function() {
            const servicesDateFilter = $('.chart-date-filter[data-chart-type="services"]').val() || 'today';
            if (servicesDateFilter === 'custom') {
                const container = $('.chart-date-filter[data-chart-type="services"]').next('.custom-date-container');
                const startDate = container.find('.start-date').val();
                const endDate = container.find('.end-date').val();
                if (startDate && endDate) {
                    fetchServicesAnalytics('custom', startDate, endDate);
                } else {
                    fetchServicesAnalytics('today');
                }
            } else {
                fetchServicesAnalytics(servicesDateFilter);
            }
        });

        function fetchPartsAnalytics(filter = 'this_month', startDate = null, endDate = null) {
            const ctx = document.getElementById('partsAddedChart');
            if (!ctx) return;

            $.ajax({
                url: '{{ route('dashboard.parts-added-analytics') }}',
                type: 'GET',
                data: {
                    filter,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    if (partsChartInstance) partsChartInstance.destroy();

                    partsChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: response.labels,
                            datasets: [{
                                label: 'New Parts',
                                data: response.data,
                                borderColor: '#51bb25',
                                tension: 0.4,
                                fill: false
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }

        // Calls
        fetchSalesAnalytics();
        fetchServicesAnalytics();
        fetchPartsAnalytics();

    });
</script>
@endpush



@endsection