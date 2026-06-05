<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\BulkActionRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group User Management
 */
class UserController extends BaseController
{
    public function __construct(private readonly UserService $userService) {}

    /**
     * List all users
     *
     * @queryParam search string Filter by name or email. Example: john
     * @queryParam status string Filter by status. Example: active
     * @queryParam role string Filter by role. Example: admin
     * @queryParam per_page int Results per page (max 100). Example: 15
     * @queryParam sort string Sort field. Example: created_at
     * @queryParam order string Sort direction (asc|desc). Example: desc
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->paginate($request->all());

        return $this->paginatedResponse(UserCollection::make($users));
    }

    /**
     * Create user
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->createdResponse(
            new UserResource($user->load('roles', 'permissions')),
            'User created successfully.'
        );
    }

    /**
     * Get user
     */
    public function show(User $user): JsonResponse
    {
        return $this->successResponse(
            new UserResource($user->load('roles', 'permissions'))
        );
    }

    /**
     * Update user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());

        return $this->successResponse(
            new UserResource($user->load('roles', 'permissions')),
            'User updated successfully.'
        );
    }

    /**
     * Soft delete user
     */
    public function destroy(User $user): JsonResponse
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot delete yourself.');

        $this->userService->delete($user);

        return $this->successResponse(message: 'User deleted successfully.');
    }

    /**
     * Restore soft-deleted user
     */
    public function restore(int $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return $this->successResponse(message: 'User restored successfully.');
    }

    /**
     * Permanently delete user
     */
    public function forceDelete(int $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();

        return $this->noContentResponse();
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,inactive,suspended']);

        $this->userService->updateStatus($user, $request->status);

        return $this->successResponse(message: 'User status updated.');
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(Request $request, User $user): JsonResponse
    {
        $request->validate(['roles' => 'required|array', 'roles.*' => 'exists:roles,name']);

        $user->syncRoles($request->roles);

        return $this->successResponse(
            new UserResource($user->load('roles')),
            'Roles assigned successfully.'
        );
    }

    /**
     * Assign permissions to user
     */
    public function assignPermissions(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user->syncPermissions($request->permissions);

        return $this->successResponse(
            new UserResource($user->load('permissions')),
            'Permissions assigned successfully.'
        );
    }

    /**
     * Bulk action on users
     */
    public function bulkAction(BulkActionRequest $request): JsonResponse
    {
        $result = $this->userService->bulkAction(
            $request->action,
            $request->ids
        );

        return $this->successResponse(message: "Bulk action '{$request->action}' completed.", meta: $result);
    }

    /**
     * Impersonate a user (super-admin only)
     */
    public function impersonate(User $user): JsonResponse
    {
        $this->authorize('impersonate', $user);

        $token = $user->createToken('impersonation', ['*'], now()->addHour());

        return $this->successResponse([
            'token'      => $token->plainTextToken,
            'expires_at' => now()->addHour()->toIso8601String(),
            'user'       => new UserResource($user),
        ], 'Impersonation token generated.');
    }
}
