<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly TokenService $tokenService
    ) {}

    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'phone'    => $data['phone'] ?? null,
            'status'   => UserStatus::Active,
        ]);

        // Assign default role
        $user->assignRole('user');

        // Send verification email
        $this->sendVerificationEmail($user);

        $token = $this->tokenService->createToken($user, 'auth-token');

        return ['user' => $user, 'token' => $token['token'], 'expires_at' => $token['expires_at']];
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->isSuspended()) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended. Please contact support.'],
            ]);
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        // Optionally revoke old tokens
        if (config('auth.revoke_previous_tokens', false)) {
            $user->tokens()->where('name', 'auth-token')->delete();
        }

        $tokenData = $this->tokenService->createToken(
            $user,
            'auth-token',
            $credentials['device_name'] ?? request()->userAgent()
        );

        activity('auth')->causedBy($user)->log('User logged in');

        return [
            'user'       => $user,
            'token'      => $tokenData['token'],
            'expires_at' => $tokenData['expires_at'],
        ];
    }

    public function refreshToken(User $user): array
    {
        $user->currentAccessToken()->delete();
        return $this->tokenService->createToken($user, 'auth-token');
    }

    public function verifyEmail(string $token): void
    {
        $email = Cache::get("email-verify:{$token}");

        if (!$email) {
            throw new \Exception('Invalid or expired verification token.');
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->markEmailAsVerified();

        Cache::forget("email-verify:{$token}");
    }

    public function resendVerification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new \Exception('Email already verified.');
        }
        $this->sendVerificationEmail($user);
    }

    private function sendVerificationEmail(User $user): void
    {
        $token = Str::random(64);
        Cache::put("email-verify:{$token}", $user->email, now()->addMinutes(60));
        $user->notify(new VerifyEmailNotification($token));
    }
}
