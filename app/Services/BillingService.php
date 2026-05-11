<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Order;

class BillingService
{
    public function createFromOrder(Order $order, array $data): Bill
    {
        $subtotal = $order->orderItems->sum('price_at_order');

        $discountType = $data['discount_type'] ?? null;
        $discountValue = (float) ($data['discount_value'] ?? 0);

        $discount = 0;
        if ($discountType === 'flat') {
            $discount = min($discountValue, $subtotal);
        } elseif ($discountType === 'percent') {
            $discount = round($subtotal * ($discountValue / 100), 2);
        }

        return Bill::create([
            'order_id'       => $order->id,
            'subtotal'       => $subtotal,
            'discount_type'  => $discountType,
            'discount_value' => $discountValue,
            'total_amount'   => max(0, $subtotal - $discount),
            'payment_status' => 'unpaid',
            'amount_paid'    => 0,
        ]);
    }

    public function recordPayment(Bill $bill, float $amount, string $method): Bill
    {
        $newPaid = $bill->amount_paid + $amount;
        $status = 'partial';
        $paidAt = $bill->paid_at;

        if ($newPaid >= $bill->total_amount) {
            $newPaid = $bill->total_amount;
            $status = 'paid';
            $paidAt = now();
        }

        $bill->update([
            'amount_paid'    => $newPaid,
            'payment_status' => $status,
            'payment_method' => $method,
            'paid_at'        => $paidAt,
        ]);

        return $bill->fresh();
    }
}
