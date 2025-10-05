@extends('layouts.app')

@section('title', 'Cerrar Turno de Caja')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-sm-row my-0">
                <div class="page-title-content">
                    <h4 class="mb-1">
                        <span class="text-muted fw-light">Caja / Turnos /</span> 
                        <span style="color: #ff0000;">Cerrar Turno #{{ $turno->id }}</span>
                    </h4>
                    <p class="text-muted mb-0">Cierre de turno de caja</p>
                </div>
                <div class="page-title-actions ms-auto">
                    <a href="{{ route('caja.turnos.show', $turno->id) }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-10 mx-auto">
            <!-- Información del Turno -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #ff0000; color: white;">
                    <h5 class="mb-0">
                        <i class="bx bx-info-circle me-2"></i>Información del Turno
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Cajero</p>
                            <h6>{{ $turno->usuario->name }}</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Fecha Apertura</p>
                            <h6>{{ $turno->fecha_apertura->format('d/m/Y H:i') }}</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Duración</p>
                            <h6>{{ $turno->fecha_apertura->diffForHumans(null, true) }}</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Monto Inicial</p>
                            <h5 style="color: #ff0000;">${{ number_format($turno->monto_inicial, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen de Ventas -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card" style="border-left: 4px solid #28a745;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total Ventas</p>
                                    <h4 class="mb-0">${{ number_format($turno->total_ventas, 2) }}</h4>
                                    <small class="text-success">{{ $turno->ventas()->where('estado', 'completada')->count() }} ventas</small>
                                </div>
                                <span class="badge bg-label-success rounded p-3">
                                    <i class="bx bx-dollar bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card" style="border-left: 4px solid #17a2b8;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Efectivo</p>
                                    <h4 class="mb-0">${{ number_format($turno->total_efectivo, 2) }}</h4>
                                </div>
                                <span class="badge bg-label-info rounded p-3">
                                    <i class="bx bx-wallet bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card" style="border-left: 4px solid #ffc107;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Otros Medios</p>
                                    <h4 class="mb-0">${{ number_format($turno->total_tarjeta + $turno->total_transferencia + $turno->total_deuna, 2) }}</h4>
                                </div>
                                <span class="badge bg-label-warning rounded p-3">
                                    <i class="bx bx-credit-card bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desglose Detallado -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul me-2"></i>Desglose por Método de Pago
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td><i class="bx bx-wallet text-success me-2"></i> Efectivo</td>
                                <td class="text-end"><strong>${{ number_format($turno->total_efectivo, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="bx bx-credit-card text-primary me-2"></i> Tarjeta</td>
                                <td class="text-end"><strong>${{ number_format($turno->total_tarjeta, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="bx bx-transfer text-info me-2"></i> Transferencia</td>
                                <td class="text-end"><strong>${{ number_format($turno->total_transferencia, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="bx bx-mobile text-warning me-2"></i> Deuna</td>
                                <td class="text-end"><strong>${{ number_format($turno->total_deuna, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Movimientos de Caja -->
            @if($turno->movimientos->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-transfer me-2"></i>Movimientos de Caja
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($turno->movimientos as $movimiento)
                            <tr>
                                <td>
                                    @if($movimiento->isIngreso())
                                        <span class="badge bg-success">Ingreso</span>
                                    @elseif($movimiento->isEgreso())
                                        <span class="badge bg-danger">Egreso</span>
                                    @else
                                        <span class="badge bg-info">Ajuste</span>
                                    @endif
                                </td>
                                <td>{{ $movimiento->concepto }}</td>
                                <td>
                                    @if($movimiento->isIngreso())
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
            @endif

            <!-- Cálculo de Efectivo Esperado -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bx bx-calculator me-2"></i>Cálculo de Efectivo Esperado
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>Monto Inicial</td>
                                <td class="text-end">${{ number_format($turno->monto_inicial, 2) }}</td>
                            </tr>
                            <tr>
                                <td>+ Ventas en Efectivo</td>
                                <td class="text-end text-success">+${{ number_format($turno->total_efectivo, 2) }}</td>
                            </tr>
                            @foreach($turno->movimientos as $movimiento)
                            @if($movimiento->isIngreso())
                            <tr>
                                <td>+ {{ $movimiento->concepto }}</td>
                                <td class="text-end text-success">+${{ number_format($movimiento->monto, 2) }}</td>
                            </tr>
                            @elseif($movimiento->isEgreso())
                            <tr>
                                <td>- {{ $movimiento->concepto }}</td>
                                <td class="text-end text-danger">-${{ number_format($movimiento->monto, 2) }}</td>
                            </tr>
                            @endif
                            @endforeach
                            <tr class="border-top">
                                <td><strong>EFECTIVO ESPERADO</strong></td>
                                <td class="text-end">
                                    <h4 style="color: #ff0000;">${{ number_format($efectivoEsperado, 2) }}</h4>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Formulario de Cierre -->
            <div class="card">
                <div class="card-header" style="background-color: #ff0000; color: white;">
                    <h5 class="mb-0">
                        <i class="bx bx-lock me-2"></i>Cerrar Turno de Caja
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-4">
                        <h6 class="alert-heading">
                            <i class="bx bx-info-circle me-2"></i>Instrucciones para el Cierre
                        </h6>
                        <ol class="mb-0">
                            <li>Cuente todo el efectivo disponible en caja</li>
                            <li>Compare con el efectivo esperado: <strong>${{ number_format($efectivoEsperado, 2) }}</strong></li>
                            <li>Ingrese el monto contado en el campo siguiente</li>
                            <li>El sistema calculará automáticamente si hay sobrante o faltante</li>
                        </ol>
                    </div>

                    <form method="POST" action="{{ route('caja.turnos.cerrar', $turno->id) }}">
                        @csrf

                        <!-- Monto Final -->
                        <div class="mb-4">
                            <label for="monto_final" class="form-label">
                                <strong>Monto Final Contado *</strong>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" style="background-color: #ff0000; color: white;">
                                    <i class="bx bx-dollar"></i>
                                </span>
                                <input type="number" 
                                       class="form-control @error('monto_final') is-invalid @enderror" 
                                       id="monto_final" 
                                       name="monto_final" 
                                       value="{{ old('monto_final', number_format($efectivoEsperado, 2, '.', '')) }}"
                                       step="0.01" 
                                       min="0" 
                                       required 
                                       autofocus>
                                <span class="input-group-text">USD</span>
                            </div>
                            @error('monto_final')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Diferencia Calculada -->
                        <div id="diferencia-container" class="alert" style="display: none;">
                            <h5 class="alert-heading mb-2">
                                <i class="bx bx-calculator me-2"></i>Diferencia Calculada
                            </h5>
                            <p class="mb-0">
                                <strong id="diferencia-label"></strong>: 
                                <strong id="diferencia-monto" style="font-size: 1.5rem;"></strong>
                            </p>
                        </div>

                        <!-- Observaciones de Cierre -->
                        <div class="mb-4">
                            <label for="observaciones_cierre" class="form-label">
                                Observaciones del Cierre
                            </label>
                            <textarea class="form-control @error('observaciones_cierre') is-invalid @enderror" 
                                      id="observaciones_cierre" 
                                      name="observaciones_cierre" 
                                      rows="3"
                                      placeholder="Observaciones adicionales, explicación de diferencias, etc.">{{ old('observaciones_cierre') }}</textarea>
                            @error('observaciones_cierre')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg" id="btnCerrar" style="background-color: #ff0000; border-color: #ff0000;">
                                <i class="bx bx-lock me-2"></i>Cerrar Turno de Caja
                            </button>
                            <a href="{{ route('caja.turnos.show', $turno->id) }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const efectivoEsperado = {{ $efectivoEsperado }};

// Calcular diferencia en tiempo real
document.getElementById('monto_final').addEventListener('input', function(e) {
    const montoFinal = parseFloat(e.target.value) || 0;
    const diferencia = montoFinal - efectivoEsperado;
    const container = document.getElementById('diferencia-container');
    const label = document.getElementById('diferencia-label');
    const monto = document.getElementById('diferencia-monto');
    
    if (montoFinal > 0) {
        container.style.display = 'block';
        
        if (Math.abs(diferencia) < 0.01) {
            // Sin diferencia
            container.className = 'alert alert-success';
            label.textContent = 'Sin Diferencia';
            monto.textContent = 'Cuadra perfecto ✓';
            monto.style.color = '#28a745';
        } else if (diferencia > 0) {
            // Sobrante
            container.className = 'alert alert-info';
            label.textContent = 'Sobrante';
            monto.textContent = '+$' + diferencia.toFixed(2);
            monto.style.color = '#17a2b8';
        } else {
            // Faltante
            container.className = 'alert alert-danger';
            label.textContent = 'Faltante';
            monto.textContent = '$' + diferencia.toFixed(2);
            monto.style.color = '#dc3545';
        }
    } else {
        container.style.display = 'none';
    }
});

// Confirmar antes de cerrar con modal personalizado
let formToSubmit = null;

document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    formToSubmit = this;
    
    const montoFinal = parseFloat(document.getElementById('monto_final').value) || 0;
    const diferencia = montoFinal - efectivoEsperado;
    
    let mensaje = '<strong>¿Está seguro de cerrar el turno?</strong><br><br>';
    mensaje += 'Efectivo esperado: <strong>$' + efectivoEsperado.toFixed(2) + '</strong><br>';
    mensaje += 'Efectivo contado: <strong>$' + montoFinal.toFixed(2) + '</strong><br><br>';
    
    if (Math.abs(diferencia) < 0.01) {
        mensaje += '<span class="text-success">✓ Sin diferencia - Cuadra perfecto</span>';
    } else if (diferencia > 0) {
        mensaje += '<span class="text-info">Sobrante: <strong>+$' + diferencia.toFixed(2) + '</strong></span>';
    } else {
        mensaje += '<span class="text-warning">Faltante: <strong>$' + Math.abs(diferencia).toFixed(2) + '</strong></span>';
    }
    
    // Mostrar modal de confirmación
    const modalHtml = `
        <div class="modal fade" id="confirmCierreModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #ff0000; color: white;">
                        <h5 class="modal-title">
                            <i class="bx bx-time-five me-2"></i>Confirmar Cierre de Turno
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${mensaje}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmCierreBtn" style="background-color: #ff0000; border-color: #ff0000;">
                            <i class="bx bx-check me-1"></i>Confirmar Cierre
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Eliminar modal anterior si existe
    const oldModal = document.getElementById('confirmCierreModal');
    if (oldModal) {
        oldModal.remove();
    }
    
    // Agregar nuevo modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('confirmCierreModal'));
    
    document.getElementById('confirmCierreBtn').addEventListener('click', function() {
        modal.hide();
        // Esperar a que el modal se cierre antes de enviar el formulario
        setTimeout(() => {
            formToSubmit.submit();
        }, 300);
    });
    
    modal.show();
});

// Trigger inicial
document.getElementById('monto_final').dispatchEvent(new Event('input'));
</script>
@endpush
