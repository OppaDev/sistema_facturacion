<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use App\Models\User;

class VerificarRoles extends Command
{
    protected $signature = 'test:verificar-roles {--user-email=}';
    protected $description = 'Verifica los roles del sistema y de un usuario específico';

    public function handle()
    {
        $this->info('🔍 Verificando roles del sistema...');
        
        // Mostrar todos los roles
        $roles = Role::all();
        $this->info('📋 Roles existentes en el sistema:');
        foreach ($roles as $role) {
            $this->line("  - {$role->name} (ID: {$role->id})");
        }
        
        // Verificar usuario específico si se proporciona email
        $emailUsuario = $this->option('user-email');
        if ($emailUsuario) {
            $this->info("\n👤 Verificando usuario: {$emailUsuario}");
            
            $usuario = User::where('email', $emailUsuario)->first();
            if ($usuario) {
                $this->info("✅ Usuario encontrado: {$usuario->name} (ID: {$usuario->id})");
                
                $rolesUsuario = $usuario->getRoleNames();
                if ($rolesUsuario->count() > 0) {
                    $this->info("🎭 Roles asignados:");
                    foreach ($rolesUsuario as $rol) {
                        $this->line("  - {$rol}");
                    }
                } else {
                    $this->warn("⚠️ El usuario NO tiene roles asignados");
                }
                
                // Verificar si tiene rol Pagos específicamente
                if ($usuario->hasRole('Pagos')) {
                    $this->info("✅ El usuario SÍ tiene el rol 'Pagos'");
                } else {
                    $this->error("❌ El usuario NO tiene el rol 'Pagos'");
                }
            } else {
                $this->error("❌ Usuario no encontrado con email: {$emailUsuario}");
            }
        }
        
        // Mostrar usuarios con rol Pagos
        $this->info("\n💰 Usuarios con rol 'Pagos':");
        $usuariosPagos = User::role('Pagos')->get();
        if ($usuariosPagos->count() > 0) {
            foreach ($usuariosPagos as $user) {
                $this->line("  - {$user->name} ({$user->email})");
            }
        } else {
            $this->warn("⚠️ No hay usuarios con el rol 'Pagos' asignado");
        }
        
        return 0;
    }
}