<?php

namespace App\Modules\DigitalCard\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\DigitalCard\Repositories\Contracts\UserRepositoryInterface;
use App\Modules\DigitalCard\Repositories\Eloquent\EloquentUserRepository;
use App\Modules\DigitalCard\Repositories\Contracts\SocialLinkRepositoryInterface;
use App\Modules\DigitalCard\Repositories\Eloquent\EloquentSocialLinkRepository;
use App\Modules\DigitalCard\Repositories\Contracts\PhoneRepositoryInterface;
use App\Modules\DigitalCard\Repositories\Eloquent\EloquentPhoneRepository;
use App\Modules\DigitalCard\Services\AuthServiceInterface;
use App\Modules\DigitalCard\Services\AuthService;
use App\Modules\DigitalCard\Services\DigitalCardServiceInterface;
use App\Modules\DigitalCard\Services\DigitalCardService;

class DigitalCardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(SocialLinkRepositoryInterface::class, EloquentSocialLinkRepository::class);
        $this->app->bind(PhoneRepositoryInterface::class, EloquentPhoneRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DigitalCardServiceInterface::class, DigitalCardService::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
