<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UsersPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    public function index()
    {
        $user = User::with('profile', 'phones', 'links')->findOrFail(Auth::id());
        return response()->json([
            'message' => 'user retreived successfully',
            'data' => new UserResource($user)
        ], 200);
    }

    public function show($id)
    {
        $loggedUser = Auth::user();
        $user = User::with('profile', 'phones', 'links')->find($id);
        if ($loggedUser->company != $user->company) {
            return response()->json([
                'message' => 'you can only see profiles within your company',
                'error' => 'wrong data'
            ], 403);
        }
        if (!$user) {
            return response()->json([
                'message' => 'this user hasnt created his profile yet',
            ], 404);
        }
        return response()->json([
            'message' => 'user retreived successfully',
            'data' => new UserResource($user)
        ], 200);
    }

    public function update(Request $request)
    {
        $user = User::find(Auth::id());
        DB::beginTransaction();

        try {
            $validatedUser = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'string',
                    'email',
                    'max:255',
                    'unique:users,email,' . $user->id,
                ],
            ]);

            if (!empty($validatedUser)) {
                $user->update($validatedUser);
            }
            if ($request->has('profile')) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $request->input('profile')
                );
            }
            if ($request->has('phones')) {
                $phones = $request->input('phones');
                $seenPhones = [];
                foreach ($phones as $phoneData) {
                    $cc = $phoneData['CC'] ?? '';
                    $phone = $phoneData['phone'] ?? '';
                    if (empty($cc) || empty($phone)) {
                        throw ValidationException::withMessages([
                            'phones' => ['Phone CC or number is missing.']
                        ]);
                    }
                    if (in_array($phone, $seenPhones)) {
                        throw ValidationException::withMessages([
                            'phones' => ["Duplicate phone in request: +{$cc} {$phone}"]
                        ]);
                    }
                    $seenPhones[] = $phone;
                    $exists = UsersPhone::where('CC', $cc)
                        ->where('phone', $phone)
                        ->where('user_id', '!=', $user->id)
                        ->exists();
                    if ($exists) {
                        throw ValidationException::withMessages([
                            'phones' => ["Phone is already used by another user: +{$cc} {$phone}"]
                        ]);
                    }
                }
                $user->phones()->delete();
                foreach ($phones as $phoneData) {
                    $user->phones()->create($phoneData);
                }
            }
            if ($request->has('links')) {
                $user->links()->delete();
                foreach ($request->input('links') as $linkData) {
                    $user->links()->create($linkData);
                }
            }

            DB::commit();
            $user->load('profile', 'phones', 'links');

            return response()->json([
                'message' => 'user data updated successfully',
                'data' => new UserResource($user)
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }
        }
    }
}
