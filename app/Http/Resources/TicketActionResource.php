<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketActionResource extends JsonResource
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
            'action_type' => $this->action_type,
            'content' => $this->content,
            'from_status' => $this->from_status?->value,
            'to_status' => $this->to_status?->value,
            'from_priority' => $this->from_priority?->value,
            'to_priority' => $this->to_priority?->value,
            'ip_address' => $this->ip_address,
            'ticket' => new TicketResource($this->whenLoaded('ticket')),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
