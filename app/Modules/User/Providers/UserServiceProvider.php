<?php

namespace App\Modules\User\Providers;

use App\Modules\User\Repositories\Contracts\InvitationRepositoryInterface;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use App\Modules\User\Repositories\Eloquent\EloquentInvitationRepository;
use App\Modules\User\Repositories\Eloquent\UserRepository;
use App\Modules\User\Services\AuthService;
use App\Modules\User\Services\InvitationService;
use App\Modules\User\Services\UserService;
use App\Modules\User\Services\ProfileService;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(InvitationRepositoryInterface::class, EloquentInvitationRepository::class);
        $this->app->singleton(AuthService::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(ProfileService::class);
        $this->app->singleton(InvitationService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
