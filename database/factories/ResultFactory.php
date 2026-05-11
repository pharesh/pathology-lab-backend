<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_item_id'  => OrderItem::factory(),
            'parameter_name' => fake()->words(2, true),
            'observed_value' => (string) fake()->randomFloat(1, 1, 20),
            'unit'           => fake()->randomElement(['g/dL', 'mg/dL', 'U/L', '%']),
            'is_abnormal'    => false,
            'remarks'        => null,
            'entered_by'     => fake()->name(),
            'entered_at'     => now(),
        ];
    }
}
