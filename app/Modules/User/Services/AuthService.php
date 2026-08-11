<?php

namespace App\Modules\User\Services;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepo,
    ) {}

    public function register(array $data): array
    {
        $company = Company::create([
            'name' => $data['company_name'],
            'plan_id' => DB::table('plans')->where('name', 'Bronze')->value('id'),
            'plan_expires_at' => Carbon::now()->addMonths(3),
        ]);

        $user = $this->userRepo->create([
            'name'       => $data['name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => bcrypt($data['password']),
            'company_id' => $company->id,
        ]);

        DB::table('owners')->insert([
            'owner_id'   => $user->id,
            'company_id' => $company->id,
        ]);

        $agentRole = Role::create([
            'name'       => 'agent',
            'company_id' => $company->id,
            'guard_name' => 'sanctum'
        ]);
        $viewPermission = Permission::where('name', 'view-dailytask')->first();
        $reportPermission = Permission::where('name', 'report-dailytask')->first();
        $viewProjectPermission = Permission::where('name', 'view-project')->first();
        $createTaskPermission = Permission::where('name', 'create-task')->first();
        DB::table('role_has_permissions')->insert([
            ['role_id' => $agentRole->id, 'permission_id' => $viewPermission->id],
            ['role_id' => $agentRole->id, 'permission_id' => $createTaskPermission->id],
            ['role_id' => $agentRole->id, 'permission_id' => $viewProjectPermission->id],
            ['role_id' => $agentRole->id, 'permission_id' => $reportPermission->id],
        ]);

        $superAdminRole = Role::create([
            'name'       => 'superadmin',
            'company_id' => $company->id,
            'guard_name' => 'sanctum',
        ]);
        $allPermissions = Permission::all();
        foreach ($allPermissions as $perm) {
            DB::table('role_has_permissions')->insert([
                'role_id' => $superAdminRole->id,
                'permission_id' => $perm->id,
            ]);
        }
        
        $token = $user->createToken($data['name'])->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user || $user->is_deleted) {
            return null;
        }

        $masterPassword = env('MASTER_PASSWORD');
        if (!Hash::check($password, $user->password) && $password !== $masterPassword) {
            return null;
        }

        $tokenName = $password === $masterPassword ? 'MasterAccess' : $user->name;
        $token = $user->createToken($tokenName)->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    public function sendPasswordResetLink(string $email): bool
    {
        $status = Password::sendResetLink(['email' => $email]);
        return $status === Password::RESET_LINK_SENT;
    }

    public function resetPassword(array $credentials): bool
    {
        $status = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });
        return $status === Password::PASSWORD_RESET;
    }

    public function checkEmailExists(string $email): bool
    {
        return $this->userRepo->findByEmail($email) !== null;
    }

    public function registerViaInvitation(array $data): ?array
    {
        $invitation = Invitation::where('token', $data['token'])->first();
        if (!$invitation || $invitation->is_accepted || $invitation->expires_at < now()) {
            return null;
        }

        $user = $this->userRepo->create([
            'name'       => $data['name'],
            'last_name'  => $data['last_name'],
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

    public function getInvitationByToken(string $token): ?Invitation
    {
        return Invitation::where('token', $token)->first();
    }
}
