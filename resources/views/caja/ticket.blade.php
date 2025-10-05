<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $venta->numero_venta }}</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: bo            <span>{{ iva_label() }}:</span>
            <span>$ {{ number_format($venta->iva, 2) }}</span>er-box;
        }

        /* Configuración para impresión térmica 80mm */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            width: 80mm;
            margin: 0 auto;
            padding: 10mm;
        }

        /* Encabezado */
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .header .info {
            font-size: 11px;
            margin-top: 5px;
        }

        /* Información de la venta */
        .venta-info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .venta-info .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .venta-info .label {
            font-weight: bold;
        }

        /* Tabla de productos */
        .productos {
            margin-bottom: 15px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }

        .productos .header-table {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 11px;
        }

        .productos .item {
            margin-bottom: 8px;
        }

        .productos .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .productos .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        /* Totales */
        .totales {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .totales .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .totales .total-final {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }

        /* Pago */
        .pago {
            margin-bottom: 15px;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 11px;
        }

        .pago .metodo {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
        }

        .pago-mixto {
            text-align: left;
            margin-top: 8px;
        }

        .pago-mixto .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        /* Cliente */
        .cliente {
            margin-bottom: 15px;
            font-size: 11px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .cliente .label {
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 11px;
            border-top: 2px dashed #000;
            padding-top: 10px;
        }

        .footer p {
            margin-bottom: 5px;
        }

        .footer .gracias {
            font-size: 13px;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Estado anulado */
        .anulada {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            border: 3px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }

        /* Impresión */
        @media print {
            body {
                padding: 5mm;
            }

            .no-print {
                display: none;
            }

            /* Evitar saltos de página */
            .productos .item {
                page-break-inside: avoid;
            }
        }

        /* Botón de impresión (solo en pantalla) */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #ff0000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        .print-button:hover {
            background: #cc0000;
        }

        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Botón de impresión (solo visible en pantalla) -->
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Imprimir Ticket
    </button>

    <!-- Encabezado -->
    <div class="header">
        <h1>INFERNO CLUB</h1>
        <div class="subtitle">Bebidas Alcohólicas & Cócteles</div>
        <div class="subtitle">Abierto 24/7</div>
        <div class="info">RUC: 1234567890001</div>
        <div class="info">Dir: Av. Principal #123, Quito</div>
        <div class="info">Tel: (02) 123-4567</div>
    </div>

    <!-- Estado anulado (si aplica) -->
    @if($venta->estado === 'anulada')
    <div class="anulada">
        *** VENTA ANULADA ***
    </div>
    @endif

    <!-- Información de la venta -->
    <div class="venta-info">
        <div class="row">
            <span class="label">TICKET:</span>
            <span>#{{ $venta->numero_venta }}</span>
        </div>
        <div class="row">
            <span class="label">FECHA:</span>
            <span>{{ $venta->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="row">
            <span class="label">HORA:</span>
            <span>{{ $venta->created_at->format('H:i:s') }}</span>
        </div>
        <div class="row">
            <span class="label">CAJERO:</span>
            <span>{{ $venta->usuario->name ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span class="label">TURNO:</span>
            <span>#{{ $venta->turno_id }}</span>
        </div>
    </div>

    <!-- Cliente (si existe) -->
    @if($venta->cliente_nombre || $venta->cliente_identificacion)
    <div class="cliente">
        @if($venta->cliente_nombre)
        <div class="row">
            <span class="label">CLIENTE:</span>
            <span>{{ $venta->cliente_nombre }}</span>
        </div>
        @endif
        @if($venta->cliente_identificacion)
        <div class="row">
            <span class="label">{{ strlen($venta->cliente_identificacion) === 13 ? 'RUC' : 'CÉDULA' }}:</span>
            <span>{{ $venta->cliente_identificacion }}</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Productos -->
    <div class="productos">
        <div class="header-table">
            <span>PRODUCTO</span>
            <span>TOTAL</span>
        </div>

        @foreach($venta->detalles as $detalle)
        <div class="item">
            <div class="item-name">{{ $detalle->producto_nombre }}</div>
            <div class="item-detail">
                <span>{{ $detalle->cantidad }} x ${{ number_format($detalle->precio_unitario, 2) }}</span>
                <span>${{ number_format($detalle->subtotal, 2) }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Totales -->
    <div class="totales">
        <div class="row">
            <span>SUBTOTAL:</span>
            <span>${{ number_format($venta->subtotal, 2) }}</span>
        </div>
        <div class="row">
            <span>IVA (15%):</span>
            <span>${{ number_format($venta->iva, 2) }}</span>
        </div>
        <div class="row total-final">
            <span>TOTAL:</span>
            <span>${{ number_format($venta->total, 2) }}</span>
        </div>
    </div>

    <!-- Método de Pago -->
    <div class="pago">
        @if($venta->tipo_pago === 'efectivo')
            <div class="metodo">💵 EFECTIVO</div>
        @elseif($venta->tipo_pago === 'tarjeta')
            <div class="metodo">💳 TARJETA</div>
        @elseif($venta->tipo_pago === 'transferencia')
            <div class="metodo">🏦 TRANSFERENCIA</div>
        @elseif($venta->tipo_pago === 'deuna')
            <div class="metodo">📱 DEUNA</div>
        @elseif($venta->tipo_pago === 'mixto')
            <div class="metodo">🔄 PAGO MIXTO</div>
            @php
                $desglose = json_decode($venta->detalle_pago_mixto, true);
            @endphp
            <div class="pago-mixto">
                @foreach($desglose as $metodo => $monto)
                    @if($monto > 0)
                    <div class="item">
                        <span style="text-transform: capitalize;">{{ $metodo }}:</span>
                        <span>${{ number_format($monto, 2) }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este no es un documento tributario válido</p>
        <p>Para factura electrónica, solicítela en caja</p>
        <p class="gracias">¡GRACIAS POR SU COMPRA!</p>
        <p>Vuelva pronto a Inferno Club</p>
        <p style="margin-top: 10px; font-size: 10px;">
            www.infernoclub.ec | @infernoclub
        </p>
    </div>

    <!-- Información adicional si está anulada -->
    @if($venta->estado === 'anulada' && $venta->motivo_anulacion)
    <div style="margin-top: 15px; border-top: 2px dashed #000; padding-top: 10px; font-size: 10px;">
        <strong>MOTIVO DE ANULACIÓN:</strong>
        <p style="margin-top: 5px;">{{ $venta->motivo_anulacion }}</p>
    </div>
    @endif

</body>
<script>
    // Auto-imprimir al cargar (opcional, puedes comentar si no deseas esto)
    window.onload = function() {
        // Espera medio segundo para que la página cargue completamente
        setTimeout(function() {
            // window.print(); // Descomenta para auto-imprimir
        }, 500);
    };
</script>
</html>
