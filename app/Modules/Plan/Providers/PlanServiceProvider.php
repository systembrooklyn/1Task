<?php

namespace App\Modules\Plan\Providers;

use App\Modules\Plan\Repositories\Contracts\PlanRepositoryInterface;
use App\Modules\Plan\Repositories\Contracts\FeatureRepositoryInterface;
use App\Modules\Plan\Repositories\Eloquent\EloquentFeatureRepository;
use App\Modules\Plan\Repositories\Eloquent\EloquentPlanRepository;
use App\Modules\Plan\Services\PlanService;
use App\Modules\Plan\Services\FeatureService;
use Illuminate\Support\ServiceProvider;

class PlanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlanRepositoryInterface::class, EloquentPlanRepository::class);
        $this->app->bind(FeatureRepositoryInterface::class, EloquentFeatureRepository::class);
        $this->app->singleton(PlanService::class);
        $this->app->singleton(FeatureService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
