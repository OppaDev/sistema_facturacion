<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use App\Models\User;

class VerificarTodosLosRoles extends Command
{
    protected $signature = 'test:verificar-todos-roles';
    protected $description = 'Verifica todos los roles del sistema y qué usuarios tienen cada rol';

    public function handle()
    {
        $this->info('🎭 VERIFICACIÓN COMPLETA DE ROLES DEL SISTEMA');
        $this->line('');

        // Obtener todos los roles
        $roles = Role::orderBy('name')->get();
        
        $this->info("📋 ROLES EXISTENTES: {$roles->count()} roles encontrados");
        $this->line('');

        foreach ($roles as $rol) {
            $this->info("🔹 ROL: {$rol->name} (ID: {$rol->id})");
            
            // Obtener usuarios con este rol
            $usuarios = User::role($rol->name)->get();
            
            if ($usuarios->count() > 0) {
                $this->line("   👥 Usuarios ({$usuarios->count()}):");
                foreach ($usuarios as $usuario) {
                    $todosLosRoles = $usuario->getRoleNames()->toArray();
                    $otrosRoles = array_filter($todosLosRoles, function($r) use ($rol) {
                        return $r !== $rol->name;
                    });
                    
                    $status = count($otrosRoles) > 0 ? '⚠️' : '✅';
                    $extra = count($otrosRoles) > 0 ? ' (+' . implode(', ', $otrosRoles) . ')' : '';
                    
                    $this->line("     {$status} {$usuario->name} ({$usuario->email}){$extra}");
                }
            } else {
                $this->warn("     ⚠️ Sin usuarios asignados");
            }
            $this->line('');
        }

        // Resumen de usuarios con múltiples roles
        $this->info('📊 RESUMEN DE USUARIOS CON MÚLTIPLES ROLES:');
        
        $usuariosMultiplesRoles = User::whereHas('roles', function($query) {
            // Usuarios que tienen más de un rol
        })->get()->filter(function($user) {
            return $user->roles->count() > 1;
        });

        if ($usuariosMultiplesRoles->count() > 0) {
            foreach ($usuariosMultiplesRoles as $usuario) {
                $roles = $usuario->getRoleNames()->toArray();
                $this->line("⚠️ {$usuario->name} ({$usuario->email}): " . implode(', ', $roles));
            }
        } else {
            $this->info('✅ Todos los usuarios tienen un solo rol asignado');
        }

        $this->line('');
        
        // Estadísticas finales
        $totalUsuarios = User::count();
        $usuariosConRoles = User::whereHas('roles')->count();
        $usuariosSinRoles = $totalUsuarios - $usuariosConRoles;

        $this->info('📈 ESTADÍSTICAS FINALES:');
        $this->line("   👥 Total usuarios: {$totalUsuarios}");
        $this->line("   ✅ Con roles: {$usuariosConRoles}");
        $this->line("   ❌ Sin roles: {$usuariosSinRoles}");

        return 0;
    }
}