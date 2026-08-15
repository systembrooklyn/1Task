<?php

namespace App\Modules\Ticket\Repositories\Contracts;

use App\Modules\Ticket\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

interface TicketRepositoryInterface
{
    public function create(array $data): Ticket;
    public function findById(int $id): ?Ticket;
    public function getByCompany(int $companyId, array $with = []): Collection;
}
