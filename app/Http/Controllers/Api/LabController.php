<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user()->lab);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:150',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string',
            'registration_no' => 'nullable|string|max:50',
        ]);

        $lab = $request->user()->lab;
        $lab->update($validated);

        return response()->json($lab);
    }
}
