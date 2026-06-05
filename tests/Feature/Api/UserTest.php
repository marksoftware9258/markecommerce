<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->regularUser = User::factory()->create();
    $this->regularUser->assignRole('user');
});

describe('User Index', function () {

    it('admin can list users', function () {
        User::factory()->count(5)->create();

        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    });

    it('regular user cannot list users', function () {
        $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertForbidden();
    });

    it('can filter users by status', function () {
        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/users?status=active')
            ->assertOk();
    });
});

describe('User Store', function () {

    it('admin can create a user', function () {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/users', [
                'name'                  => 'New User',
                'email'                 => 'newuser@example.com',
                'password'              => 'Password@123',
                'password_confirmation' => 'Password@123',
                'roles'                 => ['user'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'newuser@example.com');

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    });

    it('validates required fields on user creation', function () {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/users', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });
});

describe('User Update', function () {

    it('admin can update a user', function () {
        $user = User::factory()->create();

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/users/{$user->id}", ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    });
});

describe('User Delete', function () {

    it('admin can soft delete a user', function () {
        $user = User::factory()->create();

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/users/{$user->id}")
            ->assertOk();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    });

    it('admin cannot delete themselves', function () {
        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/users/{$this->admin->id}")
            ->assertForbidden();
    });
});

describe('User Status', function () {

    it('admin can update user status', function () {
        $user = User::factory()->create();

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/users/{$user->id}/status", ['status' => 'suspended'])
            ->assertOk();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'suspended']);
    });
});

describe('Bulk Actions', function () {

    it('admin can bulk activate users', function () {
        $users = User::factory()->count(3)->create(['status' => 'inactive']);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/users/bulk-action', [
                'action' => 'activate',
                'ids'    => $users->pluck('id')->toArray(),
            ])
            ->assertOk()
            ->assertJsonPath('meta.processed', 3);
    });
});
