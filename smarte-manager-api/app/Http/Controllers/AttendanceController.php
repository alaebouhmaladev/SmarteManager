<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Employee check-in
    |--------------------------------------------------------------------------
    */
    public function checkIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Check if multiple check-ins
        |--------------------------------------------------------------------------
        */
        $existing = Attendance::where('employee_id', $request->employee_id)
            ->whereNull('check_out')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Employee already checked in. Checkout first.'
            ], 400);
        }

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'work_date'   => Carbon::now()->toDateString(),
            'check_in'    => Carbon::now(),
        ]);

        return response()->json($attendance);
    }

    /*
    |--------------------------------------------------------------------------
    | Employee check-out operation 
    |--------------------------------------------------------------------------
    */
    public function checkOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $attendance = Attendance::where('employee_id', $request->employee_id)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'No active check-in found for this employee.'
            ], 404);
        }

        $attendance->check_out = Carbon::now();
        $attendance->total_hours = Carbon::parse($attendance->check_in)
            ->diffInMinutes(Carbon::now()) / 60;

        $attendance->save();

        return response()->json($attendance);
    }

    /*
    |--------------------------------------------------------------------------
    | List of all attendance from latest to first
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return response()->json(
            Attendance::with('employee')
                ->orderBy('id', 'DESC')
                ->get()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance history by employee
    |--------------------------------------------------------------------------
    */
    public function byEmployee(Request $request, $employeeId)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $records = Attendance::with('employee')
            ->where('employee_id', $employeeId)
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('work_date', 'DESC')
            ->get();

        return response()->json([
            'employee_id' => (int) $employeeId,
            'from'        => $from->toDateString(),
            'to'          => $to->toDateString(),
            'attendances' => $records,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Daily attendance for all employees
    |--------------------------------------------------------------------------
    */
    public function daily(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->toDateString()
            : Carbon::now()->toDateString();

        $records = Attendance::with('employee')
            ->whereDate('work_date', $date)
            ->orderBy('check_in')
            ->get();

        return response()->json([
            'date'        => $date,
            'attendances' => $records,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | total hours per employee
    |--------------------------------------------------------------------------
    */
    public function monthlySummary(Request $request)
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
        /*
        |--------------------------------------------------------------------------
        | Group by employee and sum hours
        |--------------------------------------------------------------------------
        */
        $summary = $records->groupBy('employee_id')->map(function ($items) {
            $employee   = $items->first()->employee;
            $totalHours = $items->sum('total_hours');

            return [
                'employee_id'   => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'total_hours'   => round($totalHours, 2),
            ];
        })->values();

        return response()->json([
            'month'   => $monthParam,
            'summary' => $summary,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CSV export not like to frentewnd
    |--------------------------------------------------------------------------
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
            ->orderBy('work_date')
            ->get();

        $filename = "attendance_{$monthParam}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records) {
            $handle = fopen('php://output', 'w');
           
            fputcsv($handle, [
                'Date',
                'Employee',
                'Check In',
                'Check Out',
                'Total Hours'
            ]);

            foreach ($records as $row) {
                fputcsv($handle, [
                    $row->work_date,
                    $row->employee->first_name . ' ' . $row->employee->last_name,
                    $row->check_in,
                    $row->check_out,
                    $row->total_hours,
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    public function myAttendances(Request $request)
    {
        $user = $request->user();

        if (! $user->employee_id) {
            return response()->json([
                'message' => 'No employee profile linked to this user.',
            ], 400);
        }

        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $records = Attendance::with('employee')
            ->where('employee_id', $user->employee_id)
            ->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('work_date', 'DESC')
            ->get();
  
        /*
        |--------------------------------------------------------------------------
        | fallback if no attendance yet  
        |--------------------------------------------------------------------------
        */  
        $employee = $records->first()->employee
            ?? $user->employee; 

        return response()->json([
            'employee_id'   => $user->employee_id,
            'employee_name' => $employee
                ? ($employee->first_name . ' ' . $employee->last_name)
                : $user->name,
            'from'         => $from->toDateString(),
            'to'           => $to->toDateString(),
            'attendances'  => $records,
        ]);
    }
}
