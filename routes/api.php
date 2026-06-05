<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Auth\TwoFactorController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Http\Controllers\Api\V1\Role\RoleController;
use App\Http\Controllers\Api\V1\Permission\PermissionController;
use App\Http\Controllers\Api\V1\Menu\MenuController;
use App\Http\Controllers\Api\V1\ActivityLogController;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Auth Required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:login');

        Route::post('forgot-password', [PasswordController::class, 'forgotPassword']);
        Route::post('reset-password', [PasswordController::class, 'resetPassword']);
        Route::get('verify-email/{token}', [AuthController::class, 'verifyEmail'])
            ->name('verification.verify');
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Auth Required)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'check.token.blacklist'])->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
            Route::post('resend-verification', [AuthController::class, 'resendVerification']);

            // Two-Factor Authentication
            Route::prefix('2fa')->group(function () {
                Route::post('enable', [TwoFactorController::class, 'enable']);
                Route::post('disable', [TwoFactorController::class, 'disable']);
                Route::post('verify', [TwoFactorController::class, 'verify']);
                Route::post('recovery', [TwoFactorController::class, 'useRecoveryCode']);
            });
        });

        // Profile
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::post('avatar', [ProfileController::class, 'uploadAvatar']);
            Route::delete('avatar', [ProfileController::class, 'deleteAvatar']);
            Route::put('password', [ProfileController::class, 'changePassword']);
            Route::get('activity', [ProfileController::class, 'activityLog']);
            Route::get('sessions', [ProfileController::class, 'activeSessions']);
            Route::delete('sessions/{tokenId}', [ProfileController::class, 'revokeSession']);
        });

        // Users (admin)
        Route::middleware('permission:manage-users')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('users/{user}/restore', [UserController::class, 'restore']);
            Route::delete('users/{user}/force', [UserController::class, 'forceDelete']);
            Route::put('users/{user}/status', [UserController::class, 'updateStatus']);
            Route::post('users/{user}/roles', [UserController::class, 'assignRoles']);
            Route::post('users/{user}/permissions', [UserController::class, 'assignPermissions']);
            Route::post('users/{user}/impersonate', [UserController::class, 'impersonate']);
            Route::post('users/bulk-action', [UserController::class, 'bulkAction']);
        });

        // Roles
        Route::middleware('permission:manage-roles')->group(function () {
            Route::apiResource('roles', RoleController::class);
            Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
            Route::get('roles/{role}/users', [RoleController::class, 'users']);
        });

        // Permissions
        Route::middleware('permission:manage-permissions')->group(function () {
            Route::apiResource('permissions', PermissionController::class);
            Route::get('permissions/grouped', [PermissionController::class, 'grouped']);
        });

        // Menus
        Route::prefix('menus')->group(function () {
            Route::get('/', [MenuController::class, 'index'])
                ->middleware('permission:manage-menus|view-menus');
            Route::post('/', [MenuController::class, 'store'])
                ->middleware('permission:manage-menus');
            Route::get('/tree', [MenuController::class, 'tree']);
            Route::get('/my-menus', [MenuController::class, 'myMenus']); // role-based
            Route::put('/reorder', [MenuController::class, 'reorder'])
                ->middleware('permission:manage-menus');
            Route::get('/{menu}', [MenuController::class, 'show']);
            Route::put('/{menu}', [MenuController::class, 'update'])
                ->middleware('permission:manage-menus');
            Route::delete('/{menu}', [MenuController::class, 'destroy'])
                ->middleware('permission:manage-menus');
        });

        // Activity Logs
        Route::middleware('permission:view-activity-logs')->group(function () {
            Route::get('activity-logs', [ActivityLogController::class, 'index']);
            Route::get('activity-logs/{log}', [ActivityLogController::class, 'show']);
        });
    });
});
