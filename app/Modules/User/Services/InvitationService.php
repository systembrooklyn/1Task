<?php

namespace App\Modules\User\Services;

use App\Models\Invitation;
use App\Models\User;
use App\Modules\User\Repositories\Contracts\InvitationRepositoryInterface;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\InvitationMail;

class InvitationService
{
    public function __construct(
        protected InvitationRepositoryInterface $invitationRepo,
        protected PlanLimitService $planService,
    ) {}

    public function invite(User $inviter, string $email): array
    {
        // Check plan limits
        $this->planService->checkFeatureAccess($inviter->company_id, 'limit_emp');

        // Authorize (policy will be called in controller, but we can also check here)
        // Controller will call authorize('invite', Invitation::class)

        // Delete any expired invitation for this email
        $expired = $this->invitationRepo->findExpiredByEmail($email);
        if ($expired) {
            $this->invitationRepo->delete($expired);
        }

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            return ['success' => false, 'message' => 'The email address is already registered.'];
        }

        // Check for pending invitation
        $pending = $this->invitationRepo->findByEmail($email);
        if ($pending) {
            return ['success' => false, 'message' => 'An invitation has already been sent to this email address.'];
        }

        // Create new invitation
        $token = Str::random(32);
        $expiresAt = now()->addMinutes(60);

        $invitation = $this->invitationRepo->create([
            'inviter'    => $inviter->name,
            'inviter_id' => $inviter->id,
            'email'      => $email,
            'token'      => $token,
            'company_id' => $inviter->company_id,
            'expires_at' => $expiresAt,
        ]);

        // Send email
        try {
            Mail::to($email)->send(new InvitationMail($invitation));
        } catch (\Exception $e) {
            // If mail fails, we might want to delete the invitation or handle differently
            // Original code just returns error, but we'll keep it consistent
            return ['success' => false, 'message' => 'Failed to send invitation. Please try again later.'];
        }

        return ['success' => true, 'message' => 'Invitation sent successfully.', 'invitation' => $invitation];
    }

    public function validateInvitation(string $token): ?array
    {
        $invitation = $this->invitationRepo->findByToken($token);
        if (!$invitation) {
            return ['valid' => false, 'message' => 'Invalid or expired invitation token.'];
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $this->invitationRepo->delete($invitation);
            return ['valid' => false, 'message' => 'This invitation has expired and has been deleted.'];
        }

        return ['valid' => true, 'invitation' => $invitation];
    }

    public function completeRegistration(array $data, string $token): ?User
    {
        $invitation = $this->invitationRepo->findByToken($token);
        if (!$invitation) {
            return null;
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $this->invitationRepo->delete($invitation);
            return null;
        }

        $user = User::create([
            'name'       => $data['name'],
            'last_name'  => $data['last_name'],
            'email'      => $invitation->email,
            'password'   => bcrypt($data['password']),
            // company_id is not set here in original; it's set later via roles or invitation flow
            // Actually in original it doesn't set company_id in User create, but the invitation has company_id.
            // In the original completeRegistration, user is created without company_id.
            // Then Auth::login($user) and invitation is deleted.
            // However, the invitation had company_id, but the user is not attached to a company in this flow.
            // This seems like a bug in the original code. We'll replicate exactly.
            // The user doesn't get company_id, but later maybe assigned via roles or something.
            // We'll keep the exact same behavior.
        ]);

        // Delete the invitation after successful registration
        $this->invitationRepo->delete($invitation);

        return $user;
    }

    public function getPendingInvitations(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->invitationRepo->getPendingByCompany($companyId);
    }
}