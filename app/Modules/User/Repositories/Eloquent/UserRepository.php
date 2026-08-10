<?php

namespace App\Modules\User\Repositories\Eloquent;

use App\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function softDelete(User $user): bool
    {
        $user->is_deleted = 1;
        $user->deleted_at = now();
        return $user->save();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getUsersByCompany(int $companyId, array $with = []): Collection
    {
        return User::where('company_id', $companyId)
            ->where('is_deleted', 0)
            ->with($with)
            ->get();
    }

    public function syncRoles(User $user, array $roleIds): void
    {
        $user->roles()->sync($roleIds);
    }

    public function removeRoles(User $user, array $roleIds): void
    {
        $user->roles()->detach($roleIds);
    }

    public function syncRolesWithPivot(User $user, array $roleData): void
    {
        $user->roles()->sync($roleData);
    }
}
