<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EmployeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes that require authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/me', [AuthController::class, 'me']);
    
    // Employee Management routes - mirroring web routes
    // Using permissions to mirror web route permissions
    Route::middleware('api.permission:view_employees')->group(function () {
        Route::apiResource('employees', EmployeeController::class)->except(['store', 'update', 'destroy']);
        Route::get('/employees/export/pdf', [EmployeeController::class, 'exportPdf']);
        Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel']);
        Route::get('/employees/{employeeId}/export', [EmployeeController::class, 'exportSingle']);
        Route::get('/employees/export/filtered', [EmployeeController::class, 'exportFiltered']);
        Route::get('/employees/lgas-by-state', [EmployeeController::class, 'getLgasByState']);
        Route::get('/employees/wards-by-lga', [EmployeeController::class, 'getWardsByLga']);
        Route::get('/employees/ranks-by-grade-level', [EmployeeController::class, 'getRanksByGradeLevel']);
        
        // Additional API routes
        Route::get('/api/salary-scales', [EmployeeController::class, 'getAllSalaryScales']);
        Route::get('/api/salary-scales/{salaryScaleId}', [EmployeeController::class, 'getSingleSalaryScale']);
        Route::get('/api/salary-scales/{salaryScaleId}/grade-levels', [EmployeeController::class, 'getGradeLevelsBySalaryScale']);
        Route::get('/salary-scales/{salaryScaleId}/retirement-info', [EmployeeController::class, 'getRetirementInfo']);
        Route::get('/api/grade-levels/with-steps', [EmployeeController::class, 'getGradeLevelsWithSteps']);
    });
    
    Route::middleware('api.permission:create_employees')->group(function () {
        Route::apiResource('employees', EmployeeController::class)->only(['store']);
        Route::post('/employees/import', [EmployeeController::class, 'importEmployees']);
    });
    
    Route::middleware('api.permission:edit_employees')->group(function () {
        Route::apiResource('employees', EmployeeController::class)->only(['update']);
    });
    
    Route::middleware('api.permission:delete_employees')->group(function () {
        Route::apiResource('employees', EmployeeController::class)->only(['destroy']);
    });
    
    Route::middleware(['api.permission:view_employees', 'api.permission:create_employees'])->group(function () {
        Route::apiResource('employees', EmployeeController::class)->only(['create']);
    });
    
    Route::middleware(['api.permission:view_employees', 'api.permission:edit_employees'])->group(function () {
        Route::apiResource('employees', EmployeeController::class)->only(['edit']);
    });
});