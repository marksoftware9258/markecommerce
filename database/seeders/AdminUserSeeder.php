<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('SuperAdmin@123'),
                'status'            => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super-admin');

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Admin User',
                'password'          => Hash::make('Admin@123'),
                'status'            => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name'              => 'Manager User',
                'password'          => Hash::make('Manager@123'),
                'status'            => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        // Regular User
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name'              => 'Regular User',
                'password'          => Hash::make('User@123'),
                'status'            => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('user');

        $this->command->info('✓ Admin users seeded.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['super-admin', 'superadmin@example.com', 'SuperAdmin@123'],
                ['admin',       'admin@example.com',      'Admin@123'],
                ['manager',     'manager@example.com',    'Manager@123'],
                ['user',        'user@example.com',       'User@123'],
            ]
        );
    }
}
