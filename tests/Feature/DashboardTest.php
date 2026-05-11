<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_returns_all_required_keys(): void
    {
        $response = $this->getJson('/api/v1/dashboard/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'patients_today',
                'orders_today',
                'pending_orders',
                'unpaid_bills',
                'revenue_today',
                'revenue_month',
                'total_patients',
                'completed_orders',
            ]);
    }

    public function test_stats_counts_are_zero_on_empty_database(): void
    {
        $response = $this->getJson('/api/v1/dashboard/stats');

        $response->assertOk()
            ->assertJson([
                'total_patients'   => 0,
                'pending_orders'   => 0,
                'completed_orders' => 0,
            ]);
    }

    public function test_stats_counts_patients_created_today(): void
    {
        Patient::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/dashboard/stats');

        $response->assertOk()
            ->assertJsonPath('patients_today', 3)
            ->assertJsonPath('total_patients', 3);
    }
}
