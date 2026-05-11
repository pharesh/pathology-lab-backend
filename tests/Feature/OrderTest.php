<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Patient;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    // ── CREATE ─────────────────────────────────────────────────────────────

    public function test_can_create_order_with_multiple_tests(): void
    {
        $patient = Patient::factory()->create();
        $tests   = Test::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/orders', [
            'patient_id' => $patient->id,
            'test_ids'   => $tests->pluck('id')->toArray(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('patient_id', $patient->id)
            ->assertJsonPath('status', 'pending')
            ->assertJsonStructure(['id', 'order_uid', 'patient', 'order_items']);

        $this->assertCount(2, $response->json('order_items'));
        $this->assertDatabaseHas('orders', ['patient_id' => $patient->id]);
        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_order_uid_is_auto_generated(): void
    {
        $patient = Patient::factory()->create();
        $test    = Test::factory()->create();

        $response = $this->postJson('/api/v1/orders', [
            'patient_id' => $patient->id,
            'test_ids'   => [$test->id],
        ]);

        $uid = $response->json('order_uid');
        $this->assertMatchesRegularExpression('/^ORD-\d{4}\d{4}$/', $uid);
    }

    public function test_order_items_snapshot_price_at_time_of_order(): void
    {
        $patient = Patient::factory()->create();
        $test    = Test::factory()->create(['price' => 500.00]);

        $response = $this->postJson('/api/v1/orders', [
            'patient_id' => $patient->id,
            'test_ids'   => [$test->id],
        ]);

        $this->assertEquals('500.00', $response->json('order_items.0.price_at_order'));
    }

    public function test_create_order_fails_without_patient(): void
    {
        $test = Test::factory()->create();

        $this->postJson('/api/v1/orders', ['test_ids' => [$test->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['patient_id']);
    }

    public function test_create_order_fails_with_nonexistent_patient(): void
    {
        $test = Test::factory()->create();

        $this->postJson('/api/v1/orders', ['patient_id' => 9999, 'test_ids' => [$test->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['patient_id']);
    }

    public function test_create_order_fails_without_tests(): void
    {
        $patient = Patient::factory()->create();

        $this->postJson('/api/v1/orders', ['patient_id' => $patient->id, 'test_ids' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['test_ids']);
    }

    // ── READ ───────────────────────────────────────────────────────────────

    public function test_can_list_orders_with_patient_info(): void
    {
        $patient = Patient::factory()->create();
        $test    = Test::factory()->create();
        Order::factory()->for($patient)->hasOrderItems(1, ['test_id' => $test->id, 'price_at_order' => $test->price])->create();

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'order_uid', 'status', 'patient']]]);
    }

    public function test_can_filter_orders_by_status(): void
    {
        $patient  = Patient::factory()->create();
        $test     = Test::factory()->create();
        Order::factory()->for($patient)->create(['status' => 'pending']);
        Order::factory()->for($patient)->create(['status' => 'completed']);

        $response = $this->getJson('/api/v1/orders?status=pending');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_show_order_with_items_and_results(): void
    {
        $patient = Patient::factory()->create();
        $test    = Test::factory()->create();
        $order   = Order::factory()->for($patient)->hasOrderItems(1, ['test_id' => $test->id, 'price_at_order' => $test->price])->create();

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('id', $order->id)
            ->assertJsonStructure(['id', 'order_uid', 'patient', 'order_items']);
    }

    // ── STATUS UPDATE ──────────────────────────────────────────────────────

    public function test_can_update_order_status(): void
    {
        $patient = Patient::factory()->create();
        $test    = Test::factory()->create();
        $order   = Order::factory()->for($patient)->create(['status' => 'pending']);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'processing']);

        $response->assertOk()->assertJsonPath('status', 'processing');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing']);
    }

    public function test_update_status_rejects_invalid_value(): void
    {
        $patient = Patient::factory()->create();
        $order   = Order::factory()->for($patient)->create();

        $this->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'unknown'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    // ── CANCEL ─────────────────────────────────────────────────────────────

    public function test_deleting_order_marks_it_cancelled(): void
    {
        $patient = Patient::factory()->create();
        $order   = Order::factory()->for($patient)->create(['status' => 'pending']);

        $this->deleteJson("/api/v1/orders/{$order->id}")->assertNoContent();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }
}
