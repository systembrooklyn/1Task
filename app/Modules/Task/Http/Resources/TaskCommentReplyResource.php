<?php

namespace App\Modules\Task\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCommentReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reply_text' => $this->reply_text,
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'last_name' => $this->user->last_name ?? null,
                'ppUrl' => $this->user->ppUrl ?? null,
            ],
            'is_seen' => $this->is_seen ?? false,
            'seen_by' => $this->seen_by ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
