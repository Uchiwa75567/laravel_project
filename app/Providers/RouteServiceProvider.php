<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Limite par utilisateur : 10000 requêtes/heure
        RateLimiter::for('user-hour', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perHour(10000)->by($key);
        });

        // Limite par IP : 1000 requêtes/minute
        RateLimiter::for('ip-minute', function (Request $request) {
            return Limit::perMinute(1000)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware(['api', 'throttle:user-hour', 'throttle:ip-minute'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
