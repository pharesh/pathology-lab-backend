<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Result;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function bulkStore(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'entered_by'                  => 'required|string|max:100',
            'results'                     => 'required|array',
            'results.*.order_item_id'     => 'required|exists:order_items,id',
            'results.*.parameter_name'    => 'required|string|max:100',
            'results.*.observed_value'    => 'required|string|max:100',
            'results.*.unit'              => 'nullable|string|max:30',
            'results.*.remarks'           => 'nullable|string',
        ]);

        $order->load(['patient', 'orderItems.test.referenceRanges']);
        $patient = $order->patient;

        $created = [];
        foreach ($request->results as $resultData) {
            $orderItem = $order->orderItems->firstWhere('id', $resultData['order_item_id']);
            if (!$orderItem) continue;

            $ranges = $orderItem->test->referenceRanges->all();
            $matchedRange = $this->reportService->matchReferenceRange(
                $resultData['parameter_name'],
                $patient->age,
                $patient->age_unit,
                $patient->gender,
                $ranges
            );

            $result = new Result($resultData);
            $result->entered_by  = $request->entered_by;
            $result->entered_at  = now();
            $result->is_abnormal = $matchedRange ? $result->computeIsAbnormal($matchedRange) : false;
            $result->save();

            $orderItem->update(['status' => 'result_entered']);
            $created[] = $result;
        }

        // Only mark completed when every order item has results entered
        $hasUnenteredItems = $order->orderItems()->where('status', '!=', 'result_entered')->exists();
        $order->update(['status' => $hasUnenteredItems ? 'processing' : 'completed']);

        return response()->json(['results' => $created], 201);
    }

    public function update(Request $request, Result $result): JsonResponse
    {
        $request->validate([
            'observed_value' => 'required|string|max:100',
            'unit'           => 'nullable|string|max:30',
            'remarks'        => 'nullable|string',
            'entered_by'     => 'required|string|max:100',
        ]);

        $result->fill($request->only('observed_value', 'unit', 'remarks', 'entered_by'));
        $result->entered_at = now();

        // Recompute abnormal flag after value change
        $result->load('orderItem.test.referenceRanges');
        $ranges = $result->orderItem?->test?->referenceRanges ?? collect();
        $range = $ranges->first(fn($r) => strtolower($r->parameter_name) === strtolower($result->parameter_name));
        $result->is_abnormal = $range ? $result->computeIsAbnormal($range) : false;

        $result->save();

        return response()->json($result);
    }
}
