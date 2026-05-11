<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LabFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'            => fake()->company() . ' Diagnostics',
            'email'           => fake()->unique()->safeEmail(),
            'phone'           => fake()->numerify('##########'),
            'address'         => fake()->address(),
            'registration_no' => strtoupper(fake()->bothify('LAB-####')),
            'is_active'       => true,
        ];
    }
}
