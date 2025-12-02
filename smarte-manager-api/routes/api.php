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
| Public routes no auth needed 
|--------------------------------------------------------------------------
*/
Route::post('auth/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected routes auth:sanctum needed
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | auth routes for logout and data for current user loged
    |--------------------------------------------------------------------------
    */
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Users Managemnt Routes - admin and manager - 
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('users', [UserManagementController::class, 'index']);
        Route::post('users', [UserManagementController::class, 'store']);
        Route::get('users/{user}', [UserManagementController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Users Managemnt Routes - admin -  
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {
        Route::put('users/{user}', [UserManagementController::class, 'update']);
        Route::patch('users/{user}', [UserManagementController::class, 'update']);
        Route::delete('users/{user}', [UserManagementController::class, 'destroy']);
    });

    /*
    |-------------------------------------------------------------------------
    | Dashboard Overview
    |-------------------------------------------------------------------------
    */
    Route::get('dashboard/overview', [DashboardController::class, 'overview']);

    /*
    |--------------------------------------------------------------------------
    | HR Operations (Employees, Attendance, Payroll)
    |--------------------------------------------------------------------------
    */
    Route::apiResource('employees', EmployeeController::class);

    Route::get('attendances', [AttendanceController::class, 'index']);
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

    Route::get('payroll/monthly', [PayrollController::class, 'monthly']);
    
    Route::get('payroll/employee/{employee}', [PayrollController::class, 'employeeMonthly']);

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */
    Route::get('inventory/overview', [InventoryController::class, 'overview']);
    Route::get('inventory/low-stock', [InventoryController::class, 'lowStock']);
    Route::get('inventory/valuation', [InventoryController::class, 'valuation']);
    Route::get('inventory/average-cost', [InventoryController::class, 'averageCost']);
    Route::get('inventory/product/{product}/history', [InventoryController::class, 'productHistory']);

    /*
    |--------------------------------------------------------------------------
    | Suppliers
    |--------------------------------------------------------------------------
    */
    Route::get('suppliers', [SupplierController::class, 'index']);
    Route::post('suppliers', [SupplierController::class, 'store']);
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show']);
    Route::put('suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy']);

    Route::get('suppliers/{supplier}/overview', [SupplierController::class, 'overview']);

    /*
    |--------------------------------------------------------------------------
    | Products 
    |--------------------------------------------------------------------------
    */
    Route::apiResource('products', ProductController::class);

    /*
    |--------------------------------------------------------------------------
    | Stock Movements
    |--------------------------------------------------------------------------
    */
    Route::apiResource('stock-movements', StockMovementController::class)
        ->only(['index', 'store']);

    /*
    |--------------------------------------------------------------------------
    | Expenses
    |--------------------------------------------------------------------------
    */
    Route::get('expenses', [ExpenseController::class, 'index']);
    Route::post('expenses', [ExpenseController::class, 'store']);

    Route::get('expenses/monthly-summary', [ExpenseController::class, 'monthlySummary']);
    Route::get('expenses/by-supplier/{supplier}', [ExpenseController::class, 'bySupplier']);
    Route::get('expenses/export-csv', [ExpenseController::class, 'exportMonthlyCsv']);

    Route::get('expenses/{expense}', [ExpenseController::class, 'show']);
    Route::put('expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy']);
});
