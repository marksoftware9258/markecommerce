<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\AuthUserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Authentication
 */
class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * Register a new user
     *
     * @unauthenticated
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse(
            data: [
                'user'  => new AuthUserResource($result['user']),
                'token' => $result['token'],
            ],
            message: 'Registration successful. Please verify your email.',
            status: 201
        );
    }

    /**
     * Login
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->successResponse(
            data: [
                'user'       => new AuthUserResource($result['user']),
                'token'      => $result['token'],
                'token_type' => 'Bearer',
                'expires_at' => $result['expires_at'],
            ],
            message: 'Login successful.'
        );
    }

    /**
     * Logout current device
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(message: 'Logged out successfully.');
    }

    /**
     * Logout all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(message: 'Logged out from all devices.');
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refreshToken($request->user());

        return $this->successResponse(data: [
            'token'      => $result['token'],
            'expires_at' => $result['expires_at'],
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new AuthUserResource($request->user()->load('roles.permissions'))
        );
    }

    /**
     * Verify email
     *
     * @unauthenticated
     */
    public function verifyEmail(string $token): JsonResponse
    {
        $this->authService->verifyEmail($token);

        return $this->successResponse(message: 'Email verified successfully.');
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $this->authService->resendVerification($request->user());

        return $this->successResponse(message: 'Verification email sent.');
    }
}
