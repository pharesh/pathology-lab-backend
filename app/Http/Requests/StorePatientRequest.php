<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'age'         => 'required|integer|min:0|max:150',
            'age_unit'    => 'sometimes|in:years,months,days',
            'gender'      => 'required|in:male,female,other',
            'phone'       => 'required|string|max:15',
            'email'       => 'nullable|email|max:100',
            'address'     => 'nullable|string',
            'referred_by' => 'nullable|string|max:100',
        ];
    }
}
