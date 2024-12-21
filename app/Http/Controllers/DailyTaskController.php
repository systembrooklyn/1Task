<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyTaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
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
            'note' => 'nullable|string',
            'status' => 'required|in:done,not_done',
        ]);
        $user = Auth::user();
        $company_id = $user->company_id;
        $haveAccess = false;
        $permissions = $user->getAllPermissions();
        foreach($permissions as $permission){
            if($permission->name == "create-dailytask") {
                $haveAccess = true;
            break;
        }
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        if($haveAccess || $isOwner){
            $task = DailyTask::create([
                'task_no' => 'TASK-' . strtoupper(uniqid()),
                'task_name' => $request->task_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'task_type' => $request->task_type,
                'recurrent_days' => $request->recurrent_days,
                'day_of_month' => $request->day_of_month,
                'from' => $request->from,
                'to' => $request->to,
                'company_id' => $company_id,
                'dept_id' => $request->dept_id,
                'created_by' => $user->id,
                'assigned_to' => $request->assigned_to,
                'note' => $request->note,
                'status' => 'not_done',
            ]);
            return response()->json(['task' => $task], 201);
        }else return response()->json(['message' => 'You do not have permission to create daily task'], 401);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'task_type' => 'required|in:single,daily,weekly,monthly,last_day_of_month',
            'recurrent_days' => 'nullable|array',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'from' => 'required|date_format:H:i',
            'to' => 'required|date_format:H:i',
            'assigned_to' => 'nullable|exists:users,id',
            'note' => 'nullable|string',
            'status' => 'required|in:done,not_done',
        ]);
        $company_id = $user->company_id;
        $haveAccess = false;
        $permissions = $user->getAllPermissions();
        foreach($permissions as $permission){
            if($permission->name == "edit-dailytask") {
                $haveAccess = true;
            break;
        }
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        if($haveAccess || $isOwner){
            $task = DailyTask::findOrFail($id);
            if ($user->company_id !== $task->company_id || !$user->departments->contains($task->dept_id)) {
                return response()->json(['message' => 'You do not have permission to update this task.'], 403);
            }
            $task->task_name = $validated['task_name'];
            $task->description = $validated['description'];
            $task->start_date = $validated['start_date'];
            $task->task_type = $validated['task_type'];
            $task->recurrent_days = $validated['recurrent_days'] ?? $task->recurrent_days;
            $task->day_of_month = $validated['day_of_month'] ?? $task->day_of_month;
            $task->from = $validated['from'];
            $task->to = $validated['to'];
            $task->assigned_to = $validated['assigned_to'] ?? $task->assigned_to;
            $task->note = $validated['note'];
            $task->status = $validated['status'];
            $task->updated_by = $user->id;
            $task->save();
            return response()->json(['message' => 'Task updated successfully!', 'task' => $task]);
        }else return response()->json(['message' => 'You do not have permission to edit daily task'], 401);
    }

    public function destroy($id)
    {
        $task = DailyTask::findOrFail($id);
        $user = Auth::user();
        $company_id = $user->company_id;
        $haveAccess = false;
        $permissions = $user->getAllPermissions();
        foreach($permissions as $permission){
            if($permission->name == "delete-dailytask") {
                $haveAccess = true;
            break;
        }
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        if($haveAccess || $isOwner){
            if ($task->company_id !== $user->company_id) {
                return response()->json(['error' => 'You do not belong to this company.'], 403);
            }

            $task->delete();

            return response()->json(['message' => 'Task deleted successfully.']);
        }else return response()->json(['message' => 'You do not have permission to edit daily task'], 401);
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        $haveAccess = false;
        $permissions = $user->getAllPermissions();
        foreach ($permissions as $permission) {
            if ($permission->name == "view-dailytask") {
                $haveAccess = true;
                break;
            }
        }
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();

        if ($haveAccess || $isOwner) {
            $today = now()->format('Y-m-d');
            $currentDayOfWeek = now()->dayOfWeek;
            $currentDayOfMonth = now()->day;
            $perPage = $request->input('per_page', 10);
            if ($isOwner) {
                $tasks = DailyTask::where('company_id', $company_id)
                    ->where(function ($query) use ($today, $currentDayOfWeek, $currentDayOfMonth) {
                        $query->orWhere(function ($query) use ($today) {
                            $query->where('task_type', 'daily')->whereDate('start_date','<=', $today);
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
                            $query->where('task_type', 'single')->whereDate('start_date', $today);
                        })
                        ->orWhere(function ($query) use ($today) {
                            $query->where('task_type', 'last_day_of_month')
                                ->whereDate('start_date', $today)
                                ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [now()->day]);
                        });
                    })
                    ->paginate($perPage);
            } else {
                $tasksQuery = DailyTask::where('company_id', $company_id)
                    ->whereIn('dept_id', $user->departments->pluck('id'))
                    ->where(function ($query) use ($today, $currentDayOfWeek, $currentDayOfMonth) {
                        $query->orWhere(function ($query) use ($today) {
                            $query->where('task_type', 'daily')->whereDate('start_date','<=', $today);
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
                            $query->where('task_type', 'single')->whereDate('start_date', $today);
                        })
                        ->orWhere(function ($query) use ($today) {
                            $query->where('task_type', 'last_day_of_month')
                                ->whereDate('start_date', $today)
                                ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [now()->day]);
                        });
                    });
                $tasks = $tasksQuery->paginate($perPage);
            }
            return response()->json([
                'tasks' => $tasks->items(),
                'pagination' => [
                    'total' => $tasks->total(),
                    'current_page' => $tasks->currentPage(),
                    'per_page' => $tasks->perPage(),
                    'last_page' => $tasks->lastPage(),
                    'next_page_url' => $tasks->nextPageUrl(),
                    'prev_page_url' => $tasks->previousPageUrl(),
                ],
            ]);
        } else {
            return response()->json(['message' => 'You do not have permission to view daily tasks'], 401);
        }
    }
    public function show($id)
    {
        $user = Auth::user();
        $task = DailyTask::where('company_id', $user->company_id)
            ->where('dept_id', $user->dept_id)
            ->findOrFail($id);

        return response()->json(['task' => $task]);
    }
}
