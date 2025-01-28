<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyTaskReportResource extends JsonResource
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
            'daily_task' => new DailyTaskResource($this->dailyTask),
            'submitted_by' => new UserResource($this->submittedBy),
            'notes' => $this->notes,
            'task_found' =>$this->task_found,
            'status' => $this->status,
            'report_date' => $this->created_at->toDateString(),
            'report_time' => $this->created_at->toTimeString(),
        ];
    }
}
