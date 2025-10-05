@extends('layouts.app')

@section('title', 'Detalle del Turno #' . $turno->id)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Caja / Turnos /</span> 
                <span style="color: #ff0000;">Turno #{{ $turno->id }}</span>
            </h4>
            <p class="text-muted mb-0">Detalle completo del turno de caja</p>
        </div>
        <div>
            <a href="{{ route('caja.turnos.index') }}" class="btn btn-outline-secondary me-2">
                <i class='bx bx-arrow-back'></i> Volver
            </a>
            
            @if($turno->estado === 'abierto')
                @can('cerrar', $turno)
                    <a href="{{ route('caja.turnos.cierre', $turno->id) }}" class="btn btn-danger">
                        <i class='bx bx-lock'></i> Cerrar Turno
                    </a>
                @endcan
            @else
                <a href="{{ route('caja.turnos.reporte', $turno->id) }}" class="btn btn-outline-danger" target="_blank">
                    <i class='bx bx-file-pdf'></i> Descargar PDF
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda -->
        <div class="col-lg-8 mb-4">
            
            <!-- Información del Turno -->
            <div class="card mb-4" style="border-left: 4px solid #ff0000;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class='bx bx-time-five'></i> Información del Turno</h5>
                    @if($turno->estado === 'abierto')
                        <span class="badge bg-success">Abierto</span>
                    @else
                        <span class="badge bg-secondary">Cerrado</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Turno ID</label>
                            <div class="fw-bold" style="color: #ff0000;">#{{ $turno->id }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Cajero</label>
                            <div class="fw-bold">{{ $turno->usuario->name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Fecha y Hora de Apertura</label>
                            <div>{{ $turno->fecha_apertura->format('d/m/Y H:i:s') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">
                                @if($turno->estado === 'abierto')
                                    Duración hasta ahora
                                @else
                                    Fecha y Hora de Cierre
                                @endif
                            </label>
                            <div>
                                @if($turno->estado === 'abierto')
                                    @php
                                        $duracion = $turno->fecha_apertura->diff(now());
                                        $horas = $duracion->days * 24 + $duracion->h;
                                    @endphp
                                    {{ $horas }}h {{ $duracion->i }}min
                                    <span class="badge bg-label-success ms-2">En progreso</span>
                                @else
                                    {{ $turno->fecha_cierre->format('d/m/Y H:i:s') }}
                                @endif
                            </div>
                        </div>
                        @if($turno->estado === 'cerrado')
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small">Duración Total</label>
                            <div>
                                @php
                                    $duracion = $turno->fecha_apertura->diff($turno->fecha_cierre);
                                    $horas = $duracion->days * 24 + $duracion->h;
                                @endphp
                                {{ $horas }} horas {{ $duracion->i }} minutos
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($turno->observaciones_apertura)
                        <hr>
                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading mb-2">
                                <i class='bx bx-info-circle'></i> Observaciones de Apertura
                            </h6>
                            <p class="mb-0">{{ $turno->observaciones_apertura }}</p>
                        </div>
                    @endif

                    @if($turno->estado === 'cerrado' && $turno->observaciones_cierre)
                        <hr>
                        <div class="alert alert-secondary mb-0">
                            <h6 class="alert-heading mb-2">
                                <i class='bx bx-info-circle'></i> Observaciones de Cierre
                            </h6>
                            <p class="mb-0">{{ $turno->observaciones_cierre }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ventas del Turno -->
            <div class="card mb-4" style="border-left: 4px solid #ff0000;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class='bx bx-cart'></i> Ventas del Turno</h5>
                    <span class="badge bg-label-primary">{{ $turno->ventas->where('estado', 'completada')->count() }} ventas</span>
                </div>
                <div class="card-body p-0">
                    @if($turno->ventas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Número</th>
                                        <th>Hora</th>
                                        <th>Cliente</th>
                                        <th>Método de Pago</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($turno->ventas as $venta)
                                    <tr>
                                        <td>
                                            <span class="fw-bold" style="color: #ff0000;">#{{ $venta->numero_venta }}</span>
                                        </td>
                                        <td>{{ $venta->created_at->format('H:i:s') }}</td>
                                        <td>
                                            @if($venta->cliente_nombre)
                                                {{ $venta->cliente_nombre }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($venta->tipo_pago === 'efectivo')
                                                <span class="badge bg-success">💵 Efectivo</span>
                                            @elseif($venta->tipo_pago === 'tarjeta')
                                                <span class="badge bg-primary">💳 Tarjeta</span>
                                            @elseif($venta->tipo_pago === 'transferencia')
                                                <span class="badge bg-info">🏦 Transferencia</span>
                                            @elseif($venta->tipo_pago === 'deuna')
                                                <span class="badge bg-warning">📱 Deuna</span>
                                            @elseif($venta->tipo_pago === 'mixto')
                                                <span class="badge bg-secondary">🔄 Mixto</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">${{ number_format($venta->total, 2) }}</td>
                                        <td class="text-center">
                                            @if($venta->estado === 'completada')
                                                <span class="badge bg-success">Completada</span>
                                            @else
                                                <span class="badge bg-danger">Anulada</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-vertical-rounded'></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('caja.show', $venta->id) }}">
                                                        <i class='bx bx-show me-1'></i> Ver Detalle
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('caja.ticket', $venta->id) }}" target="_blank">
                                                        <i class='bx bx-printer me-1'></i> Imprimir Ticket
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="card-body">
                            <div class="alert alert-light mb-0 text-center">
                                <i class='bx bx-cart' style="font-size: 48px; opacity: 0.3;"></i>
                                <p class="mb-0 mt-2">No hay ventas registradas en este turno</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Movimientos de Caja -->
            @if($turno->movimientos->count() > 0)
            <div class="card" style="border-left: 4px solid #ff0000;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class='bx bx-transfer'></i> Movimientos de Caja</h5>
                    <span class="badge bg-label-warning">{{ $turno->movimientos->count() }} movimientos</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hora</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Monto</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($turno->movimientos as $movimiento)
                                <tr>
                                    <td>{{ $movimiento->created_at->format('H:i:s') }}</td>
                                    <td>
                                        @if($movimiento->tipo === 'ingreso')
                                            <span class="badge bg-success">Ingreso</span>
                                        @elseif($movimiento->tipo === 'egreso')
                                            <span class="badge bg-danger">Egreso</span>
                                        @else
                                            <span class="badge bg-warning">Ajuste</span>
                                        @endif
                                    </td>
                                    <td>{{ $movimiento->concepto }}</td>
                                    <td class="text-end fw-bold">
                                        @if($movimiento->tipo === 'ingreso')
                                            <span class="text-success">+${{ number_format($movimiento->monto, 2) }}</span>
                                        @else
                                            <span class="text-danger">-${{ number_format($movimiento->monto, 2) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $movimiento->usuario->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            
            <!-- Estadísticas Generales -->
            <div class="card mb-4" style="border-left: 4px solid #ff0000;">
                <div class="card-header" style="background-color: #fff5f5;">
                    <h5 class="mb-0" style="color: #ff0000;">
                        <i class='bx bx-bar-chart-alt-2'></i> Resumen Financiero
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted small d-block mb-1">Monto Inicial</label>
                        <h4 class="mb-0 fw-bold">${{ number_format($turno->monto_inicial, 2) }}</h4>
                    </div>

                    @php
                        $ventasCompletadas = $turno->ventas->where('estado', 'completada');
                        $totalVentas = $ventasCompletadas->sum('total');
                        $totalEfectivo = $ventasCompletadas->where('tipo_pago', 'efectivo')->sum('total');
                        $totalTarjeta = $ventasCompletadas->where('tipo_pago', 'tarjeta')->sum('total');
                        $totalTransferencia = $ventasCompletadas->where('tipo_pago', 'transferencia')->sum('total');
                        $totalDeuna = $ventasCompletadas->where('tipo_pago', 'deuna')->sum('total');
                        
                        // Calcular mixtos
                        $mixtos = $ventasCompletadas->where('tipo_pago', 'mixto');
                        $mixtoEfectivo = 0;
                        $mixtoTarjeta = 0;
                        $mixtoTransferencia = 0;
                        $mixtoDeuna = 0;
                        
                        foreach($mixtos as $venta) {
                            if ($venta->detalle_pago_mixto) {
                                $desglose = json_decode($venta->detalle_pago_mixto, true);
                                $mixtoEfectivo += $desglose['efectivo'] ?? 0;
                                $mixtoTarjeta += $desglose['tarjeta'] ?? 0;
                                $mixtoTransferencia += $desglose['transferencia'] ?? 0;
                                $mixtoDeuna += $desglose['deuna'] ?? 0;
                            }
                        }
                        
                        $totalEfectivoFinal = $totalEfectivo + $mixtoEfectivo;
                        $totalTarjetaFinal = $totalTarjeta + $mixtoTarjeta;
                        $totalTransferenciaFinal = $totalTransferencia + $mixtoTransferencia;
                        $totalDeunaFinal = $totalDeuna + $mixtoDeuna;
                        
                        // Movimientos
                        $totalIngresos = $turno->movimientos->where('tipo', 'ingreso')->sum('monto');
                        $totalEgresos = $turno->movimientos->where('tipo', 'egreso')->sum('monto');
                        $totalAjustes = $turno->movimientos->where('tipo', 'ajuste')->sum('monto');
                    @endphp

                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Total Ventas</label>
                        <h5 class="mb-0 fw-bold text-success">
                            +${{ number_format($totalVentas, 2) }}
                        </h5>
                        <small class="text-muted">{{ $ventasCompletadas->count() }} ventas completadas</small>
                    </div>

                    @if($turno->movimientos->count() > 0)
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Movimientos de Caja</label>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-success">Ingresos:</span>
                            <span class="fw-bold text-success">+${{ number_format($totalIngresos, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-danger">Egresos:</span>
                            <span class="fw-bold text-danger">-${{ number_format($totalEgresos, 2) }}</span>
                        </div>
                    </div>
                    @endif

                    @if($turno->estado === 'cerrado')
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Monto Final Contado</label>
                        <h5 class="mb-0 fw-bold">${{ number_format($turno->monto_final, 2) }}</h5>
                    </div>

                    @php
                        $efectivoEsperado = $turno->monto_inicial + $totalEfectivoFinal + $totalIngresos - $totalEgresos;
                        $diferencia = $turno->monto_final - $efectivoEsperado;
                    @endphp

                    <div class="mb-0">
                        <label class="text-muted small d-block mb-1">Diferencia</label>
                        @if(abs($diferencia) < 0.01)
                            <div class="alert alert-success mb-0 py-2">
                                <strong>Sin diferencia</strong><br>
                                Cuadra perfecto ✓
                            </div>
                        @elseif($diferencia > 0)
                            <div class="alert alert-info mb-0 py-2">
                                <strong>Sobrante</strong><br>
                                +${{ number_format($diferencia, 2) }}
                            </div>
                        @else
                            <div class="alert alert-danger mb-0 py-2">
                                <strong>Faltante</strong><br>
                                ${{ number_format($diferencia, 2) }}
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Desglose por Método de Pago -->
            <div class="card" style="border-left: 4px solid #ff0000;">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bx-credit-card'></i> Por Método de Pago</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success rounded-circle p-2 me-2">
                                    <i class='bx bx-money'></i>
                                </span>
                                <span>Efectivo</span>
                            </div>
                            <span class="fw-bold">${{ number_format($totalEfectivoFinal, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary rounded-circle p-2 me-2">
                                    <i class='bx bx-credit-card'></i>
                                </span>
                                <span>Tarjeta</span>
                            </div>
                            <span class="fw-bold">${{ number_format($totalTarjetaFinal, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info rounded-circle p-2 me-2">
                                    <i class='bx bx-transfer'></i>
                                </span>
                                <span>Transferencia</span>
                            </div>
                            <span class="fw-bold">${{ number_format($totalTransferenciaFinal, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning rounded-circle p-2 me-2">
                                    <i class='bx bx-mobile-alt'></i>
                                </span>
                                <span>Deuna</span>
                            </div>
                            <span class="fw-bold">${{ number_format($totalDeunaFinal, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Botón para registrar movimiento (solo si turno abierto) -->
    @if($turno->estado === 'abierto' && auth()->user()->hasRole('Administrador'))
    <div class="row mt-4">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #ff0000;">
                <div class="card-body">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#movimientoModal">
                        <i class='bx bx-plus-circle'></i> Registrar Movimiento de Caja
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Modal para Registrar Movimiento -->
@if($turno->estado === 'abierto' && auth()->user()->hasRole('Administrador'))
<div class="modal fade" id="movimientoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #ff0000;">
                <h5 class="modal-title">
                    <i class='bx bx-transfer'></i> Registrar Movimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('caja.turnos.movimientos.store', $turno->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                        <select class="form-select" name="tipo" required>
                            <option value="">Seleccionar...</option>
                            <option value="ingreso">Ingreso - Entrada de dinero</option>
                            <option value="egreso">Egreso - Salida de dinero</option>
                            <option value="ajuste">Ajuste - Corrección</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Monto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="monto" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Concepto <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="concepto" rows="3" required maxlength="500"></textarea>
                        <small class="text-muted">Describe el motivo del movimiento</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class='bx bx-save'></i> Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
