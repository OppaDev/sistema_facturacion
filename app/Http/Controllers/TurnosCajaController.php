<?php

namespace App\Http\Controllers;

use App\Models\TurnoCaja;
use App\Models\MovimientoCaja;
use App\Models\Venta;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class TurnosCajaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of turnos.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', TurnoCaja::class);
        
        $turnos = TurnoCaja::with('usuario');
        
        // Si no es admin, solo ver sus propios turnos
        if (!Auth::user()->hasRole('Administrador')) {
            $turnos = $turnos->where('usuario_id', Auth::id());
        }
        
        // Filtros
        if ($request->filled('estado')) {
            $turnos = $turnos->where('estado', $request->input('estado'));
        }
        
        if ($request->filled('usuario_id')) {
            $turnos = $turnos->where('usuario_id', $request->input('usuario_id'));
        }
        
        if ($request->filled('fecha_desde')) {
            $turnos = $turnos->whereDate('fecha_apertura', '>=', $request->input('fecha_desde'));
        }
        
        if ($request->filled('fecha_hasta')) {
            $turnos = $turnos->whereDate('fecha_apertura', '<=', $request->input('fecha_hasta'));
        }
        
        $turnos = $turnos->orderBy('fecha_apertura', 'desc')
                        ->paginate($request->input('per_page', 15))
                        ->withQueryString();
        
        // Verificar si hay turno abierto para el usuario actual
        $turnoAbierto = TurnoCaja::turnoAbiertoParaUsuario(Auth::id());
        
        // Usuarios para filtro (solo si es admin)
        $usuarios = Auth::user()->hasRole('Administrador') 
                    ? \App\Models\User::whereHas('roles', function($q) {
                        $q->whereIn('name', ['Administrador', 'Ventas']);
                    })->orderBy('name')->get() 
                    : collect();
        
        return view('caja.turnos.index', compact('turnos', 'turnoAbierto', 'usuarios'));
    }

    /**
     * Show the form for opening a new turno.
     */
    public function create()
    {
        $this->authorize('create', TurnoCaja::class);
        
        // Verificar que no tenga un turno abierto
        $turnoAbierto = TurnoCaja::turnoAbiertoParaUsuario(Auth::id());
        
        if ($turnoAbierto) {
            return redirect()->route('caja.turnos.index')
                           ->with('warning', 'Ya tienes un turno abierto. Debes cerrarlo antes de abrir uno nuevo.');
        }
        
        return view('caja.turnos.create');
    }

    /**
     * Store a newly created turno (apertura).
     */
    public function store(Request $request)
    {
        $this->authorize('create', TurnoCaja::class);
        
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);
        
        // Verificar que no tenga un turno abierto
        $turnoAbierto = TurnoCaja::turnoAbiertoParaUsuario(Auth::id());
        
        if ($turnoAbierto) {
            return redirect()->back()->with('error', 'Ya tienes un turno abierto.');
        }
        
        DB::beginTransaction();
        
        try {
            $turno = TurnoCaja::create([
                'usuario_id' => Auth::id(),
                'fecha_apertura' => now(),
                'monto_inicial' => $request->monto_inicial,
                'total_ventas' => 0,
                'total_efectivo' => 0,
                'total_tarjeta' => 0,
                'total_transferencia' => 0,
                'total_deuna' => 0,
                'diferencia' => 0,
                'observaciones' => $request->observaciones,
                'estado' => 'abierto',
            ]);
            
            // Registrar auditoría
            $this->registrarAuditoria('create', $turno, null, $turno->toArray(), 'Turno de caja abierto - Monto inicial: $' . number_format($turno->monto_inicial, 2));
            
            DB::commit();
            
            return redirect()->route('caja.pos')
                           ->with('success', 'Turno de caja abierto exitosamente con monto inicial de $' . number_format($turno->monto_inicial, 2));
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al abrir turno: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al abrir el turno: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified turno.
     */
    public function show($id)
    {
        $turno = TurnoCaja::with(['usuario', 'ventas.detalles.producto', 'movimientos.usuario'])
                          ->findOrFail($id);
        
        $this->authorize('view', $turno);
        
        // Estadísticas del turno
        $ventasCompletadas = $turno->ventas()->where('estado', 'completada')->get();
        
        $stats = [
            'total_ventas' => $ventasCompletadas->count(),
            'ventas_efectivo' => $ventasCompletadas->where('tipo_pago', 'efectivo')->count(),
            'ventas_tarjeta' => $ventasCompletadas->where('tipo_pago', 'tarjeta')->count(),
            'ventas_transferencia' => $ventasCompletadas->where('tipo_pago', 'transferencia')->count(),
            'ventas_deuna' => $ventasCompletadas->where('tipo_pago', 'deuna')->count(),
            'ventas_mixto' => $ventasCompletadas->where('tipo_pago', 'mixto')->count(),
            'ventas_anuladas' => $turno->ventas()->onlyTrashed()->count(),
        ];
        
        return view('caja.turnos.show', compact('turno', 'stats'));
    }

    /**
     * Show the form for closing turno.
     */
    public function cierre($id)
    {
        $turno = TurnoCaja::with(['ventas' => function($q) {
            $q->where('estado', 'completada');
        }, 'movimientos'])->findOrFail($id);
        
        $this->authorize('update', $turno);
        
        if ($turno->isCerrado()) {
            return redirect()->route('caja.turnos.show', $turno)
                           ->with('info', 'Este turno ya está cerrado.');
        }
        
        // Recalcular totales
        $turno->calcularTotales();
        
        // Calcular efectivo esperado
        $efectivoEsperado = $turno->monto_inicial + $turno->total_efectivo;
        
        // Sumar ingresos y restar egresos
        foreach ($turno->movimientos as $movimiento) {
            if ($movimiento->isIngreso()) {
                $efectivoEsperado += $movimiento->monto;
            } elseif ($movimiento->isEgreso()) {
                $efectivoEsperado -= $movimiento->monto;
            }
        }
        
        return view('caja.turnos.cierre', compact('turno', 'efectivoEsperado'));
    }

    /**
     * Cerrar turno de caja.
     */
    public function cerrar(Request $request, $id)
    {
        $request->validate([
            'monto_final' => 'required|numeric|min:0',
            'observaciones_cierre' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            $turno = TurnoCaja::with('ventas', 'movimientos')->findOrFail($id);
            $this->authorize('update', $turno);
            
            if ($turno->isCerrado()) {
                return redirect()->back()->with('error', 'Este turno ya está cerrado.');
            }
            
            // Verificar que sea el turno del usuario actual o sea admin
            if ($turno->usuario_id !== Auth::id() && !Auth::user()->hasRole('Administrador')) {
                return redirect()->back()->with('error', 'No puedes cerrar el turno de otro usuario.');
            }
            
            $old = $turno->toArray();
            
            // Recalcular totales
            $turno->calcularTotales();
            
            // Calcular efectivo esperado
            $efectivoEsperado = $turno->monto_inicial + $turno->total_efectivo;
            
            foreach ($turno->movimientos as $movimiento) {
                if ($movimiento->isIngreso()) {
                    $efectivoEsperado += $movimiento->monto;
                } elseif ($movimiento->isEgreso()) {
                    $efectivoEsperado -= $movimiento->monto;
                }
            }
            
            // Calcular diferencia
            $montoFinal = $request->monto_final;
            $diferencia = $montoFinal - $efectivoEsperado;
            
            // Actualizar turno
            $turno->fecha_cierre = now();
            $turno->monto_final = $montoFinal;
            $turno->diferencia = $diferencia;
            $turno->observaciones = $turno->observaciones . "\n\nCIERRE: " . ($request->observaciones_cierre ?? 'Sin observaciones');
            $turno->estado = 'cerrado';
            $turno->save();
            
            // Registrar auditoría
            $descripcion = 'Turno de caja cerrado - Total ventas: $' . number_format($turno->total_ventas, 2);
            if (abs($diferencia) > 0.01) {
                $tipo = $diferencia > 0 ? 'SOBRANTE' : 'FALTANTE';
                $descripcion .= " - {$tipo}: $" . number_format(abs($diferencia), 2);
            }
            
            $this->registrarAuditoria('update', $turno, $old, $turno->toArray(), $descripcion);
            
            DB::commit();
            
            $mensaje = 'Turno cerrado exitosamente. Total de ventas: $' . number_format($turno->total_ventas, 2);
            
            if (abs($diferencia) > 0.01) {
                $tipo = $diferencia > 0 ? 'Sobrante' : 'Faltante';
                $mensaje .= " - {$tipo}: $" . number_format(abs($diferencia), 2);
            }
            
            return redirect()->route('caja.turnos.show', $turno)
                           ->with('success', $mensaje);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al cerrar turno: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al cerrar el turno: ' . $e->getMessage());
        }
    }

    /**
     * Store a cash movement (ingreso/egreso/ajuste).
     */
    public function storeMovimiento(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|in:ingreso,egreso,ajuste',
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'notas' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            $turno = TurnoCaja::findOrFail($id);
            $this->authorize('update', $turno);
            
            if ($turno->isCerrado()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden agregar movimientos a un turno cerrado.'
                ], 403);
            }
            
            $movimiento = MovimientoCaja::create([
                'turno_id' => $turno->id,
                'tipo' => $request->tipo,
                'concepto' => $request->concepto,
                'monto' => $request->monto,
                'usuario_id' => Auth::id(),
                'notas' => $request->notas,
            ]);
            
            // Registrar auditoría
            $this->registrarAuditoria('create', $movimiento, null, $movimiento->toArray(), 'Movimiento de caja registrado: ' . $movimiento->getTipoLabel() . ' - $' . number_format($movimiento->monto, 2));
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado exitosamente.',
                'movimiento' => $movimiento->load('usuario'),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar movimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de cierre de caja (PDF).
     */
    public function reporteCierre($id)
    {
        $turno = TurnoCaja::with(['usuario', 'ventas.detalles.producto', 'movimientos.usuario'])
                          ->findOrFail($id);
        
        $this->authorize('view', $turno);
        
        if ($turno->isAbierto()) {
            return redirect()->back()->with('error', 'No se puede generar reporte de un turno abierto.');
        }
        
        return view('caja.turnos.reporte', compact('turno'));
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
