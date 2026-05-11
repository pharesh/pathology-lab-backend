<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $testId = $this->route('test')?->id;

        return [
            'test_code'        => 'required|string|max:20|unique:tests,test_code,' . $testId,
            'test_name'        => 'required|string|max:150',
            'category'         => 'required|string|max:100',
            'sample_type'      => 'required|in:blood,urine,stool,swab,other',
            'price'            => 'required|numeric|min:0',
            'turnaround_hours' => 'sometimes|integer|min:1',
            'is_active'        => 'sometimes|boolean',
        ];
    }
}
