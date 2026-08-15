<?php

namespace App\Modules\Ticket\Http\Requests;

use App\Modules\Ticket\Enums\TicketCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'category'    => ['required', 'in:' . implode(',', array_column(TicketCategory::cases(), 'value'))],
            'description' => 'required|string',
            'email'       => 'required|email',
            'phone'       => 'nullable|string|max:25',
        ];
    }
}
