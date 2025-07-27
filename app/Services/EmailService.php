<?php

namespace App\Services;

use App\Models\Factura;
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
     * Enviar factura por email usando Maileroo API
     */
    public function enviarFactura(Factura $factura, string $email, string $asunto, string $mensaje): bool
    {
        try {
            $result = $this->mailerooService->enviarFactura($factura, $email, $asunto, $mensaje);
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Error enviando factura por email', [
                'factura_id' => $factura->id,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
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
