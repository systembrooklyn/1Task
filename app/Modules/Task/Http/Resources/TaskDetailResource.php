<?php

namespace App\Modules\Task\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'deadline' => $this->deadline,
            'priority' => $this->priority,
            'status' => $this->status,
            'company' => [
                'id' => $this->company->id ?? null,
                'name' => $this->company->name ?? null,
            ],
            'project' => [
                'id' => $this->project->id ?? null,
                'name' => $this->project->name ?? null,
            ],
            'department' => [
                'id' => $this->department->id ?? null,
                'name' => $this->department->name ?? null,
            ],
            'creator' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
                'last_name' => $this->creator->last_name ?? null,
                'ppUrl' => $this->creator->ppUrl ?? null,
            ],
            'supervisor' => [
                'id' => $this->supervisor->id ?? null,
                'name' => $this->supervisor->name ?? null,
                'last_name' => $this->supervisor->last_name ?? null,
            ],
            'assignedUsers' => $this->assignedUsers ?? [],
            'consultUsers' => $this->consultUsers ?? [],
            'informerUsers' => $this->informerUsers ?? [],
            'attachments' => $this->attachments ? TaskAttachmentResource::collection($this->attachments) : [],
            'comments' => TaskCommentResource::collection($this->comments ?? []),
            'revisions' => TaskRevisionResource::collection($this->revisions ?? []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
