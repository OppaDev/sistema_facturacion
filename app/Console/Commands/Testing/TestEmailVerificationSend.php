<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\User;

class TestEmailVerificationSend extends Command
{
    protected $signature = 'test:verification-send {email}';
    protected $description = 'Enviar email de verificación a un usuario específico';

    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario no encontrado: {$email}");
            return 1;
        }

        $this->info("Usuario encontrado: {$user->name} ({$user->email})");
        $this->info("Email verificado: " . ($user->hasVerifiedEmail() ? 'Sí' : 'No'));

        if ($user->hasVerifiedEmail()) {
            $this->warn("Este usuario ya tiene el email verificado.");
            $this->ask("¿Quieres enviar el email de todas formas? Presiona Enter para continuar o Ctrl+C para cancelar");
        }

        $this->info("Enviando email de verificación...");

        try {
            $user->sendEmailVerificationNotification();
            $this->info("✅ Email de verificación enviado exitosamente a: {$user->email}");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar email:");
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
