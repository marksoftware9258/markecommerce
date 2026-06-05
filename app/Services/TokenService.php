<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class TokenService
{
    public function createToken(User $user, string $name, ?string $deviceName = null): array
    {
        $ttl = config('sanctum.expiration'); // in minutes

        $abilities = $this->getUserAbilities($user);

        $token = $user->createToken(
            name: $deviceName ?? $name,
            abilities: $abilities,
            expiresAt: $ttl ? now()->addMinutes($ttl) : null
        );

        return [
            'token'      => $token->plainTextToken,
            'expires_at' => $ttl ? now()->addMinutes($ttl)->toIso8601String() : null,
        ];
    }

    private function getUserAbilities(User $user): array
    {
        // You can scope token abilities by role
        // For now return wildcard
        return ['*'];
    }
}
