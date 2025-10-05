<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inferno Club | Sistema de Gestión Empresarial</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --inferno-red: #ff0000;
            --inferno-black: #000000;
            --inferno-dark-red: #cc0000;
            --inferno-light-red: #ff3333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--inferno-black);
            font-family: 'Poppins', sans-serif;
            color: #fff;
            overflow-x: hidden;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--inferno-red);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.3);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 900;
            font-size: 1.8rem;
            color: var(--inferno-red) !important;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            text-shadow: 0 0 20px var(--inferno-red);
            transform: scale(1.05);
        }
        
        .navbar-brand img {
            height: 50px;
            margin-right: 15px;
            filter: drop-shadow(0 0 10px rgba(255, 0, 0, 0.5));
        }
        
        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--inferno-red);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        .nav-link:hover {
            color: var(--inferno-red) !important;
        }
        
        .navbar-toggler {
            border-color: var(--inferno-red);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 0, 0, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--inferno-black) 0%, #1a0000 100%);
            padding: 120px 0 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255, 0, 0, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .hero-logo {
            max-width: 300px;
            margin-bottom: 30px;
            filter: drop-shadow(0 0 30px rgba(255, 0, 0, 0.8));
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            color: var(--inferno-red);
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 0 0 30px rgba(255, 0, 0, 0.5);
            margin-bottom: 20px;
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from { text-shadow: 0 0 20px rgba(255, 0, 0, 0.5); }
            to { text-shadow: 0 0 40px rgba(255, 0, 0, 1), 0 0 60px rgba(255, 0, 0, 0.5); }
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            color: #ddd;
            margin-bottom: 30px;
            line-height: 1.8;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 300;
        }
        
        .hero-badge {
            display: inline-block;
            background: var(--inferno-red);
            color: #fff;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.4);
        }
        
        .btn-inferno {
            background: var(--inferno-red);
            color: #fff;
            border: 2px solid var(--inferno-red);
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.4);
        }
        
        .btn-inferno:hover {
            background: transparent;
            color: var(--inferno-red);
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 0, 0, 0.6);
        }
        
        .btn-inferno-outline {
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-inferno-outline:hover {
            background: #fff;
            color: var(--inferno-black);
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 255, 255, 0.3);
        }
        
        /* Features Section */
        .features {
            padding: 80px 0;
            background: var(--inferno-black);
            position: relative;
        }
        
        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--inferno-red), transparent);
        }
        
        .section-title {
            font-size: 3rem;
            font-weight: 900;
            color: var(--inferno-red);
            text-align: center;
            margin-bottom: 60px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .feature-card {
            background: linear-gradient(135deg, #1a0000 0%, var(--inferno-black) 100%);
            border: 2px solid rgba(255, 0, 0, 0.2);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 0, 0, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .feature-card:hover {
            border-color: var(--inferno-red);
            transform: translateY(-10px);
            box-shadow: 0 10px 40px rgba(255, 0, 0, 0.3);
        }
        
        .feature-card:hover::before {
            opacity: 1;
        }
        
        .feature-icon {
            font-size: 4rem;
            color: var(--inferno-red);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.2) rotate(360deg);
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .feature-description {
            color: #bbb;
            line-height: 1.8;
            font-weight: 300;
        }
        
        /* Stats Section */
        .stats {
            background: var(--inferno-red);
            padding: 60px 0;
            margin: 80px 0;
        }
        
        .stat-item {
            text-align: center;
            color: #fff;
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: 900;
            display: block;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #1a0000 0%, var(--inferno-black) 100%);
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255, 0, 0, 0.15) 0%, transparent 70%);
        }
        
        .cta-content {
            position: relative;
            z-index: 2;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        .cta-subtitle {
            font-size: 1.2rem;
            color: #bbb;
            margin-bottom: 40px;
            font-weight: 300;
        }
        
        /* Footer */
        .footer {
            background: var(--inferno-black);
            border-top: 2px solid var(--inferno-red);
            padding: 40px 0 20px 0;
            text-align: center;
        }
        
        .footer-logo {
            max-width: 150px;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(255, 0, 0, 0.5));
        }
        
        .footer-text {
            color: #888;
            margin-bottom: 10px;
        }
        
        .footer-text strong {
            color: var(--inferno-red);
            font-weight: 700;
        }
        
        .footer a {
            color: var(--inferno-red);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer a:hover {
            color: var(--inferno-light-red);
            text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }
        
        .social-links {
            margin: 20px 0;
        }
        
        .social-links a {
            display: inline-block;
            width: 45px;
            height: 45px;
            line-height: 45px;
            border-radius: 50%;
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid var(--inferno-red);
            color: var(--inferno-red);
            font-size: 1.3rem;
            margin: 0 8px;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--inferno-red);
            color: #fff;
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(255, 0, 0, 0.4);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .hero-logo {
                max-width: 200px;
            }
            
            .btn-inferno,
            .btn-inferno-outline {
                padding: 12px 30px;
                font-size: 1rem;
                margin: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('img/inferno-logo.png') }}" alt="Inferno Club" onerror="this.style.display='none'">
                INFERNO CLUB
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#funciones">Funcionalidades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#nosotros">Acerca del Sistema</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contacto">Acceso</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class='bx bx-log-in me-1'></i> Iniciar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <img src="{{ asset('img/inferno-logo.png') }}" alt="Inferno Club Logo" class="hero-logo" onerror="this.style.display='none'">
                <div class="hero-badge">Sistema de Gestión Empresarial</div>
                <h1 class="hero-title">INFERNO CLUB</h1>
                <p class="hero-subtitle">
                    Sistema integral de inventario y facturación diseñado para la gestión eficiente 
                    de bebidas alcohólicas y productos premium. Plataforma exclusiva para personal autorizado 
                    con control total de operaciones, inventario en tiempo real y facturación electrónica.
                </p>
                <div class="mt-4">
                    <a href="{{ route('login') }}" class="btn btn-inferno me-3">Acceder al Sistema</a>
                    <a href="#funciones" class="btn btn-inferno-outline">Ver Funcionalidades</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 mb-4 mb-md-0">
                    <div class="stat-item">
                        <span class="stat-number"><i class='bx bx-package'></i></span>
                        <span class="stat-label">Control de Inventario</span>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4 mb-md-0">
                    <div class="stat-item">
                        <span class="stat-number"><i class='bx bx-receipt'></i></span>
                        <span class="stat-label">Facturación Electrónica</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number"><i class='bx bx-bar-chart-alt-2'></i></span>
                        <span class="stat-label">Reportes en Tiempo Real</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <span class="stat-number"><i class='bx bx-shield-alt-2'></i></span>
                        <span class="stat-label">Acceso Seguro</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="funciones">
        <div class="container">
            <h2 class="section-title">Módulos del Sistema</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class='bx bx-package'></i></div>
                        <h3 class="feature-title">Gestión de Productos</h3>
                        <p class="feature-description">
                            Control completo del inventario de bebidas y productos. 
                            Administración de stock, categorías, precios y movimientos en tiempo real.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class='bx bx-receipt'></i></div>
                        <h3 class="feature-title">Facturación Electrónica</h3>
                        <p class="feature-description">
                            Emisión y gestión de facturas electrónicas. 
                            Cumplimiento normativo con integración SRI Ecuador.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class='bx bx-user-circle'></i></div>
                        <h3 class="feature-title">Control de Usuarios</h3>
                        <p class="feature-description">
                            Gestión de roles y permisos. Sistema de autenticación seguro 
                            con niveles de acceso diferenciados por perfil.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                        <h3 class="feature-title">Reportes y Análisis</h3>
                        <p class="feature-description">
                            Dashboards ejecutivos con métricas clave. 
                            Exportación de datos y reportes detallados de operaciones.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class='bx bx-history'></i></div>
                        <h3 class="feature-title">Auditoría Completa</h3>
                        <p class="feature-description">
                            Registro detallado de todas las operaciones del sistema. 
                            Trazabilidad total de cambios y acciones realizadas.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class='bx bx-cog'></i></div>
                        <h3 class="feature-title">Configuración Avanzada</h3>
                        <p class="feature-description">
                            Parametrización del sistema según necesidades del negocio. 
                            Gestión de categorías, impuestos y opciones empresariales.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="cta-section" id="nosotros">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Sistema Empresarial Profesional</h2>
                <p class="cta-subtitle" style="max-width: 900px; margin: 0 auto 40px auto;">
                    Plataforma desarrollada específicamente para la gestión integral de Inferno Club. 
                    Optimiza la administración de inventario, ventas y facturación de bebidas alcohólicas 
                    con herramientas profesionales de control operativo. Sistema robusto con arquitectura 
                    segura, diseñado para soportar operaciones las 24 horas del día, los 7 días de la semana, 
                    garantizando la continuidad del negocio y la precisión en cada transacción.
                </p>
            </div>
        </div>
    </section>

    <!-- Call to Action / Contacto -->
    <section class="features" id="contacto" style="padding: 100px 0;">
        <div class="container text-center">
            <h2 class="section-title mb-4">Acceso Restringido - Personal Autorizado</h2>
            <p class="hero-subtitle mb-5">
                Este sistema es de uso exclusivo para el personal autorizado de Inferno Club.<br>
                Si eres parte del equipo, inicia sesión para acceder a todas las funcionalidades.
            </p>
            <div>
                <a href="{{ route('login') }}" class="btn btn-inferno btn-lg">
                    <i class='bx bx-lock-alt me-2'></i> Iniciar Sesión
                </a>
            </div>
            <p class="mt-4 text-muted small">
                <i class='bx bx-info-circle me-1'></i> 
                Para solicitar acceso, contacta al administrador del sistema
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <img src="{{ asset('img/inferno-logo.png') }}" alt="Inferno Club" class="footer-logo" onerror="this.style.display='none'">
            <h3 style="color: var(--inferno-red); font-weight: 900; font-size: 1.8rem; margin-bottom: 15px;">INFERNO CLUB</h3>
            <p class="footer-text">Sistema de Gestión Empresarial</p>
            <p class="footer-text"><strong>Versión 3.3.3</strong> - Plataforma de Inventario y Facturación</p>
            
            <div class="mt-4">
                <p class="footer-text mb-2">
                    <i class='bx bx-shield-alt-2 me-1'></i> 
                    Sistema seguro con encriptación de datos y auditoría completa
                </p>
            </div>
            
            <div class="mt-4">
                <p class="footer-text mb-1">
                    &copy; {{ date('Y') }} <strong>Inferno Club</strong>. Todos los derechos reservados.
                </p>
                <small class="footer-text">
                    <i class='bx bx-info-circle me-1'></i> 
                    Acceso restringido - Solo personal autorizado | 
                    <a href="mailto:soporte@infernoclub.com">Soporte técnico</a>
                </small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
