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
use App\Http\Controllers\UserManagementController; // 👈 NEW

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

    // Auth
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // 🔹 User management (roles: admin / manager / staff)
    Route::get('users', [UserManagementController::class, 'index']);   // list users
    Route::post('users', [UserManagementController::class, 'store']);  // create user

    // Employees
    Route::apiResource('employees', EmployeeController::class);

    // Attendance
    Route::get('attendances', [AttendanceController::class, 'index']);
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']); // 👈 fixed name

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);

    // Products
    Route::apiResource('products', ProductController::class);

    // Stock movements (index + store)
    Route::apiResource('stock-movements', StockMovementController::class)
        ->only(['index', 'store']);

    // Expenses (index + store)
    Route::apiResource('expenses', ExpenseController::class)
        ->only(['index', 'store']);

    // Dashboard
    Route::get('dashboard/overview', [DashboardController::class, 'overview']);
});
