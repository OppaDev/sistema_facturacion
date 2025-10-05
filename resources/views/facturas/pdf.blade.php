<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->getNumeroFormateado() }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0 0 20px 0;
            color: #ffffff;
            font-size: 12px;
            background: #000000;
        }
        .pdf-container {
            max-width: 900px;
            margin: 0 auto;
            background: #000000;
            border: 3px solid #ff0000;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(255, 0, 0, 0.3);
            padding: 30px 30px 20px 30px;
        }
        .header {
            width: 100%;
            background: #ff0000;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .company-info {
            width: 100%;
        }
        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .company-details {
            font-size: 11px;
            color: #ffffff;
            opacity: 0.9;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #ff0000;
            margin: 20px 0 15px 0;
            text-align: center;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
            background: #ff0000;
            margin-left: 8px;
        }
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; color: #000000; }
        .badge-danger { background: #dc3545; }
        .badge-info { background: #17a2b8; }
        .badge-secondary { background: #6c757d; }
        .row {
            width: 100%;
            margin-bottom: 10px;
        }
        .row:after {
            content: "";
            display: table;
            clear: both;
        }
        .col {
            width: 32%;
            float: left;
            margin-right: 2%;
            min-height: 1px;
        }
        .col:last-child {
            margin-right: 0;
        }
        .card {
            background: rgba(255, 0, 0, 0.05);
            border-radius: 8px;
            padding: 15px 18px;
            margin-bottom: 18px;
            border: 2px solid rgba(255, 0, 0, 0.3);
        }
        .card-title {
            font-size: 14px;
            font-weight: bold;
            color: #ff0000;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-row {
            margin-bottom: 6px;
            font-size: 11px;
            color: #ffffff;
        }
        .info-label {
            font-weight: bold;
            color: #ff0000;
            text-transform: uppercase;
            font-size: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 18px 0 0 0;
            border: 2px solid rgba(255, 0, 0, 0.3);
            border-radius: 8px;
        }
        .table th {
            background: #ff0000;
            color: #ffffff;
            padding: 12px 8px;
            font-size: 11px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }
        .table td {
            padding: 10px 8px;
            border-bottom: 1px solid rgba(255, 0, 0, 0.2);
            font-size: 11px;
            color: #ffffff;
        }
        .table tr:nth-child(even) { background: rgba(255, 0, 0, 0.03); }
        .product-img {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 8px;
            vertical-align: middle;
        }
        .product-name {
            font-weight: bold;
            color: #ffffff;
        }
        .product-desc {
            color: #cccccc;
            font-size: 9px;
        }
        .quantity-badge {
            display: inline-block;
            background: #ff0000;
            color: #ffffff;
            border-radius: 8px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: bold;
        }
        .price {
            text-align: right;
            font-weight: bold;
            color: #ffffff;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
            background: rgba(255, 0, 0, 0.1);
            padding: 15px;
            border-radius: 8px;
            border: 2px solid rgba(255, 0, 0, 0.3);
        }
        .total-row {
            margin-bottom: 8px;
            font-size: 13px;
            color: #ffffff;
        }
        .total-amount {
            font-size: 22px;
            font-weight: bold;
            color: #ff0000;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid rgba(255, 0, 0, 0.5);
        }
        .qr-section {
            width: 100%;
            margin: 30px 0 10px 0;
        }
        .qr-section:after {
            content: "";
            display: table;
            clear: both;
        }
        .qr-block {
            width: 30%;
            float: left;
            text-align: center;
            margin-right: 3%;
        }
        .qr-block-title {
            font-weight: bold;
            color: #ff0000;
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .qr-image {
            width: 120px;
            height: 120px;
            margin-bottom: 8px;
            border: 2px solid #ff0000;
            border-radius: 8px;
            padding: 5px;
            background: #ffffff;
        }
        .firma-block {
            width: 65%;
            float: left;
            background: rgba(255, 0, 0, 0.05);
            border-radius: 8px;
            border: 2px solid rgba(255, 0, 0, 0.3);
            padding: 12px 18px;
        }
        .firma-title {
            color: #ff0000;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .firma-status {
            display: inline-block;
            background: #28a745;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            border-radius: 5px;
            padding: 3px 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .firma-status-pendiente {
            background: #ffc107;
            color: #000000;
        }
        .firma-desc {
            color: #cccccc;
            font-size: 9px;
            margin-bottom: 6px;
        }
        .firma-datos {
            font-size: 8px;
            word-break: break-all;
            background: rgba(0, 0, 0, 0.3);
            padding: 8px;
            border-radius: 5px;
            border: 1px solid rgba(255, 0, 0, 0.2);
            margin-top: 8px;
            color: #cccccc;
        }
        .qr-content {
            font-size: 8px;
            color: #ff0000;
            background: rgba(255, 0, 0, 0.05);
            border: 2px solid rgba(255, 0, 0, 0.3);
            border-radius: 5px;
            padding: 8px;
            margin-top: 10px;
            word-break: break-all;
        }
        .legal {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff0000;
            border-radius: 8px;
            padding: 12px;
            margin: 15px 0 0 0;
            font-size: 10px;
            color: #ffffff;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #cccccc;
            border-top: 2px solid rgba(255, 0, 0, 0.5);
            padding-top: 15px;
        }
        .qr-block {
            text-align: center;
            flex: 1;
        }
        .qr-block div:first-child {
            font-weight: bold;
            color: #ff0000;
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .qr-image {
            width: 120px;
            height: 120px;
            margin-bottom: 8px;
            border: 2px solid #ff0000;
            border-radius: 8px;
            padding: 5px;
            background: #ffffff;
        }
    </style>
</head>
<body>
<div class="pdf-container">
    <div class="header">
        <div class="company-info">
            <div class="company-name">INFERNO CLUB</div>
            <div class="company-details">
                Bebidas Alcohólicas y Cócteles Premium<br>
                Quito, El Condado, Pichincha<br>
                RUC: 1728167857001 | Email: facturacion@infernoclub.com
            </div>
        </div>
    </div>
    <div class="invoice-title">
        FACTURA ELECTRÓNICA #{{ $factura->getNumeroFormateado() }}
        <span class="badge badge-{{ $factura->estado === 'activa' ? 'success' : ($factura->estado === 'anulada' ? 'danger' : 'secondary') }}">{{ strtoupper($factura->estado) }}</span>
    </div>
    <div class="row">
        <div class="col card">
            <div class="card-title">Cliente</div>
            <div class="info-row"><span class="info-label">Nombre:</span> {{ $factura->cliente->name ?? 'Cliente eliminado' }}</div>
            @if($factura->cliente)
            <div class="info-row"><span class="info-label">Email:</span> {{ $factura->cliente->email ?? 'No especificado' }}</div>
            <div class="info-row"><span class="info-label">Teléfono:</span> {{ $factura->cliente->telefono ?? 'No especificado' }}</div>
            @endif
        </div>
        <div class="col card">
            <div class="card-title">Factura</div>
            <div class="info-row"><span class="info-label">Número:</span> #{{ $factura->getNumeroFormateado() }}</div>
            <div class="info-row"><span class="info-label">Fecha:</span> {{ $factura->created_at->format('d/m/Y') }}</div>
            <div class="info-row"><span class="info-label">Hora:</span> {{ $factura->created_at->format('H:i') }}</div>
            <div class="info-row"><span class="info-label">Vendedor:</span> {{ $factura->usuario->name ?? 'Usuario eliminado' }}</div>
        </div>
        <div class="col card">
            <div class="card-title">Datos SRI</div>
            <div class="info-row"><span class="info-label">Secuencial:</span> {{ $factura->getNumeroFormateado() }}</div>
            <div class="info-row"><span class="info-label">CUA:</span> {{ $factura->getCUAFormateado() }}</div>
            <div class="info-row"><span class="info-label">Ambiente:</span> {{ $factura->ambiente ?? 'PRODUCCION' }}</div>
            <div class="info-row"><span class="info-label">Estado SRI:</span> <span class="badge badge-{{ $factura->getEstadoAutorizacion() === 'AUTORIZADO' ? 'success' : ($factura->getEstadoAutorizacion() === 'PROCESANDO' ? 'warning' : 'info') }}">{{ $factura->getEstadoAutorizacion() }}</span></div>
        </div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->detalles as $index => $detalle)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    @if($detalle->producto && $detalle->producto->imagen)
                        <img src="{{ public_path('storage/productos/' . $detalle->producto->imagen) }}" class="product-img" alt="{{ $detalle->producto->nombre }}">
                    @endif
                    <span class="product-name">{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</span>
                    @if($detalle->producto && $detalle->producto->descripcion)
                        <div class="product-desc">{{ $detalle->producto->descripcion }}</div>
                    @endif
                </td>
                <td><span class="quantity-badge">{{ $detalle->cantidad }}</span></td>
                <td class="price">${{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="price">${{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="total-section">
        <div class="total-row"><span class="info-label">Subtotal:</span> ${{ number_format($factura->subtotal, 2) }}</div>
        <div class="total-row"><span class="info-label">IVA ({{ number_format(($factura->iva / max($factura->subtotal,1))*100, 0) }}%):</span> ${{ number_format($factura->iva, 2) }}</div>
        <div class="total-row total-amount"><span class="info-label">TOTAL:</span> ${{ number_format($factura->total, 2) }}</div>
    </div>
    <div class="qr-section">
        <div class="qr-block">
            <div class="qr-block-title">Código QR SRI</div>
            @if($factura->qr_code)
                <img src="data:image/png;base64,{{ $factura->qr_code }}" class="qr-image" alt="QR Code">
                <div style="font-size: 9px; color: #cccccc;">Escanee para verificar autenticidad</div>
            @else
                <div style="font-size: 10px; color: #dc3545;">QR no disponible</div>
            @endif
            @if($factura->contenido_qr)
            <div class="qr-content"><strong>Contenido QR:</strong> {{ $factura->contenido_qr }}</div>
            @endif
        </div>
        <div class="firma-block">
            <div class="firma-title">Firma Digital</div>
            @if($factura->isFirmada())
                <span class="firma-status">FIRMA VÁLIDA</span>
                <div class="firma-desc">La firma digital es válida</div>
            @else
                <span class="firma-status firma-status-pendiente">PENDIENTE</span>
                <div class="firma-desc">Requiere firma digital</div>
            @endif
            <div class="firma-datos"><strong>Firma:</strong><br>{{ $factura->firma_digital ?? 'No disponible' }}</div>
            <div class="firma-datos"><strong>Autorizado por:</strong> {{ $factura->usuario->name ?? 'Sistema' }}</div>
            <div class="firma-datos"><strong>Fecha de firma:</strong> {{ $factura->fecha_firma_digital ? $factura->fecha_firma_digital->format('d/m/Y H:i:s') : 'No firmada' }}</div>
        </div>
    </div>
    <div class="legal">
        <strong>AVISO IMPORTANTE:</strong> Esta factura electrónica ha sido generada por el Sistema de Rentas Internas del Ecuador. La firma digital y el código QR garantizan la autenticidad e integridad del documento. Cualquier modificación invalidará la factura.
    </div>
    <div class="footer">
        <p style="font-size: 16px; font-weight: bold; color: #ff0000; margin-bottom: 8px;">INFERNO CLUB</p>
        <p style="color: #ffffff; font-weight: bold;">Sistema de Facturación Electrónica</p>
        <p style="font-size: 9px; margin-top: 8px;">Esta factura cumple con los requisitos del SRI de Ecuador</p>
        <p style="font-size: 9px;">Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p style="font-size: 9px; margin-top: 10px; color: #888888;">Consumo responsable. Prohibida la venta a menores de edad.</p>
    </div>
</div>
</body>
</html> 