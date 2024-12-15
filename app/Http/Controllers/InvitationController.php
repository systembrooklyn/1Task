<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Invitation; 
use Illuminate\Support\Facades\Mail; 
use App\Mail\InvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    public $haveAccess = false;
    public function invite(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = Auth::user();
        $company_id = $user->company_id;
        $permissions = $user->getAllPermissions();
        foreach($permissions as $permission){
            if($permission->name == "invite-user") $haveAccess = true; 
            break;
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        if($haveAccess || $isOwner){
            $request->validate([
                'email' => 'required|email',
            ]);
            if (!$user->company_id) {
                return response()->json(['message' => 'You are not associated with any company.'], 403);
            }
            
            $existingInvitation = Invitation::where('email', $request->email)
            ->where('expires_at', '<', now())
            ->first();
            if ($existingInvitation) {
                $existingInvitation->delete();
            }
    
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json(['message' => 'The email address is already registered.'], 400);
            }
    
            $existingPendingInvitation = Invitation::where('email', $request->email)->first();
            if ($existingPendingInvitation) {
                return response()->json(['message' => 'An invitation has already been sent to this email address.'], 400);
            }
    
            $token = Str::random(32);
    
            $expiresAt = now()->addMinutes(60);
    
            $invitation = Invitation::create([
                'inviter_id' => $user->id,
                'email' => $request->email,
                'token' => $token,
                'company_id' => $user->company_id, 
                'expires_at' => $expiresAt,
            ]);
    
            try {
                Mail::to($request->email)->send(new InvitationMail($invitation));
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to send invitation. Please try again later.'], 500);
            }
            
    
            return response()->json(['message' => 'Invitation sent successfully.'], 201);
        }else return response()->json(['message' => 'You do not have permission to invite users'], 401);
        
    }


public function registerUsingInvitation($token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return response()->json(['message' => 'Invalid or expired invitation token.'], 400);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();
            return response()->json(['message' => 'This invitation has expired and has been deleted.'], 400);
        }
        return response()->json([
            'message' => 'Invitation is valid.',
            'invitation' => $invitation,
        ]);
    }

    public function completeRegistration(Request $request, $token)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $invitation = Invitation::where('token', $token)->first();
        if (!$invitation) {
            return response()->json(['message' => 'Invalid or expired invitation token.'], 400);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();
            return response()->json(['message' => 'This invitation has expired and has been deleted.'], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);
        $invitation->delete();
        return response()->json(['message' => 'User registered successfully!'], 201);
    }

}
