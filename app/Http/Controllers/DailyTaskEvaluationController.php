<?php

namespace App\Http\Controllers;

use App\Http\Resources\DailytaskevaluationResource;
use App\Models\DailyTask;
use App\Models\DailyTaskEvaluation;
use App\Models\DailyTaskEvaluationRevision;
use App\Models\DailyTaskReport;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DailyTaskEvaluationController extends Controller
{
    public function index($taskId)
    {
        $dailyTask = DailyTask::with(
            'evaluations',
            'evaluations.evaluator:id,name,last_name'
        )->findOrFail($taskId);

        $this->authorize('viewAny', DailyTaskEvaluation::class);

        return response()->json($dailyTask);
    }

    /**
     * Store a new evaluation for a specific task.
     */
    public function store(Request $request, $taskId)
    {
        $dailyTask = DailyTask::find($taskId);
        if (!$dailyTask) return response()->json([
            'message' => 'Task not found',
        ], 404);
        $this->authorize('create', DailyTaskEvaluation::class);

        $today = now()->toDateString();


        $validatedData = $request->validate([
            'comment' => 'nullable|string',
            'rating'  => 'required|integer|min:0|max:10',
            'label'   => 'nullable|string',
            'task_for' => 'nullable|date'
        ]);
        $existingEvaluation = DailyTaskEvaluation::where('daily_task_id', $taskId)
            ->whereDate('task_for', $validatedData['task_for'])
            ->first();

        if ($existingEvaluation) {
            return response()->json([
                'message' => 'This task already has an evaluation for today.',
            ], 409);
        }

        $evaluation = $dailyTask->evaluations()->create([
            'user_id' => Auth::id(),
            'comment' => $validatedData['comment'] ?? null,
            'rating'  => $validatedData['rating'],
            'label'   => $validatedData['label'] ?? null,
            'task_for' => $validatedData['task_for'] ?? null
        ]);

        return response()->json([
            'message'    => 'Evaluation created successfully!',
            'evaluation' => new DailytaskevaluationResource($evaluation),
        ], 201);
    }

    /**
     * Display a specific evaluation.
     */
    public function show($id)
    {
        $evaluation = DailyTaskEvaluation::findOrFail($id);
        $this->authorize('view', $evaluation);


        return response()->json($evaluation);
    }

    /**
     * Update an existing evaluation.
     */
    public function update(Request $request, $id)
    {
        $evaluation = DailyTaskEvaluation::findOrFail($id);

        $this->authorize('update', $evaluation);

        $validatedData = $request->validate([
            'comment' => 'nullable|string',
            'rating'  => 'required|integer|min:0|max:10',
            'label'   => 'nullable|string',
            'task_for' => 'nullable|date'
        ]);

        $user = Auth::user();
        $original = $evaluation->getOriginal();
        $evaluation->update($validatedData);
        $changes = $evaluation->getChanges();
        $trackableFields = ['comment', 'rating'];

        foreach ($changes as $field => $newValue) {
            if (in_array($field, $trackableFields)) {
                $oldValue = $original[$field] ?? null;
                if (is_array($oldValue)) {
                    $oldValue = json_encode($oldValue);
                }
                if (is_array($newValue)) {
                    $newValue = json_encode($newValue);
                }
                DailyTaskEvaluationRevision::create([
                    'field_name' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'user_id' => $user->id,
                    'daily_task_evaluation_id' => $evaluation->id,
                    'created_at' => now(),
                ]);
            }
        }

        return response()->json([
            'message'    => 'Evaluation updated successfully!',
            'evaluation' => new DailytaskevaluationResource($evaluation),
        ], 200);
    }
    public function destroy($id)
    {
        $evaluation = DailyTaskEvaluation::findOrFail($id);

        $this->authorize('delete', $evaluation);

        $evaluation->delete();

        return response()->json([
            'message' => 'Evaluation deleted successfully!',
        ], 200);
    }
    public function tasksOfTheDay($date = null)
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        $this->authorize('viewAny', DailyTaskEvaluation::class);
        try {
            $selectedDate = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            $selectedDate = Carbon::today()->toDateString();
        }
        $currentDayOfWeek  = Carbon::parse($selectedDate)->dayOfWeek;
        $currentDayOfMonth = Carbon::parse($selectedDate)->day;
        $tasksQuery = DailyTask::where('company_id', $company_id)
            ->where('active', 1)
            ->where(function ($query) use ($selectedDate, $currentDayOfWeek, $currentDayOfMonth) {
                $query->orWhere(function ($query) use ($selectedDate) {
                    $query->where('task_type', 'daily')
                        ->whereDate('start_date', '<=', $selectedDate);
                })
                    ->orWhere(function ($query) use ($selectedDate, $currentDayOfWeek) {
                        $query->where('task_type', 'weekly')
                            ->whereDate('start_date', '<=', $selectedDate)
                            ->whereJsonContains('recurrent_days', $currentDayOfWeek);
                    })
                    ->orWhere(function ($query) use ($selectedDate, $currentDayOfMonth) {
                        $query->where('task_type', 'monthly')
                            ->whereDate('start_date', '<=', $selectedDate)
                            ->where('day_of_month', $currentDayOfMonth);
                    })
                    ->orWhere(function ($query) use ($selectedDate) {
                        $query->where('task_type', 'single')
                            ->whereDate('start_date', $selectedDate);
                    })
                    ->orWhere(function ($query) use ($selectedDate) {
                        $query->where('task_type', 'last_day_of_month')
                            ->whereDate('start_date', $selectedDate)
                            ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [Carbon::parse($selectedDate)->day]);
                    });
            })
            ->whereHas('evaluations', function ($q) use ($selectedDate) {
                $q->whereDate('task_for', $selectedDate);
            })
            ->with([
                'department:id,name',
                'reports' => function ($q) use ($selectedDate) {
                    $q->whereDate('created_at', '=', $selectedDate)
                        ->with('submittedBy:id,name,last_name');
                },
                'evaluations' => function ($q) use ($selectedDate) {
                    $q->whereDate('task_for', $selectedDate)
                        ->with('evaluator:id,name,last_name');
                },
            ])
            ->select([
                'id',
                'task_no',
                'task_name',
                'description',
                'start_date',
                'task_type',
                'recurrent_days',
                'day_of_month',
                'active',
                'from',
                'to',
                'dept_id',
                'priority'
            ]);
        $tasks = $tasksQuery->get();
        $result = $tasks->map(function ($task) use ($selectedDate) {
            $report = $task->reports->first();
            $evaluation = $task->evaluations->first();

            return [
                'daily_task_id' => $task->id,
                'daily_task'    => [
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
                'report' => $report ? (object) [
                    'id' => $report->id,
                    "daily_task_id" => $report->daily_task_id,
                    "notes" => $report->notes,
                    "status" => $report->status,
                    "created_at" => $report->created_at->toDateTimeString(),
                    "updated_at" => $report->updated_at->toDateTimeString(),
                    "task_found" => $report->task_found,
                    'user' => (object) [
                        'id' => $report->submittedBy->id,
                        'name' => $report->submittedBy->name,
                        'last_name' => $report->submittedBy->last_name ?? null
                    ]
                ] : null,
                'department' => $task->department
                    ? [
                        'id'   => $task->department->id,
                        'name' => $task->department->name,
                    ]
                    : null,
                'has_evaluation' => true,
                'evaluation' => $evaluation
                    ? [
                        'id'         => $evaluation->id,
                        'comment'    => $evaluation->comment,
                        'rating'     => $evaluation->rating,
                        'label'      => $evaluation->label,
                        'task_for'   => $evaluation->task_for,
                        'created_at' => $evaluation->created_at->toDateTimeString(),
                        'evaluator'  => $evaluation->evaluator
                            ? [
                                'id'   => $evaluation->evaluator->id,
                                'name' => $evaluation->evaluator->name,
                                'last_name' => $evaluation->evaluator->last_name ?? null,
                            ]
                            : null,
                    ]
                    : null
            ];
        });
        return response()->json([
            'date'  => $selectedDate,
            'data' => $result,
        ]);
    }

    public function getDeptPerformance(Request $request)
    {
        $user = Auth::user();
        $this->authorize('view-chartReports', DailyTaskEvaluation::class);
        $from = $request->input('from');
        $to = $request->input('to');

        if (!$from && !$to) {
            $from = Carbon::now()->startOfMonth()->toDateString();
            $to = Carbon::now()->toDateString();
        } elseif ($from && !$to) {
            $to = $from;
        }
        try {
            $fromDate = Carbon::parse($from);
            $toDate = Carbon::parse($to)->endOfDay();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid date format. Use YYYY-MM-DD.',
            ], 400);
        }
        $departments = Department::where('company_id', $user->company_id)
            ->pluck('name', 'id');

        if ($departments->isEmpty()) {
            return response()->json(['evaluations_by_department' => []]);
        }
        $taskIds = DailyTask::where('company_id', $user->company_id)
            ->pluck('id');
        if ($taskIds->isEmpty()) {
            return response()->json(['evaluations_by_department' => []]);
        }
        $evaluations = DailyTaskEvaluation::whereIn('daily_task_id', $taskIds)
            ->whereBetween('task_for', [$from, $to])
            ->with('dailyTask:id,dept_id')
            ->get(['id', 'daily_task_id', 'rating']);
        $deptStats = [];

        foreach ($evaluations as $evaluation) {
            $deptId = $evaluation->dailyTask->dept_id;

            if (!isset($deptStats[$deptId])) {
                $deptStats[$deptId] = [
                    'department_name' => $departments[$deptId],
                    'sum_rating' => 0,
                    'count' => 0,
                ];
            }

            $deptStats[$deptId]['sum_rating'] += $evaluation->rating;
            $deptStats[$deptId]['count'] += 1;
        }
        $result = [];

        foreach ($deptStats as $deptId => $stats) {
            $totalRate = $stats['count'] > 0
                ? round($stats['sum_rating'] / ($stats['count'] * 10), 2) * 100
                : 0;

            $result[] = [
                'department_name' => $stats['department_name'],
                'total_rate' => $totalRate,
            ];
        }
        if (empty($result)) {
            return response()->json([
                'message' => 'No evaluations found for the selected period.',
                'data' => [],
                'range' => compact('from', 'to')
            ], 200);
        }
        $overallPerformance = collect($result)->avg('total_rate');
        $overallPerformance = round($overallPerformance, 2);
        return response()->json([
            'message' => "Performance Retrieved Successfully between $from to $to",
            'data' => [
                'company_performance' => $overallPerformance,
                'range' => compact('from', 'to'),
                'dept_performance' => $result,
            ]
        ], 200);
    }
    public function getUserPerformance(Request $request)
    {
        $this->authorize('view-chartReports', DailyTaskEvaluation::class);
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $userId = $request->input('user_id')? $request->input('user_id') : Auth::user()->id;
        $from = $request->input('from');
        $to = $request->input('to');
        $targetUser = User::where('id',$userId)->first(['name','email']);
        if (!$from && !$to) {
            $from = Carbon::now()->startOfMonth()->toDateString();
            $to = Carbon::now()->toDateString();
        } elseif ($from && !$to) {
            $to = $from;
        }
        try {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to)->endOfDay();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid date format. Use YYYY-MM-DD.',
            ], 400);
        }
        $reports = DailyTaskReport::where('submitted_by', $userId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select('daily_task_id', DB::raw('DATE(created_at) as report_date'))
            ->get()
            ->groupBy('daily_task_id');
    
        if ($reports->isEmpty()) {
            return response()->json([
                'message' => 'No reports found for the selected period.',
                'data' => [],
            ]);
        }
        $taskDateMap = [];
    
        foreach ($reports as $taskId => $reportGroup) {
            $taskDateMap[$taskId] = $reportGroup->pluck('report_date')->unique()->toArray();
        }
        $bindings = [];
        foreach ($taskDateMap as $taskId => $dates) {
            foreach ($dates as $date) {
                $bindings[] = ['daily_task_id' => $taskId, 'date' => $date];
            }
        }
        if (empty($bindings)) {
            return response()->json([
                'message' => 'No matching evaluations found.',
                'data' => [],
            ]);
        }
        $evaluations = DailyTaskEvaluation::whereIn('daily_task_id', array_keys($taskDateMap))
            ->whereBetween('task_for', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
            ->join('daily_tasks', 'daily_tasks.id', '=', 'daily_task_evaluations.daily_task_id')
            ->select(
                'daily_task_evaluations.*',
                'daily_tasks.dept_id'
            )
            ->get();
        if ($evaluations->isEmpty()) {
            return response()->json([
                'message' => 'No evaluations found for your reports in the selected period.',
                'data' => [],
            ]);
        }
        $deptStats = [];
    
        foreach ($evaluations as $evaluation) {
            $deptId = $evaluation->dept_id;
    
            if (!isset($deptStats[$deptId])) {
                $department = Department::find($deptId);
                $deptStats[$deptId] = [
                    'department_name' => optional($department)->name ?? 'Unknown',
                    'sum_rating' => 0,
                    'count' => 0,
                ];
            }
            $rating = data_get($evaluation, 'rating');
    
            if ($rating !== null) {
                $deptStats[$deptId]['sum_rating'] += $rating;
                $deptStats[$deptId]['count'] += 1;
            }
        }
        $result = [];
        foreach ($deptStats as $deptId => $stats) {
            $totalRate = $stats['count'] > 0
                ? round(($stats['sum_rating'] / ($stats['count'] * 10)) * 100, 2)
                : 0;
    
            $result[] = [
                'department_name' => $stats['department_name'],
                'total_rate' => $totalRate,
                'evaluation_count' => $stats['count']
            ];
        }
        $overallPerformance = collect($result)->avg('total_rate');
        $overallPerformance = round($overallPerformance, 2);
    
        return response()->json([
            'message' => "Performance Retrieved Successfully between $from to $to",
            'data' => [
                'user' => $targetUser,
                'user_performance' => $overallPerformance,
                'range' => compact('from', 'to'),
                'performance_by_department' => $result,
            ]
        ]);
    }
}
