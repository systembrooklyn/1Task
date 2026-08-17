<?php

namespace App\Modules\DigitalCard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDigitalCardRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'nullable|string|max:255',
            'desc' => 'nullable|string',
            'profile_pic_url' => 'nullable|url',
            'back_pic_link' => 'nullable|url',
            'social_links' => 'nullable|array',
            'social_links.*.name' => 'nullable|string',
            'social_links.*.icon' => 'nullable|string',
            'social_links.*.link' => 'nullable|url',
            'phones' => 'nullable|array',
            'phones.*.phone' => 'nullable|string',
        ];
    }
}
