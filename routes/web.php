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
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PensionerController;
use App\Http\Controllers\GradeLevelController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PendingEmployeeChangeController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Simple test route
// Route::get('/test-page', [TestController::class, 'index'])->name('test.page');

Route::middleware(['auth'])->group(function () {
    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('reports', ReportController::class);
    Route::post('/reports/generate', [ReportController::class, 'generateEmployeeReport'])->name('reports.generate');
    Route::post('/reports/bulk-generate', [ReportController::class, 'bulkGenerate'])->name('reports.bulk_generate');
    Route::get('/reports/{id}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::get('/reports/export', [ReportController::class, 'exportFiltered'])->name('reports.export');
    
    // Profile - accessible to authenticated users based on permission
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile')->middleware('permission:view_profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('permission:edit_profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('permission:edit_profile');
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
    
    // AJAX routes for location dropdowns (outside permission middleware so they can be accessed by anyone creating employees)
    Route::get('/employees/lgas-by-state', [EmployeeController::class, 'getLgasByState'])->name('employees.lgas-by-state');
    Route::get('/employees/wards-by-lga', [EmployeeController::class, 'getWardsByLga'])->name('employees.wards-by-lga');
    Route::get('/employees/ranks-by-grade-level', [EmployeeController::class, 'getRanksByGradeLevel'])->name('employees.ranks-by-grade-level');
    
    // AJAX route for salary scale grade levels
    Route::get('/api/salary-scales/{salaryScaleId}/grade-levels', function ($salaryScaleId) {
        $gradeLevels = \App\Models\GradeLevel::where('salary_scale_id', $salaryScaleId)->get();
        return response()->json($gradeLevels);
    })->name('salary-scales.grade-levels.ajax');

    Route::get('/api/salary-scales/{salaryScaleId}/grade-levels/{gradeLevelName}/steps', [\App\Http\Controllers\SalaryScaleController::class, 'getStepsForGradeLevel'])->name('salary-scales.grade-levels.steps.ajax');

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
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
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
    });

    // Retirement Viewing - HR, Admin, and Bursary
    Route::middleware('permission:view_retirement')->group(function () {
       
    });

    // Pensioner Management - HR and Admin only
    Route::middleware('permission:manage_employees')->group(function () {
        Route::resource('pensioners', PensionerController::class);
        Route::post('pensioners/{pensioner_id}/status', [PensionerController::class, 'updateStatus'])->name('pensioners.updateStatus');
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