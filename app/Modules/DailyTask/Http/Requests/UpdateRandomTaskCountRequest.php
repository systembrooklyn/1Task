<?php

namespace App\Modules\DailyTask\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRandomTaskCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'random_daily_task_count' => 'required|integer',
        ];
    }
}
