<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Models\User;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar comandos de testing solo en entornos de desarrollo
        if (app()->environment(['local', 'testing', 'development'])) {
            $this->commands([
                \App\Console\Commands\Testing\CrearFacturaPrueba::class,
                \App\Console\Commands\Testing\TestEmail::class,
                \App\Console\Commands\Testing\TestEmailConfig::class,
                \App\Console\Commands\Testing\TestEmailDetallado::class,
                \App\Console\Commands\Testing\TestFirmaDigital::class,
                \App\Console\Commands\Testing\TestMaileroo::class,
                \App\Console\Commands\Testing\TestResend::class,
                \App\Console\Commands\Testing\TestApiSecurity::class,
                \App\Console\Commands\Testing\TestLoginEndpoint::class,
                \App\Console\Commands\Testing\TestRateLimiterEndpoints::class,
                \App\Console\Commands\Testing\TestRateLimiterFixed::class,
            ]);
        }

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        
        // Configurar Rate Limiters para la API
        $this->configureRateLimiters();
    }

    /**
     * Configurar los rate limiters para la API
     */
    protected function configureRateLimiters(): void
    {
        // Rate limiter para autenticación (muy restrictivo)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by('login-' . $request->ip());
        });

        // Rate limiter para operaciones sensibles (restrictivo)
        RateLimiter::for('sensitive', function (Request $request) {
            $user = $request->user();
            $key = $user 
                ? 'sensitive-user-' . $user->id 
                : 'sensitive-ip-' . $request->ip();
            
            return Limit::perMinute(10)->by($key);
        });

        // Rate limiter para operaciones de escritura (moderado)
        RateLimiter::for('write', function (Request $request) {
            $user = $request->user();
            $key = $user 
                ? 'write-user-' . $user->id 
                : 'write-ip-' . $request->ip();
            
            return Limit::perMinute(30)->by($key);
        });

        // Rate limiter para operaciones de lectura (generoso)
        RateLimiter::for('read', function (Request $request) {
            $user = $request->user();
            $key = $user 
                ? 'read-user-' . $user->id 
                : 'read-ip-' . $request->ip();
            
            return Limit::perMinute(100)->by($key);
        });

        // Rate limiter por defecto para la API
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();
            $key = $user 
                ? 'api-user-' . $user->id 
                : 'api-ip-' . $request->ip();
            
            return Limit::perMinute(60)->by($key);
        });
    }
}
