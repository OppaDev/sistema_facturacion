<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiAuditLogger
{
    /**
     * Lista de endpoints sensibles que requieren logging detallado
     */
    private const SENSITIVE_ENDPOINTS = [
        'api/login',
        'api/logout',
        'api/refresh-token',
        'api/facturas',
        'api/pagos',
        'api/clientes'
    ];

    /**
     * Campos que nunca deben ser loggeados
     */
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'token',
        'authorization',
        'firma_digital',
        'numero_transaccion',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Solo procesar rutas API
        if (!$request->is('api/*')) {
            return $next($request);
        }

        // Procesar la request
        $response = $next($request);
        
        // Calcular tiempo de respuesta
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Determinar si requiere logging detallado
        $requiresDetailedLogging = $this->requiresDetailedLogging($request, $response);
        
        // Crear contexto de auditoría
        $auditContext = $this->buildAuditContext($request, $response, $responseTime);
        
        // Log según el nivel requerido
        $this->logApiActivity($auditContext, $requiresDetailedLogging);
        
        return $response;
    }

    /**
     * Determinar si la request requiere logging detallado
     */
    private function requiresDetailedLogging(Request $request, Response $response): bool
    {
        // Siempre loggear errores
        if ($response->getStatusCode() >= 400) {
            return true;
        }
        
        // Loggear endpoints sensibles
        $path = $request->path();
        foreach (self::SENSITIVE_ENDPOINTS as $sensitiveEndpoint) {
            if (str_starts_with($path, $sensitiveEndpoint)) {
                return true;
            }
        }
        
        // Loggear operaciones de escritura
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Construir contexto de auditoría
     */
    private function buildAuditContext(Request $request, Response $response, float $responseTime): array
    {
        $user = $request->user();
        
        $context = [
            'api_audit' => true,
            'timestamp' => now()->toISOString(),
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => $responseTime,
            'ip_address' => $this->getClientIp($request),
            'user_agent' => $request->userAgent(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_roles' => $user?->getRoleNames()->toArray() ?? [],
        ];

        // Agregar parámetros de query (sin datos sensibles)
        if ($request->query()) {
            $context['query_params'] = $this->sanitizeData($request->query());
        }

        // Agregar datos del cuerpo de la request (sin datos sensibles)
        if ($request->isMethod(['POST', 'PUT', 'PATCH'])) {
            $context['request_data'] = $this->sanitizeData($request->all());
        }

        // Información adicional para responses de error
        if ($response->getStatusCode() >= 400) {
            $context['error'] = true;
            
            // Intentar decodificar el contenido de la respuesta de error
            $content = $response->getContent();
            if ($content && is_string($content)) {
                $decodedContent = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $context['error_response'] = [
                        'message' => $decodedContent['message'] ?? 'Unknown error',
                        'error_code' => $decodedContent['error'] ?? 'UNKNOWN_ERROR',
                    ];
                }
            }
        }

        // Rate limiting headers si existen
        $rateLimitHeaders = [
            'X-RateLimit-Limit' => $response->headers->get('X-RateLimit-Limit'),
            'X-RateLimit-Remaining' => $response->headers->get('X-RateLimit-Remaining'),
        ];
        
        $rateLimitHeaders = array_filter($rateLimitHeaders);
        if (!empty($rateLimitHeaders)) {
            $context['rate_limit'] = $rateLimitHeaders;
        }

        return $context;
    }

    /**
     * Sanitizar datos removiendo campos sensibles
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $lowercaseKey = strtolower($key);
            
            // Omitir campos sensibles
            if ($this->isSensitiveField($lowercaseKey)) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }
            
            // Procesar arrays recursivamente
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Verificar si un campo es sensible
     */
    private function isSensitiveField(string $field): bool
    {
        foreach (self::SENSITIVE_FIELDS as $sensitiveField) {
            if (str_contains($field, $sensitiveField)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtener IP del cliente de forma segura
     */
    private function getClientIp(Request $request): string
    {
        // Lista de headers a verificar en orden de prioridad
        $ipHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if ($ip && $this->isValidIp($ip)) {
                // Si es X-Forwarded-For, tomar la primera IP
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                return $ip;
            }
        }
        
        return $request->ip() ?? 'unknown';
    }

    /**
     * Validar que una IP sea válida
     */
    private function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Log de actividad de API
     */
    private function logApiActivity(array $context, bool $detailed): void
    {
        $logLevel = $this->determineLogLevel($context);
        $message = $this->buildLogMessage($context);
        
        // Reducir contexto para logging básico
        if (!$detailed) {
            $context = $this->reduceContextForBasicLogging($context);
        }
        
        // Loggear según el nivel determinado
        switch ($logLevel) {
            case 'error':
                Log::error($message, $context);
                break;
            case 'warning':
                Log::warning($message, $context);
                break;
            case 'info':
                Log::info($message, $context);
                break;
            case 'debug':
                Log::debug($message, $context);
                break;
        }
    }

    /**
     * Determinar nivel de log
     */
    private function determineLogLevel(array $context): string
    {
        $statusCode = $context['status_code'];
        
        if ($statusCode >= 500) {
            return 'error';
        } elseif ($statusCode >= 400) {
            return 'warning';
        } elseif (in_array($context['method'], ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return 'info';
        } else {
            return 'debug';
        }
    }

    /**
     * Construir mensaje de log
     */
    private function buildLogMessage(array $context): string
    {
        return sprintf(
            'API %s %s - %d (%sms) - User: %s',
            $context['method'],
            $context['endpoint'],
            $context['status_code'],
            $context['response_time_ms'],
            $context['user_email'] ?? 'anonymous'
        );
    }

    /**
     * Reducir contexto para logging básico
     */
    private function reduceContextForBasicLogging(array $context): array
    {
        return array_intersect_key($context, array_flip([
            'api_audit',
            'method',
            'endpoint',
            'status_code',
            'response_time_ms',
            'user_id',
            'user_email',
        ]));
    }
}