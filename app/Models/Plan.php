<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price_monthly', 'price_yearly',
        'max_patients', 'max_users', 'max_orders_per_month',
        'has_pdf_reports', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_monthly'         => 'decimal:2',
        'price_yearly'          => 'decimal:2',
        'has_pdf_reports'       => 'boolean',
        'is_active'             => 'boolean',
        'max_patients'          => 'integer',
        'max_users'             => 'integer',
        'max_orders_per_month'  => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
