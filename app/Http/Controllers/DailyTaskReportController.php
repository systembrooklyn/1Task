<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\DailyTaskReportResource;
use App\Models\DailyTask;
use App\Models\DailyTaskReport;
use App\Models\User;
use Carbon\Carbon;
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

    public function notReportedTasks($date)
    {
        $user = Auth::user();
    
        // Validate and parse the provided date from the URL
        try {
            $selectedDate = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date provided'], 422);
        }
    
        // Get current day details based on the selected date
        $currentDayOfWeek  = Carbon::parse($selectedDate)->dayOfWeek;
        $currentDayOfMonth = Carbon::parse($selectedDate)->day;
        $company_id        = $user->company_id;
    
        // Build the query to select only tasks for the company that match one of the schedule criteria
        // and that do NOT have a report for the selected date.
        $tasks = DailyTask::query()
            ->where('company_id', $company_id)
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
            ->whereDoesntHave('reports', function ($query) use ($selectedDate) {
                // Exclude tasks that have at least one report with a created_at date matching $selectedDate
                $query->whereDate('created_at', $selectedDate);
            })
            ->with([
                'department:id,name',
                // Even if you eager load reports, they will be empty because of the whereDoesntHave clause.
                'reports.submittedBy:id,name,last_name'
            ])
            ->select('id', 'task_name', 'task_no', 'start_date', 'task_type', 'recurrent_days', 'day_of_month', 'active', 'from', 'to', 'description', 'dept_id', 'priority')
            ->get();
    
        // Map the tasks to the desired output structure
        $result = $tasks->map(function ($task) {
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
                'department'    => $task->department ? [
                    'id'   => $task->department->id,
                    'name' => $task->department->name,
                ] : null,
                // Since we already filtered tasks with reports, these values are fixed.
                'has_report'    => false,
                'reports'       => []
            ];
        });
    
        return response()->json([
            'tasks' => $result,
        ]);
    }

    // with evaluations
    // public function notReportedTasks($date)
    // {
    //     $user = Auth::user();

    //     // Validate and parse the provided date from the URL
    //     try {
    //         $selectedDate = Carbon::parse($date)->toDateString();
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Invalid date provided'], 422);
    //     }

    //     // Get current day details based on the selected date
    //     $currentDayOfWeek  = Carbon::parse($selectedDate)->dayOfWeek;
    //     $currentDayOfMonth = Carbon::parse($selectedDate)->day;
    //     $company_id        = $user->company_id;

    //     // Build the query to select only tasks for the company that match one of the schedule criteria
    //     // and that do NOT have a report for the selected date.
    //     $tasks = DailyTask::query()
    //         ->where('company_id', $company_id)
    //         ->where('active', 1)
    //         ->where(function ($query) use ($selectedDate, $currentDayOfWeek, $currentDayOfMonth) {
    //             $query->orWhere(function ($query) use ($selectedDate) {
    //                 $query->where('task_type', 'daily')
    //                     ->whereDate('start_date', '<=', $selectedDate);
    //             })
    //             ->orWhere(function ($query) use ($selectedDate, $currentDayOfWeek) {
    //                 $query->where('task_type', 'weekly')
    //                     ->whereDate('start_date', '<=', $selectedDate)
    //                     ->whereJsonContains('recurrent_days', $currentDayOfWeek);
    //             })
    //             ->orWhere(function ($query) use ($selectedDate, $currentDayOfMonth) {
    //                 $query->where('task_type', 'monthly')
    //                     ->whereDate('start_date', '<=', $selectedDate)
    //                     ->where('day_of_month', $currentDayOfMonth);
    //             })
    //             ->orWhere(function ($query) use ($selectedDate) {
    //                 $query->where('task_type', 'single')
    //                     ->whereDate('start_date', $selectedDate);
    //             })
    //             ->orWhere(function ($query) use ($selectedDate) {
    //                 $query->where('task_type', 'last_day_of_month')
    //                     ->whereDate('start_date', $selectedDate)
    //                     ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [Carbon::parse($selectedDate)->day]);
    //             });
    //         })
    //         ->whereDoesntHave('reports', function ($query) use ($selectedDate) {
    //             // Exclude tasks that have at least one report with a created_at date matching $selectedDate
    //             $query->whereDate('created_at', $selectedDate);
    //         })
    //         ->with([
    //             'department:id,name',
    //             'reports.submittedBy:id,name',
    //             'evaluations' => function ($query) use ($selectedDate) {
    //                 // Filter evaluations by the created_at date
    //                 $query->whereDate('created_at', $selectedDate)
    //                     ->with('evaluator:id,name');
    //             },
    //         ])
    //         ->select('id', 'task_name', 'task_no', 'start_date', 'task_type', 'recurrent_days', 'day_of_month', 'active', 'from', 'to', 'description', 'dept_id')
    //         ->get();

    //     // Map the tasks to the desired output structure
    //     $result = $tasks->map(function ($task) {
    //         return [
    //             'daily_task_id' => $task->id,
    //             'daily_task'    => [
    //                 'task_no'        => $task->task_no,
    //                 'task_name'      => $task->task_name,
    //                 'description'    => $task->description,
    //                 'start_date'     => $task->start_date,
    //                 'task_type'      => $task->task_type,
    //                 'recurrent_days' => $task->recurrent_days,
    //                 'day_of_month'   => $task->day_of_month,
    //                 'active'         => $task->active,
    //                 'from'           => $task->from,
    //                 'to'             => $task->to,
    //             ],
    //             'department'    => $task->department ? [
    //                 'id'   => $task->department->id,
    //                 'name' => $task->department->name,
    //             ] : null,
    //             'has_report'    => false, // Since we already filtered tasks with reports, these values are fixed.
    //             'reports'       => [],
    //             'evaluations'   => $task->evaluations->map(function ($evaluation) {
    //                 return [
    //                     'id'      => $evaluation->id,
    //                     'comment' => $evaluation->comment,
    //                     'rating'  => $evaluation->rating,
    //                     'evaluator' => $evaluation->evaluator ? [
    //                         'id'   => $evaluation->evaluator->id,
    //                         'name' => $evaluation->evaluator->name,
    //                     ] : null,
    //                 ];
    //             }),
    //         ];
    //     });

    //     return response()->json([
    //         'tasks' => $result,
    //     ]);
    // }




    public function index($date = null)
    {
        $authUser = Auth::user();
        $user = User::find($authUser->id);
        $hasPermission = $user->hasAssignedPermission('view-dailyTaskReports');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $reports = DailyTaskReport::whereHas('dailyTask', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        })
        ->whereDate('created_at', $date)
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

    //with evaluations
//     public function index(Request $request)
// {
//     $user = Auth::user();
//     $hasPermission = $user->hasAssignedPermission('view-dailyTaskReports');
//     $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

//     // Fetch reports with evaluations and related data
//     $reports = DailyTaskReport::whereHas('dailyTask', function ($query) use ($user) {
//         $query->where('company_id', $user->company_id);
//     })
//     ->with([
//         'dailyTask.department',
//         'dailyTask.evaluations.evaluator:id,name', // Eager load evaluations and evaluator details
//         'submittedBy:id,name',
//     ])
//     ->get();

//     if (!($hasPermission || $isOwner)) {
//         return response()->json([
//             'message' => 'You don\'t have permission to view daily task reports.',
//         ], 403);
//     }

//     // Map the reports to include evaluations
//     $reports = $reports->map(function ($report) {
//         return [
//             'id'          => $report->id,
//             'daily_task'   => [
//                 'id'          => $report->dailyTask->id,
//                 'task_name'   => $report->dailyTask->task_name,
//                 'description' => $report->dailyTask->description,
//                 'department' => $report->dailyTask->department ? [
//                     'id'   => $report->dailyTask->department->id,
//                     'name' => $report->dailyTask->department->name,
//                 ] : null,
//             ],
//             'submitted_by' => $report->submittedBy ? [
//                 'id'   => $report->submittedBy->id,
//                 'name' => $report->submittedBy->name,
//             ] : null,
//             'evaluations'  => $report->dailyTask->evaluations->map(function ($evaluation) {
//                 return [
//                     'id'      => $evaluation->id,
//                     'comment' => $evaluation->comment,
//                     'rating'  => $evaluation->rating,
//                     'evaluator' => $evaluation->evaluator ? [
//                         'id'   => $evaluation->evaluator->id,
//                         'name' => $evaluation->evaluator->name,
//                     ] : null,
//                 ];
//             }),
//         ];
//     });

//     return response()->json([
//         'reports' => $reports,
//     ]);
// }

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
        $authUser = Auth::user();
        $user = User::find($authUser->id);
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
