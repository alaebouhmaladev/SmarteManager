<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Employee check-in
     * Admin/Manager can also check-in another employee.
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        // Prevent multiple check-ins without checkout
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
            'work_date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now(),
        ]);

        return response()->json($attendance);
    }

    /**
     * Employee check-out
     * Finds the latest active check-in automatically.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        // Find open attendance (no check_out)
        $attendance = Attendance::where('employee_id', $request->employee_id)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No active check-in found for this employee.'
            ], 404);
        }

        $attendance->check_out = Carbon::now();

        // Calculate total worked hours
        $attendance->total_hours = Carbon::parse($attendance->check_in)
            ->diffInMinutes(Carbon::now()) / 60;

        $attendance->save();

        return response()->json($attendance);
    }

    /**
     * List all attendance records
     */
    public function index()
    {
        return response()->json(
            Attendance::with('employee')
                ->orderBy('id', 'DESC')
                ->get()
        );
    }
}
