<?php

namespace App\Modules\User\Http\Requests;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
