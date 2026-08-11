<?php

namespace App\Modules\DailyTask\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DailyTask\Http\Requests\StoreDailyTaskRequest;
use App\Modules\DailyTask\Http\Requests\UpdateDailyTaskRequest;
use App\Modules\DailyTask\Http\Requests\FilterDailyTasksRequest;
use App\Modules\DailyTask\Http\Requests\UpdateRandomTaskCountRequest;
use App\Modules\DailyTask\Services\DailyTaskService;
use App\Modules\DailyTask\Http\Resources\DailyTaskResource;
use App\Models\DailyTask;
use App\Modules\DailyTask\Http\Traits\ChecksCompanyScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DailyTaskController extends Controller
{
    use ChecksCompanyScope;
    public function __construct(protected DailyTaskService $taskService) {}

    /**
     * GET /dailytask – List tasks for the authenticated user's departments (today's tasks with report status)
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('viewAny', DailyTask::class);
        $tasks = $this->taskService->getTasksForUser($user);
        return response()->json(['tasks' => DailyTaskResource::collection($tasks)]);
    }

    /**
     * POST /dailytask – Create a new daily task
     */
    public function store(StoreDailyTaskRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('create', DailyTask::class);
        $task = $this->taskService->create($request->validated(), $user->company_id, $user->id);
        $task->load(['department', 'creator', 'assignee', 'updatedBy', 'submittedBy', 'project:id,name,status']);
        return response()->json([
            'message' => 'Daily Task created successfully.',
            'data'    => new DailyTaskResource($task),
        ], 201);
    }

    /**
     * GET /dailytask/{id} – Show a single task
     */
    public function show(int $id): JsonResponse
    {
        $task = DailyTask::with([
            'department',
            'creator',
            'assignee',
            'updatedBy',
            'submittedBy',
            'revisions.user',
            'project'
        ])->findOrFail($id);
        $this->ensureSameCompany($task);
        $this->authorize('view', $task);
        return (new DailyTaskResource($task))->response()->setStatusCode(200);
    }

    /**
     * PUT /dailytask/{id} – Update a task
     */
    public function update(UpdateDailyTaskRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $task = DailyTask::findOrFail($id);
        $this->ensureSameCompany($task);
        $this->authorize('update', $task);
        $updated = $this->taskService->update($task, $request->validated(), $user->id);
        $updated->load('project:id,name,status');
        return response()->json([
            'message' => 'Task updated successfully',
            'task'    => $updated
        ], 200);
    }

    /**
     * DELETE /dailytask/{id} – Delete a task
     */
    public function destroy(int $id): JsonResponse
    {
        $task = DailyTask::findOrFail($id);
        $this->ensureSameCompany($task);
        $this->authorize('delete', $task);
        $this->taskService->delete($task);
        return response()->json(['message' => 'Task deleted successfully.']);
    }

    /**
     * POST /activedailytask/{id} – Toggle active status
     */
    public function activeDailyTask(int $id): JsonResponse
    {
        $user = Auth::user();
        $task = DailyTask::findOrFail($id);
        $this->ensureSameCompany($task);
        $this->authorize('update', $task);
        $this->taskService->toggleActive($task, $user->id);
        return response()->json(['message' => 'Task active status toggled successfully.'], 200);
    }

    /**
     * GET /dailytask/{id}/revisions – Get revision history
     */
    public function revisions(int $id): JsonResponse
    {
        $task = DailyTask::with(['revisions.user'])->findOrFail($id);
        $this->ensureSameCompany($task);
        $this->authorize('view', $task);
        $revisions = $this->taskService->getRevisions($id);
        return response()->json([
            'daily_task_id' => $task->id,
            'revisions'     => $revisions,
        ]);
    }

    /**
     * GET /alldailytask – Get all tasks for the company (no date filtering)
     */
    public function allDailyTasks(): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('viewAllTasks', DailyTask::class);
        $tasks = $this->taskService->getAllTasksForCompany($user->company_id);
        return response()->json(['tasks' => DailyTaskResource::collection($tasks)], 200);
    }

    /**
     * POST /alldailytaskfilter – Filter tasks with pagination
     */
    public function allDailyTasksFiltered(FilterDailyTasksRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('viewAny', DailyTask::class);

        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('type_of', 'desc');
        $deptIds = $request->input('dept_ids', []);
        $taskType = $request->input('task_type');
        $active   = $request->input('active');

        $allowedSorts = ['task_no', 'created_at', 'start_date'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtolower($sortOrder);
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query = DailyTask::with([
            'department',
            'creator',
            'assignee',
            'updatedBy',
            'submittedBy',
            'project:id,name,status'
        ])->where('company_id', $user->company_id);

        if (!empty($deptIds)) {
            $query->whereIn('dept_id', $deptIds);
        }
        if ($taskType) {
            $query->where('task_type', $taskType);
        }
        if ($active !== null) {
            $active = filter_var($active, FILTER_VALIDATE_BOOLEAN);
            $query->where('active', $active);
        }

        $tasks = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        return response()->json([
            'tasks' => DailyTaskResource::collection($tasks->items()),
            'pagination' => [
                'total' => $tasks->total(),
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'last_page' => $tasks->lastPage(),
                'next_page_url' => $tasks->nextPageUrl(),
                'prev_page_url' => $tasks->previousPageUrl(),
            ],
        ], 200);
    }

    /**
     * GET /dailyTasks/yesterday – Get yesterday's evaluation tasks (with caching)
     */
    public function getYesterdayEvaluationTasks(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $data = $this->taskService->getYesterdayEvaluationTasks($user->company_id);
        return response()->json([
            'message' => "Random Daily Tasks Retrieved Successfully for: {$data['date']}",
            'data'    => $data,
        ]);
    }

    /**
     * POST /dailyTasks/setRandomCount – Set random task count per department
     */
    public function updateRandomTaskCount(UpdateRandomTaskCountRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $randomCount = $request->input('random_daily_task_count');
        if ($randomCount > 10 || $randomCount < 1) {
            return response()->json([
                'notify'  => "please insert number between 1 to 10",
                'message' => "Random count can't be less than 1 or bigger than 10 : {$randomCount}"
            ]);
        }
        try {
            $this->taskService->setRandomTaskCount($user->company_id, $randomCount);
            return response()->json([
                'notify'  => "this count will change in the next day not for today",
                'message' => "Random task count updated successfully to {$randomCount}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update random task count',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
