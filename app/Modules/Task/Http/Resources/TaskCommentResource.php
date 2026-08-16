<?php

namespace App\Modules\Task\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment_text' => $this->comment_text,
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'last_name' => $this->user->last_name ?? null,
                'ppUrl' => $this->user->ppUrl ?? null,
            ],
            'replies_count' => $this->replies_count ?? $this->replies->count() ?? 0,
            'is_seen' => $this->is_seen ?? false,
            'seen_by' => $this->seen_by ?? [],
            'replies' => $this->replies ? TaskCommentReplyResource::collection($this->replies) : [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
