<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function createLab(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lab_name'    => 'required|string|max:150',
            'lab_email'   => 'nullable|email|max:100|unique:labs,email',
            'lab_phone'   => 'nullable|string|max:20',
            'admin_name'  => 'required|string|max:150',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'plan_id'     => 'nullable|exists:plans,id',
            'status'      => 'nullable|in:trial,active',
            'trial_ends_at'        => 'nullable|date',
            'current_period_start' => 'nullable|date',
            'current_period_end'   => 'nullable|date',
        ]);

        $lab = Lab::create([
            'name'  => $data['lab_name'],
            'email' => $data['lab_email'] ?? null,
            'phone' => $data['lab_phone'] ?? null,
        ]);

        $user = User::create([
            'lab_id'   => $lab->id,
            'name'     => $data['admin_name'],
            'email'    => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'role'     => 'admin',
        ]);

        // Assign subscription if plan provided, else auto-start trial
        $planId  = $data['plan_id'] ?? Plan::where('slug', 'trial')->value('id');
        $status  = $data['status']  ?? 'trial';
        $options = [];

        if ($status === 'trial') {
            $options['trial_ends_at'] = $data['trial_ends_at'] ?? now()->addDays(14)->toDateString();
        } else {
            $options['current_period_start'] = $data['current_period_start'] ?? now()->toDateString();
            $options['current_period_end']   = $data['current_period_end']   ?? now()->addMonth()->toDateString();
        }

        if ($planId) {
            $this->subscriptionService->assignPlan($lab, $planId, $status, $options);
        }

        $lab->load('subscription.plan');

        return response()->json([
            'lab'   => $lab,
            'user'  => $user,
        ], 201);
    }

    public function stats(): JsonResponse
    {
        $totalLabs   = Lab::count();
        $activeSubs  = Subscription::where('status', 'active')->whereNull('cancelled_at')
                           ->where('current_period_end', '>=', now())->count();
        $trialSubs   = Subscription::where('status', 'trial')->whereNull('cancelled_at')
                           ->where('trial_ends_at', '>=', now())->count();
        $expiredSubs = Subscription::whereIn('status', ['expired', 'trial'])
                           ->where(function ($q) {
                               $q->where('trial_ends_at', '<', now()->subDays(3))
                                 ->orWhere('current_period_end', '<', now()->subDays(3));
                           })->whereNull('cancelled_at')->count();

        // MongoDB doesn't support SQL JOINs — load active subs with plans and sum in PHP
        $mrr = Subscription::where('status', 'active')
                   ->whereNull('cancelled_at')
                   ->where('current_period_end', '>=', now())
                   ->with('plan')
                   ->get()
                   ->sum(fn($sub) => $sub->plan?->price_monthly ?? 0);

        $totalPatients    = Patient::withoutGlobalScopes()->count();
        $totalOrders      = Order::withoutGlobalScopes()->count();

        // MongoDB doesn't support whereMonth/whereYear — use whereBetween with Carbon
        $newLabsThisMonth = Lab::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->count();

        $recentLabs = Lab::with(['subscription.plan'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($lab) => [
                'id'           => $lab->id,
                'name'         => $lab->name,
                'email'        => $lab->email,
                'is_active'    => $lab->is_active,
                'created_at'   => $lab->created_at,
                'subscription' => $this->subSummary($lab->subscription),
            ]);

        return response()->json([
            'total_labs'        => $totalLabs,
            'active_subs'       => $activeSubs,
            'trial_subs'        => $trialSubs,
            'expired_subs'      => $expiredSubs,
            'mrr'               => round($mrr, 2),
            'total_patients'    => $totalPatients,
            'total_orders'      => $totalOrders,
            'new_labs_this_month' => $newLabsThisMonth,
            'recent_labs'       => $recentLabs,
        ]);
    }

    public function labs(): JsonResponse
    {
        // MongoDB doesn't support withCount() — load users eagerly and count patients/orders separately
        $labs = Lab::with(['subscription.plan', 'users'])
            ->latest()
            ->get()
            ->map(fn($lab) => [
                'id'             => $lab->id,
                'name'           => $lab->name,
                'email'          => $lab->email,
                'phone'          => $lab->phone,
                'is_active'      => $lab->is_active,
                'created_at'     => $lab->created_at,
                'users_count'    => $lab->users->count(),
                'patients_count' => Patient::withoutGlobalScopes()->where('lab_id', $lab->id)->count(),
                'orders_count'   => Order::withoutGlobalScopes()->where('lab_id', $lab->id)->count(),
                'subscription'   => $this->subSummary($lab->subscription),
            ]);

        return response()->json($labs);
    }

    public function showLab(Lab $lab): JsonResponse
    {
        $lab->load(['subscription.plan', 'users']);
        $subHistory = Subscription::where('lab_id', $lab->id)->with('plan')->latest()->get();

        return response()->json([
            'lab'             => $lab,
            'users'           => $lab->users,
            'subscription'    => $this->subSummary($lab->subscription),
            'sub_history'     => $subHistory,
            'stats'           => [
                'patients' => Patient::withoutGlobalScopes()->where('lab_id', $lab->id)->count(),
                'orders'   => Order::withoutGlobalScopes()->where('lab_id', $lab->id)->count(),
            ],
        ]);
    }

    public function updateLab(Request $request, Lab $lab): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'sometimes|string|max:150',
            'email'           => 'sometimes|nullable|email|max:100|unique:labs,email,' . $lab->id,
            'phone'           => 'sometimes|nullable|string|max:20',
            'address'         => 'sometimes|nullable|string|max:500',
            'registration_no' => 'sometimes|nullable|string|max:100',
        ]);

        $lab->update($data);
        return response()->json(['lab' => $lab]);
    }

    public function addLabUser(Request $request, Lab $lab): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,staff',
        ]);

        $user = User::create([
            'lab_id'   => $lab->id,
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function removeLabUser(Lab $lab, User $user): JsonResponse
    {
        abort_if($user->lab_id !== $lab->id, 403, 'User does not belong to this lab.');

        $user->delete();
        return response()->json(['message' => 'User removed.']);
    }

    public function toggleLab(Lab $lab): JsonResponse
    {
        $lab->update(['is_active' => !$lab->is_active]);
        return response()->json(['is_active' => $lab->is_active]);
    }

    public function setUserPassword(Request $request, Lab $lab, User $user): JsonResponse
    {
        abort_if($user->lab_id !== $lab->id, 403, 'User does not belong to this lab.');

        $data = $request->validate(['password' => 'required|string|min:8']);

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    private function subSummary(?Subscription $sub): array
    {
        if (!$sub) return ['status' => 'none', 'plan' => null, 'days_remaining' => 0];

        return [
            'id'                  => $sub->id,
            'status'              => $sub->effectiveStatus(),
            'plan'                => $sub->plan?->name,
            'plan_slug'           => $sub->plan?->slug,
            'days_remaining'      => $sub->daysRemaining(),
            'trial_ends_at'       => $sub->trial_ends_at?->toDateString(),
            'current_period_end'  => $sub->current_period_end?->toDateString(),
            'amount_paid'         => $sub->amount_paid,
        ];
    }
}
