<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DashboardController;

Route::middleware('api')->group(function () {

    /**
     *  EMPLOYEES (Full CRUD)
     */
    Route::apiResource('employees', EmployeeController::class);

    /**
     *  ATTENDANCE SYSTEM
     */
    Route::get('attendances', [AttendanceController::class, 'index']);
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

    /**
     *  SUPPLIERS (Full CRUD)
     */
    Route::apiResource('suppliers', SupplierController::class);

    /**
     *  PRODUCTS (Full CRUD)
     */
    Route::apiResource('products', ProductController::class);

    /**
     *  STOCK MOVEMENTS (Only index + store)
     *  No update/delete/show needed
     */
    Route::apiResource('stock-movements', StockMovementController::class)
        ->only(['index', 'store']);

    /**
     *  EXPENSES (Only index + store)
     */
    Route::apiResource('expenses', ExpenseController::class)
        ->only(['index', 'store']);

    /**
     *  DASHBOARD OVERVIEW
     *  For PFA Presentation & Frontend Dashboard
     */
    Route::get('dashboard/overview', [DashboardController::class, 'overview']);
});
