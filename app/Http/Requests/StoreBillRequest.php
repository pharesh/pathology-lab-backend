<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id'       => 'required|exists:orders,id|unique:bills,order_id',
            'discount_type'  => 'nullable|in:flat,percent',
            'discount_value' => 'nullable|numeric|min:0',
        ];
    }
}
