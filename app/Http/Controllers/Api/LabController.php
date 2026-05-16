<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LabController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user()->lab);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:150',
            'email'              => 'nullable|email|max:100',
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'registration_no'    => 'nullable|string|max:50',
            'doctor_name'        => 'nullable|string|max:150',
            'doctor_designation' => 'nullable|string|max:150',
        ]);

        $lab = $request->user()->lab;
        $lab->update($validated);

        return response()->json($lab);
    }

    public function uploadSignature(Request $request): JsonResponse
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $lab = $request->user()->lab;

        if ($lab->signature_image) {
            Storage::disk('public')->delete($lab->signature_image);
        }

        $path = $request->file('signature')->store('signatures', 'public');
        $lab->update(['signature_image' => $path]);

        return response()->json($lab);
    }

    public function deleteSignature(Request $request): JsonResponse
    {
        $lab = $request->user()->lab;

        if ($lab->signature_image) {
            Storage::disk('public')->delete($lab->signature_image);
            $lab->update(['signature_image' => null]);
        }

        return response()->json($lab);
    }
}
