<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBillRequest;
use App\Models\Bill;
use App\Models\Order;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function __construct(private BillingService $billingService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Bill::with(['order.patient']);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(StoreBillRequest $request): JsonResponse
    {
        $order = Order::with('orderItems')->findOrFail($request->order_id);
        $bill = $this->billingService->createFromOrder($order, $request->validated());
        return response()->json($bill->load('order.patient'), 201);
    }

    public function show(Bill $bill): JsonResponse
    {
        $bill->load(['order.patient', 'order.orderItems.test']);
        return response()->json($bill);
    }

    public function update(Request $request, Bill $bill): JsonResponse
    {
        if ($bill->amount_paid > 0) {
            return response()->json(['message' => 'Cannot change discount after payment has been recorded.'], 422);
        }

        $validated = $request->validate([
            'discount_type'  => 'nullable|in:flat,percent',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $subtotal      = (float) $bill->subtotal;
        $discountType  = $validated['discount_type'] ?? null;
        $discountValue = (float) $validated['discount_value'];

        $total = match ($discountType) {
            'percent' => $subtotal - ($subtotal * $discountValue / 100),
            'flat'    => max(0, $subtotal - $discountValue),
            default   => $subtotal,
        };

        if ($discountType === null) {
            $discountValue = 0;
        }

        $bill->update([
            'discount_type'  => $discountType,
            'discount_value' => $discountValue,
            'total_amount'   => $total,
        ]);

        return response()->json($bill->fresh()->load(['order.patient', 'order.orderItems.test']));
    }

    public function recordPayment(Request $request, Bill $bill): JsonResponse
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,upi,card,other',
        ]);

        $bill = $this->billingService->recordPayment($bill, (float) $request->amount, $request->payment_method);

        return response()->json($bill);
    }
}
