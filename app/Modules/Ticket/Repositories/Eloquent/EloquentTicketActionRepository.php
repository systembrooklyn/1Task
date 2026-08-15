<?php

namespace App\Modules\Ticket\Repositories\Eloquent;

use App\Modules\Ticket\Models\TicketAction;
use App\Modules\Ticket\Repositories\Contracts\TicketActionRepositoryInterface;

class EloquentTicketActionRepository implements TicketActionRepositoryInterface
{
    public function create(array $data): TicketAction
    {
        return TicketAction::create($data);
    }
}
