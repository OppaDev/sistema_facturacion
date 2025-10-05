@extends('layouts.app')

@section('title', 'Turnos de Caja')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-sm-row my-0">
                <div class="page-title-content">
                    <h4 class="mb-1">
                        <span class="text-muted fw-light">Caja /</span> 
                        <span style="color: #ff0000;">Turnos</span>
                    </h4>
                    <p class="text-muted mb-0">Gestión de turnos de caja</p>
                </div>
                <div class="page-title-actions ms-auto">
                    @if(!$turnoAbierto)
                    <a href="{{ route('caja.turnos.create') }}" class="btn btn-danger" style="background-color: #ff0000; border-color: #ff0000;">
                        <i class="bx bx-time me-1"></i> Abrir Turno
                    </a>
                    @else
                    <a href="{{ route('caja.pos') }}" class="btn btn-success me-2">
                        <i class="bx bx-cart me-1"></i> Ir a POS
                    </a>
                    <a href="{{ route('caja.turnos.show', $turnoAbierto->id) }}" class="btn btn-primary">
                        <i class="bx bx-show me-1"></i> Ver Turno Actual
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estado del Turno Actual -->
    @if($turnoAbierto)
    <div class="alert alert-success mt-4" style="border-left: 4px solid #28a745;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="alert-heading mb-2">
                    <i class="bx bx-check-circle me-2"></i>Turno Abierto
                </h5>
                <p class="mb-1">
                    <strong>Turno #{{ $turnoAbierto->id }}</strong> - 
                    Abierto desde: {{ $turnoAbierto->fecha_apertura->format('d/m/Y H:i') }}
                </p>
                <p class="mb-0 text-muted">
                    Monto inicial: ${{ number_format($turnoAbierto->monto_inicial, 2) }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('caja.turnos.cierre', $turnoAbierto->id) }}" class="btn btn-warning">
                    <i class="bx bx-lock me-1"></i>Cerrar Turno
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros -->
    <div class="card mt-4 mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="abierto" {{ request('estado') == 'abierto' ? 'selected' : '' }}>Abiertos</option>
                            <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrados</option>
                        </select>
                    </div>
                    @if(auth()->user()->hasRole('Administrador') && $usuarios->count() > 0)
                    <div class="col-md-3">
                        <select name="usuario_id" class="form-select">
                            <option value="">Todos los cajeros</option>
                            @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2">
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-search me-1"></i>Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Turnos -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bx bx-list-ul me-2"></i>Historial de Turnos
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cajero</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Monto Inicial</th>
                            <th>Total Ventas</th>
                            <th>Diferencia</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turnos as $turno)
                        <tr>
                            <td><strong>{{ $turno->id }}</strong></td>
                            <td>{{ $turno->usuario->name }}</td>
                            <td>{{ $turno->fecha_apertura->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($turno->fecha_cierre)
                                    {{ $turno->fecha_cierre->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>${{ number_format($turno->monto_inicial, 2) }}</td>
                            <td>
                                @if($turno->total_ventas > 0)
                                    <strong style="color: #28a745;">${{ number_format($turno->total_ventas, 2) }}</strong>
                                @else
                                    <span class="text-muted">$0.00</span>
                                @endif
                            </td>
                            <td>
                                @if($turno->isCerrado() && $turno->diferencia != 0)
                                    @if($turno->diferencia > 0)
                                        <span class="badge bg-success">
                                            +${{ number_format($turno->diferencia, 2) }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            ${{ number_format($turno->diferencia, 2) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($turno->isAbierto())
                                    <span class="badge bg-success">
                                        <i class="bx bx-lock-open me-1"></i>Abierto
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bx bx-lock me-1"></i>Cerrado
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('caja.turnos.show', $turno->id) }}">
                                                <i class="bx bx-show me-2"></i>Ver Detalle
                                            </a>
                                        </li>
                                        @if($turno->isAbierto())
                                        @can('update', $turno)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('caja.turnos.cierre', $turno->id) }}">
                                                <i class="bx bx-lock me-2"></i>Cerrar Turno
                                            </a>
                                        </li>
                                        @endcan
                                        @else
                                        <li>
                                            <a class="dropdown-item" href="{{ route('caja.turnos.reporte', $turno->id) }}" target="_blank">
                                                <i class="bx bx-printer me-2"></i>Reporte PDF
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bx bx-time bx-lg text-muted mb-3"></i>
                                <p class="text-muted">No se encontraron turnos</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($turnos->hasPages())
            <div class="mt-4">
                {{ $turnos->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
