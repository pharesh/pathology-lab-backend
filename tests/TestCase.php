<?php

namespace Tests;

use App\Models\Lab;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $lab  = Lab::factory()->create();
        $user = User::factory()->create(['lab_id' => $lab->id, 'role' => 'admin']);
        $this->actingAs($user, 'sanctum');

        // Give the test lab an active subscription so CheckSubscription never blocks tests
        $plan = Plan::firstOrCreate(
            ['slug' => 'pro'],
            [
                'name'            => 'Pro',
                'price_monthly'   => 0,
                'price_yearly'    => 0,
                'is_active'       => true,
                'has_pdf_reports' => true,
                'sort_order'      => 0,
            ]
        );

        Subscription::create([
            'lab_id'               => $lab->id,
            'plan_id'              => $plan->id,
            'status'               => 'active',
            'current_period_start' => now(),
            'current_period_end'   => now()->addYear(),
        ]);
    }
}
