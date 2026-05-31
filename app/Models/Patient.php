<?php

namespace App\Models;

use App\Traits\BelongsToLab;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use BelongsToLab, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_uid', 'name', 'age', 'age_unit', 'gender',
        'phone', 'email', 'address', 'referred_by',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Patient $patient) {
            $patient->patient_uid = 'PAT-' . date('Y') . str_pad(
                (Patient::withoutGlobalScopes()->withTrashed()->whereYear('created_at', date('Y'))->count() + 1),
                4, '0', STR_PAD_LEFT
            );
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
