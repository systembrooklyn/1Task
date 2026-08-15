<?php

namespace App\Modules\Ticket\Providers;

use App\Modules\Ticket\Models\Ticket;
use App\Modules\Ticket\Observers\TicketObserver;
use App\Modules\Ticket\Repositories\Contracts\TicketRepositoryInterface;
use App\Modules\Ticket\Repositories\Contracts\TicketActionRepositoryInterface;
use App\Modules\Ticket\Repositories\Eloquent\EloquentTicketActionRepository;
use App\Modules\Ticket\Repositories\Eloquent\EloquentTicketRepository;
use App\Modules\Ticket\Services\TicketService;
use App\Modules\Ticket\Services\TicketActionService;
use Illuminate\Support\ServiceProvider;

class TicketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
        $this->app->bind(TicketActionRepositoryInterface::class, EloquentTicketActionRepository::class);
        $this->app->singleton(TicketService::class);
        $this->app->singleton(TicketActionService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        Ticket::observe(TicketObserver::class);
    }
}
