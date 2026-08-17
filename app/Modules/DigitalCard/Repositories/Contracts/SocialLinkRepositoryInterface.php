<?php

namespace App\Modules\DigitalCard\Repositories\Contracts;

use App\Models\DigitalCardSocialLink;

interface SocialLinkRepositoryInterface
{
    public function deleteByUserId(int $userId): void;
    public function create(array $data): DigitalCardSocialLink;
}
