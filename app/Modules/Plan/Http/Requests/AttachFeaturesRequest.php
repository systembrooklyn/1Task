<?php

namespace App\Modules\Plan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachFeaturesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'features' => 'required|array',
            'features.*.id' => 'exists:features,id',
            'features.*.value' => 'required|integer',
            'features.*.resettable' => 'boolean',
        ];
    }
}
