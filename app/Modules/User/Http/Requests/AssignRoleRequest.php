<?php

namespace App\Modules\User\Http\Requests;

class AssignRoleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id'   => 'required|exists:users,id',
            'role_ids'  => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ];
    }
}
