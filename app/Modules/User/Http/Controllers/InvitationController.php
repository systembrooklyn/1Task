<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\InviteRequest;
use App\Modules\User\Http\Requests\CompleteRegistrationRequest;
use App\Modules\User\Http\Requests\RegisterViaInvitationRequest;
use App\Modules\User\Services\InvitationService;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function __construct(protected InvitationService $invitationService) {}

    public function invite(InviteRequest $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = Auth::user();
        $this->authorize('invite', Invitation::class);

        if (!$user->company_id) {
            return response()->json(['message' => 'You are not associated with any company.'], 403);
        }

        $result = $this->invitationService->invite($user, $request->input('email'));

        if (!$result['success']) {
            $status = ($result['message'] === 'Failed to send invitation. Please try again later.') ? 500 : 400;
            return response()->json(['message' => $result['message']], $status);
        }

        return response()->json(['message' => 'Invitation sent successfully.'], 201);
    }

    public function registerUsingInvitation(string $token): JsonResponse
    {
        $result = $this->invitationService->validateInvitation($token);
        if (!$result['valid']) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json([
            'message'    => 'Invitation is valid.',
            'invitation' => $result['invitation'],
        ]);
    }

    public function completeRegistration(CompleteRegistrationRequest $request, string $token): JsonResponse
    {
        $user = $this->invitationService->completeRegistration($request->validated(), $token);
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired invitation token.'], 400);
        }

        Auth::login($user);
        return response()->json(['message' => 'User registered successfully!'], 201);
    }

    public function registerViaInvitation(RegisterViaInvitationRequest $request): JsonResponse
    {
        $result = $this->invitationService->registerViaInvitation($request->validated());
        if (!$result) {
            return response()->json(['message' => 'Invalid or expired invitation.'], 400);
        }

        return response()->json([
            'message' => 'Registration successful.',
            'user'    => $result['user'],
            'token'   => $result['token'],
        ], 201);
    }

    public function getInvitations(): JsonResponse
    {
        if (!auth('sanctum')->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $authUser = Auth::user();
        $user = User::find($authUser->id);

        $haveAccess = false;
        $permissions = $user->getAllPermissions();
        foreach ($permissions as $permission) {
            if ($permission->name == "inivte-user") {
                $haveAccess = true;
                break;
            }
        }
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if ($haveAccess || $isOwner) {
            $invitations = $this->invitationService->getPendingInvitations($user->company_id);
            return response()->json(['invitations' => $invitations]);
        } else {
            return response()->json(['message' => 'you dont have permission to invite user'], 403);
        }
    }
}
