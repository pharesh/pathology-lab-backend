<?php

namespace App\Services;

use App\Models\Lab;
use App\Models\Subscription;

class SubscriptionService
{
    public function getForLab(Lab $lab): ?Subscription
    {
        return $lab->subscription;
    }

    public function isAccessible(Lab $lab): bool
    {
        $sub = $this->getForLab($lab);
        if (!$sub) return false;
        return $sub->isAccessible();
    }

    public function statusSummary(Lab $lab): array
    {
        $sub  = $this->getForLab($lab);
        if (!$sub) {
            return ['accessible' => false, 'status' => 'no_subscription', 'days_remaining' => 0, 'plan' => null];
        }

        $sub->load('plan');
        $effective = $sub->effectiveStatus();

        return [
            'accessible'    => $sub->isAccessible(),
            'status'        => $effective,
            'days_remaining' => $sub->daysRemaining(),
            'plan'          => [
                'name'         => $sub->plan?->name,
                'slug'         => $sub->plan?->slug,
                'price_monthly' => $sub->plan?->price_monthly,
            ],
            'trial_ends_at'       => $sub->trial_ends_at?->toDateString(),
            'current_period_end'  => $sub->current_period_end?->toDateString(),
        ];
    }

    public function checkPatientLimit(Lab $lab): bool
    {
        $sub = $this->getForLab($lab);
        if (!$sub || !$sub->isAccessible()) return false;
        $max = $sub->plan?->max_patients;
        if ($max === null) return true;
        return $lab->patients()->count() < $max;
    }

    public function checkUserLimit(Lab $lab): bool
    {
        $sub = $this->getForLab($lab);
        if (!$sub || !$sub->isAccessible()) return false;
        $max = $sub->plan?->max_users;
        if ($max === null) return true;
        return $lab->users()->count() < $max;
    }

    public function checkOrderLimit(Lab $lab): bool
    {
        $sub = $this->getForLab($lab);
        if (!$sub || !$sub->isAccessible()) return false;
        $max = $sub->plan?->max_orders_per_month;
        if ($max === null) return true;
        $count = $lab->orders()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        return $count < $max;
    }

    public function assignPlan(Lab $lab, int $planId, string $status, array $options = []): Subscription
    {
        // Cancel any existing subscription
        Subscription::where('lab_id', $lab->id)->whereNull('cancelled_at')->update(['cancelled_at' => now()]);

        return Subscription::create([
            'lab_id'               => $lab->id,
            'plan_id'              => $planId,
            'status'               => $status,
            'trial_ends_at'        => $options['trial_ends_at'] ?? null,
            'current_period_start' => $options['current_period_start'] ?? null,
            'current_period_end'   => $options['current_period_end'] ?? null,
            'amount_paid'          => $options['amount_paid'] ?? 0,
            'payment_method'       => $options['payment_method'] ?? null,
            'payment_ref'          => $options['payment_ref'] ?? null,
            'notes'                => $options['notes'] ?? null,
        ]);
    }
}
