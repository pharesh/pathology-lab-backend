<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'test_ids'   => 'required|array|min:1',
            'test_ids.*' => 'required|exists:tests,id',
            'notes'      => 'nullable|string',
        ];
    }
}
