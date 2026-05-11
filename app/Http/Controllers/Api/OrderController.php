<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Test;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['patient', 'bill']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('ordered_at', $request->date);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = Order::create([
            'patient_id' => $data['patient_id'],
            'notes'      => $data['notes'] ?? null,
            'status'     => 'pending',
        ]);

        $tests = Test::whereIn('id', $data['test_ids'])->get()->keyBy('id');

        foreach ($data['test_ids'] as $testId) {
            $order->orderItems()->create([
                'test_id'        => $testId,
                'price_at_order' => $tests[$testId]->price,
                'status'         => 'pending',
            ]);
        }

        return response()->json($order->load(['patient', 'orderItems.test']), 201);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'patient',
            'bill',
            'orderItems.test.referenceRanges',
            'orderItems.results',
        ]);
        return response()->json($order);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate(['status' => 'required|in:pending,processing,completed,cancelled']);
        $order->update(['status' => $request->status]);
        return response()->json($order);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->update(['status' => 'cancelled']);
        return response()->json(null, 204);
    }
}
