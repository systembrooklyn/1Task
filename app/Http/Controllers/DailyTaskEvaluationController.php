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
        $dailyTask = DailyTask::with('evaluations')->findOrFail($taskId);

        // Authorize the action
        $this->authorize('viewAny', DailyTaskEvaluation::class);

        return response()->json($dailyTask);
    }

    /**
     * Store a new evaluation for a specific task.
     */
    public function store(Request $request, $taskId)
    {
        $dailyTask = DailyTask::findOrFail($taskId);

        // Authorize the action
        $this->authorize('create', DailyTaskEvaluation::class);

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

        // Authorize the action

        return response()->json($evaluation);
    }

    /**
     * Update an existing evaluation.
     */
    public function update(Request $request, $id)
    {
        $evaluation = DailyTaskEvaluation::findOrFail($id);

        // Authorize the action
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

        // Authorize the action
        $this->authorize('delete', $evaluation);

        $evaluation->delete();

        return response()->json([
            'message' => 'Evaluation deleted successfully!',
        ], 200);
    }
}
