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
            if (isset($data['name']) || isset($data['last_name']) || isset($data['email'])) {
                $user->update($data);
            }

            if (isset($data['profile'])) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $data['profile']
                );
            }

            if (isset($data['phones'])) {
                $this->validatePhones($data['phones'], $user->id);
                $user->phones()->delete();
                foreach ($data['phones'] as $phoneData) {
                    $user->phones()->create($phoneData);
                }
            }

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
        $fileSizeKB = $file->getSize() / 1024;
        $oldSizeKB = $user->profile->ppSize ?? 0;
        $finalSize = round($fileSizeKB - $oldSizeKB, 2);
        $this->planService->checkFeatureAccess($user->company_id, 'limit_storage', $finalSize);

        $company = $user->company;
        $disk = Storage::disk('spaces');

        $directory = "1Task/{$company->name}/profile-pictures";
        $newFileName = $file->hashName();

        $this->deleteOldProfilePicture($user, $disk);

        $path = $disk->putFileAs($directory, $file, $newFileName, 'public');

        if (!$path) {
            throw new \Exception('Failed to upload profile picture to Spaces.');
        }

        $url = $disk->url($path);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'ppUrl'   => $url,
                'ppPath'  => $path,
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
