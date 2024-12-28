<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'task_no' => $this->task_no,
            'task_name' => $this->task_name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'task_type' => $this->task_type,
            'recurrent_days' => $this->recurrent_days,
            'day_of_month' => $this->day_of_month,
            'from' => $this->from,
            'to' => $this->to,
            'company_id' => $this->company_id,
            'department' => [
                'dept_id' => $this->dept_id,
                'department_name' => $this->department->name ?? 'N/A',
            ],
            'created_by' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? 'N/A',
            ],
            'assigned_to' => [
                'id' => $this->assignee->id ?? null,
                'name' => $this->assignee->name ?? 'N/A',
            ],
            'note' => $this->note,
            'status' => $this->status,
            'updated_by' =>
            [
                'id' => $this->updated_by->id ?? null,
                'name' => $this->updated_by->name ?? 'N/A',
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
