<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Str;

class MenuService
{
    public function create(array $data): Menu
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $menu = Menu::create($data);

        if (!empty($data['roles'])) {
            $menu->roles()->sync($data['roles']);
        }

        if (!empty($data['permissions'])) {
            $menu->permissions()->sync($data['permissions']);
        }

        return $menu;
    }

    public function update(Menu $menu, array $data): Menu
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $menu->update($data);

        if (isset($data['roles'])) {
            $menu->roles()->sync($data['roles']);
        }

        if (isset($data['permissions'])) {
            $menu->permissions()->sync($data['permissions']);
        }

        return $menu->fresh(['children', 'roles', 'permissions']);
    }

    public function getTree(string $type = 'sidebar'): array
    {
        return Menu::with('children.children')
            ->active()
            ->ofType($type)
            ->roots()
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function getMenusForUser(User $user): array
    {
        $userRoleIds = $user->roles->pluck('id')->toArray();
        $userPermissionIds = $user->getAllPermissions()->pluck('id')->toArray();

        $menus = Menu::with('children')
            ->active()
            ->where(function ($q) use ($userRoleIds, $userPermissionIds) {
                // Menus with no restrictions (public menus)
                $q->doesntHave('roles')
                  ->doesntHave('permissions')
                  // OR menus assigned to user's roles
                  ->orWhereHas('roles', fn ($q) =>
                      $q->whereIn('id', $userRoleIds)
                  )
                  // OR menus requiring permissions user has
                  ->orWhereHas('permissions', fn ($q) =>
                      $q->whereIn('id', $userPermissionIds)
                  );
            })
            ->roots()
            ->orderBy('order')
            ->get();

        return $menus->toArray();
    }

    public function reorder(array $items): void
    {
        foreach ($items as $item) {
            Menu::where('id', $item['id'])->update([
                'order'     => $item['order'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }
    }
}
