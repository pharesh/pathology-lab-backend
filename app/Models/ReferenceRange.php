<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceRange extends Model
{
    use HasFactory;
    protected $fillable = [
        'test_id', 'parameter_name', 'unit', 'min_value', 'max_value',
        'text_range', 'gender_filter', 'age_min', 'age_max', 'age_unit',
    ];

    protected $casts = [
        'min_value' => 'decimal:3',
        'max_value' => 'decimal:3',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
