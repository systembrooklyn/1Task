<?php

namespace App\Modules\DailyTask\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'task_type' => 'required|in:single,daily,weekly,monthly,last_day_of_month',
            'recurrent_days' => 'nullable|array',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'from' => 'required|date_format:H:i',
            'to' => 'required|date_format:H:i|after:from',
            'dept_id' => 'required|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'priority' => 'nullable|in:normal,critical',
        ];
    }
}
