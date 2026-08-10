<?php

namespace App\Modules\User\Http\Requests;

class UpdateProfileRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->user()->id,
            'profile' => 'sometimes|array',
            'profile.ppUrl' => 'nullable|url',
            'profile.position' => 'nullable|string',
            'profile.country' => 'nullable|string',
            'profile.city' => 'nullable|string',
            'profile.state' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*.CC' => 'required_with:phones|string',
            'phones.*.phone' => 'required_with:phones|string',
            'links' => 'nullable|array',
            'links.*.icon' => 'required_with:links|string',
            'links.*.link' => 'required_with:links|url',
            'links.*.link_name' => 'nullable|string',
        ];
    }
}
