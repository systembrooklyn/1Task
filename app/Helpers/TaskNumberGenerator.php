<?php

namespace App\Helpers;

use App\Models\DailyTask;
use App\Models\DailyTaskCounter;
use Illuminate\Support\Facades\DB;


class TaskNumberGenerator
{
    /**
     * Generate a unique task number for a specific company.
     *
     * @param int
     * @return string
     */
    // public static function generateTaskNo(int $companyId): string
    // {
    //     return DB::transaction(function () use ($companyId) {
    //         $lastTask = DailyTask::where('company_id', $companyId)
    //             ->select(DB::raw('MAX(CAST(SUBSTRING(task_no, 6) AS UNSIGNED)) as max_no'))
    //             ->lockForUpdate()
    //             ->first();
    //         $nextNumber = $lastTask->max_no ? $lastTask->max_no + 1 : 1;
    //         if ($nextNumber <= 9999) {
    //             $numberPart = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    //         } else {
    //             $numberPart = (string) $nextNumber;
    //         }

    //         return 'TASK-' . strtoupper($numberPart);
    //     });
    // }
    public static function generateTaskNo(int $companyId): string
    {
        return DB::transaction(function () use ($companyId) {
            $taskCounter = DailyTaskCounter::where('company_id', $companyId)
                ->lockForUpdate()
                ->first();
            if (!$taskCounter) {
                $taskCounter = new DailyTaskCounter([
                    'company_id'  => $companyId,
                    'last_daily_task_no' => 0,
                ]);
            }
            $taskCounter->last_daily_task_no++;
            $taskCounter->save();
            $numberPart = str_pad($taskCounter->last_daily_task_no, 4, '0', STR_PAD_LEFT);
            return 'TASK-' . strtoupper($numberPart);
        });
    }

    public static function getRandomDailyTaskNum(int $companyId): int
    {
        $row = DB::table('daily_task_counters')
            ->where('company_id', $companyId)
            ->first();

        return $row?->rndm_DT_num_dept ?? 2;
    }

    /**
     * Set or update the random task count for a company
     *
     * @param int $companyId
     * @param int $value
     * @return void
     */
    public static function setRandomDailyTaskNum(int $companyId, int $value): void
    {
        DB::transaction(function () use ($companyId, $value) {
            $exists = DB::table('daily_task_counters')
                ->where('company_id', $companyId)
                ->exists();

            if ($exists) {
                DB::table('daily_task_counters')
                    ->where('company_id', $companyId)
                    ->update([
                        'rndm_DT_num_dept' => $value,
                    ]);
            } else {
                DB::table('daily_task_counters')->insert([
                    'company_id' => $companyId,
                    'last_daily_task_no' => 0,
                    'rndm_DT_num_dept' => $value,
                ]);
            }
        });
    }
}