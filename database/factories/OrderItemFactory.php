<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'       => Order::factory(),
            'test_id'        => Test::factory(),
            'price_at_order' => fake()->randomFloat(2, 50, 2000),
            'status'         => 'pending',
        ];
    }
}
