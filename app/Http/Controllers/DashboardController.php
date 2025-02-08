<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Models\DailyTaskReport;
use App\Models\Department;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{


    protected $user;
    protected $companyId;
    protected $departmentIds;
    protected $permissionOwner;
    protected $permissionDashboard;
    protected $isOwner;

    /**
     * BaseController constructor.
     */
    public function __construct(){
        $this->middleware(function ($request, $next) {
            $this->user = auth('sanctum')->user();

            if (!$this->user) {
                abort(401, 'Unauthorized');
            }
            $this->companyId = $this->user->company_id;
            $this->departmentIds = $this->user->departments()->pluck('departments.id')->toArray();
            $this->permissionOwner = $this->user->getAllPermissions()
                                            ->where('name', 'view-dashboard-owner')
                                            ->pluck('name')
                                            ->toArray();
            $this->permissionDashboard = $this->user->getAllPermissions()
                                            ->where('name', 'view-dashboard')
                                            ->pluck('name')
                                            ->toArray();
            $this->isOwner = $this->user->companies()->wherePivot('company_id', $this->user->company_id)->exists();

            return $next($request);
        });
    }

    /**
     * Retrieve the department IDs associated with the logged-in user.
     *
     * @return array
     */
    protected function getCounts($date = null){
        $selectedDate = $date
        ? Carbon::parse($date)
        : now();
        if($this->isOwner || $this->permissionOwner){
            $countEmps = $this->countOwnerEmps($selectedDate);
            $countProjects = $this->countProjects($selectedDate);
            $countDepartments = $this->countOwnerDepts($selectedDate);
            $countDailyTasks = $this->countOwnerDeptDailyTasks($selectedDate);
            $countAllDailyTasks = $this->countOwnerAllDailyTasks($selectedDate);
        }elseif($this->permissionDashboard){
            $countEmps = $this->countDeptEmps($selectedDate);
            $countProjects = $this->countProjects($selectedDate);
            $countDepartments = null;
            $countDailyTasks = $this->countDeptDailyTasks($selectedDate);
            $countAllDailyTasks = null;
        }else{
            return response()->json(['message' => 'You Dont have permission to view Dashboard'], 403);
        }
        return response()->json([
            'message'=>'Dashboard retrieved successfully',
            'Emps'=>$countEmps,
            'Projects'=>$countProjects,
            'AllDailyTasks'=>$countAllDailyTasks,
            'DailyTasks'=>$countDailyTasks,
            'Departments'=>$countDepartments,
        ]);
    }
    protected function countOwnerEmps($date = null){
        $total = User::where('company_id',$this->companyId)
                        ->WhereDate('created_at','<=',$date)
                        ->count();
        $Invited = Invitation::where('company_id',$this->companyId)
                                ->WhereDate('created_at','<=',$date)
                                ->count();
        $pending = Invitation::where('company_id',$this->companyId)
                                ->WhereDate('created_at','<=',$date)
                               ->where('is_accepted',0)
                               ->count();
        return ['total'=>$total-1,'invited'=>$Invited,'pending'=>$pending];
    }

    protected function countDeptEmps($date = null){
        $total = User::where('company_id',$this->companyId)
                        ->WhereDate('created_at','<=',$date)
                       ->whereHas('departments',function($query){
                        $query->where('departments.id',$this->departmentIds);
                       })->count();
        return ['total'=>$total,'invited'=>null,'pending'=>null];
    }

    protected function countProjects($date = null){
        $total = Project::where('company_id',$this->companyId)
                            ->WhereDate('created_at','<=',$date)
                            ->count();
        $active = project::where('status',1)
                           ->where('company_id',$this->companyId)
                           ->WhereDate('created_at','<=',$date)
                           ->count();
        $inActive = $total - $active;

        return ['total'=>$total,'active'=>$active,'inActive'=>$inActive];
    }

    protected function countOwnerDepts($date = null) {
        $totalDept = Department::where('company_id', $this->companyId)
                                ->WhereDate('created_at','<=',$date)
                                ->get();
        $total = $totalDept->count();
        $departmentsWithUserCount = $totalDept->map(function($department) {
            return [
                'department_name' => $department->name,
                'total_users' => $department->users->count(),
            ];
        });
        return [
            'total' => $total,
            'Departments' => $departmentsWithUserCount
        ];
    }

    protected function countOwnerDeptDailyTasks($date = null){
        $selectedDate = $date;
        $selectedDayOfWeek = $selectedDate->dayOfWeek;
        $selectedDayOfMonth = $selectedDate->day;
        $tasks = DailyTask::select([
                'id',
                'dept_id',
                'task_type',
                'recurrent_days',
                'day_of_month',
                'active',
                'company_id',
            ])
            ->where('company_id', $this->companyId)
            ->where('active', 1)
            ->where(function ($query) use ($selectedDayOfWeek, $selectedDayOfMonth) {
                $query->where('task_type', 'daily')
                      ->orWhere(function ($query) use ($selectedDayOfWeek) {
                          $query->where('task_type', 'weekly')
                                ->whereJsonContains('recurrent_days', $selectedDayOfWeek);
                      })
                      ->orWhere(function ($query) use ($selectedDayOfMonth) {
                          $query->where('task_type', 'monthly')
                                ->where('day_of_month', $selectedDayOfMonth);
                      });
            })
            ->with([
                'department:id,name',
                'reports' => function ($query) use ($selectedDate) {
                    $query->select('id', 'daily_task_id', 'status')
                          ->whereDate('created_at', $selectedDate);
                },
            ])
            ->get();
        $tasks->each(function ($task) {
            $task->setRelation('todayReport', $task->reports->first());
        });
        $departmentData = $tasks->groupBy('dept_id')->map(function ($tasksInDept, $deptId) {
            $departmentName = $tasksInDept->first()->department->name ?? 'Unknown';
            $tasksWithReports = $tasksInDept->filter(function ($task) {
                return $task->todayReport;
            });
    
            $totalTasks    = $tasksInDept->count();
            $totalReports  = $tasksWithReports->count();
            $doneReports   = $tasksWithReports->where('todayReport.status', 'done')->count();
            $notDoneReports= $totalReports - $doneReports;
    
            return [
                'department_name'  => $departmentName,
                'total_tasks'      => $totalTasks,
                'total_reports'    => $totalReports,
                'done_reports'     => $doneReports,
                'not_done_reports' => $notDoneReports,
            ];
        });
        $tasksWithReports = $tasks->filter(function ($task) {
            return $task->todayReport;
        });
    
        $totalReportsAcrossAllDepts = $tasksWithReports->count();
        $doneReportsAcrossAllDepts  = $tasksWithReports->where('todayReport.status', 'done')->count();
        $notDoneReportsAcrossAllDepts = $totalReportsAcrossAllDepts - $doneReportsAcrossAllDepts;
        return [
            'today_total_daily_tasks' => $tasks->count(),
            'total_reports'           => $totalReportsAcrossAllDepts,
            'done_reports'            => $doneReportsAcrossAllDepts,
            'not_done_reports'        => $notDoneReportsAcrossAllDepts,
            'DailyTaskDepts'          => $departmentData->values(),
        ];
    }

    protected function countDeptDailyTasks($date = null){
        $selectedDate = $date;
        $selectedDayOfWeek = $selectedDate->dayOfWeek;
        $selectedDayOfMonth = $selectedDate->day;
        $departmentIds = is_array($this->departmentIds) ? $this->departmentIds : [$this->departmentIds];
        $tasks = DailyTask::select([
                'id',
                'dept_id',
                'task_type',
                'recurrent_days',
                'day_of_month',
                'active',
                'company_id',
            ])
            ->where('company_id', $this->companyId)
            ->where('active', 1)
            ->whereIn('dept_id', $departmentIds)
            ->where(function ($query) use ($selectedDayOfWeek, $selectedDayOfMonth) {
                $query->where('task_type', 'daily')
                      ->orWhere(function ($query) use ($selectedDayOfWeek) {
                          $query->where('task_type', 'weekly')
                                ->whereJsonContains('recurrent_days', $selectedDayOfWeek);
                      })
                      ->orWhere(function ($query) use ($selectedDayOfMonth) {
                          $query->where('task_type', 'monthly')
                                ->where('day_of_month', $selectedDayOfMonth);
                      });
            })
            ->with([
                'department:id,name',
                'reports' => function ($query) use ($selectedDate) {
                    $query->select('id', 'daily_task_id', 'status')
                          ->whereDate('created_at', $selectedDate);
                },
            ])
            ->get();
        $tasks->each(function ($task) {
            $task->setRelation('todayReport', $task->reports->first());
        });
        $departmentData = $tasks->groupBy('dept_id')->map(function ($tasksInDept, $deptId) {
            $departmentName = $tasksInDept->first()->department->name ?? 'Unknown';
            $tasksWithReports = $tasksInDept->filter(function ($task) {
                return $task->todayReport;
            });
    
            $totalTasks    = $tasksInDept->count();
            $totalReports  = $tasksWithReports->count();
            $doneReports   = $tasksWithReports->where('todayReport.status', 'done')->count();
            $notDoneReports= $totalReports - $doneReports;
    
            return [
                'department_name'  => $departmentName,
                'total_tasks'      => $totalTasks,
                'total_reports'    => $totalReports,
                'done_reports'     => $doneReports,
                'not_done_reports' => $notDoneReports,
            ];
        });
        $tasksWithReports = $tasks->filter(function ($task) {
            return $task->todayReport;
        });
    
        $totalReportsAcrossAllDepts = $tasksWithReports->count();
        $doneReportsAcrossAllDepts  = $tasksWithReports->where('todayReport.status', 'done')->count();
        $notDoneReportsAcrossAllDepts = $totalReportsAcrossAllDepts - $doneReportsAcrossAllDepts;
        return [
            'today_total_daily_tasks' => $tasks->count(),
            'total_reports'           => $totalReportsAcrossAllDepts,
            'done_reports'            => $doneReportsAcrossAllDepts,
            'not_done_reports'        => $notDoneReportsAcrossAllDepts,
            'DailyTaskDepts'          => $departmentData->values(),
        ];
    }

    protected function countOwnerAllDailyTasks($date = null){
        $total = DailyTask::where('company_id', $this->companyId)
                            ->WhereDate('created_at','<=',$date)
                            ->count();
        $active = DailyTask::where('company_id', $this->companyId)
                            ->WhereDate('created_at','<=',$date)
                            ->where('active',1)
                            ->count();
        $inActive = $total- $active;
        return ['total'=>$total,'active'=>$active,'inActive'=>$inActive];
    }
}
