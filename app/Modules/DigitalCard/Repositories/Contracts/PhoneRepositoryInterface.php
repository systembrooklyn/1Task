<?php

namespace App\Modules\DigitalCard\Repositories\Contracts;

use App\Models\DigitalCardUsersPhone;

interface PhoneRepositoryInterface
{
    public function deleteByUserId(int $userId): void;
    public function create(array $data): DigitalCardUsersPhone;
}
