<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Plan::orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'slug'                 => 'required|string|unique:plans,slug',
            'price_monthly'        => 'required|numeric|min:0',
            'price_yearly'         => 'required|numeric|min:0',
            'max_patients'         => 'nullable|integer|min:1',
            'max_users'            => 'nullable|integer|min:1',
            'max_orders_per_month' => 'nullable|integer|min:1',
            'has_pdf_reports'      => 'boolean',
            'description'          => 'nullable|string',
            'is_active'            => 'boolean',
            'sort_order'           => 'integer',
        ]);

        return response()->json(Plan::create($data), 201);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $data = $request->validate([
            'name'                 => 'sometimes|string|max:100',
            'slug'                 => 'sometimes|string|unique:plans,slug,' . $plan->id,
            'price_monthly'        => 'sometimes|numeric|min:0',
            'price_yearly'         => 'sometimes|numeric|min:0',
            'max_patients'         => 'nullable|integer|min:1',
            'max_users'            => 'nullable|integer|min:1',
            'max_orders_per_month' => 'nullable|integer|min:1',
            'has_pdf_reports'      => 'boolean',
            'description'          => 'nullable|string',
            'is_active'            => 'boolean',
            'sort_order'           => 'integer',
        ]);

        $plan->update($data);
        return response()->json($plan);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        if ($plan->subscriptions()->whereNull('cancelled_at')->exists()) {
            return response()->json(['message' => 'Cannot delete a plan with active subscriptions.'], 422);
        }
        $plan->delete();
        return response()->json(null, 204);
    }
}
