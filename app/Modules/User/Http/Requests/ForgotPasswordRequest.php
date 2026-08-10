<?php

namespace App\Modules\User\Http\Requests;

class ForgotPasswordRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }
}