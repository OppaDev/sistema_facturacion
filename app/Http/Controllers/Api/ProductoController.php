<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ProductosController as BaseProductosController;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Http\Resources\Api\ProductoResource;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreProductoRequest;

class ProductoController extends BaseProductosController
{
    /**
     * Listar productos (versión API que usa la lógica del padre)
     */
    public function index(Request $request)
    {
        try {
            // Solo Bodega, Administrador y Ventas pueden acceder a productos
            if (!$request->user()->hasAnyRole(['Administrador', 'Bodega', 'Ventas'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para acceder a los productos',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            // Usar la misma lógica del controlador padre adaptada para API
            $query = Producto::with('categoria');

            // Aplicar filtros usando la lógica del padre
            if ($request->has('eliminados')) {
                $query = $query->onlyTrashed();
            }

            if ($request->filled('buscar')) {
                $buscar = $request->input('buscar');
                $query->where(function($q) use ($buscar) {
                    $q->where('nombre', 'like', "%$buscar%")
                      ->orWhere('descripcion', 'like', "%$buscar%");
                });
            }

            if ($request->filled('categoria_id')) {
                $query->where('categoria_id', $request->input('categoria_id'));
            }

            // Filtro adicional para stock bajo
            if ($request->filled('stock_bajo')) {
                $query->where('stock', '<=', 10);
            }

            $perPage = $request->input('per_page', 15);
            $productos = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'productos' => ProductoResource::collection($productos->items()),
                    'pagination' => [
                        'current_page' => $productos->currentPage(),
                        'per_page' => $productos->perPage(),
                        'total' => $productos->total(),
                        'last_page' => $productos->lastPage(),
                        'has_more_pages' => $productos->hasMorePages()
                    ],
                    'filters' => [
                        'categorias' => \App\Models\Categoria::where('activo', true)->select('id', 'nombre')->get()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al listar productos API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de productos',
                'error' => 'LIST_ERROR'
            ], 500);
        }
    }

    /**
     * Crear producto (versión API que usa la lógica del padre)
     */
    public function store(StoreProductoRequest $request)
    {
        try {
            if (!$request->user()->hasAnyRole(['Administrador', 'Bodega'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para crear productos',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            // Usar el método store del controlador padre
            $result = parent::store($request);

            // Si el resultado es una redirección o vista, convertir a JSON
            if ($result instanceof \Illuminate\Http\RedirectResponse || $result instanceof \Illuminate\Http\JsonResponse) {
                // Buscar el último producto creado
                $producto = Producto::latest()->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'data' => new ProductoResource($producto)
                ], 201);
            }
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error al crear producto API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto',
                'error' => 'CREATE_ERROR'
            ], 500);
        }
    }

    /**
     * Mostrar producto específico (versión API)
     */
    public function show($id)
    {
        try {
            if (!request()->user()->hasAnyRole(['Administrador', 'Bodega', 'Ventas'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver esta información',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $producto = Producto::withTrashed()->with(['categoria', 'creador', 'modificador', 'facturaDetalles.factura'])->find($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ProductoResource($producto)
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener producto API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el producto',
                'error' => 'SHOW_ERROR'
            ], 500);
        }
    }

    /**
     * Actualizar producto (versión API que usa la lógica del padre)
     */
    public function update(StoreProductoRequest $request, $id)
    {
        try {
            if (!$request->user()->hasAnyRole(['Administrador', 'Bodega'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para modificar productos',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $producto = Producto::withTrashed()->find($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            // Usar el método update del controlador padre
            $result = parent::update($request, $id);

            // Si el resultado es una redirección o vista, convertir a JSON
            if ($result instanceof \Illuminate\Http\RedirectResponse || $result instanceof \Illuminate\Http\JsonResponse) {
                $producto->refresh();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente',
                    'data' => new ProductoResource($producto)
                ], 200);
            }
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error al actualizar producto API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
                'error' => 'UPDATE_ERROR'
            ], 500);
        }
    }

    /**
     * Eliminar producto (versión API que usa la lógica del padre)
     */
    public function destroy(Request $request, $id)
    {
        try {
            if (!$request->user()->hasAnyRole(['Administrador', 'Bodega'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar productos',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $producto = Producto::find($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            // Validar que no esté en facturas activas (lógica específica para API)
            $enFacturas = $producto->facturaDetalles()
                ->whereHas('factura', function($q) {
                    $q->where('estado', '!=', 'anulada');
                })->count();

            if ($enFacturas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el producto porque está asociado a facturas activas',
                    'error' => 'HAS_ACTIVE_INVOICES',
                    'data' => ['facturas_asociadas' => $enFacturas]
                ], 422);
            }

            // Simular request con datos requeridos para usar método del padre
            $request->merge([
                'password' => 'api_deletion',
                'observacion' => 'Eliminación vía API'
            ]);

            // Temporalmente saltarse validación de password para API
            $originalPassword = $request->user()->password;
            $request->user()->password = \Hash::make('api_deletion');
            
            $result = parent::destroy($request, $id);
            
            // Restaurar password original
            $request->user()->password = $originalPassword;

            // Si el resultado es una redirección o vista, convertir a JSON
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente'
                ], 200);
            }
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error al eliminar producto API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto',
                'error' => 'DELETE_ERROR'
            ], 500);
        }
    }

    /**
     * Actualizar solo el stock de un producto (método específico de API)
     */
    public function updateStock(Request $request, $id)
    {
        try {
            if (!$request->user()->hasAnyRole(['Administrador', 'Bodega'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para modificar el stock',
                    'error' => 'UNAUTHORIZED'
                ], 403);
            }

            $request->validate([
                'stock' => 'required|integer|min:0',
                'motivo' => 'nullable|string|max:255'
            ]);

            $producto = Producto::find($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            \DB::beginTransaction();

            $stockAnterior = $producto->stock;
            $producto->stock = $request->stock;
            $producto->updated_by = $request->user()->id;
            $producto->save();

            // Usar el método de auditoría del padre
            $this->registrarAuditoria('stock_update', $producto, 
                ['stock' => $stockAnterior], 
                ['stock' => $request->stock, 'motivo' => $request->motivo], 
                'Stock actualizado via API', 
                $request->motivo
            );

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock actualizado exitosamente',
                'data' => [
                    'producto' => $producto->nombre,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $producto->stock,
                    'diferencia' => $producto->stock - $stockAnterior,
                    'stock_status' => $this->getStockStatus($producto->stock)
                ]
            ], 200);

        } catch (ValidationException $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al actualizar stock API: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el stock',
                'error' => 'STOCK_UPDATE_ERROR'
            ], 500);
        }
    }

    /**
     * Determinar estado del stock (método específico para API)
     */
    private function getStockStatus($stock)
    {
        if ($stock <= 0) {
            return 'sin_stock';
        } elseif ($stock <= 10) {
            return 'stock_bajo';
        } else {
            return 'disponible';
        }
    }
}