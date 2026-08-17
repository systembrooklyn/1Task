<?php

namespace App\Modules\RolePermission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemovePermissionsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'role_id'       => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ];
    }
}
