<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\TurnoCaja;
use App\Models\MovimientoCaja;
use App\Models\Producto;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class CajaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of ventas.
     */
    public function index(Request $request)
    {
        // Solo Admin y Ventas pueden acceder
        $this->authorize('viewAny', Venta::class);
        
        $ventas = Venta::with(['usuario', 'turno', 'detalles.producto']);
        
        // Si no es admin, solo ver sus propias ventas
        if (!Auth::user()->hasRole('Administrador')) {
            $ventas = $ventas->where('usuario_id', Auth::id());
        }
        
        // Filtros de búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $ventas = $ventas->where(function($q) use ($buscar) {
                $q->where('numero_venta', 'like', "%$buscar%")
                  ->orWhere('cliente_nombre', 'like', "%$buscar%")
                  ->orWhere('cliente_identificacion', 'like', "%$buscar%");
            });
        }
        
        if ($request->filled('estado')) {
            if ($request->input('estado') === 'anulada') {
                $ventas = $ventas->onlyTrashed();
            } else {
                $ventas = $ventas->where('estado', $request->input('estado'));
            }
        }
        
        if ($request->filled('tipo_pago')) {
            $ventas = $ventas->where('tipo_pago', $request->input('tipo_pago'));
        }
        
        if ($request->filled('fecha_desde')) {
            $ventas = $ventas->whereDate('fecha_venta', '>=', $request->input('fecha_desde'));
        }
        
        if ($request->filled('fecha_hasta')) {
            $ventas = $ventas->whereDate('fecha_venta', '<=', $request->input('fecha_hasta'));
        }
        
        $ventas = $ventas->orderBy('fecha_venta', 'desc')
                        ->paginate($request->input('per_page', 15))
                        ->withQueryString();
        
        // Estadísticas del día
        $ventasHoy = Venta::whereDate('fecha_venta', today())
                          ->where('estado', 'completada');
        
        if (!Auth::user()->hasRole('Administrador')) {
            $ventasHoy = $ventasHoy->where('usuario_id', Auth::id());
        }
        
        $stats = [
            'total_ventas_hoy' => $ventasHoy->sum('total'),
            'cantidad_ventas_hoy' => $ventasHoy->count(),
            'efectivo_hoy' => $ventasHoy->sum('monto_efectivo'),
            'tarjeta_hoy' => $ventasHoy->sum('monto_tarjeta'),
            'transferencia_hoy' => $ventasHoy->sum('monto_transferencia'),
            'deuna_hoy' => $ventasHoy->sum('monto_deuna'),
        ];
        
        return view('caja.index', compact('ventas', 'stats'));
    }

    /**
     * Show the form for creating a new venta (POS interface).
     */
    public function create()
    {
        $this->authorize('create', Venta::class);
        
        // Verificar que el usuario tenga un turno abierto
        $turnoAbierto = TurnoCaja::turnoAbiertoParaUsuario(Auth::id());
        
        if (!$turnoAbierto) {
            return redirect()->route('caja.turnos.index')
                           ->with('warning', 'Debes abrir un turno de caja antes de realizar ventas.');
        }
        
        // Obtener productos con stock disponible
        $productos = Producto::where('stock', '>', 0)
                            ->where('estado', 'activo')
                            ->orderBy('nombre')
                            ->get();
        
        return view('caja.create', compact('productos', 'turnoAbierto'));
    }

    /**
     * Store a newly created venta in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Venta::class);
        
        // Validar request
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'tipo_pago' => 'required|in:efectivo,tarjeta,transferencia,deuna,mixto',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'monto_tarjeta' => 'nullable|numeric|min:0',
            'monto_transferencia' => 'nullable|numeric|min:0',
            'monto_deuna' => 'nullable|numeric|min:0',
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_identificacion' => 'nullable|string|max:20',
            'notas' => 'nullable|string|max:500',
        ]);
        
        // Verificar turno abierto
        $turnoAbierto = TurnoCaja::turnoAbiertoParaUsuario(Auth::id());
        
        if (!$turnoAbierto) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un turno abierto. Por favor abre un turno antes de realizar ventas.'
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            // Validar stock de productos
            $productosVenta = [];
            $subtotal = 0;
            
            foreach ($request->productos as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item['producto_id']);
                
                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception('Stock insuficiente para el producto: ' . $producto->nombre . ' - Stock disponible: ' . $producto->stock . ' - Cantidad solicitada: ' . $item['cantidad']);
                }
                
                $subtotalDetalle = $producto->precio * $item['cantidad'];
                $subtotal += $subtotalDetalle;
                
                $productosVenta[] = [
                    'producto' => $producto,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotalDetalle,
                ];
            }
            
            // Calcular totales
            $iva = calculate_iva($subtotal);
            $descuento = $request->input('descuento', 0);
            $total = $subtotal + $iva - $descuento;
            
            // Validar montos según tipo de pago
            $montoEfectivo = (float) $request->input('monto_efectivo', 0);
            $montoTarjeta = (float) $request->input('monto_tarjeta', 0);
            $montoTransferencia = (float) $request->input('monto_transferencia', 0);
            $montoDeuna = (float) $request->input('monto_deuna', 0);
            
            if ($request->tipo_pago === 'mixto') {
                $totalPagado = $montoEfectivo + $montoTarjeta + $montoTransferencia + $montoDeuna;
                if (abs($totalPagado - $total) > 0.01) {
                    throw new \Exception('La suma de los montos no coincide con el total de la venta. Total: $' . number_format($total, 2) . ' - Pagado: $' . number_format($totalPagado, 2));
                }
            } else {
                // Para pagos simples, asignar todo el monto al método seleccionado
                $montoEfectivo = $request->tipo_pago === 'efectivo' ? $total : 0;
                $montoTarjeta = $request->tipo_pago === 'tarjeta' ? $total : 0;
                $montoTransferencia = $request->tipo_pago === 'transferencia' ? $total : 0;
                $montoDeuna = $request->tipo_pago === 'deuna' ? $total : 0;
            }
            
            // Crear venta
            $venta = Venta::create([
                'numero_venta' => Venta::generarNumeroVenta(),
                'fecha_venta' => now(),
                'usuario_id' => Auth::id(),
                'tipo_pago' => $request->tipo_pago,
                'monto_efectivo' => $montoEfectivo,
                'monto_tarjeta' => $montoTarjeta,
                'monto_transferencia' => $montoTransferencia,
                'monto_deuna' => $montoDeuna,
                'subtotal' => $subtotal,
                'iva' => $iva,
                'descuento' => $descuento,
                'total' => $total,
                'cliente_nombre' => $request->input('cliente_nombre'),
                'cliente_identificacion' => $request->input('cliente_identificacion'),
                'notas' => $request->input('notas'),
                'estado' => 'completada',
                'turno_id' => $turnoAbierto->id,
            ]);
            
            // Crear detalles y actualizar stock
            foreach ($productosVenta as $item) {
                $producto = $item['producto'];
                
                // Crear detalle
                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                ]);
                
                // Actualizar stock
                $stockAnterior = $producto->stock;
                $producto->stock -= $item['cantidad'];
                $producto->save();
                
                Log::info("Stock actualizado para producto {$producto->nombre}: {$stockAnterior} -> {$producto->stock} (cantidad vendida: {$item['cantidad']})");
            }
            
            // Registrar auditoría
            $this->registrarAuditoria('create', $venta, null, $venta->toArray(), 'Venta creada en Caja - Turno #' . $turnoAbierto->id);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Venta ' . $venta->getNumeroFormateado() . ' registrada exitosamente por $' . number_format($venta->total, 2),
                'venta_id' => $venta->id,
                'numero_venta' => $venta->getNumeroFormateado(),
                'total' => $venta->total,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear venta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified venta.
     */
    public function show($id)
    {
        $venta = Venta::withTrashed()
                     ->with(['usuario', 'turno', 'detalles.producto'])
                     ->findOrFail($id);
        
        $this->authorize('view', $venta);
        
        return view('caja.show', compact('venta'));
    }

    /**
     * Anular una venta (soft delete).
     */
    public function anular(Request $request, $id)
    {
        $request->validate([
            'password' => 'required',
            'motivo_anulacion' => 'required|string|min:10|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            $venta = Venta::with('detalles')->findOrFail($id);
            $this->authorize('delete', $venta);
            
            // Verificar contraseña
            if (!Hash::check($request->password, Auth::user()->password)) {
                return redirect()->back()->with('error', 'Contraseña incorrecta.');
            }
            
            // Verificar que no haya pasado más de 24 horas
            if ($venta->fecha_venta->diffInHours(now()) > 24) {
                return redirect()->back()->with('error', 'No se puede anular una venta con más de 24 horas de antigüedad.');
            }
            
            $old = $venta->toArray();
            
            // Restaurar stock
            foreach ($venta->detalles as $detalle) {
                $producto = Producto::lockForUpdate()->findOrFail($detalle->producto_id);
                $stockAnterior = $producto->stock;
                $producto->stock += $detalle->cantidad;
                $producto->save();
                
                Log::info("Stock restaurado para producto {$producto->nombre}: {$stockAnterior} -> {$producto->stock} (cantidad devuelta: {$detalle->cantidad})");
            }
            
            // Soft delete de la venta
            $venta->estado = 'anulada';
            $venta->save();
            $venta->delete();
            
            $this->registrarAuditoria('delete', $venta, $old, null, 'Venta anulada - Motivo: ' . $request->motivo_anulacion);
            
            DB::commit();
            
            return redirect()->route('caja.index')
                           ->with('success', 'Venta ' . $venta->getNumeroFormateado() . ' anulada exitosamente. Stock restaurado.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al anular venta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al anular la venta: ' . $e->getMessage());
        }
    }

    /**
     * Imprimir ticket de venta.
     */
    public function ticket($id)
    {
        $venta = Venta::with(['usuario', 'detalles.producto'])->findOrFail($id);
        $this->authorize('view', $venta);
        
        return view('caja.ticket', compact('venta'));
    }

    /**
     * Dashboard POS (Punto de Venta).
     */
    public function pos()
    {
        $this->authorize('create', Venta::class);
        
        // Verificar turno abierto
        $turnoAbierto = TurnoCaja::turnoAbiertoParaUsuario(Auth::id());
        
        if (!$turnoAbierto) {
            return redirect()->route('caja.turnos.index')
                           ->with('warning', 'Debes abrir un turno de caja antes de acceder al punto de venta.');
        }
        
        // Obtener productos con stock
        $productos = Producto::where('stock', '>', 0)
                            ->where('estado', 'activo')
                            ->orderBy('nombre')
                            ->get();
        
        // Estadísticas del turno actual
        $stats = [
            'ventas_turno' => $turnoAbierto->ventas()->where('estado', 'completada')->count(),
            'total_turno' => $turnoAbierto->ventas()->where('estado', 'completada')->sum('total'),
            'efectivo_turno' => $turnoAbierto->ventas()->where('estado', 'completada')->sum('monto_efectivo'),
            'monto_inicial' => $turnoAbierto->monto_inicial,
        ];
        
        return view('caja.pos', compact('productos', 'turnoAbierto', 'stats'));
    }

    /**
     * Registrar auditoría.
     */
    private function registrarAuditoria($action, $model, $old = null, $new = null, $description = null)
    {
        Auditoria::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
