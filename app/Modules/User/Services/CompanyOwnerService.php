<?php

namespace App\Modules\User\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CompanyOwnerService
{
    public function getCompanyOwner(int $companyId): ?array
    {
        $company = Company::with('owners')->find($companyId);
        if (!$company) {
            return null;
        }

        $owners = $company->owners->map(function ($owner) {
            return [
                'name'      => $owner->name,
                'last_name' => $owner->last_name ?? null,
                'email'     => $owner->email,
            ];
        });

        return [
            'company_name' => $company->name,
            'owners'       => $owners,
        ];
    }

    public function isOwner(int $userId): bool
    {
        return Company::whereHas('owners', function ($query) use ($userId) {
            $query->where('owner_id', $userId);
        })->exists();
    }
}
