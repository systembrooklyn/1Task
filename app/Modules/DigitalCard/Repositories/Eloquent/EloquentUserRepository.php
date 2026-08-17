<?php

namespace App\Modules\DigitalCard\Repositories\Eloquent;

use App\Models\DigitalCardUser;
use App\Modules\DigitalCard\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(array $data): DigitalCardUser
    {
        return DigitalCardUser::create($data);
    }

    public function findByEmail(string $email): ?DigitalCardUser
    {
        return DigitalCardUser::where('email', $email)->first();
    }

    public function findByUserCode(string $userCode): ?DigitalCardUser
    {
        return DigitalCardUser::where('user_code', $userCode)->first();
    }

    public function findById(int $id): ?DigitalCardUser
    {
        return DigitalCardUser::find($id);
    }

    public function update(DigitalCardUser $user, array $data): DigitalCardUser
    {
        $user->update($data);
        return $user;
    }

    public function delete(DigitalCardUser $user): void
    {
        $user->delete();
    }
}
