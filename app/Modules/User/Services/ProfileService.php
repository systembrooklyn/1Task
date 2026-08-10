<?php

namespace App\Modules\User\Services;

use App\Models\User;
use App\Models\UsersPhone;
use App\Services\PlanLimitService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function __construct(
        protected PlanLimitService $planService,
    ) {}

    public function updateProfile(User $user, array $data): User
    {
        DB::beginTransaction();
        try {
            // Update user main fields
            if (isset($data['name']) || isset($data['last_name']) || isset($data['email'])) {
                $user->update($data);
            }

            // Update profile
            if (isset($data['profile'])) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $data['profile']
                );
            }

            // Update phones
            if (isset($data['phones'])) {
                $this->validatePhones($data['phones'], $user->id);
                $user->phones()->delete();
                foreach ($data['phones'] as $phoneData) {
                    $user->phones()->create($phoneData);
                }
            }

            // Update links
            if (isset($data['links'])) {
                $user->links()->delete();
                foreach ($data['links'] as $linkData) {
                    $linkData['link_name'] = $linkData['link_name'] ?? $linkData['icon'];
                    $user->links()->create($linkData);
                }
            }

            DB::commit();
            $user->load('profile', 'phones', 'links');
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Upload a profile picture to DigitalOcean Spaces.
     */
    public function uploadProfilePicture(User $user, UploadedFile $file): string
    {
        // Check storage limits via PlanLimitService (as in original code)
        $fileSizeKB = $file->getSize() / 1024;
        $oldSizeKB = $user->profile->ppSize ?? 0;
        $finalSize = round($fileSizeKB - $oldSizeKB, 2);
        $this->planService->checkFeatureAccess($user->company_id, 'limit_storage', $finalSize);

        $company = $user->company;
        $disk = Storage::disk('spaces');

        // Build the directory path: 1Task/{company_name}/profile-pictures/
        $directory = "1Task/{$company->name}/profile-pictures";
        $newFileName = $file->hashName(); // Unique name

        // Delete old profile picture if it exists and is stored in Spaces
        $this->deleteOldProfilePicture($user, $disk);

        // Upload the new file to Spaces
        $path = $disk->putFileAs($directory, $file, $newFileName, 'public');

        if (!$path) {
            throw new \Exception('Failed to upload profile picture to Spaces.');
        }

        // Get the public URL
        $url = $disk->url($path);

        // Save the new file info in the user's profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'ppUrl'   => $url,
                'ppPath'  => $path,        // Store the relative path for future deletion
                'ppSize'  => $fileSizeKB,
            ]
        );

        return $url;
    }

    /**
     * Delete the old profile picture from Spaces if it exists.
     */
    protected function deleteOldProfilePicture(User $user, $disk): void
    {
        $profile = $user->profile;
        if (!$profile || empty($profile->ppPath)) {
            return;
        }

        // Only delete if the file exists in Spaces
        if ($disk->exists($profile->ppPath)) {
            $disk->delete($profile->ppPath);
        }
    }

    /**
     * Validate phone numbers for duplicates (same as original logic).
     */
    protected function validatePhones(array $phones, int $userId): void
    {
        $seenPhones = [];
        foreach ($phones as $phoneData) {
            $cc = $phoneData['CC'] ?? '';
            $phone = $phoneData['phone'] ?? '';
            if (empty($cc) || empty($phone)) {
                throw ValidationException::withMessages(['phones' => 'Phone CC or number is missing.']);
            }
            if (in_array($phone, $seenPhones)) {
                throw ValidationException::withMessages(['phones' => "Duplicate phone in request: +{$cc} {$phone}"]);
            }
            $seenPhones[] = $phone;

            // Check if the phone is already used by another user (excluding current)
            $exists = UsersPhone::where('CC', $cc)
                ->where('phone', $phone)
                ->where('user_id', '!=', $userId)
                ->exists();
            if ($exists) {
                throw ValidationException::withMessages(['phones' => "Phone is already used by another user: +{$cc} {$phone}"]);
            }
        }
    }
}
