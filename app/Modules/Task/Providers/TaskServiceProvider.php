<?php

namespace App\Modules\Task\Providers;

use App\Modules\Task\Repositories\Contracts\TaskAttachmentRepositoryInterface;
use App\Modules\Task\Repositories\Eloquent\EloquentTaskAttachmentRepository;
use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Binds the Contract to the Eloquent Implementation
        $this->app->bind(
            TaskAttachmentRepositoryInterface::class,
            EloquentTaskAttachmentRepository::class
        );
    }

    public function boot(): void
    {
        // Loads the module routes exactly as you do in your Ticketing module
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
