<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                            ->where('is_deleted',0)
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
            ];
        });
        return response()->json(['users' => $companyUsersData], 200);
    }
}
