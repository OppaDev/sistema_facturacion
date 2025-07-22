<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SendGrid\Mail\Mail;
use SendGrid;

class TestSendGridAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sendgrid-api {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar envío de email con SendGrid API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info("Probando SendGrid API para: {$email}");

        try {
            $apiKey = config('mail.mailers.sendgrid.api_key');
            
            if (empty($apiKey)) {
                $this->error("❌ La clave API de SendGrid no está configurada.");
                $this->error("Configura SENDGRID_API_KEY en tu archivo .env");
                return 1;
            }
            
            $sendgrid = new SendGrid($apiKey);
            
            $mail = new Mail();
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            $mail->setSubject("Prueba SendGrid API - " . config('mail.from.name'));
            $mail->addTo($email);
            $mail->addContent("text/html", "<h1>¡Hola desde SendGrid API!</h1><p>Si recibes este email, SendGrid API está funcionando correctamente.</p><p>Fecha: " . now()->format('d/m/Y H:i:s') . "</p>");
            
            $response = $sendgrid->send($mail);
            
            if ($response->statusCode() == 202) {
                $this->info("✅ Email enviado exitosamente!");
                $this->info("Revisa tu bandeja de entrada en: {$email}");
            } else {
                $this->error("❌ Error al enviar email. Status: " . $response->statusCode());
                $this->error("Respuesta: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("❌ Error al enviar email: " . $e->getMessage());
            $this->error("Detalles: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
} 