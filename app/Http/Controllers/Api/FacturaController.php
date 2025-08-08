<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Producto;
use App\Models\User;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FacturaController extends Controller
{
    /**
     * Listar facturas según el rol del usuario
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Factura::with(['detalles.producto', 'usuario', 'cliente']);

            // Filtrar según el rol
            if ($user->hasRole('Cliente')) {
                // Los clientes solo ven sus propias facturas
                $query->where('cliente_id', $user->id);
            } elseif ($user->hasRole('Ventas')) {
                // Ventas solo ve las facturas que creó
                $query->where('usuario_id', $user->id);
            } elseif (!$user->hasAnyRole(['Administrador', 'Secretario'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver facturas',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            // Filtros
            if ($request->filled('estado')) {
                if ($request->estado === 'eliminadas') {
                    $query->onlyTrashed();
                } else {
                    $query->where('estado', $request->estado);
                }
            }

            if ($request->filled('cliente_id') && $user->hasAnyRole(['Administrador', 'Secretario'])) {
                $query->where('cliente_id', $request->cliente_id);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            if ($request->filled('numero_factura')) {
                $query->where('numero_factura', 'like', '%' . $request->numero_factura . '%');
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            
            if (in_array($sortBy, ['created_at', 'numero_factura', 'total', 'estado'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Paginación
            $perPage = $request->input('per_page', 15);
            $facturas = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'facturas' => $facturas->map(function($factura) {
                        return [
                            'id' => $factura->id,
                            'numero_factura' => $factura->getNumeroFormateado(),
                            'fecha' => $factura->created_at->format('Y-m-d'),
                            'fecha_completa' => $factura->created_at->format('Y-m-d H:i:s'),
                            'cliente' => [
                                'id' => $factura->cliente->id,
                                'nombre' => $factura->cliente->name,
                                'email' => $factura->cliente->email
                            ],
                            'vendedor' => [
                                'id' => $factura->usuario->id,
                                'nombre' => $factura->usuario->name
                            ],
                            'subtotal' => number_format($factura->subtotal, 2),
                            'iva' => number_format($factura->iva, 2),
                            'total' => number_format($factura->total, 2),
                            'subtotal_raw' => $factura->subtotal,
                            'iva_raw' => $factura->iva,
                            'total_raw' => $factura->total,
                            'estado' => $factura->estado,
                            'productos_count' => $factura->detalles->count(),
                            'is_deleted' => $factura->deleted_at ? true : false,
                            'puede_pagar' => $factura->estado === 'pendiente' && !$factura->deleted_at
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $facturas->currentPage(),
                        'per_page' => $facturas->perPage(),
                        'total' => $facturas->total(),
                        'last_page' => $facturas->lastPage(),
                        'has_more_pages' => $facturas->hasMorePages()
                    ],
                    'resumen' => [
                        'total_facturas' => $facturas->total(),
                        'monto_total' => $facturas->getCollection()->sum('total_raw')
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al listar facturas API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de facturas',
                'error' => 'LIST_ERROR'
            ], 500);
        }
    }

    /**
     * Crear nueva factura (Solo Ventas y Administrador)
     */
    public function store(Request $request)
    {
        try {
            if (!$request->user()->hasAnyRole(['Administrador', 'Ventas'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para crear facturas',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $request->validate([
                'cliente_id' => 'required|exists:users,id',
                'productos' => 'required|array|min:1',
                'productos.*.id' => 'required|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'observacion' => 'nullable|string|max:500'
            ], [
                'cliente_id.required' => 'El cliente es requerido',
                'cliente_id.exists' => 'El cliente seleccionado no existe',
                'productos.required' => 'Debe incluir al menos un producto',
                'productos.*.id.required' => 'ID del producto es requerido',
                'productos.*.id.exists' => 'El producto no existe',
                'productos.*.cantidad.required' => 'La cantidad es requerida',
                'productos.*.cantidad.min' => 'La cantidad debe ser mayor a 0'
            ]);

            // Verificar que el cliente tenga rol de Cliente
            $cliente = User::find($request->cliente_id);
            if (!$cliente->hasRole('Cliente')) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario seleccionado no es un cliente válido',
                    'error' => 'INVALID_CLIENT'
                ], 422);
            }

            DB::beginTransaction();

            // Calcular totales y validar stock
            $subtotal = 0;
            $detallesFactura = [];

            foreach ($request->productos as $productoData) {
                $producto = Producto::find($productoData['id']);
                
                if (!$producto) {
                    throw ValidationException::withMessages([
                        'productos' => ["El producto con ID {$productoData['id']} no existe"]
                    ]);
                }

                if ($producto->stock < $productoData['cantidad']) {
                    throw ValidationException::withMessages([
                        'productos' => ["Stock insuficiente para {$producto->nombre}. Stock disponible: {$producto->stock}"]
                    ]);
                }

                $subtotalProducto = $producto->precio * $productoData['cantidad'];
                $subtotal += $subtotalProducto;

                $detallesFactura[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotalProducto,
                    'producto' => $producto
                ];
            }

            // Calcular IVA (12%)
            $iva = $subtotal * 0.12;
            $total = $subtotal + $iva;

            // Crear factura
            $factura = Factura::create([
                'cliente_id' => $request->cliente_id,
                'usuario_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'estado' => 'pendiente',
                'observacion' => $request->observacion
            ]);

            // Crear detalles y actualizar stock
            foreach ($detallesFactura as $detalle) {
                FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal']
                ]);

                // Actualizar stock
                $detalle['producto']->decrement('stock', $detalle['cantidad']);
            }

            // Registrar auditoría
            $this->registrarAuditoria('create', $factura, null, $factura->toArray(), 'Factura creada via API', $request->user()->id);

            DB::commit();

            // Cargar relaciones para la respuesta
            $factura->load(['detalles.producto', 'cliente', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Factura creada exitosamente',
                'data' => [
                    'factura' => [
                        'id' => $factura->id,
                        'numero_factura' => $factura->getNumeroFormateado(),
                        'fecha' => $factura->created_at->format('Y-m-d H:i:s'),
                        'cliente' => [
                            'id' => $factura->cliente->id,
                            'nombre' => $factura->cliente->name,
                            'email' => $factura->cliente->email
                        ],
                        'subtotal' => number_format($factura->subtotal, 2),
                        'iva' => number_format($factura->iva, 2),
                        'total' => number_format($factura->total, 2),
                        'estado' => $factura->estado,
                        'productos' => $factura->detalles->map(function($detalle) {
                            return [
                                'nombre' => $detalle->producto->nombre,
                                'cantidad' => $detalle->cantidad,
                                'precio_unitario' => number_format($detalle->precio_unitario, 2),
                                'subtotal' => number_format($detalle->subtotal, 2)
                            ];
                        }),
                        'observacion' => $factura->observacion
                    ]
                ]
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear factura API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la factura',
                'error' => 'CREATE_ERROR'
            ], 500);
        }
    }

    /**
     * Mostrar factura específica
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $query = Factura::withTrashed()->with(['detalles.producto', 'cliente', 'usuario', 'pagos']);

            // Aplicar filtros según rol
            if ($user->hasRole('Cliente')) {
                $query->where('cliente_id', $user->id);
            } elseif ($user->hasRole('Ventas')) {
                $query->where('usuario_id', $user->id);
            } elseif (!$user->hasAnyRole(['Administrador', 'Secretario'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver esta factura',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $factura = $query->find($id);

            if (!$factura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada o no tienes permisos para verla',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            // Calcular información de pagos
            $pagoAprobado = $factura->pagos()->where('estado', 'aprobado')->first();
            $totalPagos = $factura->pagos()->where('estado', 'aprobado')->sum('monto');
            $saldoPendiente = $factura->total - $totalPagos;

            return response()->json([
                'success' => true,
                'data' => [
                    'factura' => [
                        'id' => $factura->id,
                        'numero_factura' => $factura->getNumeroFormateado(),
                        'fecha' => $factura->created_at->format('Y-m-d'),
                        'fecha_completa' => $factura->created_at->format('Y-m-d H:i:s'),
                        'cliente' => [
                            'id' => $factura->cliente->id,
                            'nombre' => $factura->cliente->name,
                            'email' => $factura->cliente->email,
                            'telefono' => $factura->cliente->telefono,
                            'direccion' => $factura->cliente->direccion
                        ],
                        'vendedor' => [
                            'id' => $factura->usuario->id,
                            'nombre' => $factura->usuario->name,
                            'email' => $factura->usuario->email
                        ],
                        'subtotal' => number_format($factura->subtotal, 2),
                        'iva' => number_format($factura->iva, 2),
                        'total' => number_format($factura->total, 2),
                        'subtotal_raw' => $factura->subtotal,
                        'iva_raw' => $factura->iva,
                        'total_raw' => $factura->total,
                        'estado' => $factura->estado,
                        'observacion' => $factura->observacion,
                        'is_deleted' => $factura->deleted_at ? true : false,
                        'productos' => $factura->detalles->map(function($detalle) {
                            return [
                                'id' => $detalle->producto->id,
                                'nombre' => $detalle->producto->nombre,
                                'descripcion' => $detalle->producto->descripcion,
                                'cantidad' => $detalle->cantidad,
                                'precio_unitario' => number_format($detalle->precio_unitario, 2),
                                'precio_unitario_raw' => $detalle->precio_unitario,
                                'subtotal' => number_format($detalle->subtotal, 2),
                                'subtotal_raw' => $detalle->subtotal
                            ];
                        }),
                        'info_pagos' => [
                            'total_pagado' => number_format($totalPagos, 2),
                            'saldo_pendiente' => number_format($saldoPendiente, 2),
                            'tiene_pago_aprobado' => $pagoAprobado ? true : false,
                            'tipo_pago_aprobado' => $pagoAprobado ? $pagoAprobado->tipo_pago : null,
                            'fecha_pago_aprobado' => $pagoAprobado ? $pagoAprobado->created_at->format('Y-m-d H:i:s') : null,
                            'total_pagos_registrados' => $factura->pagos()->count(),
                            'puede_pagar' => $factura->estado === 'pendiente' && !$factura->deleted_at && $saldoPendiente > 0
                        ],
                        'created_at' => $factura->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $factura->updated_at->format('Y-m-d H:i:s')
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener factura API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la factura',
                'error' => 'SHOW_ERROR'
            ], 500);
        }
    }

    /**
     * Anular factura (cambiar estado a anulada)
     */
    public function cancel(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            if (!$user->hasAnyRole(['Administrador', 'Ventas'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para anular facturas',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $request->validate([
                'motivo' => 'required|string|max:255'
            ], [
                'motivo.required' => 'El motivo de anulación es requerido'
            ]);

            $factura = Factura::find($id);

            if (!$factura) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            // Verificar permisos específicos para la factura
            if ($user->hasRole('Ventas') && $factura->usuario_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes anular tus propias facturas',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            if ($factura->estado === 'anulada') {
                return response()->json([
                    'success' => false,
                    'message' => 'La factura ya está anulada',
                    'error' => 'ALREADY_CANCELED'
                ], 422);
            }

            if ($factura->estado === 'pagada') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede anular una factura que ya está pagada',
                    'error' => 'ALREADY_PAID'
                ], 422);
            }

            DB::beginTransaction();

            $old = $factura->toArray();

            // Cambiar estado a anulada
            $factura->estado = 'anulada';
            $factura->observacion = ($factura->observacion ? $factura->observacion . ' | ' : '') . 'ANULADA: ' . $request->motivo;
            $factura->save();

            // Restaurar stock de los productos
            foreach ($factura->detalles as $detalle) {
                $detalle->producto->increment('stock', $detalle->cantidad);
            }

            // Registrar auditoría
            $this->registrarAuditoria('cancel', $factura, $old, $factura->toArray(), 'Factura anulada via API: ' . $request->motivo, $user->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura anulada exitosamente',
                'data' => [
                    'factura_id' => $factura->id,
                    'numero_factura' => $factura->getNumeroFormateado(),
                    'estado' => $factura->estado,
                    'motivo_anulacion' => $request->motivo
                ]
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al anular factura API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al anular la factura',
                'error' => 'CANCEL_ERROR'
            ], 500);
        }
    }

    /**
     * Obtener facturas pendientes del cliente autenticado
     */
    public function pending(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->hasRole('Cliente')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los clientes pueden ver sus facturas pendientes',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $facturasPendientes = $user->facturasComoCliente()
                ->where('estado', 'pendiente')
                ->with(['detalles.producto', 'usuario'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'facturas_pendientes' => $facturasPendientes->map(function($factura) {
                        return [
                            'id' => $factura->id,
                            'numero_factura' => $factura->getNumeroFormateado(),
                            'fecha' => $factura->created_at->format('Y-m-d'),
                            'total' => number_format($factura->total, 2),
                            'total_raw' => $factura->total,
                            'estado' => $factura->estado,
                            'vendedor' => $factura->usuario->name ?? 'N/A',
                            'productos_count' => $factura->detalles->count(),
                            'puede_pagar' => true
                        ];
                    }),
                    'total_facturas_pendientes' => $facturasPendientes->count(),
                    'monto_total_pendiente' => $facturasPendientes->sum('total')
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener facturas pendientes API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las facturas pendientes',
                'error' => 'PENDING_ERROR'
            ], 500);
        }
    }

    /**
     * Registrar auditoría
     */
    private function registrarAuditoria($accion, $modelo, $old, $new, $descripcion, $userId)
    {
        try {
            Auditoria::create([
                'user_id' => $userId,
                'action' => $accion,
                'model_type' => get_class($modelo),
                'model_id' => $modelo->id,
                'old_values' => $old ? json_encode($old) : null,
                'new_values' => $new ? json_encode($new) : null,
                'description' => $descripcion,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar auditoría de factura API: ' . $e->getMessage());
        }
    }
}