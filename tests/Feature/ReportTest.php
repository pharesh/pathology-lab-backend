<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Patient;
use App\Models\ReferenceRange;
use App\Models\Result;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function createFullOrder(): array
    {
        $patient   = Patient::factory()->create(['name' => 'Report Patient', 'age' => 35, 'gender' => 'male']);
        $test      = Test::factory()->create(['test_name' => 'CBC', 'test_code' => 'CBC001']);
        $range     = ReferenceRange::factory()->create([
            'test_id'        => $test->id,
            'parameter_name' => 'Hemoglobin',
            'unit'           => 'g/dL',
            'min_value'      => 13.0,
            'max_value'      => 17.0,
            'gender_filter'  => 'all',
        ]);
        $order     = Order::factory()->for($patient)->create(['status' => 'completed']);
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id, 'test_id' => $test->id, 'price_at_order' => $test->price, 'status' => 'result_entered']);
        Result::factory()->create([
            'order_item_id'  => $orderItem->id,
            'parameter_name' => 'Hemoglobin',
            'observed_value' => '10.2',
            'unit'           => 'g/dL',
            'is_abnormal'    => true,
            'entered_by'     => 'Lab Tech',
        ]);
        $bill = Bill::factory()->create(['order_id' => $order->id, 'subtotal' => $test->price, 'total_amount' => $test->price]);

        return compact('patient', 'test', 'order', 'orderItem', 'bill');
    }

    // ── PDF REPORT ─────────────────────────────────────────────────────────

    public function test_can_download_patient_report_as_pdf(): void
    {
        ['order' => $order] = $this->createFullOrder();

        $response = $this->get("/api/v1/orders/{$order->id}/report");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_report_download_has_correct_filename(): void
    {
        ['order' => $order, 'patient' => $patient] = $this->createFullOrder();

        $response = $this->get("/api/v1/orders/{$order->id}/report");

        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString($patient->patient_uid, $disposition);
        $this->assertStringContainsString('.pdf', $disposition);
    }

    public function test_report_returns_404_for_nonexistent_order(): void
    {
        $this->get('/api/v1/orders/9999/report')
            ->assertNotFound();
    }

    // ── PDF INVOICE ────────────────────────────────────────────────────────

    public function test_can_download_invoice_as_pdf(): void
    {
        ['order' => $order] = $this->createFullOrder();

        $response = $this->get("/api/v1/orders/{$order->id}/invoice");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_invoice_download_has_correct_filename(): void
    {
        ['order' => $order, 'bill' => $bill] = $this->createFullOrder();

        $response = $this->get("/api/v1/orders/{$order->id}/invoice");

        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString($bill->bill_uid, $disposition);
        $this->assertStringContainsString('.pdf', $disposition);
    }

    public function test_invoice_returns_404_for_nonexistent_order(): void
    {
        $this->get('/api/v1/orders/9999/invoice')
            ->assertNotFound();
    }
}
