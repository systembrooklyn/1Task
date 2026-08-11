<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignDepartmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_ids'   => 'required|array',
            'department_ids.*' => 'integer|exists:departments,id',
        ];
    }
}
