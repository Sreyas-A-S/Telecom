<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardAnalyticsApiController;

Route::prefix('dashboard')->group(function () {
    Route::get('lead-statistics', [DashboardAnalyticsApiController::class, 'getLeadStatistics']);
    Route::get('lead-source-breakdown', [DashboardAnalyticsApiController::class, 'getLeadSourceBreakdown']);
    Route::get('employee-lead-performance', [DashboardAnalyticsApiController::class, 'getEmployeeLeadPerformance']);
    Route::get('top-clients', [DashboardAnalyticsApiController::class, 'getTopClients']);
    Route::get('recent-activities', [DashboardAnalyticsApiController::class, 'getRecentActivities']);
    Route::get('upcoming-events', [DashboardAnalyticsApiController::class, 'getUpcomingEvents']);
    Route::get('all-leads', [DashboardAnalyticsApiController::class, 'getAllLeads']);
});
