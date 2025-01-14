<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // Redirect user to Google for authentication
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // Handle the callback from Google
    public function handleGoogleCallback()
    {
        try {
            // Use Socialite to obtain user information from Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Check if the user exists in the database by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update Google ID if not already set
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                // Create a new user if no matching email is found
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(uniqid()), // Generate a random password
                ]);
            }

            // Generate an API token
            $token = $user->createToken('GoogleLoginToken')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user->load(['company', 'departments', 'roles.permissions']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error authenticating with Google',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

