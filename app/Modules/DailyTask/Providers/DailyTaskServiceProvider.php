<?php

namespace App\Modules\DailyTask\Providers;

use App\Modules\DailyTask\Repositories\Contracts\DailyTaskRepositoryInterface;
use App\Modules\DailyTask\Repositories\Contracts\DailyTaskReportRepositoryInterface;
use App\Modules\DailyTask\Repositories\Contracts\DailyTaskEvaluationRepositoryInterface;
use App\Modules\DailyTask\Repositories\Eloquent\EloquentDailyTaskEvaluationRepository;
use App\Modules\DailyTask\Repositories\Eloquent\EloquentDailyTaskReportRepository;
use App\Modules\DailyTask\Repositories\Eloquent\EloquentDailyTaskRepository;
use App\Modules\DailyTask\Services\DailyTaskService;
use App\Modules\DailyTask\Services\DailyTaskReportService;
use App\Modules\DailyTask\Services\DailyTaskEvaluationService;
use Illuminate\Support\ServiceProvider;

class DailyTaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DailyTaskRepositoryInterface::class, EloquentDailyTaskRepository::class);
        $this->app->bind(DailyTaskReportRepositoryInterface::class, EloquentDailyTaskReportRepository::class);
        $this->app->bind(DailyTaskEvaluationRepositoryInterface::class, EloquentDailyTaskEvaluationRepository::class);

        $this->app->singleton(DailyTaskService::class);
        $this->app->singleton(DailyTaskReportService::class);
        $this->app->singleton(DailyTaskEvaluationService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
