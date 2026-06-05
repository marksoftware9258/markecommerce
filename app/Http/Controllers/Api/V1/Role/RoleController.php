<?php

namespace App\Http\Controllers\Api\V1\Role;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\User\UserCollection;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * @group Role Management
 */
class RoleController extends BaseController
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        $roles = $this->roleService->paginate($request->all());

        return $this->paginatedResponse(RoleCollection::make($roles));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return $this->createdResponse(new RoleResource($role->load('permissions')));
    }

    public function show(Role $role): JsonResponse
    {
        return $this->successResponse(
            new RoleResource($role->load('permissions'))
        );
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->update($role, $request->validated());

        return $this->successResponse(new RoleResource($role->load('permissions')));
    }

    public function destroy(Role $role): JsonResponse
    {
        abort_if(in_array($role->name, ['super-admin', 'admin', 'user']), 403,
            'Cannot delete system roles.');

        $this->roleService->delete($role);

        return $this->successResponse(message: 'Role deleted.');
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions);

        return $this->successResponse(
            new RoleResource($role->load('permissions')),
            'Permissions synced.'
        );
    }

    public function users(Role $role, Request $request): JsonResponse
    {
        $users = $role->users()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse(UserCollection::make($users));
    }
}
