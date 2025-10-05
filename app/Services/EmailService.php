<?php

namespace App\Services;

use App\Services\MailerooService;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected MailerooService $mailerooService;

    public function __construct()
    {
        $this->mailerooService = new MailerooService();
    }

    /**
     * Enviar email general usando Maileroo
     */
    public function sendEmail(string $to, string $subject, string $textContent, ?string $htmlContent = null): bool
    {
        try {
            $result = $this->mailerooService->sendEmail($to, $subject, $textContent, $htmlContent);
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Error enviando email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar configuracion de email
     */
    public function verificarConfiguracion(): array
    {
        return [
            'provider' => 'Maileroo API',
            'api_key' => config('services.maileroo.api_key') ? '***configurada***' : 'no configurada',
            'domain' => config('services.maileroo.domain') ?: 'no configurado',
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }

    /**
     * Test de conexión con Maileroo
     */
    public function testConnection(): bool
    {
        $result = $this->mailerooService->testConnection();
        return $result['success'] ?? false;
    }

    /**
     * Obtener estadísticas de la cuenta
     */
    public function getStats(): ?array
    {
        return $this->mailerooService->getStats();
    }
}
