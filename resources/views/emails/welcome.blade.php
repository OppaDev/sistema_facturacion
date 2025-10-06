<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Inferno Club</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #0a0a0a;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.3);
            border: 2px solid #cc0000;
        }
        .header {
            background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #cc0000 100%);
            padding: 50px 30px;
            text-align: center;
            color: #ffffff;
            border-bottom: 3px solid #ff0000;
        }
        .header h1 {
            margin: 10px 0;
            font-size: 36px;
            font-weight: 700;
            color: #ff0000;
            text-shadow: 0 0 15px rgba(255, 0, 0, 0.5);
        }
        .header p {
            margin: 0;
            font-size: 16px;
            color: #cccccc;
        }
        .content {
            padding: 40px 30px;
            background-color: #1a1a1a;
        }
        .content h2 {
            color: #ff0000;
            font-size: 26px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .content p {
            line-height: 1.8;
            color: #cccccc;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .content strong {
            color: #ff0000;
        }
        .features {
            background-color: #0a0a0a;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
            border: 1px solid #cc0000;
        }
        .features h3 {
            color: #ff0000;
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .feature-item {
            display: flex;
            align-items: start;
            margin-bottom: 15px;
        }
        .feature-item:last-child {
            margin-bottom: 0;
        }
        .feature-icon {
            font-size: 24px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .feature-text {
            flex: 1;
        }
        .feature-text strong {
            color: #ff0000;
            display: block;
            margin-bottom: 5px;
        }
        .feature-text span {
            color: #999;
            font-size: 14px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .action-button {
            display: inline-block;
            padding: 16px 50px;
            background: linear-gradient(135deg, #cc0000 0%, #ff0000 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 0, 0, 0.4);
            border: 2px solid #ff0000;
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 0, 0, 0.6);
        }
        .credentials-box {
            background-color: #2a0000;
            border-left: 4px solid #ff0000;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .credentials-box p {
            margin: 0 0 10px 0;
            color: #ffcccc;
        }
        .credentials-box strong {
            color: #ff0000;
        }
        .email-highlight {
            font-size: 18px;
            font-weight: 700;
            color: #ff0000 !important;
            background-color: #0a0a0a;
            padding: 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .tips {
            background-color: #0a0a0a;
            border-left: 4px solid #ff0000;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border: 1px solid #330000;
        }
        .tips h4 {
            margin-top: 0;
            color: #ff0000;
            font-size: 16px;
            font-weight: 700;
        }
        .tips ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        .tips li {
            color: #cccccc;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .footer {
            background-color: #000000;
            padding: 30px;
            text-align: center;
            border-top: 2px solid #cc0000;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #999;
        }
        .footer a {
            color: #ff0000;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div style="font-size: 60px; margin-bottom: 10px;">🔥</div>
            <h1>¡BIENVENIDO!</h1>
            <p style="font-size: 14px; letter-spacing: 2px; color: #999; margin-top: 10px;">INFERNO CLUB - BAR & LOUNGE</p>
            <p>Tu cuenta ha sido creada exitosamente</p>
        </div>
        
        <div class="content">
            <h2>¡Hola, {{ $userName }}!</h2>
            
            <p>Es un placer darte la bienvenida a <strong>Inferno Club</strong>. Tu cuenta ha sido creada y ya puedes acceder a todas las funcionalidades del sistema.</p>
            
            <div class="credentials-box">
                <p><strong>📧 Tu correo de acceso:</strong></p>
                <p class="email-highlight">{{ $userEmail }}</p>
                <p style="margin-top: 15px; font-size: 14px;">Usa este correo junto con tu contraseña para iniciar sesión.</p>
            </div>
            
            <div class="features">
                <h3>¿Qué puedes hacer ahora?</h3>
                
                <div class="feature-item">
                    <span class="feature-icon">📊</span>
                    <div class="feature-text">
                        <strong>Gestionar Ventas</strong>
                        <span>Registra y consulta todas las ventas del negocio</span>
                    </div>
                </div>
                
                <div class="feature-item">
                    <span class="feature-icon">📦</span>
                    <div class="feature-text">
                        <strong>Control de Productos</strong>
                        <span>Administra tu inventario y catálogo de productos</span>
                    </div>
                </div>
                
                <div class="feature-item">
                    <span class="feature-icon">💰</span>
                    <div class="feature-text">
                        <strong>Módulo de Caja</strong>
                        <span>Maneja turnos, movimientos y cierre de caja</span>
                    </div>
                </div>
                
                <div class="feature-item">
                    <span class="feature-icon">📈</span>
                    <div class="feature-text">
                        <strong>Reportes y Auditoría</strong>
                        <span>Consulta reportes detallados y rastrea cambios</span>
                    </div>
                </div>
            </div>
            
            <div class="button-container">
                <a href="{{ $loginUrl }}" class="action-button">
                    Iniciar Sesión Ahora
                </a>
            </div>
            
            <div class="tips">
                <h4>💡 Consejos para empezar:</h4>
                <ul>
                    <li>Completa tu perfil con toda tu información</li>
                    <li>Familiarízate con el panel de control</li>
                    <li>Explora cada módulo disponible según tus permisos</li>
                    <li>Si tienes dudas, contacta con el administrador</li>
                </ul>
            </div>
            
            <p style="margin-top: 30px; color: #999; font-size: 14px;">
                Si no solicitaste esta cuenta, por favor contacta inmediatamente al administrador del sistema.
            </p>
        </div>
        
        <div class="footer">
            <p><strong>Inferno Club</strong></p>
            <p>&copy; {{ date('Y') }} Inferno Club. Todos los derechos reservados.</p>
            <p style="margin-top: 10px;">
                ¿Necesitas ayuda? <a href="mailto:{{ config('mail.from.address') }}">Contáctanos</a>
            </p>
        </div>
    </div>
</body>
</html>
