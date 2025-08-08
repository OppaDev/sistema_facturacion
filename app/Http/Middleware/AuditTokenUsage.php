<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

class AuditTokenUsage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Solo auditar si se autenticó con Sanctum
        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            $token = $user->currentAccessToken();

            if ($token) {
                try {
                    Auditoria::create([
                        'user_id' => $user->id,
                        'action' => 'api_access',
                        'model_type' => 'Laravel\Sanctum\PersonalAccessToken',
                        'model_id' => $token->id,
                        'old_values' => null,
                        'new_values' => json_encode([
                            'token_name' => $token->name,
                            'endpoint' => $request->getPathInfo(),
                            'method' => $request->getMethod(),
                            'status_code' => $response->getStatusCode()
                        ]),
                        'description' => "API accedida con token '{$token->name}' - {$request->getMethod()} {$request->getPathInfo()}",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->header('User-Agent')
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the request
                    \Log::error('Error registrando auditoría de token: ' . $e->getMessage());
                }
            }
        }

        return $response;
    }
}