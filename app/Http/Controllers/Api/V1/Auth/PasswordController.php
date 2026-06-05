<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\PasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Password Management
 */
class PasswordController extends BaseController
{
    public function __construct(private readonly PasswordService $passwordService) {}

    /**
     * Send password reset link
     *
     * @unauthenticated
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $this->passwordService->sendResetLink($request->email);

        return $this->successResponse(
            message: 'Password reset link sent to your email.'
        );
    }

    /**
     * Reset password using token
     *
     * @unauthenticated
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'                 => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $this->passwordService->resetPassword(
            $request->token,
            $request->email,
            $request->password
        );

        return $this->successResponse(message: 'Password reset successfully. Please login.');
    }
}
