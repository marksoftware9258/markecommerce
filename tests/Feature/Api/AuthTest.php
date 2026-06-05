<?php

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
});

// -----------------------------------------------
// Registration
// -----------------------------------------------
describe('Registration', function () {

    it('can register a new user', function () {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['user', 'token'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    });

    it('rejects duplicate email on registration', function () {
        User::factory()->create(['email' => 'john@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
        ])->assertUnprocessable();
    });

    it('requires all mandatory fields', function () {
        $this->postJson('/api/v1/auth/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });
});

// -----------------------------------------------
// Login
// -----------------------------------------------
describe('Login', function () {

    it('can login with valid credentials', function () {
        $user = User::factory()->create([
            'password' => Hash::make('Password@123'),
            'status'   => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'Password@123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user', 'token', 'token_type', 'expires_at'],
            ]);
    });

    it('rejects invalid credentials', function () {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    });

    it('rejects suspended users', function () {
        $user = User::factory()->create([
            'password' => Hash::make('Password@123'),
            'status'   => UserStatus::Suspended,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'Password@123',
        ])->assertUnprocessable();
    });
});

// -----------------------------------------------
// Logout
// -----------------------------------------------
describe('Logout', function () {

    it('can logout', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Token should be revoked
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    });

    it('can logout from all devices', function () {
        $user = User::factory()->create();
        $t1 = $user->createToken('device-1')->plainTextToken;
        $t2 = $user->createToken('device-2')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$t1}")
            ->postJson('/api/v1/auth/logout-all')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    });
});

// -----------------------------------------------
// Me
// -----------------------------------------------
describe('Me', function () {

    it('returns authenticated user', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    });

    it('returns 401 when unauthenticated', function () {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    });
});
