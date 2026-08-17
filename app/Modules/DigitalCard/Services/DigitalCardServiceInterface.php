<?php

namespace App\Modules\DigitalCard\Services;

use App\Models\DigitalCardUser;

interface DigitalCardServiceInterface
{
    public function getDigitalCard(int $userId): DigitalCardUser;
    public function updateDigitalCard(int $userId, array $data): DigitalCardUser;
    public function deleteAccount(int $userId): void;
    public function viewDigitalCard(string $userCode): ?DigitalCardUser;
}
