<?php

namespace App\Http\Controllers;

use App\Models\DigitalCardUser;
use App\Models\DigitalCardSocialLink;
use App\Models\DigitalCardUsersPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Mail;

class DigitalCardController extends Controller
{   
    use HasApiTokens, Notifiable;
    /**
     * digital users register and login
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:digital_card_users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = DigitalCardUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'verification_code' => Str::random(6),
        ]);
        try {
            Mail::send('emails.verification_code', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Your Email Verification Code');
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send verification email.'], 500);
        }

        return response()->json([
            'message' => 'User registered successfully. Please check your email for the verification code.',
        ]);
    }
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verification_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = DigitalCardUser::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if ($user->verification_code !== $request->verification_code) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }
        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully.',
            'user' => $user
        ]);
    }

    // Login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:digital_card_users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = DigitalCardUser::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
        if (is_null($user->email_verified_at)) {
            return response()->json(['message' => 'Please verify your email first.'], 403);
        }
        $token = $user->createToken('DigitalCardApp')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
        ]);
    }

    public function updateDigitalCard(Request $request)
    {
        $user = auth('digital_card_users')->user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'desc' => 'nullable|string',
            'profile_pic_url' => 'nullable|url',
            'back_pic_link' => 'nullable|url',
            'social_links' => 'nullable|array',
            'social_links.*.name' => 'nullable|string',
            'social_links.*.icon' => 'nullable|string',
            'social_links.*.link' => 'nullable|url',
            'phones' => 'nullable|array',
            'phones.*.phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user->update($request->only(['title', 'desc', 'profile_pic_url', 'back_pic_link']));
        if ($request->has('social_links')) {
            $user->socialLinks()->delete();
            foreach ($request->social_links as $socialLink) {
                DigitalCardSocialLink::create([
                    'user_id' => $user->id,
                    'name' => $socialLink['name'],
                    'icon' => $socialLink['icon'],
                    'link' => $socialLink['link'],
                ]);
            }
        }
        if ($request->has('phones')) {
            $user->phones()->delete();
            foreach ($request->phones as $phone) {
                DigitalCardUsersPhone::create([
                    'user_id' => $user->id,
                    'phone' => $phone['phone'],
                ]);
            }
        }
        return response()->json([
            'message' => 'Digital card updated successfully.',
            'user' => $user->load('socialLinks', 'phones'),
        ]);
    }
    /**
     * Deactivate the user's account.
     */
    public function deleteAccount()
    {
        $user = auth('digital_card_users')->user();

        $user->delete();

        return response()->json(['message' => 'Account deactivated successfully.']);
    }

    /**
     * Get the logged-in user's digital card details.
     */
    public function getDigitalCard()
    {
        $user = auth('digital_card_users')->user();

        return response()->json([
            'user' => $user->load('socialLinks', 'phones'),
        ]);
    }

    /**
     * Get a digital card by user_code.
     */
    public function viewDigitalCard($user_code)
    {
        $user = DigitalCardUser::with(['socialLinks', 'phones'])->where('user_code', $user_code)->first();

        if (!$user) {
            return response()->json(['message' => 'Digital card not found.'], 404);
        }

        return response()->json([
            'message' => 'Digital card retrieved successfully.',
            'digital_card' => $user,
        ]);
    }
}

