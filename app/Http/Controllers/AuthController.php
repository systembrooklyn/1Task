<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Traits\HasRoles;

class AuthController extends Controller
{
    use HasRoles;
    public function register(Request $request){
        $fields = $request->validate([
            'name'=>'required|max:25',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'company_name' => 'required|string|max:255',
        ]);
        $company = Company::create(['name' => $fields['company_name']]);
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'company_id' => $company->id,
        ]);
        DB::table('owners')->insert([
            'owner_id' => $user->id,
            'company_id' => $company->id,
        ]);

        $token = $user->createToken($request->name);
        
        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    public function registerViaInvitation(Request $request)
{
    $request->validate([
        'token' => 'required',
        'name' => 'required|string|max:255',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $invitation = Invitation::where('token', $request->token)->first();

    if (!$invitation || $invitation->is_accepted || $invitation->expires_at < now()) {
        return response()->json(['message' => 'Invalid or expired invitation.'], 400);
    }
    $user = User::create([
        'name' => $request->name,
        'email' => $invitation->email,
        'password' => Hash::make($request->password),
        'company_id' => $invitation->company_id,
    ]);

    $invitation->update(['is_accepted' => true]);

    $token = $user->createToken($request->name)->plainTextToken;

    return response()->json(['message' => 'Registration successful.', 'user' => $user, 'token' => $token], 201);
}




    public function checkEmailExists(Request $request){
        $request->validate([
            'email' => 'required|email'
        ]);
    
        $emailExists = User::where('email', $request->email)->exists();
    
        return response()->json([
            'exists' => $emailExists
        ]);
    }

    public function login(Request $request) 
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users',
        'password' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed. Please check your input.',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'The provided credentials are incorrect.',
            'errors' => [
                'email' => [
                    'The provided email or password is incorrect.'
                ]
            ]
        ], 401);
    }

    $token = $user->createToken($user->name)->plainTextToken;

    $userData = $user->load([
        'company',
        'departments',
        'roles',
        'roles.permissions'
    ]);
    
    $userData->makeHidden(['created_at', 'updated_at','email_verified_at','company_id']);
    $userData->company->makeHidden(['created_at', 'updated_at']);
    $userData->departments->each(function ($department) {
        $department->makeHidden(['created_at', 'updated_at','company_id']);
    });
    $userData->roles->each(function ($role) {
        $role->makeHidden(['created_at', 'updated_at','company_id','guard_name','pivot']);
        $role->permissions->each(function ($permission) {
            $permission->makeHidden(['created_at', 'updated_at','guard_name','pivot']);
        });
    });
    
    return response()->json([
        'user' => $userData,
        'token' => $token
    ]);
}

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return [
            'message' => 'You are logged out'
        ];
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent successfully!'
            ]);
        }

        return response()->json([
            'message' => 'Failed to send password reset link.'
        ], 400);
    }


    
        
    /**
     * Handle the password reset process.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
    public function assignRoleToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);
        $user = User::findOrFail($validated['user_id']);


        $roles = Role::find($validated['role_ids']);
        if ($roles->isEmpty()) {
            return response()->json(['message' => 'No valid roles found.'], 400);
        }

        foreach ($roles as $role) {
           
            if ($user->company_id !== $role->company_id) {
                return response()->json(['message' => 'User and role do not belong to the same company.'], 400);
            }

            
            if ($role->guard_name == 'sanctum') {
                $user->assignRole($role);
            } else {
                return response()->json(['message' => 'Invalid guard name for one of the roles.'], 400);
            }
        }

        return response()->json(['message' => 'Roles assigned successfully.'], 200);
    }


    public function unassignRoleFromUser(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'role_ids' => 'required|array',
        'role_ids.*' => 'exists:roles,id',
    ]);

    $user = User::findOrFail($validated['user_id']);

    // Retrieve roles based on the provided role_ids
    $roles = Role::find($validated['role_ids']);

    if ($roles->isEmpty()) {
        return response()->json(['message' => 'No valid roles found.'], 400);
    }

    foreach ($roles as $role) {
        // Check if the user and role belong to the same company
        if ($user->company_id !== $role->company_id) {
            return response()->json(['message' => 'User and role do not belong to the same company.'], 400);
        }

        // Check if the user already has the role
        if (!$user->hasRole($role->name)) {
            return response()->json(['message' => 'User does not have this role.'], 400);
        }

        // Unassign the role
        $user->removeRole($role);
    }

    return response()->json(['message' => 'Roles unassigned successfully.'], 200);
}

    public function deleteUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $user = User::findOrFail($validated['user_id']);

        DB::beginTransaction();

        try {
            $user->roles()->detach();

            $user->departments()->detach();

            $user->delete();

            DB::commit();

            return response()->json(['message' => 'User and related data deleted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred. Could not delete the user.'], 500);
        }
    }
    
}
