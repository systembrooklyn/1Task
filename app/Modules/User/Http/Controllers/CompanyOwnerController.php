<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\CompanyOwnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CompanyOwnerController extends Controller
{
    public function __construct(protected CompanyOwnerService $ownerService) {}

    public function getCompanyOwner(): JsonResponse
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $data = $this->ownerService->getCompanyOwner($companyId);
        if (!$data) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        return response()->json($data);
    }

    public function checkOwner(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $isOwner = $this->ownerService->isOwner($user->id);
        return response()->json(['isOwner' => $isOwner], 200);
    }
}
