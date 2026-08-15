<?php

namespace App\Modules\Ticket\Models;

use App\Modules\Ticket\Enums\TicketCategory;
use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Company;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'category',
        'description',
        'email',
        'phone',
        'status',
        'priority',
        'closed_at',
        'ticket_number',
        'ip_address',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'category' => TicketCategory::class,
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function actions()
    {
        return $this->hasMany(TicketAction::class)->latest();
    }
}
