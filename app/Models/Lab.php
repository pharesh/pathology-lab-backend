<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MongoDB\Laravel\Eloquent\Model;

class Lab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'registration_no', 'is_active',
        'doctor_name', 'doctor_designation', 'signature_image',
    ];

    protected $appends = ['signature_url'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature_image
            ? url('storage/' . $this->signature_image)
            : null;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
