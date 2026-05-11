<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Superadmins are never blocked
        if (!$user || $user->role === 'superadmin') {
            return $next($request);
        }

        $lab = $user->lab;
        if (!$lab) {
            return response()->json(['message' => 'Lab not found.'], 403);
        }

        if (!$this->subscriptionService->isAccessible($lab)) {
            $summary = $this->subscriptionService->statusSummary($lab);
            return response()->json([
                'message'      => 'Your subscription has expired. Please contact support to renew.',
                'subscription' => $summary,
            ], 403);
        }

        return $next($request);
    }
}
