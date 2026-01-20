<?php

// Updated routes for your web.php file - replace the existing payroll routes section

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\DisciplinaryController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\RetirementController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\BiometricController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BankDetailsController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GradeLevelController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ComprehensiveReportController;
use App\Http\Controllers\PendingEmployeeChangeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProbationController;
use App\Http\Controllers\PensionComputationController;
use App\Http\Controllers\PensionerController;
use App\Http\Controllers\PendingPensionerChangeController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Simple test route
// Route::get('/test-page', [TestController::class, 'index'])->name('test.page');

// Test route for the new report system
Route::get('/test-reports', function () {
    // Initialize the service
    $reportService = new App\Services\ComprehensiveReportService();

    try {
        // Test generating a few reports to make sure they work
        $masterReport = $reportService->generateEmployeeMasterReport();
        $directoryReport = $reportService->generateEmployeeDirectoryReport();
        $statusReport = $reportService->generateEmployeeStatusReport();

        return response()->json([
            'status' => 'success',
            'message' => 'All reports generated successfully!',
            'reports_generated' => [
                'employee_master' => count($masterReport['employees']),
                'employee_directory' => count($directoryReport['employees']),
                'employee_status' => count($statusReport['employees_by_status'])
            ]
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
})->name('test.reports');

Route::middleware(['auth'])->group(function () {
    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('reports', ReportController::class);
    Route::post('/reports/generate', [ReportController::class, 'generateEmployeeReport'])->name('reports.generate');
    Route::post('/reports/bulk-generate', [ReportController::class, 'bulkGenerate'])->name('reports.bulk_generate');
    Route::get('/reports/{id}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::get('/reports/export', [ReportController::class, 'exportFiltered'])->name('reports.export');

    // New Comprehensive Report System
    Route::get('/comprehensive-reports', [ComprehensiveReportController::class, 'index'])->name('reports.comprehensive.index');
    Route::get('/comprehensive-reports/create', [ComprehensiveReportController::class, 'create'])->name('reports.comprehensive.create');
    Route::post('/comprehensive-reports/generate', [ComprehensiveReportController::class, 'generateReport'])->name('reports.comprehensive.generate');
    Route::get('/comprehensive-reports/{id}', [ComprehensiveReportController::class, 'show'])->name('reports.comprehensive.show');
    Route::get('/comprehensive-reports/{id}/download', [ComprehensiveReportController::class, 'download'])->name('reports.comprehensive.download');

    // Profile - accessible to authenticated users based on permission
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile')->middleware('permission:view_profile');
    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password')->middleware('permission:change_password');
    Route::put('/profile/change-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password')->middleware('permission:change_password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('permission:change_password');

    // Employee Management - HR and Admin only
    Route::middleware('permission:manage_employees')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('/employees/export/pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export.pdf');
        Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
        Route::get('/employees/{employeeId}/export', [EmployeeController::class, 'exportSingle'])->name('employee.export');
        Route::post('/employees/import', [EmployeeController::class, 'importEmployees'])->name('employees.import');
        Route::get('/employees/export/filtered', [EmployeeController::class, 'exportFiltered'])->name('employees.export.filtered');
        Route::resource('roles', RoleController::class);

    });

    // Bank Details Management
    Route::middleware('permission:manage_bank_details')->group(function () {
        Route::get('/bank-details', [BankDetailsController::class, 'index'])->name('bank-details.index');
        Route::get('/bank-details/{employeeId}', [BankDetailsController::class, 'show'])->name('bank-details.show');
        Route::put('/bank-details/{employeeId}', [BankDetailsController::class, 'update'])->name('bank-details.update');
        Route::post('/bank-details/search', [BankDetailsController::class, 'search'])->name('bank-details.search');
    });

    // Leave Management Routes
    // Keep only the store and update methods under manage_employees middleware for admin functions
    Route::post('leaves/{leave}/approve', [\App\Http\Controllers\LeaveController::class, 'approve'])->name('leaves.approve');


    // Probation Management - HR and Admin only
    Route::middleware('permission:view_probation')->group(function () {
        Route::prefix('probation')->name('probation.')->group(function () {
            Route::get('/', [ProbationController::class, 'index'])->name('index');
            Route::get('/{employee}', [ProbationController::class, 'show'])->name('show');
            Route::post('/{employee}/approve', [ProbationController::class, 'approve'])->name('approve')->middleware('permission:approve_probation');
            Route::post('/{employee}/reject', [ProbationController::class, 'reject'])->name('reject')->middleware('permission:reject_probation');
            Route::post('/{employee}/start', [ProbationController::class, 'startProbation'])->name('start')->middleware('permission:manage_probation');
            Route::post('/{employee}/extend', [ProbationController::class, 'extend'])->name('extend')->middleware('permission:manage_probation');
        });
    });

    // Routes for employee to manage their own leaves - outside manage_employees middleware
    Route::get('/my-leaves', [\App\Http\Controllers\LeaveController::class, 'myLeaves'])->name('leaves.my');
    Route::get('/my-leaves/create', [\App\Http\Controllers\LeaveController::class, 'createMyLeave'])->name('leaves.create.my');
    Route::post('/my-leaves', [\App\Http\Controllers\LeaveController::class, 'storeMyLeave'])->name('leaves.store.my');

    // Main leave resource with specific permissions
    Route::get('/leaves', [\App\Http\Controllers\LeaveController::class, 'index'])->name('leaves.index')->middleware('permission:view_leaves');
    Route::get('/leaves/create', [\App\Http\Controllers\LeaveController::class, 'create'])->name('leaves.create')->middleware('permission:manage_leaves');
    Route::post('/leaves', [\App\Http\Controllers\LeaveController::class, 'store'])->name('leaves.store')->middleware('permission:manage_leaves');
    Route::get('/leaves/{leave}', [\App\Http\Controllers\LeaveController::class, 'show'])->name('leaves.show')->middleware('permission:view_leaves');
    Route::get('/leaves/{leave}/edit', [\App\Http\Controllers\LeaveController::class, 'edit'])->name('leaves.edit')->middleware('permission:manage_leaves');
    Route::put('/leaves/{leave}', [\App\Http\Controllers\LeaveController::class, 'update'])->name('leaves.update')->middleware('permission:manage_leaves');
    Route::delete('/leaves/{leave}', [\App\Http\Controllers\LeaveController::class, 'destroy'])->name('leaves.destroy')->middleware('permission:manage_leaves');

    // AJAX routes for location dropdowns (outside permission middleware so they can be accessed by anyone creating employees)
    Route::get('/employees/lgas-by-state', [EmployeeController::class, 'getLgasByState'])->name('employees.lgas-by-state');
    Route::get('/employees/wards-by-lga', [EmployeeController::class, 'getWardsByLga'])->name('employees.wards-by-lga');
    Route::get('/employees/ranks-by-grade-level', [EmployeeController::class, 'getRanksByGradeLevel'])->name('employees.ranks-by-grade-level');

    // AJAX route for getting all salary scales
    Route::get('/api/salary-scales', function () {
        $salaryScales = \App\Models\SalaryScale::select('id', 'acronym', 'full_name')->get();
        return response()->json($salaryScales);
    })->name('salary-scales.all.ajax');

    // AJAX route for getting a single salary scale by ID
    Route::get('/api/salary-scales/{salaryScaleId}', function ($salaryScaleId) {
        $salaryScale = \App\Models\SalaryScale::select('id', 'acronym', 'full_name')->find($salaryScaleId);
        return response()->json($salaryScale);
    })->name('salary-scales.single.ajax');

    // AJAX route for salary scale grade levels
    Route::get('/api/salary-scales/{salaryScaleId}/grade-levels', function ($salaryScaleId) {
        $gradeLevels = \App\Models\GradeLevel::where('salary_scale_id', $salaryScaleId)->get();
        return response()->json($gradeLevels);
    })->name('salary-scales.grade-levels.ajax');

    Route::get('/api/salary-scales/{salaryScaleId}/grade-levels/{gradeLevelName}/steps', [\App\Http\Controllers\SalaryScaleController::class, 'getStepsForGradeLevel'])->name('salary-scales.grade-levels.steps.ajax');

    // AJAX route for getting grade levels with their steps
    Route::get('/api/grade-levels/with-steps', function () {
        $gradeLevels = \App\Models\GradeLevel::with('steps')->get();
        return response()->json($gradeLevels);
    })->name('grade-levels.with-steps.ajax');

    Route::get('/salary-scales/{salaryScaleId}/retirement-info', function ($salaryScaleId) {
        $salaryScale = \App\Models\SalaryScale::find($salaryScaleId);
        if ($salaryScale) {
            return response()->json([
                'max_retirement_age' => (int)$salaryScale->max_retirement_age,
                'max_years_of_service' => (int)$salaryScale->max_years_of_service,
            ]);
        }
        return response()->json(null, 404);
    })->name('salary-scales.retirement-info');

    // Employee Viewing - HR, Admin, and Bursary
    Route::middleware('permission:view_employees')->group(function () {
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    });

    // Department Management - HR and Admin only
    Route::middleware('permission:manage_departments')->group(function () {
        Route::resource('departments', DepartmentController::class);
    });

    // Biometric Management - HR and Admin only
    Route::middleware('permission:manage_biometrics')->group(function () {
        Route::resource('biometrics', BiometricController::class);
    });

    // Disciplinary Management - HR and Admin only
    Route::middleware('permission:manage_disciplinary')->group(function () {
        Route::get('/disciplinary/employees/search', [DisciplinaryController::class, 'searchEmployees'])->name('disciplinary.employees.search');
        Route::post('/session/store-selected-employee', [DisciplinaryController::class, 'storeSelectedEmployee'])->name('disciplinary.store.selected.employee');
        Route::resource('disciplinary', DisciplinaryController::class);
    });

    // SMS Management - HR and Admin only
    Route::middleware('permission:manage_sms')->group(function () {
        Route::resource('sms', SmsController::class);
    });

    // FIXED: User Management - Admin only (Enhanced with bulk creation)
    Route::middleware('permission:manage_employees')->group(function () {
        // Main user resource routes

        // FIXED: Additional user management routes with correct HTTP methods
        Route::resource('users', UserController::class)->except([
            'show'
        ]);
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
       Route::post('/users/bulk-create', [UserController::class, 'bulkCreateUsers'])->name('users.bulk-create');
       Route::get('/users/employees-without-users', [UserController::class, 'showEmployeesWithoutUsers'])->name('users.employees-without-users');

    });

    // Retirement Management - HR and Admin only
    Route::middleware('permission:manage_retirement')->group(function () {
        Route::get('/retirements/retired', [RetirementController::class, 'retiredList'])->name('retirements.retired');
        Route::get('/retirements/retired-statuses', [RetirementController::class, 'getAllRetiredStatuses'])->name('retirements.retired-statuses');
        Route::resource('retirements', RetirementController::class);
        Route::post('/employees/{employee}/retire', [RetirementController::class, 'retire'])->name('retirement.retire');
        Route::get('/retirements', [RetirementController::class, 'index'])->name('retirements.index');
        Route::get('/retirements/{retirement}', [RetirementController::class, 'show'])->name('retirements.show');
        Route::get('/retirements/{employeeId}/pension-compute', [RetirementController::class, 'redirectToPensionComputation'])->name('retirements.pension-compute');
    });

    // Pensioner Management - HR and Admin only
    Route::middleware('permission:manage_pensioners')->group(function () {
        Route::resource('pensioners', PensionerController::class);
        Route::get('/pensioners', [PensionerController::class, 'index'])->name('pensioners.index');
        Route::get('/pensioners/create', [PensionerController::class, 'create'])->name('pensioners.create');
        Route::get('/pensioners/{pensioner}', [PensionerController::class, 'show'])->name('pensioners.show');
        Route::get('/pensioners/{pensioner}/edit', [PensionerController::class, 'edit'])->name('pensioners.edit');
        Route::post('/pensioners/move-retired', [PensionerController::class, 'moveRetiredToPensioners'])->name('pensioners.move-retired');
        Route::get('/pensioners/type/{type}', [PensionerController::class, 'getPensionersByType'])->name('pensioners.by-type');
        Route::post('/pensioners/{pensioner}/mark-gratuity-paid', [PensionerController::class, 'markGratuityPaid'])->name('pensioners.mark-gratuity-paid');
        Route::post('/pensioners/{pensioner}/mark-deceased', [PensionerController::class, 'markDeceased'])->name('pensioners.mark-deceased');

        // Pending Pensioner Changes - Approval System
        Route::middleware('permission:view_pensioner_changes|approve_pensioner_changes')->group(function () {
            Route::get('/pending-pensioner-changes', [PendingPensionerChangeController::class, 'index'])->name('pending-pensioner-changes.index');
            Route::get('/pending-pensioner-changes/{pendingChange}', [PendingPensionerChangeController::class, 'show'])->name('pending-pensioner-changes.show');
        });

        Route::middleware('permission:approve_pensioner_changes')->group(function () {
            Route::post('/pending-pensioner-changes/{pendingChange}/approve', [PendingPensionerChangeController::class, 'approve'])->name('pending-pensioner-changes.approve');
            Route::post('/pending-pensioner-changes/{pendingChange}/reject', [PendingPensionerChangeController::class, 'reject'])->name('pending-pensioner-changes.reject');
        });

        // Pension Computation
        Route::get('/pension/computation/create', [PensionComputationController::class, 'create'])->name('pension.create');
        Route::post('/pension/computation', [PensionComputationController::class, 'compute'])->name('pension.compute');
        Route::post('/pension/computation/store', [PensionComputationController::class, 'store'])->name('pension.store');
        Route::get('/pension/computation/steps', [PensionComputationController::class, 'getStepsByGL'])->name('pension.steps');
        Route::get('/pension/computation/employee-details', [PensionComputationController::class, 'getEmployeeDetails'])->name('pension.employee-details');
    });

    // Retirement Viewing - HR, Admin, and Bursary
    Route::middleware('permission:view_retirement')->group(function () {

    });

    // Payroll Management - Bursary and Admin only (ENHANCED WITH SEARCH & FILTERS)
        Route::middleware('permission:manage_payroll')->group(function () {

            // IMPORTANT: Place specific routes BEFORE dynamic routes to avoid conflicts

            // Payroll generation and export (with filters) - MUST BE FIRST
            Route::post('/payroll/generate', [PayrollController::class, 'generatePayroll'])->name('payroll.generate');
            Route::get('/payroll/export', [PayrollController::class, 'exportPayroll'])->name('payroll.export');

            // AJAX and Search routes - MUST BE BEFORE DYNAMIC ROUTES
            Route::get('/payroll/api/search', [PayrollController::class, 'search'])->name('payroll.search');
            Route::get('/payroll/api/statistics', [PayrollController::class, 'getStatistics'])->name('payroll.statistics');

            // Bulk operations - MUST BE BEFORE DYNAMIC ROUTES
            Route::post('/payroll/bulk/update-status', [PayrollController::class, 'bulkUpdateStatus'])->name('payroll.bulk_update_status');

            // Bulk workflow operations
            Route::post('/payroll/bulk/send-for-review', [PayrollController::class, 'bulkSendForReview'])->name('payroll.bulk_send_for_review');
            Route::post('/payroll/bulk/mark-as-reviewed', [PayrollController::class, 'bulkMarkAsReviewed'])->name('payroll.bulk_mark_as_reviewed');
            Route::post('/payroll/bulk/send-for-approval', [PayrollController::class, 'bulkSendForApproval'])->name('payroll.bulk_send_for_approval');
            Route::post('/payroll/bulk/final-approve', [PayrollController::class, 'bulkFinalApprove'])->name('payroll.bulk_final_approve');
            Route::post('/payroll/bulk/request-delete', [PayrollController::class, 'bulkRequestDelete'])->name('payroll.bulk_request_delete');
            Route::post('/payroll/bulk/approve-delete', [PayrollController::class, 'bulkApproveDelete'])->name('payroll.bulk_approve_delete');

            // Individual workflow operations
            Route::post('/payroll/{payrollId}/send-for-review', [PayrollController::class, 'sendForReview'])->name('payroll.send-for-review');
            Route::post('/payroll/{payrollId}/mark-as-reviewed', [PayrollController::class, 'markAsReviewed'])->name('payroll.mark-as-reviewed');
            Route::post('/payroll/{payrollId}/send-for-approval', [PayrollController::class, 'sendForApproval'])->name('payroll.send-for-approval');
            Route::post('/payroll/{payrollId}/final-approve', [PayrollController::class, 'finalApprove'])->name('payroll.final-approve');

            // Detailed payroll information route
            Route::get('/payroll/{payrollId}/detailed', [PayrollController::class, 'getDetailedPayroll'])->name('payroll.detailed');

            // Employee-specific deductions and additions - BEFORE DYNAMIC ROUTES
            Route::get('/payroll/employee/{employeeId}/deductions', [PayrollController::class, 'showDeductions'])->name('payroll.deductions.show');
            Route::get('/payroll/employee/{employeeId}/additions', [PayrollController::class, 'showAdditions'])->name('payroll.additions.show');
            Route::post('/payroll/employee/{employeeId}/deductions', [PayrollController::class, 'storeDeduction'])->name('payroll.deductions.store');
            Route::post('/payroll/employee/{employeeId}/additions', [PayrollController::class, 'storeAddition'])->name('payroll.additions.store');
            Route::delete('/payroll/employee/{employeeId}/deductions/{deductionId}', [PayrollController::class, 'destroyDeduction'])->name('payroll.deductions.destroy');
            Route::delete('/payroll/employee/{employeeId}/additions/{additionId}', [PayrollController::class, 'destroyAddition'])->name('payroll.additions.destroy');

            // Manage All Adjustments
            Route::get('/payroll/adjustments', [PayrollController::class, 'manageAllAdjustments'])->name('payroll.adjustments.manage');

            Route::get('/payroll/additions', [PayrollController::class, 'additions'])->name('payroll.additions');
            Route::get('/payroll/test', function() {
                return response()->json(['status' => 'success', 'message' => 'Payroll test route is working!']);
            })->name('payroll.test');
            Route::post('/payroll/additions/bulk', [PayrollController::class, 'storeBulkAdditions'])->name('payroll.additions.bulk.store');
            Route::get('/payroll/deductions', [PayrollController::class, 'deductions'])->name('payroll.deductions');
            Route::post('/payroll/deductions/bulk', [PayrollController::class, 'storeBulkDeductions'])->name('payroll.deductions.bulk.store');

            // Main payroll resource routes - DYNAMIC ROUTES COME LAST
            Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
            Route::get('/payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
            Route::post('/payroll', [PayrollController::class, 'store'])->name('payroll.store');
            Route::get('/payroll/{payrollId}', [PayrollController::class, 'show'])->name('payroll.show');
            Route::get('/payroll/{payrollId}/edit', [PayrollController::class, 'edit'])->name('payroll.edit');
            Route::put('/payroll/{payrollId}', [PayrollController::class, 'update'])->name('payroll.update');
            Route::delete('/payroll/{payrollId}', [PayrollController::class, 'destroy'])->name('payroll.destroy');


            // Individual payroll actions - AFTER MAIN ROUTES
            Route::get('/payroll/{payrollId}/payslip', [PayrollController::class, 'generatePaySlip'])->name('payroll.payslip');
            Route::post('/payroll/{payrollId}/recalculate', [PayrollController::class, 'recalculate'])->name('payroll.recalculate');
            Route::post('/payroll/{payrollId}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
            Route::post('/payroll/{payrollId}/reject', [PayrollController::class, 'reject'])->name('payroll.reject');



            // Salary Scales
            Route::resource('salary-scales', \App\Http\Controllers\SalaryScaleController::class);
            Route::get('/salary-scales/{salaryScale}/grade-levels', [\App\Http\Controllers\SalaryScaleController::class, 'showGradeLevels'])->name('salary-scales.grade-levels');

            // Grade Levels within Salary Scales
            Route::prefix('salary-scales/{salaryScale}')->group(function () {
                Route::get('/grade-levels/create', [\App\Http\Controllers\SalaryScale\GradeLevelController::class, 'create'])->name('salary-scales.grade-levels.create');
                Route::post('/grade-levels', [\App\Http\Controllers\SalaryScale\GradeLevelController::class, 'store'])->name('salary-scales.grade-levels.store');
                Route::get('/grade-levels/{gradeLevel}/edit', [\App\Http\Controllers\SalaryScale\GradeLevelController::class, 'edit'])->name('salary-scales.grade-levels.edit');
                Route::put('/grade-levels/{gradeLevel}', [\App\Http\Controllers\SalaryScale\GradeLevelController::class, 'update'])->name('salary-scales.grade-levels.update');
                Route::delete('/grade-levels/{gradeLevel}', [\App\Http\Controllers\SalaryScale\GradeLevelController::class, 'destroy'])->name('salary-scales.grade-levels.destroy');
            });

            // Deduction Types
            Route::resource('deduction-types', \App\Http\Controllers\DeductionTypeController::class);

            // Addition Types
            Route::resource('addition-types', \App\Http\Controllers\AdditionTypeController::class);

            // Loans Management
            Route::resource('loans', \App\Http\Controllers\LoanController::class)->except(['edit', 'update']);
            Route::get('/loans/employees/{employee}/additions', [LoanController::class, 'getAdditionsForEmployee'])->name('loans.employee.additions');
            Route::get('/loans/employees/{employee}/salary', [LoanController::class, 'getEmployeeSalary'])->name('loans.employee.salary');
            Route::get('/loans/types/{loanType}/principal-amount', [LoanController::class, 'getLoanTypePrincipalAmount'])->name('loans.type.principal-amount');

            // Grade Level Adjustments
            Route::get('grade-levels/{gradeLevel}/adjustments', [\App\Http\Controllers\GradeLevelAdjustmentController::class, 'index'])->name('grade-levels.adjustments.index');
            Route::post('grade-levels/{gradeLevel}/adjustments', [\App\Http\Controllers\GradeLevelAdjustmentController::class, 'store'])->name('grade-levels.adjustments.store');
            Route::delete('grade-levels/{gradeLevel}/adjustments/{adjustmentId}', [\App\Http\Controllers\GradeLevelAdjustmentController::class, 'destroy'])->name('grade-levels.adjustments.destroy');

            // Step Management Routes
            Route::prefix('salary-scales/{salaryScale}/grade-levels/{gradeLevel}')->group(function () {
                // Steps
                Route::get('/steps/create', [\App\Http\Controllers\SalaryScale\StepController::class, 'create'])->name('salary-scales.grade-levels.steps.create');
                Route::post('/steps', [\App\Http\Controllers\SalaryScale\StepController::class, 'store'])->name('salary-scales.grade-levels.steps.store');
                Route::get('/steps/{step}/edit', [\App\Http\Controllers\SalaryScale\StepController::class, 'edit'])->name('salary-scales.grade-levels.steps.edit');
                Route::put('/steps/{step}', [\App\Http\Controllers\SalaryScale\StepController::class, 'update'])->name('salary-scales.grade-levels.steps.update');
                Route::delete('/steps/{step}', [\App\Http\Controllers\SalaryScale\StepController::class, 'destroy'])->name('salary-scales.grade-levels.steps.destroy');
            });
        });

        // Promotion and Demotion Management
        Route::middleware('permission:view_promotions')->group(function () {
            Route::resource('promotions', \App\Http\Controllers\PromotionController::class)->except(['edit', 'update']);
            Route::get('/promotions/employees/search', [\App\Http\Controllers\PromotionController::class, 'searchEmployees'])->name('promotions.employees.search');
            Route::get('/employees/{employeeId}', [\App\Http\Controllers\PromotionController::class, 'getEmployeeDetails'])->name('employees.details');
            Route::post('/promotions/{promotion}/approve', [\App\Http\Controllers\PromotionController::class, 'approve'])->name('promotions.approve');
            Route::post('/promotions/{promotion}/reject', [\App\Http\Controllers\PromotionController::class, 'reject'])->name('promotions.reject');
        });

    // Pending Employee Changes - Admin only
    Route::middleware('permission:approve_employee_changes')->group(function () {
        Route::resource('pending-changes', PendingEmployeeChangeController::class);
        Route::post('/pending-changes/{pendingChange}/approve', [PendingEmployeeChangeController::class, 'approve'])->name('pending-changes.approve');
        Route::post('/pending-changes/{pendingChange}/reject', [PendingEmployeeChangeController::class, 'reject'])->name('pending-changes.reject');
    });

    // Audit Trails - Admin only
    Route::middleware('permission:view_audit_logs')->group(function () {
        Route::resource('audit-trails', AuditTrailController::class);
        Route::get('/audit-logs', [AuditTrailController::class, 'index'])->name('audit.index');
    });



});

Route::get('/clear-cache', function() {
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    return "Cache cleared!";
});

// Employee CSV Export Route
Route::get('/employees/export/csv', [EmployeeController::class, 'exportCsv'])->name('employees.export.csv')->middleware('permission:manage_employees');