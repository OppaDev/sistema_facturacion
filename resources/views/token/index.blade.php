@extends('layouts.app')

@section('title', 'Gestión de Tokens API')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="page-title d-flex flex-column justify-content-center flex-sm-row my-0">
        <div class="page-title-content">
          <h4 class="mb-1">
            <span class="text-muted fw-light">Sistema /</span> Tokens API
          </h4>
          <p class="text-muted mb-0">Gestión de tokens de acceso para la API del sistema</p>
        </div>
        <div class="page-title-actions ms-auto">
          <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Volver al Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Notificaciones -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow mb-4" role="alert">
      <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow mb-4" role="alert">
      <i class="bx bx-x-circle me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  @endif

  @if(session('token_generado'))
    <div class="alert alert-success alert-dismissible fade show shadow mb-4" role="alert">
      <i class="bx bx-check-circle me-2"></i>
      <strong>Token generado exitosamente:</strong>
      <br>
      <div class="mt-2 p-3 bg-light rounded">
        <code class="text-success fw-bold">{{ session('token_generado') }}</code>
        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('{{ session('token_generado') }}')">
          <i class="bx bx-copy"></i> Copiar
        </button>
      </div>
      <small class="text-muted mt-2 d-block">
        <i class="bx bx-info-circle"></i> ¡Guarda este token! No se volverá a mostrar por seguridad.
      </small>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow mb-4" role="alert">
      <i class="bx bx-error-circle me-2"></i>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  @endif

  <!-- Formulario para crear token -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-key me-2"></i> Generar Nuevo Token
          </h5>
          <div class="ms-auto">
            <small class="text-muted">
              <i class="bx bx-shield me-1"></i> Los tokens se almacenan encriptados por seguridad
            </small>
          </div>
        </div>
        <div class="card-body">
          <form action="{{ route('crearTokenAcceso') }}" method="POST">
            @csrf
            <div class="row g-3">
              <div class="col-md-4">
                <label for="entidad_tipo" class="form-label">
                  <i class="bx bx-category me-1"></i> Tipo de Entidad
                </label>
                <select name="entidad_tipo" id="entidad_tipo" class="form-select @error('entidad_tipo') is-invalid @enderror" required onchange="actualizarEntidades()">
                  <option value="" disabled selected>-- Seleccione el tipo --</option>
                  <option value="usuario" {{ old('entidad_tipo') == 'usuario' ? 'selected' : '' }}>Usuario</option>
                  <option value="cliente" {{ old('entidad_tipo') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                </select>
                @error('entidad_tipo')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label for="entidad_id" class="form-label">
                  <i class="bx bx-user me-1"></i> <span id="label_entidad">Seleccionar Entidad</span>
                </label>
                <select name="entidad_id" id="entidad_id" class="form-select @error('entidad_id') is-invalid @enderror" required disabled>
                  <option value="" disabled selected>-- Primero seleccione el tipo --</option>
                </select>
                @error('entidad_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label for="token_name" class="form-label">
                  <i class="bx bx-rename me-1"></i> Nombre del Token
                </label>
                <input 
                  type="text" 
                  name="token_name" 
                  id="token_name" 
                  class="form-control @error('token_name') is-invalid @enderror"
                  placeholder="Ej: API-Mobile-App, Dashboard-Web, etc."
                  value="{{ old('token_name') }}"
                  required
                >
                @error('token_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                  <i class="bx bx-info-circle"></i> 
                  Usa un nombre descriptivo para identificar el propósito del token
                </div>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Generar Token
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Lista de tokens por usuario -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h5 class="mb-0">
            <i class="bx bx-list-ul me-2"></i> Tokens Activos por Entidad
          </h5>
        </div>
        <div class="card-body">
          @if($usuarios->isEmpty() && $clientes->isEmpty())
            <div class="text-center py-4">
              <i class="bx bx-user-x display-4 text-muted"></i>
              <p class="text-muted mt-2">No hay usuarios ni clientes activos en el sistema</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>
                      <i class="bx bx-user me-1"></i> Entidad
                    </th>
                    <th>
                      <i class="bx bx-key me-1"></i> Tokens Activos
                    </th>
                    <th>
                      <i class="bx bx-cog me-1"></i> Acciones
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {{-- Usuarios --}}
                  @foreach($usuarios as $usuario)
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                              {{ strtoupper(substr($usuario->name, 0, 2)) }}
                            </span>
                          </div>
                          <div>
                            <h6 class="mb-0">{{ $usuario->name }}</h6>
                            <small class="text-muted">{{ $usuario->email }}</small>
                            <br>
                            <span class="badge bg-label-primary">Usuario - {{ $usuario->getRoleNames()->first() }}</span>
                          </div>
                        </div>
                      </td>
                      <td>
                        @if($usuario->tokens->isEmpty())
                          <span class="text-muted">
                            <i class="bx bx-x-circle me-1"></i> Sin tokens generados
                          </span>
                        @else
                          <div class="token-list">
                            @foreach($usuario->tokens as $token)
                              <div class="token-item d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                <div>
                                  <div class="fw-semibold text-primary">
                                    <i class="bx bx-key me-1"></i> {{ $token->name }}
                                  </div>
                                  <small class="text-muted">
                                    <i class="bx bx-calendar me-1"></i>
                                    Creado: {{ $token->created_at->format('d/m/Y H:i') }}
                                  </small>
                                  <br>
                                  <small class="text-muted">
                                    <i class="bx bx-time me-1"></i>
                                    Último uso:
                                    @if($token->last_used_at)
                                      {{ $token->last_used_at->format('d/m/Y H:i') }}
                                    @else
                                      <span class="text-warning">Nunca usado</span>
                                    @endif
                                  </small>
                                  <br>
                                  <small class="text-info">
                                    <i class="bx bx-shield me-1"></i>
                                    Token:
                                    <br>
                                    <div class="mt-1 p-2 bg-light rounded d-flex align-items-center justify-content-between">
                                      <code class="token-display text-break" style="font-size: 0.8rem; max-width: 70%;">
                                        {{ $token->decrypted_token ?? 'No disponible' }}
                                      </code>
                                      <button class="btn btn-xs btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $token->decrypted_token ?? 'No disponible' }}')">
                                        <i class="bx bx-copy"></i> Copiar
                                      </button>
                                    </div>
                                  </small>
                                  @if($token->abilities && !empty($token->abilities))
                                    <br>
                                    <small class="text-info">
                                      <i class="bx bx-shield me-1"></i>
                                      Permisos: {{ implode(', ', $token->abilities) }}
                                    </small>
                                  @endif
                                </div>
                                <div>
                                  <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#eliminarTokenModal"
                                    data-token-id="{{ $token->id }}"
                                    data-token-name="{{ $token->name }}"
                                    data-entidad-name="{{ $usuario->name }}"
                                    data-entidad-type="Usuario"
                                  >
                                    <i class="bx bx-trash"></i>
                                  </button>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </td>
                      <td>
                        <span class="badge bg-label-{{ $usuario->tokens->count() > 0 ? 'success' : 'secondary' }}">
                          {{ $usuario->tokens->count() }} token(s)
                        </span>
                      </td>
                    </tr>
                  @endforeach
                  
                  {{-- Clientes --}}
                  @foreach($clientes as $cliente)
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                              {{ strtoupper(substr($cliente->nombre, 0, 2)) }}
                            </span>
                          </div>
                          <div>
                            <h6 class="mb-0">{{ $cliente->nombre }}</h6>
                            <small class="text-muted">{{ $cliente->email }}</small>
                            <br>
                            <span class="badge bg-label-success">Cliente</span>
                          </div>
                        </div>
                      </td>
                      <td>
                        @if($cliente->tokens->isEmpty())
                          <span class="text-muted">
                            <i class="bx bx-x-circle me-1"></i> Sin tokens generados
                          </span>
                        @else
                          <div class="token-list">
                            @foreach($cliente->tokens as $token)
                              <div class="token-item d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                <div>
                                  <div class="fw-semibold text-success">
                                    <i class="bx bx-key me-1"></i> {{ $token->name }}
                                  </div>
                                  <small class="text-muted">
                                    <i class="bx bx-calendar me-1"></i>
                                    Creado: {{ $token->created_at->format('d/m/Y H:i') }}
                                  </small>
                                  <br>
                                  <small class="text-muted">
                                    <i class="bx bx-time me-1"></i>
                                    Último uso:
                                    @if($token->last_used_at)
                                      {{ $token->last_used_at->format('d/m/Y H:i') }}
                                    @else
                                      <span class="text-warning">Nunca usado</span>
                                    @endif
                                  </small>
                                  <br>
                                  <small class="text-info">
                                    <i class="bx bx-shield me-1"></i>
                                    Token:
                                    <br>
                                    <div class="mt-1 p-2 bg-light rounded d-flex align-items-center justify-content-between">
                                      <code class="token-display text-break" style="font-size: 0.8rem; max-width: 70%;">
                                        {{ $token->decrypted_token ?? 'No disponible' }}
                                      </code>
                                      <button class="btn btn-xs btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $token->decrypted_token ?? 'No disponible' }}')">
                                        <i class="bx bx-copy"></i> Copiar
                                      </button>
                                    </div>
                                  </small>
                                  @if($token->abilities && !empty($token->abilities))
                                    <br>
                                    <small class="text-info">
                                      <i class="bx bx-shield me-1"></i>
                                      Permisos: {{ implode(', ', $token->abilities) }}
                                    </small>
                                  @endif
                                </div>
                                <div>
                                  <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#eliminarTokenModal"
                                    data-token-id="{{ $token->id }}"
                                    data-token-name="{{ $token->name }}"
                                    data-entidad-name="{{ $cliente->nombre }}"
                                    data-entidad-type="Cliente"
                                  >
                                    <i class="bx bx-trash"></i>
                                  </button>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                      </td>
                      <td>
                        <span class="badge bg-label-{{ $cliente->tokens->count() > 0 ? 'success' : 'secondary' }}">
                          {{ $cliente->tokens->count() }} token(s)
                        </span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para eliminar token -->
<div class="modal fade" id="eliminarTokenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bx bx-trash me-2"></i> Eliminar Token
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="eliminarTokenForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>¡Atención!</strong> Esta acción no se puede deshacer.
          </div>
          <p>
            ¿Está seguro de que desea eliminar el token <strong id="tokenNameText"></strong>
            de <span id="entidadTypeText"></span> <strong id="entidadNameText"></strong>?
          </p>
          <div class="mb-3">
            <label for="admin_password" class="form-label">
              <i class="bx bx-lock me-1"></i> Confirme su contraseña de administrador
            </label>
            <input 
              type="password" 
              class="form-control @error('admin_password') is-invalid @enderror" 
              id="admin_password" 
              name="admin_password" 
              required
            >
            @error('admin_password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="bx bx-trash me-1"></i> Eliminar Token
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
// Datos de entidades (usuarios y clientes)
const usuariosData = @json($usuarios);
const clientesData = @json($clientes);

// Función para actualizar las opciones de entidades
function actualizarEntidades() {
    const tipoSelect = document.getElementById('entidad_tipo');
    const entidadSelect = document.getElementById('entidad_id');
    const labelEntidad = document.getElementById('label_entidad');
    
    // Limpiar opciones
    entidadSelect.innerHTML = '<option value="" disabled selected>-- Seleccione una entidad --</option>';
    
    if (tipoSelect.value === 'usuario') {
        labelEntidad.textContent = 'Seleccionar Usuario';
        usuariosData.forEach(usuario => {
            const option = document.createElement('option');
            option.value = usuario.id;
            const roleName = usuario.roles && usuario.roles.length > 0 ? usuario.roles[0].name : 'Sin rol';
            option.textContent = `${usuario.name} (${usuario.email}) - ${roleName}`;
            entidadSelect.appendChild(option);
        });
        entidadSelect.disabled = false;
    } else if (tipoSelect.value === 'cliente') {
        labelEntidad.textContent = 'Seleccionar Cliente';
        clientesData.forEach(cliente => {
            const option = document.createElement('option');
            option.value = cliente.id;
            option.textContent = `${cliente.nombre} (${cliente.email})`;
            entidadSelect.appendChild(option);
        });
        entidadSelect.disabled = false;
    } else {
        entidadSelect.disabled = true;
        labelEntidad.textContent = 'Seleccionar Entidad';
    }
}

// Función para copiar al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Mostrar mensaje de confirmación
        const alert = document.createElement('div');
        alert.className = 'alert alert-info alert-dismissible fade show position-fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            <i class="bx bx-check me-2"></i>
            Token copiado al portapapeles
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 3000);
    }).catch(function(err) {
        console.error('Error al copiar: ', err);
    });
}

// Configurar modal para eliminar token
document.addEventListener('DOMContentLoaded', function() {
    const eliminarTokenModal = document.getElementById('eliminarTokenModal');
    if (eliminarTokenModal) {
        eliminarTokenModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const tokenId = button.getAttribute('data-token-id');
            const tokenName = button.getAttribute('data-token-name');
            const entidadName = button.getAttribute('data-entidad-name');
            const entidadType = button.getAttribute('data-entidad-type');
            
            const form = document.getElementById('eliminarTokenForm');
            const tokenNameText = document.getElementById('tokenNameText');
            const entidadNameText = document.getElementById('entidadNameText');
            const entidadTypeText = document.getElementById('entidadTypeText');
            
            form.action = `/tokens/${tokenId}`;
            tokenNameText.textContent = tokenName;
            entidadNameText.textContent = entidadName;
            entidadTypeText.textContent = entidadType.toLowerCase();
        });
    }
    
    // Inicializar el formulario si hay valores previos
    const entidadTipoSelect = document.getElementById('entidad_tipo');
    if (entidadTipoSelect && entidadTipoSelect.value) {
        actualizarEntidades();
    }
});
</script>
@endpush

@push('styles')
<style>
.token-item {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.token-item:hover {
    background-color: #e9ecef;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.token-list {
    max-height: 300px;
    overflow-y: auto;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.token-display {
    word-break: break-all;
    font-family: 'Courier New', monospace;
    background-color: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.token-container {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px;
}
</style>
@endpush
@endsection
