<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min($filters['per_page'] ?? 15, 100);

        return Role::query()
            ->withCount('users', 'permissions')
            ->when($filters['search'] ?? null, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        if (!empty($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh(['permissions']);
    }

    public function delete(Role $role): void
    {
        // Remove role from all users first
        $role->users()->detach();
        $role->delete();
    }
}
