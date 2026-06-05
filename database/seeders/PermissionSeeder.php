<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Permissions are grouped by module.
     * Format: 'group' => ['permission-name', ...]
     */
    private array $permissions = [
        'users' => [
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'impersonate-users',
        ],
        'roles' => [
            'manage-roles',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
        ],
        'permissions' => [
            'manage-permissions',
            'view-permissions',
        ],
        'menus' => [
            'manage-menus',
            'view-menus',
            'create-menus',
            'edit-menus',
            'delete-menus',
        ],
        'activity-logs' => [
            'view-activity-logs',
        ],
        'settings' => [
            'manage-settings',
            'view-settings',
        ],
        'dashboard' => [
            'view-dashboard',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $group => $names) {
            foreach ($names as $name) {
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['group' => $group]
                );
            }
        }

        $this->command->info('✓ Permissions seeded.');
    }
}
