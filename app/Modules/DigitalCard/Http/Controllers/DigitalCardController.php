<?php

namespace App\Modules\DigitalCard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DigitalCard\Http\Requests\UpdateDigitalCardRequest;
use App\Modules\DigitalCard\Services\DigitalCardServiceInterface;
use Illuminate\Http\JsonResponse;

class DigitalCardController extends Controller
{
    protected DigitalCardServiceInterface $digitalCardService;

    public function __construct(DigitalCardServiceInterface $digitalCardService)
    {
        $this->digitalCardService = $digitalCardService;
    }

    public function getDigitalCard(): JsonResponse
    {
        $user = auth('digital_card_users')->user();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $userWithRelations = $this->digitalCardService->getDigitalCard($user->id);
        return response()->json([
            'user' => $userWithRelations,
        ], 200);
    }

    public function updateDigitalCard(UpdateDigitalCardRequest $request): JsonResponse
    {
        $user = auth('digital_card_users')->user();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $updatedUser = $this->digitalCardService->updateDigitalCard($user->id, $request->validated());
        return response()->json([
            'message' => 'Digital card updated successfully.',
            'user' => $updatedUser,
        ], 200);
    }

    public function deleteAccount(): JsonResponse
    {
        $user = auth('digital_card_users')->user();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $this->digitalCardService->deleteAccount($user->id);
        return response()->json(['message' => 'Account deactivated successfully.'], 200);
    }

    public function viewDigitalCard(string $user_code): JsonResponse
    {
        $user = $this->digitalCardService->viewDigitalCard($user_code);
        if (!$user) {
            return response()->json(['message' => 'Digital card not found.'], 404);
        }
        return response()->json([
            'message' => 'Digital card retrieved successfully.',
            'digital_card' => $user,
        ], 200);
    }
}
