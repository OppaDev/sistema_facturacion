@extends('layouts.app')

@section('title', 'Detalle de Venta #' . $venta->numero_venta)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Caja /</span> 
                <span style="color: #ff0000;">Venta #{{ $venta->numero_venta }}</span>
            </h4>
            <p class="text-muted mb-0">Detalle completo de la venta</p>
        </div>
        <div>
            <a href="{{ route('caja.index') }}" class="btn btn-outline-secondary me-2">
                <i class='bx bx-arrow-back'></i> Volver
            </a>
            <a href="{{ route('caja.ticket', $venta->id) }}" class="btn btn-outline-danger" target="_blank">
                <i class='bx bx-printer'></i> Imprimir Ticket
            </a>
            @can('anular', $venta)
                @if($venta->estado === 'completada')
                    <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#anularModal">
                        <i class='bx bx-x-circle'></i> Anular Venta
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda -->
        <div class="col-lg-8 mb-4">
            
            <!-- Información General -->
            <div class="card mb-4" style="border-left: 4px solid #ff0000;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class='bx bx-info-circle'></i> Información General</h5>
                    @if($venta->estado === 'completada')
                        <span class="badge bg-success">Completada</span>
                    @else
                        <span class="badge bg-danger">Anulada</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Número de Venta</label>
                            <div class="fw-bold" style="color: #ff0000;">{{ $venta->numero_venta }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Fecha y Hora</label>
                            <div class="fw-bold">{{ $venta->created_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Cajero</label>
                            <div class="fw-bold">{{ $venta->usuario->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Turno de Caja</label>
                            <div>
                                <a href="{{ route('caja.turnos.show', $venta->turno_id) }}" class="text-decoration-none">
                                    Turno #{{ $venta->turno_id }}
                                    <i class='bx bx-link-external'></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Cliente -->
                    @if($venta->cliente_nombre || $venta->cliente_identificacion)
                        <hr>
                        <h6 class="mb-3"><i class='bx bx-user'></i> Datos del Cliente</h6>
                        <div class="row">
                            @if($venta->cliente_nombre)
                                <div class="col-md-6 mb-2">
                                    <label class="text-muted small">Nombre</label>
                                    <div>{{ $venta->cliente_nombre }}</div>
                                </div>
                            @endif
                            @if($venta->cliente_identificacion)
                                <div class="col-md-6 mb-2">
                                    <label class="text-muted small">Identificación</label>
                                    <div>{{ $venta->cliente_identificacion }}</div>
                                </div>
                            @endif
                            @if($venta->cliente_email)
                                <div class="col-md-6 mb-2">
                                    <label class="text-muted small">Email</label>
                                    <div>{{ $venta->cliente_email }}</div>
                                </div>
                            @endif
                            @if($venta->cliente_telefono)
                                <div class="col-md-6 mb-2">
                                    <label class="text-muted small">Teléfono</label>
                                    <div>{{ $venta->cliente_telefono }}</div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-light mt-3 mb-0">
                            <i class='bx bx-info-circle'></i> Venta sin datos de cliente
                        </div>
                    @endif

                    @if($venta->motivo_anulacion)
                        <hr>
                        <div class="alert alert-danger mb-0">
                            <h6 class="alert-heading mb-2">
                                <i class='bx bx-error-circle'></i> Motivo de Anulación
                            </h6>
                            <p class="mb-0">{{ $venta->motivo_anulacion }}</p>
                            <small class="text-muted">
                                Anulada por: {{ $venta->anulada_por_usuario->name ?? 'Sistema' }} 
                                el {{ $venta->updated_at->format('d/m/Y H:i:s') }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Productos Vendidos -->
            <div class="card" style="border-left: 4px solid #ff0000;">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bx-shopping-bag'></i> Productos Vendidos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($venta->detalles as $detalle)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $detalle->producto_nombre }}</div>
                                        <small class="text-muted">Código: {{ $detalle->producto_codigo }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-primary">{{ $detalle->cantidad }}</span>
                                    </td>
                                    <td class="text-end">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                    <td class="text-end fw-bold">${{ number_format($detalle->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            
            <!-- Método de Pago -->
            <div class="card mb-4" style="border-left: 4px solid #ff0000;">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bx-credit-card'></i> Método de Pago</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($venta->tipo_pago === 'efectivo')
                            <div class="badge bg-success rounded-circle p-2 me-3">
                                <i class='bx bx-money' style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Efectivo</div>
                                <small class="text-muted">Pago en efectivo</small>
                            </div>
                        @elseif($venta->tipo_pago === 'tarjeta')
                            <div class="badge bg-primary rounded-circle p-2 me-3">
                                <i class='bx bx-credit-card' style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Tarjeta</div>
                                <small class="text-muted">Débito/Crédito</small>
                            </div>
                        @elseif($venta->tipo_pago === 'transferencia')
                            <div class="badge bg-info rounded-circle p-2 me-3">
                                <i class='bx bx-transfer' style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Transferencia</div>
                                <small class="text-muted">Bancaria</small>
                            </div>
                        @elseif($venta->tipo_pago === 'deuna')
                            <div class="badge bg-warning rounded-circle p-2 me-3">
                                <i class='bx bx-mobile-alt' style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Deuna</div>
                                <small class="text-muted">App de pago</small>
                            </div>
                        @elseif($venta->tipo_pago === 'mixto')
                            <div class="badge bg-secondary rounded-circle p-2 me-3">
                                <i class='bx bx-shuffle' style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Mixto</div>
                                <small class="text-muted">Múltiples métodos</small>
                            </div>
                        @endif
                    </div>

                    <!-- Desglose de Pago Mixto -->
                    @if($venta->tipo_pago === 'mixto' && $venta->detalle_pago_mixto)
                        <div class="alert alert-light">
                            <h6 class="mb-2">Desglose de Pagos:</h6>
                            @php
                                $desglose = json_decode($venta->detalle_pago_mixto, true);
                            @endphp
                            @foreach($desglose as $metodo => $monto)
                                @if($monto > 0)
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-capitalize">{{ $metodo }}:</span>
                                        <span class="fw-bold">${{ number_format($monto, 2) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Totales -->
            <div class="card" style="border-left: 4px solid #ff0000;">
                <div class="card-header" style="background-color: #fff5f5;">
                    <h5 class="mb-0" style="color: #ff0000;">
                        <i class='bx bx-calculator'></i> Totales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold">${{ number_format($venta->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ iva_label() }}:</span>
                        <span class="fw-bold">${{ number_format($venta->iva, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-0">
                        <h5 class="mb-0" style="color: #ff0000;">TOTAL:</h5>
                        <h4 class="mb-0 fw-bold" style="color: #ff0000;">${{ number_format($venta->total, 2) }}</h4>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Modal Anular Venta -->
<div class="modal fade" id="anularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 2px solid #ff0000;">
                <h5 class="modal-title">
                    <i class='bx bx-error-circle text-danger'></i> 
                    Anular Venta #{{ $venta->numero_venta }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('caja.anular', $venta->id) }}" method="POST" id="anularForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class='bx bx-error-circle'></i>
                        <strong>Advertencia:</strong> Esta acción no se puede deshacer. 
                        La venta quedará marcada como anulada.
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Confirma tu contraseña <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Por seguridad, ingresa tu contraseña</small>
                    </div>

                    <div class="mb-3">
                        <label for="motivo" class="form-label">
                            Motivo de la anulación <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" 
                                  required minlength="10" maxlength="500"></textarea>
                        <small class="text-muted">Mínimo 10 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class='bx bx-x-circle'></i> Anular Venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // El formulario ahora usa el modal de Bootstrap para confirmación
    // La validación se realiza dentro del modal antes de enviar el formulario
    document.getElementById('anularForm')?.addEventListener('submit', function(e) {
        // El modal de Bootstrap ya proporciona la confirmación visual
        // Solo se llega aquí si el usuario clickeó en "Anular Venta" dentro del modal
        console.log('Procesando anulación de venta...');
    });
</script>
@endpush
