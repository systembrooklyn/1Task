<?php

namespace App\Modules\DailyTask\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => 'nullable|string',
            'rating' => 'required|integer|min:0|max:10',
            'label' => 'nullable|string',
            'task_for' => 'nullable|date',
        ];
    }
}
