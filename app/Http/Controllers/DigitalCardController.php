<?php

namespace App\Http\Controllers;

use App\Mail\EditCodeMail;
use App\Models\DigitalCardUser;
use App\Models\DigitalCardSocialLink;
use App\Models\DigitalCardUsersPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class DigitalCardController extends Controller
{   
    /**
     * digital users register and login
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:digital_card_users,email',
            'password' => 'required|min:6',
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'desc' => 'nullable|string',
            'profile_pic_url' => 'nullable|url',
            'back_pic_link' => 'nullable|url',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        $user = DigitalCardUser::create($validatedData);

        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user,
        ], 201);
    }

    // Login digital card user
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = DigitalCardUser::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate a token for the user
        $token = $user->createToken('DigitalCardApp')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }

    // Logout digital card user
    public function logout(Request $request)
    {
        Auth::user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(['message' => 'Logged out successfully']);
    }

    // Email Verification Method
    public function verifyEmail(Request $request)
    {
        // Validate the verification link
        $request->validate([
            'id' => 'required|exists:digital_card_users,id',
            'hash' => 'required|string',
        ]);

        $user = DigitalCardUser::findOrFail($request->id);

        // Check if the email is already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        // Verify the email address
        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email verified successfully'], 200);
    }




    public function createUser(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'desc' => 'nullable|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:digital_card_users,email',
            'profile_pic_url' => 'nullable|url',
            'back_pic_link' => 'nullable|url',
            'phones' => 'required|array',
            'phones.*' => 'required|numeric',
            'social_links' => 'required|array',
            'social_links.*.name' => 'required|string|max:255',
            'social_links.*.icon' => 'nullable|string|max:255',
            'social_links.*.link' => 'required|url',
        ]);

        $digitalCardUser = DigitalCardUser::create([
            'title' => $validatedData['title'] ?? null,
            'desc' => $validatedData['desc'] ?? null,
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'profile_pic_url' => $validatedData['profile_pic_url'] ?? null,
            'back_pic_link' => $validatedData['back_pic_link'] ?? null,
        ]);

        foreach ($validatedData['phones'] as $phone) {
            DigitalCardUsersPhone::create([
                'user_id' => $digitalCardUser->id,
                'phone' => $phone,
            ]);
        }

        foreach ($validatedData['social_links'] as $socialLink) {
            DigitalCardSocialLink::create([
                'user_id' => $digitalCardUser->id,
                'name' => $socialLink['name'],
                'icon' => $socialLink['icon'] ?? null,
                'link' => $socialLink['link'],
            ]);
        }

        return response()->json([
            'message' => 'User created successfully!',
            'data' => $digitalCardUser->load('phones', 'socialLinks'),
        ], 201);
    }
    public function getUserByCode($userCode)
    {
        $user = DigitalCardUser::with(['socialLinks', 'phones'])
            ->where('user_code', $userCode)
            ->firstOrFail();

        return response()->json($user);
    }
    public function sendEditCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:digital_card_users,email',
        ]);

        $user = DigitalCardUser::where('email', $validated['email'])->firstOrFail();
        $editCode = rand(100000, 999999);
        $user->edit_code = (string) $editCode;
        $user->edit_code_expires_at = now()->addMinutes(120);
        $user->save();
        Mail::to($user->email)->send(new EditCodeMail($editCode));

        return response()->json(['message' => 'Edit code sent successfully!'], 200);
    }
    public function updateUser(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:digital_card_users,email',
            'edit_code' => 'required',
            'title' => 'sometimes|string|max:255',
            'desc' => 'nullable|string',
            'name' => 'sometimes|string|max:255',
            'profile_pic_url' => 'nullable|url',
            'back_pic_link' => 'nullable|url',
            'phones' => 'nullable|array',
            'phones.*' => 'required|numeric|unique:digital_card_users_phones,phone',
            'social_links' => 'nullable|array',
            'social_links.*.name' => 'required|string|max:255',
            'social_links.*.icon' => 'nullable|string|max:255',
            'social_links.*.link' => 'required|url',
        ]);
        $user = DigitalCardUser::where('email', $validated['email'])->firstOrFail();
        if ($user->edit_code !== (string) $validated['edit_code'] || $user->edit_code_expires_at < now()) {
            return response()->json(['message' => 'Invalid or expired edit code.'], 400);
        }
        $user->update($request->only(['title', 'desc', 'name', 'profile_pic_url', 'back_pic_link']));
        if (isset($validated['phones'])) {
            $user->phones()->delete();
            foreach ($validated['phones'] as $phone) {
                DigitalCardUsersPhone::create([
                    'user_id' => $user->id,
                    'phone' => $phone,
                ]);
            }
        }
        if (isset($validated['social_links'])) {
            $user->socialLinks()->delete();
            foreach ($validated['social_links'] as $socialLink) {
                DigitalCardSocialLink::create([
                    'user_id' => $user->id,
                    'name' => $socialLink['name'],
                    'icon' => $socialLink['icon'] ?? null,
                    'link' => $socialLink['link'],
                ]);
            }
        }
        $user->edit_code = null;
        $user->edit_code_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Digital card updated successfully!', 'user' => $user], 200);
    }
    public function deleteUser($userCode)
    {
        $user = DigitalCardUser::with(['socialLinks', 'phones'])
        ->where('user_code', $userCode)
        ->firstOrFail();
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}

