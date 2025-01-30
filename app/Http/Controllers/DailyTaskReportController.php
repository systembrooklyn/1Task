<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\DailyTaskReportResource;
use App\Models\DailyTask;
use App\Models\DailyTaskReport;
use Illuminate\Support\Facades\Auth;

class DailyTaskReportController extends Controller
{
    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $query = DailyTaskReport::with(['dailyTask', 'submittedBy']);
    //     if ($request->has(['start_date', 'end_date'])) {
    //         $request->validate([
    //             'start_date' => 'required|date',
    //             'end_date' => 'required|date|after_or_equal:start_date',
    //         ]);

    //         $query->whereBetween('created_at', [
    //             $request->start_date . ' 00:00:00',
    //             $request->end_date . ' 23:59:59',
    //         ]);
    //     }
    //     $sortBy = $request->get('sort_by', 'created_at');
    //     $sortOrder = $request->get('sort_order', 'desc');

    //     $allowedSorts = ['created_at', 'status', 'submitted_by'];
    //     if (!in_array($sortBy, $allowedSorts)) {
    //         $sortBy = 'created_at';
    //     }

    //     $allowedOrders = ['asc', 'desc'];
    //     if (!in_array($sortOrder, $allowedOrders)) {
    //         $sortOrder = 'desc';
    //     }

    //     $query->orderBy($sortBy, $sortOrder);
    //     $perPage = $request->get('per_page', 15);
    //     $reports = $query->paginate($perPage);
    //     $reportsData = DailyTaskReportResource::collection($reports->items());
    //     return response()->json([
    //         'reports' => $reportsData,
    //         'pagination' => [
    //             'total' => $reports->total(),
    //             'current_page' => $reports->currentPage(),
    //             'per_page' => $reports->perPage(),
    //             'last_page' => $reports->lastPage(),
    //             'next_page_url' => $reports->nextPageUrl(),
    //             'prev_page_url' => $reports->previousPageUrl(),
    //         ],
    //     ]);
    // }


    public function index(Request $request)
    {
        $user = Auth::user();
        $hasPermission = $user->hasAssignedPermission('view-dailyTaskReports');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        $reports = DailyTaskReport::whereHas('dailyTask', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
        ->with(['dailyTask.department', 'submittedBy'])
        ->get();
        
        if(!($hasPermission || $isOwner)){
            return response()->json([
                'message' => 'you dont have permission to view daily task reports',
            ]);
        }
        else{
            return response()->json([
                'reports' => $reports,
            ]);
        }
    }

    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $hasPermission = $user->hasAssignedPermission('view-dailyTaskReports');
    //     $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

    //     // Check if the user has permission or is an owner
    //     if (!($hasPermission || $isOwner)) {
    //         return response()->json([
    //             'message' => 'You don\'t have permission to view daily task reports',
    //         ]);
    //     }

    //     // Get the current date
    //     $currentDate = now()->toDateString();

    //     // Fetch reports along with daily task data, including a flag for tasks without a report on the current day
    //     $reports = DailyTask::where('company_id', $user->company_id)
    //         ->with(['reports' => function ($query) use ($currentDate) {
    //             $query->whereDate('created_at', $currentDate)
    //             ->with('submittedBy');
    //         }, 'department'])
    //         ->get()
    //         ->map(function ($task) use ($currentDate) {
    //             // Check if the task has a report for today
    //             $todayReport = $task->reports->first();
    //             $hasReportToday = $todayReport ? true : false;

    //             return [
    //                 'task' => $task,
    //                 'has_report_today' => $hasReportToday,
    //                 'report' => $todayReport ? $todayReport : null, 
    //                 'submitted_by_user' => $todayReport ? $todayReport->submittedBy : null, // Retrieve submittedBy user details
    //         ];
    //         });

    //     return response()->json([
    //         'reports' => $reports,
    //     ]);
    // }


    public function submitReport(Request $request, $dailyTaskId)
    {
        $user = Auth::user();
        $dailyTask = DailyTask::findOrFail($dailyTaskId);
        $this->authorize('report', $dailyTask);
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'status' => 'required|in:done,not_done',
            'task_found' => 'nullable|boolean'
        ]);
        $reportDate = now()->toDateString();
        $existingReport = DailyTaskReport::where('daily_task_id', $dailyTask->id)
                                         ->whereDate('created_at', $reportDate)
                                         ->first();

        if ($existingReport) {
            return response()->json([
                'message' => 'A report for this task today already exists.',
                'report' => new DailyTaskReportResource($existingReport),
            ], 409);
        }
        $report = DailyTaskReport::create([
            'daily_task_id' => $dailyTask->id,
            'submitted_by' => $user->id,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
            'task_found' =>$validated['task_found'] ?? null
        ]);
        return response()->json([
            'message' => 'Report submitted successfully.',
            'report' => new DailyTaskReportResource($report),
        ], 201);
    }

    /**
     * Retrieve reports for daily tasks for today.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function todaysReports()
    {
        $user = Auth::user();
        $hasPermission = $user->hasAssignedPermission('view-dailyTaskReports');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        $today = now()->toDateString();
        $dailyTasks = DailyTask::with(['reports' => function ($query) use ($today) {
                                $query->whereDate('created_at', $today);
                            }])
                            ->where('company_id', $user->company_id)
                            ->get();
        $tasksData = $dailyTasks->map(function ($task) {
            return [
                'id' => $task->id,
                'task_name' => $task->task_name,
                'has_today_report' => $task->reports->isNotEmpty(),
                'report' => $task->reports->first() ? new DailyTaskReportResource($task->reports->first()) : null,
            ];
        });
        if(!($hasPermission || $isOwner)){
            return response()->json([
                'message' => 'you dont have permission to view daily task reports',
            ]);
        }
        else{
            return response()->json([
                'date' => $today,
                'tasks' => $tasksData,
            ], 200);
        }
    }
}
