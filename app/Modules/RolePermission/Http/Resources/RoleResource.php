<?php

namespace App\Modules\RolePermission\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'role_id'   => $this->id,
            'role_name' => $this->name,
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->map(function ($permission) {
                    return [
                        'permission_id'   => $permission->id,
                        'permission_name' => $permission->name,
                    ];
                });
            }),
        ];
    }
}
