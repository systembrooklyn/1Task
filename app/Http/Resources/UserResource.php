<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'fireToken' => $this->fireToken ?? null,
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'phones' => UserPhoneResource::collection($this->whenLoaded('phones')),
            'links' => UserLinkResource::collection($this->whenLoaded('links')),
        ];
    }
}
