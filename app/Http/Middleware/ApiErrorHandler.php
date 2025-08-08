<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ApiErrorHandler
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Exception $exception) {
            return $this->handleException($request, $exception);
        }
    }

    /**
     * Manejar excepciones y devolver respuestas JSON estructuradas
     */
    protected function handleException(Request $request, \Exception $exception): JsonResponse
    {
        // Solo manejar requests a rutas API
        if (!$request->is('api/*')) {
            throw $exception;
        }

        $response = $this->getErrorResponse($exception);
        
        // Log del error (sin datos sensibles)
        $this->logApiError($request, $exception, $response['status']);

        return response()->json($response['data'], $response['status']);
    }

    /**
     * Obtener respuesta de error según el tipo de excepción
     */
    protected function getErrorResponse(\Exception $exception): array
    {
        switch (true) {
            case $exception instanceof ValidationException:
                return [
                    'status' => 422,
                    'data' => [
                        'success' => false,
                        'message' => 'Error de validación',
                        'error' => 'VALIDATION_ERROR',
                        'errors' => $exception->errors()
                    ]
                ];

            case $exception instanceof AuthenticationException:
                return [
                    'status' => 401,
                    'data' => [
                        'success' => false,
                        'message' => 'No autenticado',
                        'error' => 'UNAUTHENTICATED'
                    ]
                ];

            case $exception instanceof AuthorizationException:
                return [
                    'status' => 403,
                    'data' => [
                        'success' => false,
                        'message' => 'No autorizado',
                        'error' => 'UNAUTHORIZED'
                    ]
                ];

            case $exception instanceof ModelNotFoundException:
                return [
                    'status' => 404,
                    'data' => [
                        'success' => false,
                        'message' => 'Recurso no encontrado',
                        'error' => 'RESOURCE_NOT_FOUND'
                    ]
                ];

            case $exception instanceof NotFoundHttpException:
                return [
                    'status' => 404,
                    'data' => [
                        'success' => false,
                        'message' => 'Endpoint no encontrado',
                        'error' => 'ENDPOINT_NOT_FOUND'
                    ]
                ];

            case $exception instanceof MethodNotAllowedHttpException:
                return [
                    'status' => 405,
                    'data' => [
                        'success' => false,
                        'message' => 'Método HTTP no permitido',
                        'error' => 'METHOD_NOT_ALLOWED',
                        'data' => [
                            'allowed_methods' => $exception->getHeaders()['Allow'] ?? []
                        ]
                    ]
                ];

            case $exception instanceof TooManyRequestsHttpException:
                $retryAfter = $exception->getHeaders()['Retry-After'] ?? 60;
                return [
                    'status' => 429,
                    'data' => [
                        'success' => false,
                        'message' => 'Demasiadas solicitudes',
                        'error' => 'RATE_LIMIT_EXCEEDED',
                        'data' => [
                            'retry_after_seconds' => $retryAfter,
                            'retry_after_minutes' => ceil($retryAfter / 60)
                        ]
                    ]
                ];

            case $exception instanceof \Illuminate\Database\QueryException:
                return [
                    'status' => 500,
                    'data' => [
                        'success' => false,
                        'message' => 'Error en la base de datos',
                        'error' => 'DATABASE_ERROR'
                    ]
                ];

            default:
                // Error genérico del servidor
                return [
                    'status' => 500,
                    'data' => [
                        'success' => false,
                        'message' => 'Error interno del servidor',
                        'error' => 'INTERNAL_SERVER_ERROR',
                        ...(app()->environment(['local', 'testing']) ? [
                            'debug' => [
                                'message' => $exception->getMessage(),
                                'file' => $exception->getFile(),
                                'line' => $exception->getLine()
                            ]
                        ] : [])
                    ]
                ];
        }
    }

    /**
     * Log de errores de API (sin datos sensibles)
     */
    protected function logApiError(Request $request, \Exception $exception, int $status): void
    {
        $user = $request->user();
        
        $context = [
            'api_error' => true,
            'status' => $status,
            'method' => $request->getMethod(),
            'endpoint' => $request->getPathInfo(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $user ? $user->id : null,
            'exception_type' => get_class($exception),
            'message' => $exception->getMessage(),
        ];

        // Solo incluir stack trace en desarrollo
        if (app()->environment(['local', 'testing'])) {
            $context['file'] = $exception->getFile();
            $context['line'] = $exception->getLine();
        }

        // Log según la severidad
        if ($status >= 500) {
            \Log::error('API Server Error', $context);
        } elseif ($status >= 400) {
            \Log::warning('API Client Error', $context);
        } else {
            \Log::info('API Error', $context);
        }
    }
}