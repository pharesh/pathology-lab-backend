<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReferenceRangeRequest;
use App\Http\Requests\StoreTestRequest;
use App\Models\ReferenceRange;
use App\Models\Test;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Test::with('referenceRanges');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(StoreTestRequest $request): JsonResponse
    {
        $test = Test::create($request->validated());
        return response()->json($test->load('referenceRanges'), 201);
    }

    public function show(Test $test): JsonResponse
    {
        return response()->json($test->load('referenceRanges'));
    }

    public function update(StoreTestRequest $request, Test $test): JsonResponse
    {
        $test->update($request->validated());
        return response()->json($test->load('referenceRanges'));
    }

    public function destroy(Test $test): JsonResponse
    {
        $test->delete();
        return response()->json(null, 204);
    }

    public function storeRange(StoreReferenceRangeRequest $request, Test $test): JsonResponse
    {
        $range = $test->referenceRanges()->create($request->validated());
        return response()->json($range, 201);
    }

    public function updateRange(StoreReferenceRangeRequest $request, Test $test, ReferenceRange $range): JsonResponse
    {
        $range->update($request->validated());
        return response()->json($range);
    }

    public function destroyRange(Test $test, ReferenceRange $range): JsonResponse
    {
        $range->delete();
        return response()->json(null, 204);
    }
}
