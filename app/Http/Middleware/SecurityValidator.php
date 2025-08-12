<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\HasDataSanitization;

class SecurityValidator
{
    use HasDataSanitization;

    /**
     * Headers peligrosos que pueden indicar ataques
     */
    private const DANGEROUS_HEADERS = [
        'x-forwarded-host',
        'x-forwarded-server',
        'x-cluster-client-ip',
        'x-original-url',
        'x-rewrite-url',
    ];

    /**
     * User agents sospechosos
     */
    private const SUSPICIOUS_USER_AGENTS = [
        'sqlmap',
        'nikto',
        'nessus',
        'burp',
        'nmap',
        'dirb',
        'dirbuster',
        'hydra',
        'havij',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo procesar rutas API
        if (!$request->is('api/*')) {
            return $next($request);
        }

        // Validar headers de seguridad
        if ($this->hasSecurityViolationInHeaders($request)) {
            $this->logSecurityViolation($request, 'suspicious_headers');
            return response()->json([
                'success' => false,
                'message' => 'Request no válido',
                'error' => 'INVALID_REQUEST'
            ], 400);
        }

        // Validar User Agent sospechoso
        if ($this->hasSuspiciousUserAgent($request)) {
            $this->logSecurityViolation($request, 'suspicious_user_agent');
            return response()->json([
                'success' => false,
                'message' => 'Request no válido',
                'error' => 'INVALID_REQUEST'
            ], 400);
        }

        // Validar tamaño de request
        if ($this->isRequestTooLarge($request)) {
            $this->logSecurityViolation($request, 'request_too_large');
            return response()->json([
                'success' => false,
                'message' => 'Request demasiado grande',
                'error' => 'REQUEST_TOO_LARGE'
            ], 413);
        }

        // Validar parámetros por posibles ataques
        if ($this->hasInjectionAttemptInParameters($request)) {
            $this->logSecurityViolation($request, 'injection_attempt');
            return response()->json([
                'success' => false,
                'message' => 'Parámetros no válidos',
                'error' => 'INVALID_PARAMETERS'
            ], 400);
        }

        // Validar rate limiting básico por IP
        if ($this->isRateLimitExceeded($request)) {
            $this->logSecurityViolation($request, 'rate_limit_exceeded');
            return response()->json([
                'success' => false,
                'message' => 'Demasiadas requests',
                'error' => 'RATE_LIMIT_EXCEEDED'
            ], 429);
        }

        return $next($request);
    }

    /**
     * Verificar headers sospechosos
     */
    private function hasSecurityViolationInHeaders(Request $request): bool
    {
        foreach (self::DANGEROUS_HEADERS as $header) {
            if ($request->headers->has($header)) {
                return true;
            }
        }

        // Verificar host header manipulation
        $host = $request->header('host');
        if ($host && !$this->isValidHost($host)) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el host es válido
     */
    private function isValidHost(string $host): bool
    {
        // Lista de hosts permitidos (configurable desde .env)
        $allowedHosts = config('security.allowed_hosts', ['localhost', '127.0.0.1']);

        // Remover puerto si existe
        $hostWithoutPort = explode(':', $host)[0];

        return in_array($hostWithoutPort, $allowedHosts) ||
               in_array($host, $allowedHosts);
    }

    /**
     * Verificar User Agent sospechoso
     */
    private function hasSuspiciousUserAgent(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        foreach (self::SUSPICIOUS_USER_AGENTS as $suspicious) {
            if (str_contains($userAgent, $suspicious)) {
                return true;
            }
        }

        // User Agent vacío o demasiado corto
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return true;
        }

        return false;
    }

    /**
     * Verificar tamaño de request
     */
    private function isRequestTooLarge(Request $request): bool
    {
        // Límite máximo en bytes (configurable)
        $maxSize = config('security.api_max_request_size', 1048576); // 1MB por defecto

        $content = $request->getContent();
        return strlen($content) > $maxSize;
    }

    /**
     * Verificar intentos de injection en parámetros
     */
    private function hasInjectionAttemptInParameters(Request $request): bool
    {
        // Verificar query parameters
        foreach ($request->query() as $key => $value) {
            if (is_string($value) && $this->detectInjectionAttempt($value)) {
                return true;
            }
            if (is_string($key) && $this->detectInjectionAttempt($key)) {
                return true;
            }
        }

        // Verificar body parameters (solo para ciertos métodos)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $input = $request->all();
            if ($this->hasInjectionInArray($input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar injection en array recursivamente
     */
    private function hasInjectionInArray(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && $this->detectInjectionAttempt($key)) {
                return true;
            }

            if (is_string($value) && $this->detectInjectionAttempt($value)) {
                return true;
            }

            if (is_array($value) && $this->hasInjectionInArray($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar rate limiting básico
     */
    private function isRateLimitExceeded(Request $request): bool
    {
        $ip = $request->ip();
        $cacheKey = "security_rate_limit:{$ip}";

        // Límite: 300 requests por minuto por IP
        $limit = config('security.rate_limit', 300);
        $decay = config('security.rate_limit_decay', 60); // segundos

        $current = cache($cacheKey, 0);

        if ($current >= $limit) {
            return true;
        }

        cache()->put($cacheKey, $current + 1, $decay);

        return false;
    }

    /**
     * Log violación de seguridad
     */
    private function logSecurityViolation(Request $request, string $type): void
    {
        \Log::warning('Security violation detected', [
            'type' => $type,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'query_params' => $request->query(),
            'headers' => $request->headers->all(),
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ]);

        // En caso de violaciones graves, podríamos bloquear la IP
        if (in_array($type, ['injection_attempt', 'suspicious_user_agent'])) {
            $this->considerIpBlocking($request->ip());
        }
    }

    /**
     * Considerar bloqueo de IP tras múltiples violaciones
     */
    private function considerIpBlocking(string $ip): void
    {
        $cacheKey = "security_violations:{$ip}";
        $violations = cache($cacheKey, 0);
        $violations++;

        // Bloquear IP tras 10 violaciones en 1 hora
        if ($violations >= 10) {
            cache()->put("blocked_ip:{$ip}", true, 3600); // 1 hora de bloqueo

            \Log::critical('IP address blocked due to multiple security violations', [
                'ip' => $ip,
                'violations_count' => $violations,
                'blocked_until' => now()->addHour()->toISOString(),
            ]);
        }

        cache()->put($cacheKey, $violations, 3600);
    }
}
