<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdvanceController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware('auth')->group(function () {
    // Dashboard (accessible to all authenticated users)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employees - requires employees.view permission
    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::middleware('permission:employees.create')->group(function () {
            Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
            Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        });
        Route::middleware('permission:employees.edit')->group(function () {
            Route::post('/employees/bulk-update-rates', [EmployeeController::class, 'bulkUpdateRates'])->name('employees.bulk-update-rates');
            Route::post('/employees/bulk-update-status', [EmployeeController::class, 'bulkUpdateStatus'])->name('employees.bulk-update-status');
            Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
            Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        });
        Route::middleware('permission:employees.delete')->delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    });

    // Attendance - requires attendances.view permission
    Route::middleware('permission:attendances.view')->group(function () {
        Route::get('/attendance/daily', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/daily/data', [AttendanceController::class, 'data'])->name('attendance.data');
        Route::get('/attendance/calendar/data', [AttendanceController::class, 'calendarData'])->name('attendance.calendar');
        Route::middleware('permission:attendances.create')->group(function () {
            Route::post('/attendance/bulk-save', [AttendanceController::class, 'store'])->name('attendance.store');
            Route::post('/attendance/bulk-action', [AttendanceController::class, 'bulkAction'])->name('attendance.bulk-action');
        });
    });

    // Advances - requires advances.view permission
    Route::middleware('permission:advances.view')->group(function () {
        Route::get('/advances', [AdvanceController::class, 'index'])->name('advances.index');
        Route::middleware('permission:advances.create')->group(function () {
            Route::get('/advances/create', [AdvanceController::class, 'create'])->name('advances.create');
            Route::post('/advances', [AdvanceController::class, 'store'])->name('advances.store');
        });
        Route::middleware('permission:advances.edit')->group(function () {
            Route::get('/advances/{advance}/edit', [AdvanceController::class, 'edit'])->name('advances.edit');
            Route::put('/advances/{advance}', [AdvanceController::class, 'update'])->name('advances.update');
        });
        Route::middleware('permission:advances.delete')->delete('/advances/{advance}', [AdvanceController::class, 'destroy'])->name('advances.destroy');
        Route::get('/advances/{advance}', [AdvanceController::class, 'show'])->name('advances.show');
    });

    // Salaries - requires salaries.view permission
    Route::middleware('permission:salaries.view')->group(function () {
        Route::get('/salaries', [SalaryController::class, 'index'])->name('salaries.index');
        Route::middleware('permission:salaries.generate')->group(function () {
            Route::get('/salaries/create', [SalaryController::class, 'create'])->name('salaries.create');
            Route::post('/salaries', [SalaryController::class, 'store'])->name('salaries.store');
        });
        Route::middleware('permission:salaries.lock')->group(function () {
            Route::get('/salaries/{salarySheet}/edit', [SalaryController::class, 'edit'])->name('salaries.edit');
            Route::put('/salaries/{salarySheet}', [SalaryController::class, 'update'])->name('salaries.update');
        });
        Route::middleware('permission:salaries.regenerate')->patch('/salaries/{salarySheet}/regenerate', [SalaryController::class, 'regenerate'])->name('salaries.regenerate');
        Route::middleware('permission:salaries.regenerate')->patch('/salaries/{salarySheet}/apply-advances', [SalaryController::class, 'applyAdvances'])->name('salaries.apply-advances');
        Route::get('/salaries/{salarySheet}', [SalaryController::class, 'show'])->name('salaries.show');
    });

    // Payments - requires payments.view permission
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{salarySheet}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/employees/{employee}/payment-history', [PaymentController::class, 'employeePayments'])->name('payments.employee-history');
        Route::middleware('permission:payments.create')->group(function () {
            Route::get('/payments/{salarySheet}/pay', [PaymentController::class, 'pay'])->name('payments.pay');
            Route::post('/payments/{salarySheet}', [PaymentController::class, 'store'])->name('payments.store');
        });
        Route::middleware('permission:payments.delete')->delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // Reports - requires reports.view permission
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/monthly-hajira', [ReportController::class, 'monthlyHajira'])->name('reports.monthly-hajira');
        Route::get('/reports/overtime', [ReportController::class, 'overtime'])->name('reports.overtime');
        Route::get('/reports/absent', [ReportController::class, 'absent'])->name('reports.absent');
        Route::get('/reports/advance', [ReportController::class, 'advance'])->name('reports.advance');
        Route::get('/reports/salary-sheet', [ReportController::class, 'salarySheet'])->name('reports.salary-sheet');
        Route::get('/reports/payment', [ReportController::class, 'payment'])->name('reports.payment');
        Route::get('/reports/employee-ledger', [ReportController::class, 'employeeLedger'])->name('reports.employee-ledger');
        Route::get('/reports/accounts-summary', [ReportController::class, 'accountsSummary'])->name('reports.accounts-summary');
    });

    // Admin - User management
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users',                  [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create',           [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/users',                 [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit',      [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}',           [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',        [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/password',[\App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    // Change own password (all authenticated users)
    Route::get('/profile/password',   fn() => view('profile.change-password'))->name('profile.change-password');
    Route::patch('/profile/password', [\App\Http\Controllers\UserController::class, 'changePassword'])->name('profile.change-password.update');

    // Admin - Activity Logs
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    });

    // Admin - Settings
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::delete('/settings/data/{type}', [SettingController::class, 'clearData'])->name('settings.clear-data');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
