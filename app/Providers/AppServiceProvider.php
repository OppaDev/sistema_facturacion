<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
