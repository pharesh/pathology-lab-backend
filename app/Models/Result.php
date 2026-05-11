<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_item_id', 'parameter_name', 'observed_value',
        'unit', 'is_abnormal', 'remarks', 'entered_by', 'entered_at',
    ];

    protected $casts = [
        'is_abnormal' => 'boolean',
        'entered_at' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function computeIsAbnormal(ReferenceRange $range): bool
    {
        $val = is_numeric($this->observed_value) ? (float) $this->observed_value : null;
        if ($val === null) {
            return $range->text_range !== null && strtolower($this->observed_value) !== strtolower($range->text_range);
        }
        if ($range->min_value !== null && $val < (float) $range->min_value) return true;
        if ($range->max_value !== null && $val > (float) $range->max_value) return true;
        return false;
    }
}
