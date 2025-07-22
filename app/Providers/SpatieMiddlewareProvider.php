<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SpatieMiddlewareProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $router = $this->app['router'];
        
        // Verificar si las clases existen antes de registrarlas
        if (class_exists(\Spatie\Permission\Middleware\RoleMiddleware::class)) {
            $router->aliasMiddleware('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
        }
        
        if (class_exists(\Spatie\Permission\Middleware\PermissionMiddleware::class)) {
            $router->aliasMiddleware('permission', \Spatie\Permission\Middleware\PermissionMiddleware::class);
        }
        
        if (class_exists(\Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class)) {
            $router->aliasMiddleware('role_or_permission', \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class);
        }
    }
}
