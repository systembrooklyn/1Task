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
    public function invite(Request $request)
{
    // Ensure the user is authenticated
    if (!Auth::check()) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    $user = Auth::user();

    // Validate the email
    $request->validate([
        'email' => 'required|email',
    ]);

    $existingInvitation = Invitation::where('email', $request->email)
    ->where('expires_at', '<', now())
    ->first();
    if ($existingInvitation) {
        // If expired, delete it
        $existingInvitation->delete();
    }

    // Check if the email already exists in the users table
    $existingUser = User::where('email', $request->email)->first();
    if ($existingUser) {
        return response()->json(['message' => 'The email address is already registered.'], 400);
    }

    // Check if the email is already in the invitations table (pending invitation)
    $existingInvitation = Invitation::where('email', $request->email)->first();
    if ($existingInvitation) {
        return response()->json(['message' => 'An invitation has already been sent to this email address.'], 400);
    }

    // Generate the invitation token
    $token = Str::random(32);

    $expiresAt = now()->addMinutes(60);

    // Create the invitation
    $invitation = Invitation::create([
        'inviter_id' => $user->id, 
        'email' => $request->email,
        'token' => $token,
        'expires_at' => $expiresAt,
    ]);

    // Send invitation email
    try {
        Mail::to($request->email)->send(new InvitationMail($invitation));
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to send invitation. Please try again later.'], 500);
    }
    

    return response()->json(['message' => 'Invitation sent successfully.'], 201);
}


public function registerUsingInvitation($token)
    {
        // Find the invitation using the token
        $invitation = Invitation::where('token', $token)->first();

        // Check if the invitation exists and has not expired
        if (!$invitation) {
            return response()->json(['message' => 'Invalid or expired invitation token.'], 400);
        }

        // Check if the invitation has expired
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            // Optionally delete the expired invitation
            $invitation->delete();
            return response()->json(['message' => 'This invitation has expired and has been deleted.'], 400);
        }

        // Show registration form (Optional)
        // You can return a view or a response to the user with prefilled data from the invitation
        return response()->json([
            'message' => 'Invitation is valid.',
            'invitation' => $invitation,
        ]);
    }

    public function completeRegistration(Request $request, $token)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the invitation using the token
        $invitation = Invitation::where('token', $token)->first();

        // Check if the invitation exists and has not expired
        if (!$invitation) {
            return response()->json(['message' => 'Invalid or expired invitation token.'], 400);
        }

        // Check if the invitation has expired
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();
            return response()->json(['message' => 'This invitation has expired and has been deleted.'], 400);
        }

        // Create the user using the invitation email and the provided password
        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
        ]);

        // Optionally, you can log the user in after registration
        Auth::login($user);

        // Delete the invitation after use
        $invitation->delete();

        // Respond with success
        return response()->json(['message' => 'User registered successfully!'], 201);
    }

}
