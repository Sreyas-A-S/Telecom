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

@if(auth()->user()->user_type === 'admin' || $userDealershipId == null)
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h4>Dashboard</h4>
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
</div>
@endif

@if(auth()->user()->user_type === 'admin' || $userDepartment === 'Sales' || $userDealershipId == null)
<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-3">
            <h5>Leads Dashboard</h5>
        </div>
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
                                <span id="lostLeadsLoader" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>
                                <span id="lostLeads" class="count-up-number"
                                    style="font-size: 2rem; font-weight: 600; display: none;">0</span>
                            </h2><span class="f-12 f-w-400">(All Time) </span>
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
    </div>
    <div class="row">
        <div class="col-xl-4 col-md-12">
            <div class="card">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h4>Top Sources</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div id="lead-source-breakdown-chart"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-md-12">
            <div class="card">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <div class="d-flex align-items-center">
                            <h4 class="mb-1">Sales Analytics</h4>
                            <div class="ms-3">
                                <strong>Revenue:</strong> <span id="sales-revenue">Rs. 0.00</span>
                            </div>
                        </div>
                        <div class="dropdown icon-dropdown">
                            <button class="btn dropdown-toggle" id="salesFilterDropdown" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false"><i class="icon-more-alt"></i></button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesFilterDropdown">
                                <a class="dropdown-item sales-filter" href="#" data-filter="this_week">This Week</a>
                                <a class="dropdown-item sales-filter" href="#" data-filter="last_week">Last Week</a>
                                <a class="dropdown-item sales-filter" href="#" data-filter="this_month">This Month</a>
                                <a class="dropdown-item sales-filter" href="#" data-filter="last_month">Last Month</a>
                                <a class="dropdown-item sales-filter" href="#" data-filter="custom">Custom Range</a>
                            </div>
                        </div>
                    </div>
                    <div id="sales-custom-date-range" class="mt-2 row d-none">
                        <div class="col-md-5">
                            <input type="date" class="form-control form-control-sm" id="sales-start-date">
                        </div>
                        <div class="col-md-5">
                            <input type="date" class="form-control form-control-sm" id="sales-end-date">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-sm w-100" id="apply-sales-filter">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="this-months-sales-chart"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12">
            <div class="card h-100">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h4 class="m-0">Top Clients</h4>
                    </div>
                </div>
                <div class="card-body pt-0">
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

        <div class="col-xl-6 col-md-12">
            <div class="card h-100">
                <div class="card-header card-no-border pb-0">
                    <div class="header-top">
                        <h4 class="m-0">Top Agents</h4>
                    </div>
                </div>
                <div class="card-body pt-0">
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
@endif

{{-- 
@if(auth()->user()->user_type === 'admin' || $userDepartment == 'Service' || $userDealershipId == null)
<!-- Services Dashboard content... -->
@endif

@if(auth()->user()->user_type === 'admin' || $userDepartment == 'Parts' || $userDealershipId == null)
<!-- Parts Dashboard content... -->
@endif
--}}

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
            new Chart(taskTypeCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(myTaskTypeCounts),
                    datasets: [{
                        data: Object.values(myTaskTypeCounts),
                        backgroundColor: ['#54ba4a', '#ffa119', '#7366ff', '#f73164', '#16c7f9'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
</script>
@endpush
@endsection
