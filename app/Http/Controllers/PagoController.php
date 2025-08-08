<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Factura;
use App\Http\Requests\StorePagoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\PagoAprobadoNotification;
use App\Notifications\PagoRechazadoNotification;
use App\Notifications\PagoRegistradoNotification;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource (Web).
     */
    public function index(Request $request)
    {
        // Solo usuarios con rol Pagos o Administrador pueden ver pagos
        $this->authorize('viewAny', Pago::class);
        
        $query = Pago::with(['factura.cliente', 'pagadoPor', 'validadoPor'])
                    ->orderBy('created_at', 'desc');

        // Filtros
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

        $pagos = $query->paginate($request->input('per_page', 15))
                      ->withQueryString();

        // Estadísticas
        $estadisticas = [
            'pendientes' => Pago::where('estado', 'pendiente')->count(),
            'aprobados' => Pago::where('estado', 'aprobado')->count(),
            'rechazados' => Pago::where('estado', 'rechazado')->count(),
            'total_monto_pendiente' => Pago::where('estado', 'pendiente')->sum('monto')
        ];

        return view('pagos.index', compact('pagos', 'estadisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage (API).
     */
    public function store(StorePagoRequest $request)
    {
        try {
            DB::beginTransaction();

            // Crear el pago
            $pago = Pago::create([
                'factura_id' => $request->factura_id,
                'tipo_pago' => $request->tipo_pago,
                'monto' => $request->monto,
                'numero_transaccion' => $request->numero_transaccion,
                'observacion' => $request->observacion,
                'estado' => 'pendiente',
                'pagado_por' => Auth::id()
            ]);

            // Registrar auditoría
            \App\Models\Auditoria::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'model_type' => 'App\Models\Pago',
                'model_id' => $pago->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'factura_id' => $pago->factura_id,
                    'tipo_pago' => $pago->tipo_pago,
                    'monto' => $pago->monto,
                    'numero_transaccion' => $pago->numero_transaccion,
                    'estado' => $pago->estado
                ]),
                'description' => "Pago registrado por API - Factura #{$pago->factura_id} - Monto: $" . number_format($pago->monto, 2),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);

            // Enviar notificación de confirmación al cliente
            $cliente = $pago->factura->cliente;
            if ($cliente && $cliente->email) {
                try {
                    $cliente->notify(new PagoRegistradoNotification($pago));
                    
                    Log::info('Notificación de pago registrado enviada', [
                        'pago_id' => $pago->id,
                        'cliente_email' => $cliente->email
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Error al enviar notificación de pago registrado', [
                        'pago_id' => $pago->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Cargar relaciones para la respuesta
            $pago->load(['factura', 'pagadoPor']);

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente. Será revisado por nuestro equipo.',
                'data' => [
                    'pago_id' => $pago->id,
                    'factura_numero' => $pago->factura->getNumeroFormateado(),
                    'monto' => number_format($pago->monto, 2),
                    'tipo_pago' => $pago->tipo_pago,
                    'estado' => $pago->estado,
                    'fecha_registro' => $pago->created_at->format('Y-m-d H:i:s'),
                    'mensaje' => 'Tu pago ha sido registrado y está pendiente de validación.'
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar pago via API', [
                'user_id' => Auth::id(),
                'factura_id' => $request->factura_id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el pago. Intenta nuevamente.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        $this->authorize('view', $pago);
        
        $pago->load(['factura.cliente', 'factura.detalles.producto', 'pagadoPor', 'validadoPor']);
        
        return view('pagos.show', compact('pago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pago $pago)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pago $pago)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pago $pago)
    {
        //
    }

    /**
     * Aprobar un pago (Web).
     */
    public function aprobar(Request $request, Pago $pago)
    {
        $this->authorize('update', $pago);

        if (!$pago->isPendiente()) {
            return redirect()->back()->with('error', 'Solo se pueden aprobar pagos pendientes.');
        }

        try {
            DB::beginTransaction();

            // Guardar estado anterior para auditoría
            $estadoAnterior = [
                'estado' => $pago->estado,
                'validado_por' => $pago->validado_por,
                'validated_at' => $pago->validated_at
            ];

            // Actualizar pago
            $pago->update([
                'estado' => 'aprobado',
                'validado_por' => Auth::id(),
                'validated_at' => now()
            ]);

            // Actualizar estado de factura a 'pagada'
            $pago->factura->update(['estado' => 'pagada']);

            // Registrar auditoría del pago
            \App\Models\Auditoria::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'model_type' => 'App\Models\Pago',
                'model_id' => $pago->id,
                'old_values' => json_encode($estadoAnterior),
                'new_values' => json_encode([
                    'estado' => 'aprobado',
                    'validado_por' => Auth::id(),
                    'validated_at' => now()
                ]),
                'description' => "Pago aprobado - Factura #{$pago->factura_id} - Monto: $" . number_format($pago->monto, 2),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);

            // Registrar auditoría de la factura
            \App\Models\Auditoria::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'model_type' => 'App\Models\Factura',
                'model_id' => $pago->factura->id,
                'old_values' => json_encode(['estado' => 'pendiente']),
                'new_values' => json_encode(['estado' => 'pagada']),
                'description' => "Factura marcada como pagada - Pago #{$pago->id} aprobado",
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);

            // Enviar notificación por email al cliente
            $cliente = $pago->factura->cliente;
            if ($cliente && $cliente->email) {
                try {
                    $cliente->notify(new PagoAprobadoNotification($pago));
                    
                    Log::info('Notificación de pago aprobado enviada', [
                        'pago_id' => $pago->id,
                        'cliente_email' => $cliente->email
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Error al enviar notificación de pago aprobado', [
                        'pago_id' => $pago->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('pagos.index')->with('success', 'Pago aprobado exitosamente. La factura ha sido marcada como pagada y se ha notificado al cliente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al aprobar pago', [
                'pago_id' => $pago->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al aprobar el pago. Intenta nuevamente.');
        }
    }

    /**
     * Rechazar un pago (Web).
     */
    public function rechazar(Request $request, Pago $pago)
    {
        $this->authorize('update', $pago);

        $request->validate([
            'motivo_rechazo' => 'nullable|string|max:500'
        ]);

        if (!$pago->isPendiente()) {
            return redirect()->back()->with('error', 'Solo se pueden rechazar pagos pendientes.');
        }

        try {
            DB::beginTransaction();

            // Guardar estado anterior para auditoría
            $estadoAnterior = [
                'estado' => $pago->estado,
                'validado_por' => $pago->validado_por,
                'validated_at' => $pago->validated_at,
                'observacion' => $pago->observacion
            ];

            // Actualizar pago
            $motivoCompleto = $pago->observacion;
            if ($request->motivo_rechazo) {
                $motivoCompleto .= ($motivoCompleto ? "\n\n" : '') . "RECHAZADO: " . $request->motivo_rechazo;
            }

            $pago->update([
                'estado' => 'rechazado',
                'validado_por' => Auth::id(),
                'validated_at' => now(),
                'observacion' => $motivoCompleto
            ]);

            // La factura se mantiene en estado 'pendiente' para que puedan intentar pagar nuevamente

            // Registrar auditoría
            \App\Models\Auditoria::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'model_type' => 'App\Models\Pago',
                'model_id' => $pago->id,
                'old_values' => json_encode($estadoAnterior),
                'new_values' => json_encode([
                    'estado' => 'rechazado',
                    'validado_por' => Auth::id(),
                    'validated_at' => now(),
                    'motivo_rechazo' => $request->motivo_rechazo
                ]),
                'description' => "Pago rechazado - Factura #{$pago->factura_id} - Motivo: " . ($request->motivo_rechazo ?: 'Sin motivo especificado'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);

            // Enviar notificación por email al cliente
            $cliente = $pago->factura->cliente;
            if ($cliente && $cliente->email) {
                try {
                    $cliente->notify(new PagoRechazadoNotification($pago, $request->motivo_rechazo));
                    
                    Log::info('Notificación de pago rechazado enviada', [
                        'pago_id' => $pago->id,
                        'cliente_email' => $cliente->email,
                        'motivo' => $request->motivo_rechazo
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Error al enviar notificación de pago rechazado', [
                        'pago_id' => $pago->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('pagos.index')->with('success', 'Pago rechazado y cliente notificado. El cliente podrá intentar pagar nuevamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al rechazar pago', [
                'pago_id' => $pago->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al rechazar el pago. Intenta nuevamente.');
        }
    }
}
