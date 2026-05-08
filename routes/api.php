<?php

use App\Http\Controllers\Api\TaskTimeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\TaskController; // Added TaskController
use App\Http\Controllers\Api\EmployeeController; // Added EmployeeController
use App\Http\Controllers\Api\LeaveRequestApiController;
use App\Http\Controllers\Api\ExpenseRequestApiController;
use App\Http\Controllers\Api\DocumentRequestApiController;
use App\Http\Controllers\Api\LoanRequestApiController;
use App\Http\Controllers\Api\OrganizationHierarchyApiController;
use App\Http\Controllers\Api\AgentController; // Added AgentController
use App\Http\Controllers\Api\LeadSourceController;
use App\Http\Controllers\Api\LeadCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductModelController;
use App\Http\Controllers\Api\DealershipController;
use App\Http\Controllers\Api\ModelSeriesController;
use App\Http\Controllers\Api\ClockApiController;
use App\Http\Controllers\Api\FSRReportController; // Added FSRReportController
use App\Http\Controllers\Api\PartController; // Added PartController
use App\Http\Controllers\Api\FSRQuotationController; // Updated FSRQuotationController
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DocumentTypeApiController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ZoneController;
use App\Http\Controllers\Api\GpsTraceController; // Added GpsTraceController
use App\Http\Controllers\Api\ServiceManagerApprovalApiController;
use App\Http\Controllers\Api\TaskFollowupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BrandSettingController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\Api\NotificationController;

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::get('/permissions/by-role/{role}', [PermissionController::class, 'getPermissionsByRole']);

Route::middleware(['auth:api'])->group(function () {

    Route::post('user/player-id', [AuthController::class, 'updatePlayerId']);

    Route::get('clients/search-by-phone', [LeadController::class, 'searchClientsByPhoneApi']);


    Route::get('services/service-engineers', [ServiceController::class, 'getServiceEngineers']);
    Route::get('services/products', [ServiceController::class, 'getProducts']);
    Route::get('services/clients', [ServiceController::class, 'getClients']);
    Route::apiResource('services', ServiceController::class)->names('api.services');
    Route::put('services/{service}/assign-engineer', [ServiceController::class, 'assignEngineer']);
    Route::resource('clients', ClientController::class)->except(['create', 'edit'])->names('api.clients');
    Route::resource('agents', AgentController::class)->names('api.agents'); //
    Route::resource('lead-sources', LeadSourceController::class)->only(['index', 'store'])->names('api.lead-sources');
    Route::resource('lead-categories', LeadCategoryController::class)->only(['index', 'store'])->names('api.lead-categories');
    Route::resource('products', ProductController::class)->only(['index', 'store'])->names('api.products');
    Route::get('products/{product}/product-models', [ProductController::class, 'getProductModels']);
    Route::apiResource('product-models', ProductModelController::class);
    Route::get('product-models/{productModel}/model-series', [ModelSeriesController::class, 'getModelSeriesByProductModelId']); // New route
    Route::apiResource('model-series', ModelSeriesController::class);
    Route::get('products/{product}/product-models', [ProductModelController::class, 'getProductModelsByProductId']);
    Route::resource('dealerships', DealershipController::class)->only(['index', 'store'])->names('api.dealerships');
    Route::resource('leads', LeadController::class)->names('api.leads');
    Route::put('leads', [LeadController::class, 'update']);
    Route::get('leads/{lead}/profile', [LeadController::class, 'profile']);
    Route::get('leads/export-excel', [LeadController::class, 'exportExcel']);
    Route::put('leads/{lead}/status', [LeadController::class, 'updateStatus']);
    Route::put('leads/{lead}/update-chance-of-success', [LeadController::class, 'updateChanceOfSuccess']);
    Route::put('leads/{lead}/update-billing', [LeadController::class, 'updateBilling']);
    Route::put('leads/{lead}/loss-reason', [LeadController::class, 'updateLossReason']);
    Route::post('lead-sources', [LeadController::class, 'storeLeadSource']);
    Route::put('leads/{lead}/assign-employee', [LeadController::class, 'assignEmployee']);
    Route::post('leads/{lead}/convert', [LeadController::class, 'convertToClient']);
    Route::get('clients/search-by-phone', [LeadController::class, 'searchClientsByPhoneApi']);

    Route::get('leads/{lead}/followups', [LeadController::class, 'getFollowups']);
    Route::get('leads/{lead}/fsr-reports', [LeadController::class, 'getFsrReports']);
    Route::post('leads/{lead}/followups', [LeadController::class, 'storeFollowup']);
    Route::get('leads/{lead}/followups/{followup}/edit', [LeadController::class, 'editFollowup']);
    Route::put('leads/{lead}/followups/{followup}', [LeadController::class, 'updateFollowup']);
    Route::delete('leads/{lead}/followups/{followup}', [LeadController::class, 'deleteFollowup']);

    Route::post('permissions/check-menu-group', [PermissionController::class, 'checkMenuGroup'])->name('api.permissions.check-menu-group');
    Route::post('permissions/check-menu', [PermissionController::class, 'checkMenu'])->name('api.permissions.check-menu');

    // Employee Api Routes
    Route::resource('employees', EmployeeController::class)->only(['index'])->names('api.employees');
    Route::post('employees/update-vehicle-type', [EmployeeController::class, 'updateVehicleType'])->name('api.employees.update-vehicle-type');
    Route::get('employees/current-vehicle-type', [EmployeeController::class, 'getVehicleType'])->name('api.employees.current-vehicle-type');
    Route::post('employees/notify-vehicle-type-change', [EmployeeController::class, 'notifyVehicleTypeChange'])->name('api.employees.notify-vehicle-type-change');
    // Route::get('employees/search', [EmployeeController::class, 'search'])->name('api.employees.search');

    Route::apiResource('leave-requests', LeaveRequestApiController::class)->except(['update'])->names('api.leave-requests');
    Route::post('leave-requests/{leave_request}', [LeaveRequestApiController::class, 'updateFromPost'])->name('api.leave-requests.update');
    Route::apiResource('document-requests', DocumentRequestApiController::class)->except(['update'])->names('api.document-requests');
    Route::post('document-requests/{document_request}', [DocumentRequestApiController::class, 'updateFromPost'])->name('api.document-requests.update');
    Route::put('document-requests/{document_request}/change-status', [DocumentRequestApiController::class, 'changeStatus'])->name('api.document-requests.change-status');
    // New API route for Document Types
    Route::get('document-types', [DocumentTypeApiController::class, 'index'])->name('api.document-types.index');
    Route::get('zones', [ZoneController::class, 'index'])->name('api.zones.index');

    Route::post('expense-requests/{expense_request}', [ExpenseRequestApiController::class, 'updateFromPost'])->name('api.expense-requests.updateFromPost');
    Route::put('expense-requests/{expense_request}/change-status', [ExpenseRequestApiController::class, 'changeStatus'])->name('api.expense-requests.change-status');
    Route::resource('expense-requests', ExpenseRequestApiController::class)->names('api.expense-requests');
    Route::resource('loan-requests', LoanRequestApiController::class)->names('api.loan-requests');
    Route::put('loan-requests/{loan_request}/change-status', [LoanRequestApiController::class, 'changeStatus'])->name('api.loan-requests.change-status');
    Route::put('leave-requests/{leave_request}/update-status', [LeaveRequestApiController::class, 'updateStatus'])->name('api.leave-requests.update-status');


    // Task Api Routes
    Route::get('tasks/my-tasks', [TaskController::class, 'getMyTasks']);
    Route::post('tasks/{task}/status', [TaskController::class, 'updateStatus']);

    Route::post('visits/start', [GpsTraceController::class, 'startVisit'])->name('api.visits.start');
    Route::post('visits/mark', [GpsTraceController::class, 'markVisit'])->name('api.visits.mark');
    Route::post('visits/halt', [GpsTraceController::class, 'haltVisit'])->name('api.visits.halt');
    Route::post('visits/report-location', [GpsTraceController::class, 'reportLocation'])->name('api.visits.report-location');
    Route::get('tasks/{task}/gps-traces', [GpsTraceController::class, 'getGpsTracesByTaskId'])->name('api.tasks.gpsTraces');
    Route::get('users/{userId}/gps-trace-status', [GpsTraceController::class, 'getLatestGpsTraceStatus'])->name('api.users.gpsTraceStatus');
    Route::get('user/tracking-status', [GpsTraceController::class, 'getUserTrackingStatus'])->name('api.user.trackingStatus');
    Route::apiResource('tasks', TaskController::class)->names('api.tasks');
    Route::post('tasks/{task}/start', [TaskController::class, 'startTask'])->name('api.tasks.start');
    Route::post('tasks/{task}/pause', [TaskController::class, 'pauseTask'])->name('api.tasks.pause');
    Route::post('tasks/{task}/resume', [TaskController::class, 'resumeTask'])->name('api.tasks.resume');
    Route::post('tasks/{task}/stop', [TaskController::class, 'stopTask'])->name('api.tasks.stop');
    Route::post('tasks/{task}/approve-early-action', [TaskController::class, 'approveEarlyAction'])->name('api.tasks.approveEarlyAction');

    Route::get('tasks/{task}/followups', [TaskFollowupController::class, 'index'])->name('api.tasks.followups.index');
    Route::post('tasks/{task}/followups', [TaskFollowupController::class, 'store'])->name('api.tasks.followups.store');
    Route::get('tasks/{task}/followups/{followup}', [TaskFollowupController::class, 'edit'])->name('api.tasks.followups.edit');
    Route::put('tasks/{task}/followups/{followup}', [TaskFollowupController::class, 'update'])->name('api.tasks.followups.update');
    Route::delete('tasks/{task}/followups/{followup}', [TaskFollowupController::class, 'destroy'])->name('api.tasks.followups.destroy');

    Route::get('tasks/{task}/time', [TaskTimeController::class, 'getTaskTime'])->name('api.tasks.time');
    Route::get('tasks/{task}/time-logs', [TaskTimeController::class, 'getTaskLogs'])->name('api.tasks.time-logs');


    // FSR Report API Routes
    Route::get('fsr-reports', [FSRReportController::class, 'index'])->name('api.fsr-reports.index');
    Route::post('fsr-reports', [FSRReportController::class, 'store'])->name('api.fsr-reports.store');
    Route::get('fsr-reports/{id}', [FSRReportController::class, 'show'])->name('api.fsr-reports.show');
    Route::post('fsr-reports/{id}', [FSRReportController::class, 'updateFromPost'])->name('api.fsr-reports.update');
    Route::delete('fsr-reports/{id}', [FSRReportController::class, 'destroy'])->name('api.fsr-reports.destroy');
    Route::get('fsr-payments', [FSRReportController::class, 'getPayments'])->name('api.fsr-reports.getPayments');
    Route::post('fsr-payments', [FSRReportController::class, 'storePayment'])->name('api.fsr-reports.storePayment');
    Route::get('fsr-payments/{payment}', [FSRReportController::class, 'showPayment'])->name('api.fsr-reports.showPayment');
    Route::put('fsr-payments/{payment}', [FSRReportController::class, 'updatePayment'])->name('api.fsr-reports.updatePayment');
    Route::delete('fsr-payments/{payment}', [FSRReportController::class, 'deletePayment'])->name('api.fsr-reports.deletePayment');
    Route::delete('fsr-reports/{id}/images/{imageIndex}', [FSRReportController::class, 'deleteImage'])->name('api.fsr-reports.deleteImage');

    // Part API Routes
    Route::apiResource('parts', PartController::class)->names('api.parts');
    Route::get('parts/search', [PartController::class, 'search'])->name('api.parts.search');

    // FSR Part Quotation API Routes
    Route::apiResource('fsr-quotations', FSRQuotationController::class)->except(['create', 'edit'])->names('api.fsr-quotations');
    Route::put('fsr-quotations/{fsr_quotation}/approve', [FSRQuotationController::class, 'approve'])->name('api.fsr-quotations.approve');
    Route::put('fsr-quotations/{fsr_quotation}/reject', [FSRQuotationController::class, 'reject'])->name('api.fsr-quotations.reject');







    Route::get('organization/hierarchy', [OrganizationHierarchyApiController::class, 'getHierarchyApi']);

    // Clock In/Out
    Route::post('clock/in', [ClockApiController::class, 'clockIn']);
    Route::post('clock/out', [ClockApiController::class, 'clockOut']);
    Route::get('clock/status', [ClockApiController::class, 'getClockStatus']);

    // Dashboard Analytics API Routes
    Route::get('dashboard/lead-statistics', [DashboardController::class, 'getLeadStatistics']);
    Route::get('dashboard/lead-source-breakdown', [DashboardController::class, 'getLeadSourceBreakdown']);
    Route::get('dashboard/employee-lead-performance', [DashboardController::class, 'getEmployeeLeadPerformance']);
    Route::get('dashboard/top-clients', [DashboardController::class, 'getTopClients']);
    Route::get('dashboard/recent-activities', [DashboardController::class, 'getRecentActivities']);
    Route::get('dashboard/upcoming-events', [DashboardController::class, 'getUpcomingEvents']);
    Route::get('dashboard/all-leads', [DashboardController::class, 'getAllLeads']);

    Route::get('brand-settings/{dealership_id}/settings', [BrandSettingController::class, 'getDealershipSettings']);

    Route::get('service-manager/approvals', [ServiceManagerApprovalApiController::class, 'getTasksForApproval']);
    Route::post('service-manager/approvals/{task}', [ServiceManagerApprovalApiController::class, 'approveEarlyAction']);
    Route::get('/permissions/by-role/{role}', [PermissionController::class, 'getPermissionsByRole']);

    Route::apiResource('notifications', NotificationController::class)->only(['index'])->names('api.notifications');

    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
    Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('api.notifications.mark-as-read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-as-read');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('api.notifications.destroy');
});
