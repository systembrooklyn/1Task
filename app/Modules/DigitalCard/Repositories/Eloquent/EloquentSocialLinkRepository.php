<?php

namespace App\Modules\DigitalCard\Repositories\Eloquent;

use App\Models\DigitalCardSocialLink;
use App\Modules\DigitalCard\Repositories\Contracts\SocialLinkRepositoryInterface;

class EloquentSocialLinkRepository implements SocialLinkRepositoryInterface
{
    public function deleteByUserId(int $userId): void
    {
        DigitalCardSocialLink::where('user_id', $userId)->delete();
    }

    public function create(array $data): DigitalCardSocialLink
    {
        return DigitalCardSocialLink::create($data);
    }
}
