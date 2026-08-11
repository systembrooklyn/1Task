<?php

namespace App\Modules\DailyTask\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterDailyTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page'   => 'nullable|integer|min:1',
            'sort_by'    => 'nullable|in:task_no,created_at,start_date',
            'type_of'    => 'nullable|in:asc,desc',
            'dept_ids'   => 'nullable|array',
            'dept_ids.*' => 'exists:departments,id',
            'task_type'  => 'nullable|in:single,daily,weekly,monthly,last_day_of_month',
            'active'     => 'nullable|boolean',
        ];
    }
}
