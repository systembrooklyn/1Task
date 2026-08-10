<?php

namespace App\Modules\User\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function update(User $user, array $data): bool;
    public function delete(User $user): bool;
    public function softDelete(User $user): bool;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function getUsersByCompany(int $companyId, array $with = []): Collection;
    public function syncRoles(User $user, array $roleIds): void;
    public function removeRoles(User $user, array $roleIds): void;
    
    /**
     * Sync roles with pivot data.
     *
     * @param User  $user
     * @param array $roleData  Format: [role_id => ['pivot_column' => 'value']]
     */
    public function syncRolesWithPivot(User $user, array $roleData): void;
}
