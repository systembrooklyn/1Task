<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Models\DailyTaskEvaluation;
use App\Models\DailyTaskEvaluationRevision;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyTaskEvaluationController extends Controller
{
    public function index($taskId)
    {
        $dailyTask = DailyTask::with('evaluations',
        'evaluations.evaluator:id,name')->findOrFail($taskId);

        $this->authorize('viewAny', DailyTaskEvaluation::class);

        return response()->json($dailyTask);
    }

    /**
     * Store a new evaluation for a specific task.
     */
    public function store(Request $request, $taskId)
    {
        $dailyTask = DailyTask::find($taskId);
        if(!$dailyTask) return response()->json([
            'message' => 'Task not found',
        ], 404);
        $this->authorize('create', DailyTaskEvaluation::class);

        $today = now()->toDateString();
        $existingEvaluation = DailyTaskEvaluation::where('daily_task_id', $taskId)
            ->whereDate('created_at', $today)
            ->first();

        if ($existingEvaluation) {
            return response()->json([
                'message' => 'This task already has an evaluation for today.',
            ], 409);
        }

        $validatedData = $request->validate([
            'comment' => 'nullable|string',
            'rating'  => 'required|integer|min:0|max:10',
            'label'   => 'nullable|string'
        ]);

        $evaluation = $dailyTask->evaluations()->create([
            'user_id' => Auth::id(),
            'comment' => $validatedData['comment'] ?? null,
            'rating'  => $validatedData['rating'],
        ]);

        return response()->json([
            'message'    => 'Evaluation created successfully!',
            'evaluation' => $evaluation,
        ]);
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
            'label'   => 'nullable|string'
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
            'evaluation' => $evaluation,
        ]);
    }

    /**
     * Delete an evaluation.
     */
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
    
        // 1) Parse the date from the URL param; fallback to today if invalid
        try {
            $selectedDate = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            $selectedDate = Carbon::today()->toDateString();
        }
    
        // 2) (Optional) If you need the daily/weekly/monthly logic, define it:
        $currentDayOfWeek  = Carbon::parse($selectedDate)->dayOfWeek; 
        $currentDayOfMonth = Carbon::parse($selectedDate)->day;       
    
        // 3) Build query to fetch tasks *only* if:
        //    - They match the daily/weekly/monthly schedule, etc.
        //    - They *have* an evaluation for the given date.
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
                // Only include tasks that have an evaluation on the selected date
                $q->whereDate('created_at', $selectedDate);
            })
            ->with([
                // Eager-load department
                'department:id,name',
                // Eager-load only the evaluations from that date
                'evaluations' => function ($q) use ($selectedDate) {
                    $q->whereDate('created_at', $selectedDate)
                      ->with('evaluator:id,name');
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
            ]);
    
        // 4) Get the tasks
        $tasks = $tasksQuery->get();
    
        // 5) Transform them into your desired response structure
        $result = $tasks->map(function ($task) use ($selectedDate) {
            // There is at least one evaluation (by definition of whereHas),
            // but let's show the first as an "object" or all if you need.
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
                ],
                'department' => $task->department
                    ? [
                        'id'   => $task->department->id,
                        'name' => $task->department->name,
                    ]
                    : null,
    
                // We already know it has an evaluation, but let's keep a clear boolean
                'has_evaluation' => true,
    
                // Return single evaluation as an object
                'evaluation' => $evaluation
                    ? [
                        'id'         => $evaluation->id,
                        'comment'    => $evaluation->comment,
                        'rating'     => $evaluation->rating,
                        'label'      => $evaluation->label,
                        'created_at' => $evaluation->created_at,
                        'evaluator'  => $evaluation->evaluator
                            ? [
                                'id'   => $evaluation->evaluator->id,
                                'name' => $evaluation->evaluator->name,
                            ]
                            : null,
                    ]
                    : null
            ];
        });
    
        // 6) Return JSON
        return response()->json([
            'date'  => $selectedDate,
            'data' => $result,
        ]);
    }
}
