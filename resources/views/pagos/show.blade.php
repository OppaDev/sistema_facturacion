@extends('layouts.app')

@section('title', 'Detalle del Pago #' . $pago->id)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Pagos /</span> Detalle del Pago #{{ $pago->id }}
        </h4>
        <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Volver
        </a>
    </div>

    <div class="row">
        <!-- Información del Pago -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Información del Pago</h5>
                    @if($pago->estado === 'pendiente')
                        <span class="badge bg-warning">Pendiente</span>
                    @elseif($pago->estado === 'aprobado')
                        <span class="badge bg-success">Aprobado</span>
                    @elseif($pago->estado === 'rechazado')
                        <span class="badge bg-danger">Rechazado</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Información Básica</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>ID del Pago:</strong></td>
                                    <td>#{{ $pago->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Monto:</strong></td>
                                    <td>${{ number_format($pago->monto, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo de Pago:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($pago->tipo_pago) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Número de Transacción:</strong></td>
                                    <td>{{ $pago->numero_transaccion ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha de Registro:</strong></td>
                                    <td>{{ $pago->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Cliente</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $pago->factura->cliente->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $pago->factura->cliente->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pagado por:</strong></td>
                                    <td>{{ $pago->pagadoPor->name ?? 'N/A' }}</td>
                                </tr>
                            </table>

                            @if($pago->validadoPor)
                            <h6 class="text-muted mt-3">Validación</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Validado por:</strong></td>
                                    <td>{{ $pago->validadoPor->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha de Validación:</strong></td>
                                    <td>{{ $pago->validated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                            @endif
                        </div>
                    </div>

                    @if($pago->observacion)
                    <div class="mt-3">
                        <h6 class="text-muted">Observaciones</h6>
                        <div class="alert alert-info">
                            {!! nl2br(e($pago->observacion)) !!}
                        </div>
                    </div>
                    @endif
                </div>

                @if($pago->isPendiente())
                <div class="card-footer">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="aprobarPago({{ $pago->id }})">
                            <i class="bx bx-check"></i> Aprobar Pago
                        </button>
                        <button type="button" class="btn btn-danger" onclick="rechazarPago({{ $pago->id }})">
                            <i class="bx bx-x"></i> Rechazar Pago
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Información de la Factura -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Factura Asociada</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="badge bg-primary fs-6">{{ $pago->factura->getNumeroFormateado() }}</span>
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Estado:</strong></td>
                            <td>
                                @if($pago->factura->estado === 'pendiente')
                                    <span class="badge bg-warning">Pendiente</span>
                                @elseif($pago->factura->estado === 'pagada')
                                    <span class="badge bg-success">Pagada</span>
                                @elseif($pago->factura->estado === 'anulada')
                                    <span class="badge bg-danger">Anulada</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Subtotal:</strong></td>
                            <td>${{ number_format($pago->factura->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>IVA:</strong></td>
                            <td>${{ number_format($pago->factura->iva, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total:</strong></td>
                            <td><strong>${{ number_format($pago->factura->total, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Fecha Factura:</strong></td>
                            <td>{{ $pago->factura->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="{{ route('facturas.show', $pago->factura) }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bx bx-show"></i> Ver Factura Completa
                        </a>
                    </div>
                </div>
            </div>

            <!-- Productos de la Factura -->
            @if($pago->factura->detalles->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Productos ({{ $pago->factura->detalles->count() }})</h5>
                </div>
                <div class="card-body">
                    @foreach($pago->factura->detalles as $detalle)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <div class="fw-semibold">{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</div>
                            <small class="text-muted">Cantidad: {{ $detalle->cantidad }}</small>
                        </div>
                        <div class="text-end">
                            <div>${{ number_format($detalle->subtotal, 2) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para aprobar pago -->
<div class="modal fade" id="aprobarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aprobar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea aprobar este pago por <strong>${{ number_format($pago->monto, 2) }}</strong>?</p>
                <p><small class="text-muted">La factura será marcada como pagada automáticamente.</small></p>
            </div>
            <div class="modal-footer">
                <form id="aprobarForm" method="POST">
                    @csrf
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Aprobar Pago</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para rechazar pago -->
<div class="modal fade" id="rechazarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rechazar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea rechazar este pago por <strong>${{ number_format($pago->monto, 2) }}</strong>?</p>
                <div class="mb-3">
                    <label for="motivo_rechazo" class="form-label">Motivo del rechazo (opcional)</label>
                    <textarea class="form-control" id="motivo_rechazo" name="motivo_rechazo" rows="3" placeholder="Explique el motivo del rechazo..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <form id="rechazarForm" method="POST">
                    @csrf
                    <input type="hidden" name="motivo_rechazo" id="motivo_rechazo_hidden">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar Pago</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function aprobarPago(pagoId) {
    const form = document.getElementById('aprobarForm');
    form.action = `/pagos/${pagoId}/aprobar`;
    
    const modal = new bootstrap.Modal(document.getElementById('aprobarModal'));
    modal.show();
}

function rechazarPago(pagoId) {
    const form = document.getElementById('rechazarForm');
    form.action = `/pagos/${pagoId}/rechazar`;
    
    const modal = new bootstrap.Modal(document.getElementById('rechazarModal'));
    modal.show();
    
    // Sincronizar el textarea con el hidden input
    document.getElementById('motivo_rechazo').addEventListener('input', function() {
        document.getElementById('motivo_rechazo_hidden').value = this.value;
    });
}
</script>
@endpush