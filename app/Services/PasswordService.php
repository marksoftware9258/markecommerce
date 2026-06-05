<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordService
{
    public function sendResetLink(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        // Throttle: one request per minute
        $throttleKey = "password-reset-throttle:{$email}";
        if (Cache::has($throttleKey)) {
            throw ValidationException::withMessages([
                'email' => ['Please wait before requesting another reset link.'],
            ]);
        }

        $token = Str::random(64);
        $expiresAt = now()->addMinutes(config('auth.passwords.users.expire', 60));

        DB::table('password_reset_tokens')->upsert([
            'email'      => $email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ], ['email']);

        Cache::put($throttleKey, true, 60); // 1 minute throttle

        $user->notify(new ResetPasswordNotification($token, $expiresAt));
    }

    public function resetPassword(string $token, string $email, string $newPassword): void
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !Hash::check($token, $record->token)) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired reset token.'],
            ]);
        }

        $expiry = config('auth.passwords.users.expire', 60);
        if (Carbon::parse($record->created_at)->addMinutes($expiry)->isPast()) {
            throw ValidationException::withMessages([
                'token' => ['This reset token has expired.'],
            ]);
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->update(['password' => $newPassword]);

        // Invalidate all tokens after password reset
        $user->tokens()->delete();

        // Clean up reset token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        activity('auth')->causedBy($user)->log('Password reset');
    }
}
