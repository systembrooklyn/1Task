<?php

namespace App\Modules\Ticket\Models;

use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class TicketAction extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'action_type',
        'content',
        'from_status',
        'to_status',
        'from_priority',
        'to_priority',
        'ip_address'
    ];

    protected $casts = [
        'from_status' => TicketStatus::class,
        'to_status' => TicketStatus::class,
        'from_priority' => TicketPriority::class,
        'to_priority' => TicketPriority::class,
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
