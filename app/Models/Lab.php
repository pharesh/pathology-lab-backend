<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'registration_no', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
