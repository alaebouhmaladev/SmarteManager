<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index function return users with desc order
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return response()->json(
            User::orderBy('id', 'DESC')->get()
        );
    }
    /*
    |--------------------------------------------------------------------------
    | store function create new user
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $authUser = $request->user();

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => [
                'required',
                Rule::in(['admin', 'manager', 'hr', 'stock_manager', 'staff']),
            ],
        ]);

        // force role 'staff' if manager create this user 
        if ($authUser->role === 'manager' && $data['role'] !== 'staff') {
            return response()->json([
                'message' => 'Managers can only create staff users.',
            ], 403);
        }

        $user = User::create($data);

        if (in_array($data['role'], ['manager', 'hr', 'stock_manager', 'staff'], true)) {

            $parts = explode(' ', $user->name, 2);
            $firstName = $parts[0] ?? $user->name;
            $lastName  = $parts[1] ?? '';

            $employee = Employee::create([
                'first_name'  => $firstName,
                'last_name'   => $lastName,
                'phone'       => null,
                'role'        => ucfirst(str_replace('_', ' ', $data['role'])), 
                'hourly_rate' => 0,
                'hire_date'   => now()->toDateString(),
                'status'      => 'active',
            ]);

            $user->employee_id = $employee->id;
            $user->save();

        }

        return response()->json($user, 201);
    }

    /*
    |--------------------------------------------------------------------------
    | show function return user data
    |--------------------------------------------------------------------------
    */
    public function show(User $user)
    {
        return response()->json($user);
    }
    /*
    |--------------------------------------------------------------------------
    | update function updating user data just admin can make updates 
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'sometimes|string|min:6',
            'role'     => [
                'sometimes',
                Rule::in(['admin', 'manager', 'hr', 'stock_manager', 'staff']),
            ],
        ]);

        $user->update($data);

        if (
            isset($data['role']) &&
            in_array($data['role'], ['manager', 'hr', 'stock_manager', 'staff'], true) &&
            !$user->employee_id
        ) {
            $parts = explode(' ', $user->name, 2);
            $firstName = $parts[0] ?? $user->name;
            $lastName  = $parts[1] ?? '';

            $employee = Employee::create([
                'first_name'  => $firstName,
                'last_name'   => $lastName,
                'phone'       => null,
                'role'        => ucfirst(str_replace('_', ' ', $data['role'])),
                'hourly_rate' => 0,
                'hire_date'   => now()->toDateString(),
                'status'      => 'active',
            ]);

            $user->employee_id = $employee->id;
            $user->save();
        }

        return response()->json($user);
    }
    /*
    |--------------------------------------------------------------------------
    | destroy function delete user using id
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
