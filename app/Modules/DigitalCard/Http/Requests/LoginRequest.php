<?php

namespace App\Modules\DigitalCard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:digital_card_users,email',
            'password' => 'required|string|min:8',
        ];
    }
}
