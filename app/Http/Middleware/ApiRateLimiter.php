<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limiterName = 'api'): Response
    {
        $key = $this->resolveRequestSignature($request, $limiterName);

        // Configuraciones específicas por tipo de endpoint
        $limits = $this->getRateLimit($limiterName, $request);

        if (RateLimiter::tooManyAttempts($key, $limits['maxAttempts'])) {
            $seconds = RateLimiter::availableIn($key);
            
            // Registrar intento de rate limiting
            \Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->getPathInfo(),
                'method' => $request->getMethod(),
                'limiter' => $limiterName,
                'key' => $key,
                'retry_after' => $seconds
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Demasiadas solicitudes. Intenta nuevamente más tarde.',
                'error' => 'RATE_LIMIT_EXCEEDED',
                'data' => [
                    'retry_after_seconds' => $seconds,
                    'retry_after_minutes' => ceil($seconds / 60)
                ]
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $limits['maxAttempts'],
                'X-RateLimit-Remaining' => 0
            ]);
        }

        // Incrementar contador
        RateLimiter::hit($key, $limits['decayMinutes'] * 60);

        $response = $next($request);

        // Agregar headers de rate limiting a la respuesta
        $remaining = $limits['maxAttempts'] - RateLimiter::attempts($key);
        $response->headers->add([
            'X-RateLimit-Limit' => $limits['maxAttempts'],
            'X-RateLimit-Remaining' => max(0, $remaining)
        ]);

        return $response;
    }

    /**
     * Resolver la firma de la solicitud para el rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $limiterName): string
    {
        $user = $request->user();
        
        // Para usuarios autenticados, usar el ID del usuario
        if ($user) {
            return 'api_' . $limiterName . '_user_' . $user->id;
        }

        // Para usuarios no autenticados, usar IP + User Agent
        return 'api_' . $limiterName . '_ip_' . sha1(
            $request->ip() . '|' . $request->userAgent() . '|' . $limiterName
        );
    }

    /**
     * Obtener límites de rate limiting según el tipo de endpoint
     */
    protected function getRateLimit(string $limiterName, Request $request): array
    {
        // Límites por defecto
        $defaultLimits = [
            'maxAttempts' => 60,      // 60 requests
            'decayMinutes' => 1       // por minuto
        ];

        // Límites específicos por tipo de endpoint
        $specificLimits = [
            'auth' => [
                'maxAttempts' => 5,   // 5 intentos de login
                'decayMinutes' => 1   // por minuto
            ],
            'sensitive' => [
                'maxAttempts' => 10,  // 10 requests para operaciones sensibles
                'decayMinutes' => 1   // por minuto
            ],
            'read' => [
                'maxAttempts' => 100, // 100 requests para operaciones de lectura
                'decayMinutes' => 1   // por minuto
            ],
            'write' => [
                'maxAttempts' => 30,  // 30 requests para operaciones de escritura
                'decayMinutes' => 1   // por minuto
            ]
        ];

        return $specificLimits[$limiterName] ?? $defaultLimits;
    }
}