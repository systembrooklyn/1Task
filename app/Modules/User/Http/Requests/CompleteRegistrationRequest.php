<?php

namespace App\Modules\User\Http\Requests;

class CompleteRegistrationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password'  => 'required|string|min:8|confirmed',
        ];
    }
}