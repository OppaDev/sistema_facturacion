@extends('layouts.app')

@section('title', 'Punto de Venta - Caja')

@push('styles')
<style>
    .pos-container {
        height: calc(100vh - 200px);
    }
    .product-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    .product-card:hover {
        border-color: #ff0000;
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(255, 0, 0, 0.2);
    }
    .product-card.out-of-stock {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .cart-item {
        border-bottom: 1px solid #e0e0e0;
        padding: 10px 0;
    }
    .cart-summary {
        position: sticky;
        top: 20px;
    }
    .badge-stock {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    .search-box {
        position: sticky;
        top: 0;
        z-index: 10;
        background: white;
        padding: 15px 0;
    }
    
    /* Animaciones para notificaciones */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title d-flex flex-column justify-content-center flex-sm-row">
                <div class="page-title-content">
                    <h4 class="mb-1">
                        <span class="text-muted fw-light">Caja /</span> 
                        <span style="color: #ff0000;">Punto de Venta</span>
                    </h4>
                    <p class="text-muted mb-0">Turno #{{ $turnoAbierto->id }} - Abierto desde: {{ $turnoAbierto->fecha_apertura->format('d/m/Y H:i') }}</p>
                </div>
                <div class="page-title-actions ms-auto">
                    <a href="{{ route('caja.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bx bx-list-ul me-1"></i> Ver Ventas
                    </a>
                    <a href="{{ route('caja.turnos.show', $turnoAbierto->id) }}" class="btn btn-outline-primary">
                        <i class="bx bx-time me-1"></i> Ver Turno
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del Turno -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card" style="border-left: 3px solid #ff0000;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1">Ventas del Turno</p>
                            <h4 class="mb-0">{{ $stats['ventas_turno'] }}</h4>
                        </div>
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="bx bx-receipt bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card" style="border-left: 3px solid #ff0000;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1">Total Recaudado</p>
                            <h4 class="mb-0">${{ number_format($stats['total_turno'], 2) }}</h4>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="bx bx-dollar bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card" style="border-left: 3px solid #ff0000;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1">Efectivo en Caja</p>
                            <h4 class="mb-0">${{ number_format($stats['efectivo_turno'], 2) }}</h4>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="bx bx-wallet bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card" style="border-left: 3px solid #ff0000;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text text-muted mb-1">Monto Inicial</p>
                            <h4 class="mb-0">${{ number_format($stats['monto_inicial'], 2) }}</h4>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="bx bx-money bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- POS Interface -->
    <div class="row pos-container">
        <!-- Productos (Izquierda) -->
        <div class="col-lg-7 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <!-- Búsqueda -->
                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: #ff0000; color: white;">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" 
                                   id="searchProduct" 
                                   class="form-control" 
                                   placeholder="Buscar producto por nombre o código..."
                                   autocomplete="off">
                        </div>
                    </div>

                    <!-- Lista de Productos -->
                    <div class="row mt-3" id="productList" style="max-height: calc(100vh - 400px); overflow-y: auto;">
                        @forelse($productos as $producto)
                        <div class="col-lg-4 col-md-6 col-sm-6 mb-3 product-item" 
                             data-id="{{ $producto->id }}"
                             data-nombre="{{ strtolower($producto->nombre) }}"
                             data-codigo="{{ strtolower($producto->codigo ?? '') }}">
                            <div class="card product-card {{ $producto->stock <= 0 ? 'out-of-stock' : '' }}" 
                                 onclick="{{ $producto->stock > 0 ? 'addToCart('.$producto->id.', \''.$producto->nombre.'\', '.$producto->precio.', '.$producto->stock.')' : '' }}">
                                <span class="badge {{ $producto->stock > 0 ? 'bg-success' : 'bg-danger' }} badge-stock">
                                    Stock: {{ $producto->stock }}
                                </span>
                                <div class="card-body text-center p-3">
                                    <div class="mb-2">
                                        <i class="bx bx-package bx-lg" style="color: #ff0000;"></i>
                                    </div>
                                    <h6 class="mb-1">{{ $producto->nombre }}</h6>
                                    @if($producto->codigo)
                                    <small class="text-muted">{{ $producto->codigo }}</small>
                                    @endif
                                    <div class="mt-2">
                                        <h5 class="mb-0" style="color: #ff0000;">${{ number_format($producto->precio, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5">
                            <i class="bx bx-box bx-lg text-muted mb-3"></i>
                            <p class="text-muted">No hay productos disponibles</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Carrito (Derecha) -->
        <div class="col-lg-5 col-md-6">
            <div class="card cart-summary h-100">
                <div class="card-header" >
                    <h5 class="mb-0">
                        <i class="bx bx-cart me-2"></i>Carrito de Compra
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div id="cartItems">
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-cart bx-lg mb-3"></i>
                            <p>El carrito está vacío</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Subtotal -->
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <strong id="subtotal">$0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ iva_label() }}:</span>
                        <strong id="iva">$0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-top pt-2">
                        <h5 class="mb-0">TOTAL:</h5>
                        <h4 class="mb-0" style="color: #ff0000;" id="total">$0.00</h4>
                    </div>

                    <!-- Botones de Acción -->
                    <button type="button" class="btn btn-outline-danger w-100 mb-2" id="clearCart">
                        <i class="bx bx-trash me-1"></i> Limpiar Carrito
                    </button>
                    <button type="button" class="btn btn-danger w-100" id="processPayment" disabled style="background-color: #ff0000; border-color: #ff0000;">
                        <i class="bx bx-dollar me-1"></i> Procesar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sistema de Notificaciones -->
<div id="notification-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
  <!-- Las notificaciones se insertarán aquí dinámicamente -->
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #ff0000; color: white;">
                <h5 class="modal-title">
                    <i class="bx bx-error-circle me-2"></i><span id="confirmTitle">Confirmar Acción</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmButton" style="background-color: #ff0000; border-color: #ff0000;">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Pago -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #ff0000; color: white;">
                <h5 class="modal-title">
                    <i class="bx bx-credit-card me-2"></i>Procesar Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                @csrf
                <div class="modal-body">
                    <!-- Resumen -->
                    <div class="alert alert-dark mb-4">
                        <div class="row">
                            <div class="col-6">
                                <strong>Subtotal:</strong> $<span id="modal-subtotal">0.00</span>
                            </div>
                            <div class="col-6">
                                <strong>IVA:</strong> $<span id="modal-iva">0.00</span>
                            </div>
                            <div class="col-12 mt-2 border-top pt-2">
                                <h5>TOTAL A PAGAR: $<span id="modal-total">0.00</span></h5>
                            </div>
                        </div>
                    </div>

                    <!-- Método de Pago -->
                    <div class="mb-3">
                        <label class="form-label">Método de Pago *</label>
                        <select class="form-select" id="tipoPago" name="tipo_pago" required>
                            <option value="">Seleccione...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="deuna">Deuna App</option>
                            <option value="mixto">Pago Mixto</option>
                        </select>
                    </div>

                    <!-- Montos por Método (para pago mixto) -->
                    <div id="montosMixto" style="display: none;">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            Ingrese los montos por cada método. La suma debe ser igual al total.
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Efectivo</label>
                                <input type="number" class="form-control monto-input" id="montoEfectivo" name="monto_efectivo" min="0" step="0.01" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tarjeta</label>
                                <input type="number" class="form-control monto-input" id="montoTarjeta" name="monto_tarjeta" min="0" step="0.01" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transferencia</label>
                                <input type="number" class="form-control monto-input" id="montoTransferencia" name="monto_transferencia" min="0" step="0.01" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Deuna App</label>
                                <input type="number" class="form-control monto-input" id="montoDeuna" name="monto_deuna" min="0" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            Total Pagado: $<span id="totalPagado">0.00</span> | 
                            Diferencia: $<span id="diferencia">0.00</span>
                        </div>
                    </div>

                    <!-- Cliente (Opcional) -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Cliente (Opcional)</label>
                            <input type="text" class="form-control" name="cliente_nombre" placeholder="Juan Pérez">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cédula/RUC (Opcional)</label>
                            <input type="text" class="form-control" name="cliente_identificacion" placeholder="1234567890">
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-3">
                        <label class="form-label">Notas (Opcional)</label>
                        <textarea class="form-control" name="notas" rows="2" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmarPago" style="background-color: #ff0000; border-color: #ff0000;">
                        <i class="bx bx-check me-1"></i>Confirmar Venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Sistema de notificaciones elegante
class NotificationSystem {
  constructor() {
    this.container = document.getElementById('notification-container');
  }

  show(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show shadow-lg`;
    notification.style.cssText = `
      min-width: 300px;
      max-width: 400px;
      margin-bottom: 10px;
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      animation: slideInRight 0.3s ease-out;
    `;

    const iconMap = {
      success: 'bx-check-circle',
      danger: 'bx-error-circle',
      warning: 'bx-warning',
      info: 'bx-info-circle'
    };

    notification.innerHTML = `
      <div class="d-flex align-items-center">
        <i class="bx ${iconMap[type]} fs-4 me-2"></i>
        <div class="flex-grow-1">
          <div class="fw-semibold">${this.getTitle(type)}</div>
          <div class="small">${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;

    this.container.appendChild(notification);

    // Auto-remove después del tiempo especificado
    setTimeout(() => {
      this.hide(notification);
    }, duration);

    // Event listener para cerrar manualmente
    notification.querySelector('.btn-close').addEventListener('click', () => {
      this.hide(notification);
    });
  }

  hide(notification) {
    notification.style.animation = 'slideOutRight 0.3s ease-in';
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }

  getTitle(type) {
    const titles = {
      success: '¡Éxito!',
      danger: 'Error',
      warning: 'Advertencia',
      info: 'Información'
    };
    return titles[type] || 'Notificación';
  }
}

// Función de confirmación personalizada
function showConfirm(message, title = 'Confirmar Acción', callback) {
  const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
  document.getElementById('confirmTitle').textContent = title;
  document.getElementById('confirmMessage').textContent = message;
  
  const confirmButton = document.getElementById('confirmButton');
  const newConfirmButton = confirmButton.cloneNode(true);
  confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
  
  newConfirmButton.addEventListener('click', function() {
    modal.hide();
    callback(true);
  });
  
  modal.show();
}

// Inicializar sistema de notificaciones
const notifications = new NotificationSystem();

let cart = [];
let subtotal = 0;
let iva = 0;
let total = 0;

// Búsqueda de productos
document.getElementById('searchProduct').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    const products = document.querySelectorAll('.product-item');
    
    products.forEach(product => {
        const nombre = product.dataset.nombre;
        const codigo = product.dataset.codigo;
        
        if (nombre.includes(search) || codigo.includes(search)) {
            product.style.display = '';
        } else {
            product.style.display = 'none';
        }
    });
});

// Agregar al carrito
function addToCart(id, nombre, precio, stock) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        if (existingItem.cantidad < stock) {
            existingItem.cantidad++;
        } else {
            notifications.show('No hay más stock disponible de este producto', 'warning');
            return;
        }
    } else {
        cart.push({
            id: id,
            nombre: nombre,
            precio: parseFloat(precio),
            cantidad: 1,
            stock: stock
        });
    }
    
    updateCart();
}

// Actualizar carrito
function updateCart() {
    const cartItems = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bx bx-cart bx-lg mb-3"></i>
                <p>El carrito está vacío</p>
            </div>
        `;
        document.getElementById('processPayment').disabled = true;
    } else {
        let html = '';
        cart.forEach(item => {
            const itemTotal = item.precio * item.cantidad;
            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${item.nombre}</h6>
                            <small class="text-muted">$${item.precio.toFixed(2)} x ${item.cantidad}</small>
                        </div>
                        <strong style="color: #ff0000;">$${itemTotal.toFixed(2)}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-danger" onclick="decreaseQuantity(${item.id})">
                                <i class="bx bx-minus"></i>
                            </button>
                            <button class="btn btn-outline-secondary" disabled>${item.cantidad}</button>
                            <button class="btn btn-outline-success" onclick="increaseQuantity(${item.id})">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        cartItems.innerHTML = html;
        document.getElementById('processPayment').disabled = false;
    }
    
    calculateTotals();
}

// Calcular totales
function calculateTotals() {
    subtotal = cart.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    iva = subtotal * {{ iva_rate() }};
    total = subtotal + iva;
    
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('iva').textContent = '$' + iva.toFixed(2);
    document.getElementById('total').textContent = '$' + total.toFixed(2);
}

// Aumentar cantidad
function increaseQuantity(id) {
    const item = cart.find(i => i.id === id);
    if (item && item.cantidad < item.stock) {
        item.cantidad++;
        updateCart();
    } else {
        notifications.show('No hay más stock disponible', 'warning');
    }
}

// Disminuir cantidad
function decreaseQuantity(id) {
    const item = cart.find(i => i.id === id);
    if (item && item.cantidad > 1) {
        item.cantidad--;
        updateCart();
    }
}

// Eliminar del carrito
function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCart();
}

// Limpiar carrito
document.getElementById('clearCart').addEventListener('click', function() {
    showConfirm('¿Está seguro de limpiar el carrito?', 'Limpiar Carrito', function(confirmed) {
        if (confirmed) {
            cart = [];
            updateCart();
            notifications.show('Carrito limpiado correctamente', 'info');
        }
    });
});

// Procesar pago
document.getElementById('processPayment').addEventListener('click', function() {
    document.getElementById('modal-subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('modal-iva').textContent = iva.toFixed(2);
    document.getElementById('modal-total').textContent = total.toFixed(2);
    
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
});

// Cambiar método de pago
document.getElementById('tipoPago').addEventListener('change', function() {
    const montosMixto = document.getElementById('montosMixto');
    if (this.value === 'mixto') {
        montosMixto.style.display = 'block';
    } else {
        montosMixto.style.display = 'none';
    }
});

// Calcular total pagado en pago mixto
document.querySelectorAll('.monto-input').forEach(input => {
    input.addEventListener('input', function() {
        const efectivo = parseFloat(document.getElementById('montoEfectivo').value) || 0;
        const tarjeta = parseFloat(document.getElementById('montoTarjeta').value) || 0;
        const transferencia = parseFloat(document.getElementById('montoTransferencia').value) || 0;
        const deuna = parseFloat(document.getElementById('montoDeuna').value) || 0;
        
        const totalPagado = efectivo + tarjeta + transferencia + deuna;
        const diferencia = total - totalPagado;
        
        document.getElementById('totalPagado').textContent = totalPagado.toFixed(2);
        document.getElementById('diferencia').textContent = diferencia.toFixed(2);
        
        if (Math.abs(diferencia) < 0.01) {
            document.getElementById('diferencia').parentElement.classList.remove('alert-warning');
            document.getElementById('diferencia').parentElement.classList.add('alert-success');
        } else {
            document.getElementById('diferencia').parentElement.classList.remove('alert-success');
            document.getElementById('diferencia').parentElement.classList.add('alert-warning');
        }
    });
});

// Enviar formulario
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const tipoPago = document.getElementById('tipoPago').value;
    if (!tipoPago) {
        notifications.show('Debe seleccionar un método de pago', 'warning');
        return;
    }
    
    // Validar pago mixto
    if (tipoPago === 'mixto') {
        const efectivo = parseFloat(document.getElementById('montoEfectivo').value) || 0;
        const tarjeta = parseFloat(document.getElementById('montoTarjeta').value) || 0;
        const transferencia = parseFloat(document.getElementById('montoTransferencia').value) || 0;
        const deuna = parseFloat(document.getElementById('montoDeuna').value) || 0;
        const totalPagado = efectivo + tarjeta + transferencia + deuna;
        
        if (Math.abs(total - totalPagado) > 0.01) {
            notifications.show('La suma de los montos debe ser igual al total', 'warning');
            return;
        }
    }
    
    // Preparar datos
    const formData = new FormData(this);
    
    // Agregar productos como array
    cart.forEach((item, index) => {
        formData.append(`productos[${index}][producto_id]`, item.id);
        formData.append(`productos[${index}][cantidad]`, item.cantidad);
    });
    
    // Deshabilitar botón
    const btnConfirmar = document.getElementById('btnConfirmarPago');
    btnConfirmar.disabled = true;
    btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
    
    // Enviar request
    fetch('{{ route("caja.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Limpiar carrito
            cart = [];
            updateCart();
            
            // Cerrar modal
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            
            // Mostrar mensaje de éxito
            notifications.show(data.message, 'success', 6000);
            
            // Preguntar si quiere imprimir ticket
            showConfirm('¿Desea imprimir el ticket de la venta?', 'Imprimir Ticket', function(confirmed) {
                if (confirmed) {
                    window.open('/caja/' + data.venta_id + '/ticket', '_blank');
                }
            });
            
            // Resetear formulario
            document.getElementById('paymentForm').reset();
        } else {
            notifications.show(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notifications.show('Error al procesar la venta. Por favor intente nuevamente.', 'danger');
    })
    .finally(() => {
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = '<i class="bx bx-check me-1"></i>Confirmar Venta';
    });
});
</script>
@endpush
