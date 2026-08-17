<?php

namespace App\Modules\RolePermission\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Role::class);
    }

    public function rules()
    {
        return [
            'name'        => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }
}
