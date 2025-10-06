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
                \App\Console\Commands\Testing\TestMaileroo::class,
                \App\Console\Commands\Testing\TestResend::class,
                \App\Console\Commands\Testing\TestEmailVerification::class,
                \App\Console\Commands\Testing\TestEmailVerificationSend::class,
            ]);
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
