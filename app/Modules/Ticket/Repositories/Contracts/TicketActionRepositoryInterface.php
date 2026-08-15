<?php

namespace App\Modules\Ticket\Repositories\Contracts;

use App\Modules\Ticket\Models\TicketAction;

interface TicketActionRepositoryInterface
{
    public function create(array $data): TicketAction;
}
