<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Resources\DailyTaskResource;
use App\Models\DailyTaskRevision;
use Illuminate\Support\Facades\Log;

class DailyTaskController extends Controller
{

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
        ]);
        if ($validated['task_type'] === 'daily') {
            $validated['recurrent_days'] = null;
            $validated['day_of_month'] = null;
        } elseif ($validated['task_type'] === 'weekly') {
            $validated['day_of_month'] = null;
        } elseif ($validated['task_type'] === 'monthly') {
            $validated['recurrent_days'] = null;
        }
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
                    'company_id' => $companyId,
                    'dept_id' => $validated['dept_id'],
                    'created_by' => $user->id,
                    'assigned_to' => $validated['assigned_to'] ?? null,
                    'active' => true,
                    'updated_by' => null,
                ]);
            });

            $task->load(['department', 'creator', 'assignee', 'updatedBy', 'submittedBy']);

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
            'description' => $validated['description'] ?? $task->description,
            'start_date' => $validated['start_date'] ?? $task->start_date,
            'task_type' => $validated['task_type'] ?? $task->task_type,
            'recurrent_days' => $validated['recurrent_days'] ?? null,
            'day_of_month' => $validated['day_of_month'] ?? null,
            'from' => $validated['from'] ?? $task->from,
            'to' => $validated['to'] ?? $task->to,
            'assigned_to' => $validated['assigned_to'] ?? $task->assigned_to,
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

    public function index(Request $request)
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        $this->authorize('viewAny', DailyTask::class);
        $per_page = $request->input('per_page');
        $deptFilter = $request->input('dept_filter');
        $sort_by = $request->input('sort_by', 'from');
        $type_of = $request->input('type_of', 'asc');
        $allowedSorts = ['start_date','from'];
        if (!in_array($sort_by, $allowedSorts)) {
            $sort_by = 'from';
        }
        $type_of = strtolower($type_of);
        if (!in_array($type_of, ['asc', 'desc'])) {
            $type_of = 'asc';
        }

        $today = now()->format('Y-m-d');
        $currentDayOfWeek = now()->dayOfWeek;
        $currentDayOfMonth = now()->day;
        $departmentIds = $user->departments()->pluck('departments.id')->toArray();
        $perPage = $request->input('per_page', $per_page ? $per_page : 10);

        $tasksQuery = DailyTask::query()
            ->where('company_id', $company_id)
            ->where('active',1)
            ->whereIn('dept_id', $deptFilter? $deptFilter : $departmentIds)
            ->where(function ($query) use ($today, $currentDayOfWeek, $currentDayOfMonth) {
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
            ->select('daily_tasks.*', 'daily_task_reports.status as today_report_status')
            ->orderByRaw("CASE
            WHEN daily_task_reports.daily_task_id IS NULL THEN 1
            WHEN daily_task_reports.status = 'done' THEN 2
            WHEN daily_task_reports.status = 'not_done' THEN 3
            ELSE 4 END")
            ->orderBy($sort_by, $type_of);

        $tasks = $tasksQuery->paginate($perPage);
        $tasksData = DailyTaskResource::collection($tasks->items());
        
        return response()->json([
            'tasks' => $tasksData,
            'pagination' => [
                'total' => $tasks->total(),
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'last_page' => $tasks->lastPage(),
                'next_page_url' => $tasks->nextPageUrl(),
                'prev_page_url' => $tasks->previousPageUrl(),
            ],
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        $task = DailyTask::with(['department', 'creator', 'assignee', 'updatedBy', 'submittedBy', 'revisions.user'])->findOrFail($id);
        $this->authorize('view', $task);

        return (new DailyTaskResource($task))
                ->response()
                ->setStatusCode(200);
    }

    public function allDailyTasks(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $user = Auth::user();
        $this->authorize('viewAny', DailyTask::class);
        $sort_by = $request->input('sort_by', 'created_at');
        $type_of = $request->input('type_of', 'desc');
        $allowedSorts = ['start_date','created_at'];
        if (!in_array($sort_by, $allowedSorts)) {
            $sort_by = 'created_at';
        }
        $type_of = strtolower($type_of);
        if (!in_array($type_of, ['asc', 'desc'])) {
            $type_of = 'desc';
        }

        $tasks = DailyTask::with(['department', 'creator', 'assignee', 'updatedBy', 'submittedBy'])
                          ->where('company_id', $user->company_id)
                          ->orderBy($sort_by, $type_of)
                          ->paginate($perPage);
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
    
    public function activeDailyTask(Request $request, $id){
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
        $user = Auth::user();
        $today = now()->toDateString();
        $dailyTasks = DailyTask::with(['todayReport', 'department', 'creator', 'assignee', 'submittedBy', 'updatedBy'])
                        ->where('company_id', $user->company_id)
                        ->whereIn('dept_id', $user->departments()->pluck('departments.id')->toArray())
                        ->get();
        $tasksData = DailyTaskResource::collection($dailyTasks);
        return response()->json([
            'date' => $today,
            'tasks' => $tasksData,
        ], 200);
    }
}
