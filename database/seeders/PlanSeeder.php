<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                 => 'Trial',
                'slug'                 => 'trial',
                'price_monthly'        => 0,
                'price_yearly'         => 0,
                'max_patients'         => 50,
                'max_users'            => 2,
                'max_orders_per_month' => 100,
                'has_pdf_reports'      => true,
                'description'          => '14-day free trial. No credit card required.',
                'sort_order'           => 0,
            ],
            [
                'name'                 => 'Basic',
                'slug'                 => 'basic',
                'price_monthly'        => 999,
                'price_yearly'         => 9990,
                'max_patients'         => 500,
                'max_users'            => 5,
                'max_orders_per_month' => null,
                'has_pdf_reports'      => true,
                'description'          => 'For small to mid-size labs.',
                'sort_order'           => 1,
            ],
            [
                'name'                 => 'Pro',
                'slug'                 => 'pro',
                'price_monthly'        => 2499,
                'price_yearly'         => 24990,
                'max_patients'         => null,
                'max_users'            => 15,
                'max_orders_per_month' => null,
                'has_pdf_reports'      => true,
                'description'          => 'Unlimited patients. For growing labs.',
                'sort_order'           => 2,
            ],
            [
                'name'                 => 'Enterprise',
                'slug'                 => 'enterprise',
                'price_monthly'        => 4999,
                'price_yearly'         => 49990,
                'max_patients'         => null,
                'max_users'            => null,
                'max_orders_per_month' => null,
                'has_pdf_reports'      => true,
                'description'          => 'Unlimited everything. Priority support.',
                'sort_order'           => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
