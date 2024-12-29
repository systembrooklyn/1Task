<?php

namespace App\Helpers;

use App\Models\DailyTask;
use Illuminate\Support\Facades\DB;

class TaskNumberGenerator
{
    /**
     * Generate a unique task number for a specific company.
     *
     * @param int
     * @return string
     */
    public static function generateTaskNo(int $companyId): string
    {
        return DB::transaction(function () use ($companyId) {
            $lastTask = DailyTask::where('company_id', $companyId)
                ->select(DB::raw('MAX(CAST(SUBSTRING(task_no, 6) AS UNSIGNED)) as max_no'))
                ->lockForUpdate()
                ->first();
            $nextNumber = $lastTask->max_no ? $lastTask->max_no + 1 : 1;
            if ($nextNumber <= 9999) {
                $numberPart = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            } else {
                $numberPart = (string) $nextNumber;
            }

            return 'TASK-' . strtoupper($numberPart);
        });
    }
}