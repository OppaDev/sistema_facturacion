<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cierre - Turno #{{ $turno->id }}</title>
    <style>
        /* Reset y configuración de página */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }

        /* Encabezado */
        .header {
            text-align: center;
            border-bottom: 3px solid #ff0000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #ff0000;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }

        .header .document-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            color: #000;
        }

        /* Información del turno */
        .turno-info {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ff0000;
        }

        .turno-info table {
            width: 100%;
        }

        .turno-info td {
            padding: 5px;
        }

        .turno-info .label {
            font-weight: bold;
            width: 150px;
        }

        .turno-info .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        /* Sección */
        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #ff0000;
            border-bottom: 2px solid #ff0000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }

        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }

        table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-success {
            color: #28a745;
        }

        .fw-bold {
            font-weight: bold;
        }

        /* Cards de estadísticas */
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-card {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .stat-card .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #ff0000;
        }

        /* Resumen financiero */
        .financial-summary {
            background-color: #fff5f5;
            border: 2px solid #ff0000;
            padding: 15px;
            margin-top: 20px;
        }

        .financial-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .financial-row .label {
            display: table-cell;
            width: 70%;
            font-weight: bold;
        }

        .financial-row .value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: bold;
        }

        .financial-row.total {
            font-size: 16px;
            border-top: 2px solid #ff0000;
            padding-top: 10px;
            margin-top: 10px;
            color: #ff0000;
        }

        .diferencia-box {
            margin-top: 15px;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid;
        }

        .diferencia-box.sin-diferencia {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .diferencia-box.sobrante {
            background-color: #d1ecf1;
            border-color: #0c5460;
            color: #0c5460;
        }

        .diferencia-box.faltante {
            background-color: #f8d7da;
            border-color: #721c24;
            color: #721c24;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 15mm;
            width: calc(100% - 30mm);
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .firmas {
            display: table;
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .firma {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .firma-linea {
            border-top: 2px solid #000;
            width: 200px;
            margin: 0 auto 5px;
        }

        /* Saltos de página */
        .page-break {
            page-break-after: always;
        }

        /* Observaciones */
        .observaciones {
            background-color: #f9f9f9;
            padding: 10px;
            border-left: 3px solid #ff0000;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header">
        <h1>INFERNO CLUB</h1>
        <div class="subtitle">Bebidas Alcohólicas & Cócteles</div>
        <div class="subtitle">Abierto 24/7</div>
        <div class="subtitle">RUC: 1234567890001 | Tel: (02) 123-4567</div>
        <div class="document-title">REPORTE DE CIERRE DE TURNO</div>
        <div style="font-size: 12px; margin-top: 5px;">
            Turno #{{ $turno->id }}
        </div>
    </div>

    <!-- Información del Turno -->
    <div class="turno-info">
        <table>
            <tr>
                <td class="label">Turno ID:</td>
                <td>#{{ $turno->id }}</td>
                <td class="label">Estado:</td>
                <td>
                    @if($turno->estado === 'abierto')
                        <span class="badge badge-success">ABIERTO</span>
                    @else
                        <span class="badge badge-secondary">CERRADO</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Cajero:</td>
                <td>{{ $turno->usuario->name }}</td>
                <td class="label">Email:</td>
                <td>{{ $turno->usuario->email }}</td>
            </tr>
            <tr>
                <td class="label">Fecha Apertura:</td>
                <td>{{ $turno->fecha_apertura->format('d/m/Y H:i:s') }}</td>
                <td class="label">Fecha Cierre:</td>
                <td>
                    @if($turno->estado === 'cerrado')
                        {{ $turno->fecha_cierre->format('d/m/Y H:i:s') }}
                    @else
                        <em>En curso</em>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Duración:</td>
                <td colspan="3">
                    @php
                        $duracion = $turno->fecha_apertura->diff($turno->estado === 'cerrado' ? $turno->fecha_cierre : now());
                        $horas = $duracion->days * 24 + $duracion->h;
                    @endphp
                    {{ $horas }} horas {{ $duracion->i }} minutos
                </td>
            </tr>
        </table>
    </div>

    <!-- Estadísticas Generales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">TOTAL VENTAS</div>
            <div class="value">{{ $turno->ventas->where('estado', 'completada')->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="label">MONTO INICIAL</div>
            <div class="value">${{ number_format($turno->monto_inicial, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">MONTO FINAL</div>
            <div class="value">
                @if($turno->estado === 'cerrado')
                    ${{ number_format($turno->monto_final, 2) }}
                @else
                    -
                @endif
            </div>
        </div>
    </div>

    @php
        $ventasCompletadas = $turno->ventas->where('estado', 'completada');
        $totalVentas = $ventasCompletadas->sum('total');
        $totalEfectivo = $ventasCompletadas->where('tipo_pago', 'efectivo')->sum('total');
        $totalTarjeta = $ventasCompletadas->where('tipo_pago', 'tarjeta')->sum('total');
        $totalTransferencia = $ventasCompletadas->where('tipo_pago', 'transferencia')->sum('total');
        $totalDeuna = $ventasCompletadas->where('tipo_pago', 'deuna')->sum('total');
        
        // Calcular mixtos
        $mixtos = $ventasCompletadas->where('tipo_pago', 'mixto');
        $mixtoEfectivo = 0;
        $mixtoTarjeta = 0;
        $mixtoTransferencia = 0;
        $mixtoDeuna = 0;
        
        foreach($mixtos as $venta) {
            if ($venta->detalle_pago_mixto) {
                $desglose = json_decode($venta->detalle_pago_mixto, true);
                $mixtoEfectivo += $desglose['efectivo'] ?? 0;
                $mixtoTarjeta += $desglose['tarjeta'] ?? 0;
                $mixtoTransferencia += $desglose['transferencia'] ?? 0;
                $mixtoDeuna += $desglose['deuna'] ?? 0;
            }
        }
        
        $totalEfectivoFinal = $totalEfectivo + $mixtoEfectivo;
        $totalTarjetaFinal = $totalTarjeta + $mixtoTarjeta;
        $totalTransferenciaFinal = $totalTransferencia + $mixtoTransferencia;
        $totalDeunaFinal = $totalDeuna + $mixtoDeuna;
        
        // Movimientos
        $totalIngresos = $turno->movimientos->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = $turno->movimientos->where('tipo', 'egreso')->sum('monto');
    @endphp

    <!-- Desglose por Método de Pago -->
    <div class="section">
        <div class="section-title">Desglose por Método de Pago</div>
        <table>
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>💵 Efectivo</td>
                    <td class="text-center">{{ $ventasCompletadas->where('tipo_pago', 'efectivo')->count() + $mixtos->count() }}</td>
                    <td class="text-right fw-bold">${{ number_format($totalEfectivoFinal, 2) }}</td>
                </tr>
                <tr>
                    <td>💳 Tarjeta</td>
                    <td class="text-center">{{ $ventasCompletadas->where('tipo_pago', 'tarjeta')->count() + ($mixtoTarjeta > 0 ? $mixtos->count() : 0) }}</td>
                    <td class="text-right fw-bold">${{ number_format($totalTarjetaFinal, 2) }}</td>
                </tr>
                <tr>
                    <td>🏦 Transferencia</td>
                    <td class="text-center">{{ $ventasCompletadas->where('tipo_pago', 'transferencia')->count() + ($mixtoTransferencia > 0 ? $mixtos->count() : 0) }}</td>
                    <td class="text-right fw-bold">${{ number_format($totalTransferenciaFinal, 2) }}</td>
                </tr>
                <tr>
                    <td>📱 Deuna</td>
                    <td class="text-center">{{ $ventasCompletadas->where('tipo_pago', 'deuna')->count() + ($mixtoDeuna > 0 ? $mixtos->count() : 0) }}</td>
                    <td class="text-right fw-bold">${{ number_format($totalDeunaFinal, 2) }}</td>
                </tr>
                <tr style="background-color: #fff5f5;">
                    <td colspan="2" class="fw-bold" style="color: #ff0000;">TOTAL VENTAS</td>
                    <td class="text-right fw-bold" style="color: #ff0000; font-size: 14px;">
                        ${{ number_format($totalVentas, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Movimientos de Caja -->
    @if($turno->movimientos->count() > 0)
    <div class="section">
        <div class="section-title">Movimientos de Caja</div>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th class="text-right">Monto</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($turno->movimientos as $movimiento)
                <tr>
                    <td>{{ $movimiento->created_at->format('H:i:s') }}</td>
                    <td>
                        @if($movimiento->tipo === 'ingreso')
                            ➕ Ingreso
                        @elseif($movimiento->tipo === 'egreso')
                            ➖ Egreso
                        @else
                            🔄 Ajuste
                        @endif
                    </td>
                    <td>{{ $movimiento->concepto }}</td>
                    <td class="text-right fw-bold {{ $movimiento->tipo === 'ingreso' ? 'text-success' : 'text-danger' }}">
                        {{ $movimiento->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($movimiento->monto, 2) }}
                    </td>
                    <td>{{ $movimiento->usuario->name }}</td>
                </tr>
                @endforeach
                <tr style="background-color: #f5f5f5;">
                    <td colspan="3" class="fw-bold">TOTAL INGRESOS</td>
                    <td class="text-right fw-bold text-success">+${{ number_format($totalIngresos, 2) }}</td>
                    <td></td>
                </tr>
                <tr style="background-color: #f5f5f5;">
                    <td colspan="3" class="fw-bold">TOTAL EGRESOS</td>
                    <td class="text-right fw-bold text-danger">-${{ number_format($totalEgresos, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Resumen Financiero -->
    @if($turno->estado === 'cerrado')
    <div class="financial-summary">
        <div class="section-title" style="border: none; margin-bottom: 15px;">
            Resumen Financiero - Cálculo de Efectivo
        </div>

        <div class="financial-row">
            <div class="label">Monto Inicial:</div>
            <div class="value">${{ number_format($turno->monto_inicial, 2) }}</div>
        </div>

        <div class="financial-row">
            <div class="label">+ Ventas en Efectivo:</div>
            <div class="value text-success">+${{ number_format($totalEfectivoFinal, 2) }}</div>
        </div>

        @if($totalIngresos > 0)
        <div class="financial-row">
            <div class="label">+ Ingresos de Caja:</div>
            <div class="value text-success">+${{ number_format($totalIngresos, 2) }}</div>
        </div>
        @endif

        @if($totalEgresos > 0)
        <div class="financial-row">
            <div class="label">- Egresos de Caja:</div>
            <div class="value text-danger">-${{ number_format($totalEgresos, 2) }}</div>
        </div>
        @endif

        @php
            $efectivoEsperado = $turno->monto_inicial + $totalEfectivoFinal + $totalIngresos - $totalEgresos;
        @endphp

        <div class="financial-row total">
            <div class="label">EFECTIVO ESPERADO:</div>
            <div class="value">${{ number_format($efectivoEsperado, 2) }}</div>
        </div>

        <div class="financial-row" style="margin-top: 15px; font-size: 14px;">
            <div class="label">Monto Final Contado:</div>
            <div class="value">${{ number_format($turno->monto_final, 2) }}</div>
        </div>

        @php
            $diferencia = $turno->monto_final - $efectivoEsperado;
        @endphp

        @if(abs($diferencia) < 0.01)
            <div class="diferencia-box sin-diferencia">
                ✓ SIN DIFERENCIA - CUADRA PERFECTO
            </div>
        @elseif($diferencia > 0)
            <div class="diferencia-box sobrante">
                SOBRANTE: +${{ number_format($diferencia, 2) }}
            </div>
        @else
            <div class="diferencia-box faltante">
                FALTANTE: ${{ number_format($diferencia, 2) }}
            </div>
        @endif
    </div>
    @endif

    <!-- Observaciones -->
    @if($turno->observaciones_apertura)
    <div class="observaciones">
        <strong>Observaciones de Apertura:</strong><br>
        {{ $turno->observaciones_apertura }}
    </div>
    @endif

    @if($turno->estado === 'cerrado' && $turno->observaciones_cierre)
    <div class="observaciones">
        <strong>Observaciones de Cierre:</strong><br>
        {{ $turno->observaciones_cierre }}
    </div>
    @endif

    <!-- Salto de página -->
    <div class="page-break"></div>

    <!-- Detalle de Ventas (Segunda Página) -->
    <div class="section">
        <div class="section-title">Detalle de Ventas del Turno</div>
        
        @if($ventasCompletadas->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Método</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">IVA</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasCompletadas as $venta)
                <tr>
                    <td>#{{ $venta->numero_venta }}</td>
                    <td>{{ $venta->created_at->format('H:i:s') }}</td>
                    <td>
                        @if($venta->cliente_nombre)
                            {{ Str::limit($venta->cliente_nombre, 20) }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-transform: capitalize;">{{ $venta->tipo_pago }}</td>
                    <td class="text-right">${{ number_format($venta->subtotal, 2) }}</td>
                    <td class="text-right">${{ number_format($venta->iva, 2) }}</td>
                    <td class="text-right fw-bold">${{ number_format($venta->total, 2) }}</td>
                </tr>
                @endforeach
                <tr style="background-color: #fff5f5;">
                    <td colspan="6" class="fw-bold text-right" style="color: #ff0000;">TOTAL:</td>
                    <td class="text-right fw-bold" style="color: #ff0000; font-size: 13px;">
                        ${{ number_format($totalVentas, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
        @else
        <p style="text-align: center; padding: 20px; color: #666;">
            No hay ventas registradas en este turno
        </p>
        @endif
    </div>

    <!-- Firmas -->
    <div class="firmas">
        <div class="firma">
            <div class="firma-linea"></div>
            <div class="fw-bold">{{ $turno->usuario->name }}</div>
            <div style="font-size: 10px; color: #666;">Cajero</div>
        </div>
        <div class="firma">
            <div class="firma-linea"></div>
            <div class="fw-bold">_________________</div>
            <div style="font-size: 10px; color: #666;">Supervisor/Administrador</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i:s') }}<br>
        Inferno Club - Sistema de Facturación v2.0
    </div>

</body>
</html>
