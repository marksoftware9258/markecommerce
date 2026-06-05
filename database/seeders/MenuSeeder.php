<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole   = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();

        $menus = [
            [
                'name'      => 'Dashboard',
                'slug'      => 'dashboard',
                'url'       => '/dashboard',
                'icon'      => 'home',
                'type'      => 'sidebar',
                'order'     => 1,
                'is_active' => true,
                'roles'     => [],  // all authenticated users
            ],
            [
                'name'      => 'User Management',
                'slug'      => 'user-management',
                'url'       => null,
                'icon'      => 'users',
                'type'      => 'sidebar',
                'order'     => 2,
                'is_active' => true,
                'roles'     => [$adminRole?->id, $managerRole?->id],
                'children'  => [
                    ['name' => 'Users',       'slug' => 'users-list',   'url' => '/users',       'icon' => 'user',        'order' => 1],
                    ['name' => 'Roles',       'slug' => 'roles-list',   'url' => '/roles',       'icon' => 'shield',      'order' => 2],
                    ['name' => 'Permissions', 'slug' => 'perms-list',   'url' => '/permissions', 'icon' => 'key',         'order' => 3],
                ],
            ],
            [
                'name'      => 'Menus',
                'slug'      => 'menu-management',
                'url'       => '/menus',
                'icon'      => 'menu',
                'type'      => 'sidebar',
                'order'     => 3,
                'is_active' => true,
                'roles'     => [$adminRole?->id],
            ],
            [
                'name'      => 'Activity Logs',
                'slug'      => 'activity-logs',
                'url'       => '/activity-logs',
                'icon'      => 'activity',
                'type'      => 'sidebar',
                'order'     => 4,
                'is_active' => true,
                'roles'     => [$adminRole?->id],
            ],
            [
                'name'      => 'Settings',
                'slug'      => 'settings',
                'url'       => '/settings',
                'icon'      => 'settings',
                'type'      => 'sidebar',
                'order'     => 5,
                'is_active' => true,
                'roles'     => [$adminRole?->id],
            ],
        ];

        foreach ($menus as $menuData) {
            $children  = $menuData['children'] ?? [];
            $roleIds   = array_filter($menuData['roles'] ?? []);
            unset($menuData['children'], $menuData['roles']);

            $menu = Menu::firstOrCreate(['slug' => $menuData['slug']], $menuData);

            if (!empty($roleIds)) {
                $menu->roles()->sync($roleIds);
            }

            foreach ($children as $i => $child) {
                $child['parent_id'] = $menu->id;
                $child['type']      = 'sidebar';
                $child['is_active'] = true;
                $childMenu = Menu::firstOrCreate(['slug' => $child['slug']], $child);

                if (!empty($roleIds)) {
                    $childMenu->roles()->sync($roleIds);
                }
            }
        }

        $this->command->info('✓ Menus seeded.');
    }
}
