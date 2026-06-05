<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function enable(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_secret'         => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(
                json_encode($recoveryCodes)
            ),
            'two_factor_confirmed_at'   => null, // not yet confirmed
        ]);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return [
            'secret'         => $secret,
            'qr_code_url'    => $qrCodeUrl,
            'recovery_codes' => $recoveryCodes,
        ];
    }

    public function confirm(User $user, string $code): void
    {
        $secret = Crypt::decryptString($user->two_factor_secret);

        $valid = $this->google2fa->verifyKey($secret, $code);

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => ['The provided two-factor code is invalid.'],
            ]);
        }

        $user->update(['two_factor_confirmed_at' => now()]);
    }

    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);
    }

    public function verify(User $user, string $code): bool
    {
        $secret = Crypt::decryptString($user->two_factor_secret);
        return $this->google2fa->verifyKey($secret, $code);
    }

    public function useRecoveryCode(User $user, string $code): void
    {
        $codes = json_decode(
            Crypt::decryptString($user->two_factor_recovery_codes),
            true
        );

        $index = array_search($code, $codes);

        if ($index === false) {
            throw ValidationException::withMessages([
                'code' => ['Invalid recovery code.'],
            ]);
        }

        // Invalidate the used recovery code
        unset($codes[$index]);

        $user->update([
            'two_factor_recovery_codes' => Crypt::encryptString(
                json_encode(array_values($codes))
            ),
        ]);
    }

    private function generateRecoveryCodes(int $count = 8): array
    {
        return Collection::times($count, fn () =>
            strtoupper(Str::random(5) . '-' . Str::random(5))
        )->all();
    }
}
