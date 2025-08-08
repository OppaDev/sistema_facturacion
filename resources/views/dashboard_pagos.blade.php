@extends('layouts.app')
@section('title', 'Dashboard Pagos')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row justify-content-center mb-4">
    <div class="col-lg-12">
      <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInDown mb-4">
        <div class="card-body text-center">
          <h2 class="fw-bold mb-2" style="color: #9c27b0;">¡Bienvenido, {{ Auth::user()->name }}!</h2>
          <p class="text-muted mb-3">Panel de gestión de pagos. Valida y gestiona los pagos de clientes.</p>
        </div>
      </div>
      
      <!-- Estadísticas principales -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInLeft">
            <div class="mb-2"><i class="bx bx-time-five fs-1 text-warning"></i></div>
            <div class="fw-bold fs-4">{{ $pagosPendientes ?? 0 }}</div>
            <div class="text-muted">Pagos pendientes</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInUp">
            <div class="mb-2"><i class="bx bx-check-circle fs-1 text-success"></i></div>
            <div class="fw-bold fs-4">{{ $pagosAprobados ?? 0 }}</div>
            <div class="text-muted">Pagos aprobados</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInUp">
            <div class="mb-2"><i class="bx bx-x-circle fs-1 text-danger"></i></div>
            <div class="fw-bold fs-4">{{ $pagosRechazados ?? 0 }}</div>
            <div class="text-muted">Pagos rechazados</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInRight">
            <div class="mb-2"><i class="bx bx-calendar fs-1 text-primary"></i></div>
            <div class="fw-bold fs-4">{{ $totalPagosMes ?? 0 }}</div>
            <div class="text-muted">Pagos este mes</div>
          </div>
        </div>
      </div>

      <!-- Métricas financieras -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInLeft">
            <div class="mb-2"><i class="bx bx-dollar fs-1 text-success"></i></div>
            <div class="fw-bold fs-4">${{ number_format($montoTotalAprobado ?? 0, 2) }}</div>
            <div class="text-muted">Total aprobado</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInUp">
            <div class="mb-2"><i class="bx bx-trending-up fs-1 text-info"></i></div>
            <div class="fw-bold fs-4">${{ number_format($montoMesAprobado ?? 0, 2) }}</div>
            <div class="text-muted">Aprobado este mes</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0 rounded-3 text-center p-4 animate__animated animate__fadeInRight">
            <div class="mb-2"><i class="bx bx-stopwatch fs-1 text-warning"></i></div>
            <div class="fw-bold fs-4">{{ $tiempoPromedioValidacion ?? 0 }}h</div>
            <div class="text-muted">Tiempo promedio validación</div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <!-- Accesos rápidos -->
        <div class="col-md-6">
          <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-transparent">
              <h5 class="fw-bold mb-0"><i class="bx bx-credit-card me-2"></i>Accesos Rápidos</h5>
            </div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-6">
                  <a href="{{ route('pagos.index') }}" class="text-decoration-none">
                    <div class="card bg-gradient-primary text-white text-center p-3 h-100">
                      <div class="mb-2"><i class="bx bx-list-ul fs-2"></i></div>
                      <small class="fw-bold">Ver todos los pagos</small>
                    </div>
                  </a>
                </div>
                <div class="col-6">
                  <a href="{{ route('pagos.index', ['estado' => 'pendiente']) }}" class="text-decoration-none">
                    <div class="card bg-gradient-warning text-white text-center p-3 h-100">
                      <div class="mb-2">
                        <i class="bx bx-time-five fs-2"></i>
                        @if($pagosPendientes > 0)
                          <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $pagosPendientes }}</span>
                        @endif
                      </div>
                      <small class="fw-bold">Pagos pendientes</small>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Estadísticas por tipo de pago -->
          <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-transparent">
              <h5 class="fw-bold mb-0"><i class="bx bx-chart me-2"></i>Tipos de Pago</h5>
            </div>
            <div class="card-body">
              @if($pagosPorTipo && $pagosPorTipo->count() > 0)
                @foreach($pagosPorTipo as $tipo)
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                      <i class="bx 
                        @if($tipo->tipo_pago == 'efectivo') bx-money 
                        @elseif($tipo->tipo_pago == 'tarjeta') bx-credit-card 
                        @elseif($tipo->tipo_pago == 'transferencia') bx-transfer-alt 
                        @elseif($tipo->tipo_pago == 'cheque') bx-receipt 
                        @else bx-dollar @endif me-2"></i>
                      <span class="text-capitalize">{{ ucfirst($tipo->tipo_pago) }}</span>
                    </div>
                    <div class="text-end">
                      <div class="fw-bold">${{ number_format($tipo->monto_total, 2) }}</div>
                      <small class="text-muted">{{ $tipo->total }} pagos</small>
                    </div>
                  </div>
                @endforeach
              @else
                <p class="text-muted text-center">No hay datos de tipos de pago</p>
              @endif
            </div>
          </div>
        </div>

        <!-- Pagos recientes -->
        <div class="col-md-6">
          <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
              <h5 class="fw-bold mb-0"><i class="bx bx-history me-2"></i>Pagos Recientes</h5>
              <a href="{{ route('pagos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body">
              @if($pagosRecientes && $pagosRecientes->count() > 0)
                <div class="table-responsive">
                  <table class="table table-sm">
                    <tbody>
                      @foreach($pagosRecientes->take(8) as $pago)
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="avatar-sm me-2">
                                @if($pago->estado == 'pendiente')
                                  <i class="bx bx-time-five text-warning fs-5"></i>
                                @elseif($pago->estado == 'aprobado')
                                  <i class="bx bx-check-circle text-success fs-5"></i>
                                @else
                                  <i class="bx bx-x-circle text-danger fs-5"></i>
                                @endif
                              </div>
                              <div>
                                <div class="fw-bold small">{{ $pago->factura->cliente->name ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ ucfirst($pago->tipo_pago) }}</div>
                              </div>
                            </div>
                          </td>
                          <td class="text-end">
                            <div class="fw-bold small">${{ number_format($pago->monto, 2) }}</div>
                            <div class="text-muted small">{{ $pago->created_at->format('d/m H:i') }}</div>
                          </td>
                          <td>
                            <a href="{{ route('pagos.show', $pago) }}" class="btn btn-sm btn-ghost-primary">
                              <i class="bx bx-show"></i>
                            </a>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <div class="text-center text-muted py-4">
                  <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                  <p>No hay pagos recientes</p>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection