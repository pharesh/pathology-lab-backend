<?php

namespace App\Models;

use App\Traits\BelongsToLab;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use BelongsToLab, HasFactory;
    protected $fillable = [
        'order_uid', 'patient_id', 'ordered_at', 'status', 'notes',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Order $order) {
            $order->order_uid = 'ORD-' . date('Y') . str_pad(
                (Order::withoutGlobalScopes()->whereYear('created_at', date('Y'))->count() + 1),
                4, '0', STR_PAD_LEFT
            );
        });
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }
}
