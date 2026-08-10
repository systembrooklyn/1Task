<?php

namespace App\Modules\User\Http\Requests;

class RegisterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'         => 'required|max:25',
            'last_name'    => 'required|max:25',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|confirmed',
            'company_name' => 'required|string|max:255',
        ];
    }
}
