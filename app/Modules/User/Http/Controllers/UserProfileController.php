<?php

namespace App\Modules\User\Http\Controllers;

use App\Modules\User\Services\ProfileService;
use App\Modules\User\Http\Requests\UpdateProfileRequest;
use App\Modules\User\Http\Requests\UploadProfilePictureRequest;
use App\Modules\User\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function __construct(protected ProfileService $profileService) {}

    public function index(): JsonResponse
    {
        $user = Auth::user()->load('profile', 'phones', 'links');
        return response()->json([
            'message' => 'user retrieved successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function show($id): JsonResponse
    {
        $loggedUser = Auth::user();
        $user = User::with('profile', 'phones', 'links')->find($id);
        if (!$user || $loggedUser->company_id != $user->company_id) {
            return response()->json(['message' => 'you can only see profiles within your company'], 403);
        }
        return response()->json([
            'message' => 'user retrieved successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile(Auth::user(), $request->validated());
        return response()->json([
            'message' => 'user data updated successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    public function uploadProfilePicture(UploadProfilePictureRequest $request): JsonResponse
    {
        $url = $this->profileService->uploadProfilePicture(Auth::user(), $request->file('profile_picture'));
        return response()->json([
            'message' => 'Profile picture uploaded successfully.',
            'url' => $url,
            'file_size_kb' => round($request->file('profile_picture')->getSize() / 1024, 2),
        ], 200);
    }
}
