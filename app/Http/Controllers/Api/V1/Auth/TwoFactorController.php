<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Two-Factor Authentication
 */
class TwoFactorController extends BaseController
{
    public function __construct(private readonly TwoFactorService $twoFactorService) {}

    /**
     * Enable 2FA — returns QR code URI and backup recovery codes
     */
    public function enable(Request $request): JsonResponse
    {
        $result = $this->twoFactorService->enable($request->user());

        return $this->successResponse([
            'qr_code_url'    => $result['qr_code_url'],
            'secret'         => $result['secret'],
            'recovery_codes' => $result['recovery_codes'],
        ], 'Scan the QR code with your authenticator app, then confirm with a code.');
    }

    /**
     * Confirm and activate 2FA with a TOTP code
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $this->twoFactorService->confirm($request->user(), $request->code);

        return $this->successResponse(message: 'Two-factor authentication enabled.');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate(['password' => 'required|string|current_password']);

        $this->twoFactorService->disable($request->user());

        return $this->successResponse(message: 'Two-factor authentication disabled.');
    }

    /**
     * Use a recovery code in place of a TOTP code
     */
    public function useRecoveryCode(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $this->twoFactorService->useRecoveryCode($request->user(), $request->code);

        return $this->successResponse(message: 'Recovery code accepted. Please generate new recovery codes.');
    }
}
