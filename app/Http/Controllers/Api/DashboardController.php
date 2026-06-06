<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Order;
use App\Models\Patient;
use Carbon\Carbon;
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

        $todayStart  = Carbon::today()->startOfDay();
        $todayEnd    = Carbon::today()->endOfDay();
        $monthStart  = Carbon::now()->startOfMonth();
        $monthEnd    = Carbon::now()->endOfMonth();

        return response()->json([
            'patients_today'   => Patient::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'orders_today'     => Order::whereBetween('ordered_at',  [$todayStart, $todayEnd])->count(),
            'pending_orders'   => Order::whereIn('status', ['pending', 'processing'])->count(),
            'unpaid_bills'     => Bill::where('payment_status', '!=', 'paid')->count(),
            'revenue_today'    => Bill::whereBetween('created_at', [$todayStart, $todayEnd])->sum('amount_paid'),
            'revenue_month'    => Bill::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount_paid'),
            'total_patients'   => Patient::count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'recent_orders'    => $recentOrders,
        ]);
    }
}
