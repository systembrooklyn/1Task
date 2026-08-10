<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller; // ✅ correct base controller
use App\Modules\User\Http\Requests\InviteRequest;
use App\Modules\User\Http\Requests\CompleteRegistrationRequest;
use App\Modules\User\Services\InvitationService;
use App\Models\Invitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function __construct(protected InvitationService $invitationService) {}

    public function invite(InviteRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // ✅ Now `authorize` is available
        $this->authorize('invite', Invitation::class);

        if (!$user->company_id) {
            return response()->json(['message' => 'You are not associated with any company.'], 403);
        }

        $result = $this->invitationService->invite($user, $request->input('email'));

        if (!$result['success']) {
            return response()->json(
                ['message' => $result['message']],
                $result['message'] === 'Failed to send invitation. Please try again later.' ? 500 : 400
            );
        }

        return response()->json(['message' => 'Invitation sent successfully.'], 201);
    }

    public function registerUsingInvitation($token): JsonResponse
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

    public function completeRegistration(CompleteRegistrationRequest $request, $token): JsonResponse
    {
        $user = $this->invitationService->completeRegistration($request->validated(), $token);
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired invitation token.'], 400);
        }

        Auth::login($user);
        return response()->json(['message' => 'User registered successfully!'], 201);
    }

    public function getInvitations(): JsonResponse
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = Auth::user();

        $haveAccess = $user->getAllPermissions()->contains('name', 'invite-user');
        $isOwner = $user->companies()->wherePivot('company_id', $user->company_id)->exists();

        if (!$haveAccess && !$isOwner) {
            return response()->json(['message' => 'You don\'t have permission to invite users.'], 403);
        }

        $invitations = $this->invitationService->getPendingInvitations($user->company_id);

        return response()->json(['invitations' => $invitations]);
    }
}
