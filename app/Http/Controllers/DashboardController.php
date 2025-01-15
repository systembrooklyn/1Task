<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Models\DailyTaskReport;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected function getCounts(){
        $user = auth('sanctum')->user();
        $hasPermission = $user->assignedPermissions()->contains('name', 'view-dailytask');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();
        $countDailyTask = $this->countOwnerDailyTasks($user->company_id);
        $countTodayDailyTasks = $this->todayOwnerDailyTasks($user->company_id);
        return response()->json([
            'user'=>$user,
            'is owner'=>$isOwner,
            'perm'=>$hasPermission,
            'DailyTask'=>$countDailyTask,
            'TodayDailyTasks'=>$countTodayDailyTasks
        ]);
    }
    protected function countOwnerDailyTasks(string $company_id){
        $total = DailyTask::where('company_id', $company_id)->count();
        $active = DailyTask::where('company_id', $company_id)
                            ->where('active',1)
                            ->count();
        $inActive = DailyTask::where('company_id', $company_id)
                            ->where('active',0)
                            ->count();
        return ['total'=>$total,'active'=>$active,'inActive'=>$inActive];
    }
    protected function todayOwnerDailyTasks(string $company_id){
        $today = now()->format('Y-m-d');
        $currentDayOfWeek = now()->dayOfWeek;
        $currentDayOfMonth = now()->day;
        $total = DailyTask::where('company_id', $company_id)
            ->where('active',1)
            ->where(function ($query) use ($today, $currentDayOfWeek, $currentDayOfMonth) {
            $query->orWhere(function ($query) use ($today) {
                $query->where('task_type', 'daily')
                    ->whereDate('start_date', '<=', $today);
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
                $query->where('task_type', 'single')
                    ->whereDate('start_date', $today);
            })
            ->orWhere(function ($query) use ($today) {
                $query->where('task_type', 'last_day_of_month')
                    ->whereDate('start_date', $today)
                    ->whereRaw('DAY(LAST_DAY(start_date)) = ?', [now()->day]);
            });
        })->count();
        $hasReports = DailyTask::where('company_id', $company_id)
                            ->where('active',1)
                            ->has('reports')
                            ->get()
                            ->count();
        $reportedDone = DailyTask::where('company_id', $company_id)
                                    ->where('active', 1)
                                    ->has('reports')
                                    ->where('status','done')
                                    ->get()
                                    ->count();
        return ['total'=>$total,'hasReports'=>$hasReports,'reportedDone'=>$reportedDone];
    }
}
