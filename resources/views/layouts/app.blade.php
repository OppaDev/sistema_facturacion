<!doctype html>

<html lang="es" class="layout-menu-fixed layout-compact" data-assets-path="/sneat/assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title', 'Sistema de Inventario')</title>
    
    <meta name="description" content="Sistema de Inventario y Facturación" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/public/img/inferno-logo.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="/sneat/assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="/sneat/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="/sneat/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="/sneat/assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Helpers -->
    <script src="/sneat/assets/vendor/js/helpers.js"></script>
    <script src="/sneat/assets/js/config.js"></script>

    @stack('styles')
</head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="{{ route('dashboard') }}" class="app-brand-link d-flex justify-content-center align-items-center">
              <span class="app-brand-logo demo">
                <img src="{{ asset('img/inferno-logo.png') }}" alt="Inferno Club" style="width: 100px; height: auto;">
              </span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left align-middle"></i>
            </a>
          </div>

          <div class="menu-divider mt-0"></div>
          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            <!-- Dashboard -->
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
              </a>
            </li>

            <!-- Productos -->
            @role('Administrador|Bodega')
            <li class="menu-item {{ request()->routeIs('productos.*') ? 'active' : '' }}">
              <a href="{{ route('productos.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-box"></i>
                <div class="text-truncate" data-i18n="Productos">Productos</div>
              </a>
            </li>
            @endrole

            <!-- Categorías -->
            @role('Administrador|Bodega')
            <li class="menu-item {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
              <a href="{{ route('categorias.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-purchase-tag"></i>
                <div class="text-truncate" data-i18n="Categorías">Categorías</div>
              </a>
            </li>
            @endrole


            <!-- Caja (POS) -->
            @role('Administrador|Ventas')
            <li class="menu-item {{ request()->is('caja*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div class="text-truncate" data-i18n="Caja">Caja (POS)</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('caja.pos') ? 'active' : '' }}">
                  <a href="{{ route('caja.pos') }}" class="menu-link">
                    <div class="text-truncate" data-i18n="Punto de Venta">Punto de Venta</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('caja.index') || request()->routeIs('caja.show') ? 'active' : '' }}">
                  <a href="{{ route('caja.index') }}" class="menu-link">
                    <div class="text-truncate" data-i18n="Ventas">Ventas</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->is('caja/turnos*') ? 'active' : '' }}">
                  <a href="{{ route('caja.turnos.index') }}" class="menu-link">
                    <div class="text-truncate" data-i18n="Turnos">Turnos de Caja</div>
                  </a>
                </li>
              </ul>
            </li>
            @endrole

            <!-- Auditoría -->
            @role('Administrador')
            <li class="menu-item {{ request()->routeIs('auditorias.*') ? 'active' : '' }}">
              <a href="{{ route('auditorias.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-history"></i>
                <div class="text-truncate" data-i18n="Auditoría">Auditoría</div>
              </a>
            </li>
            @endrole

            <!-- Usuarios -->
            @role('Administrador|Secretario')
            <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
              <a href="{{ route('users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div class="text-truncate" data-i18n="Usuarios">Usuarios</div>
              </a>
            </li>
            @endrole

            <!-- Roles -->
            @role('Administrador')
            <li class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
              <a href="{{ route('roles.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield"></i>
                <div class="text-truncate" data-i18n="Roles">Roles</div>
              </a>
            </li>
            @endrole
          </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
        <!-- Navbar -->
          <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="icon-base bx bx-menu icon-md"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
              <!-- Sistema Info -->
              <div class="navbar-nav align-items-center me-auto">
                <div class="nav-item d-flex align-items-center">
                  <span class="text-muted d-none d-md-block">
                    <i class="bx bx-calendar me-1"></i>
                    <span id="current-date"></span>
                  </span>
                </div>
              </div>
              <!-- /Sistema Info -->

              <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <div class="avatar-initial rounded-circle bg-primary">
                        @if(Auth::check())
                          <span class="text-white fw-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        @else
                          <span class="text-white fw-bold">?</span>
                        @endif
                      </div>
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <div class="avatar-initial rounded-circle bg-primary">
                                @if(Auth::check())
                                  <span class="text-white fw-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                @else
                                  <span class="text-white fw-bold">?</span>
                                @endif
                              </div>
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            @if(Auth::check())
                              <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                              <small class="text-body-secondary">{{ Auth::user()->roles->first()->name ?? 'Usuario' }}</small>
                            @else
                              <h6 class="mb-0">Invitado</h6>
                              <small class="text-body-secondary">Sin sesión</small>
                            @endif
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    @if(Auth::check())
                    <li>
                      <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="icon-base bx bx-user icon-md me-3"></i><span>Mi Perfil</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                      <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <a class="dropdown-item" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                          <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Cerrar Sesión</span>
                        </a>
                      </form>
                    </li>
                    @endif
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                  {{ session('success') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                  {{ session('error') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('warning'))
                <div class="alert alert-warning alert-dismissible" role="alert">
                  {{ session('warning') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('info'))
                <div class="alert alert-info alert-dismissible" role="alert">
                  {{ session('info') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            <!-- / Content -->
        
        <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                  <div class="mb-2 mb-md-0">
                    © <script>document.write(new Date().getFullYear());</script>
                    Sistema de Inventario - Desarrollado por OppaDev
                  </div>
                  <div class="d-none d-lg-inline-block">
                    <span class="text-muted">Versión 1.0</span>
                  </div>
                </div>
            </div>
        </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
    </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="/sneat/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/sneat/assets/vendor/libs/popper/popper.js"></script>
    <script src="/sneat/assets/vendor/js/bootstrap.js"></script>
    <script src="/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/sneat/assets/vendor/js/menu.js"></script>
    <script src="/sneat/assets/js/main.js"></script>

    <!-- Select2 CSS y JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Vendors JS -->
    <script src="/sneat/assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Application Scripts -->
    @vite(['resources/js/app.js'])

    <!-- Fecha actual -->
    <script>
      function updateDate() {
        const dateElement = document.getElementById('current-date');
        if (dateElement) {
          const now = new Date();
          const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          };
          dateElement.textContent = now.toLocaleDateString('es-ES', options);
        }
      }
      
      // Actualizar fecha al cargar
      document.addEventListener('DOMContentLoaded', updateDate);
      
      // Actualizar fecha cada minuto
      setInterval(updateDate, 60000);
    </script>

    @stack('scripts')
</body>
</html>
