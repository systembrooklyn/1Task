<?php

namespace App\Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Task;
use Illuminate\Validation\Validator;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment_text' => 'required|string',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $taskId = $this->route('id');
            $task = Task::find($taskId);

            if (!$task) {
                $validator->errors()->add('task', 'Task not found.');
                return;
            }

            $user = $this->user();
            if ($user->company_id !== $task->company_id) {
                $validator->errors()->add('user', 'You do not belong to the same company as this task.');
            }
        });
    }
}