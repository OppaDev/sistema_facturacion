@extends('layouts.app')

@section('title', 'Gestión de Categorías')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="page-title d-flex flex-column justify-content-center flex-sm-row align-items-center align-items-sm-start my-0 mb-3">
        <div class="page-title-content">
          <h4 class="mb-1">
            <span class="text-muted fw-light">Inventario /</span> Categorías
          </h4>
          <p class="text-muted mb-0">Gestión de categorías de productos</p>
        </div>
        <div class="page-title-actions ms-auto mt-3 mt-sm-0">
          @if(!request('eliminados'))
            <a href="{{ route('categorias.create') }}" class="btn btn-primary">
              <i class="bx bx-plus me-1"></i> Nueva Categoría
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs y Filtros -->
  <div class="card">
    <div class="card-body">
      <!-- Tabs -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="btn-group" role="group">
          <a href="{{ route('categorias.index', request()->except('eliminados')) }}" 
             class="btn btn-outline-primary {{ !request('eliminados') ? 'active' : '' }}">
            <i class="bx bx-check-circle me-1"></i> Activas
            <span class="badge bg-label-primary ms-1">{{ $totalActivas }}</span>
          </a>
          <a href="{{ route('categorias.index', array_merge(request()->query(), ['eliminados' => 1])) }}" 
             class="btn btn-outline-danger {{ request('eliminados') ? 'active' : '' }}">
            <i class="bx bx-trash me-1"></i> Eliminadas
            <span class="badge bg-label-danger ms-1">{{ $totalEliminadas }}</span>
          </a>
        </div>
      </div>

      <!-- Filtros -->
      <div class="card bg-light border-0 mb-3">
        <div class="card-body">
          <form method="GET" action="{{ route('categorias.index') }}" class="row g-3">
            @if(request('eliminados'))
              <input type="hidden" name="eliminados" value="1">
            @endif
            
            <div class="col-md-4">
              <label for="buscar" class="form-label">Buscar</label>
              <input type="text" name="buscar" id="buscar" class="form-control" 
                     placeholder="Nombre o descripción..." value="{{ request('buscar') }}">
            </div>
            
            @if(!request('eliminados'))
              <div class="col-md-3">
                <label for="activo" class="form-label">Estado</label>
                <select name="activo" id="activo" class="form-select">
                  <option value="">Todos</option>
                  <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activo</option>
                  <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                </select>
              </div>
            @endif
            
            <div class="col-md-2">
              <label for="per_page" class="form-label">Por página</label>
              <select name="per_page" id="per_page" class="form-select">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
              </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
              <button type="submit" class="btn btn-primary flex-fill">
                <i class="bx bx-search me-1"></i> Buscar
              </button>
              <a href="{{ route('categorias.index', request('eliminados') ? ['eliminados' => 1] : []) }}" 
                 class="btn btn-label-secondary">
                <i class="bx bx-reset"></i>
              </a>
            </div>
          </form>
        </div>
      </div>

      @if(!request('eliminados'))
        <!-- Tabla Categorías Activas -->
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Color</th>
                <th class="text-center">Productos</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($categorias as $categoria)
                <tr>
                  <td><strong>{{ $categoria->nombre }}</strong></td>
                  <td>
                    @if($categoria->descripcion)
                      {{ Str::limit($categoria->descripcion, 50) }}
                    @else
                      <span class="text-muted fst-italic">Sin descripción</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge" style="background-color: {{ $categoria->color }}; color: #fff;">
                      {{ $categoria->color }}
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-label-info">{{ $categoria->productos_count }}</span>
                  </td>
                  <td class="text-center">
                    @if($categoria->activo)
                      <span class="badge bg-label-success">
                        <i class="bx bx-check me-1"></i>Activo
                      </span>
                    @else
                      <span class="badge bg-label-secondary">
                        <i class="bx bx-x me-1"></i>Inactivo
                      </span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="dropdown">
                      <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" 
                              data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item" href="{{ route('categorias.show', $categoria) }}">
                            <i class="bx bx-show me-2"></i>Ver Detalles
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item" href="{{ route('categorias.edit', $categoria) }}">
                            <i class="bx bx-edit me-2"></i>Editar
                          </a>
                        </li>
                        <li>
                          <hr class="dropdown-divider">
                        </li>
                        <li>
                          <a class="dropdown-item text-danger" href="javascript:void(0);" 
                             data-bs-toggle="modal" data-bs-target="#modalEliminar{{ $categoria->id }}">
                            <i class="bx bx-trash me-2"></i>Eliminar
                          </a>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>

                <!-- Modal Eliminar -->
                <div class="modal fade" id="modalEliminar{{ $categoria->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('categorias.destroy', $categoria) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                          <h5 class="modal-title">
                            <i class="bx bx-error me-2 text-danger"></i> Eliminar Categoría
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="bx bx-error-circle fs-4 me-2"></i>
                            <div>
                              ¿Estás seguro de eliminar la categoría <strong>"{{ $categoria->nombre }}"</strong>?
                            </div>
                          </div>
                          
                          @if($categoria->productos_count > 0)
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                              <i class="bx bx-info-circle fs-4 me-2"></i>
                              <div>
                                Esta categoría tiene <strong>{{ $categoria->productos_count }} producto(s)</strong> asociados.
                                No podrás eliminarla hasta que reasignes o elimines los productos.
                              </div>
                            </div>
                          @endif
                          
                          <div class="mb-3">
                            <label for="password{{ $categoria->id }}" class="form-label">
                              Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" id="password{{ $categoria->id }}" 
                                   class="form-control" required>
                          </div>
                          
                          <div class="mb-3">
                            <label for="observacion{{ $categoria->id }}" class="form-label">
                              Motivo <span class="text-danger">*</span>
                            </label>
                            <select name="observacion" id="observacion{{ $categoria->id }}" 
                                    class="form-select" required>
                              <option value="">Selecciona un motivo</option>
                              <option value="Categoría sin uso">Categoría sin uso</option>
                              <option value="Reorganización de categorías">Reorganización de categorías</option>
                              <option value="Error en creación">Error en creación</option>
                              <option value="Duplicada">Duplicada</option>
                              <option value="Otro">Otro</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            Cancelar
                          </button>
                          <button type="submit" class="btn btn-danger" {{ $categoria->productos_count > 0 ? 'disabled' : '' }}>
                            <i class="bx bx-trash me-1"></i> Eliminar
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                      <i class="bx bx-package" style="font-size: 3rem;"></i>
                      <p class="mt-3">No hay categorías registradas</p>
                      <a href="{{ route('categorias.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Crear Primera Categoría
                      </a>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      @else
        <!-- Tabla Categorías Eliminadas -->
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th class="text-center">Productos</th>
                <th>Eliminado el</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($categorias as $categoria)
                <tr>
                  <td><strong>{{ $categoria->nombre }}</strong></td>
                  <td>{{ $categoria->descripcion ?? 'Sin descripción' }}</td>
                  <td class="text-center">
                    <span class="badge bg-label-info">{{ $categoria->productos_count }}</span>
                  </td>
                  <td>{{ $categoria->deleted_at->format('d/m/Y H:i') }}</td>
                  <td class="text-center">
                    <div class="dropdown">
                      <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" 
                              data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item text-success" href="javascript:void(0);" 
                             data-bs-toggle="modal" data-bs-target="#modalRestaurar{{ $categoria->id }}">
                            <i class="bx bx-revision me-2"></i>Restaurar
                          </a>
                        </li>
                        <li>
                          <hr class="dropdown-divider">
                        </li>
                        <li>
                          <a class="dropdown-item text-danger" href="javascript:void(0);" 
                             data-bs-toggle="modal" data-bs-target="#modalEliminarDefinitivo{{ $categoria->id }}">
                            <i class="bx bx-trash me-2"></i>Eliminar Definitivamente
                          </a>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>

                <!-- Modal Restaurar -->
                <div class="modal fade" id="modalRestaurar{{ $categoria->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('categorias.restore', $categoria->id) }}">
                        @csrf
                        <div class="modal-header">
                          <h5 class="modal-title">
                            <i class="bx bx-revision me-2 text-success"></i> Restaurar Categoría
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <p>¿Restaurar la categoría <strong>"{{ $categoria->nombre }}"</strong>?</p>
                          
                          <div class="mb-3">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                          </div>
                          
                          <div class="mb-3">
                            <label class="form-label">Motivo <span class="text-danger">*</span></label>
                            <select name="observacion" class="form-select" required>
                              <option value="">Selecciona un motivo</option>
                              <option value="Categoría necesaria nuevamente">Categoría necesaria nuevamente</option>
                              <option value="Error en eliminación">Error en eliminación</option>
                              <option value="Otro">Otro</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-success">
                            <i class="bx bx-revision me-1"></i> Restaurar
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Modal Eliminar Definitivamente -->
                <div class="modal fade" id="modalEliminarDefinitivo{{ $categoria->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('categorias.forceDelete', $categoria->id) }}">
                        @csrf
                        <div class="modal-header">
                          <h5 class="modal-title">
                            <i class="bx bx-error me-2 text-danger"></i> Eliminar Definitivamente
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="bx bx-error-circle fs-4 me-2"></i>
                            <div>
                              <strong>¡ADVERTENCIA!</strong> Esta acción NO se puede deshacer.
                            </div>
                          </div>
                          
                          <p>¿Eliminar permanentemente <strong>"{{ $categoria->nombre }}"</strong>?</p>
                          
                          @if($categoria->productos_count > 0)
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                              <i class="bx bx-info-circle fs-4 me-2"></i>
                              <div>
                                Esta categoría tiene productos asociados y no puede eliminarse.
                              </div>
                            </div>
                          @endif
                          
                          <div class="mb-3">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                          </div>
                          
                          <div class="mb-3">
                            <label class="form-label">Motivo <span class="text-danger">*</span></label>
                            <select name="observacion" class="form-select" required>
                              <option value="">Selecciona un motivo</option>
                              <option value="Limpieza de base de datos">Limpieza de base de datos</option>
                              <option value="Error irreversible">Error irreversible</option>
                              <option value="Otro">Otro</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-danger" {{ $categoria->productos_count > 0 ? 'disabled' : '' }}>
                            <i class="bx bx-trash me-1"></i> Eliminar Definitivamente
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-5">
                    <div class="text-muted">
                      <i class="bx bx-package" style="font-size: 3rem;"></i>
                      <p class="mt-3">No hay categorías eliminadas</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      @endif

      <!-- Paginación -->
      @if($categorias->hasPages())
        <div class="mt-3">
          {{ $categorias->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
