<?php

namespace App\Modules\RolePermission\Repositories\Eloquent;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Modules\RolePermission\Repositories\Contracts\RoleRepositoryInterface;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function findById(int $id): ?Role
    {
        return Role::find($id);
    }

    public function findByCompany(int $companyId)
    {
        return Role::where('company_id', $companyId)->get();
    }

    public function findActiveByCompany(int $companyId)
    {
        return Role::where('company_id', $companyId)
                   ->where('is_deleted', 0)
                   ->get();
    }

    public function findByNameAndCompany(string $name, int $companyId): ?Role
    {
        return Role::where('company_id', $companyId)
                   ->where('name', $name)
                   ->first();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role;
    }

    public function softDelete(Role $role): void
    {
        $role->update([
            'is_deleted' => 1,
            'deleted_at' => Carbon::now(),
        ]);
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    public function detachUsers(Role $role): void
    {
        DB::table('role_user')->where('role_id', $role->id)->delete();
    }
}