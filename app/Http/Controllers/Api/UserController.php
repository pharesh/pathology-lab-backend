<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function requireAdmin(Request $request): ?JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Admin access required.'], 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $users = User::where('lab_id', $request->user()->lab_id)
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        if ($err = $this->requireAdmin($request)) return $err;

        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'role'                  => 'required|in:admin,staff',
        ]);

        $user = User::create([
            'lab_id'   => $request->user()->lab_id,
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return response()->json($user->only('id', 'name', 'email', 'role', 'created_at'), 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($err = $this->requireAdmin($request)) return $err;

        if ($user->lab_id !== $request->user()->lab_id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();
        return response()->json(null, 204);
    }
}
