<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Resources\User\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * @group Profile
 */
class ProfileController extends BaseController
{
    public function __construct(private readonly ProfileService $profileService) {}

    public function show(Request $request): JsonResponse
    {
        return $this->successResponse(
            new UserResource($request->user()->load('roles.permissions'))
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->update($request->user(), $request->validated());

        return $this->successResponse(new UserResource($user), 'Profile updated.');
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $this->profileService->uploadAvatar($request->user(), $request->file('avatar'));

        return $this->successResponse(['avatar_url' => $user->avatar_url], 'Avatar uploaded.');
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $this->profileService->deleteAvatar($request->user());

        return $this->successResponse(message: 'Avatar deleted.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->profileService->changePassword($request->user(), $request->validated());

        // Revoke all other tokens after password change
        $request->user()->tokens()
            ->where('id', '!=', $request->user()->currentAccessToken()->id)
            ->delete();

        return $this->successResponse(message: 'Password changed. Other sessions have been revoked.');
    }

    public function activityLog(Request $request): JsonResponse
    {
        $logs = Activity::causedBy($request->user())
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->successResponse($logs);
    }

    public function activeSessions(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select(['id', 'name', 'last_used_at', 'created_at', 'expires_at'])
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'current'      => $t->id === $request->user()->currentAccessToken()->id,
                'last_used_at' => $t->last_used_at,
                'created_at'   => $t->created_at,
                'expires_at'   => $t->expires_at,
            ]);

        return $this->successResponse($tokens);
    }

    public function revokeSession(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->findOrFail($tokenId);
        $token->delete();

        return $this->successResponse(message: 'Session revoked.');
    }
}
