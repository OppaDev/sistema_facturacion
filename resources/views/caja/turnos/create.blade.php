@extends('layouts.app')

@section('title', 'Abrir Turno de Caja')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-sm-row my-0">
                <div class="page-title-content">
                    <h4 class="mb-1">
                        <span class="text-muted fw-light">Caja / Turnos /</span> 
                        <span style="color: #ff0000;">Abrir Turno</span>
                    </h4>
                    <p class="text-muted mb-0">Apertura de turno de caja</p>
                </div>
                <div class="page-title-actions ms-auto">
                    <a href="{{ route('caja.turnos.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8 mx-auto">
            <!-- Formulario -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-time me-2"></i>Apertura de Turno de Caja
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('caja.turnos.store') }}">
                        @csrf

                        <!-- Información del Usuario -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="bx bx-user me-2"></i>Cajero:</strong>
                                    {{ auth()->user()->name }}
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="bx bx-calendar me-2"></i>Fecha:</strong>
                                    {{ now()->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>

                        <!-- Instrucciones -->
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="bx bx-info-circle me-2"></i>Instrucciones
                            </h6>
                            <ul class="mb-0">
                                <li>Cuente el efectivo disponible en caja antes de abrir el turno</li>
                                <li>El monto inicial será el punto de referencia para el cierre</li>
                                <li>Solo puede tener un turno abierto a la vez</li>
                                <li>Debe cerrar el turno al finalizar su jornada</li>
                            </ul>
                        </div>

                        <!-- Monto Inicial -->
                        <div class="mb-4">
                            <label for="monto_inicial" class="form-label">
                                <strong>Monto Inicial en Caja *</strong>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" style="background-color: #ff0000; color: white;">
                                    <i class="bx bx-dollar"></i>
                                </span>
                                <input type="number" 
                                       class="form-control @error('monto_inicial') is-invalid @enderror" 
                                       id="monto_inicial" 
                                       name="monto_inicial" 
                                       value="{{ old('monto_inicial', '100.00') }}"
                                       step="0.01" 
                                       min="0" 
                                       required 
                                       autofocus>
                                <span class="input-group-text">USD</span>
                            </div>
                            @error('monto_inicial')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Ingrese el monto de efectivo contado en caja
                            </small>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-4">
                            <label for="observaciones" class="form-label">
                                Observaciones Iniciales (Opcional)
                            </label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                      id="observaciones" 
                                      name="observaciones" 
                                      rows="3"
                                      placeholder="Ej: Billetes rotos, monedas faltantes, etc.">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg" style="background-color: #ff0000; border-color: #ff0000;">
                                <i class="bx bx-lock-open me-2"></i>Abrir Turno de Caja
                            </button>
                            <a href="{{ route('caja.turnos.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Historial Reciente -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-history me-2"></i>Mis últimos turnos
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $misUltimosTurnos = \App\Models\TurnoCaja::where('usuario_id', auth()->id())
                            ->orderBy('fecha_apertura', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @if($misUltimosTurnos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto Inicial</th>
                                    <th>Ventas</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($misUltimosTurnos as $turno)
                                <tr>
                                    <td>{{ $turno->fecha_apertura->format('d/m/Y') }}</td>
                                    <td>${{ number_format($turno->monto_inicial, 2) }}</td>
                                    <td>${{ number_format($turno->total_ventas, 2) }}</td>
                                    <td>
                                        @if($turno->isAbierto())
                                            <span class="badge bg-success">Abierto</span>
                                        @else
                                            <span class="badge bg-secondary">Cerrado</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">No tienes turnos previos</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Formatear input de monto
document.getElementById('monto_inicial').addEventListener('input', function(e) {
    let value = parseFloat(e.target.value) || 0;
    if (value < 0) {
        e.target.value = 0;
    }
});
</script>
@endpush
