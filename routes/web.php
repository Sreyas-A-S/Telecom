<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DealershipController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMetaController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LossOrderController;
use App\Http\Controllers\ServiceImportController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskReportController;
use App\Http\Controllers\TaskFollowupController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\ExpenseRequestController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\ClockController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\OrganizationHierarchyController;
use App\Http\Controllers\HierarchyMapController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PackageKitController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\FSRReportController;
use App\Http\Controllers\FSRQuotationController;
use App\Http\Controllers\TaskTimerController;
use App\Http\Controllers\BrandSettingController;
use App\Http\Controllers\ClientImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\EmployeeImportController;
use App\Http\Controllers\LiveLocationController;
use App\Http\Controllers\ProductModelController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\LocationReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartImportController;
use App\Http\Controllers\SettlementsController;
use App\Http\Controllers\PerformanceReviewController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ServiceManagerApprovalController;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\GeneralReportController;
use App\Http\Controllers\JobVacancyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CallController;


Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

Route::get('/login', function () {
    return view('login');
});

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/login', [AuthController::class, 'login'])->name('login');


//routes with auth middleware
Route::middleware(['auth'])->group(function () {


    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index'); // Corrected web notification route
    Route::get('/notifications/recent', [NotificationController::class, 'getRecentNotifications'])->name('notifications.recent');
    Route::post('notifications/{notification}/hide', [NotificationController::class, 'hide'])->name('notifications.hide');
    Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::resource('notifications', NotificationController::class);
    Route::post('notifications/all', [NotificationController::class, 'allNotifications'])->name('notifications.all'); // Changed to POST for Datatables AJAX


    Route::get('teams/datatable', [TeamController::class, 'getDataTableData'])->name('teams.datatable');
    Route::resource('teams', TeamController::class)->names('teams');

    Route::get('/fsr-quotations-review', [FSRQuotationController::class, 'generalReviewIndex'])->name('fsr-quotations.review.index');
    Route::get('/fsr-quotations-review-data', [FSRQuotationController::class, 'getGeneralReviewQuotations'])->name('fsr-quotations.review.data');
    Route::put('/fsr-quotations/update-approved-quantities/{fsrReport}', [FSRQuotationController::class, 'updateApprovedQuantities'])->name('fsr-quotations.update-approved-quantities');
    Route::get('/interviews/export', [InterviewController::class, 'exportExcel'])->name('interviews.export');
    Route::get('/settlements/export', [SettlementsController::class, 'exportExcel'])->name('settlements.export');
    Route::get('/attendance/export', [AttendanceController::class, 'exportExcel'])->name('attendance.export');
    Route::get('/birthdays/export', [BirthdayController::class, 'exportExcel'])->name('birthdays.export');
    Route::get('/performance-review/export', [PerformanceReviewController::class, 'exportExcel'])->name('performance-review.export');
    Route::get('/job-vacancies-export-excel', [JobVacancyController::class, 'exportExcel'])->name('job-vacancies.export');

    Route::get('/fsr-quotations/{fsrReport}/export-pdf', [FSRQuotationController::class, 'exportPdf'])->name('fsr-quotations.export-pdf');

    Route::get('/task-continuation', [ServiceManagerApprovalController::class, 'index'])->name('task_continuation.index');
    Route::get('/tasks/global-timer-status', [TaskController::class, 'getGlobalTimerStatus'])->name('tasks.globalTimerStatus');
    Route::resource('tasks', TaskController::class);
    Route::get('location-reports', [LocationReportController::class, 'index'])->name('location-reports.index');
    Route::get('location-reports/visit-details/{visitId}', [LocationReportController::class, 'getVisitDetails'])->name('location-reports.visit-details');
    Route::post('/tasks/{task}/start', [TaskController::class, 'startTask'])->name('tasks.start');
    Route::post('/tasks/{task}/pause', [TaskController::class, 'pauseTask'])->name('tasks.pause');
    Route::post('/tasks/{task}/resume', [TaskController::class, 'resumeTask'])->name('tasks.resume');
    Route::post('/tasks/{task}/stop', [TaskController::class, 'stopTask'])->name('tasks.stop');
    Route::post('/tasks/{task}/update-status', [TaskController::class, 'updateTaskStatus'])->name('tasks.updateStatus');
    Route::post('/tasks/{task}/approve-early-action', [TaskController::class, 'approveEarlyAction'])->name('tasks.approveEarlyAction');
    Route::post('/tasks/{task}/followups', [TaskFollowupController::class, 'store'])->name('tasks.followups.store');
    Route::get('/tasks/{task}/followups', [TaskFollowupController::class, 'index'])->name('tasks.followups.index');
    Route::get('/tasks/{task}/followups/data', [TaskFollowupController::class, 'getDataTableData'])->name('tasks.followups.data');
    Route::get('/tasks/{task}/analytics', [TaskController::class, 'getAnalytics'])->name('tasks.analytics');
    Route::get('/tasks/{task}/overview', [TaskController::class, 'overview'])->name('tasks.overview');
    Route::get('/tasks/{task}/route-map', [TaskController::class, 'showRouteMap'])->name('tasks.route-map');
    Route::get('live-location', [LiveLocationController::class, 'index'])->name('live-location.index');
    Route::get('/live-location/employees', [LiveLocationController::class, 'getEmployeesWithVisits'])->name('live-location.employees');
    Route::delete('/track-visits/{visitId}', [LiveLocationController::class, 'deleteTrace'])->name('track-visits.delete');
    Route::get('/live-location/data', [LiveLocationController::class, 'getDataTableData'])->name('live-location.data'); // Renamed route
    Route::get('/live-location/stats', [LiveLocationController::class, 'getStats'])->name('live-location.stats'); // New route for stats
    Route::get('/live-location/export', [LiveLocationController::class, 'export'])->name('live-location.export'); // Route for Excel export
    Route::get('/live-location/export-pdf', [LiveLocationController::class, 'exportPdf'])->name('live-location.export-pdf'); // Route for PDF export
    Route::get('/track-visits/{visitId}/traces', [LiveLocationController::class, 'getVisitTraces'])->name('track-visits.traces');
    Route::get('/track-visits/{visitId}/distance', [LiveLocationController::class, 'getDistanceCovered'])->name('track-visits.distance');

    // New Timeline Route
    Route::get('timeline', [LiveLocationController::class, 'timeline'])->name('timeline.index');
    Route::get('/timeline/data', [LiveLocationController::class, 'getTimelineDataTableData'])->name('timeline.data'); // New route for Timeline DataTable
    Route::get('/timeline/export-pdf', [LiveLocationController::class, 'exportPdf'])->name('timeline.export-pdf'); // Route for PDF export
    Route::get('/timeline/export/manifest', [LiveLocationController::class, 'exportManifest'])->name('timeline.export.manifest');
    Route::post('/timeline/export/process', [LiveLocationController::class, 'exportProcess'])->name('timeline.export.process');
    Route::get('/timeline/export/process', function() {
        return response()->json(['message' => 'This route should be POST. If you see this, a GET request was made. Check if you were redirected (e.g. CSRF/Session expiry).'], 405);
    });
    Route::post('/timeline/export/cancel', [LiveLocationController::class, 'exportCancel'])->name('timeline.export.cancel');
    Route::get('/timeline/export/download', [LiveLocationController::class, 'exportDownload'])->name('timeline.export.download');
    Route::get('/tasks/{task}/followups/data', [TaskFollowupController::class, 'getDataTableData'])->name('tasks.followups.data');
    Route::get('/tasks/{task}/followups/{followup}/edit', [TaskFollowupController::class, 'edit'])->scopeBindings()->name('tasks.followups.edit');
    Route::put('/tasks/{task}/followups/{followup}', [TaskFollowupController::class, 'update'])->scopeBindings()->name('tasks.followups.update');
    Route::delete('/tasks/{task}/followups/{followup}', [TaskFollowupController::class, 'destroy'])->scopeBindings()->name('tasks.followups.destroy');
    Route::get('/tasks/{userId}/gps-data', [TaskFollowupController::class, 'getGpsData'])->name('tasks.gps.data');
    Route::get('/tasks/{task}/fsr/create', [TaskController::class, 'createFSR'])->name('tasks.fsr.create');
    Route::post('/tasks/{task}/fsr', [FSRReportController::class, 'store'])->name('tasks.fsr.store');
    Route::get('/fsr/{fsrReport}/edit', [FSRReportController::class, 'edit'])->name('fsr.edit');
    Route::get('/fsr/{fsrReport}', [FSRReportController::class, 'show'])->name('fsr.show');
    Route::put('/fsr/{fsrReport}', [FSRReportController::class, 'update'])->name('fsr.update');
    Route::post('/fsr/{fsrReport}/payments', [FSRReportController::class, 'addPayment'])->name('fsr.payments.store');
    Route::delete('/fsr-payments/{payment}', [FSRReportController::class, 'deletePayment'])->name('fsr.payments.destroy');
    Route::get('/fsr/{fsrReport}/images/{imageIndex}', [FSRReportController::class, 'showImage'])->name('fsr.showImage');
    Route::delete('/fsr/{fsrReport}/images/{imageIndex}', [FSRReportController::class, 'deleteImage'])->name('fsr.deleteImage');
    Route::get('/fsr-reports/{fsrReport}/details', [FSRReportController::class, 'getDetails'])->name('fsr-reports.details');
    Route::post('/clock/in', [ClockController::class, 'clockIn'])->name('clock.in');
    Route::post('/clock/out', [ClockController::class, 'clockOut'])->name('clock.out');

    Route::get('/organization', [OrganizationHierarchyController::class, 'index'])->name('organization.index');
    Route::get('/organization/hierarchy/embed', [OrganizationHierarchyController::class, 'embed'])->name('organization.embed');
    Route::get('/hierarchy-map', [HierarchyMapController::class, 'index'])->name('hierarchy-map.index');


    Route::get('/products/{product}/models', [ProductController::class, 'getProductModels']);

    Route::get('/product-models/{productModel}/model-series', [ProductModelController::class, 'getModelSeries']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/lead-statistics', [DashboardController::class, 'getLeadStatistics'])->name('dashboard.lead-statistics');
    Route::get('/dashboard/lead-source-breakdown', [DashboardController::class, 'getLeadSourceBreakdown'])->name('dashboard.lead-source-breakdown');
    Route::get('/dashboard/employee-lead-performance', [DashboardController::class, 'getEmployeeLeadPerformance'])->name('dashboard.employee-lead-performance');
    Route::get('/dashboard/top-clients', [DashboardController::class, 'getTopClients'])->name('dashboard.top-clients');
    Route::get('/dashboard/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('dashboard.recent-activities');
    Route::get('/dashboard/upcoming-events', [DashboardController::class, 'getUpcomingEvents'])->name('dashboard.upcoming-events');
    Route::get('/dashboard/all-leads', [DashboardController::class, 'getAllLeads'])->name('dashboard.all-leads');
    Route::get('/dashboard/this-months-sales', [DashboardController::class, 'getThisMonthsSales'])->name('dashboard.this-months-sales');
    Route::get('/dashboard/top-agents', [DashboardController::class, 'getTopAgents'])->name('dashboard.top-agents');
    Route::get('/dashboard/top-products-on-services', [DashboardController::class, 'getTopProductsOnServices'])->name('dashboard.top-products-on-services');
    Route::get('/dashboard/this-months-services', [DashboardController::class, 'getThisMonthsServices'])->name('dashboard.this-months-services');
    Route::get('/dashboard/top-clients-on-services', [DashboardController::class, 'getTopClientsOnServices'])->name('dashboard.top-clients-on-services');
    Route::get('/dashboard/top-service-engineers', [DashboardController::class, 'getTopServiceEngineers'])->name('dashboard.top-service-engineers');
    Route::get('/dashboard/sales-statistics', [DashboardController::class, 'getSalesStatistics'])->name('dashboard.sales-statistics');
    Route::get('/dashboard/lead-conversion-rate', [DashboardController::class, 'getLeadConversionRate'])->name('dashboard.lead-conversion-rate');
    Route::get('/dashboard/top-products-on-services', [DashboardController::class, 'getTopProductsOnServices'])->name('dashboard.top-products-on-services');
    Route::get('/dashboard/top-service-engineers', [DashboardController::class, 'getTopServiceEngineers'])->name('dashboard.top-service-engineers');
    Route::get('/dashboard/top-clients-on-services', [DashboardController::class, 'getTopClientsOnServices'])->name('dashboard.top-clients-on-services');
    Route::get('/dashboard/this-months-services', [DashboardController::class, 'getThisMonthsServices'])->name('dashboard.this-months-services');
    Route::get('/dashboard/top-contributors', [DashboardController::class, 'topContributors'])->name('dashboard.top-contributors');
    Route::get('/dashboard/parts-added-analytics', [DashboardController::class, 'getPartsAddedAnalytics'])->name('dashboard.parts-added-analytics');
    Route::get('/dashboard/service-statistics', [DashboardController::class, 'getServiceStatistics'])->name('dashboard.service-statistics');
    Route::get('/dashboard/parts-statistics', [DashboardController::class, 'getPartsStatistics'])->name('dashboard.parts-statistics');
    //roles routes 


    Route::get('/leave-requests/calendar-events', [LeaveRequestController::class, 'getCalendarEvents'])->name('leave-requests.calendar-events');
    Route::get('/leave-requests/balances', [LeaveRequestController::class, 'getLeaveBalances'])->name('leave-requests.balances');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::resource('leave-requests', LeaveRequestController::class)->names('leave-requests');
    Route::post('/leave-requests/{leaveRequest}/change-status', [LeaveRequestController::class, 'updateStatus'])->name('leave-requests.changeStatus');

    Route::get('/attendance/calendar', [AttendanceController::class, 'calendar'])->name('attendance.calendar');
    Route::get('/attendance/calendar-events', [AttendanceController::class, 'getCalendarEvents'])->name('attendance.calendar-events');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{employeeId}', [AttendanceController::class, 'show'])->name('attendance.show');

    Route::get('/birthdays', [BirthdayController::class, 'index'])->name('birthdays.index');
    Route::get('/birthdays/settings', [BirthdayController::class, 'settings'])->name('birthdays.settings');
    Route::post('/birthdays/settings', [BirthdayController::class, 'updateSettings'])->name('birthdays.settings.update');
    Route::get('/birthdays/logs', [BirthdayController::class, 'logs'])->name('birthdays.logs');

    Route::get('/performance-review', [PerformanceReviewController::class, 'index'])->name('performance-review.index');
    Route::post('/performance-review', [PerformanceReviewController::class, 'store'])->name('performance-review.store');
    Route::get('/performance-review/{id}', [PerformanceReviewController::class, 'show'])->name('performance-review.show');
    Route::get('/performance-review/{id}/edit', [PerformanceReviewController::class, 'edit'])->name('performance-review.edit');
    Route::put('/performance-review/{id}', [PerformanceReviewController::class, 'update'])->name('performance-review.update');
    Route::delete('/performance-review/{id}', [PerformanceReviewController::class, 'destroy'])->name('performance-review.destroy');
    Route::post('/performance-review/{id}/comments', [PerformanceReviewController::class, 'storeComment'])->name('performance-review.comments.store');
    Route::put('/performance-review/comments/{id}', [PerformanceReviewController::class, 'updateComment'])->name('performance-review.comments.update');
    Route::delete('/performance-review/comments/{id}', [PerformanceReviewController::class, 'destroyComment'])->name('performance-review.comments.destroy');
    Route::get('/performance-review/comments/{id}', [PerformanceReviewController::class, 'showComment'])->name('performance-review.comments.show');
    Route::get('/performance-review/{id}/export-pdf', [PerformanceReviewController::class, 'exportPdf'])->name('performance-review.export.pdf');
    Route::get('/performance-review/employee-history/{employeeId}', [PerformanceReviewController::class, 'getEmployeeHistory'])->name('performance-review.employee-history');
    Route::put('/performance-review/{performanceReview}/remove-report', [PerformanceReviewController::class, 'removeReport'])->name('performance-review.remove-report');

    Route::get('/expense-requests/export-pdf', [ExpenseRequestController::class, 'exportPdf'])->name('expense-requests.export-pdf');
    Route::get('/expense-requests/export-legacy-pdf', [ExpenseRequestController::class, 'exportLegacyPdf'])->name('expense-requests.export-legacy-pdf');
    Route::get('/expense-requests/view-legacy-report', [ExpenseRequestController::class, 'viewLegacyReport'])->name('expense-requests.view-legacy-report');
    Route::get('/expense-requests/calendar-events', [ExpenseRequestController::class, 'getCalendarEvents'])->name('expense-requests.calendar-events');
    Route::resource('expense-requests', ExpenseRequestController::class)->names('expense-requests');
    Route::post('expense-requests/{expense_request}/change-status', [ExpenseRequestController::class, 'changeStatus'])->name('expense-requests.changeStatus');
    Route::get('/expense-requests/search-employees', [ExpenseRequestController::class, 'searchEmployees'])->name('expense-requests.search-employees');
    Route::get('/document-requests/search-document-types', [DocumentRequestController::class, 'searchDocumentTypes'])->name('document-requests.searchDocumentTypes');
    Route::post('document-requests/{document_request}/change-status', [DocumentRequestController::class, 'changeStatus'])->name('document-requests.changeStatus');
    Route::get('/document-requests/calendar-events', [DocumentRequestController::class, 'getCalendarEvents'])->name('document-requests.calendar-events');
    Route::post('document-requests', [DocumentRequestController::class, 'store'])->name('document-requests.store');
    Route::resource('document-requests', DocumentRequestController::class)->names('document-requests');
    Route::get('/loan-requests/calendar-events', [LoanRequestController::class, 'getCalendarEvents'])->name('loan-requests.calendar-events');
    Route::resource('loan-requests', LoanRequestController::class)->names('loan-requests');
    Route::post('loan-requests/{loan_request}/change-status', [LoanRequestController::class, 'changeStatus'])->name('loan-requests.changeStatus');

    // Entries
    Route::get('/entries/search-clients', [LeadController::class, 'searchAllClients'])->name('entries.search-clients');
    Route::get('/entries', [ServiceController::class, 'index'])->name('entries.index');
    Route::get('/entries/datatable', [ServiceController::class, 'getDataTableData'])->name('entries.datatable');
    Route::get('/entries/clients', [ServiceController::class, 'getClients'])->name('entries.clients');
    Route::get('/entries/products', [ServiceController::class, 'getProducts'])->name('entries.products');
    Route::get('/entries/product-models', [ServiceController::class, 'getProductModels'])->name('entries.product-models');
    Route::get('/entries/model-series', [ServiceController::class, 'getModelSeries2'])->name('entries.model-series');
    Route::get('/entries/product-details', [ServiceController::class, 'getProductDetails'])->name('entries.product-details');
    Route::get('/entries/dealerships', [ServiceController::class, 'getDealerships'])->name('entries.dealerships');
    Route::get('/entries/service-engineers', [ServiceController::class, 'getServiceEngineers'])->name('entries.service-engineers');
    Route::post('entries/{entry}/assign-engineer', [ServiceController::class, 'assignEngineer'])->name('entries.assign-engineer');
    Route::get('entries/{service}/followups', [ServiceController::class, 'getServiceFollowups'])->name('entries.serviceFollowups');
    Route::get('entries/{service}/followups-page', [ServiceController::class, 'showFollowupsPage'])->name('entries.followups.index');
    Route::get('leads/{lead}/tasks', [LeadController::class, 'getLeadTasks'])->name('leads.tasks.index');
    Route::get('services/gps-data', [ServiceController::class, 'getGpsDataForService'])->name('services.gps.data');
    Route::get('services/{service}/followups-datatable', [ServiceController::class, 'getFollowupsDataTable'])->name('services.followups.datatable');
    Route::get('/entries/export', [ServiceController::class, 'export'])->name('entries.export');
    Route::get('/entries/search-clients', [LeadController::class, 'searchAllClients'])->name('entries.search-clients');
    Route::resource('entries', ServiceController::class)->except(['index'])->names('entries');

    // Database Backup Route
    Route::get('/db/backup', function () {
        Artisan::call('db:backup');
        $output = Artisan::output();
        return response()->json([
            'success' => true,
            'message' => 'Backup attempt finished.',
            'output' => $output
        ]);
    })->name('db.backup');

    // Package Kits
    Route::get('service-kits/datatable', [PackageKitController::class, 'getDataTableData'])->name('service-kits.datatable');
    Route::resource('service-kits', PackageKitController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('service-kits');

    // Parts
    Route::get('parts/datatable', [PartController::class, 'getDataTableData'])->name('parts.datatable');
    Route::get('parts/list', [PartController::class, 'getPartsList'])->name('parts.list');
    Route::get('parts/distinct-types', [PartController::class, 'getDistinctTypes'])->name('parts.distinct-types');

    Route::get('parts/search', [PartController::class, 'search'])->name('parts.search');
    Route::resource('parts', PartController::class)->names('parts');

    Route::get('/employees/search', [EmployeeController::class, 'search'])->name('employees.search');
    Route::get('/employees/searchEmployee', [EmployeeController::class, 'searchEmployee'])->name('employees.searchEmployee');
    Route::get('roles/merge-duplicates', [RoleController::class, 'mergeDuplicates'])->name('roles.merge-duplicates');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('roles/{role}/assign-permissions', [PermissionController::class, 'assignPermissions'])->name('roles.assign-permissions');
    Route::put('roles/{role}/assign-permissions', [PermissionController::class, 'savePermissions'])->name('roles.save-permissions');
    Route::post('roles/{role}/import-permissions', [PermissionController::class, 'importPermissions'])->name('roles.import-permissions');
    Route::get('roles/{role}/export-template', [PermissionController::class, 'exportPermissionsTemplate'])->name('roles.export-template');
    Route::post('/roles/toggle-permission', [PermissionController::class, 'togglePermission'])->name('roles.toggle-permission');

    // Permissions
    Route::resource('permissions', PermissionController::class)->names('permissions');

    Route::get('/brand-settings/{dealership_id?}', [BrandSettingController::class, 'index'])->name('brand-settings.index');
    Route::post('/brand-settings', [BrandSettingController::class, 'store'])->name('brand-settings.store');
    Route::post('/brand-settings/update-setting', [BrandSettingController::class, 'updateSetting'])->name('brand-settings.update-setting');

    // Organization Master Settings
    Route::get('/organization-settings', [App\Http\Controllers\OrganizationSettingController::class, 'index'])->name('organization-settings.index');
    Route::post('/organization-settings', [App\Http\Controllers\OrganizationSettingController::class, 'update'])->name('organization-settings.update');

    // Backups
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::get('backups/create', function () {
        return redirect()->route('backups.index');
    })->name('backups.create');
    Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
    Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::delete('backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('backups/upload', [BackupController::class, 'upload'])->name('backups.upload');

    //dealerships
    Route::resource('dealerships', DealershipController::class)->names('dealerships');

    //zones
    Route::resource('zones', ZoneController::class)->names('zones');
    Route::get('/districts/by-state/{stateName}', [DistrictController::class, 'getDistrictsByState'])->name('districts.byState');

    // Employee Export/Import
    Route::get('/employees/export', [EmployeeController::class, 'exportExcel'])->name('employees.export');
    Route::get('/employees/export-all-pdf', [App\Http\Controllers\EmployeeExportController::class, 'exportAll'])->name('employees.export-all.pdf');
    Route::get('/employees/{employee}/export-pdf', [App\Http\Controllers\EmployeeExportController::class, 'exportSingle'])->name('employees.export.pdf');
    Route::post('/employees/import', [EmployeeImportController::class, 'import'])->name('employees.import');
    Route::delete('/employees/import/{import_id}', [EmployeeImportController::class, 'undo'])->name('employees.import.undo');
    Route::get('/employees/import/recent', [EmployeeImportController::class, 'getRecentImports'])->name('employees.import.recent');
    Route::get('/employees/import/template', [EmployeeImportController::class, 'downloadTemplate'])->name('employees.import.template');
    Route::get('/employees/import/progress/{importId}', [EmployeeImportController::class, 'getImportProgress'])->name('employees.import.progress');

    Route::get('employees/get-department-managers', [EmployeeController::class, 'getDepartmentManagers'])->name('employees.getDepartmentManagers');
    Route::resource('employees', EmployeeController::class)->names('employees');
    Route::get('/interviews/job-vacancies/{jobVacancy}/applications', [InterviewController::class, 'getApplicationsByVacancy'])->name('interviews.applications.by-vacancy');
    Route::resource('interviews', InterviewController::class)->names('interviews');
    Route::get('/interviews/{interview}/export/pdf', [InterviewController::class, 'exportPdf'])->name('interviews.export.pdf');
    Route::post('interviews/{interview}/comments', [InterviewController::class, 'storeComment'])->name('interviews.storeComment');
    Route::get('interviews/comments/{comment}', [InterviewController::class, 'showComment'])->name('interviews.comments.show');
    Route::put('interviews/comments/{comment}', [InterviewController::class, 'updateComment'])->name('interviews.updateComment');
    Route::delete('interviews/comments/{comment}', [InterviewController::class, 'destroyComment'])->name('interviews.destroyComment');
    Route::get('/employees-brokers', [EmployeeController::class, 'getBrokers'])->name('employees.brokers');
    Route::get('/employees-all', [EmployeeController::class, 'getEmployees'])->name('employees.all');
    Route::get('/employees-assignable', [EmployeeController::class, 'getAssignableEmployees'])->name('employees.assignable');
    Route::post('/employees-store-broker', [EmployeeController::class, 'storeBroker'])->name('employees.store-broker');
    Route::get('/agents-data', [AgentController::class, 'getAgentsForDatatables'])->name('agents.data');
    Route::get('/agents/{id}', [AgentController::class, 'show'])->name('agents.details');
    Route::put('/agents/{id}', [AgentController::class, 'update'])->name('agents.update');

    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
    Route::resource('agents', AgentController::class)->except(['show', 'update']);

    Route::resource('categories', CategoryController::class)->names('categories');

    Route::resource('sub-categories', SubCategoryController::class)->names('sub-categories');

    Route::resource('taxes', TaxController::class)->names('taxes');
    Route::get('/job-vacancies/list', [JobVacancyController::class, 'list'])->name('job-vacancies.list');
    Route::get('/job-vacancies/{id}/analytics', [JobVacancyController::class, 'analytics'])->name('job-vacancies.analytics');
    Route::post('/job-vacancies/{id}/track-copy', [JobVacancyController::class, 'trackCopy'])->name('job-vacancies.track-copy');
    Route::post('/job-vacancies/applications/{id}/convert', [JobVacancyController::class, 'convertApplication'])->name('job-vacancies.applications.convert');
    Route::get('/job-vacancies/applications/{id}', [JobVacancyController::class, 'showApplication'])->name('job-vacancies.applications.show');
    Route::post('/job-vacancies/applications/{id}/update-status', [JobVacancyController::class, 'updateApplicationStatus'])->name('job-vacancies.applications.update-status');

    Route::resource('job-vacancies', JobVacancyController::class)->names('job-vacancies');

    Route::resource('products', ProductController::class)->except(['create', 'edit'])->names('products');
    Route::get('get-product-models-by-ids', [ProductController::class, 'getProductModelsByProductIds'])->name('products.get-models');
    Route::get('/product-models/all', [ProductController::class, 'getAllModels'])->name('product-models.all');
    Route::get('/products-list', [ProductController::class, 'getProductsList'])->name('products.list');

    // Product Import
    Route::post('/products/import', [ProductImportController::class, 'import'])->name('products.import');
    Route::get('/products/import/template', [ProductImportController::class, 'downloadTemplate'])->name('products.import.template');
    Route::get('/products/import/recent', [ProductImportController::class, 'getRecentImports'])->name('products.import.recent');
    Route::get('/products/import/progress/{importId}', [ProductImportController::class, 'getImportProgress'])->name('products.import.progress');
    Route::delete('/products/import/{importId}', [ProductImportController::class, 'undo'])->name('products.import.undo');

    // Part Import
    Route::post('/parts/import', [PartImportController::class, 'import'])->name('parts.import');
    Route::get('/parts/import/template', [PartImportController::class, 'downloadTemplate'])->name('parts.import.template');
    Route::get('/parts/import/recent', [PartImportController::class, 'getRecentImports'])->name('parts.import.recent');
    Route::get('/parts/import/progress/{importId}', [PartImportController::class, 'getImportProgress'])->name('parts.import.progress');
    Route::delete('/parts/import/{importId}', [PartImportController::class, 'undo'])->name('parts.import.undo');

    // About Products
    Route::get('/about-products', [ProductMetaController::class, 'index'])->name('about-products.index');
    Route::get('/about-products/categories-data', [ProductMetaController::class, 'getCategories'])->name('about-products.categories.data');
    Route::post('/about-products/categories-store', [ProductMetaController::class, 'storeCategory'])->name('about-products.categories.store');
    Route::get('/about-products/sub-categories-data', [ProductMetaController::class, 'getSubCategories'])->name('about-products.sub-categories.data');
    Route::post('/about-products/sub-categories-store', [ProductMetaController::class, 'storeSubCategory'])->name('about-products.sub-categories.store');
    Route::get('/about-products/taxes-data', [ProductMetaController::class, 'getTaxes'])->name('about-products.taxes.data');
    Route::post('/about-products/taxes-store', [ProductMetaController::class, 'storeTax'])->name('about-products.taxes.store');

    // Product Models
    Route::get('/about-products/product-models-data', [ProductMetaController::class, 'getProductModels'])->name('about-products.product-models.data');
    Route::post('/about-products/product-models-store', [ProductMetaController::class, 'storeProductModel'])->name('about-products.product-models.store');
    Route::get('/about-products/product-models/{productModel}/edit', [ProductMetaController::class, 'editProductModel'])->name('about-products.product-models.edit');
    Route::put('/about-products/product-models/{productModel}', [ProductMetaController::class, 'updateProductModel'])->name('about-products.product-models.update');
    Route::delete('/about-products/product-models/{productModel}', [ProductMetaController::class, 'deleteProductModel'])->name('about-products.product-models.destroy');
    Route::get('/about-products/product-models-by-product', [ProductMetaController::class, 'getModelsByProduct'])->name('about-products.product-models.by-product');
    Route::get('/products/by-dealership/{dealershipId}', [ProductMetaController::class, 'getProductsByDealership'])->name('products.byDealership');
    Route::get('/dealerships/brands', [ProductMetaController::class, 'getBrands'])->name('dealerships.brands');

    // Model Series
    Route::get('/about-products/model-series-data', [ProductMetaController::class, 'getModelSeries'])->name('about-products.model-series.data');
    Route::post('/about-products/model-series-store', [ProductMetaController::class, 'storeModelSeries'])->name('about-products.model-series.store');
    Route::get('/about-products/model-series/{modelSeries}/edit', [ProductMetaController::class, 'editModelSeries'])->name('about-products.model-series.edit');
    Route::put('/about-products/model-series/{modelSeries}', [ProductMetaController::class, 'updateModelSeries'])->name('about-products.model-series.update');
    Route::delete('/about-products/model-series/{modelSeries}', [ProductMetaController::class, 'deleteModelSeries'])->name('about-products.model-series.destroy');

    Route::get('get-categories-list', [CategoryController::class, 'getCategories'])->name('categories.get');

    //leads
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/assigned-data', [LeadController::class, 'getAssignedLeadsData'])->name('leads.assigned.data');
    Route::get('/leads/unassigned-data', [LeadController::class, 'getUnassignedLeadsData'])->name('leads.unassigned.data');
    Route::get('/leads/export-excel', [LeadController::class, 'exportExcel'])->name('leads.export-excel');
    Route::get('/leads/search-clients-by-phone', [LeadController::class, 'searchClientsByPhone'])->name('leads.search-clients-by-phone');
    Route::get('/leads/client-history/{client}', [LeadController::class, 'getClientHistory'])->name('leads.client-history');
    Route::get('/leads/{lead}/profile', [LeadController::class, 'profile'])->name('leads.profile');
    Route::put('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.updateStatus');
    Route::put('/leads/{lead}/update-chance-of-success', [LeadController::class, 'updateChanceOfSuccess'])->name('leads.updateChanceOfSuccess');
    Route::get('/lead-sources', [LeadController::class, 'indexLeadSources'])->name('lead-sources.index');
    Route::post('/lead-sources', [LeadController::class, 'storeLeadSource'])->name('lead-sources.store');
    Route::get('/lead-categories', [LeadController::class, 'indexLeadCategories'])->name('lead-categories.index');
    Route::post('/lead-categories', [LeadController::class, 'storeLeadCategory'])->name('lead-categories.store');
    Route::post('/products-store-dynamic', [LeadController::class, 'storeProduct'])->name('products.store-dynamic');
    Route::get('/agents-list', [AgentController::class, 'getAgentsList'])->name('agents.list');
    Route::post('/agents', [AgentController::class, 'store'])->name('agents.store'); // New route for storing non-employee agents
    Route::get('/leads/{lead}/task-overview/{task}', [LeadController::class, 'showTaskOverview'])->name('leads.tasks.overview');
    Route::get('/leads/{lead}/task-overview/{task}/export-excel', [LeadController::class, 'exportTaskOverviewExcel'])->name('leads.task-overview.export-excel');
    Route::get('/leads/{lead}/task-overview/{task}/export-pdf', [LeadController::class, 'exportTaskOverviewPdf'])->name('leads.task-overview.export-pdf');
    Route::get('/leads/{lead}/fsr-reports', [LeadController::class, 'getFsrReports'])->name('leads.fsr-reports');
    Route::get('/leads/{lead}/followups', [LeadController::class, 'getFollowups'])->name('leads.followups.index');
    Route::post('/leads/{lead}/followups', [LeadController::class, 'storeFollowup'])->name('leads.followups.store');
    Route::get('/leads/{lead}/followups/{followup}/edit', [LeadController::class, 'editFollowup'])->name('leads.followups.edit');
    Route::put('/leads/{lead}/followups/{followup}', [LeadController::class, 'updateFollowup'])->name('leads.followups.update');
    Route::delete('/leads/{lead}/followups/{followup}', [LeadController::class, 'deleteFollowup'])->scopeBindings()->name('leads.followups.destroy');
    Route::put('leads', [LeadController::class, 'update']);
    Route::resource('leads', LeadController::class)->names('leads');

    Route::put('/leads/{lead}/assign-employee', [LeadController::class, 'assignEmployee'])->name('leads.assignEmployee');
    Route::post('/leads/{lead}/revert-conversion', [LeadController::class, 'revertConversion'])->name('leads.revert-conversion');

    // Follow Ups
    Route::get('/followups', [LeadController::class, 'allFollowupsIndex'])->name('followups.index');
    Route::get('/followups/data', [LeadController::class, 'getAllFollowupsData'])->name('followups.data');



    Route::get('/clients/export-list-pdf', [ClientController::class, 'exportListPdf'])->name('clients.export.list.pdf');
    Route::get('/clients/{client}/export-excel', [ClientController::class, 'exportExcel'])->name('clients.export.excel');
    Route::get('/clients/{client}/export-pdf', [ClientController::class, 'exportPdf'])->name('clients.export.pdf');
    Route::resource('clients', ClientController::class)->names('clients');
    Route::post('/clients/{client}/revert-conversion', [ClientController::class, 'revertConversion'])->name('clients.revert-conversion');
    Route::delete('/clients/products/{clientProduct}', [ClientController::class, 'destroyProduct'])->name('clients.products.destroy');
    // Client Import
    Route::post('/clients/import', [ClientImportController::class, 'import'])->name('clients.import');
    Route::get('/clients/import/template', [ClientImportController::class, 'downloadTemplate'])->name('clients.import.template');
    Route::post('/clients/import-products', [ClientImportController::class, 'importProducts'])->name('clients.import-products');
    Route::get('/clients/import-products/template', [ClientImportController::class, 'downloadProductsTemplate'])->name('clients.import-products.template');
    Route::post('/clients/update-products', [ClientImportController::class, 'updateProducts'])->name('clients.update-products');
    Route::get('/clients/update-products/template', [ClientImportController::class, 'downloadUpdateProductsTemplate'])->name('clients.update-products.template');
    Route::get('/clients/import/recent', [ClientImportController::class, 'getRecentImports'])->name('clients.import.recent');
    Route::get('/clients/import/progress/{importId}', [ClientImportController::class, 'getImportProgress'])->name('clients.import.progress');
    Route::delete('/clients/import/{importId}', [ClientImportController::class, 'undo'])->name('clients.import.undo');
    Route::post('/leads/{lead}/convert', [ClientController::class, 'convertToClient'])->name('leads.convert');

    // Loss Orders
    Route::get('/loss-orders-data', [LossOrderController::class, 'getDataTableData'])->name('loss-orders.datatable');
    Route::get('/loss-orders/export-excel', [LossOrderController::class, 'exportExcel'])->name('loss-orders.export-excel');
    Route::get('/loss-orders/{lossOrder}/export-pdf', [LossOrderController::class, 'exportPdf'])->name('loss-orders.export.pdf');
    Route::resource('loss-orders', LossOrderController::class)->names('loss-orders');



    // Service Import
    Route::post('/services/import', [ServiceImportController::class, 'import'])->name('services.import');
    Route::delete('/services/import/{import_id}', [ServiceImportController::class, 'undo'])->name('services.import.undo');
    Route::get('/services/import/recent', [ServiceImportController::class, 'getRecentImports'])->name('services.import.recent');
    Route::get('/services/import/template', [ServiceImportController::class, 'downloadTemplate'])->name('services.import.template');
    Route::get('/services/import/progress/{importId}', [ServiceImportController::class, 'getImportProgress'])->name('services.import.progress');

    // Pipelines

    // Pipelines

    Route::get('/pipelines/datatable', [PipelineController::class, 'getDataTableData'])->name('pipelines.datatable');
    Route::get('/pipelines/export-excel', [PipelineController::class, 'exportExcel'])->name('pipelines.export-excel');
    Route::get('/pipelines/{id}/export-pdf', [PipelineController::class, 'exportPdfRow'])->name('pipelines.export-pdf-row');
    Route::get('/pipelines', [PipelineController::class, 'index'])->name('pipelines.index');



    Route::get('/my-profile', [ProfileController::class, 'index'])->name('my-profile');
    Route::post('/my-profile', [ProfileController::class, 'update'])->name('my-profile.update');

    Route::post('tasks/{task}/start-timer', [TaskTimerController::class, 'start']);
    Route::post('tasks/{task}/pause-timer', [TaskTimerController::class, 'pause']);
    Route::post('tasks/{task}/resume-timer', [TaskTimerController::class, 'resume'])->name('tasks.resume-timer');

    Route::post('settlements/remarks/{settlementRemark}/update-filled', [SettlementsController::class, 'updateFilledStatus'])->name('settlements.remarks.updateFilledStatus');
    Route::post('settlements/remarks/{settlementRemark}/upload-file', [SettlementsController::class, 'uploadRemarkFile'])->name('settlements.remarks.uploadFile');
    Route::get('settlements/notifications', [SettlementsController::class, 'notifications'])->name('settlements.notifications');
    Route::get('settlements/{settlement}/edit-data', [SettlementsController::class, 'editData'])->name('settlements.editData');
    Route::get('settlements/data', [SettlementsController::class, 'getDataForDatatable'])->name('settlements.data');
    Route::resource('settlements', SettlementsController::class)->names('settlements');
    Route::get('settlements/{settlement}/export-pdf', [SettlementsController::class, 'exportPdf'])->name('settlements.exportPdf');

    Route::post('settlements/{settlement}/remarks', [SettlementsController::class, 'storeRemark'])->name('settlements.storeRemark');
    Route::post('/settlements/{settlement}/department-remarks/update-filled', [SettlementsController::class, 'updateDepartmentRemarksStatus'])->name('settlements.department.remarks.updateFilledStatus');

    Route::get('/general-reports', [GeneralReportController::class, 'index'])->name('general-reports.index');
    Route::get('/general-reports/data', [GeneralReportController::class, 'getData'])->name('general-reports.data');
    Route::get('/general-reports/export-excel', [GeneralReportController::class, 'exportExcel'])->name('general-reports.export-excel');
    Route::get('/general-reports/export-pdf', [GeneralReportController::class, 'exportPdf'])->name('general-reports.export-pdf');

    Route::get('/task-reports', [TaskReportController::class, 'index'])->name('task-reports.index');
    Route::get('/task-reports/data', [TaskReportController::class, 'getData'])->name('task-reports.data');
    Route::get('/task-reports/export-excel', [TaskReportController::class, 'exportExcel'])->name('task-reports.export-excel');
    Route::get('/task-reports/{task}', [TaskReportController::class, 'show'])->name('task-reports.show');
    Route::get('/task-reports/{task}/export-fsr-pdf', [TaskReportController::class, 'exportFsrPdf'])->name('task-reports.export-fsr-pdf');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/{key}', [SettingController::class, 'getSetting'])->name('settings.get');

    // Smart Search
    Route::get('/search/pages', [SearchController::class, 'searchPages'])->name('search.pages');


    Route::post('/update-setting', [BrandSettingController::class, 'updateSetting'])->name('web.brand-settings.update-setting');
    Route::get('/brand-settings/{dealership_id}/settings', [BrandSettingController::class, 'getDealershipSettings'])->name('web.brand-settings.get-settings');

    // Call Centre Routes
    Route::post('/calls/toggle-availability', [CallController::class, 'toggleAvailability'])->name('calls.toggle-availability');
    Route::get('/calls/agent-status', [CallController::class, 'getAgentStatus'])->name('calls.agent-status');
    Route::post('/calls/initiate-mock', [CallController::class, 'initiateIncomingCall'])->name('calls.initiate-mock');
    Route::post('/calls/{call}/answer', [CallController::class, 'answerCall'])->name('calls.answer');
    Route::post('/calls/{call}/end', [CallController::class, 'endCall'])->name('calls.end');
});

// Terminal (Protected by static password in controller)

Route::get('/docs', function () {
    return view('swagger_ui');
});

Route::get('/docs/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});

Route::get('/debug-interview', [InterviewController::class, 'debugInterview']);

Route::get('/migrate', function () {
    Artisan::call('migrate', ['--force' => true]);
    return response()->json([
        'message' => 'Database migrated successfully!',
        'output' => Artisan::output()
    ]);
});

Route::get('/db-seed', function () {
    Artisan::call('db:seed', ['--force' => true]);
    return response()->json([
        'message' => 'Database seeded successfully!',
        'output' => Artisan::output()
    ]);
});

Route::get('/migrate-fresh', function () {
    Artisan::call('migrate:fresh', ['--force' => true]);
    return response()->json([
        'message' => 'Database refreshed successfully!',
        'output' => Artisan::output()
    ]);
});

Route::get('/fresh-seed', function () {
    Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
    return response()->json([
        'message' => 'Database refreshed and seeded successfully!',
        'output' => Artisan::output()
    ]);
});

Route::get('/l5-swagger/generate', function () {
    Artisan::call('l5-swagger:generate');
    return response()->json([
        'message' => 'Swagger documentation generated successfully!',
        'output' => Artisan::output()
    ]);
});
