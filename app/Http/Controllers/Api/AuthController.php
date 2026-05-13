<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lab_name'  => 'required|string|max:150',
            'lab_email' => 'nullable|email|max:100|unique:labs,email',
            'lab_phone' => 'nullable|string|max:20',
            'name'      => 'required|string|max:150',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        $lab = Lab::create([
            'name'  => $validated['lab_name'],
            'email' => $validated['lab_email'] ?? null,
            'phone' => $validated['lab_phone'] ?? null,
        ]);

        $user = User::create([
            'lab_id'   => $lab->id,
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'admin',
        ]);

        // Auto-start 14-day trial on the Trial plan
        $trialPlan = Plan::where('slug', 'trial')->first();
        if ($trialPlan) {
            $this->subscriptionService->assignPlan($lab, $trialPlan->id, 'trial', [
                'trial_ends_at' => now()->addDays(14),
            ]);
        }

        $lab->load('subscription.plan');
        $token = $user->createToken('auth-token', [], now()->addDays(30))->plainTextToken;

        return response()->json(['user' => $user, 'lab' => $lab, 'token' => $token], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $user = Auth::user();

        // Block deactivated labs
        if ($user->role !== 'superadmin' && $user->lab && !$user->lab->is_active) {
            Auth::logout();
            return response()->json(['message' => 'Your lab account has been deactivated. Please contact support.'], 403);
        }

        $token = $user->createToken('auth-token', [], now()->addDays(30))->plainTextToken;

        $lab = $user->role === 'superadmin' ? null : $user->lab?->load('subscription.plan');

        return response()->json([
            'user'  => $user,
            'lab'   => $lab,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $lab  = $user->role === 'superadmin' ? null : $user->lab?->load('subscription.plan');
        return response()->json(['user' => $user, 'lab' => $lab]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'         => 'sometimes|string|max:150',
            'current_password' => 'required_with:password|string',
            'password'     => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($data['password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 422);
            }
            $user->password = Hash::make($data['password']);
        }

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        $user->save();

        return response()->json(['user' => $user, 'message' => 'Profile updated successfully.']);
    }
}
