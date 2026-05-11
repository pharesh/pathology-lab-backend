<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToLab
{
    protected static function bootBelongsToLab(): void
    {
        // Automatically scope all queries to the authenticated user's lab
        // Superadmins bypass isolation so they can manage all labs
        static::addGlobalScope('lab', function (Builder $builder) {
            $user = Auth::guard('sanctum')->user() ?? Auth::user();
            if ($user && $user->role === 'superadmin') return;
            $labId = $user?->lab_id;
            if ($labId) {
                $builder->where($builder->getModel()->getTable() . '.lab_id', $labId);
            }
        });

        // Auto-assign lab_id on record creation
        static::creating(function ($model) {
            if (empty($model->lab_id)) {
                $labId = static::resolveCurrentLabId();
                if ($labId) {
                    $model->lab_id = $labId;
                }
            }
        });
    }

    protected static function resolveCurrentLabId(): ?int
    {
        // Sanctum guard first (API & tests), then default guard fallback
        $user = Auth::guard('sanctum')->user() ?? Auth::user();
        return $user?->lab_id;
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Lab::class);
    }
}
