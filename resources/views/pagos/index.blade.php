@extends('layouts.app')

@section('title', 'Gestión de Pagos')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Pagos /</span> Gestión de Pagos
    </h4>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-time-five text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Pendientes</span>
                    <h3 class="card-title mb-2">{{ $estadisticas['pendientes'] }}</h3>
                    <small class="text-warning">${{ number_format($estadisticas['total_monto_pendiente'], 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Aprobados</span>
                    <h3 class="card-title mb-2">{{ $estadisticas['aprobados'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-x-circle text-danger" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Rechazados</span>
                    <h3 class="card-title mb-2">{{ $estadisticas['rechazados'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-money text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Pagos</span>
                    <h3 class="card-title mb-2">{{ $estadisticas['pendientes'] + $estadisticas['aprobados'] + $estadisticas['rechazados'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pagos.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="pendiente" {{ request('estado', 'pendiente') === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                            <option value="aprobado" {{ request('estado') === 'aprobado' ? 'selected' : '' }}>Aprobados</option>
                            <option value="rechazado" {{ request('estado') === 'rechazado' ? 'selected' : '' }}>Rechazados</option>
                            <option value="todos" {{ request('estado') === 'todos' ? 'selected' : '' }}>Todos</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tipo de Pago</label>
                        <select name="tipo_pago" class="form-select">
                            <option value="">Todos</option>
                            <option value="efectivo" {{ request('tipo_pago') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="tarjeta" {{ request('tipo_pago') === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            <option value="transferencia" {{ request('tipo_pago') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="cheque" {{ request('tipo_pago') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="buscar" class="form-control" placeholder="Cliente, email, transacción..." value="{{ request('buscar') }}">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Pagos -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Lista de Pagos</h5>
            <small class="text-muted">{{ $pagos->total() }} pagos encontrados</small>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Factura</th>
                        <th>Monto</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                    <tr>
                        <td><strong>#{{ $pago->id }}</strong></td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">{{ $pago->factura->cliente->name ?? 'N/A' }}</span>
                                <small class="text-muted">{{ $pago->factura->cliente->email ?? 'N/A' }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $pago->factura->getNumeroFormateado() }}</span>
                        </td>
                        <td>
                            <strong>${{ number_format($pago->monto, 2) }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($pago->tipo_pago) }}</span>
                        </td>
                        <td>
                            @if($pago->estado === 'pendiente')
                                <span class="badge bg-warning">Pendiente</span>
                            @elseif($pago->estado === 'aprobado')
                                <span class="badge bg-success">Aprobado</span>
                            @elseif($pago->estado === 'rechazado')
                                <span class="badge bg-danger">Rechazado</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span>{{ $pago->created_at->format('d/m/Y') }}</span>
                                <small class="text-muted">{{ $pago->created_at->format('H:i') }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('pagos.show', $pago) }}">
                                        <i class="bx bx-show me-1"></i> Ver Detalles
                                    </a>
                                    
                                    @if($pago->isPendiente())
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item text-success" onclick="aprobarPago({{ $pago->id }})">
                                            <i class="bx bx-check me-1"></i> Aprobar
                                        </button>
                                        <button type="button" class="dropdown-item text-danger" onclick="rechazarPago({{ $pago->id }})">
                                            <i class="bx bx-x me-1"></i> Rechazar
                                        </button>
                                    @endif
                                    
                                    @if($pago->validadoPor)
                                        <div class="dropdown-divider"></div>
                                        <span class="dropdown-item-text">
                                            <small class="text-muted">
                                                Validado por: {{ $pago->validadoPor->name }}<br>
                                                {{ $pago->validated_at->format('d/m/Y H:i') }}
                                            </small>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bx bx-search" style="font-size: 3rem; color: #ddd;"></i>
                            <p class="mt-2 text-muted">No se encontraron pagos</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pagos->hasPages())
        <div class="card-footer">
            {{ $pagos->links() }}
        </div>
        @endif
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
                <p>¿Está seguro de que desea aprobar este pago?</p>
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
                <p>¿Está seguro de que desea rechazar este pago?</p>
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