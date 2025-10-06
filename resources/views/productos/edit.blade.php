@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')
<!-- Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Productos /</span> Editar Producto
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Editar: {{ $producto->nombre }}</h5>
                <a href="{{ route('productos.show', $producto) }}" class="btn btn-sm btn-label-secondary">
                    <i class="bx bx-show me-1"></i> Ver Producto
                </a>
            </div>
            <form method="POST" action="{{ route('productos.update', $producto) }}" enctype="multipart/form-data" autocomplete="off" id="productoForm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <!-- Imagen del Producto -->
                        <div class="col-md-4 mb-4">
                            <label class="form-label">Imagen del Producto</label>
                            <div class="d-flex flex-column align-items-center">
                                <div class="mb-3" style="width: 200px; height: 200px; border: 2px dashed #d9dee3; border-radius: 8px; display: flex; align-items: center; justify-content: center; background-color: #f5f5f9;">
                                    @if($producto->imagen)
                                        <img id="preview-imagen" src="{{ asset('storage/productos/' . $producto->imagen) }}" alt="Preview" class="img-fluid rounded" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                        <i class="bx bx-image text-muted d-none" id="icono-preview" style="font-size: 48px;"></i>
                                    @else
                                        <i class="bx bx-image text-muted" id="icono-preview" style="font-size: 48px;"></i>
                                        <img id="preview-imagen" src="#" alt="Preview" class="img-fluid rounded d-none" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                    @endif
                                </div>
                                <input type="file" name="imagen" id="imagen" accept="image/jpeg,image/png,image/webp" class="form-control @error('imagen') is-invalid @enderror">
                                @error('imagen')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted mt-2">JPG, PNG o WEBP (máx. 2MB)</small>
                            </div>
                        </div>

                        <!-- Datos del Producto -->
                        <div class="col-md-8">
                            <div class="row">
                                <!-- Nombre -->
                                <div class="col-12 mb-3">
                                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $producto->nombre) }}" required maxlength="100" placeholder="Ej: Cerveza Corona" autofocus>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Descripción -->
                                <div class="col-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea name="descripcion" id="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3" maxlength="1000" placeholder="Descripción opcional del producto">{{ old('descripcion', $producto->descripcion) }}</textarea>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Categoría -->
                                <div class="col-md-6 mb-3">
                                    <label for="categoria_id" class="form-label">Categoría</label>
                                    <select name="categoria_id" id="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                                        <option value="">Sin categoría</option>
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('categoria_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Stock -->
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $producto->stock) }}" min="0" max="1000" required placeholder="0">
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Precio -->
                                <div class="col-md-6 mb-3">
                                    <label for="precio" class="form-label">Precio <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="precio" id="precio" class="form-control @error('precio') is-invalid @enderror" value="{{ old('precio', $producto->precio) }}" min="0.01" max="10000" required placeholder="0.00">
                                        @error('precio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                        <option value="activo" {{ old('estado', $producto->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ old('estado', $producto->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer con botones -->
                <div class="card-footer pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Los campos con <span class="text-danger">*</span> son obligatorios</small>
                        <div class="d-flex gap-2">
                            <a href="{{ route('productos.show', $producto) }}" class="btn btn-label-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="btn-text">
                                    <i class="bx bx-save me-1"></i> Actualizar Producto
                                </span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Actualizando...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview de imagen
    const inputImagen = document.getElementById('imagen');
    const preview = document.getElementById('preview-imagen');
    const icono = document.getElementById('icono-preview');
    
    inputImagen.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                icono.classList.add('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            preview.classList.add('d-none');
            icono.classList.remove('d-none');
        }
    });

    // Submit button loading state
    const form = document.getElementById('productoForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');

    form.addEventListener('submit', function() {
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
        submitBtn.disabled = true;
    });
});
</script>
@endpush