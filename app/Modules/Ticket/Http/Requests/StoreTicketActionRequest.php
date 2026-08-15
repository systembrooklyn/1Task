<?php

namespace App\Modules\Ticket\Http\Requests;

use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action_type'  => 'required|in:note,status,priority',
            'content'      => 'nullable|string|max:1000',
            'to_status'    => ['required_if:action_type,status', 'in:' . implode(',', array_column(TicketStatus::cases(), 'value'))],
            'to_priority'  => ['required_if:action_type,priority', 'in:' . implode(',', array_column(TicketPriority::cases(), 'value'))],
        ];
    }
}
