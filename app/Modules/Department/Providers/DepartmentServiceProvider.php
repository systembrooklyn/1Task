<?php

namespace App\Modules\Department\Providers;

use App\Modules\Department\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Modules\Department\Repositories\Eloquent\EloquentDepartmentRepository;
use App\Modules\Department\Services\DepartmentService;
use Illuminate\Support\ServiceProvider;

class DepartmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DepartmentRepositoryInterface::class, EloquentDepartmentRepository::class);
        $this->app->singleton(DepartmentService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
