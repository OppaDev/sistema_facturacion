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
        // Log estratégico: Acceso a formulario de creación
        Log::info('CREACION_USUARIO: Accediendo al formulario de creación', [
            'usuario_autenticado' => Auth::id(),
            'usuario_roles' => Auth::user()->roles->pluck('name')->toArray(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            $roles = Role::all();
            
            // Log estratégico: Roles disponibles
            Log::info('CREACION_USUARIO: Cargando roles disponibles', [
                'total_roles' => $roles->count(),
                'roles' => $roles->pluck('name')->toArray(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return view('users.create', compact('roles'));
            
        } catch (\Exception $e) {
            Log::error('CREACION_USUARIO: Error al cargar formulario de creación', [
                'error' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return redirect()->route('users.index')
                ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function store(Request $request)
    {
        // Log estratégico: Inicio del proceso de creación
        Log::info('CREACION_USUARIO: Iniciando proceso de creación de usuario', [
            'usuario_autenticado' => Auth::id(),
            'datos_recibidos' => $request->except(['password', 'password_confirmation']),
            'timestamp' => now()->toDateTimeString()
        ]);

        // Validación avanzada con reglas personalizadas
        try {
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

            // Log estratégico: Validación exitosa
            Log::info('CREACION_USUARIO: Validación exitosa', [
                'usuario_autenticado' => Auth::id(),
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log estratégico: Error de validación
            Log::warning('CREACION_USUARIO: Error de validación', [
                'usuario_autenticado' => Auth::id(),
                'errores' => $e->errors(),
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }

        try {
            // Verificar que el usuario autenticado tenga permisos usando roles
            $currentUser = Auth::user();
            $hasPermission = $currentUser->hasRole(['Administrador', 'Secretario']);
            
            // Log estratégico: Verificación de permisos
            Log::info('CREACION_USUARIO: Verificando permisos', [
                'usuario_autenticado' => Auth::id(),
                'usuario_roles' => $currentUser->roles->pluck('name')->toArray(),
                'tiene_permisos' => $hasPermission,
                'timestamp' => now()->toDateTimeString()
            ]);

            if (!$hasPermission) {
                Log::warning('CREACION_USUARIO: Sin permisos para crear usuarios', [
                    'usuario_autenticado' => Auth::id(),
                    'usuario_roles' => $currentUser->roles->pluck('name')->toArray(),
                    'timestamp' => now()->toDateTimeString()
                ]);
                return redirect()->back()->with('error', 'No tiene permisos para crear usuarios.');
            }

            // Verificar límite de usuarios si existe
            $totalUsers = User::count();
            Log::info('CREACION_USUARIO: Verificando límite de usuarios', [
                'total_usuarios' => $totalUsers,
                'limite' => 1000,
                'timestamp' => now()->toDateTimeString()
            ]);

            if ($totalUsers >= 1000) { // Ajustar según necesidades
                Log::warning('CREACION_USUARIO: Límite de usuarios alcanzado', [
                    'total_usuarios' => $totalUsers,
                    'timestamp' => now()->toDateTimeString()
                ]);
                return redirect()->back()->with('error', 'Se ha alcanzado el límite máximo de usuarios.');
            }

            // Log estratégico: Iniciando creación del usuario
            Log::info('CREACION_USUARIO: Iniciando creación en base de datos', [
                'email' => $request->email,
                'nombre' => $request->name,
                'estado' => $request->estado,
                'roles' => $request->roles,
                'timestamp' => now()->toDateTimeString()
            ]);

            // Crear el usuario
            $user = new User();
            $user->name = trim($request->name);
            $user->email = strtolower(trim($request->email));
            $user->password = Hash::make($request->password);
            $user->estado = $request->estado;
            $user->email_verified_at = null; // Requerir verificación
            $user->save();
            
            // Log estratégico: Usuario creado exitosamente
            Log::info('CREACION_USUARIO: Usuario creado en base de datos', [
                'usuario_id' => $user->id,
                'email' => $user->email,
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Asignar roles
            if ($request->roles) {
                Log::info('CREACION_USUARIO: Asignando roles', [
                    'usuario_id' => $user->id,
                    'roles' => $request->roles,
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                $user->syncRoles($request->roles);
                
                Log::info('CREACION_USUARIO: Roles asignados exitosamente', [
                    'usuario_id' => $user->id,
                    'roles_asignados' => $user->roles->pluck('name')->toArray(),
                    'timestamp' => now()->toDateTimeString()
                ]);
            }

            // Registrar en auditoría
            Log::info('CREACION_USUARIO: Proceso completado exitosamente', [
                'user_id' => $user->id,
                'created_by' => Auth::id(),
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'timestamp' => now()->toDateTimeString()
            ]);

            // Enviar notificación de bienvenida (opcional)
            //$user->notify(new WelcomeNotification($request->password));

            return redirect()->route('users.index')
                ->with('success', 'Usuario creado correctamente. Se ha enviado un correo de verificación.');

        } catch (\Exception $e) {
            Log::error('CREACION_USUARIO: Error crítico en el proceso', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['password', 'password_confirmation']),
                'timestamp' => now()->toDateTimeString()
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear el usuario. Por favor, intente nuevamente.')
                ->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    public function edit(User $user)
    {
        // Log estratégico: Acceso a formulario de edición
        Log::info('ACTUALIZACION_USUARIO: Accediendo al formulario de edición', [
            'usuario_autenticado' => Auth::id(),
            'usuario_a_editar' => $user->id,
            'email_a_editar' => $user->email,
            'usuario_roles' => Auth::user()->roles->pluck('name')->toArray(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            // Verificar permisos de edición
            $currentUser = Auth::user();
            $canEdit = $currentUser->hasRole(['Administrador', 'Secretario']) || $currentUser->id === $user->id;
            
            Log::info('ACTUALIZACION_USUARIO: Verificando permisos de edición', [
                'usuario_autenticado' => Auth::id(),
                'puede_editar' => $canEdit,
                'es_propio_perfil' => $currentUser->id === $user->id,
                'timestamp' => now()->toDateTimeString()
            ]);

            if (!$canEdit) {
                Log::warning('ACTUALIZACION_USUARIO: Sin permisos para editar usuario', [
                    'usuario_autenticado' => Auth::id(),
                    'usuario_a_editar' => $user->id,
                    'timestamp' => now()->toDateTimeString()
                ]);
                return redirect()->route('users.index')->with('error', 'No tiene permisos para editar este usuario.');
            }

            $roles = Role::all();
            
            // Log estratégico: Roles disponibles y datos del usuario
            Log::info('ACTUALIZACION_USUARIO: Cargando datos para edición', [
                'usuario_a_editar' => $user->id,
                'roles_actuales' => $user->roles->pluck('name')->toArray(),
                'estado_actual' => $user->estado,
                'total_roles_disponibles' => $roles->count(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return view('users.edit', compact('user', 'roles'));
            
        } catch (\Exception $e) {
            Log::error('ACTUALIZACION_USUARIO: Error al cargar formulario de edición', [
                'error' => $e->getMessage(),
                'usuario_a_editar' => $user->id,
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return redirect()->route('users.index')
                ->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    public function update(Request $request, User $user)
    {
        // Log estratégico: Inicio del proceso de actualización
        Log::info('ACTUALIZACION_USUARIO: Iniciando proceso de actualización', [
            'usuario_autenticado' => Auth::id(),
            'usuario_a_actualizar' => $user->id,
            'email_original' => $user->email,
            'datos_recibidos' => $request->except(['password', 'password_confirmation']),
            'timestamp' => now()->toDateTimeString()
        ]);

        // Validación de permisos
        $currentUser = Auth::user();
        $canEdit = $currentUser->hasRole(['Administrador', 'Secretario']) || $currentUser->id === $user->id;

        if (!$canEdit) {
            Log::warning('ACTUALIZACION_USUARIO: Sin permisos para actualizar usuario', [
                'usuario_autenticado' => Auth::id(),
                'usuario_a_actualizar' => $user->id,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->route('users.index')->with('error', 'No tiene permisos para actualizar este usuario.');
        }

        // Validaciones mejoradas
        $rules = [
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
                'unique:users,email,' . $user->id,
                'max:255'
            ],
            'estado' => [
                'required',
                'in:activo,inactivo'
            ],
        ];

        // Solo los admin/secretarios pueden cambiar roles
        if ($currentUser->hasRole(['Administrador', 'Secretario'])) {
            $rules['roles'] = [
                'required',
                'array',
                'min:1',
                'max:3'
            ];
            $rules['roles.*'] = [
                'exists:roles,name',
                'string'
            ];
        }

        // Validación de contraseña solo si se proporciona
        if ($request->filled('password')) {
            $rules['password'] = [
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ];
            $rules['password_confirmation'] = ['required', 'string'];
        }

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'roles.required' => 'Debe seleccionar al menos un rol.',
            'roles.array' => 'Los roles deben ser una lista.',
            'roles.min' => 'Debe seleccionar al menos un rol.',
            'roles.max' => 'No puede asignar más de 3 roles.',
            'roles.*.exists' => 'Uno de los roles seleccionados no existe.',
        ];

        try {
            $request->validate($rules, $messages);

            // Log estratégico: Validación exitosa
            Log::info('ACTUALIZACION_USUARIO: Validación exitosa', [
                'usuario_autenticado' => Auth::id(),
                'usuario_a_actualizar' => $user->id,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log estratégico: Error de validación
            Log::warning('ACTUALIZACION_USUARIO: Error de validación', [
                'usuario_autenticado' => Auth::id(),
                'usuario_a_actualizar' => $user->id,
                'errores' => $e->errors(),
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }

        try {
            // Guardar datos originales para auditoría
            $datosOriginales = [
                'name' => $user->name,
                'email' => $user->email,
                'estado' => $user->estado,
                'roles' => $user->roles->pluck('name')->toArray()
            ];

            // Log estratégico: Iniciando actualización
            Log::info('ACTUALIZACION_USUARIO: Iniciando actualización en base de datos', [
                'usuario_a_actualizar' => $user->id,
                'datos_originales' => $datosOriginales,
                'datos_nuevos' => $request->except(['password', 'password_confirmation']),
                'timestamp' => now()->toDateTimeString()
            ]);

            // Actualizar datos básicos
            $user->name = trim($request->name);
            $user->email = strtolower(trim($request->email));
            $user->estado = $request->estado;
            
            // Actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                Log::info('ACTUALIZACION_USUARIO: Actualizando contraseña', [
                    'usuario_a_actualizar' => $user->id,
                    'timestamp' => now()->toDateTimeString()
                ]);
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Log estratégico: Usuario actualizado
            Log::info('ACTUALIZACION_USUARIO: Datos básicos actualizados', [
                'usuario_id' => $user->id,
                'email_actualizado' => $user->email,
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Actualizar roles solo si el usuario tiene permisos
            if ($currentUser->hasRole(['Administrador', 'Secretario']) && $request->has('roles')) {
                $rolesOriginales = $user->roles->pluck('name')->toArray();
                
                Log::info('ACTUALIZACION_USUARIO: Actualizando roles', [
                    'usuario_id' => $user->id,
                    'roles_originales' => $rolesOriginales,
                    'roles_nuevos' => $request->roles,
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                $user->syncRoles($request->roles);
                
                Log::info('ACTUALIZACION_USUARIO: Roles actualizados exitosamente', [
                    'usuario_id' => $user->id,
                    'roles_finales' => $user->roles->pluck('name')->toArray(),
                    'timestamp' => now()->toDateTimeString()
                ]);
            }
            
            // Sincronizar estado con Cliente si es cliente
            if ($user->hasRole('Cliente')) {
                $user->load('cliente');
                if ($user->cliente) {
                    Log::info('ACTUALIZACION_USUARIO: Sincronizando estado con cliente', [
                        'usuario_id' => $user->id,
                        'cliente_id' => $user->cliente->id,
                        'estado' => $user->estado,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    
                    $user->cliente->estado = $user->estado;
                    $user->cliente->save();
                }
            }

            // Log estratégico: Proceso completado
            Log::info('ACTUALIZACION_USUARIO: Proceso completado exitosamente', [
                'usuario_id' => $user->id,
                'actualizado_por' => Auth::id(),
                'email_final' => $user->email,
                'roles_finales' => $user->roles->pluck('name')->toArray(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');

        } catch (\Exception $e) {
            Log::error('ACTUALIZACION_USUARIO: Error crítico en el proceso', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'usuario_a_actualizar' => $user->id,
                'data' => $request->except(['password', 'password_confirmation']),
                'timestamp' => now()->toDateTimeString()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar el usuario. Por favor, intente nuevamente.')
                ->withInput($request->except(['password', 'password_confirmation']));
        }
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
            
            // Registrar en auditoría completa
            \App\Models\Auditoria::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'model_type' => 'Laravel\Sanctum\PersonalAccessToken',
                'model_id' => $personalAccessToken->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'token_name' => $request->token_name,
                    'entidad_tipo' => $request->entidad_tipo,
                    'entidad_id' => $entidad->id,
                    'entidad_nombre' => $entidadNombre
                ]),
                'description' => "Token API '{$request->token_name}' creado para {$request->entidad_tipo}: {$entidadNombre}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
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
            $user = User::find($userId);
            
            // Registrar en auditoría completa ANTES de eliminar
            \App\Models\Auditoria::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'model_type' => 'Laravel\Sanctum\PersonalAccessToken',
                'model_id' => $token->id,
                'old_values' => json_encode([
                    'token_name' => $tokenName,
                    'tokenable_id' => $userId,
                    'tokenable_name' => $user ? $user->name : 'Usuario no encontrado'
                ]),
                'new_values' => null,
                'description' => "Token API '{$tokenName}' eliminado del usuario: " . ($user ? $user->name : 'Usuario no encontrado'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
            $token->delete();

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
