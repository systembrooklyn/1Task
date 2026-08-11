<?php

namespace App\Modules\DailyTask\Http\Traits;

use App\Models\DailyTask;
use App\Models\DailyTaskEvaluation;
use App\Models\DailyTaskReport;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait ChecksCompanyScope
{
    /**
     * Ensure the authenticated user belongs to the same company as the given model.
     *
     * @param DailyTask|DailyTaskEvaluation|DailyTaskReport $model
     * @throws AccessDeniedHttpException
     */
    protected function ensureSameCompany($model): void
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $modelCompanyId = null;

        if ($model instanceof DailyTask) {
            $modelCompanyId = $model->company_id;
        } elseif ($model instanceof DailyTaskEvaluation) {
            $modelCompanyId = $model->dailyTask->company_id;
        } elseif ($model instanceof DailyTaskReport) {
            $modelCompanyId = $model->dailyTask->company_id;
        }

        if ($modelCompanyId && $modelCompanyId !== $companyId) {
            throw new AccessDeniedHttpException('You do not have access to this resource.');
        }
    }
}
