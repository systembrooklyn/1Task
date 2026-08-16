<?php

namespace App\Modules\Task\Providers;

use App\Modules\Task\Repositories\Contracts\TaskAttachmentRepositoryInterface;
use App\Modules\Task\Repositories\Contracts\TaskCommentReplyRepositoryInterface;
use App\Modules\Task\Repositories\Contracts\TaskCommentRepositoryInterface;
use App\Modules\Task\Repositories\Contracts\TaskRepositoryInterface;
use App\Modules\Task\Repositories\Contracts\TaskRevisionRepositoryInterface;
use App\Modules\Task\Repositories\Contracts\TaskUserStatusRepositoryInterface;
use App\Modules\Task\Repositories\Eloquent\EloquentTaskAttachmentRepository;
use App\Modules\Task\Repositories\Eloquent\EloquentTaskCommentReplyRepository;
use App\Modules\Task\Repositories\Eloquent\EloquentTaskCommentRepository;
use App\Modules\Task\Repositories\Eloquent\EloquentTaskRepository;
use App\Modules\Task\Repositories\Eloquent\EloquentTaskRevisionRepository;
use App\Modules\Task\Repositories\Eloquent\EloquentTaskUserStatusRepository;
use App\Modules\Task\Services\TaskCommentReplyService;
use App\Modules\Task\Services\TaskCommentService;
use App\Modules\Task\Services\TaskRevisionService;
use App\Modules\Task\Services\TaskService;
use App\Modules\Task\Services\TaskUserStatusService;
use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TaskAttachmentRepositoryInterface::class,
            EloquentTaskAttachmentRepository::class
        );
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
        $this->app->bind(TaskCommentRepositoryInterface::class, EloquentTaskCommentRepository::class);
        $this->app->bind(TaskCommentReplyRepositoryInterface::class, EloquentTaskCommentReplyRepository::class);
        $this->app->bind(TaskUserStatusRepositoryInterface::class, EloquentTaskUserStatusRepository::class);
        $this->app->bind(TaskRevisionRepositoryInterface::class, EloquentTaskRevisionRepository::class);

        $this->app->singleton(TaskService::class);
        $this->app->singleton(TaskCommentService::class);
        $this->app->singleton(TaskCommentReplyService::class);
        $this->app->singleton(TaskUserStatusService::class);
        $this->app->singleton(TaskRevisionService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
