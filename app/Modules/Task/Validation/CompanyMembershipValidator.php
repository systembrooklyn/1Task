<?php

namespace App\Modules\Task\Validation;

use App\Models\User;
use Illuminate\Validation\Validator;

class CompanyMembershipValidator
{
    /**
     * Validate that all given user IDs belong to the specified company.
     *
     * @param Validator $validator
     * @param array $userIds
     * @param int $companyId
     * @param string $errorMessage
     * @return void
     */
    public static function validate(
        Validator $validator,
        array $userIds,
        int $companyId,
        string $errorMessage = 'All users must belong to the same company as the authenticated user.'
    ): void {
        $filtered = array_filter($userIds, function ($value) {
            return !empty($value) && is_numeric($value) && $value > 0;
        });

        if (empty($filtered)) {
            return;
        }

        $userIds = array_unique(array_map('intval', array_values($filtered)));

        $validUsers = User::whereIn('id', $userIds)->where('company_id', $companyId)->pluck('id')->toArray();

        $invalid = array_diff($userIds, $validUsers);

        if (!empty($invalid)) {
            $invalidIds = implode(', ', $invalid);
            $validator->errors()->add(
                'users',
                "The following user IDs do not belong to your company: {$invalidIds}."
            );
        }
    }
}
