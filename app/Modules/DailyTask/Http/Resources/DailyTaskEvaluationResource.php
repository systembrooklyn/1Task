<?php

namespace App\Modules\DailyTask\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyTaskEvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'comment'       => $this->comment,
            'rating'        => $this->rating,
            'daily_task_id' => $this->daily_task_id,
            'task_for'      => $this->task_for,
            'created_at'    => $this->created_at->toDateTimeString(),
            'updated_at'    => $this->updated_at->toDateTimeString(),
            'user_id'       => $this->user_id,
        ];
    }
}
