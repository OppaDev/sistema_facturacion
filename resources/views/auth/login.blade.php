@extends('layouts.guest')

@section('title', 'Iniciar Sesión - Inferno Club')

@section('content')
<style>
  body {
    background: linear-gradient(135deg, #000000 0%, #1a0000 100%);
    min-height: 100vh;
  }
  .inferno-card {
    background: linear-gradient(135deg, #1a0000 0%, #000000 100%);
    border: 2px solid #ff0000 !important;
    box-shadow: 0 10px 40px rgba(255, 0, 0, 0.3) !important;
  }
  .inferno-title {
    color: #ff0000;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 3px;
    text-shadow: 0 0 20px rgba(255, 0, 0, 0.5);
  }
  .inferno-subtitle {
    color: #cccccc;
    font-weight: 300;
  }
  .form-label {
    color: #ff0000;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
  }
  .form-control, .input-group-text {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 0, 0, 0.3) !important;
    color: #ffffff !important;
  }
  .form-control::placeholder {
    color: #666666 !important;
  }
  .form-control:focus {
    background: rgba(255, 255, 255, 0.08) !important;
    border-color: #ff0000 !important;
    box-shadow: 0 0 15px rgba(255, 0, 0, 0.3) !important;
    color: #ffffff !important;
  }
  .input-group-text {
    color: #ff0000 !important;
  }
  .btn-inferno {
    background: #ff0000 !important;
    border: 2px solid #ff0000 !important;
    color: #ffffff !important;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    padding: 12px;
    transition: all 0.3s ease;
  }
  .btn-inferno:hover {
    background: transparent !important;
    color: #ff0000 !important;
    box-shadow: 0 8px 30px rgba(255, 0, 0, 0.6) !important;
    transform: translateY(-2px);
  }
  .form-check-input:checked {
    background-color: #ff0000 !important;
    border-color: #ff0000 !important;
  }
  .form-check-label, .text-muted {
    color: #cccccc !important;
  }
  .text-link {
    color: #ff0000 !important;
    font-weight: 600;
    transition: all 0.3s ease;
  }
  .text-link:hover {
    color: #ff3333 !important;
    text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
  }
  .divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #ff0000, transparent);
  }
  .alert-danger {
    background: rgba(255, 0, 0, 0.1) !important;
    border: 2px solid #ff0000 !important;
    color: #ff0000 !important;
  }
  .inferno-logo {
    width: 120px;
    height: 120px;
    margin: 0 auto 20px;
    filter: drop-shadow(0 0 20px rgba(255, 0, 0, 0.8));
  }
</style>

<div class="container-xxl min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row w-100 justify-content-center">
    <div class="col-md-6 col-lg-5 col-12">
      <div class="card inferno-card shadow-lg border-0 rounded-4 animate__animated animate__fadeInDown">
        <div class="card-body p-5">
          <!-- Logo grande -->
          <div class="text-center mb-4">
            <img src="{{ asset('img/inferno-logo.png') }}" alt="Inferno Club" class="inferno-logo" onerror="this.style.display='none'">
          </div>
          <h2 class="fw-bold text-center mb-2 inferno-title">INFERNO CLUB</h2>
          <p class="text-center inferno-subtitle mb-4">Sistema de Gestión Empresarial</p>

          @if($errors->has('danger'))
            <div class="alert alert-danger text-center fw-bold">
              {{ $errors->first('danger') }}
            </div>
          @endif

          <form id="formAuthentication" action="{{ route('login') }}" method="POST" autocomplete="off">
            @csrf
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bx bx-envelope"></i></span>
                <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Ingresa tu email" autofocus value="{{ old('email') }}" />
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bx bx-lock"></i></span>
                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Contraseña" />
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="mb-3 d-flex justify-content-between align-items-center">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                <label class="form-check-label" for="remember"> Recordarme </label>
              </div>
              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small text-link">¿Olvidaste tu contraseña?</a>
              @endif
            </div>
            <div class="mb-3">
              <button class="btn btn-inferno btn-lg w-100 fw-bold" type="submit">
                <i class='bx bx-log-in me-2'></i> Acceder al Sistema
              </button>
            </div>
          </form>

          <div class="text-center mt-3 mb-2">
            <span style="color: #cccccc;">¿Nuevo en la plataforma?</span>
            <a href="{{ route('register') }}" class="text-link ms-1">Crea una cuenta</a>
          </div>

          <div class="divider my-4"></div>

          @if(session('suspendida') || session('inactiva'))
            <div id="suspend-modal" class="modal fade show" tabindex="-1" style="display:block; background:rgba(0,0,0,0.7);" aria-modal="true" role="dialog">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-4 animate__animated animate__fadeInDown">
                  <div class="modal-body text-center p-5">
                    <div class="mb-3">
                      <i class="bx bx-block fs-1 text-danger"></i>
                    </div>
                    <h4 class="fw-bold mb-2 text-danger">Cuenta suspendida</h4>
                    <p class="mb-2">Tu cuenta ha sido suspendida por el administrador.</p>
                    <p class="mb-2"><strong>Motivo:</strong> {{ session('motivo') }}</p>
                    <p class="mb-3">Si crees que fue un error, contacta a soporte:<br>
                      <a href="mailto:darwinrvaldiviezo@gmail.com" class="fw-bold text-primary">darwinrvaldiviezo@gmail.com</a>
                    </p>
                    <div class="mb-3">
                      <span class="badge bg-danger fs-5">Cerrando sesión en <span id="countdown">5</span>...</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <script>
              let seconds = 5;
              const countdown = document.getElementById('countdown');
              const interval = setInterval(() => {
                seconds--;
                if(countdown) countdown.textContent = seconds;
                if(seconds <= 0) {
                  clearInterval(interval);
                  window.location.href = "{{ route('login') }}";
                }
              }, 1000);
              // Bloquear scroll y teclado
              document.body.style.overflow = 'hidden';
              document.addEventListener('keydown', function(e){ e.preventDefault(); });
            </script>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
