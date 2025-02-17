<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Models\DailyTaskEvaluation;
use App\Models\DailyTaskEvaluationRevision;
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
}
