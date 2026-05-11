<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->randomFloat(2, 100, 5000);

        return [
            'order_id'       => Order::factory(),
            'subtotal'       => $total,
            'discount_type'  => null,
            'discount_value' => 0,
            'total_amount'   => $total,
            'payment_status' => 'unpaid',
            'amount_paid'    => 0,
            'payment_method' => null,
            'paid_at'        => null,
        ];
    }
}
