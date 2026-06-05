<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Super Admin — gets all permissions via Gate::before in AuthServiceProvider
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // Admin — most permissions except impersonate
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(
            Permission::where('name', '!=', 'impersonate-users')->pluck('name')
        );

        // Manager — user + menu management
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view-users', 'edit-users',
            'view-roles',
            'view-menus',
            'view-dashboard',
            'view-activity-logs',
        ]);

        // Regular user — minimal permissions
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'view-dashboard',
        ]);

        $this->command->info('✓ Roles seeded.');
    }
}
