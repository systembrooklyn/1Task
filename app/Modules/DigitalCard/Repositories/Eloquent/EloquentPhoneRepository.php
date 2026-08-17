<?php

namespace App\Modules\DigitalCard\Repositories\Eloquent;

use App\Models\DigitalCardUsersPhone;
use App\Modules\DigitalCard\Repositories\Contracts\PhoneRepositoryInterface;

class EloquentPhoneRepository implements PhoneRepositoryInterface
{
    public function deleteByUserId(int $userId): void
    {
        DigitalCardUsersPhone::where('user_id', $userId)->delete();
    }

    public function create(array $data): DigitalCardUsersPhone
    {
        return DigitalCardUsersPhone::create($data);
    }
}
