<?php

namespace App\Http\Controllers\Api\V1\Permission;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Permission\PermissionCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/**
 * @group Permission Management
 */
class PermissionController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $permissions = Permission::query()
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
            )
            ->orderBy('name')
            ->paginate($request->get('per_page', 50));

        return $this->paginatedResponse(PermissionCollection::make($permissions));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|unique:permissions,name',
            'guard_name' => 'sometimes|string',
            'group'      => 'sometimes|string',
        ]);

        $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
            'group'      => $request->group,
        ]);

        return $this->createdResponse(new PermissionResource($permission));
    }

    public function show(Permission $permission): JsonResponse
    {
        return $this->successResponse(new PermissionResource($permission));
    }

    public function update(Request $request, Permission $permission): JsonResponse
    {
        $request->validate([
            'name'  => "required|string|unique:permissions,name,{$permission->id}",
            'group' => 'sometimes|string',
        ]);

        $permission->update($request->only('name', 'group'));

        return $this->successResponse(new PermissionResource($permission));
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();

        return $this->noContentResponse();
    }

    /**
     * Get permissions grouped by category/module
     */
    public function grouped(): JsonResponse
    {
        $permissions = Permission::all()
            ->groupBy(fn ($p) => $p->group ?? 'general')
            ->map(fn ($group) => $group->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->name,
            ]));

        return $this->successResponse($permissions);
    }
}
