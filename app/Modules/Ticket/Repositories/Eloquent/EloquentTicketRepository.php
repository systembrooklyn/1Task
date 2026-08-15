<?php

namespace App\Modules\Ticket\Repositories\Eloquent;

use App\Modules\Ticket\Models\Ticket;
use App\Modules\Ticket\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function findById(int $id): ?Ticket
    {
        return Ticket::find($id);
    }

    public function getByCompany(int $companyId, array $with = []): Collection
    {
        return Ticket::where('company_id', $companyId)->with($with)->get();
    }
}
