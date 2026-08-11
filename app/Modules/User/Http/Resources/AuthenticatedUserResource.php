<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthenticatedUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user = $this->resource;

        return [
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'ppUrl'      => $user->profile?->ppUrl ?? null,
                'company'    => [
                    'id'   => $user->company->id,
                    'name' => $user->company->name,
                ],
                'departments' => $user->departments->map(fn($dept) => [
                    'id'   => $dept->id,
                    'name' => $dept->name,
                ]),
            ],
            'roles' => $user->roles->map(fn($role) => [
                'id'   => $role->id,
                'name' => $role->name,
            ]),
            'permissions' => $user->roles
                ->flatMap(fn($role) => $role->permissions->map(fn($perm) => [
                    'id'   => $perm->id,
                    'name' => $perm->name,
                ]))
                ->unique('id')
                ->values(),
        ];
    }
}
