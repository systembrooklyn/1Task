<?php

namespace App\Modules\DailyTask\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => 'nullable|string',
            'status' => 'required|in:done,not_done',
            'task_found' => 'nullable|boolean',
        ];
    }
}
