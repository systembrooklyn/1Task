<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyUserResource extends JsonResource
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
            'id'               => $user->id,
            'name'             => $user->name,
            'last_name'        => $user->last_name ?? null,
            'email'            => $user->email,
            'ppUrl'            => $user->profile?->ppUrl ?? null,
            'position'         => $user->profile?->position ?? null,
            'fireToken'        => $user->fireToken,
            'departments'      => $user->departments->pluck('name'),
            'roles'            => $user->roles->pluck('name'),
            'departments_ids'  => $user->departments->map(fn($d) => [
                'id'   => $d->id,
                'name' => $d->name,
            ]),
            'roles_ids'        => $user->roles->map(fn($r) => [
                'id'   => $r->id,
                'name' => $r->name,
            ]),
        ];
    }
}
