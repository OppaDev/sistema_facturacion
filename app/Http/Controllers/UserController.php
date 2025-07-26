<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filtro = $request->input('filtro', 'activos');
        $busqueda = $request->input('busqueda');
        $cantidad = $request->input('cantidad', 10);
        $query = User::query();

        // Filtro por estado
        switch ($filtro) {
            case 'inactivos':
                $query->where('estado', 'inactivo')->whereNull('deleted_at')->whereNull('pending_delete_at');
                break;
            case 'pendientes':
                $query->whereNotNull('pending_delete_at')->whereNull('deleted_at');
                break;
            case 'eliminados':
                $query->onlyTrashed();
                break;
            default:
                $query->where('estado', 'activo')->whereNull('deleted_at')->whereNull('pending_delete_at');
        }

        // Búsqueda robusta
        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('name', 'like', "%$busqueda%")
                  ->orWhere('email', 'like', "%$busqueda%")
                  ->orWhere('estado', 'like', "%$busqueda%")
                  ->orWhereHas('roles', function($qr) use ($busqueda) {
                      $qr->where('name', 'like', "%$busqueda%")
                         ->orWhere('description', 'like', "%$busqueda%") ;
                  });
            });
        }

        $users = $query->orderBy('id', 'desc')->with('roles')->paginate($cantidad)->appends($request->except('page'));
        $roles = Role::all();
        
        // Solo cargar reportes si estamos en eliminados
        $reportes = null;
        if ($filtro == 'eliminados') {
            // Aquí puedes agregar la lógica para cargar reportes si es necesario
            // $reportes = Auditoria::where('tabla', 'users')->get();
        }
        
        return view('users.index', compact('users', 'filtro', 'roles', 'reportes'));
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // Validación avanzada con reglas personalizadas
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/'
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'max:255'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'password_confirmation' => [
                'required',
                'string'
            ],
            'estado' => [
                'required',
                'in:activo,inactivo'
            ],
            'roles' => [
                'required',
                'array',
                'min:1',
                'max:3'
            ],
            'roles.*' => [
                'exists:roles,name',
                'string'
            ],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
            'password_confirmation.required' => 'Debe confirmar la contraseña.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'roles.required' => 'Un rol es obligatorio.',
            'roles.array' => 'Los roles deben ser una lista.',
            'roles.min' => 'Debe seleccionar al menos un rol.',
            'roles.max' => 'No puede asignar más de 3 roles.',
            'roles.*.exists' => 'Uno de los roles seleccionados no existe.',
            'roles.*.string' => 'Los roles deben ser texto.',
        ]);

        try {
            // Verificar que el usuario autenticado tenga permisos
            if (!auth()->user()->can('create', User::class)) {
                return redirect()->back()->with('error', 'No tiene permisos para crear usuarios.');
            }

            // Verificar límite de usuarios si existe
            $totalUsers = User::count();
            if ($totalUsers >= 1000) { // Ajustar según necesidades
                return redirect()->back()->with('error', 'Se ha alcanzado el límite máximo de usuarios.');
            }

            // Crear el usuario
            $user = new User();
            $user->name = trim($request->name);
            $user->email = strtolower(trim($request->email));
            $user->password = Hash::make($request->password);
            $user->estado = $request->estado;
            $user->email_verified_at = null; // Requerir verificación
            $user->save();
            
            // Asignar roles
            if ($request->roles) {
                $user->syncRoles($request->roles);
            }

            // Registrar en auditoría
            Log::info('Usuario creado', [
                'user_id' => $user->id,
                'created_by' => Auth::id(),
                'email' => $user->email,
                'roles' => $request->roles
            ]);

            // Enviar notificación de bienvenida (opcional)
            // $user->notify(new WelcomeNotification($request->password));

            return redirect()->route('users.index')
                ->with('success', 'Usuario creado correctamente. Se ha enviado un correo de verificación.');

        } catch (\Exception $e) {
            Log::error('Error al crear usuario', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password', 'password_confirmation'])
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear el usuario. Por favor, intente nuevamente.')
                ->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'estado' => 'required|in:activo,inactivo',
            'roles' => 'required|array|size:1',
            'roles.*' => 'exists:roles,name',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'roles.required' => 'Debe seleccionar al menos un rol.',
            'roles.array' => 'Los roles deben ser una lista.',
            'roles.min' => 'Debe seleccionar al menos un rol.',
            'roles.*.exists' => 'Uno de los roles seleccionados no existe.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->estado = $request->estado;
        $user->save();
        
        if ($request->roles) {
            $user->syncRoles($request->roles);
        }
        
        // Sincronizar estado con Cliente si es cliente
        if ($user->hasRole('cliente')) {
            $user->load('cliente');
            if ($user->cliente) {
                $user->cliente->estado = $user->estado;
                $user->cliente->save();
            }
        }
        
        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        $request->validate([
            'admin_password' => 'required',
            'motivo' => 'required|string|min:5|max:500',
        ], [
            'admin_password.required' => 'La contraseña de administrador es obligatoria.',
            'motivo.required' => 'Debe ingresar un motivo.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max' => 'El motivo no puede tener más de 500 caracteres.',
        ]);

        /** @var User $authUser */
        $authUser = Auth::user();
        if (!Hash::check($request->admin_password, $authUser->password)) {
            return back()->withErrors(['admin_password_incorrecta' => 'La contraseña es incorrecta.'])->withInput();
        }

        // Validación: No permitir eliminar usuario de ventas si tiene facturas emitidas
        if ($user->hasRole('Ventas')) {
            $facturasEmitidas = \App\Models\Factura::where('usuario_id', $user->id)->count();
            if ($facturasEmitidas > 0) {
                return back()->with('error', 'No se puede eliminar este usuario porque tiene facturas emitidas a su nombre. Debe reasignar o anular esas facturas antes de eliminarlo.');
            }
        }

        // Registrar en auditoría
        if (class_exists('App\\Models\\Auditoria')) {
            \App\Models\Auditoria::create([
                'user_id' => \Auth::id(),
                'action' => 'delete',
                'model_type' => User::class,
                'model_id' => $user->id,
                'description' => 'El usuario ' . $user->name . ' fue eliminado.',
                'observacion' => $request->motivo,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        }

        $user->delete();
        if ($user->hasRole('cliente')) {
            $user->load('cliente');
            if ($user->cliente) {
                $user->cliente->delete();
            }
        }
        if (\Auth::id() === $user->id) {
            \Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with('error', 'Su cuenta ha sido eliminada. Contacte soporte si es un error.');
        }
        return redirect()->route('users.index')->with('success', 'Usuario eliminado temporalmente.');
    }

    public function restore(Request $request, $id)
    {
        $request->validate([
            'admin_password' => 'required',
            'motivo' => 'required|string|min:5|max:500',
        ], [
            'admin_password.required' => 'La contraseña de administrador es obligatoria.',
            'motivo.required' => 'Debe ingresar un motivo.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max' => 'El motivo no puede tener más de 500 caracteres.',
        ]);

        if (!\Hash::check($request->admin_password, \Auth::user()->password)) {
            return back()->withErrors(['admin_password_incorrecta' => 'La contraseña es incorrecta.'])->withInput();
        }

        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        if ($user->hasRole('cliente') && $user->cliente) {
            $user->cliente->restore();
        }
        // Auditoría
        if (class_exists('App\\Models\\Auditoria')) {
            \App\Models\Auditoria::create([
                'user_id' => \Auth::id(),
                'action' => 'restore',
                'model_type' => User::class,
                'model_id' => $user->id,
                'description' => 'El usuario ' . $user->name . ' fue restaurado.',
                'observacion' => $request->motivo,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        }
        return redirect()->route('users.index', ['filtro' => 'eliminados'])->with('success', 'Usuario restaurado correctamente.');
    }

    public function forceDelete(Request $request, $id)
    {
        $request->validate([
            'admin_password' => 'required',
            'motivo' => 'required|string|min:5|max:500',
        ], [
            'admin_password.required' => 'La contraseña de administrador es obligatoria.',
            'motivo.required' => 'Debe ingresar un motivo.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max' => 'El motivo no puede tener más de 500 caracteres.',
        ]);

        if (!\Hash::check($request->admin_password, \Auth::user()->password)) {
            return back()->withErrors(['admin_password_incorrecta' => 'La contraseña es incorrecta.'])->withInput();
        }

        $user = User::onlyTrashed()->findOrFail($id);
        if ($user->hasRole('cliente') && $user->cliente) {
            $user->cliente->forceDelete();
        }
        // Auditoría
        if (class_exists('App\\Models\\Auditoria')) {
            \App\Models\Auditoria::create([
                'user_id' => \Auth::id(),
                'action' => 'forceDelete',
                'model_type' => User::class,
                'model_id' => $user->id,
                'description' => 'El usuario ' . $user->name . ' fue eliminado permanentemente.',
                'observacion' => $request->motivo,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        }
        $user->forceDelete();
        return redirect()->route('users.index', ['filtro' => 'eliminados'])->with('success', 'Usuario eliminado definitivamente.');
    }

    public function toggleEstado(User $user)
    {
        $user->estado = $user->estado === 'activo' ? 'inactivo' : 'activo';
        $user->save();
        // Sincronizar estado con Cliente si es cliente
        if ($user->hasRole('cliente') && $user->cliente) {
            $user->cliente->estado = $user->estado;
            $user->cliente->save();
        }
        // Si el usuario está logueado y se desactiva, cerrar sesión
        if ($user->estado === 'inactivo' && Auth::id() === $user->id) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect('/login')->with('error', 'Su cuenta ha sido suspendida.');
        }
        return redirect()->route('users.index')->with('success', 'Estado del usuario actualizado.');
    }

    public function activarUsuario(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required',
            'observacion' => 'required|string|max:500',
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'observacion.required' => 'Debe ingresar una observación.',
            'observacion.max' => 'La observación no puede tener más de 500 caracteres.',
        ]);

        // Validar contraseña del admin autenticado
        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'La contraseña es incorrecta.'])->withInput();
        }

        $user->estado = 'activo';
        $user->observacion = $request->observacion;
        $user->save();

        // Sincronizar estado con Cliente si es cliente
        if ($user->hasRole('cliente') && $user->cliente) {
            $user->cliente->estado = 'activo';
            $user->cliente->save();
        }

        return redirect()->route('users.index', ['filtro' => 'inactivos'])->with('success', 'Usuario activado correctamente.');
    }

    public function desactivarUsuario(Request $request, User $user)
    {
        $request->validate([
            'admin_password' => 'required',
            'motivo' => 'required|string|min:5|max:500',
        ], [
            'admin_password.required' => 'La contraseña de administrador es obligatoria.',
            'motivo.required' => 'Debe ingresar un motivo.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max' => 'El motivo no puede tener más de 500 caracteres.',
        ]);

        if (!\Hash::check($request->admin_password, \Auth::user()->password)) {
            return back()->withErrors(['admin_password_incorrecta' => 'La contraseña es incorrecta.'])->withInput();
        }

        $user->estado = 'inactivo';
        $user->motivo_suspension = $request->motivo;
        $user->save();
        if ($user->hasRole('cliente') && $user->cliente) {
            $user->cliente->estado = 'inactivo';
            $user->cliente->save();
        }
        // Auditoría
        if (class_exists('App\\Models\\Auditoria')) {
            \App\Models\Auditoria::create([
                'user_id' => \Auth::id(),
                'action' => 'inactivar',
                'model_type' => User::class,
                'model_id' => $user->id,
                'description' => 'El usuario ' . $user->name . ' fue desactivado.',
                'observacion' => $request->motivo,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        }
        if (\Auth::id() === $user->id) {
            \Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with([
                'suspendida' => true,
                'motivo' => $request->motivo
            ]);
        }
        return redirect()->route('users.index')->with('success', 'Usuario desactivado correctamente.');
    }

    public function solicitarBorradoCuenta(Request $request)
    {
        $user = Auth::user();
        $user->pending_delete_at = now();
        $user->save();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('error', 'Has solicitado eliminar tu cuenta. Se eliminará definitivamente en 3 días. Puedes recuperarla iniciando sesión antes de ese plazo.');
    }

    public function cancelarBorradoCuenta()
    {
        $user = Auth::user();
        $user->pending_delete_at = null;
        $user->save();
        return redirect()->route('dashboard')->with('success', 'Eliminación de cuenta cancelada.');
    }

    // Mostrar vista de gestión de tokens
    public function indexTokens()
    {
        // Obtener usuarios activos con sus tokens
        $usuarios = User::with(['tokens' => function($query) {
            $query->select('id', 'tokenable_id', 'tokenable_type', 'name', 'plaintext_token', 'last_used_at', 'created_at', 'abilities');
        }, 'roles'])->where('estado', 'activo')->get();
        
        // Obtener clientes activos con sus tokens (usuarios con rol Cliente)
        $clientes = User::with(['tokens' => function($query) {
            $query->select('id', 'tokenable_id', 'tokenable_type', 'name', 'plaintext_token', 'last_used_at', 'created_at', 'abilities');
        }, 'roles'])->whereHas('roles', function($query) {
            $query->where('name', 'Cliente');
        })->where('estado', 'activo')->get();
        
        // Función para desencriptar tokens
        $desencriptarTokens = function($entidad) {
            foreach ($entidad->tokens as $token) {
                // Debug: Log el estado del token
                Log::info('Token debug', [
                    'token_id' => $token->id,
                    'token_name' => $token->name,
                    'tokenable_type' => $token->tokenable_type,
                    'has_plaintext_token' => !empty($token->plaintext_token),
                    'plaintext_token_length' => $token->plaintext_token ? strlen($token->plaintext_token) : 0
                ]);
                
                if ($token->plaintext_token) {
                    try {
                        $decrypted = Crypt::decryptString($token->plaintext_token);
                        $token->decrypted_token = $decrypted;
                        Log::info('Token desencriptado exitosamente', [
                            'token_id' => $token->id,
                            'decrypted_length' => strlen($decrypted)
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al desencriptar token', [
                            'token_id' => $token->id,
                            'error' => $e->getMessage()
                        ]);
                        $token->decrypted_token = 'Error al desencriptar: ' . $e->getMessage();
                    }
                } else {
                    $token->decrypted_token = 'Token no encriptado (campo vacío)';
                    Log::warning('Token sin plaintext_token', ['token_id' => $token->id]);
                }
            }
        };
        
        // Desencriptar tokens de usuarios
        foreach ($usuarios as $usuario) {
            $desencriptarTokens($usuario);
        }
        
        // Desencriptar tokens de clientes
        foreach ($clientes as $cliente) {
            $desencriptarTokens($cliente);
        }
        
        return view('token.index', compact('usuarios', 'clientes'));
    }

    // Crear token de acceso para API
    public function crearTokenAcceso(Request $request)
    {
        $request->validate([
            'entidad_tipo' => 'required|in:usuario,cliente',
            'entidad_id' => 'required|integer',
            'token_name' => 'required|string|max:255|min:3',
        ], [
            'entidad_tipo.required' => 'Debe seleccionar el tipo de entidad.',
            'entidad_tipo.in' => 'El tipo de entidad debe ser usuario o cliente.',
            'entidad_id.required' => 'Debe seleccionar una entidad.',
            'entidad_id.integer' => 'El ID de la entidad debe ser un número válido.',
            'token_name.required' => 'El nombre del token es obligatorio.',
            'token_name.min' => 'El nombre del token debe tener al menos 3 caracteres.',
            'token_name.max' => 'El nombre del token no puede tener más de 255 caracteres.',
        ]);

        try {
            // Validar existencia de la entidad según el tipo
            if ($request->entidad_tipo === 'usuario') {
                $entidad = User::where('estado', 'activo')->findOrFail($request->entidad_id);
                $entidadNombre = $entidad->name;
            } else {
                // Para cliente, también usar User pero con rol Cliente
                $entidad = User::whereHas('roles', function($query) {
                    $query->where('name', 'Cliente');
                })->where('estado', 'activo')->findOrFail($request->entidad_id);
                $entidadNombre = $entidad->name;
            }
            
            $token = $entidad->createToken($request->token_name);
            
            // Obtener el registro del token recién creado y encriptar el plaintext
            $personalAccessToken = $token->accessToken;
            $personalAccessToken->plaintext_token = Crypt::encryptString($token->plainTextToken);
            $personalAccessToken->save();
            
            // Registrar en auditoría
            Log::info('Token API creado', [
                'entidad_tipo' => $request->entidad_tipo,
                'entidad_id' => $entidad->id,
                'entidad_nombre' => $entidadNombre,
                'token_name' => $request->token_name,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('tokens.index')->with('token_generado', $token->plainTextToken);
        } catch (\Exception $e) {
            Log::error('Error al crear token', [
                'error' => $e->getMessage(),
                'entidad_tipo' => $request->entidad_tipo,
                'entidad_id' => $request->entidad_id,
                'token_name' => $request->token_name,
            ]);

            return redirect()->back()->with('error', 'Error al crear el token. Intente nuevamente.');
        }
    }

    // Eliminar token de acceso
    public function eliminarToken(Request $request, $tokenId)
    {
        $request->validate([
            'admin_password' => 'required',
        ], [
            'admin_password.required' => 'La contraseña de administrador es obligatoria.',
        ]);

        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors(['admin_password' => 'La contraseña es incorrecta.']);
        }

        try {
            $token = PersonalAccessToken::findOrFail($tokenId);
            $tokenName = $token->name;
            $userId = $token->tokenable_id;
            
            $token->delete();

            // Registrar en auditoría
            Log::info('Token API eliminado', [
                'token_name' => $tokenName,
                'user_id' => $userId,
                'deleted_by' => Auth::id(),
            ]);

            return redirect()->route('tokens.index')->with('success', 'Token eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el token.');
        }
    }
}
