<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Order;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $recentOrders = Order::with(['patient', 'bill'])
            ->orderByDesc('ordered_at')
            ->limit(8)
            ->get()
            ->map(fn ($o) => [
                'id'           => $o->id,
                'order_uid'    => $o->order_uid,
                'patient_name' => $o->patient?->name,
                'patient_uid'  => $o->patient?->patient_uid,
                'status'       => $o->status,
                'payment'      => $o->bill?->payment_status ?? 'no bill',
                'bill_id'      => $o->bill?->id,
                'ordered_at'   => $o->ordered_at,
            ]);

        return response()->json([
            'patients_today'   => Patient::whereDate('created_at', today())->count(),
            'orders_today'     => Order::whereDate('ordered_at', today())->count(),
            'pending_orders'   => Order::whereIn('status', ['pending', 'processing'])->count(),
            'unpaid_bills'     => Bill::where('payment_status', '!=', 'paid')->count(),
            'revenue_today'    => Bill::whereDate('created_at', today())->sum('amount_paid'),
            'revenue_month'    => Bill::whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->sum('amount_paid'),
            'total_patients'   => Patient::count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'recent_orders'    => $recentOrders,
        ]);
    }
}
