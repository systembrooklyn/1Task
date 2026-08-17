<?php

namespace App\Modules\RolePermission\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\RolePermission\Repositories\Contracts\RoleRepositoryInterface;
use App\Modules\RolePermission\Repositories\Eloquent\EloquentRoleRepository;
use App\Modules\RolePermission\Repositories\Contracts\PermissionRepositoryInterface;
use App\Modules\RolePermission\Repositories\Eloquent\EloquentPermissionRepository;
use App\Modules\RolePermission\Services\RoleServiceInterface;
use App\Modules\RolePermission\Services\RoleService;
use App\Modules\RolePermission\Services\PermissionServiceInterface;
use App\Modules\RolePermission\Services\PermissionService;

class RolePermissionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, EloquentPermissionRepository::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}