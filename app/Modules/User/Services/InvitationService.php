<?php

namespace App\Modules\User\Services;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Role;
use App\Modules\User\Repositories\Contracts\InvitationRepositoryInterface;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\InvitationMail;

class InvitationService
{
    protected $planService;

    public function __construct(
        protected InvitationRepositoryInterface $invitationRepo,
        PlanLimitService $planService
    ) {
        $this->planService = $planService;
    }

    public function invite(User $inviter, string $email): array
    {
        $this->planService->checkFeatureAccess($inviter->company_id, 'limit_emp');

        $expired = $this->invitationRepo->findExpiredByEmail($email);
        if ($expired) {
            $this->invitationRepo->delete($expired);
        }

        if (User::where('email', $email)->exists()) {
            return ['success' => false, 'message' => 'The email address is already registered.'];
        }

        $pending = $this->invitationRepo->findByEmail($email);
        if ($pending) {
            return ['success' => false, 'message' => 'An invitation has already been sent to this email address.'];
        }

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

        try {
            Mail::to($email)->send(new InvitationMail($invitation));
        } catch (\Exception $e) {
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
            'last_name'  => $data['last_name'] ?? null,
            'email'      => $invitation->email,
            'password'   => bcrypt($data['password']),
            'company_id' => $invitation->company_id,
        ]);

        $this->invitationRepo->delete($invitation);
        return $user;
    }

    public function registerViaInvitation(array $data): ?array
    {
        $invitation = $this->invitationRepo->findByToken($data['token']);
        if (!$invitation || $invitation->is_accepted || $invitation->expires_at < now()) {
            return null;
        }

        $user = User::create([
            'name'       => $data['name'],
            'last_name'  => $data['last_name'] ?? null,
            'email'      => $invitation->email,
            'password'   => bcrypt($data['password']),
            'company_id' => $invitation->company_id,
        ]);

        $invitation->update(['is_accepted' => true]);

        $agentRole = Role::where('name', 'agent')
            ->where('guard_name', 'sanctum')
            ->where('company_id', $invitation->company_id)
            ->first();

        if ($agentRole) {
            DB::table('role_user')->insert([
                'user_id'    => $user->id,
                'role_id'    => $agentRole->id,
                'company_id' => $invitation->company_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $token = $user->createToken($data['name'])->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    public function getPendingInvitations(int $companyId)
    {
        return $this->invitationRepo->getPendingByCompany($companyId);
    }
}
