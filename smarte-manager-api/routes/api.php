<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| Public routes (no auth)
|--------------------------------------------------------------------------
*/

Route::post('auth/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected routes (auth:sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // ----------------------------- AUTH -----------------------------
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);


    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    | - Admin + Manager: list users, create users
    | - Manager can only create STAFF (handled in controller)
    | - Admin: update/delete users
    */

    Route::middleware('role:admin,manager')->group(function () {
        Route::get('users', [UserManagementController::class, 'index']);
        Route::post('users', [UserManagementController::class, 'store']);
        Route::get('users/{user}', [UserManagementController::class, 'show']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::put('users/{user}', [UserManagementController::class, 'update']);
        Route::patch('users/{user}', [UserManagementController::class, 'update']);
        Route::delete('users/{user}', [UserManagementController::class, 'destroy']);
    });


    /*
    |--------------------------------------------------------------------------
    | BUSINESS MODULES (Admin + Manager)
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | EMPLOYEES
        |--------------------------------------------------------------------------
        */
        Route::apiResource('employees', EmployeeController::class);


        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE
        |--------------------------------------------------------------------------
        */
        Route::get('attendances', [AttendanceController::class, 'index']);
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

        Route::get('attendances/employee/{employee}', [AttendanceController::class, 'byEmployee']);
        Route::get('attendances/daily', [AttendanceController::class, 'daily']);
        Route::get('attendances/monthly-summary', [AttendanceController::class, 'monthlySummary']);
        Route::get('attendances/export-csv', [AttendanceController::class, 'exportMonthlyCsv']);


        /*
        |--------------------------------------------------------------------------
        | PAYROLL
        |--------------------------------------------------------------------------
        */
        Route::get('payroll/monthly', [PayrollController::class, 'monthly']);
        Route::get('payroll/export-csv', [PayrollController::class, 'exportMonthlyCsv']);
        // NEW: single employee payslip
        Route::get('payroll/employee/{employee}', [PayrollController::class, 'employeeMonthly']);


        /*
        |--------------------------------------------------------------------------
        | INVENTORY
        |--------------------------------------------------------------------------
        */
        Route::get('inventory/overview', [InventoryController::class, 'overview']);
        Route::get('inventory/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('inventory/valuation', [InventoryController::class, 'valuation']);
        Route::get('inventory/average-cost', [InventoryController::class, 'averageCost']);
        Route::get('inventory/product/{product}/history', [InventoryController::class, 'productHistory']);


        /*
        |--------------------------------------------------------------------------
        | SUPPLIERS
        |--------------------------------------------------------------------------
        */
        Route::apiResource('suppliers', SupplierController::class);
        // Supplier overview
        Route::get('suppliers/{supplier}/overview', [SupplierController::class, 'overview']);


        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */
        Route::apiResource('products', ProductController::class);


        /*
        |--------------------------------------------------------------------------
        | STOCK MOVEMENTS
        |--------------------------------------------------------------------------
        */
        Route::apiResource('stock-movements', StockMovementController::class)
            ->only(['index', 'store']);


        /*
        |--------------------------------------------------------------------------
        | EXPENSES
        |--------------------------------------------------------------------------
        */
        Route::apiResource('expenses', ExpenseController::class)
            ->only(['index', 'store']);

        // Expense features
        Route::get('expenses/monthly-summary', [ExpenseController::class, 'monthlySummary']);
        Route::get('expenses/by-supplier/{supplier}', [ExpenseController::class, 'bySupplier']);
        Route::get('expenses/export-csv', [ExpenseController::class, 'exportMonthlyCsv']);


        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */
        Route::get('dashboard/overview', [DashboardController::class, 'overview']);
    });


    /*
    |--------------------------------------------------------------------------
    | STAFF ROUTES
    |--------------------------------------------------------------------------
    | For ROLE = staff, each user sees only their own attendance.
    */

    Route::middleware('role:staff')->group(function () {
        Route::get('my/attendances', [AttendanceController::class, 'myAttendances']);
    });
});
