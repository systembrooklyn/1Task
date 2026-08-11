<?php

namespace App\Modules\DailyTask\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DailyTask\Http\Requests\StoreEvaluationRequest;
use App\Modules\DailyTask\Http\Requests\UpdateEvaluationRequest;
use App\Modules\DailyTask\Http\Requests\GetDeptPerformanceRequest;
use App\Modules\DailyTask\Http\Requests\GetUserPerformanceRequest;
use App\Modules\DailyTask\Services\DailyTaskEvaluationService;
use App\Modules\DailyTask\Http\Resources\DailyTaskEvaluationResource;
use App\Models\DailyTask;
use App\Models\DailyTaskEvaluation;
use App\Modules\DailyTask\Http\Traits\ChecksCompanyScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DailyTaskEvaluationController extends Controller
{
    use ChecksCompanyScope;
    public function __construct(protected DailyTaskEvaluationService $evalService) {}

    public function index(int $taskId): JsonResponse
    {
        $dailyTask = DailyTask::with('evaluations.evaluator:id,name,last_name')->findOrFail($taskId);
        $this->ensureSameCompany($dailyTask);
        $this->authorize('viewAny', DailyTaskEvaluation::class);
        return response()->json($dailyTask);
    }

    public function store(StoreEvaluationRequest $request, int $taskId): JsonResponse
    {
        $dailyTask = DailyTask::find($taskId);
        if (!$dailyTask) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $this->ensureSameCompany($dailyTask);
        $this->authorize('create', DailyTaskEvaluation::class);
        $user = Auth::user();
        $existing = $this->evalService->getEvaluationByTaskAndDate($taskId, $request->input('task_for'));
        if ($existing) {
            return response()->json(['message' => 'This task already has an evaluation for today.'], 409);
        }
        $evaluation = $this->evalService->createEvaluation($dailyTask, $request->validated(), $user->id);
        return response()->json([
            'message' => 'Evaluation created successfully!',
            'evaluation' => new DailyTaskEvaluationResource($evaluation),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $evaluation = DailyTaskEvaluation::findOrFail($id);
        $this->ensureSameCompany($evaluation);
        $this->authorize('view', $evaluation);
        return response()->json($evaluation);
    }

    public function update(UpdateEvaluationRequest $request, int $id): JsonResponse
    {
        $evaluation = DailyTaskEvaluation::findOrFail($id);
        $this->ensureSameCompany($evaluation);
        $this->authorize('update', $evaluation);
        $user = Auth::user();
        $updated = $this->evalService->updateEvaluation($evaluation, $request->validated(), $user->id);
        return response()->json([
            'message' => 'Evaluation updated successfully!',
            'evaluation' => new DailyTaskEvaluationResource($updated),
        ], 200);
    }

    public function tasksOfTheDay($date = null): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('viewAny', DailyTaskEvaluation::class);
        $result = $this->evalService->getTasksOfTheDay($user->company_id, $date);
        return response()->json([
            'date' => $result['date'],
            'data' => $result['data'],
        ]);
    }

    public function getDeptPerformance(GetDeptPerformanceRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->authorize('view-chartReports', DailyTaskEvaluation::class);
        $result = $this->evalService->getDeptPerformance(
            $user->company_id,
            $request->input('from'),
            $request->input('to')
        );
        return response()->json($result);
    }

    public function getUserPerformance(GetUserPerformanceRequest $request): JsonResponse
    {
        $this->authorize('view-chartReports', DailyTaskEvaluation::class);
        $userId = $request->input('user_id') ?? Auth::id();
        $result = $this->evalService->getUserPerformance(
            $userId,
            $request->input('from'),
            $request->input('to')
        );
        return response()->json($result);
    }
}
