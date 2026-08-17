<?php

namespace App\Modules\DigitalCard\Services;

use App\Models\DigitalCardUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Modules\DigitalCard\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    protected UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(array $data): DigitalCardUser
    {
        $user = $this->userRepo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'verification_code' => Str::random(6),
        ]);

        // Send email – if fails, we let controller handle or throw
        try {
            Mail::send('emails.verification_code', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Email Verification Code');
            });
        } catch (\Exception $e) {
            // Re-throw or return error – we'll handle in controller
            throw new \Exception('Failed to send verification email.');
        }

        return $user;
    }

    public function verifyCode(string $email, string $code): DigitalCardUser
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            throw ValidationException::withMessages(['email' => 'User not found.']);
        }
        if ($user->verification_code !== $code) {
            throw ValidationException::withMessages(['verification_code' => 'Invalid verification code.']);
        }
        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->verification_code = null;
        $this->userRepo->update($user, $user->toArray()); // or just save
        return $user;
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }
        if (is_null($user->email_verified_at)) {
            throw ValidationException::withMessages(['email' => 'Please verify your email first.']);
        }
        $token = $user->createToken('DigitalCardApp')->plainTextToken;
        return ['token' => $token];
    }
}
