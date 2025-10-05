@extends('layouts.app')
@section('title', 'Dashboard Ventas')

@push('styles')
<style>
  .stat-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255, 0, 0, 0.15) !important;
  }
  .action-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
  }
  .action-card:hover {
    transform: translateY(-5px);
    border-color: #ff0000;
    box-shadow: 0 8px 20px rgba(255, 0, 0, 0.2) !important;
  }
  .turno-badge {
    animation: pulse 2s infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
  }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header -->
  <div class="row justify-content-center mb-4">
    <div class="col-lg-10">
      <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInDown mb-4" style="border-left: 5px solid #ff0000;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="fw-bold mb-2" style="color: #ff0000;">¡Bienvenido, {{ Auth::user()->name }}!</h2>
              <p class="text-muted mb-0">Panel de Punto de Venta. Gestiona tus ventas y consulta estadísticas del mes.</p>
            </div>
            @if($turnoAbierto)
              <div class="text-end">
                <span class="badge bg-success turno-badge fs-6">
                  <i class="bx bx-check-circle me-1"></i>Turno Abierto
                </span>
                <div class="text-muted small mt-1">
                  Desde: {{ $turnoAbierto->fecha_apertura->format('H:i') }}
                </div>
              </div>
            @else
              <div class="text-end">
                <span class="badge bg-danger fs-6">
                  <i class="bx bx-x-circle me-1"></i>Sin Turno
                </span>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Estadísticas del Mes -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card stat-card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInLeft">
            <div class="mb-2"><i class="bx bx-receipt fs-1" style="color: #ff0000;"></i></div>
            <div class="fw-bold fs-3" style="color: #ff0000;">{{ $ventasMes ?? 0 }}</div>
            <div class="text-muted">Ventas del Mes</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stat-card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInUp">
            <div class="mb-2"><i class="bx bx-dollar-circle fs-1 text-success"></i></div>
            <div class="fw-bold fs-3 text-success">${{ number_format($totalRecaudado ?? 0, 2) }}</div>
            <div class="text-muted">Total Recaudado</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stat-card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInRight">
            <div class="mb-2"><i class="bx bx-trending-up fs-1 text-primary"></i></div>
            <div class="fw-bold fs-3 text-primary">${{ number_format($ticketPromedio ?? 0, 2) }}</div>
            <div class="text-muted">Ticket Promedio</div>
          </div>
        </div>
      </div>

      @if($turnoAbierto)
      <!-- Estadísticas del Turno Actual -->
      <div class="card shadow-sm border-0 rounded-3 mb-4 animate__animated animate__fadeIn" style="border-left: 3px solid #ff0000;">
        <div class="card-header" style="background-color: #fff;">
          <h5 class="mb-0" style="color: #ff0000;">
            <i class="bx bx-time-five me-2"></i>Turno Actual #{{ $turnoAbierto->id }}
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 text-center">
              <div class="mb-2">
                <i class="bx bx-receipt fs-2" style="color: #ff0000;"></i>
              </div>
              <div class="fw-bold fs-4">{{ $ventasTurno }}</div>
              <div class="text-muted small">Ventas</div>
            </div>
            <div class="col-md-4 text-center">
              <div class="mb-2">
                <i class="bx bx-dollar fs-2 text-success"></i>
              </div>
              <div class="fw-bold fs-4">${{ number_format($totalTurno, 2) }}</div>
              <div class="text-muted small">Recaudado</div>
            </div>
            <div class="col-md-4 text-center">
              <div class="mb-2">
                <i class="bx bx-wallet fs-2 text-info"></i>
              </div>
              <div class="fw-bold fs-4">${{ number_format($turnoAbierto->monto_inicial, 2) }}</div>
              <div class="text-muted small">Monto Inicial</div>
            </div>
          </div>
        </div>
      </div>
      @endif

      <!-- Top Productos -->
      @if($topProductos && $topProductos->count() > 0)
      <div class="card shadow-sm border-0 rounded-3 mb-4 animate__animated animate__fadeIn">
        <div class="card-header" style="background-color: #fff;">
          <h5 class="mb-0">
            <i class="bx bx-trophy me-2 text-warning"></i>Top 5 Productos Más Vendidos del Mes
          </h5>
        </div>
        <div class="card-body">
          <div class="list-group list-group-flush">
            @foreach($topProductos as $index => $item)
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <span class="badge bg-label-primary rounded-pill me-2">{{ $index + 1 }}</span>
                  <strong>{{ $item->producto->nombre ?? 'Producto eliminado' }}</strong>
                </div>
                <span class="badge" style="background-color: #ff0000;">
                  {{ $item->total_vendido }} unidades
                </span>
              </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif

      <!-- Acciones Rápidas -->
      <div class="row g-3">
        @if($turnoAbierto)
          <div class="col-md-4">
            <a href="{{ route('caja.pos') }}" class="text-decoration-none">
              <div class="card action-card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInLeft h-100">
                <div class="mb-3"><i class="bx bx-cart-alt bx-tada fs-1" style="color: #ff0000;"></i></div>
                <h5 class="fw-bold mb-2" style="color: #ff0000;">Punto de Venta</h5>
                <p class="text-muted mb-0">Registra ventas de manera rápida y eficiente.</p>
              </div>
            </a>
          </div>
          <div class="col-md-4">
            <a href="{{ route('caja.index') }}" class="text-decoration-none">
              <div class="card action-card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInUp h-100">
                <div class="mb-3"><i class="bx bx-list-ul fs-1 text-primary"></i></div>
                <h5 class="fw-bold mb-2">Historial de Ventas</h5>
                <p class="text-muted mb-0">Consulta todas las ventas realizadas.</p>
              </div>
            </a>
          </div>
          <div class="col-md-4">
            <a href="{{ route('caja.turnos.show', $turnoAbierto->id) }}" class="text-decoration-none">
              <div class="card action-card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInRight h-100">
                <div class="mb-3"><i class="bx bx-time-five fs-1 text-success"></i></div>
                <h5 class="fw-bold mb-2">Ver Turno Actual</h5>
                <p class="text-muted mb-0">Consulta el detalle de tu turno.</p>
              </div>
            </a>
          </div>
        @else
          <div class="col-md-6">
            <a href="{{ route('caja.turnos.create') }}" class="text-decoration-none">
              <div class="card action-card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInLeft h-100">
                <div class="mb-3"><i class="bx bx-play-circle bx-tada fs-1" style="color: #ff0000;"></i></div>
                <h5 class="fw-bold mb-2" style="color: #ff0000;">Abrir Turno</h5>
                <p class="text-muted mb-0">Inicia tu turno de caja para comenzar a vender.</p>
              </div>
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('caja.turnos.index') }}" class="text-decoration-none">
              <div class="card action-card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInRight h-100">
                <div class="mb-3"><i class="bx bx-history fs-1 text-primary"></i></div>
                <h5 class="fw-bold mb-2">Historial de Turnos</h5>
                <p class="text-muted mb-0">Consulta el historial de turnos anteriores.</p>
              </div>
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection 