<?php

namespace App\Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'         => 'required|file',
            'comment_text' => 'nullable|string',
        ];
    }
}
