<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    /** Assign or renew subscription for a lab (superadmin only) */
    public function assign(Request $request, Lab $lab): JsonResponse
    {
        $data = $request->validate([
            'plan_id'              => 'required|exists:plans,id',
            'status'               => 'required|in:trial,active,cancelled',
            'trial_ends_at'        => 'nullable|date',
            'current_period_start' => 'nullable|date',
            'current_period_end'   => 'nullable|date',
            'amount_paid'          => 'nullable|numeric|min:0',
            'payment_method'       => 'nullable|string',
            'payment_ref'          => 'nullable|string',
            'notes'                => 'nullable|string',
        ]);

        $sub = $this->subscriptionService->assignPlan($lab, $data['plan_id'], $data['status'], $data);

        return response()->json($sub->load('plan'));
    }

    /** Current lab's own subscription status (for lab users) */
    public function myStatus(Request $request): JsonResponse
    {
        $lab = $request->user()->lab;
        if (!$lab) return response()->json(['status' => 'none'], 200);

        return response()->json($this->subscriptionService->statusSummary($lab));
    }
}
