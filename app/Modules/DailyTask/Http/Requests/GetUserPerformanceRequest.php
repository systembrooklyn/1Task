<?php

namespace App\Modules\DailyTask\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetUserPerformanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ];
    }
}
