<?php

namespace App\Http\Controllers;

use App\Models\DigitalCardUser;
use App\Models\DigitalCardSocialLink;
use App\Models\DigitalCardUsersPhone;
use Illuminate\Http\Request;

class DigitalCardController extends Controller
{
    public function listUsers()
    {
        return DigitalCardUser::with(['socialLinks', 'phones'])->get();
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

        // Create the digital card user
        $digitalCardUser = DigitalCardUser::create([
            'title' => $validatedData['title'] ?? null,
            'desc' => $validatedData['desc'] ?? null,
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'profile_pic_url' => $validatedData['profile_pic_url'] ?? null,
            'back_pic_link' => $validatedData['back_pic_link'] ?? null,
        ]);

        // Save associated phone numbers
        foreach ($validatedData['phones'] as $phone) {
            DigitalCardUsersPhone::create([
                'user_id' => $digitalCardUser->id,
                'phone' => $phone,
            ]);
        }

        // Save associated social links
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

    public function getUser($id)
    {
        return DigitalCardUser::with(['socialLinks', 'phones'])->findOrFail($id);
    }
    public function getUserByCode($userCode)
    {
        $user = DigitalCardUser::with(['socialLinks', 'phones'])
            ->where('user_code', $userCode)
            ->firstOrFail();

        return response()->json($user);
    }
    public function updateUser(Request $request, $id)
    {
        $user = DigitalCardUser::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string',
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:digital_card_users,email,' . $id,
            'desc' => 'nullable|string',
            'profile_pic_url' => 'nullable|string',
            'back_pic_link' => 'nullable|string',
        ]);

        $user->update($data);
        return $user;
    }
    public function deleteUser($id)
    {
        $user = DigitalCardUser::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
    public function listSocialLinks($userId)
    {
        return DigitalCardSocialLink::where('user_id', $userId)->get();
    }
    public function addSocialLink(Request $request, $userId)
    {
        $user = DigitalCardUser::findOrFail($userId);

        $data = $request->validate([
            'name' => 'required|string',
            'icon' => 'required|string',
            'link' => 'required|string|url',
        ]);

        return $user->socialLinks()->create($data);
    }
    public function updateSocialLink(Request $request, $id)
    {
        $link = DigitalCardSocialLink::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string',
            'icon' => 'sometimes|string',
            'link' => 'sometimes|string|url',
        ]);

        $link->update($data);
        return $link;
    }
    public function deleteSocialLink($id)
    {
        $link = DigitalCardSocialLink::findOrFail($id);
        $link->delete();
        return response()->json(['message' => 'Social link deleted successfully']);
    }
    public function listPhones($userId)
    {
        return DigitalCardUsersPhone::where('user_id', $userId)->get();
    }
    public function addPhone(Request $request, $userId)
    {
        $user = DigitalCardUser::findOrFail($userId);

        $data = $request->validate([
            'phone' => 'required|numeric|unique:digital_card_users_phones,phone',
        ]);

        return $user->phones()->create($data);
    }
    public function updatePhone(Request $request, $id)
    {
        $phone = DigitalCardUsersPhone::findOrFail($id);

        $data = $request->validate([
            'phone' => 'required|numeric|unique:digital_card_users_phones,phone,' . $id,
        ]);

        $phone->update($data);
        return $phone;
    }
    public function deletePhone($id)
    {
        $phone = DigitalCardUsersPhone::findOrFail($id);
        $phone->delete();
        return response()->json(['message' => 'Phone deleted successfully']);
    }
}

