@extends('layouts.app')

@section('title', 'Editar Categoría')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="page-title d-flex flex-column justify-content-center my-0 mb-3">
        <h4 class="mb-1">
          <span class="text-muted fw-light">Inventario / <a href="{{ route('categorias.index') }}" class="text-muted">Categorías</a> /</span> Editar Categoría
        </h4>
        <p class="text-muted mb-0">Modificar información de la categoría "{{ $categoria->nombre }}"</p>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8 mx-auto">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-edit me-2"></i>Editar Categoría
          </h5>
          <span class="badge" style="background-color: {{ $categoria->color }}; color: #fff;">{{ $categoria->nombre }}</span>
        </div>
        
        <form action="{{ route('categorias.update', $categoria) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="card-body">
            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible" role="alert">
                <h6 class="alert-heading mb-1"><i class="bx bx-error-circle me-1"></i>Errores de validación</h6>
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <div class="row">
              <div class="col-md-8 mb-3">
                <label for="nombre" class="form-label">
                  Nombre <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       name="nombre" 
                       id="nombre" 
                       class="form-control @error('nombre') is-invalid @enderror" 
                       value="{{ old('nombre', $categoria->nombre) }}" 
                       placeholder="Ej: Electrónica, Ropa, Alimentos..."
                       required
                       autofocus>
                @error('nombre')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4 mb-3">
                <label for="color" class="form-label">
                  Color <span class="text-danger">*</span>
                </label>
                <input type="color" 
                       name="color" 
                       id="color" 
                       class="form-control form-control-color w-100 @error('color') is-invalid @enderror" 
                       value="{{ old('color', $categoria->color) }}" 
                       required>
                @error('color')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Color para identificar la categoría</small>
              </div>
            </div>

            <div class="mb-3">
              <label for="descripcion" class="form-label">
                Descripción
              </label>
              <textarea name="descripcion" 
                        id="descripcion" 
                        class="form-control @error('descripcion') is-invalid @enderror" 
                        rows="3"
                        placeholder="Descripción opcional de la categoría...">{{ old('descripcion', $categoria->descripcion) }}</textarea>
              @error('descripcion')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="activo" class="form-label">
                Estado <span class="text-danger">*</span>
              </label>
              <select name="activo" id="activo" class="form-select @error('activo') is-invalid @enderror" required>
                <option value="1" {{ old('activo', $categoria->activo) == '1' ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ old('activo', $categoria->activo) == '0' ? 'selected' : '' }}>Inactivo</option>
              </select>
              @error('activo')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">Las categorías inactivas no estarán disponibles para nuevos productos</small>
            </div>

            @if($categoria->productos_count > 0)
              <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="bx bx-info-circle fs-4 me-2"></i>
                <div>
                  Esta categoría tiene <strong>{{ $categoria->productos_count }} producto(s)</strong> asociados.
                </div>
              </div>
            @endif
          </div>

          <div class="card-footer">
            <div class="d-flex justify-content-between">
              <a href="{{ route('categorias.index') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i> Volver
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i> Guardar Cambios
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
