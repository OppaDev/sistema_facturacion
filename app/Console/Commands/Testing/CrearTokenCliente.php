<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\User;

class CrearTokenCliente extends Command
{
    protected $signature = 'test:crear-token {email}';
    protected $description = 'Crea un token API para un cliente específico';

    public function handle()
    {
        $email = $this->argument('email');
        
        $usuario = User::where('email', $email)->first();
        
        if (!$usuario) {
            $this->error("❌ Usuario no encontrado: {$email}");
            return 1;
        }
        
        if (!$usuario->hasRole('Cliente')) {
            $this->error("❌ El usuario {$email} no tiene rol 'Cliente'");
            return 1;
        }
        
        // Crear token
        $token = $usuario->createToken('API Token - Testing')->plainTextToken;
        
        $this->info("🔑 Token creado exitosamente para: {$usuario->name}");
        $this->line("📧 Email: {$email}");
        $this->line("🎟️ Token: {$token}");
        $this->line("");
        $this->info("💡 Guarda este token para usar en las pruebas API");
        
        return 0;
    }
}