<?php

namespace App\Modules\User\Http\Requests;

class InviteRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}