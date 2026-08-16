<?php

namespace App\Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkCommentReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment_id' => 'required|exists:task_comments,id',
        ];
    }
}