@extends('layouts.app')

@section('title', 'Detalle de Categoría')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="page-title d-flex flex-column justify-content-center flex-sm-row align-items-center align-items-sm-start my-0 mb-3">
        <div class="page-title-content">
          <h4 class="mb-1">
            <span class="text-muted fw-light">Inventario / <a href="{{ route('categorias.index') }}" class="text-muted">Categorías</a> /</span> {{ $categoria->nombre }}
          </h4>
          <p class="text-muted mb-0">Información detallada de la categoría</p>
        </div>
        <div class="page-title-actions ms-auto mt-3 mt-sm-0">
          @if(!$categoria->trashed())
            <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-warning">
              <i class="bx bx-edit me-1"></i> Editar
            </a>
          @endif
          <a href="{{ route('categorias.index') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i> Volver
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Información Principal -->
    <div class="col-lg-8">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bx bx-info-circle me-2"></i>Información de la Categoría
          </h5>
        </div>
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-8">
              <h3 class="mb-3">{{ $categoria->nombre }}</h3>
              @if($categoria->descripcion)
                <div class="alert alert-secondary" role="alert">
                  <h6 class="alert-heading mb-2"><i class="bx bx-file-blank me-1"></i>Descripción</h6>
                  <p class="mb-0">{{ $categoria->descripcion }}</p>
                </div>
              @else
                <p class="text-muted fst-italic">Sin descripción</p>
              @endif
            </div>
            <div class="col-md-4 text-center">
              <div class="p-5 rounded shadow-sm" style="background-color: {{ $categoria->color }};">
                <span class="text-white fw-bold fs-5">{{ $categoria->color }}</span>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="card bg-label-info">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                      <span class="avatar-initial rounded bg-label-info">
                        <i class="bx bx-package fs-3"></i>
                      </span>
                    </div>
                    <div>
                      <h3 class="mb-0">{{ $categoria->productos_count }}</h3>
                      <small>Productos</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card bg-label-{{ $categoria->activo ? 'success' : 'secondary' }}">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                      <span class="avatar-initial rounded bg-label-{{ $categoria->activo ? 'success' : 'secondary' }}">
                        <i class="bx bx-{{ $categoria->activo ? 'check-circle' : 'x-circle' }} fs-3"></i>
                      </span>
                    </div>
                    <div>
                      <h3 class="mb-0">{{ $categoria->activo ? 'Activo' : 'Inactivo' }}</h3>
                      <small>Estado</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Productos de esta categoría -->
      @if($categoria->productos->count() > 0)
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="bx bx-package me-2"></i>Productos ({{ $categoria->productos->count() }})
            </h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th class="text-end">Precio</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($categoria->productos as $producto)
                    <tr>
                      <td>
                        @if($producto->imagen)
                          <img src="{{ asset('storage/' . $producto->imagen) }}" 
                               alt="{{ $producto->nombre }}" 
                               class="rounded"
                               style="width: 50px; height: 50px; object-fit: cover;">
                        @else
                          <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-label-secondary">
                              <i class="bx bx-image"></i>
                            </span>
                          </div>
                        @endif
                      </td>
                      <td>
                        <strong>{{ $producto->nombre }}</strong>
                      </td>
                      <td><code>{{ $producto->codigo }}</code></td>
                      <td class="text-end">
                        <strong>${{ number_format($producto->precio, 2) }}</strong>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-label-{{ $producto->stock > 10 ? 'success' : ($producto->stock > 0 ? 'warning' : 'danger') }}">
                          {{ $producto->stock }}
                        </span>
                      </td>
                      <td class="text-center">
                        @if($producto->activo)
                          <span class="badge bg-label-success">Activo</span>
                        @else
                          <span class="badge bg-label-secondary">Inactivo</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @else
        <div class="card">
          <div class="card-body text-center py-5">
            <div class="text-muted">
              <i class="bx bx-package" style="font-size: 3rem;"></i>
              <p class="mt-3">No hay productos en esta categoría</p>
            </div>
          </div>
        </div>
      @endif
    </div>

    <!-- Panel lateral de auditoría -->
    <div class="col-lg-4">
      <div class="card mb-4">
        <div class="card-header">
          <h6 class="mb-0">
            <i class="bx bx-time me-1"></i>Auditoría
          </h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <small class="text-muted d-block">Creado por</small>
            <strong>{{ $categoria->creador->name ?? 'Desconocido' }}</strong>
            <br>
            <small class="text-muted">{{ $categoria->created_at->format('d/m/Y H:i') }}</small>
          </div>

          @if($categoria->updated_at != $categoria->created_at)
            <div class="mb-3">
              <small class="text-muted d-block">Modificado por</small>
              <strong>{{ $categoria->modificador->name ?? 'Desconocido' }}</strong>
              <br>
              <small class="text-muted">{{ $categoria->updated_at->format('d/m/Y H:i') }}</small>
            </div>
          @endif

          @if($categoria->trashed())
            <div class="alert alert-danger p-2 mb-0" role="alert">
              <small><i class="bx bx-trash me-1"></i>Eliminado el: {{ $categoria->deleted_at->format('d/m/Y H:i') }}</small>
            </div>
          @endif
        </div>
      </div>

      <!-- Últimas auditorías -->
      @if($categoria->auditorias && $categoria->auditorias->count() > 0)
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">
              <i class="bx bx-history me-1"></i>Historial de Cambios
            </h6>
          </div>
          <div class="card-body">
            <ul class="timeline ms-2">
              @foreach($categoria->auditorias->take(5) as $auditoria)
                <li class="timeline-item timeline-item-transparent">
                  <span class="timeline-point timeline-point-{{ $auditoria->accion == 'crear' ? 'success' : ($auditoria->accion == 'eliminar' ? 'danger' : 'warning') }}"></span>
                  <div class="timeline-event">
                    <div class="timeline-header mb-1">
                      <h6 class="mb-0">{{ ucfirst($auditoria->accion) }}</h6>
                      <small class="text-muted">{{ $auditoria->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-0">
                      <small>{{ $auditoria->user->name ?? 'Usuario' }}</small>
                    </p>
                    @if($auditoria->observacion)
                      <p class="mb-0 text-muted">
                        <small>{{ $auditoria->observacion }}</small>
                      </p>
                    @endif
                  </div>
                </li>
              @endforeach
            </ul>

            @if($categoria->auditorias->count() > 5)
              <div class="text-center mt-3">
                <small class="text-muted">y {{ $categoria->auditorias->count() - 5 }} registros más...</small>
              </div>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
