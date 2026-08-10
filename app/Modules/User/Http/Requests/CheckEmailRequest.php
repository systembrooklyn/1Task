<?php

namespace App\Modules\User\Http\Requests;

class CheckEmailRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}
