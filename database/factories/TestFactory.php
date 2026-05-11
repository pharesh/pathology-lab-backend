<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'test_code'        => strtoupper(fake()->unique()->bothify('??###')),
            'test_name'        => fake()->words(3, true),
            'category'         => fake()->randomElement(['Hematology', 'Biochemistry', 'Microbiology', 'Serology']),
            'sample_type'      => fake()->randomElement(['blood', 'urine', 'stool', 'swab', 'other']),
            'price'            => fake()->randomFloat(2, 50, 2000),
            'turnaround_hours' => fake()->numberBetween(2, 48),
            'is_active'        => true,
        ];
    }
}
