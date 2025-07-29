<?php

namespace App\Services;

use App\Models\Factura;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MailerooService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl = 'https://smtp.maileroo.com';

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.maileroo.api_key');
    }

    /**
     * Enviar email usando Maileroo HTTP API
     */
    public function sendEmail($to, $subject, $textContent, $htmlContent = null, $attachments = [])
    {
        try {
            // Preparar multipart/form-data
            $multipart = [
                ['name' => 'from', 'contents' => config('mail.from.address')],
                ['name' => 'to', 'contents' => $to],
                ['name' => 'subject', 'contents' => $subject],
            ];

            if ($htmlContent) {
                $multipart[] = ['name' => 'html', 'contents' => $htmlContent];
            }
            
            if ($textContent) {
                $multipart[] = ['name' => 'plain', 'contents' => $textContent];
            }

            // Procesar adjuntos
            foreach ($attachments as $attachment) {
                if (isset($attachment['content'])) {
                    $multipart[] = [
                        'name' => 'attachments[]',
                        'contents' => base64_decode($attachment['content']),
                        'filename' => $attachment['name'],
                        'headers' => ['Content-Type' => $attachment['type'] ?? 'application/octet-stream']
                    ];
                }
            }

            $response = $this->client->post($this->baseUrl . '/send', [
                'headers' => [
                    'X-API-Key' => $this->apiKey,
                ],
                'multipart' => $multipart,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Email enviado via Maileroo HTTP API', [
                'to' => $to,
                'subject' => $subject,
                'response' => $result
            ]);

            return [
                'success' => $result['success'] ?? true,
                'message' => $result['message'] ?? 'Email enviado correctamente',
                'data' => $result
            ];

        } catch (RequestException $e) {
            $errorMessage = 'Error enviando email via Maileroo HTTP API';
            $errorDetails = [];

            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody()->getContents();
                $errorDetails = json_decode($body, true) ?? ['raw_response' => $body];
                
                Log::error($errorMessage, [
                    'to' => $to,
                    'subject' => $subject,
                    'status_code' => $response->getStatusCode(),
                    'error' => $errorDetails
                ]);
            } else {
                Log::error($errorMessage, [
                    'to' => $to,
                    'subject' => $subject,
                    'exception' => $e->getMessage()
                ]);
            }

            return [
                'success' => false,
                'message' => $errorMessage . ': ' . ($errorDetails['message'] ?? $e->getMessage()),
                'error' => $errorDetails
            ];

        } catch (\Exception $e) {
            Log::error('Error general enviando email via Maileroo', [
                'to' => $to,
                'subject' => $subject,
                'exception' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error general: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar factura por email
     */
    public function enviarFactura(Factura $factura, string $email, string $asunto, string $mensaje): array
    {
        try {
            // Verificar que la factura esté emitida
            if (!$factura->isEmitida()) {
                return [
                    'success' => false,
                    'message' => 'La factura debe estar emitida antes de enviar por email'
                ];
            }

            // Generar PDF de la factura
            $pdfContent = $this->generarPDFFactura($factura);
            
            $htmlContent = view('emails.factura', [
                'factura' => $factura,
                'cliente' => $factura->cliente,
                'mensaje' => $mensaje
            ])->render();

            $attachments = [];
            
            if ($pdfContent) {
                $attachments[] = [
                    'name' => 'factura_' . $factura->getNumeroFormateado() . '.pdf',
                    'content' => base64_encode($pdfContent),
                    'type' => 'application/pdf'
                ];
            }

            Log::info('Iniciando envío de factura por email (HTTP API Maileroo)', [
                'factura_id' => $factura->id,
                'email_destino' => $email,
                'usuario' => Auth::user()->fullName ?? 'Sistema',
                'mensaje_incluido' => !empty($mensaje)
            ]);

            return $this->sendEmail($email, $asunto, $mensaje, $htmlContent, $attachments);

        } catch (\Exception $e) {
            Log::error('Error enviando factura por email via Maileroo HTTP API', [
                'factura_id' => $factura->id,
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error enviando factura: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar el estado de la conexión con Maileroo
     */
    public function testConnection()
    {
        try {
            // Probar enviando un email de test usando HTTP API
            $testResult = $this->sendEmail(
                config('mail.from.address'),
                'Test de Conexión Maileroo HTTP API',
                'Este es un test de conexión HTTP API con Maileroo',
                '<p>Este es un <strong>test de conexión HTTP API</strong> con Maileroo</p>'
            );

            return [
                'success' => $testResult['success'],
                'message' => $testResult['success']
                    ? 'Conexión HTTP API exitosa con Maileroo'
                    : 'Error en conexión HTTP API: ' . $testResult['message'],
                'data' => $testResult
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error conectando con Maileroo HTTP API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas (no disponible por HTTP API básico)
     */
    public function getStats()
    {
        return [
            'success' => true,
            'message' => 'Estadísticas no disponibles por HTTP API básico',
            'data' => [
                'method' => 'HTTP_API',
                'note' => 'Las estadísticas requieren acceso al panel de Maileroo'
            ]
        ];
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
}
