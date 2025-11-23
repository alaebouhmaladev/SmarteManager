<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Monthly payroll calculation
     * GET /api/payroll/monthly?month=2025-11
     */
    public function monthly(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $monthParam = $request->query('month') ?? Carbon::now()->format('Y-m');
        [$year, $month] = explode('-', $monthParam);

        $records = Attendance::with('employee')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get();

        $summary = $records->groupBy('employee_id')->map(function ($items) {
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

        $totalPayroll = $summary->sum('salary');

        return response()->json([
            'month'         => $monthParam,
            'total_payroll' => $totalPayroll,
            'employees'     => $summary,
        ]);
    }

    /**
     * Detailed payslip for one employee
     * GET /api/payroll/employee/{employee}?month=2025-11
     */
    public function employeeMonthly(Request $request, $employeeId)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $monthParam = $request->query('month') ?? Carbon::now()->format('Y-m');
        [$year, $month] = explode('-', $monthParam);

        $employee = Employee::findOrFail($employeeId);

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->orderBy('work_date')
            ->get();

        $totalHours = $attendances->sum('total_hours');
        $rate       = $employee->hourly_rate ?? 0;
        $salary     = round($totalHours * $rate, 2);

        return response()->json([
            'month'    => $monthParam,
            'employee' => [
                'id'          => $employee->id,
                'first_name'  => $employee->first_name,
                'last_name'   => $employee->last_name,
                'hourly_rate' => (float) $rate,
            ],
            'total_hours' => round($totalHours, 2),
            'salary'      => $salary,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Export payroll as CSV
     * GET /api/payroll/export-csv?month=2025-11
     */
    public function exportMonthlyCsv(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $monthParam = $request->query('month') ?? Carbon::now()->format('Y-m');
        [$year, $month] = explode('-', $monthParam);

        $records = Attendance::with('employee')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get();

        $summary = $records->groupBy('employee_id')->map(function ($items) {
            $employee   = $items->first()->employee;
            $totalHours = $items->sum('total_hours');
            $rate       = $employee->hourly_rate ?? 0;

            return [
                'employee'      => $employee->first_name . ' ' . $employee->last_name,
                'total_hours'   => round($totalHours, 2),
                'hourly_rate'   => (float) $rate,
                'salary'        => round($totalHours * $rate, 2),
            ];
        })->values();

        $filename = "payroll_{$monthParam}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($summary) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Employee', 'Total Hours', 'Hourly Rate', 'Salary']);

            foreach ($summary as $row) {
                fputcsv($handle, [
                    $row['employee'],
                    $row['total_hours'],
                    $row['hourly_rate'],
                    $row['salary'],
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
