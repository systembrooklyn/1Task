<?php

namespace App\Modules\User\Repositories\Eloquent;

use App\Models\Invitation;
use App\Modules\User\Repositories\Contracts\InvitationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentInvitationRepository implements InvitationRepositoryInterface
{
    public function create(array $data): Invitation
    {
        return Invitation::create($data);
    }

    public function delete(Invitation $invitation): bool
    {
        return $invitation->delete();
    }

    public function findByToken(string $token): ?Invitation
    {
        return Invitation::where('token', $token)->first();
    }

    public function findByEmail(string $email): ?Invitation
    {
        return Invitation::where('email', $email)->first();
    }

    public function findExpiredByEmail(string $email): ?Invitation
    {
        return Invitation::where('email', $email)
            ->where('expires_at', '<', now())
            ->first();
    }

    public function getPendingByCompany(int $companyId): Collection
    {
        return Invitation::where('company_id', $companyId)
            ->where('is_accepted', 0)
            ->get();
    }
}
