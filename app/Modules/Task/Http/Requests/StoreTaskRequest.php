<?php

namespace App\Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Task\Validation\CompanyMembershipValidator;
use App\Models\Department;
use Illuminate\Validation\Validator;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_user_id' => 'required|array',
            'assigned_user_id.*' => 'exists:users,id',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'consult_user_id' => 'nullable|array',
            'consult_user_id.*' => 'exists:users,id',
            'inform_user_id' => 'nullable|array',
            'inform_user_id.*' => 'exists:users,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'project_id' => 'nullable|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'sometimes|in:pending,rework,done,review,inProgress',
            // New Attachment Rules
            'main_task_attachments'     => 'nullable|array',
            'main_task_attachments.*'   => 'file|max:102400',
            'remove_main_attachments'   => 'nullable|array',
            'remove_main_attachments.*' => 'integer',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) return;

            $companyId = $this->user()->company_id;

            // Bulletproof casting for JSON arrays AND Form-Data strings/nulls
            $assigned   = $this->input('assigned_user_id', []);
            $consult    = $this->input('consult_user_id', []);
            $inform     = $this->input('inform_user_id', []);
            $supervisor = $this->input('supervisor_user_id');

            $userIds = array_merge(
                is_array($assigned) ? $assigned : ($assigned ? [$assigned] : []),
                is_array($consult)  ? $consult  : ($consult  ? [$consult]  : []),
                is_array($inform)   ? $inform   : ($inform   ? [$inform]   : []),
                $supervisor ? [$supervisor] : []
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
