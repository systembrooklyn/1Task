<?php

namespace App\Providers;

use App\Models\DailyTask;
use App\Models\DailyTaskReport;
use App\Models\Department;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Role;
use App\Observers\DailyTaskReportObserver;
use App\Policies\DailyTaskPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RolePolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        DailyTaskReport::observe(DailyTaskReportObserver::class);
    }

    protected $policies = [
        Department::class => DepartmentPolicy::class,
        DailyTask::class => DailyTaskPolicy::class,
        Invitation::class => InvitationPolicy::class,
        Project::class => ProjectPolicy::class,
        Role::class => RolePolicy::class,

    ];
}
