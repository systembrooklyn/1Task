<?php

namespace App\Modules\Task\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'task_id'             => $this->task_id,
            'uploaded_by_user_id' => $this->uploaded_by_user_id,
            'file_path'           => $this->file_path,
            'file_name'           => $this->file_name,
            'file_size'           => $this->file_size,
            'download_url'        => $this->download_url,
            'created_at'          => $this->created_at?->toDateTimeString(),
            'updated_at'          => $this->updated_at?->toDateTimeString(),
        ];
    }
}
