<?php

namespace App\Models;

use App\Traits\BelongsToLab;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class Bill extends Model
{
    use BelongsToLab, HasFactory;
    protected $fillable = [
        'bill_uid', 'order_id', 'subtotal', 'discount_type', 'discount_value',
        'total_amount', 'payment_status', 'amount_paid', 'payment_method', 'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Bill $bill) {
            $bill->bill_uid = 'INV-' . date('Y') . str_pad(
                (Bill::withoutGlobalScopes()->whereYear('created_at', date('Y'))->count() + 1),
                4, '0', STR_PAD_LEFT
            );
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
