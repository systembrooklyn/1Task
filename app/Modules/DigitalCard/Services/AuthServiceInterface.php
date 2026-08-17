<?php

namespace App\Modules\DigitalCard\Services;

use App\Models\DigitalCardUser;

interface AuthServiceInterface
{
    public function register(array $data): DigitalCardUser;
    public function verifyCode(string $email, string $code): DigitalCardUser;
    public function login(string $email, string $password): array; // returns ['token' => token] or throws
}
