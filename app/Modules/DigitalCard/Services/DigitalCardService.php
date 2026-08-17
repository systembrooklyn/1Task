<?php

namespace App\Modules\DigitalCard\Services;

use App\Models\DigitalCardUser;
use App\Modules\DigitalCard\Repositories\Contracts\UserRepositoryInterface;
use App\Modules\DigitalCard\Repositories\Contracts\SocialLinkRepositoryInterface;
use App\Modules\DigitalCard\Repositories\Contracts\PhoneRepositoryInterface;
use Illuminate\Validation\ValidationException;

class DigitalCardService implements DigitalCardServiceInterface
{
    protected UserRepositoryInterface $userRepo;
    protected SocialLinkRepositoryInterface $socialLinkRepo;
    protected PhoneRepositoryInterface $phoneRepo;

    public function __construct(
        UserRepositoryInterface $userRepo,
        SocialLinkRepositoryInterface $socialLinkRepo,
        PhoneRepositoryInterface $phoneRepo
    ) {
        $this->userRepo = $userRepo;
        $this->socialLinkRepo = $socialLinkRepo;
        $this->phoneRepo = $phoneRepo;
    }

    public function getDigitalCard(int $userId): DigitalCardUser
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            throw ValidationException::withMessages(['user' => 'User not found.']);
        }
        return $user->load('socialLinks', 'phones');
    }

    public function updateDigitalCard(int $userId, array $data): DigitalCardUser
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            throw ValidationException::withMessages(['user' => 'User not found.']);
        }

        // Update basic fields
        $updatable = ['title', 'desc', 'profile_pic_url', 'back_pic_link'];
        $updateData = array_intersect_key($data, array_flip($updatable));
        $this->userRepo->update($user, $updateData);

        // Handle social links
        if (isset($data['social_links'])) {
            $this->socialLinkRepo->deleteByUserId($userId);
            foreach ($data['social_links'] as $socialLink) {
                $this->socialLinkRepo->create([
                    'user_id' => $userId,
                    'name' => $socialLink['name'],
                    'icon' => $socialLink['icon'],
                    'link' => $socialLink['link'],
                ]);
            }
        }

        // Handle phones
        if (isset($data['phones'])) {
            $this->phoneRepo->deleteByUserId($userId);
            foreach ($data['phones'] as $phone) {
                $this->phoneRepo->create([
                    'user_id' => $userId,
                    'phone' => $phone['phone'],
                ]);
            }
        }

        return $user->load('socialLinks', 'phones');
    }

    public function deleteAccount(int $userId): void
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            throw ValidationException::withMessages(['user' => 'User not found.']);
        }
        $this->userRepo->delete($user);
    }

    public function viewDigitalCard(string $userCode): ?DigitalCardUser
    {
        return $this->userRepo->findByUserCode($userCode)?->load('socialLinks', 'phones');
    }
}
