<?php

namespace App\Modules\Task\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'read_comments' => $this->read_comments ?? false,
            'is_starred' => $this->is_starred ?? false,
            'is_archived' => $this->is_archived ?? false,
            'project' => $this->project ? [
                'id' => $this->project->id,
                'name' => $this->project->name,
            ] : null,
            'department' => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null,
            'creator' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
                'last_name' => $this->creator->last_name ?? null,
                'ppUrl' => $this->creator->ppUrl ?? null,
            ],
            'assigned_user' => $this->assignedUser ?? $this->assignedUsers ?? [],
            'supervisor' => [
                'id' => $this->supervisor->id ?? null,
                'name' => $this->supervisor->name ?? null,
                'last_name' => $this->supervisor->last_name ?? null,
            ],
            'consult' => $this->consult ?? $this->consultUsers ?? [],
            'informer' => $this->informer ?? $this->informerUsers ?? [],
            'comments_count' => $this->comments_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
