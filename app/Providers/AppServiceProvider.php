<?php

namespace App\Providers;

use App\Models\DailyTask;
use App\Models\Department;
use App\Models\Invitation;
use App\Models\Project;
use App\Policies\DailyTaskPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\ProjectPolicy;
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
    }

    protected $policies = [
        Department::class => DepartmentPolicy::class,
        DailyTask::class => DailyTaskPolicy::class,
        Invitation::class => InvitationPolicy::class,
        Project::class => ProjectPolicy::class,
        
    ];
}
