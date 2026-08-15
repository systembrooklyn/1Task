<?php

namespace App\Modules\User\Services;

use App\Exceptions\NoActivePlanException;
use App\Exceptions\ResourceDeletedException;
use App\Models\User;
use Carbon\Carbon;

class CompanyPlanService
{
    /**
     * Get company plan details for the authenticated user.
     *
     * @param User $user
     * @return array
     * @throws ResourceDeletedException
     * @throws NoActivePlanException
     */
    public function getCompanyPlanDetails(User $user): array
    {
        // Check if user is deleted
        if ($user->is_deleted) {
            throw new ResourceDeletedException('This account has been deleted please contact the support');
        }

        // Check if company has a plan
        if (!$user->company->plan_id) {
            throw new NoActivePlanException();
        }

        $expireDate = Carbon::parse($user->company->plan_expires_at);
        $expired = $expireDate < today() ? 1 : 0;

        if ($expired) {
            throw new NoActivePlanException('Your plan has expired, please subscribe', 'Plan Expired');
        }

        return [
            'user_id'     => $user->id,
            'company_id'  => $user->company_id,
            'plan_id'     => $user->company->plan_id,
            'plan_name'   => $user->company->plan->name,
            'expire_date' => $expireDate->format('Y-m-d'),
            'expired'     => $expired,
        ];
    }
}
