<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Strict mode in non-production
        Model::shouldBeStrict(!app()->isProduction());

        // Unwrap data key from API resources
        JsonResource::withoutWrapping();

        // Super-admin bypasses all Gate checks
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });

        // Impersonation policy
        Gate::define('impersonate', function (User $user, User $target) {
            return $user->hasRole('super-admin') && $user->id !== $target->id;
        });

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        // General API throttle
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(
                (int) config('app.api_rate_limit', 60)
            )->by($request->user()?->id ?: $request->ip());
        });

        // Strict throttle for login endpoint
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email') . '|' . $request->ip());
        });
    }
}
