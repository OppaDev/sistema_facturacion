<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\PagoController as BasePagoController;
use Illuminate\Http\Request;
use App\Models\Pago;
use App\Http\Resources\Api\PagoResource;
use App\Http\Requests\StorePagoRequest;

class PagoController extends BasePagoController
{
    /**
     * Listar pagos (versión API que usa la lógica del padre)
     */
    public function index(Request $request)
    {
        try {
            // Verificar autorización usando la policy del controlador padre
            $this->authorize('viewAny', Pago::class);
            
            // Usar la misma lógica del controlador padre
            $query = Pago::with(['factura.cliente', 'pagadoPor', 'validadoPor'])
                        ->orderBy('created_at', 'desc');

            // Aplicar filtros de la versión web
            $filtroEstado = $request->input('estado', 'pendiente');
            if ($filtroEstado !== 'todos') {
                $query->where('estado', $filtroEstado);
            }

            if ($request->filled('buscar')) {
                $buscar = $request->input('buscar');
                $query->where(function($q) use ($buscar) {
                    $q->whereHas('factura.cliente', function($subQ) use ($buscar) {
                        $subQ->where('name', 'like', "%{$buscar}%")
                             ->orWhere('email', 'like', "%{$buscar}%");
                    })
                    ->orWhere('numero_transaccion', 'like', "%{$buscar}%")
                    ->orWhere('id', 'like', "%{$buscar}%");
                });
            }

            if ($request->filled('tipo_pago')) {
                $query->where('tipo_pago', $request->input('tipo_pago'));
            }

            // Paginación
            $pagos = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => [
                    'pagos' => PagoResource::collection($pagos->items()),
                    'pagination' => [
                        'current_page' => $pagos->currentPage(),
                        'per_page' => $pagos->perPage(),
                        'total' => $pagos->total(),
                        'last_page' => $pagos->lastPage(),
                        'has_more_pages' => $pagos->hasMorePages(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en Api\PagoController@index: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de pagos',
                'error' => 'LIST_ERROR'
            ], 500);
        }
    }

    /**
     * Mostrar pago específico (versión API) - Manteniendo compatibilidad
     */
    public function show(Pago $pago)
    {
        try {
            $this->authorize('view', $pago);
            
            $pago->load(['factura.cliente', 'pagadoPor', 'validadoPor']);
            
            return response()->json([
                'success' => true,
                'data' => new PagoResource($pago)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en Api\PagoController@show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el pago',
                'error' => 'SHOW_ERROR'
            ], 500);
        }
    }

    /**
     * Crear pago (versión API que usa la lógica del padre)
     */
    public function store(StorePagoRequest $request)
    {
        try {
            // Reutilizar la validación y lógica del controlador padre
            $result = parent::store($request);
            
            // Si el resultado es una vista (redirect), convertir a JSON
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago creado exitosamente'
                ], 201);
            }
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error en Api\PagoController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el pago',
                'error' => 'CREATE_ERROR'
            ], 500);
        }
    }

    /**
     * Aprobar pago (versión API)
     */
    public function approve(Request $request, $id)
    {
        try {
            $this->authorize('update', Pago::class);
            
            $pago = Pago::findOrFail($id);
            
            // Usar el método del controlador padre
            $result = $this->aprobar($request, $pago);
            
            return response()->json([
                'success' => true,
                'message' => 'Pago aprobado exitosamente',
                'data' => new PagoResource($pago->fresh(['factura.cliente', 'pagadoPor', 'validadoPor']))
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en Api\PagoController@approve: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar el pago',
                'error' => 'APPROVE_ERROR'
            ], 500);
        }
    }

    /**
     * Rechazar pago (versión API)
     */
    public function reject(Request $request, $id)
    {
        try {
            $this->authorize('update', Pago::class);
            
            $pago = Pago::findOrFail($id);
            
            // Usar el método del controlador padre
            $result = $this->rechazar($request, $pago);
            
            return response()->json([
                'success' => true,
                'message' => 'Pago rechazado exitosamente',
                'data' => new PagoResource($pago->fresh(['factura.cliente', 'pagadoPor', 'validadoPor']))
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en Api\PagoController@reject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar el pago',
                'error' => 'REJECT_ERROR'
            ], 500);
        }
    }
}