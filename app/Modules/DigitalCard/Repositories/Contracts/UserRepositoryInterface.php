<?php

namespace App\Modules\DigitalCard\Repositories\Contracts;

use App\Models\DigitalCardUser;

interface UserRepositoryInterface
{
    public function create(array $data): DigitalCardUser;
    public function findByEmail(string $email): ?DigitalCardUser;
    public function findByUserCode(string $userCode): ?DigitalCardUser;
    public function findById(int $id): ?DigitalCardUser;
    public function update(DigitalCardUser $user, array $data): DigitalCardUser;
    public function delete(DigitalCardUser $user): void;
}
