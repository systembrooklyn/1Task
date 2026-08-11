<?php

namespace App\Modules\Project\Providers;

use App\Modules\Project\Repositories\Contracts\ProjectRepositoryInterface;
use App\Modules\Project\Repositories\Eloquent\EloquentProjectRepository;
use App\Modules\Project\Repositories\Eloquent\EloqunentProjectRepository;
use App\Modules\Project\Services\ProjectService;
use Illuminate\Support\ServiceProvider;

class ProjectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
        $this->app->singleton(ProjectService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
