<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Patient::query();

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q
                ->where('name', 'like', $s)
                ->orWhere('phone', 'like', $s)
                ->orWhere('patient_uid', 'like', $s)
            );
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Patient::create($request->validated());
        return response()->json($patient, 201);
    }

    public function show(Patient $patient): JsonResponse
    {
        $patient->load(['orders.bill', 'orders.orderItems.test']);
        return response()->json($patient);
    }

    public function update(StorePatientRequest $request, Patient $patient): JsonResponse
    {
        $patient->update($request->validated());
        return response()->json($patient);
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();
        return response()->json(null, 204);
    }
}
