<?php

namespace Database\Factories;

use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferenceRangeFactory extends Factory
{
    public function definition(): array
    {
        $min = fake()->randomFloat(2, 1, 50);

        return [
            'test_id'        => Test::factory(),
            'parameter_name' => fake()->words(2, true),
            'unit'           => fake()->randomElement(['g/dL', 'mg/dL', 'mmol/L', 'U/L', '%', 'cells/μL']),
            'min_value'      => $min,
            'max_value'      => $min + fake()->randomFloat(2, 5, 50),
            'text_range'     => null,
            'gender_filter'  => 'all',
            'age_min'        => null,
            'age_max'        => null,
            'age_unit'       => 'years',
        ];
    }
}
