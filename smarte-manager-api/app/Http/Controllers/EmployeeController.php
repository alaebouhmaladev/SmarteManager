<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // Get all employees
    public function index()
    {
        return response()->json(Employee::all());
    }

    // Create employee
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'nullable',
            'role' => 'nullable',
            'hourly_rate' => 'required|numeric',
            'hire_date' => 'nullable|date',
            'status' => 'in:active,inactive',
        ]);

        $employee = Employee::create($data);

        return response()->json($employee, 201);
    }

    // Show employee
    public function show($id)
    {
        return response()->json(Employee::findOrFail($id));
    }

    // Update employee
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $employee->update($request->all());

        return response()->json($employee);
    }

    // Delete employee
    public function destroy($id)
    {
        Employee::destroy($id);
        return response()->json(['message' => 'Employee deleted']);
    }
}
