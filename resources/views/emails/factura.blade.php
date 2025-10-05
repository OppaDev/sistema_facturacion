<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->getNumeroFormateado() }}</title>
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            line-height: 1.6;
            color: #ffffff;
            background-color: #000000;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background: #000000;
            border: 2px solid #ff0000;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.3);
        }
        .header {
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(90deg, transparent, #ff0000, transparent);
            filter: blur(5px);
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 1.1em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .content {
            background: linear-gradient(135deg, #1a0000 0%, #000000 100%);
            padding: 30px 20px;
        }
        .content h2 {
            color: #ff0000;
            font-size: 1.8em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-align: center;
        }
        .factura-info {
            background: rgba(255, 0, 0, 0.05);
            border: 2px solid rgba(255, 0, 0, 0.3);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .factura-info p {
            margin: 10px 0;
            font-size: 1.05em;
            color: #ffffff;
        }
        .factura-info strong {
            color: #ff0000;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 1px;
        }
        .estado-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .estado-emitida {
            background: #28a745;
            color: #ffffff;
        }
        .estado-firmada {
            background: #ffc107;
            color: #000000;
        }
        .estado-pendiente {
            background: #6c757d;
            color: #ffffff;
        }
        .mensaje-box {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff0000;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .mensaje-box h3 {
            color: #ff0000;
            margin-top: 0;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            border: 1px solid rgba(255, 0, 0, 0.2);
        }
        .info-section p {
            margin: 10px 0;
            color: #cccccc;
        }
        .footer {
            background: #000000;
            border-top: 3px solid #ff0000;
            color: #ffffff;
            padding: 25px 20px;
            text-align: center;
        }
        .footer-logo {
            color: #ff0000;
            font-size: 1.8em;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }
        .footer p {
            margin: 8px 0;
            color: #cccccc;
        }
        .footer strong {
            color: #ff0000;
        }
        .footer-disclaimer {
            font-size: 0.85em;
            color: #888888;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 0, 0, 0.2);
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #ff0000, transparent);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔥 INFERNO CLUB</h1>
            <p>Facturación Electrónica</p>
        </div>
        
        <div class="content">
            <h2>Factura #{{ $factura->getNumeroFormateado() }}</h2>
            
            <div class="factura-info">
                <p><strong>Cliente:</strong> {{ $factura->cliente->name ?? 'Cliente eliminado' }}</p>
                <p><strong>Fecha de Emisión:</strong> {{ $factura->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Total a Pagar:</strong> <span style="color: #ff0000; font-size: 1.3em; font-weight: 900;">${{ number_format($factura->total, 2) }}</span></p>
                <p><strong>Estado:</strong> 
                    @if($factura->isEmitida())
                        <span class="estado-badge estado-emitida">✓ EMITIDA</span>
                    @elseif($factura->isFirmada())
                        <span class="estado-badge estado-firmada">✓ FIRMADA</span>
                    @else
                        <span class="estado-badge estado-pendiente">⏳ PENDIENTE</span>
                    @endif
                </p>
            </div>
            
            @if(isset($mensaje) && !empty($mensaje))
                <div class="mensaje-box">
                    <h3>📋 Mensaje Adicional:</h3>
                    <p style="color: #ffffff;">{{ $mensaje }}</p>
                </div>
            @endif
            
            <div class="divider"></div>
            
            <div class="info-section">
                <p style="font-weight: 700; color: #ffffff; font-size: 1.1em;">📄 Factura Electrónica Adjunta</p>
                <p>La factura se encuentra adjunta a este email en formato PDF.</p>
                <p style="font-size: 0.95em; margin-top: 15px;">
                    ✓ Esta factura electrónica cumple con los requisitos del SRI de Ecuador.<br>
                    ✓ Documento válido para efectos tributarios.
                </p>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-logo">INFERNO CLUB</div>
            <p><strong>Bebidas Alcohólicas y Cócteles Premium</strong></p>
            <p>Servicio 24/7 - Sin límites de horario</p>
            
            <div style="margin: 15px 0;">
                <p>📍 Quito, El Condado, Pichincha</p>
                <p>📱 RUC: 1728167857001</p>
                <p>📧 Email: facturacion@infernoclub.com</p>
            </div>
            
            <div class="footer-disclaimer">
                <p>Este es un email automático del sistema de facturación de Inferno Club.</p>
                <p>Por favor no responda a este mensaje. Para consultas contacte a nuestro servicio al cliente.</p>
                <p style="margin-top: 10px; font-size: 0.8em;">
                    🔞 Consumo responsable. Prohibida la venta a menores de edad.
                </p>
            </div>
        </div>
    </div>
</body>
</html> 