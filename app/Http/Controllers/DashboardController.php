<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Auditoria;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\TurnoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarios = User::all();
        $user = auth()->user();

        // ADMINISTRADOR: Dashboard refactorizado con módulo Caja/Ventas
        if ($user->hasRole('Administrador')) {
            
            // === MÉTRICAS PRINCIPALES ===
            
            // Usuarios y cajeros
            $cajeros = User::whereHas('roles', function($q) {
                $q->where('name', 'Ventas');
            })->where('estado', 'activo')->count();
            
            // Inventario
            $totalProductos = Producto::sum('stock');
            $productosSinStock = Producto::where('stock', 0)->count();
            $productosBajoStock = Producto::where('stock', '<', 10)
                                         ->with('categoria')
                                         ->orderBy('stock', 'asc')
                                         ->limit(5)
                                         ->get();
            
            // === VENTAS DEL MES ===
            $ventasMes = Venta::whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                              ->where('estado', 'completada')
                              ->count();
            
            $totalRecaudadoMes = Venta::whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->where('estado', 'completada')
                                      ->sum('total');
            
            $ticketPromedio = $ventasMes > 0 ? round($totalRecaudadoMes / $ventasMes, 2) : 0;
            
            // === VENTAS DE HOY ===
            $ventasHoy = Venta::whereDate('created_at', today())
                              ->where('estado', 'completada')
                              ->count();
            
            $recaudadoHoy = Venta::whereDate('created_at', today())
                                 ->where('estado', 'completada')
                                 ->sum('total');
            
            // === TOP PRODUCTOS VENDIDOS ===
            $topProductos = VentaDetalle::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
                                        ->whereHas('venta', function($q) {
                                            $q->whereMonth('created_at', now()->month)
                                              ->whereYear('created_at', now()->year)
                                              ->where('estado', 'completada');
                                        })
                                        ->with('producto.categoria')
                                        ->groupBy('producto_id')
                                        ->orderBy('total_vendido', 'desc')
                                        ->limit(5)
                                        ->get();
            
            $top3Productos = $topProductos->take(3);
            
            // === TURNOS DE CAJA ===
            $turnosAbiertos = TurnoCaja::where('estado', 'abierto')->count();
            
            $turnosHoy = TurnoCaja::whereDate('fecha_apertura', today())->count();
            
            // === VENTAS RECIENTES ===
            $ventasRecientes = Venta::with('usuario', 'turno')
                                    ->where('estado', 'completada')
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();
            
            // === GRÁFICA: VENTAS POR DÍA (mes actual vs mes anterior) ===
            $dias = range(1, now()->daysInMonth);
            $ventasEsteMes = [];
            $ventasMesPasado = [];
            
            foreach ($dias as $dia) {
                // Este mes
                $ventasEsteMes[] = Venta::whereDay('created_at', $dia)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('estado', 'completada')
                    ->sum('total');
                
                // Mes pasado
                $ventasMesPasado[] = Venta::whereDay('created_at', $dia)
                    ->whereMonth('created_at', now()->subMonth()->month)
                    ->whereYear('created_at', now()->subMonth()->year)
                    ->where('estado', 'completada')
                    ->sum('total');
            }
            
            // === PRODUCTOS POR CATEGORÍA ===
            $productosPorCategoria = Categoria::withCount('productos')
                                              ->withSum('productos', 'stock')
                                              ->get();
            
            // === MOVIMIENTOS DE INVENTARIO (salidas por ventas) ===
            $movimientosRecientes = VentaDetalle::with(['producto', 'venta.usuario'])
                                                ->whereHas('venta', function($q) {
                                                    $q->where('estado', 'completada');
                                                })
                                                ->orderBy('created_at', 'desc')
                                                ->limit(5)
                                                ->get();
            
            // === SALIDAS DE PRODUCTOS POR DÍA ===
            $entradasSalidasDias = [];
            foreach ($dias as $dia) {
                $salidas = VentaDetalle::whereHas('venta', function($q) use ($dia) {
                    $q->whereDay('created_at', $dia)
                      ->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year)
                      ->where('estado', 'completada');
                })->sum('cantidad');
                
                $entradasSalidasDias[] = [
                    'dia' => $dia,
                    'entradas' => 0, // No hay entradas automáticas en sistema POS
                    'salidas' => $salidas,
                ];
            }
            
            // === TOP CAJEROS DEL MES ===
            $topCajeros = Venta::select('usuario_id', DB::raw('COUNT(*) as total_ventas'), DB::raw('SUM(total) as total_recaudado'))
                               ->whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->where('estado', 'completada')
                               ->with('usuario')
                               ->groupBy('usuario_id')
                               ->orderBy('total_recaudado', 'desc')
                               ->limit(5)
                               ->get();
            
            // === AUDITORÍA ===
            $logsAuditoria = Auditoria::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
            
            return view('dashboard', compact(
                'usuarios',
                'cajeros',
                'totalProductos',
                'productosSinStock',
                'productosBajoStock',
                'ventasMes',
                'totalRecaudadoMes',
                'ticketPromedio',
                'ventasHoy',
                'recaudadoHoy',
                'topProductos',
                'top3Productos',
                'turnosAbiertos',
                'turnosHoy',
                'ventasRecientes',
                'ventasEsteMes',
                'ventasMesPasado',
                'dias',
                'productosPorCategoria',
                'movimientosRecientes',
                'entradasSalidasDias',
                'topCajeros',
                'logsAuditoria'
            ));
        }

        // CLIENTE: No necesitan dashboard en sistema POS
        if ($user->hasRole('Cliente')) {
            // En un sistema de punto de venta, los clientes no tienen acceso al sistema
            // Solo se registran sus datos para tickets/ventas
            abort(403, 'Los clientes no tienen acceso al dashboard. Este es un sistema interno de punto de venta.');
        }

        // SECRETARIO
        if ($user->hasRole('Secretario')) {
            $usuariosActivos = User::where('estado', 'activo')->count();
            $clientesActivos = User::whereHas('roles', function($q) {
                $q->where('name', 'Cliente');
            })->where('estado', 'activo')->count();
            return view('dashboard_secretario', compact('usuariosActivos', 'clientesActivos'));
        }

        // BODEGA
        if ($user->hasRole('Bodega')) {
            $totalProductos = \App\Models\Producto::sum('stock');
            $productosBajoStock = \App\Models\Producto::where('stock', '<', 10)->get();
            return view('dashboard_bodega', compact('totalProductos', 'productosBajoStock'));
        }

        // VENTAS
        if ($user->hasRole('Ventas')) {
            // Usar el módulo de Caja
            $ventasMes = \App\Models\Venta::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->where('estado', 'completada')
                                  ->count();
            
            $totalRecaudado = \App\Models\Venta::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->where('estado', 'completada')
                               ->sum('total');
            
            $ticketPromedio = $ventasMes > 0 ? $totalRecaudado / $ventasMes : 0;
            
            // Turno actual del usuario
            $turnoAbierto = \App\Models\TurnoCaja::where('usuario_id', $user->id)
                                    ->where('estado', 'abierto')
                                    ->first();
            
            // Ventas del turno actual
            $ventasTurno = 0;
            $totalTurno = 0;
            if ($turnoAbierto) {
                $ventasTurno = \App\Models\Venta::where('turno_id', $turnoAbierto->id)
                                      ->where('estado', 'completada')
                                      ->count();
                $totalTurno = \App\Models\Venta::where('turno_id', $turnoAbierto->id)
                                     ->where('estado', 'completada')
                                     ->sum('total');
            }
            
            // Top 5 productos más vendidos del mes
            $topProductos = \App\Models\VentaDetalle::select('producto_id', \DB::raw('SUM(cantidad) as total_vendido'))
                                     ->whereHas('venta', function($q) {
                                         $q->whereMonth('created_at', now()->month)
                                           ->whereYear('created_at', now()->year)
                                           ->where('estado', 'completada');
                                     })
                                     ->with('producto')
                                     ->groupBy('producto_id')
                                     ->orderBy('total_vendido', 'desc')
                                     ->limit(5)
                                     ->get();
            
            return view('dashboard_ventas', compact(
                'ventasMes', 
                'totalRecaudado', 
                'ticketPromedio', 
                'turnoAbierto',
                'ventasTurno',
                'totalTurno',
                'topProductos'
            ));
        }

        // Si no tiene rol válido
        abort(403, 'Rol no autorizado');
    }
}
