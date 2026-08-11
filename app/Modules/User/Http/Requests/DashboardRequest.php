<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'nullable|date_format:Y-m-d',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['date' => $this->route('date')]);
    }
}
