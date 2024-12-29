<?php

namespace App\Observers;

use App\Models\DailyTaskReport;
use Illuminate\Support\Facades\Auth;

class DailyTaskReportObserver
{
    /**
     * Handle the DailyTaskReport "created" event.
     */
    public function created(DailyTaskReport $dailyTaskReport): void
    {
        //
    }

    public function creating(DailyTaskReport $dailyTaskReport)
    {
        // Automatically set the 'submitted_by' field if not set
        if (!$dailyTaskReport->submitted_by) {
            $dailyTaskReport->submitted_by = Auth::id();
        }
    }
    /**
     * Handle the DailyTaskReport "updated" event.
     */
    public function updated(DailyTaskReport $dailyTaskReport): void
    {
        //
    }

    /**
     * Handle the DailyTaskReport "deleted" event.
     */
    public function deleted(DailyTaskReport $dailyTaskReport): void
    {
        //
    }

    /**
     * Handle the DailyTaskReport "restored" event.
     */
    public function restored(DailyTaskReport $dailyTaskReport): void
    {
        //
    }

    /**
     * Handle the DailyTaskReport "force deleted" event.
     */
    public function forceDeleted(DailyTaskReport $dailyTaskReport): void
    {
        //
    }
}
