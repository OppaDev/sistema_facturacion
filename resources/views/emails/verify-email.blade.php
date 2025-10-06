<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Email - Inferno Club</title>
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
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
            border-bottom: 3px solid #ff0000;
        }
        .header h1 {
            margin: 10px 0 0 0;
            font-size: 32px;
            font-weight: 700;
            color: #ff0000;
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        .content {
            padding: 40px 30px;
            background-color: #1a1a1a;
        }
        .content h2 {
            color: #ff0000;
            font-size: 22px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .content p {
            line-height: 1.6;
            color: #cccccc;
            margin-bottom: 20px;
        }
        .content strong {
            color: #ff0000;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .verify-button {
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
        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 0, 0, 0.6);
        }
        .alternative-link {
            margin-top: 30px;
            padding: 20px;
            background-color: #0a0a0a;
            border-radius: 6px;
            border-left: 4px solid #ff0000;
        }
        .alternative-link p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #999;
        }
        .alternative-link a {
            color: #ff0000;
            word-break: break-all;
            font-size: 13px;
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
        .info-box {
            background-color: #2a0000;
            border-left: 4px solid #ff0000;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            color: #ffcccc;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div style="font-size: 48px; margin-bottom: 10px;">🔥</div>
            <h1>INFERNO CLUB</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; color: #999; letter-spacing: 2px;">BAR & LOUNGE</p>
        </div>
        
        <div class="content">
            <h2>¡Hola, {{ $userName }}!</h2>
            
            <p>Gracias por registrarte en <strong>Inferno Club</strong>. Estamos emocionados de tenerte con nosotros.</p>
            
            <p>Para completar tu registro y activar tu cuenta, por favor verifica tu dirección de correo electrónico haciendo clic en el botón de abajo:</p>
            
            <div class="button-container">
                <a href="{{ $verificationUrl }}" class="verify-button">
                    Verificar mi Email
                </a>
            </div>
            
            <div class="info-box">
                <p><strong>Nota:</strong> Este enlace de verificación expirará en 60 minutos por seguridad.</p>
            </div>
            
            <div class="alternative-link">
                <p><strong>¿El botón no funciona?</strong></p>
                <p>Copia y pega el siguiente enlace en tu navegador:</p>
                <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
            </div>
            
            <p style="margin-top: 30px; color: #999; font-size: 14px;">
                Si no creaste una cuenta en Inferno Club, puedes ignorar este correo de forma segura.
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
