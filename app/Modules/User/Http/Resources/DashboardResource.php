<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this['message'] ?? 'Dashboard retrieved successfully',
            'Emps' => $this['Emps'] ?? [],
            'Projects' => $this['Projects'] ?? [],
            'AllDailyTasks' => $this['AllDailyTasks'],
            'DailyTasks' => $this['DailyTasks'] ?? [],
            'Departments' => $this['Departments'] ?? [],
            'Evaluations' => $this['Evaluations'] ?? [],
            'Tasks' => $this['Tasks'] ?? [],
        ];
    }

    /**
     * Customize the response to remove the "data" wrapper.
     */
    public static function withoutWrapping(): void
    {
        parent::withoutWrapping();
    }
}
