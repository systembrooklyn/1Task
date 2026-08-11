<?php

namespace App\Modules\User\Services;

use App\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepo,
    ) {}

    public function updateUser(User $user, array $data): bool
    {
        return $this->userRepo->update($user, $data);
    }

    public function deleteUser(User $user, bool $soft = true): bool
    {
        $user->roles()->detach();
        $user->departments()->detach();
        $user->tokens()->delete();

        if ($soft) {
            return $this->userRepo->softDelete($user);
        }
        return $this->userRepo->delete($user);
    }

    public function updateFireToken(User $user, string $fireToken): bool
    {
        return $this->userRepo->update($user, ['fireToken' => $fireToken]);
    }

    public function assignRoles(User $user, array $roleIds): void
    {
        $this->userRepo->syncRoles($user, $roleIds);
    }

    public function removeRoles(User $user, array $roleIds): void
    {
        $this->userRepo->removeRoles($user, $roleIds);
    }

    public function getCompanyUsers(int $companyId, array $with = ['departments', 'roles', 'profile'])
    {
        return $this->userRepo->getUsersByCompany($companyId, $with);
    }

    /**
     * Assign roles with pivot data (e.g., company_id).
     */
    public function assignRolesWithPivot(User $user, array $roleData): void
    {
        $this->userRepo->syncRolesWithPivot($user, $roleData);
    }
}
