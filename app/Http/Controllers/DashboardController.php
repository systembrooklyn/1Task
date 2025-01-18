<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Models\DailyTaskReport;
use App\Models\Department;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // public function getUserCompanyId(): ?int
    // {
    //     $user = auth('sanctum')->user();

    //     return $user->company_id ?? null;
    // }


    protected $user;
    protected $companyId;
    protected $departmentIds;
    protected $permissionOwner;
    protected $permissionDashboard;
    protected $isOwner;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
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
    protected function getCounts(){
        if($this->isOwner || $this->permissionOwner){
            $countEmps = $this->countOwnerEmps();
            $countProjects = $this->countProjects();
            $countDepartments = $this->countOwnerDepts();
            $countDailyTasks = $this->countOwnerDeptDailyTasks();
            $countAllDailyTasks = $this->countOwnerAllDailyTasks();
        }elseif($this->permissionDashboard){
            $countEmps = $this->countDeptEmps();
            $countProjects = $this->countProjects();
            $countDepartments = null;
            $countDailyTasks = $this->countDeptDailyTasks();
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
    protected function countOwnerEmps(){
        $total = User::where('company_id',$this->companyId)->count();
        $Invited = Invitation::where('company_id',$this->companyId)->count();
        $pending = Invitation::where('company_id',$this->companyId)
                               ->where('is_accepted',0)
                               ->count();
        return ['total'=>$total-1,'invited'=>$Invited,'pending'=>$pending];
    }

    protected function countDeptEmps(){
        $total = User::where('company_id',$this->companyId)
                       ->whereHas('departments',function($query){
                        $query->where('departments.id',$this->departmentIds);
                       })->count();
        return ['total'=>$total,'invited'=>null,'pending'=>null];
    }

    protected function countProjects(){
        $total = Project::where('company_id',$this->companyId)->count();
        $active = project::where('status',1)
                           ->where('company_id',$this->companyId)
                           ->count();
        $inActive = $total - $active;

        return ['total'=>$total,'active'=>$active,'inActive'=>$inActive];
    }

    protected function countOwnerDepts() {
        $totalDept = Department::where('company_id', $this->companyId)->get();
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

    protected function countOwnerDeptDailyTasks() {
        $today = now();
        $todayDayOfWeek = $today->dayOfWeek;
        $todayDayOfMonth = $today->day;
        $tasks = DailyTask::where('company_id', $this->companyId)
                            ->where('active', 1)
                            ->where(function($query) use ($todayDayOfWeek, $todayDayOfMonth) {
                                $query->where('task_type', 'daily')
                                        ->orWhere(function($query) use ($todayDayOfWeek) {
                                            $query->where('task_type', 'weekly')
                                                ->whereJsonContains('recurrent_days', $todayDayOfWeek);
                                        })
                                        ->orWhere(function($query) use ($todayDayOfMonth) {
                                            $query->where('task_type', 'monthly')
                                                ->where('day_of_month', $todayDayOfMonth);
                                        });
                            })
                            ->with(['department', 'todayReport'])
                            ->get();
        $departmentData = $tasks->groupBy('dept_id')->map(function($tasks, $deptId) {
            $departmentName = $tasks->first()->department->name;
            $tasksWithTodayReports = $tasks->filter(function($task) {
                return $task->todayReport;
            });
            $totalTasks = $tasks->count();
            $totalReports = $tasksWithTodayReports->count();
            $doneReports = $tasksWithTodayReports->filter(function($task) {
                return $task->todayReport->status === 'done';
            })->count();
            $notDoneReports = $tasksWithTodayReports->filter(function($task) {
                return $task->todayReport->status !== 'done';
            })->count();

            return [
                'department_name' => $departmentName,
                'total_tasks' => $totalTasks,
                'total_reports' => $totalReports,
                'done_reports' => $doneReports,
                'not_done_reports' => $notDoneReports,
            ];
        });
        $tasksWithReports = $tasks->filter(function($task) {
            return $task->todayReport;
        });

        $totalReportsAcrossAllDepts = $tasksWithReports->count();
        $doneReportsAcrossAllDepts = $tasksWithReports->filter(function($task) {
            return $task->todayReport->status === 'done';
        })->count();
        $notDoneReportsAcrossAllDepts = $tasksWithReports->filter(function($task) {
            return $task->todayReport->status !== 'done';
        })->count();
        return [
            'today_total_daily_tasks' => $tasks->count(),
            'total_reports' => $totalReportsAcrossAllDepts,
            'done_reports' => $doneReportsAcrossAllDepts,
            'not_done_reports' => $notDoneReportsAcrossAllDepts,
            'DailyTaskDepts' => $departmentData->values(),
        ];
    }

    protected function countDeptDailyTasks(){
        $today = now();
        $todayDayOfWeek = $today->dayOfWeek;
        $todayDayOfMonth = $today->day;
        $departmentIds = is_array($this->departmentIds) ? $this->departmentIds : [$this->departmentIds];
        $tasks = DailyTask::where('company_id', $this->companyId)
                            ->where('active', 1)
                            ->whereIn('dept_id', $departmentIds)
                            ->where(function($query) use ($todayDayOfWeek, $todayDayOfMonth) {
                                $query->where('task_type', 'daily')
                                        ->orWhere(function($query) use ($todayDayOfWeek) {
                                            $query->where('task_type', 'weekly')
                                                ->whereJsonContains('recurrent_days', $todayDayOfWeek);
                                        })
                                        ->orWhere(function($query) use ($todayDayOfMonth) {
                                            $query->where('task_type', 'monthly')
                                                ->where('day_of_month', $todayDayOfMonth);
                                        });
                            })
                            ->with(['department', 'todayReport'])
                            ->get();
        $departmentData = $tasks->groupBy('dept_id')->map(function($tasks, $deptId) {
            $departmentName = $tasks->first()->department->name;
            $tasksWithTodayReports = $tasks->filter(function($task) {
                return $task->todayReport;
            });
            $totalTasks = $tasks->count();
            $totalReports = $tasksWithTodayReports->count();
            $doneReports = $tasksWithTodayReports->filter(function($task) {
                return $task->todayReport->status === 'done';
            })->count();
            $notDoneReports = $tasksWithTodayReports->filter(function($task) {
                return $task->todayReport->status !== 'done';
            })->count();

            return [
                'department_name' => $departmentName,
                'total_tasks' => $totalTasks,
                'total_reports' => $totalReports,
                'done_reports' => $doneReports,
                'not_done_reports' => $notDoneReports,
            ];
        });
        $tasksWithReports = $tasks->filter(function($task) {
            return $task->todayReport;
        });

        $totalReportsAcrossUserDepts = $tasksWithReports->count();
        $doneReportsAcrossUserDepts = $tasksWithReports->filter(function($task) {
            return $task->todayReport->status === 'done';
        })->count();
        $notDoneReportsAcrossUserDepts = $tasksWithReports->filter(function($task) {
            return $task->todayReport->status !== 'done';
        })->count();
        return [
            'today_total_daily_tasks' => $tasks->count(),
            'total_reports' => $totalReportsAcrossUserDepts,
            'done_reports' => $doneReportsAcrossUserDepts,
            'not_done_reports' => $notDoneReportsAcrossUserDepts,
            'DepartmentData' => $departmentData->values(),
        ];
    }



    protected function countOwnerAllDailyTasks(){
        $total = DailyTask::where('company_id', $this->companyId)->count();
        $active = DailyTask::where('company_id', $this->companyId)
                            ->where('active',1)
                            ->count();
        $inActive = $total- $active;
        return ['total'=>$total,'active'=>$active,'inActive'=>$inActive];
    }
}
