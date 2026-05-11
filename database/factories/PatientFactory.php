<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => fake()->name(),
            'age'        => fake()->numberBetween(1, 90),
            'age_unit'   => 'years',
            'gender'     => fake()->randomElement(['male', 'female']),
            'phone'      => fake()->numerify('##########'),
            'email'      => fake()->unique()->safeEmail(),
            'address'    => fake()->address(),
            'referred_by' => fake()->name(),
        ];
    }
}
