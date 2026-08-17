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
        $permissions = $this->permissionService->all();
        return response()->json($permissions);
    }

    public function show(int $id)
    {
        $permission = $this->permissionService->find($id);
        return response()->json($permission);
    }
}
