<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifica tu correo electrónico</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0000 50%, #cc0000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .verify-container {
            background: #1a1a1a;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(255, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 2.5rem 2rem 2rem 2rem;
            margin: 2rem;
            border: 2px solid #cc0000;
        }
        .verify-title {
            font-size: 2rem;
            font-weight: bold;
            color: #ff0000;
            margin-bottom: 0.5rem;
            text-align: center;
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        .inferno-logo {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .inferno-subtitle {
            text-align: center;
            color: #999;
            font-size: 0.9rem;
            letter-spacing: 2px;
            margin-bottom: 1.5rem;
        }
        .verify-icon {
            font-size: 3rem;
            color: #ff0000;
            margin-bottom: 1rem;
            display: flex;
            justify-content: center;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
            background-color: #1a3a1a;
            border: 1px solid #28a745;
            color: #a8f5a8;
        }
        .alert i {
            color: #28a745;
        }
        .btn-primary {
            background: linear-gradient(135deg, #cc0000 0%, #ff0000 100%);
            border: 2px solid #ff0000;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 0, 0, 0.4);
            color: #fff;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
            box-shadow: 0 6px 20px rgba(255, 0, 0, 0.6);
            transform: translateY(-2px);
            color: #fff;
        }
        .btn-link {
            color: #ff0000;
            text-decoration: underline;
            font-weight: 500;
        }
        .btn-link:hover {
            color: #cc0000;
        }
        .text-muted {
            color: #999 !important;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="inferno-logo">🔥</div>
        <div class="verify-title">INFERNO CLUB</div>
        <div class="inferno-subtitle">BAR & LOUNGE</div>
        
        <div class="verify-icon">
            <i class="bi bi-envelope-check-fill"></i>
        </div>
        <div style="font-size: 1.3rem; font-weight: 600; color: #ff0000; text-align: center; margin-bottom: 1rem;">
            Verifica tu correo electrónico
        </div>
        <div class="mb-4 text-muted text-center">
            ¡Gracias por registrarte! Antes de continuar, por favor verifica tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar.
            <br><br>Si no recibiste el correo, puedes solicitar uno nuevo.
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success text-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                Se ha enviado un enlace de verificación a tu correo electrónico.
            </div>
        @endif

        <div class="d-flex flex-column gap-2">
            <form method="POST" action="{{ route('verification.send') }}" class="mb-2">
                @csrf
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-arrow-repeat me-1"></i> Enviar correo de verificación
                </button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link w-100">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</body>
</html>
