<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Auditoria;

class AuthController extends Controller
{
    /**
     * Login API - Generar token Sanctum
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'El email es requerido',
                'email.email' => 'El email debe tener un formato válido',
                'password.required' => 'La contraseña es requerida',
            ]);

            $user = User::where('email', $request->email)->first();

            // Validaciones de seguridad
            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciales incorrectas'],
                ]);
            }

            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciales incorrectas'],
                ]);
            }

            // Validar estado del usuario
            if ($user->estado !== 'activo') {
                throw ValidationException::withMessages([
                    'email' => ['Usuario inactivo. Contacte al administrador'],
                ]);
            }

            if ($user->deleted_at) {
                throw ValidationException::withMessages([
                    'email' => ['Usuario eliminado. Contacte al administrador'],
                ]);
            }

            if ($user->pending_delete_at) {
                throw ValidationException::withMessages([
                    'email' => ['Usuario en proceso de eliminación'],
                ]);
            }

            // Revocar tokens anteriores (opcional - por seguridad)
            $user->tokens()->delete();

            // Crear nuevo token
            $tokenName = 'API-' . $user->name . '-' . now()->format('Y-m-d H:i:s');
            $token = $user->createToken($tokenName)->plainTextToken;

            // Registrar auditoría
            $this->registrarAuditoria('api_login', $user, null, [
                'token_name' => $tokenName,
                'login_time' => now(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ], 'Login exitoso via API');

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames()
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => null // Los tokens Sanctum no expiran por defecto
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error en login API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => 'LOGIN_ERROR'
            ], 500);
        }
    }

    /**
     * Logout API - Revocar token actual
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $token = $user->currentAccessToken();

            // Registrar auditoría antes de eliminar el token
            $this->registrarAuditoria('api_logout', $user, null, [
                'token_name' => $token ? $token->name : 'unknown',
                'logout_time' => now(),
                'ip' => $request->ip()
            ], 'Logout exitoso via API');

            // Revocar token actual
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout exitoso. Token revocado.',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error en logout API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar logout',
                'error' => 'LOGOUT_ERROR'
            ], 500);
        }
    }

    /**
     * Refresh Token - Renovar token actual
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();
            $currentToken = $user->currentAccessToken();
            
            // Crear nuevo token
            $tokenName = 'API-REFRESH-' . $user->name . '-' . now()->format('Y-m-d H:i:s');
            $newToken = $user->createToken($tokenName)->plainTextToken;
            
            // Eliminar token anterior
            $currentToken->delete();

            // Registrar auditoría
            $this->registrarAuditoria('api_token_refresh', $user, null, [
                'old_token' => $currentToken->name,
                'new_token' => $tokenName,
                'refresh_time' => now()
            ], 'Token renovado via API');

            return response()->json([
                'success' => true,
                'message' => 'Token renovado exitosamente',
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al renovar token API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar token',
                'error' => 'REFRESH_ERROR'
            ], 500);
        }
    }

    /**
     * Información del usuario autenticado
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            $token = $user->currentAccessToken();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'direccion' => $user->direccion,
                        'estado' => $user->estado,
                        'roles' => $user->getRoleNames(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    ],
                    'token_info' => [
                        'name' => $token ? $token->name : null,
                        'created_at' => $token ? $token->created_at->format('Y-m-d H:i:s') : null,
                        'last_used_at' => $token ? ($token->last_used_at ? $token->last_used_at->format('Y-m-d H:i:s') : null) : null,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener perfil API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del usuario',
                'error' => 'PROFILE_ERROR'
            ], 500);
        }
    }

    /**
     * Registrar auditoría
     */
    private function registrarAuditoria($accion, $user, $old, $new, $descripcion)
    {
        try {
            Auditoria::create([
                'user_id' => $user->id,
                'action' => $accion,
                'model_type' => User::class,
                'model_id' => $user->id,
                'old_values' => $old ? json_encode($old) : null,
                'new_values' => $new ? json_encode($new) : null,
                'description' => $descripcion,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar auditoría de auth API: ' . $e->getMessage());
        }
    }
}