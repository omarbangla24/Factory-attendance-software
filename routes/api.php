<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController as ApiEmployeeController;
use App\Http\Controllers\Api\AttendanceController as ApiAttendanceController;
use App\Http\Controllers\Api\AdvanceController as ApiAdvanceController;
use App\Http\Controllers\Api\SalaryController as ApiSalaryController;
use App\Http\Controllers\Api\PaymentController as ApiPaymentController;
use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard endpoints (all users)
    Route::get('/dashboard/summary', [ApiDashboardController::class, 'summary']);

    // Employee endpoints - requires employees.view
    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/employees', [ApiEmployeeController::class, 'index']);
        Route::get('/employees/active', [ApiEmployeeController::class, 'active']);
        Route::get('/employees/{employee}', [ApiEmployeeController::class, 'show']);
        Route::get('/employees/{employee}/advance-balance', [ApiAdvanceController::class, 'employeeBalance']);
        
        Route::middleware('permission:employees.create')->post('/employees', [ApiEmployeeController::class, 'store']);
        Route::middleware('permission:employees.edit')->group(function () {
            Route::put('/employees/{employee}', [ApiEmployeeController::class, 'update']);
            Route::post('/employees/bulk-update-rates', [ApiEmployeeController::class, 'bulkUpdateRates']);
            Route::post('/employees/bulk-update-status', [ApiEmployeeController::class, 'bulkUpdateStatus']);
        });
        Route::middleware('permission:employees.delete')->delete('/employees/{employee}', [ApiEmployeeController::class, 'destroy']);
    });

    // Attendance endpoints - requires attendances.view
    Route::middleware('permission:attendances.view')->group(function () {
        Route::get('/attendances', [ApiAttendanceController::class, 'index']);
        Route::get('/attendances/summary', [ApiAttendanceController::class, 'summary']);
        
        Route::middleware('permission:attendances.create')->post('/attendances/bulk-save', [ApiAttendanceController::class, 'bulkSave']);
    });

    // Advance endpoints - requires advances.view
    Route::middleware('permission:advances.view')->group(function () {
        Route::get('/advances', [ApiAdvanceController::class, 'index']);
        Route::get('/advances/{advance}', [ApiAdvanceController::class, 'show']);
        
        Route::middleware('permission:advances.create')->post('/advances', [ApiAdvanceController::class, 'store']);
        Route::middleware('permission:advances.edit')->put('/advances/{advance}', [ApiAdvanceController::class, 'update']);
        Route::middleware('permission:advances.delete')->delete('/advances/{advance}', [ApiAdvanceController::class, 'destroy']);
    });

    // Salary endpoints - requires salaries.view
    Route::middleware('permission:salaries.view')->group(function () {
        Route::get('/salaries', [ApiSalaryController::class, 'index']);
        Route::get('/salaries/{id}', [ApiSalaryController::class, 'show']);
        
        Route::middleware('permission:salaries.generate')->post('/salaries', [ApiSalaryController::class, 'store']);
        Route::middleware('permission:salaries.lock')->put('/salaries/{id}', [ApiSalaryController::class, 'update']);
        Route::middleware('permission:salaries.lock')->post('/salaries/{id}/lock', [ApiSalaryController::class, 'lock']);
        Route::middleware('permission:salaries.regenerate')->post('/salaries/{id}/regenerate', [ApiSalaryController::class, 'regenerate']);
    });

    // Payment endpoints - requires payments.view
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments', [ApiPaymentController::class, 'index']);
        Route::get('/salaries/{id}/payments', [ApiPaymentController::class, 'salaryPayments']);
        Route::get('/employees/{id}/payments', [ApiPaymentController::class, 'employeePayments']);
        
        Route::middleware('permission:payments.create')->post('/payments', [ApiPaymentController::class, 'store']);
        Route::middleware('permission:payments.delete')->delete('/payments/{id}', [ApiPaymentController::class, 'destroy']);
    });

    // Report endpoints - requires reports.view
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports/monthly-hajira', [ApiReportController::class, 'monthlyHajira']);
        Route::get('/reports/overtime', [ApiReportController::class, 'overtime']);
        Route::get('/reports/absent', [ApiReportController::class, 'absent']);
        Route::get('/reports/advance', [ApiReportController::class, 'advance']);
        Route::get('/reports/salary-sheet', [ApiReportController::class, 'salarySheet']);
        Route::get('/reports/payment', [ApiReportController::class, 'payment']);
        Route::get('/reports/employee-ledger/{employee_id}', [ApiReportController::class, 'employeeLedger']);
        Route::get('/reports/accounts-summary', [ApiReportController::class, 'accountsSummary']);
    });
});
