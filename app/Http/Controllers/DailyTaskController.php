<?php

namespace App\Http\Controllers;

use App\Helpers\TaskNumberGenerator;
use App\Models\DailyTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Resources\DailyTaskResource;
use App\Models\DailyTaskRevision;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Services\PlanLimitService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DailyTaskController extends Controller
{

    protected $planService;

    public function __construct(PlanLimitService $planService)
    {
        $this->planService = $planService;
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        try {
            $this->authorize('create', DailyTask::class);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'task_type' => 'required|in:single,daily,weekly,monthly,last_day_of_month',
            'recurrent_days' => 'nullable|array',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'from' => 'required|date_format:H:i',
            'to' => 'required|date_format:H:i|after:from',
            'dept_id' => 'required|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'project_id'    => 'nullable|exists:projects,id',
            'priority' => 'nullable|in:normal,critical',
        ]);
        // return $validated;
        if ($validated['task_type'] === 'daily') {
            $validated['recurrent_days'] = null;
            $validated['day_of_month'] = null;
        } elseif ($validated['task_type'] === 'weekly') {
            $validated['day_of_month'] = null;
        } elseif ($validated['task_type'] === 'monthly') {
            $validated['recurrent_days'] = null;
        }
        $this->planService->checkFeatureAccess($user->company_id, 'limit_dailyTask');
        try {
            $task = DB::transaction(function () use ($validated, $companyId, $user) {
                return DailyTask::create([
                    'task_name' => $validated['task_name'],
                    'description' => $validated['description'] ?? null,
                    'start_date' => $validated['start_date'],
                    'task_type' => $validated['task_type'],
                    'recurrent_days' => $validated['recurrent_days'] ?? null,
                    'day_of_month' => $validated['day_of_month'] ?? null,
                    'from' => $validated['from'],
                    'to' => $validated['to'],
                    'priority' => $validated['priority'] ?? 'normal',
                    'company_id' => $companyId,
                    'dept_id' => $validated['dept_id'],
                    'project_id'      => $validated['project_id'] ?? null,
                    'created_by' => $user->id,
                    'assigned_to' => $validated['assigned_to'] ?? null,
                    'active' => true,
                    'updated_by' => null,
                ]);
            });

            $task->load(['department', 'creator', 'assignee', 'updatedBy', 'submittedBy', 'project:id,name,status']);

            return response()->json([
                'message' => 'Daily Task created successfully.',
                'data' => new DailyTaskResource($task),
            ], 201);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'message' => 'Task number already exists. Please try again.',
                ], 409);
            }
            Log::error('Task Creation Failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create Daily Task.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Task Creation Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'An unexpected error occurred.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $task = DailyTask::findOrFail($id);
        $this->authorize('update', $task);
        $original = $task->getOriginal();
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'task_type' => 'required|in:single,daily,weekly,monthly,last_day_of_month',
            'recurrent_days' => 'nullable|array',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'from' => 'required|date_format:H:i',
            'to' => 'required|date_format:H:i|after:from',
            'dept_id' => 'required|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'project_id'    => 'nullable|exists:projects,id',
            'priority' => 'nullable|in:normal,critical',
        ]);
        if ($validated['task_type'] === 'daily') {
            $validated['recurrent_days'] = null;
            $validated['day_of_month'] = null;
        } elseif ($validated['task_type'] === 'weekly') {
            $validated['day_of_month'] = null;
        } elseif ($validated['task_type'] === 'monthly') {
            $validated['recurrent_days'] = null;
        }
        $updateData = [
            'task_name' => $validated['task_name'] ?? $task->name,
            'dept_id' => $validated['dept_id'] ?? $task->dept_id,
            'description' => $validated['description'] ?? $task->description,
            'start_date' => $validated['start_date'] ?? $task->start_date,
            'task_type' => $validated['task_type'] ?? $task->task_type,
            'recurrent_days' => $validated['recurrent_days'] ?? null,
            'day_of_month' => $validated['day_of_month'] ?? null,
            'from' => $validated['from'] ?? $task->from,
            'to' => $validated['to'] ?? $task->to,
            'priority' => $validated['priority'] ?? $task->priority,
            'assigned_to' => $validated['assigned_to'] ?? $task->assigned_to,
            'project_id'     => array_key_exists('project_id', $validated) ? $validated['project_id'] : $task->project_id,
            'updated_by' => $user->id,
        ];
        $task->update($updateData);
        $changes = $task->getChanges();
        $trackableFields = [
            'task_name',
            'status',
            'description',
            'start_date',
            'task_type',
            'recurrent_days',
            'day_of_month',
            'from',
            'to',
            'assigned_to',
            'note',
            'project_id',
            'dept_id'
        ];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $trackableFields)) {
                $oldValue = $original[$field] ?? null;
                if (is_array($oldValue)) {
                    $oldValue = json_encode($oldValue);
                }
                if (is_array($newValue)) {
                    $newValue = json_encode($newValue);
                }
                if ($field === 'dept_id') {
                    $oldValue = $oldValue ? optional(Department::find($oldValue))->name : null;
                    $newValue = $newValue ? optional(Department::find($newValue))->name : null;
                }

                // For project-related fields, fetch the project's name instead of its ID
                if ($field === 'project_id') {
                    $oldValue = $oldValue ? optional(Project::find($oldValue))->name : null;
                    $newValue = $newValue ? optional(Project::find($newValue))->name : null;
                }
                DailyTaskRevision::create([
                    'daily_task_id' => $task->id,
                    'user_id' => $user->id,
                    'field_name' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'created_at' => now(),
                ]);
            }
        }
        $task->load('project:id,name,status');

        return response()->json(['message' => 'Task updated successfully', 'task' => $task], 200);
    }

    public function destroy($id)
    {
        $task = DailyTask::findOrFail($id);
        $user = Auth::user();
        $this->authorize('delete', $task);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }

    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $company_id = $user->company_id;
    //     $this->authorize('viewAny', DailyTask::class);
    //     $per_page = $request->input('per_page');
    //     $deptFilter = $request->input('dept_filter');
    //     $sort_by = $request->input('sort_by', 'from');
    //     $type_of = $request->input('type_of', 'asc');
    //     $allowedSorts = ['start_date','from'];
    //     if (!in_array($sort_by, $allowedSorts)) {
    //         $sort_by = 'from';
    //     }
    //     $type_of = strtolower($type_of);
    //     if (!in_array($type_of, ['asc', 'desc'])) {
    //         $type_of = 'asc';
    //     }

    //     $today = now()->format('Y-m-d');
    //     $currentDayOfWeek = now()->dayOfWeek;
    //     $currentDayOfMonth = now()->day;
    //     $departmentIds = $user->departments()->pluck('departments.id')->toArray();
    //     $perPage = $request->input('per_page', $per_page ? $per_page : 10);

    //     $tasksQuery = DailyTask::query()
    //         ->where('company_id', $company_id)
    //         ->where('active',1)
    //         ->whereIn('dept_id', $deptFilter? $deptFilter : $departmentIds)
    //         ->where(function ($query) use ($today, $currentDayOfWeek, $currentDayOfMonth) {
    //             $query->orWhere(function ($query) use ($today) {
    //                 $query->where('task_type', 'daily')
    //                     ->whereDate('start_date', '<=', $today);
    //             })
    //             ->orWhere(function ($query) use ($today, $currentDayOfWeek) {
    //                 $query->where('task_type', 'weekly')
    //                     ->whereDate('start_date', '<=', $today)
    //                     ->whereJsonContains('recurrent_days', $currentDayOfWeek);
    //             })
    //             ->orWhere(function ($query) use ($today, $currentDayOfMonth) {
    //                 $query->where('task_type', 'monthly')
    //                     ->whereDate('start_date', '<=', $today)
    //                     ->where('day_of_month', $currentDayOfMonth);
    //             })
    //             ->orWhere(function ($query) use ($today) {
    //                 $query->where('task_type', 'single')
    //                     ->whereDate('start_date', $today);
    //             })
    //             ->orWhere(function ($query) use ($today) {
    //                 $query->where('task_type', 'last_day_of_month')
    //                     ->whereDate('start_date', $today)
    //                     ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [now()->day]);
    //             });
    //         })
    //         ->leftJoin('daily_task_reports', function ($join) use ($today) {
    //             $join->on('daily_task_reports.daily_task_id', '=', 'daily_tasks.id')
    //                 ->whereDate('daily_task_reports.created_at', '=', $today);
    //         })
    //         ->select('daily_tasks.*', 'daily_task_reports.status as today_report_status')
    //         ->orderByRaw("CASE
    //         WHEN daily_task_reports.daily_task_id IS NULL THEN 1
    //         WHEN daily_task_reports.status = 'done' THEN 2
    //         WHEN daily_task_reports.status = 'not_done' THEN 3
    //         ELSE 4 END")
    //         ->orderBy($sort_by, $type_of);

    //     $tasks = $tasksQuery->paginate($perPage);
    //     $tasksData = DailyTaskResource::collection($tasks->items());

    //     return response()->json([
    //         'tasks' => $tasksData,
    //         'pagination' => [
    //             'total' => $tasks->total(),
    //             'current_page' => $tasks->currentPage(),
    //             'per_page' => $tasks->perPage(),
    //             'last_page' => $tasks->lastPage(),
    //             'next_page_url' => $tasks->nextPageUrl(),
    //             'prev_page_url' => $tasks->previousPageUrl(),
    //         ],
    //     ]);
    // }


    public function index(Request $request)
    {
        $authUser = Auth::user();
        $user = User::find($authUser->id);
        $company_id = $user->company_id;
        $this->authorize('viewAny', DailyTask::class);

        $today = now()->format('Y-m-d');
        $currentDayOfWeek = now()->dayOfWeek;
        $currentDayOfMonth = now()->day;

        $departmentIds = $user->departments()->pluck('departments.id')->toArray();
        $tasksQuery = DailyTask::query()
            ->where('company_id', $company_id)
            ->where('active', 1)
            ->whereIn('dept_id', $departmentIds)
            ->where(function ($query) use ($today, $currentDayOfWeek, $currentDayOfMonth) {
                // Filter for today's tasks
                $query->orWhere(function ($query) use ($today) {
                    $query->where('task_type', 'daily')
                        ->whereDate('start_date', '<=', $today);
                })
                    ->orWhere(function ($query) use ($today, $currentDayOfWeek) {
                        $query->where('task_type', 'weekly')
                            ->whereDate('start_date', '<=', $today)
                            ->whereJsonContains('recurrent_days', $currentDayOfWeek);
                    })
                    ->orWhere(function ($query) use ($today, $currentDayOfMonth) {
                        $query->where('task_type', 'monthly')
                            ->whereDate('start_date', '<=', $today)
                            ->where('day_of_month', $currentDayOfMonth);
                    })
                    ->orWhere(function ($query) use ($today) {
                        $query->where('task_type', 'single')
                            ->whereDate('start_date', $today);
                    })
                    ->orWhere(function ($query) use ($today) {
                        $query->where('task_type', 'last_day_of_month')
                            ->whereDate('start_date', $today)
                            ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [now()->day]);
                    });
            })
            ->leftJoin('daily_task_reports', function ($join) use ($today) {
                $join->on('daily_task_reports.daily_task_id', '=', 'daily_tasks.id')
                    ->whereDate('daily_task_reports.created_at', '=', $today);
            })
            ->orderByRaw("CASE
                WHEN daily_task_reports.daily_task_id IS NULL THEN 1
                WHEN daily_task_reports.status = 'done' THEN 2
                WHEN daily_task_reports.status = 'not_done' THEN 3
                ELSE 4 END")
            ->orderBy('to', 'asc')
            ->select('daily_tasks.*', 'daily_task_reports.status as today_report_status')
            ->with([
                'department:id,name',
                'creator:id,name',
                'assignee:id,name',
                'updatedBy:id,name',
                'todayReport:id,daily_task_id,notes,task_found,status,submitted_by,created_at',
                'todayReport.submittedBy:id,name',
                'project:id,name,status'
            ]);
        $tasks = $tasksQuery->get();
        $tasksData = DailyTaskResource::collection($tasks);

        return response()->json([
            'tasks' => $tasksData,
        ]);
    }


    public function show($id)
    {
        $user = Auth::user();
        $task = DailyTask::with(['department', 'creator', 'assignee', 'updatedBy', 'submittedBy', 'revisions.user', 'project'])->findOrFail($id);
        $this->authorize('view', $task);

        return (new DailyTaskResource($task))
            ->response()
            ->setStatusCode(200);
    }

    public function allDailyTasks()
    {
        $user = Auth::user();
        $this->authorize('viewAllTasks', DailyTask::class);

        $tasksQuery = DailyTask::query()
            ->where('company_id', $user->company_id)
            ->orderBy('task_no', 'desc')
            ->with([
                'department:id,name',
                'creator:id,name,last_name',
                'assignee:id,name,last_name',
                'updatedBy:id,name,last_name',
                'todayReport:id,daily_task_id,notes,task_found,status,submitted_by,created_at',
                'todayReport.submittedBy:id,name,last_name',
                'project:id,name,status'
            ]);
        $tasks = $tasksQuery->get();
        return response()->json([
            'tasks' => DailyTaskResource::collection($tasks),
        ], 200);
    }

    // public function allDailyTasks(Request $request)
    // {
    //     $perPage = $request->input('per_page', 10);
    //     $user = Auth::user();
    //     $this->authorize('viewAny', DailyTask::class);
    //     $sort_by = $request->input('sort_by', 'created_at');
    //     $type_of = $request->input('type_of', 'desc');
    //     $allowedSorts = ['start_date','created_at'];
    //     if (!in_array($sort_by, $allowedSorts)) {
    //         $sort_by = 'created_at';
    //     }
    //     $type_of = strtolower($type_of);
    //     if (!in_array($type_of, ['asc', 'desc'])) {
    //         $type_of = 'desc';
    //     }

    //     $tasks = DailyTask::with(['department', 'creator', 'assignee', 'updatedBy', 'submittedBy'])
    //                       ->where('company_id', $user->company_id)
    //                       ->orderBy($sort_by, $type_of)
    //                       ->paginate($perPage);
    //     return response()->json([
    //         'tasks' => DailyTaskResource::collection($tasks->items()),
    //         'pagination' => [
    //             'total' => $tasks->total(),
    //             'current_page' => $tasks->currentPage(),
    //             'per_page' => $tasks->perPage(),
    //             'last_page' => $tasks->lastPage(),
    //             'next_page_url' => $tasks->nextPageUrl(),
    //             'prev_page_url' => $tasks->previousPageUrl(),
    //         ],
    //     ], 200);
    // }


    public function allDailyTasksFiltered(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $user = Auth::user();
        $this->authorize('viewAny', DailyTask::class);

        $sort_by = $request->input('sort_by', 'created_at');
        $type_of = $request->input('type_of', 'desc');
        $dept_ids = $request->input('dept_ids', []);
        $task_type = $request->input('task_type');
        $active = $request->input('active');
        $allowedSorts = ['task_no', 'created_at', 'start_date'];
        if (!in_array($sort_by, $allowedSorts)) {
            $sort_by = 'created_at';
        }
        $type_of = strtolower($type_of);
        if (!in_array($type_of, ['asc', 'desc'])) {
            $type_of = 'desc';
        }
        $query = DailyTask::with(['department', 'creator', 'assignee', 'updatedBy', 'submittedBy', 'project:id,name,status'])
            ->where('company_id', $user->company_id);
        if (!empty($dept_ids)) {
            $query->whereIn('dept_id', $dept_ids);
        }

        if ($task_type) {
            $query->where('task_type', $task_type);
        }

        if ($active !== null) {
            $active = filter_var($active, FILTER_VALIDATE_BOOLEAN);
            $query->where('active', $active);
        }
        $tasks = $query->orderBy($sort_by, $type_of)->paginate($perPage);
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

    // public function submitDailyTask(Request $request, $id){
    //     $user = Auth::user();
    //     $request->validate([
    //         'note' => 'nullable|string',
    //         'status' => 'required|in:done,not_done',
    //     ]);
    //     $task = DailyTask::findOrFail($id);
    //     $task->note = $request['note'];
    //     $task->status = $request['status'];
    //     $task->submitted_by = $user->id;
    //     $task->save();
    //     return response()->json(['message' => 'Task submitted successfully!']);
    // }

    public function activeDailyTask(Request $request, $id)
    {
        $user = Auth::user();
        $task = DailyTask::findOrFail($id);
        $this->authorize('update', $task);
        $originalActiveStatus = $task->active;
        $task->active = !$task->active;
        $task->save();
        $newActiveStatus = $task->active;
        if ($originalActiveStatus !== $newActiveStatus) {
            DailyTaskRevision::create([
                'daily_task_id' => $task->id,
                'user_id' => $user->id,
                'field_name' => 'active',
                'old_value' => $originalActiveStatus ? '1' : '0',
                'new_value' => $newActiveStatus ? '1' : '0',
                'created_at' => now(),
            ]);
        }
        return response()->json([
            'message' => 'Task active status toggled successfully.',
        ], 200);
    }

    public function revisions($id)
    {
        $task = DailyTask::with(['revisions.user'])->findOrFail($id);
        $this->authorize('view', $task);
        $formattedRevisions = $task->revisions->map(function ($revision) {
            return [
                'id'          => $revision->id,
                'field_name'  => $revision->field_name,
                'old_value'   => $revision->old_value,
                'new_value'   => $revision->new_value,
                'user'        => [
                    'id'    => $revision->user->id,
                    'name'  => $revision->user->name,
                    'last_name'  => $revision->user->last_name ?? null,
                    'email' => $revision->user->email,
                ],
                'created_at'  => $revision->created_at,
                'updated_at'  => $revision->updated_at,
            ];
        });

        return response()->json([
            'daily_task_id' => $task->id,
            'revisions'     => $formattedRevisions,
        ]);
    }
    public function todaysReports()
    {
        $authUser = Auth::user();
        $user = User::find($authUser->id);
        $today = now()->toDateString();
        $dailyTasks = DailyTask::with(['todayReport', 'department', 'creator', 'assignee', 'submittedBy', 'updatedBy', 'project:id,name,status'])
            ->where('company_id', $user->company_id)
            ->whereIn('dept_id', $user->departments()->pluck('departments.id')->toArray())
            ->get();
        $tasksData = DailyTaskResource::collection($dailyTasks);
        return response()->json([
            'date' => $today,
            'tasks' => $tasksData,
        ], 200);
    }


    public function getYesterdayEvaluationTasks(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        $companyId = $user->company_id;
        $today = Carbon::now();
        $yesterday = $today->copy()->subDay();
        $formattedDate = $yesterday->format('Y-m-d');
        $cacheKey = "evaluation_tasks_{$companyId}_" . $yesterday->format('Y-m-d');
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return response()->json([
                'message' => "Random Daily Tasks Retrieved Successfully for: {$cached['data']['date']}",
                'data' => $cached['data']
            ]);
        }
        for ($i = 1; $i <= 15; $i++) {
            $oldDate = $today->copy()->subDays($i);
            $oldCacheKey = "evaluation_tasks_{$companyId}_" . $oldDate->format('Y-m-d');
            Cache::forget($oldCacheKey);
        }
        $tasks = DailyTask::where('company_id', $companyId)
            ->where('active', true)
            ->select('id', 'start_date', 'task_type', 'recurrent_days', 'day_of_month', 'company_id', 'dept_id')
            ->get();

        $validTasks = $tasks->filter(function ($task) use ($yesterday) {
            return $this->shouldTaskAppearOnDate($task, $yesterday);
        });
        $numTasksPerDept = TaskNumberGenerator::getRandomDailyTaskNum($companyId);
        $groupedTasks = $validTasks
            ->groupBy('dept_id')
            ->map(function ($departmentTasks) use ($numTasksPerDept) {
                return $departmentTasks->random(min($numTasksPerDept, $departmentTasks->count()));
            })
            ->filter()
            ->flatten(1);

        $taskIds = $groupedTasks->pluck('id')->values()->toArray();

        $responseData = [
            'date' => $formattedDate,
            'dailytask_ids' => $taskIds,
            'count' => count($taskIds),
        ];
        Cache::put($cacheKey, ['data' => $responseData], now()->addDay());

        return response()->json([
            'message' => "Random Daily Tasks Retrieved Successfully for: $formattedDate",
            'data' => $responseData
        ]);
    }

    public function updateRandomTaskCount(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'random_daily_task_count' => 'required|integer'
        ]);

        $companyId = $user->company_id;
        $randomCount = $request->input('random_daily_task_count');
        if ($randomCount > 10 || $randomCount < 1) {
            return response()->json([
                'notify' => "please insert number between 1 to 10",
                'message' => "Random count can't be less than 1 or bigger than 10 : {$randomCount}"
            ]);
        }
        try {
            TaskNumberGenerator::setRandomDailyTaskNum($companyId, $randomCount);

            return response()->json([
                'notify' => "this count will change in the next day not for today",
                'message' => "Random task count updated successfully to {$randomCount}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update random task count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine if a task should appear on a given date.
     *
     * @param DailyTask $task
     * @param Carbon $date
     * @return bool
     */
    private function shouldTaskAppearOnDate(DailyTask $task, Carbon $date): bool
    {
        switch ($task->task_type) {
            case 'daily':
                return true;

            case 'weekly':
                return is_array($task->recurrent_days) &&
                    in_array($date->format('l'), $task->recurrent_days);

            case 'monthly':
                return $date->day == $task->day_of_month;

            default:
                return false;
        }
    }
}
