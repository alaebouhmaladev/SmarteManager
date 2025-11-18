<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function overview()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        // Total employees
        $totalEmployees = Employee::count();

        // Total hours worked this month
        $totalHoursThisMonth = Attendance::whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->sum('total_hours');

        // Total expenses this month
        $totalExpensesThisMonth = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Stock value = current_stock * average_cost
        $stockValue = Product::select(DB::raw('SUM(current_stock * average_cost) as total'))
            ->value('total') ?? 0;

        // Products under minimum stock
        $lowStockProducts = Product::whereColumn('current_stock', '<', 'min_stock')
            ->get(['id', 'name', 'current_stock', 'min_stock']);

        return response()->json([
            'total_employees'        => $totalEmployees,
            'total_hours_this_month' => round($totalHoursThisMonth, 2),
            'total_expenses_this_month' => round($totalExpensesThisMonth, 2),
            'stock_value'            => round($stockValue, 2),
            'low_stock_products'     => $lowStockProducts,
            'period' => [
                'start' => $startOfMonth,
                'end'   => $endOfMonth,
            ],
        ]);
    }
}
