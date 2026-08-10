<?php

namespace App\Modules\User\Repositories\Contracts;

use App\Models\Invitation;
use Illuminate\Database\Eloquent\Collection;

interface InvitationRepositoryInterface
{
    public function create(array $data): Invitation;
    public function delete(Invitation $invitation): bool;
    public function findByToken(string $token): ?Invitation;
    public function findByEmail(string $email): ?Invitation;
    public function findExpiredByEmail(string $email): ?Invitation;
    public function getPendingByCompany(int $companyId): Collection;
}
