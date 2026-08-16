<?php

namespace App\Modules\Plan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'currency' => 'required|string',
            'is_active' => 'boolean',
        ];
    }
}
