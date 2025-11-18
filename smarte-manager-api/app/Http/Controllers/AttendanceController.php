<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Check-in
    public function checkIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'work_date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now(),
        ]);

        return response()->json($attendance);
    }

    // Check-out
    public function checkOut(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);

        $attendance->check_out = Carbon::now();

        // Calculate hours
        $attendance->total_hours = Carbon::parse($attendance->check_in)
            ->diffInMinutes(Carbon::now()) / 60;

        $attendance->save();

        return response()->json($attendance);
    }

    // All attendance
    public function index()
    {
        return response()->json(
            Attendance::with('employee')->orderBy('id', 'DESC')->get()
        );
    }
}
