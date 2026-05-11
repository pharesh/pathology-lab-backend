<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Do NOT wrap with bcrypt() — the User model's 'hashed' cast handles it.
        // Passing an already-hashed string causes double-hashing and breaks login.
        User::firstOrCreate(
            ['email' => 'admin@pathlab.com'],
            [
                'name'     => 'Admin',
                'password' => 'password',
                'role'     => 'superadmin'
            ]
        );

        $this->call(TestSeeder::class);
    }
}
