<?php

namespace App\Modules\User\Http\Requests;

class FireTokenRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'fireToken' => 'required|string|max:255',
        ];
    }
}
