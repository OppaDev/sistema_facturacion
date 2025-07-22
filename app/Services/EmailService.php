<?php

namespace App\Services;

use App\Models\Factura;
use Illuminate\Support\Facades\Log;
use SendGrid\Mail\Mail;
use SendGrid;

class EmailService
{
    /**
     * Enviar factura por email usando SendGrid API
     */
    public function enviarFactura(Factura $factura, string $email, string $asunto, string $mensaje): bool
    {
        try {
            // Verificar que la factura esté emitida
            if (!$factura->isEmitida()) {
                throw new \Exception('La factura debe estar emitida antes de enviar por email');
            }

            // Verificar que el cliente tenga email
            if (empty($email)) {
                throw new \Exception('El email del destinatario es requerido');
            }

            // Generar PDF de la factura
            $pdf = $this->generarPDFFactura($factura);
            
            // Obtener configuración de SendGrid
            $apiKey = config('mail.mailers.sendgrid.api_key');
            $fromAddress = config('mail.from.address');
            $fromName = config('mail.from.name');
            
            if (empty($apiKey)) {
                throw new \Exception('La clave API de SendGrid no está configurada');
            }
            
            // Enviar email usando SendGrid API
            $sendgrid = new SendGrid($apiKey);
            
            $mail = new Mail();
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            $mail->setSubject($asunto);
            $mail->addTo($email);
            $mail->addContent("text/html", $this->generarHTMLFactura($factura, $mensaje));
            
            // Adjuntar PDF
            $attachment = new \SendGrid\Mail\Attachment();
            $attachment->setContent(base64_encode($pdf));
            $attachment->setType("application/pdf");
            $attachment->setFilename("factura_" . $factura->getNumeroFormateado() . ".pdf");
            $attachment->setDisposition("attachment");
            $mail->addAttachment($attachment);
            
            $response = $sendgrid->send($mail);
            
            if ($response->statusCode() == 202) {
                Log::info("✅ Email enviado exitosamente a {$email} para factura #{$factura->getNumeroFormateado()}");
                return true;
            } else {
                Log::error("❌ Error al enviar email. Status: " . $response->statusCode());
                Log::error("Respuesta: " . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error("❌ Error enviando email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar PDF de la factura
     */
    private function generarPDFFactura(Factura $factura): string
    {
        try {
            $html = view('facturas.pdf', compact('factura'))->render();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->output();
        } catch (\Exception $e) {
            Log::error("Error generando PDF para factura #{$factura->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generar HTML del email
     */
    private function generarHTMLFactura(Factura $factura, string $mensaje): string
    {
        return view('emails.factura', compact('factura', 'mensaje'))->render();
    }

    /**
     * Verificar configuración de email
     */
    public function verificarConfiguracion(): array
    {
        return [
            'provider' => 'SendGrid API',
            'api_key' => config('mail.mailers.sendgrid.api_key') ? '***configurada***' : 'no configurada',
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }
} 