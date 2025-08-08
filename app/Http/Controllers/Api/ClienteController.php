<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\UserController as BaseUserController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\Api\ClienteResource;
use Illuminate\Validation\ValidationException;

class ClienteController extends BaseUserController
{
    /**
     * Listar todos los clientes (versión API que usa la lógica del padre)
     */
    public function index(Request $request)
    {
        try {
            // Verificar permisos usando la lógica del controlador padre
            if (!$request->user()->hasAnyRole(['Administrador', 'Secretario'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para acceder a esta información',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            // Modificar el request para filtrar solo clientes
            $request->merge([
                'filtro' => $request->input('estado', 'activos'),
                'busqueda' => $request->input('buscar'),
                'cantidad' => $request->input('per_page', 15)
            ]);

            // Obtener usuarios usando la lógica del padre, pero filtrar solo clientes
            $query = User::role('Cliente')->with('roles');
            
            // Aplicar la misma lógica de filtros del padre
            $filtro = $request->input('filtro', 'activos');
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

            // Aplicar búsqueda usando la lógica del padre
            if ($request->filled('busqueda')) {
                $busqueda = $request->input('busqueda');
                $query->where(function($q) use ($busqueda) {
                    $q->where('name', 'like', "%$busqueda%")
                      ->orWhere('email', 'like', "%$busqueda%")
                      ->orWhere('estado', 'like', "%$busqueda%")
                      ->orWhereHas('roles', function($qr) use ($busqueda) {
                          $qr->where('name', 'like', "%$busqueda%")
                             ->orWhere('description', 'like', "%$busqueda%");
                      });
                });
            }

            $clientes = $query->orderBy('id', 'desc')->paginate($request->input('cantidad', 15));

            return response()->json([
                'success' => true,
                'data' => [
                    'clientes' => ClienteResource::collection($clientes->items()),
                    'pagination' => [
                        'current_page' => $clientes->currentPage(),
                        'per_page' => $clientes->perPage(),
                        'total' => $clientes->total(),
                        'last_page' => $clientes->lastPage(),
                        'has_more_pages' => $clientes->hasMorePages()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al listar clientes API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de clientes',
                'error' => 'LIST_ERROR'
            ], 500);
        }
    }

    /**
     * Crear nuevo cliente (versión API que usa la lógica del padre)
     */
    public function store(Request $request)
    {
        try {
            // Verificar permisos
            if (!$request->user()->hasAnyRole(['Administrador', 'Secretario'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para crear clientes',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            // Modificar el request para asegurar que se cree como cliente
            $request->merge([
                'estado' => 'activo',
                'roles' => ['Cliente']
            ]);

            // Usar el método store del controlador padre
            $result = parent::store($request);

            // Si el resultado es una redirección (vista), convertir a JSON
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                // Buscar el último usuario creado con rol Cliente
                $cliente = User::role('Cliente')->latest()->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente creado exitosamente',
                    'data' => new ClienteResource($cliente)
                ], 201);
            }
            
            return $result;
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente',
                'error' => 'CREATE_ERROR'
            ], 500);
        }
    }

    /**
     * Mostrar cliente específico (versión API)
     */
    public function show($id)
    {
        try {
            $user = request()->user();

            // Los clientes solo pueden ver su propia información
            if ($user->hasRole('Cliente') && $user->id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver esta información',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $cliente = User::withTrashed()->with('roles')->find($id);

            if (!$cliente || !$cliente->hasRole('Cliente')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ClienteResource($cliente->load(['facturasComoCliente', 'pagos']))
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el cliente',
                'error' => 'SHOW_ERROR'
            ], 500);
        }
    }

    /**
     * Actualizar cliente (versión API que usa la lógica del padre)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Los clientes solo pueden actualizar su propia información
            if ($user->hasRole('Cliente') && $user->id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para modificar esta información',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $cliente = User::find($id);
            if (!$cliente || !$cliente->hasRole('Cliente')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            // Mantener rol Cliente
            if (!$request->has('roles')) {
                $request->merge(['roles' => ['Cliente']]);
            }

            // Usar el método update del controlador padre
            $result = parent::update($request, $cliente);

            // Si el resultado es una redirección (vista), convertir a JSON
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                $cliente->refresh();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente actualizado exitosamente',
                    'data' => new ClienteResource($cliente)
                ], 200);
            }
            
            return $result;
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el cliente',
                'error' => 'UPDATE_ERROR'
            ], 500);
        }
    }

    /**
     * Eliminar cliente (versión API que usa la lógica del padre)
     */
    public function destroy(Request $request, $id)
    {
        try {
            if (!$request->user()->hasRole('Administrador')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los administradores pueden eliminar clientes',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $cliente = User::find($id);

            if (!$cliente || !$cliente->hasRole('Cliente')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            // Validar que no tenga facturas pendientes (lógica específica para clientes)
            $facturasPendientes = $cliente->facturasComoCliente()->where('estado', 'pendiente')->count();
            if ($facturasPendientes > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el cliente porque tiene facturas pendientes',
                    'error' => 'HAS_PENDING_INVOICES',
                    'data' => ['facturas_pendientes' => $facturasPendientes]
                ], 422);
            }

            // Simular request con motivo para usar el método del padre
            $request->merge([
                'admin_password' => $request->user()->password,
                'motivo' => 'Eliminación via API'
            ]);

            // Usar el método destroy del controlador padre
            $result = parent::destroy($request, $cliente);

            // Si el resultado es una redirección (vista), convertir a JSON
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente eliminado exitosamente'
                ], 200);
            }
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error al eliminar cliente API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el cliente',
                'error' => 'DELETE_ERROR'
            ], 500);
        }
    }

}