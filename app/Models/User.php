<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'lab_id', 'name', 'email', 'password', 'role',
    ];

    /**
     * Override Sanctum's createToken to work with MongoDB.
     * NewAccessToken type-checks for Laravel\Sanctum\PersonalAccessToken which cannot
     * extend MongoDB\Laravel\Eloquent\Model simultaneously, so we return a plain DTO.
     */
    public function createToken(string $name, array $abilities = ['*'], DateTimeInterface $expiresAt = null): object
    {
        $plainTextToken = Str::random(40);

        $token = $this->tokens()->create([
            'name'       => $name,
            'token'      => hash('sha256', $plainTextToken),
            'abilities'  => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return (object) [
            'accessToken'    => $token,
            'plainTextToken' => $token->getKey() . '|' . $plainTextToken,
        ];
    }

    public function lab(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Lab::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
