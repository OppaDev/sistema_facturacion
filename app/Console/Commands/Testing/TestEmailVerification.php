<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use App\Mail\WelcomeUser;

class TestEmailVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maileroo:verify {email} {--type=verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el envío de emails de verificación y bienvenida usando Maileroo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->option('type');

        $this->info('==========================================================');
        $this->info('  PRUEBA DE EMAILS - INFERNO CLUB');
        $this->info('==========================================================');
        $this->newLine();

        try {
            if ($type === 'verify') {
                $this->testVerificationEmail($email);
            } elseif ($type === 'welcome') {
                $this->testWelcomeEmail($email);
            } elseif ($type === 'both') {
                $this->testVerificationEmail($email);
                $this->newLine();
                $this->testWelcomeEmail($email);
            } else {
                $this->error('Tipo no válido. Usa: verify, welcome o both');
                return Command::FAILURE;
            }

            $this->newLine();
            $this->info('==========================================================');
            $this->info('✅ Prueba completada exitosamente');
            $this->info('==========================================================');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('==========================================================');
            $this->error('❌ ERROR al enviar email');
            $this->error('==========================================================');
            $this->error('Mensaje: ' . $e->getMessage());
            $this->error('Archivo: ' . $e->getFile() . ':' . $e->getLine());
            
            return Command::FAILURE;
        }
    }

    private function testVerificationEmail($email)
    {
        $this->info('📧 Enviando Email de Verificación...');
        $this->info('Destinatario: ' . $email);
        $this->newLine();

        // Generar URL de verificación simulada
        $verificationUrl = url('/verify-email/' . base64_encode($email . '|' . now()->timestamp));
        
        Mail::to($email)->send(new VerifyEmail($verificationUrl, 'Usuario de Prueba'));

        $this->info('✅ Email de verificación enviado correctamente');
        $this->info('URL de verificación: ' . $verificationUrl);
    }

    private function testWelcomeEmail($email)
    {
        $this->info('📧 Enviando Email de Bienvenida...');
        $this->info('Destinatario: ' . $email);
        $this->newLine();

        $loginUrl = url('/login');
        
        Mail::to($email)->send(new WelcomeUser('Usuario de Prueba', $email, $loginUrl));

        $this->info('✅ Email de bienvenida enviado correctamente');
        $this->info('URL de login: ' . $loginUrl);
    }
}
