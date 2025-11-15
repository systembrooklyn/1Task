<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'from_status' => \App\Enums\TicketStatus::class,
        'to_status' => \App\Enums\TicketStatus::class,
        'from_priority' => \App\Enums\TicketPriority::class,
        'to_priority' => \App\Enums\TicketPriority::class,
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
