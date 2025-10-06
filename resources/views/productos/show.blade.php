@extends('layouts.app')

@section('title', 'Detalle de Producto')

@section('content')

<!-- Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Productos /</span> {{ $producto->nombre }}
        </h4>
    </div>
</div>

<div class="row">
    <!-- Información Principal del Producto -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Información del Producto</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit me-1"></i> Editar
                    </a>
                    <a href="{{ route('productos.index') }}" class="btn btn-sm btn-label-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Imagen del Producto -->
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="text-center">
                            @if($producto->imagen)
                                <img src="{{ asset('storage/productos/' . $producto->imagen) }}"
                                     class="img-fluid rounded"
                                     alt="Imagen del producto"
                                     style="max-height: 300px; width: auto; border: 1px solid #d9dee3;">
                            @else
                                <div class="d-flex flex-column align-items-center justify-content-center" style="height: 300px; border: 2px dashed #d9dee3; border-radius: 8px; background-color: #f5f5f9;">
                                    <i class="bx bx-image text-muted" style="font-size: 64px;"></i>
                                    <p class="text-muted mt-2 mb-0">Sin imagen</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Detalles del Producto -->
                    <div class="col-md-8">
                        <h3 class="mb-4">{{ $producto->nombre }}</h3>

                        @if($producto->descripcion)
                            <div class="mb-4">
                                <label class="form-label text-muted">Descripción</label>
                                <p class="mb-0">{{ $producto->descripcion }}</p>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Categoría -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Categoría</label>
                                <p class="mb-0">
                                    <span class="badge bg-label-primary">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</span>
                                </p>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <p class="mb-0">
                                    @if($producto->trashed())
                                        <span class="badge bg-label-danger">Eliminado</span>
                                    @elseif($producto->estado == 'activo')
                                        <span class="badge bg-label-success">Activo</span>
                                    @else
                                        <span class="badge bg-label-warning">Inactivo</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Stock -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Stock</label>
                                <p class="mb-0 fw-semibold fs-5">{{ $producto->stock }} unidades</p>
                            </div>

                            <!-- Precio -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Precio</label>
                                <p class="mb-0 fw-semibold fs-5 text-primary">${{ number_format($producto->precio, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Lateral -->
    <div class="col-lg-4">
        <!-- Información de Auditoría -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Información de Auditoría</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Creado por</small>
                    <p class="mb-0">
                        @if($producto->creador)
                            {{ $producto->creador->name }}
                        @else
                            Sistema
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Fecha de creación</small>
                    <p class="mb-0">{{ $producto->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($producto->updated_by)
                    <div class="mb-3">
                        <small class="text-muted d-block">Última modificación por</small>
                        <p class="mb-0">
                            @if($producto->modificador)
                                {{ $producto->modificador->name }}
                            @else
                                Sistema
                            @endif
                        </p>
                    </div>
                @endif
                <div class="mb-3">
                    <small class="text-muted d-block">Última actualización</small>
                    <p class="mb-0">{{ $producto->updated_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($producto->deleted_at)
                    <div class="mb-0">
                        <small class="text-muted d-block">Eliminado el</small>
                        <p class="mb-0 text-danger">{{ $producto->deleted_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Acciones</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(!$producto->trashed())
                        <a href="{{ route('productos.edit', $producto) }}" class="btn btn-primary">
                            <i class="bx bx-edit me-1"></i> Editar Producto
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarProducto{{ $producto->id }}">
                            <i class="bx bx-trash me-1"></i> Eliminar Producto
                        </button>
                    @else
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRestaurarProducto{{ $producto->id }}">
                            <i class="bx bx-reset me-1"></i> Restaurar Producto
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalBorrarDefinitivoProducto{{ $producto->id }}">
                            <i class="bx bx-trash me-1"></i> Eliminar Definitivamente
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Producto -->
@if(!$producto->trashed())
<div class="modal fade" id="modalEliminarProducto{{ $producto->id }}" tabindex="-1" aria-labelledby="modalEliminarProductoLabel{{ $producto->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('productos.destroy', $producto) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarProductoLabel{{ $producto->id }}">
                        <i class="bx bx-trash me-2"></i>Eliminar Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <strong>¡Atención!</strong> Esta acción enviará el producto a la papelera. Podrás restaurarlo más tarde.
                    </div>
                    <div class="mb-3">
                        <p class="mb-2"><strong>Producto:</strong> {{ $producto->nombre }}</p>
                        <p class="mb-2"><strong>Categoría:</strong> {{ $producto->categoria->nombre ?? 'Sin categoría' }}</p>
                        <p class="mb-0"><strong>Stock:</strong> {{ $producto->stock }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña de administrador <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="observacion" class="form-label">Motivo de eliminación <span class="text-danger">*</span></label>
                        <select name="observacion" id="observacion" class="form-select @error('observacion') is-invalid @enderror" required>
                            <option value="">Seleccionar motivo</option>
                            <option value="Producto descontinuado">Producto descontinuado</option>
                            <option value="Stock agotado permanentemente">Stock agotado permanentemente</option>
                            <option value="Producto defectuoso">Producto defectuoso</option>
                            <option value="Baja demanda">Baja demanda</option>
                            <option value="Error en el sistema">Error en el sistema</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('observacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Restaurar Producto -->
@if($producto->trashed())
<div class="modal fade" id="modalRestaurarProducto{{ $producto->id }}" tabindex="-1" aria-labelledby="modalRestaurarProductoLabel{{ $producto->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('productos.restore', $producto->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRestaurarProductoLabel{{ $producto->id }}">
                        <i class="bx bx-reset me-2"></i>Restaurar Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success mb-3">
                        <strong>Restaurar:</strong> {{ $producto->nombre }}
                    </div>
                    <p class="mb-3">Este producto volverá a estar disponible en el sistema.</p>
                    <div class="mb-3">
                        <label for="password_restore" class="form-label">Contraseña de administrador <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password_restore" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="observacion_restore" class="form-label">Motivo de restauración <span class="text-danger">*</span></label>
                        <select name="observacion" id="observacion_restore" class="form-select @error('observacion') is-invalid @enderror" required>
                            <option value="">Seleccionar motivo</option>
                            <option value="Error de eliminación">Error de eliminación</option>
                            <option value="Producto disponible nuevamente">Producto disponible nuevamente</option>
                            <option value="Solicitud de cliente">Solicitud de cliente</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('observacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-reset me-1"></i> Restaurar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Borrar Definitivamente -->
<div class="modal fade" id="modalBorrarDefinitivoProducto{{ $producto->id }}" tabindex="-1" aria-labelledby="modalBorrarDefinitivoProductoLabel{{ $producto->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('productos.forceDelete', $producto->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBorrarDefinitivoProductoLabel{{ $producto->id }}">
                        <i class="bx bx-error me-2"></i>Eliminar Definitivamente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <strong>¡ADVERTENCIA!</strong> Esta acción es irreversible. El producto se eliminará permanentemente.
                    </div>
                    <div class="mb-3">
                        <p class="mb-2"><strong>Producto a eliminar:</strong> {{ $producto->nombre }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="password_force" class="form-label">Contraseña de administrador <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password_force" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="observacion_force" class="form-label">Motivo de eliminación definitiva <span class="text-danger">*</span></label>
                        <select name="observacion" id="observacion_force" class="form-select @error('observacion') is-invalid @enderror" required>
                            <option value="">Seleccionar motivo</option>
                            <option value="Producto obsoleto">Producto obsoleto</option>
                            <option value="Datos duplicados">Datos duplicados</option>
                            <option value="Limpieza de base de datos">Limpieza de base de datos</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('observacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Eliminar Definitivamente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection