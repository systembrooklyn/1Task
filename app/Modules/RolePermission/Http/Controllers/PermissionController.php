<?php

namespace App\Modules\RolePermission\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Modules\RolePermission\Services\PermissionServiceInterface;

class PermissionController extends Controller
{
    protected PermissionServiceInterface $permissionService;

    public function __construct(PermissionServiceInterface $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        // Original: Permission::get() – returns all attributes
        $permissions = $this->permissionService->all();
        return response()->json($permissions);
    }

    public function show(int $id)
    {
        // Original: Permission::findOrFail($id) – returns full model
        $permission = $this->permissionService->find($id);
        return response()->json($permission);
    }
}
