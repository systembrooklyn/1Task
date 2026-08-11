<?php

namespace App\Modules\Project\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'sometimes|string',
            'desc'           => 'nullable|string',
            'status'         => 'sometimes|boolean',
            'leader_id'      => 'nullable|exists:users,id',
            'deadline'       => 'nullable|date',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'start_date'     => 'nullable|date',
        ];
    }
}
