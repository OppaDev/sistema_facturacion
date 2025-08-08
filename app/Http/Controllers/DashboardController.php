<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Factura;
use App\Models\Categoria;
use App\Models\FacturaDetalle;
use App\Models\Auditoria;
use App\Models\User;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarios = User::all();
        $user = auth()->user();

        // ADMINISTRADOR: dashboard original
        if ($user->hasRole('Administrador')) {
            // --- Lógica original ---
            $clientesActivos = User::whereHas('roles', function($q) {
                $q->where('name', 'Cliente');
            })->where('estado', 'activo')->count();
            $totalProductos = \App\Models\Producto::sum('stock');
            $facturasMes = \App\Models\Factura::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count();
            $ventasMes = \App\Models\Factura::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->sum('total');
            $productosBajoStock = \App\Models\Producto::where('stock', '<', 10)
                                         ->with('categoria')
                                         ->orderBy('stock', 'asc')
                                         ->limit(5)
                                         ->get();
            $topProductos = \App\Models\FacturaDetalle::select('producto_id', \DB::raw('SUM(cantidad) as total_vendido'))
                                         ->with('producto.categoria')
                                         ->groupBy('producto_id')
                                         ->orderBy('total_vendido', 'desc')
                                         ->limit(5)
                                         ->get();
            $dias = range(1, now()->daysInMonth);
            $ventasEsteMes = [];
            $ventasMesPasado = [];
            $mesActual = now()->month;
            $anioActual = now()->year;
            $mesPasado = now()->subMonth()->month;
            $anioPasado = now()->subMonth()->year;
            foreach ($dias as $dia) {
                $ventasEsteMes[] = \App\Models\Factura::whereDay('created_at', $dia)
                    ->whereMonth('created_at', $mesActual)
                    ->whereYear('created_at', $anioActual)
                    ->sum('total');
                $ventasMesPasado[] = \App\Models\Factura::whereDay('created_at', $dia)
                    ->whereMonth('created_at', $mesPasado)
                    ->whereYear('created_at', $anioPasado)
                    ->sum('total');
            }
            $productosPorCategoria = \App\Models\Categoria::withCount('productos')
                                             ->withSum('productos', 'stock')
                                             ->get();
            $facturasRecientes = \App\Models\Factura::with('cliente')
                                       ->orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get();
            $productosSinStock = \App\Models\Producto::where('stock', 0)->count();
            $facturasPendientes = \App\Models\Factura::where('estado', 'pendiente')->count();
            $tasaConversion = $clientesActivos > 0 ? round(($facturasMes / $clientesActivos) * 100, 1) : 0;
            $top3Productos = $topProductos->take(3);
            $movimientosRecientes = \App\Models\FacturaDetalle::with(['producto', 'factura'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $entradasSalidasDias = [];
            foreach ($dias as $dia) {
                $salidas = \App\Models\FacturaDetalle::whereDay('created_at', $dia)
                    ->whereMonth('created_at', $mesActual)
                    ->whereYear('created_at', $anioActual)
                    ->sum('cantidad');
                $entradas = 0;
                $entradasSalidasDias[] = [
                    'dia' => $dia,
                    'entradas' => $entradas,
                    'salidas' => $salidas,
                ];
            }
            $ultimosClientes = User::whereHas('roles', function($q) {
                $q->where('name', 'Cliente');
            })->orderBy('created_at', 'desc')->limit(5)->get();
            $logsAuditoria = \App\Models\Auditoria::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
            return view('dashboard', compact('usuarios',
                'clientesActivos',
                'totalProductos',
                'facturasMes',
                'ventasMes',
                'productosBajoStock',
                'topProductos',
                'productosPorCategoria',
                'facturasRecientes',
                'productosSinStock',
                'facturasPendientes',
                'tasaConversion',
                'ventasEsteMes',
                'ventasMesPasado',
                'dias',
                'top3Productos',
                'movimientosRecientes',
                'entradasSalidasDias',
                'ultimosClientes',
                'logsAuditoria'
            ));
        }

                // CLIENTE: dashboard específico
        if ($user->hasRole('Cliente')) {
            // Ahora el usuario ES el cliente directamente
            $comprasCliente = Factura::where('cliente_id', $user->id)->count();
            $facturasCliente = Factura::where('cliente_id', $user->id)->count();
            $totalGastado = Factura::where('cliente_id', $user->id)->sum('total');
            
            return view('dashboard_cliente', compact('comprasCliente', 'facturasCliente', 'totalGastado'));
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
            $facturasMes = \App\Models\Factura::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count();
            $ventasMes = \App\Models\Factura::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->sum('total');
            $ticketPromedio = $facturasMes > 0 ? $ventasMes / $facturasMes : 0;
            return view('dashboard_ventas', compact('facturasMes', 'ventasMes', 'ticketPromedio'));
        }

        // PAGOS
        if ($user->hasRole('Pagos')) {
            $pagosPendientes = \App\Models\Pago::where('estado', 'pendiente')->count();
            $pagosAprobados = \App\Models\Pago::where('estado', 'aprobado')->count();
            $pagosRechazados = \App\Models\Pago::where('estado', 'rechazado')->count();
            $totalPagosMes = \App\Models\Pago::whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year)
                                   ->count();
            
            $montoTotalAprobado = \App\Models\Pago::where('estado', 'aprobado')->sum('monto');
            $montoMesAprobado = \App\Models\Pago::where('estado', 'aprobado')
                                      ->whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->sum('monto');
            
            $pagosRecientes = \App\Models\Pago::with(['factura.cliente', 'pagadoPor'])
                                     ->orderBy('created_at', 'desc')
                                     ->limit(10)
                                     ->get();
            
            $tiempoPromedioValidacion = \App\Models\Pago::whereNotNull('validated_at')
                                              ->selectRaw('AVG(EXTRACT(EPOCH FROM (validated_at - created_at))/3600) as promedio_horas')
                                              ->value('promedio_horas');
            $tiempoPromedioValidacion = round($tiempoPromedioValidacion ?? 0, 1);
            
            // Estadísticas por tipo de pago
            $pagosPorTipo = \App\Models\Pago::selectRaw('tipo_pago, COUNT(*) as total, SUM(monto) as monto_total')
                                  ->groupBy('tipo_pago')
                                  ->get();
            
            return view('dashboard_pagos', compact(
                'pagosPendientes',
                'pagosAprobados', 
                'pagosRechazados',
                'totalPagosMes',
                'montoTotalAprobado',
                'montoMesAprobado',
                'pagosRecientes',
                'tiempoPromedioValidacion',
                'pagosPorTipo'
            ));
        }

        // Si no tiene rol válido
        abort(403, 'Rol no autorizado');
    }
}
