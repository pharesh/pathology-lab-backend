<?php

namespace App\Models;

use App\Traits\BelongsToLab;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    use BelongsToLab, HasFactory, SoftDeletes;

    protected $fillable = [
        'test_code', 'test_name', 'category', 'sample_type',
        'price', 'turnaround_hours', 'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
        'turnaround_hours' => 24,
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function referenceRanges(): HasMany
    {
        return $this->hasMany(ReferenceRange::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
