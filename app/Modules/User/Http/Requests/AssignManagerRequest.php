<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'user_id'       => 'required|exists:users,id',
        ];
    }
}
