<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Patient;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function createCompletedOrder(float $price1 = 250, float $price2 = 500): array
    {
        $patient = Patient::factory()->create();
        $order   = Order::factory()->for($patient)->create(['status' => 'completed']);
        OrderItem::factory()->create(['order_id' => $order->id, 'price_at_order' => $price1]);
        OrderItem::factory()->create(['order_id' => $order->id, 'price_at_order' => $price2]);

        return compact('patient', 'order');
    }

    // ── CREATE BILL ────────────────────────────────────────────────────────

    public function test_can_create_bill_for_order(): void
    {
        ['order' => $order] = $this->createCompletedOrder(250, 500);

        $response = $this->postJson('/api/v1/bills', ['order_id' => $order->id]);

        $response->assertCreated()
            ->assertJsonPath('subtotal', '750.00')
            ->assertJsonPath('total_amount', '750.00')
            ->assertJsonPath('payment_status', 'unpaid')
            ->assertJsonStructure(['id', 'bill_uid', 'order_id', 'subtotal', 'total_amount']);

        $this->assertDatabaseHas('bills', ['order_id' => $order->id]);
    }

    public function test_bill_uid_is_auto_generated(): void
    {
        ['order' => $order] = $this->createCompletedOrder();

        $response = $this->postJson('/api/v1/bills', ['order_id' => $order->id]);

        $this->assertMatchesRegularExpression('/^INV-\d{4}\d{4}$/', $response->json('bill_uid'));
    }

    public function test_bill_applies_flat_discount(): void
    {
        ['order' => $order] = $this->createCompletedOrder(250, 500);

        $response = $this->postJson('/api/v1/bills', [
            'order_id'       => $order->id,
            'discount_type'  => 'flat',
            'discount_value' => 100,
        ]);

        $response->assertCreated()
            ->assertJsonPath('subtotal', '750.00')
            ->assertJsonPath('total_amount', '650.00');
    }

    public function test_bill_applies_percent_discount(): void
    {
        ['order' => $order] = $this->createCompletedOrder(250, 500);

        $response = $this->postJson('/api/v1/bills', [
            'order_id'       => $order->id,
            'discount_type'  => 'percent',
            'discount_value' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('subtotal', '750.00')
            ->assertJsonPath('total_amount', '675.00');
    }

    public function test_cannot_create_duplicate_bill_for_same_order(): void
    {
        ['order' => $order] = $this->createCompletedOrder();
        $this->postJson('/api/v1/bills', ['order_id' => $order->id]);

        $this->postJson('/api/v1/bills', ['order_id' => $order->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_create_bill_fails_with_nonexistent_order(): void
    {
        $this->postJson('/api/v1/bills', ['order_id' => 9999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id']);
    }

    // ── READ BILL ──────────────────────────────────────────────────────────

    public function test_can_list_bills_with_patient_info(): void
    {
        ['order' => $order] = $this->createCompletedOrder();
        Bill::factory()->create(['order_id' => $order->id, 'subtotal' => 750, 'total_amount' => 750]);

        $response = $this->getJson('/api/v1/bills');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'bill_uid', 'total_amount', 'payment_status']]]);
    }

    public function test_can_filter_bills_by_payment_status(): void
    {
        ['order' => $order1] = $this->createCompletedOrder();
        ['order' => $order2] = $this->createCompletedOrder();
        Bill::factory()->create(['order_id' => $order1->id, 'subtotal' => 100, 'total_amount' => 100, 'payment_status' => 'paid', 'amount_paid' => 100]);
        Bill::factory()->create(['order_id' => $order2->id, 'subtotal' => 200, 'total_amount' => 200, 'payment_status' => 'unpaid']);

        $response = $this->getJson('/api/v1/bills?payment_status=unpaid');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('unpaid', $response->json('data.0.payment_status'));
    }

    public function test_can_show_bill_with_order_and_items(): void
    {
        ['order' => $order] = $this->createCompletedOrder();
        $bill = Bill::factory()->create(['order_id' => $order->id, 'subtotal' => 750, 'total_amount' => 750]);

        $response = $this->getJson("/api/v1/bills/{$bill->id}");

        $response->assertOk()
            ->assertJsonPath('id', $bill->id)
            ->assertJsonStructure(['id', 'bill_uid', 'order' => ['patient', 'order_items']]);
    }

    // ── RECORD PAYMENT ─────────────────────────────────────────────────────

    public function test_can_record_full_payment(): void
    {
        ['order' => $order] = $this->createCompletedOrder(250, 500);
        $bill = Bill::factory()->create(['order_id' => $order->id, 'subtotal' => 750, 'total_amount' => 750, 'payment_status' => 'unpaid', 'amount_paid' => 0]);

        $response = $this->patchJson("/api/v1/bills/{$bill->id}/payment", [
            'amount'         => 750,
            'payment_method' => 'cash',
        ]);

        $response->assertOk()
            ->assertJsonPath('payment_status', 'paid')
            ->assertJsonPath('amount_paid', '750.00')
            ->assertJsonPath('payment_method', 'cash');

        $this->assertDatabaseHas('bills', ['id' => $bill->id, 'payment_status' => 'paid']);
    }

    public function test_partial_payment_sets_status_to_partial(): void
    {
        ['order' => $order] = $this->createCompletedOrder(250, 500);
        $bill = Bill::factory()->create(['order_id' => $order->id, 'subtotal' => 750, 'total_amount' => 750, 'payment_status' => 'unpaid', 'amount_paid' => 0]);

        $response = $this->patchJson("/api/v1/bills/{$bill->id}/payment", [
            'amount'         => 400,
            'payment_method' => 'upi',
        ]);

        $response->assertOk()
            ->assertJsonPath('payment_status', 'partial')
            ->assertJsonPath('amount_paid', '400.00');
    }

    public function test_payment_exceeding_total_is_capped_at_total(): void
    {
        ['order' => $order] = $this->createCompletedOrder();
        $bill = Bill::factory()->create(['order_id' => $order->id, 'subtotal' => 750, 'total_amount' => 750, 'payment_status' => 'unpaid', 'amount_paid' => 0]);

        $response = $this->patchJson("/api/v1/bills/{$bill->id}/payment", [
            'amount'         => 9999,
            'payment_method' => 'cash',
        ]);

        $response->assertOk()
            ->assertJsonPath('payment_status', 'paid')
            ->assertJsonPath('amount_paid', '750.00');
    }

    public function test_payment_fails_without_method(): void
    {
        ['order' => $order] = $this->createCompletedOrder();
        $bill = Bill::factory()->create(['order_id' => $order->id, 'subtotal' => 750, 'total_amount' => 750]);

        $this->patchJson("/api/v1/bills/{$bill->id}/payment", ['amount' => 100])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_payment_rejects_invalid_method(): void
    {
        ['order' => $order] = $this->createCompletedOrder();
        $bill = Bill::factory()->create(['order_id' => $order->id, 'subtotal' => 750, 'total_amount' => 750]);

        $this->patchJson("/api/v1/bills/{$bill->id}/payment", ['amount' => 100, 'payment_method' => 'bitcoin'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['payment_method']);
    }
}
