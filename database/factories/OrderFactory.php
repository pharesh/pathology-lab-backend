<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'ordered_at' => now(),
            'status'     => 'pending',
            'notes'      => null,
        ];
    }

    public function hasOrderItems(int $count = 1, array $attributes = []): static
    {
        return $this->has(
            \App\Models\OrderItem::factory()->count($count)->state($attributes),
            'orderItems'
        );
    }
}
