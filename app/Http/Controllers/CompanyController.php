<?php

namespace App\Http\Controllers;

use App\Exceptions\NoActivePlanException;
use App\Exceptions\ResourceDeletedException;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyController extends Controller
{
    public function getCompanyUsers(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->company_id) {
            return response()->json(['message' => 'User not associated with a company or not authenticated'], 400);
        }
        $company_id = $user->company_id;
        $companyUsers = User::where('company_id', $company_id)
            ->where('is_deleted', 0)
            // ->whereDoesntHave('companies', function ($query) use ($company_id) {
            //     $query->where('company_id', $company_id);
            // })
            ->with(['departments', 'roles'])
            ->get();
        if ($companyUsers->isEmpty()) {
            return response()->json(['message' => 'No users found for this company'], 404);
        }
        $companyUsersData = $companyUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'departments' => $user->departments->pluck('name'),
                'roles' => $user->roles->pluck('name'),
                'departments_ids' => $user->departments->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                    ];
                }),
                'roles_ids' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                }),
            ];
        });
        return response()->json(['users' => $companyUsersData], 200);
    }

    public function getCompanyPlanDetails()
    {
        $user = Auth::user();
        $expired = 0;
        if ($user->is_deleted) throw new ResourceDeletedException('This account has been deleted please contact the support');
        if (!$user->company->plan_id) throw new NoActivePlanException();
        $expireDate = Carbon::parse($user->company->plan_expires_at);
        $expired = $expireDate < today() ? 1 : 0;
        if ($expired) {
            throw new NoActivePlanException('Your plan has expired, please subscribe', 'Plan Expired');
        }
        return response()->json([
            'message' => 'check plan details retreived successfully',
            'data' => [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'plan_id'  => $user->company->plan_id,
                'plan_name' => $user->company->plan->name,
                'expire_date' => $expireDate->format('Y-m-d'),
                'expired' => $expired
            ]
        ], 200);
    }
}
