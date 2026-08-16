<?php

namespace App\Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Task\Validation\CompanyMembershipValidator;
use App\Models\Department;
use Illuminate\Validation\Validator;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|nullable',
            'description' => 'sometimes|string|nullable',
            'start_date' => 'sometimes|date',
            'deadline' => 'sometimes|date|nullable|after_or_equal:start_date',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'status' => 'sometimes|in:pending,rework,done,review,inProgress',
            'assigned_user_id' => 'sometimes|array|nullable',
            'assigned_user_id.*' => 'exists:users,id',
            'supervisor_user_id' => 'sometimes|exists:users,id|nullable',
            'consult_user_id' => 'sometimes|array|nullable',
            'consult_user_id.*' => 'exists:users,id',
            'inform_user_id' => 'sometimes|array|nullable',
            'inform_user_id.*' => 'exists:users,id',
            'project_id' => 'sometimes|exists:projects,id|nullable',
            'department_id' => 'sometimes|exists:departments,id|nullable',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $companyId = $this->user()->company_id;

            $userIds = array_merge(
                $this->input('assigned_user_id', []),
                $this->input('consult_user_id', []),
                $this->input('inform_user_id', []),
                [$this->input('supervisor_user_id')]
            );
            CompanyMembershipValidator::validate($validator, $userIds, $companyId);

            $departmentId = $this->input('department_id');
            if ($departmentId) {
                $department = Department::find($departmentId);
                if (!$department) {
                    $validator->errors()->add('department_id', 'Department not found.');
                } elseif ($department->company_id !== $companyId) {
                    $validator->errors()->add('department_id', 'Department does not belong to your company.');
                }
            }
        });
    }
}
