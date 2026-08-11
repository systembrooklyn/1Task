<?php

namespace App\Modules\User\Http\Requests;

class RegisterViaInvitationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'token'    => 'required|string',
            'name'     => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
