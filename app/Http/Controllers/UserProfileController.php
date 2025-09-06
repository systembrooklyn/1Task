<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UsersPhone;
// use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{

    // protected $planService;

    // public function __construct(PlanLimitService $planService)
    // {
    //     $this->planService = $planService;
    // }
    public function index()
    {
        $user = User::with('profile', 'phones', 'links')->findOrFail(Auth::id());
        // $result = $this->planService->checkFeatureAccess($user->company_id, 'api_calls');

        // if (!$result['allowed']) {
        //     return response()->json([
        //         'message' => $result['message'],
        //         'feature' => $result['feature'],
        //         'limit'   => $result['limit'],
        //         'used'    => $result['used']
        //     ], 403);
        // }
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
                'last_name' => 'sometimes|string|max:255',
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
                    $linkData['link_name'] = $linkData['link_name'] ?? $linkData['icon'];
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


    public function uploadProfilePicture(Request $request)
    {
        $userAuth = Auth::user();
        $user = User::find($userAuth->id);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        // $fileSizeKB = $request->file('profile_picture')->getSize() / 1024;
        // $oldSizeKB = $user->profile->ppSize ?? 0;
        // $finalSize = (round($fileSizeKB, 2) - round($oldSizeKB, 2));
        // $result = $this->planService->checkFeatureAccess($user->company_id, 'limit_storage', $finalSize);

        // if (!$result['allowed']) {
        //     return response()->json([
        //         'message' => $result['message'],
        //         'feature' => $result['feature'],
        //         'used' => round($result['used'], 2) . ' MB',
        //         'limit' => round($result['limit'], 2) . ' MB'
        //     ], 403);
        // }
        $pic = $request->file('profile_picture');
        $company = $user->company;

        $firebaseConfig = [
            'apiKey' => "AIzaSyC8p6mRMJEuv0y4AFA6GP0fVPlQyyRAWhQ",
            'authDomain' => "brooklyn-chat.firebaseapp.com",
            'databaseURL' => "https://brooklyn-chat-default-rtdb.europe-west1.firebasedatabase.app ",
            'projectId' => "brooklyn-chat",
            'storageBucket' => "brooklyn-chat.appspot.com",
            'messagingSenderId' => "450185737947",
            'appId' => "1:450185737947:web:a7dce19db9e0b37478fefe"
        ];
        $storageBucket = $firebaseConfig['storageBucket'];
        $filePath = "1Task/{$company->name}/profile-pictures/" . $pic->hashName();
        $firebaseStorageUrl = "https://firebasestorage.googleapis.com/v0/b/{$storageBucket}/o/" . urlencode($filePath) . "?uploadType=media";
        $uploadToken = $request->bearerToken() ?: "YOUR_UPLOAD_TOKEN";
        $profile = $user->profile;
        if ($profile && $profile->ppPath) {
            $oldFilePath = urlencode($profile->ppPath);
            $deleteUrl = "https://firebasestorage.googleapis.com/v0/b/brooklyn-chat.appspot.com/o/$oldFilePath";
            $deleteResponse = Http::withHeaders([
                'Authorization' => "Bearer {$uploadToken}",
            ])->delete($deleteUrl);

            if (!$deleteResponse->successful() && $deleteResponse->status() !== 404) {
                return response()->json([
                    'warning' => 'Failed to delete old profile picture.',
                    'details' => $deleteResponse->json()
                ], 200);
            }
        }
        $fileContent = fopen($pic->getPathname(), 'r');

        $response = Http::timeout(300)
            ->withHeaders([
                'Authorization' => "Bearer {$uploadToken}",
                'Content-Type' => $pic->getMimeType(),
            ])
            ->withBody($fileContent, $pic->getMimeType())
            ->post($firebaseStorageUrl);

        fclose($fileContent);

        if ($response->successful()) {
            $fileMetadata = $response->json();
            $fileName = basename($fileMetadata['name']);
            $fileSizeKB = $fileMetadata['size'] / 1024;
            $downloadToken = $fileMetadata['downloadTokens'];
            $downloadUrl = "https://firebasestorage.googleapis.com/v0/b/{$storageBucket}/o/" .
                urlencode($filePath) . "?alt=media&token={$downloadToken}";
            $profileData = [
                'ppUrl' => $downloadUrl,
                'ppPath' => $filePath,
                'ppSize' => $fileSizeKB
            ];

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            return response()->json([
                'message' => 'Profile picture uploaded successfully.',
                'url' => $downloadUrl,
                'file_size_kb' => round($fileSizeKB, 2),
            ], 200);
        }

        return response()->json([
            'error' => 'File upload failed',
            'details' => $response->body()
        ], 500);
    }
}
