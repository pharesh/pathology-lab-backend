<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id', 'test_id', 'price_at_order', 'status',
    ];

    protected $casts = [
        'price_at_order' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
