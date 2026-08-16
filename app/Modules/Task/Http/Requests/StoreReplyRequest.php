<?php

namespace App\Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TaskComment;
use Illuminate\Validation\Validator;

class StoreReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply_text' => 'required|string',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $commentId = $this->route('commentId');
            $comment = TaskComment::with('task')->find($commentId);

            if (!$comment) {
                $validator->errors()->add('comment', 'Comment not found.');
                return;
            }

            $user = $this->user();
            if ($user->company_id !== $comment->task->company_id) {
                $validator->errors()->add('user', 'You do not belong to the same company as this task.');
            }
        });
    }
}
