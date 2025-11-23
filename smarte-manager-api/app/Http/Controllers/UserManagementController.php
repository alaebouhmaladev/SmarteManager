<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * List all users (admin + manager).
     */
    public function index()
    {
        return response()->json(
            User::orderBy('id', 'DESC')->get()
        );
    }

    /**
     * Create a new user.
     *
     * - Admin can create: admin, manager, staff
     * - Manager can create: staff ONLY
     */
    public function store(Request $request)
    {
        $authUser = $request->user();

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => ['required', Rule::in(['admin', 'manager', 'staff'])],
        ]);

        // If a MANAGER is creating a user → force role = 'staff'
        if ($authUser->role === 'manager' && $data['role'] !== 'staff') {
            return response()->json([
                'message' => 'Managers can only create staff users.'
            ], 403);
        }

        // User model has "password" cast to hashed, so no manual bcrypt needed
        $user = User::create($data);

        return response()->json($user, 201);
    }

    /**
     * Show a single user (admin + manager).
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update a user (admin only – route is protected).
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
            'role'     => ['sometimes', Rule::in(['admin', 'manager', 'staff'])],
        ]);

        $user->update($data);

        return response()->json($user);
    }

    /**
     * Delete a user (admin only).
     * Prevent deleting yourself.
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
