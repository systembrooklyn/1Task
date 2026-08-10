<?php

namespace App\Modules\User\Http\Requests;

class ResetPasswordRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }
}