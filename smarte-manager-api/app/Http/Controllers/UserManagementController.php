<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    // List users (admin & manager only)
    public function index(Request $request)
    {
        $authUser = $request->user();

        if (!in_array($authUser->role, ['admin', 'manager'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return User::select('id', 'name', 'email', 'role', 'created_at')->get();
    }

    // Create user with role-based rules
    public function store(Request $request)
    {
        $authUser = $request->user();

        // Only admin or manager can create users
        if (!in_array($authUser->role, ['admin', 'manager'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Validate incoming data
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,manager,staff',
        ]);

        // Apply role rules:
        // - Admin: can create manager + staff
        // - Manager: can create staff only
        if ($authUser->role === 'manager' && $data['role'] !== 'staff') {
            return response()->json([
                'message' => 'Managers can only create staff users.',
            ], 403);
        }

        if ($authUser->role === 'admin' && $data['role'] === 'admin') {
            // Optional: block creating other admins, or allow it.
            // Here we block it for safety. Remove this if you want.
            return response()->json([
                'message' => 'Admin creation is restricted.',
            ], 403);
        }

        // Because User model uses 'password' => 'hashed' cast,
        // we just assign plain password and Laravel hashes it.
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => $data['role'],
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 201);
    }
}
