<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'lab_id', 'plan_id', 'status',
        'trial_ends_at', 'current_period_start', 'current_period_end',
        'cancelled_at', 'amount_paid', 'payment_method', 'payment_ref', 'notes',
    ];

    protected $casts = [
        'trial_ends_at'         => 'datetime',
        'current_period_start'  => 'datetime',
        'current_period_end'    => 'datetime',
        'cancelled_at'          => 'datetime',
        'amount_paid'           => 'decimal:2',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** Recompute status based on dates and return effective status. */
    public function effectiveStatus(): string
    {
        if ($this->cancelled_at) return 'cancelled';

        $now = now();

        if ($this->status === 'trial') {
            if ($this->trial_ends_at && $now->lte($this->trial_ends_at)) return 'trial';
            if ($this->trial_ends_at && $now->lte($this->trial_ends_at->addDays(3))) return 'grace';
            return 'expired';
        }

        if ($this->current_period_end) {
            if ($now->lte($this->current_period_end)) return 'active';
            if ($now->lte($this->current_period_end->addDays(3))) return 'grace';
        }

        return 'expired';
    }

    public function isAccessible(): bool
    {
        return in_array($this->effectiveStatus(), ['trial', 'active', 'grace']);
    }

    public function daysRemaining(): int
    {
        $end = $this->status === 'trial' ? $this->trial_ends_at : $this->current_period_end;
        if (!$end) return 0;
        return max(0, (int) now()->diffInDays($end, false));
    }
}
