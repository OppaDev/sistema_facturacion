<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\User;

class TestMenuLogica extends Command
{
    protected $signature = 'test:menu-logica {email}';
    protected $description = 'Testa la lógica del menú para un usuario específico';

    public function handle()
    {
        $email = $this->argument('email');
        
        $usuario = User::where('email', $email)->first();
        if (!$usuario) {
            $this->error("Usuario no encontrado: {$email}");
            return 1;
        }
        
        $this->info("🔍 Testeando lógica del menú para: {$usuario->name} ({$email})");
        
        // Test de roles individuales
        $roles = ['Administrador', 'Secretario', 'Bodega', 'Ventas', 'Cliente', 'Pagos'];
        
        foreach ($roles as $rol) {
            $tieneRol = $usuario->hasRole($rol);
            $status = $tieneRol ? '✅' : '❌';
            $this->line("  {$status} hasRole('{$rol}'): " . ($tieneRol ? 'true' : 'false'));
        }
        
        // Test de hasAnyRole específico
        $this->info("\n🎭 Test de hasAnyRole:");
        
        $combinaciones = [
            'Administrador|Secretario',
            'Administrador|Bodega', 
            'Administrador|Ventas',
            'Administrador|Pagos',
            'Administrador|Cliente'
        ];
        
        foreach ($combinaciones as $combo) {
            $roles = explode('|', $combo);
            $tieneAlgunRol = $usuario->hasAnyRole($roles);
            $status = $tieneAlgunRol ? '✅' : '❌';
            $this->line("  {$status} hasAnyRole(['{$combo}']): " . ($tieneAlgunRol ? 'true' : 'false'));
        }
        
        // Test específico para pagos
        $this->info("\n💰 Test específico para Gestión de Pagos:");
        $puedeVerPagos = $usuario->hasAnyRole(['Administrador', 'Pagos']);
        $status = $puedeVerPagos ? '✅ PUEDE VER' : '❌ NO PUEDE VER';
        $this->line("  {$status} Menú de Gestión de Pagos");
        
        // Contar pagos pendientes (como en el sidebar)
        $pagosPendientes = \App\Models\Pago::where('estado', 'pendiente')->count();
        $this->info("📊 Pagos pendientes en sistema: {$pagosPendientes}");
        
        return 0;
    }
}