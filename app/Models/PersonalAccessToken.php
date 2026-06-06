<?php

namespace App\Models;

use Laravel\Sanctum\Contracts\HasAbilities;
use MongoDB\Laravel\Eloquent\Model;

class PersonalAccessToken extends Model implements HasAbilities
{
    protected $table = 'personal_access_tokens';

    protected $guarded = [];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return [
            'abilities'    => 'json',
            'last_used_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }

    public static function findToken($token)
    {
        if (! str_contains($token, '|')) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $token] = explode('|', $token, 2);

        $instance = static::find($id);

        if ($instance && hash_equals($instance->token, hash('sha256', $token))) {
            return $instance;
        }
    }

    public function can($ability)
    {
        return in_array('*', $this->abilities ?? []) ||
               in_array($ability, $this->abilities ?? []);
    }

    public function cant($ability)
    {
        return ! $this->can($ability);
    }

    public function abilities(): array
    {
        return $this->abilities ?? [];
    }
}
