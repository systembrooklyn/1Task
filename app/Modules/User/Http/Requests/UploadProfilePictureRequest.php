<?php

namespace App\Modules\User\Http\Requests;

class UploadProfilePictureRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
