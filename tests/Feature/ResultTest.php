<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Patient;
use App\Models\ReferenceRange;
use App\Models\Result;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderWithTest(array $patientAttrs = [], array $testAttrs = []): array
    {
        $patient   = Patient::factory()->create(array_merge(['age' => 30, 'gender' => 'male'], $patientAttrs));
        $test      = Test::factory()->create($testAttrs);
        $order     = Order::factory()->for($patient)->create(['status' => 'pending']);
        $orderItem = OrderItem::factory()->create([
            'order_id'       => $order->id,
            'test_id'        => $test->id,
            'price_at_order' => $test->price,
        ]);

        return compact('patient', 'test', 'order', 'orderItem');
    }

    // ── BULK STORE ─────────────────────────────────────────────────────────

    public function test_can_bulk_enter_results_for_order(): void
    {
        ['order' => $order, 'orderItem' => $item] = $this->createOrderWithTest();

        $response = $this->postJson("/api/v1/orders/{$order->id}/results", [
            'entered_by' => 'Dr. Kumar',
            'results'    => [
                ['order_item_id' => $item->id, 'parameter_name' => 'Hemoglobin', 'observed_value' => '14.5', 'unit' => 'g/dL'],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.parameter_name', 'Hemoglobin')
            ->assertJsonPath('results.0.entered_by', 'Dr. Kumar');

        $this->assertDatabaseHas('results', ['parameter_name' => 'Hemoglobin', 'observed_value' => '14.5']);
    }

    public function test_order_status_becomes_completed_after_results_submitted(): void
    {
        ['order' => $order, 'orderItem' => $item] = $this->createOrderWithTest();

        $this->postJson("/api/v1/orders/{$order->id}/results", [
            'entered_by' => 'Lab Tech',
            'results'    => [
                ['order_item_id' => $item->id, 'parameter_name' => 'Hemoglobin', 'observed_value' => '13.0'],
            ],
        ]);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }

    public function test_order_item_status_becomes_result_entered(): void
    {
        ['order' => $order, 'orderItem' => $item] = $this->createOrderWithTest();

        $this->postJson("/api/v1/orders/{$order->id}/results", [
            'entered_by' => 'Lab Tech',
            'results'    => [
                ['order_item_id' => $item->id, 'parameter_name' => 'Hemoglobin', 'observed_value' => '13.0'],
            ],
        ]);

        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'status' => 'result_entered']);
    }

    public function test_result_fails_without_entered_by(): void
    {
        ['order' => $order, 'orderItem' => $item] = $this->createOrderWithTest();

        $this->postJson("/api/v1/orders/{$order->id}/results", [
            'results' => [
                ['order_item_id' => $item->id, 'parameter_name' => 'Hb', 'observed_value' => '12'],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors(['entered_by']);
    }

    // ── ABNORMAL DETECTION ─────────────────────────────────────────────────

    public function test_result_is_flagged_abnormal_when_above_max(): void
    {
        ['order' => $order, 'orderItem' => $item, 'test' => $test] = $this->createOrderWithTest(
            ['gender' => 'male'],
        );
        ReferenceRange::factory()->create([
            'test_id'        => $test->id,
            'parameter_name' => 'Hemoglobin',
            'min_value'      => 13.0,
            'max_value'      => 17.0,
            'gender_filter'  => 'male',
        ]);

        $response = $this->postJson("/api/v1/orders/{$order->id}/results", [
            'entered_by' => 'Tech',
            'results'    => [
                ['order_item_id' => $item->id, 'parameter_name' => 'Hemoglobin', 'observed_value' => '19.0'],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('results.0.is_abnormal', true);
    }

    public function test_result_is_flagged_abnormal_when_below_min(): void
    {
        ['order' => $order, 'orderItem' => $item, 'test' => $test] = $this->createOrderWithTest();
        ReferenceRange::factory()->create([
            'test_id'        => $test->id,
            'parameter_name' => 'Hemoglobin',
            'min_value'      => 13.0,
            'max_value'      => 17.0,
            'gender_filter'  => 'all',
        ]);

        $response = $this->postJson("/api/v1/orders/{$order->id}/results", [
            'entered_by' => 'Tech',
            'results'    => [
                ['order_item_id' => $item->id, 'parameter_name' => 'Hemoglobin', 'observed_value' => '10.0'],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('results.0.is_abnormal', true);
    }

    public function test_result_is_normal_within_range(): void
    {
        ['order' => $order, 'orderItem' => $item, 'test' => $test] = $this->createOrderWithTest();
        ReferenceRange::factory()->create([
            'test_id'        => $test->id,
            'parameter_name' => 'WBC Count',
            'min_value'      => 4.0,
            'max_value'      => 11.0,
            'gender_filter'  => 'all',
        ]);

        $response = $this->postJson("/api/v1/orders/{$order->id}/results", [
            'entered_by' => 'Tech',
            'results'    => [
                ['order_item_id' => $item->id, 'parameter_name' => 'WBC Count', 'observed_value' => '7.5'],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('results.0.is_abnormal', false);
    }

    public function test_result_without_matching_range_is_not_abnormal(): void
    {
        ['order' => $order, 'orderItem' => $item] = $this->createOrderWithTest();

        $response = $this->postJson("/api/v1/orders/{$order->id}/results", [
            'entered_by' => 'Tech',
            'results'    => [
                ['order_item_id' => $item->id, 'parameter_name' => 'Unknown Param', 'observed_value' => '999'],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('results.0.is_abnormal', false);
    }

    // ── UPDATE SINGLE RESULT ───────────────────────────────────────────────

    public function test_can_update_single_result(): void
    {
        ['order' => $order, 'orderItem' => $item] = $this->createOrderWithTest();
        $result = Result::factory()->create(['order_item_id' => $item->id]);

        $response = $this->putJson("/api/v1/results/{$result->id}", [
            'observed_value' => '15.0',
            'unit'           => 'g/dL',
            'entered_by'     => 'New Tech',
        ]);

        $response->assertOk()->assertJsonPath('observed_value', '15.0');
        $this->assertDatabaseHas('results', ['id' => $result->id, 'observed_value' => '15.0']);
    }
}
