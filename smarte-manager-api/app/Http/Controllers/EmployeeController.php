<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    |   index function returen all employees with desc order 
    |--------------------------------------------------------------------------
    */ 
    public function index()
    {
        return response()->json(
            Employee::orderBy('id', 'DESC')->get()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | store function create new employee
    |--------------------------------------------------------------------------
    */ 
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'role'        => 'nullable|string|max:255',
            'hourly_rate' => 'required|numeric|min:0',
            'hire_date'   => 'nullable|date',
            'status'      => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $employee = Employee::create($data);

        return response()->json($employee, 201);
    }

    /*
    |--------------------------------------------------------------------------
    | show functyion Return all Employees 
    |--------------------------------------------------------------------------
    */ 
    public function show(Employee $employee)
    {
        return response()->json($employee);
    }

    /*
    |--------------------------------------------------------------------------
    | update function update Employee
    |--------------------------------------------------------------------------
    */ 
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'first_name'  => 'sometimes|string|max:255',
            'last_name'   => 'sometimes|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'role'        => 'nullable|string|max:255',
            'hourly_rate' => 'sometimes|numeric|min:0',
            'hire_date'   => 'nullable|date',
            'status'      => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $employee->update($data);

        return response()->json($employee);
    }
    /*
    |--------------------------------------------------------------------------
    | destroy function delete Employee
    |--------------------------------------------------------------------------
    */ 
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json(['message' => 'Employee deleted.']);
    }
}
