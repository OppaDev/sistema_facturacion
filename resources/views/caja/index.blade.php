@extends('layouts.app')

@section('title', 'Ventas - Caja')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-sm-row my-0">
                <div class="page-title-content">
                    <h4 class="mb-1">
                        <span class="text-muted fw-light">Caja /</span> 
                        <span style="color: #ff0000;">Ventas</span>
                    </h4>
                    <p class="text-muted mb-0">Gestión de ventas en punto de venta</p>
                </div>
                <div class="page-title-actions ms-auto">
                    <a href="{{ route('caja.pos') }}" class="btn btn-danger" style="background-color: #ff0000; border-color: #ff0000;">
                        <i class="bx bx-cart me-1"></i> Punto de Venta
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del Día -->
    <div class="row mt-4 mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card" style="border-left: 3px solid #ff0000;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1">Ventas de Hoy</p>
                            <h4 class="mb-0">{{ $stats['cantidad_ventas_hoy'] }}</h4>
                        </div>
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="bx bx-receipt bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card" style="border-left: 3px solid #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1">Total del Día</p>
                            <h4 class="mb-0">${{ number_format($stats['total_ventas_hoy'], 2) }}</h4>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="bx bx-dollar bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1 small">Efectivo</p>
                            <h6 class="mb-0">${{ number_format($stats['efectivo_hoy'], 2) }}</h6>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="bx bx-wallet bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1 small">Tarjeta</p>
                            <h6 class="mb-0">${{ number_format($stats['tarjeta_hoy'], 2) }}</h6>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="bx bx-credit-card bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1 small">Otros</p>
                            <h6 class="mb-0">${{ number_format($stats['transferencia_hoy'] + $stats['deuna_hoy'], 2) }}</h6>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="bx bx-mobile bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Tabla -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bx bx-list-ul me-2"></i>Lista de Ventas
            </h5>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" 
                               name="buscar" 
                               class="form-control" 
                               placeholder="Buscar por número o cliente..."
                               value="{{ request('buscar') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completadas</option>
                            <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anuladas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="tipo_pago" class="form-select">
                            <option value="">Todos los pagos</option>
                            <option value="efectivo" {{ request('tipo_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="tarjeta" {{ request('tipo_pago') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            <option value="transferencia" {{ request('tipo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="deuna" {{ request('tipo_pago') == 'deuna' ? 'selected' : '' }}>Deuna</option>
                            <option value="mixto" {{ request('tipo_pago') == 'mixto' ? 'selected' : '' }}>Mixto</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Método Pago</th>
                            <th>Total</th>
                            <th>Cajero</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas as $venta)
                        <tr>
                            <td>
                                <strong style="color: #ff0000;">{{ $venta->getNumeroFormateado() }}</strong>
                            </td>
                            <td>{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($venta->cliente_nombre)
                                    {{ $venta->cliente_nombre }}
                                    @if($venta->cliente_identificacion)
                                    <br><small class="text-muted">{{ $venta->cliente_identificacion }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badges = [
                                        'efectivo' => ['bg-success', 'bx-wallet'],
                                        'tarjeta' => ['bg-primary', 'bx-credit-card'],
                                        'transferencia' => ['bg-info', 'bx-transfer'],
                                        'deuna' => ['bg-warning', 'bx-mobile'],
                                        'mixto' => ['bg-secondary', 'bx-dollar']
                                    ];
                                    $badge = $badges[$venta->tipo_pago] ?? ['bg-secondary', 'bx-question-mark'];
                                @endphp
                                <span class="badge {{ $badge[0] }}">
                                    <i class="bx {{ $badge[1] }} me-1"></i>
                                    {{ ucfirst($venta->tipo_pago) }}
                                </span>
                            </td>
                            <td>
                                <strong>${{ number_format($venta->total, 2) }}</strong>
                            </td>
                            <td>{{ $venta->usuario->name }}</td>
                            <td>
                                @if($venta->trashed())
                                    <span class="badge bg-danger">Anulada</span>
                                @else
                                    <span class="badge bg-success">Completada</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('caja.show', $venta->id) }}">
                                                <i class="bx bx-show me-2"></i>Ver Detalle
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('caja.ticket', $venta->id) }}" target="_blank">
                                                <i class="bx bx-printer me-2"></i>Imprimir Ticket
                                            </a>
                                        </li>
                                        @if(!$venta->trashed() && $venta->fecha_venta->diffInHours(now()) < 24)
                                        @can('delete', $venta)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="anularVenta({{ $venta->id }})">
                                                <i class="bx bx-trash me-2"></i>Anular Venta
                                            </a>
                                        </li>
                                        @endcan
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-receipt bx-lg text-muted mb-3"></i>
                                <p class="text-muted">No se encontraron ventas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($ventas->hasPages())
            <div class="mt-4">
                {{ $ventas->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Anular Venta -->
<div class="modal fade" id="anularVentaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bx bx-trash me-2"></i>Anular Venta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="anularVentaForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>
                        Esta acción restaurará el stock de los productos vendidos.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="password" required>
                        <small class="text-muted">Ingrese su contraseña para confirmar</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo de Anulación *</label>
                        <textarea class="form-control" name="motivo_anulacion" rows="3" required minlength="10"></textarea>
                        <small class="text-muted">Mínimo 10 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-check me-1"></i>Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function anularVenta(ventaId) {
    const form = document.getElementById('anularVentaForm');
    form.action = '/caja/' + ventaId + '/anular';
    const modal = new bootstrap.Modal(document.getElementById('anularVentaModal'));
    modal.show();
}
</script>
@endpush
