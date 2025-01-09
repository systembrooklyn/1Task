<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyOwnerController extends Controller
{
    public function getCompanyOwner()
    {
        $user = Auth::user();
        $company = Company::with('owners')->find($user->company_id);

        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $owners = $company->owners->map(function ($owner) {
            return [
                'name'  => $owner->name,
                'email' => $owner->email,
            ];
        });

        return response()->json([
            'company_name' => $company->name,
            'owners' => $owners
        ]);
    }
    public function checkOwner(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $isOwner = Company::whereHas('owners', function ($query) use ($user) {
            $query->where('owner_id', $user->id);
        })->exists();
        return response()->json(['isOwner' => $isOwner], 200);
    }
}
