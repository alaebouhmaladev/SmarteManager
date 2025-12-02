<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Product;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    |   Return dahsboard overview data
    |--------------------------------------------------------------------------
    */
    public function overview(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $monthParam = $request->query('month') ?? Carbon::now()->format('Y-m');
        [$year, $month] = explode('-', $monthParam);

        // ---------------- EMPLOYEES ----------------
        $totalEmployees = Employee::where('status', 'active')->count();

        // ---------------- ATTENDANCE  ----------------
        $today = Carbon::today()->toDateString();

        $todayCheckins = Attendance::whereDate('work_date', $today)->count();

        $currentlyPresent = Attendance::whereDate('work_date', $today)
            ->whereNull('check_out')
            ->count();

        // ---------------- INVENTORY ----------------
        $inventoryValue = Product::select(
                DB::raw('SUM(current_stock * average_cost) as total_value')
            )
            ->value('total_value') ?? 0;

        $lowStockCount = Product::whereColumn('current_stock', '<=', 'min_stock')
            ->count();

        // ---------------- EXPENSES ----------------
        $monthlyExpenses = Expense::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        // ---------------- PAYROLL ----------------
        $attendances = Attendance::with('employee')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get();

        $payrollByEmployee = $attendances->groupBy('employee_id')->map(function ($items) {
            $employee   = $items->first()->employee;
            $totalHours = $items->sum('total_hours');
            $rate       = $employee->hourly_rate ?? 0;

            return [
                'employee_id'   => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'total_hours'   => round($totalHours, 2),
                'hourly_rate'   => (float) $rate,
                'salary'        => round($totalHours * $rate, 2),
            ];
        })->values();

        $totalPayroll = $payrollByEmployee->sum('salary');

        // ---------------- RESPONSE ----------------
        return response()->json([
            'month' => $monthParam,

            'employees' => [
                'total_active' => $totalEmployees,
            ],

            'attendance' => [
                'today_date'       => $today,
                'today_checkins'   => $todayCheckins,
                'currently_present'=> $currentlyPresent,
            ],

            'inventory' => [
                'total_value'     => round($inventoryValue, 2),
                'low_stock_count' => $lowStockCount,
            ],

            'expenses' => [
                'total_this_month' => round($monthlyExpenses, 2),
            ],

            'payroll' => [
                'total_this_month' => round($totalPayroll, 2),
                'employees'        => $payrollByEmployee,
            ],
        ]);
    }
}
