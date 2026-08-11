<?php

namespace App\Modules\DailyTask\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DailyTask\Http\Requests\SubmitReportRequest;
use App\Modules\DailyTask\Services\DailyTaskReportService;
use App\Modules\DailyTask\Http\Resources\DailyTaskReportResource;
use App\Models\DailyTask;
use App\Modules\DailyTask\Http\Traits\ChecksCompanyScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyTaskReportController extends Controller
{
    use ChecksCompanyScope;
    public function __construct(protected DailyTaskReportService $reportService) {}

    public function submitReport(SubmitReportRequest $request, int $dailyTaskId): JsonResponse
    {
        $user = Auth::user();
        $dailyTask = DailyTask::findOrFail($dailyTaskId);
        $this->ensureSameCompany($dailyTask);
        $this->authorize('report', $dailyTask);
        $report = $this->reportService->submitReport($dailyTask, $request->validated(), $user->id);
        if ($report->wasRecentlyCreated === false) {
            return response()->json([
                'message' => 'A report for this task today already exists.',
                'report' => new DailyTaskReportResource($report),
            ], 409);
        }
        return response()->json([
            'message' => 'Report submitted successfully.',
            'report' => new DailyTaskReportResource($report),
        ], 201);
    }

    public function index($date = null): JsonResponse
    {
        $user = Auth::user();
        $hasPermission = $user->hasAssignedPermission('view-dailyTaskReports');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!$hasPermission && !$isOwner) {
            return response()->json(['message' => 'you dont have permission to view daily task reports'], 403);
        }
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $reports = $this->reportService->getReportsByDate($user->company_id, $date, ['dailyTask.department', 'submittedBy']);
        return response()->json(['reports' => $reports]);
    }

    public function notReportedTasks($date): JsonResponse
    {
        $user = Auth::user();
        try {
            $selectedDate = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date provided'], 422);
        }
        $tasks = $this->reportService->getNotReportedTasks($user->company_id, $selectedDate);
        $result = $tasks->map(function ($task) {
            return [
                'daily_task_id' => $task->id,
                'daily_task' => [
                    'task_no'        => $task->task_no,
                    'task_name'      => $task->task_name,
                    'description'    => $task->description,
                    'start_date'     => $task->start_date,
                    'task_type'      => $task->task_type,
                    'recurrent_days' => $task->recurrent_days,
                    'day_of_month'   => $task->day_of_month,
                    'active'         => $task->active,
                    'from'           => $task->from,
                    'to'             => $task->to,
                    'priority'       => $task->priority
                ],
                'department' => $task->department ? [
                    'id'   => $task->department->id,
                    'name' => $task->department->name,
                ] : null,
                'has_report' => false,
                'reports' => []
            ];
        });
        return response()->json(['tasks' => $result]);
    }

    public function todaysReports(): JsonResponse
    {
        $user = Auth::user();
        $hasPermission = $user->hasAssignedPermission('view-dailyTaskReports');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        if (!$hasPermission && !$isOwner) {
            return response()->json(['message' => 'you dont have permission to view daily task reports'], 403);
        }
        $tasks = $this->reportService->getTodaysReports($user->company_id);
        $tasksData = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'task_name' => $task->task_name,
                'has_today_report' => $task->reports->isNotEmpty(),
                'report' => $task->reports->first() ? new DailyTaskReportResource($task->reports->first()) : null,
            ];
        });
        return response()->json([
            'date' => now()->toDateString(),
            'tasks' => $tasksData,
        ], 200);
    }
}
