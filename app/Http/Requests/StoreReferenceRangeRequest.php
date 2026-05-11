<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferenceRangeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'parameter_name' => 'required|string|max:100',
            'unit'           => 'nullable|string|max:30',
            'min_value'      => 'nullable|numeric',
            'max_value'      => 'nullable|numeric',
            'text_range'     => 'nullable|string|max:100',
            'gender_filter'  => 'sometimes|in:male,female,all',
            'age_min'        => 'nullable|integer|min:0',
            'age_max'        => 'nullable|integer|min:0',
            'age_unit'       => 'sometimes|in:years,months,days',
        ];
    }
}
