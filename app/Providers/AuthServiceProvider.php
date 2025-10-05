<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Venta::class => \App\Policies\VentaPolicy::class,
        \App\Models\TurnoCaja::class => \App\Policies\TurnoCajaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
} 