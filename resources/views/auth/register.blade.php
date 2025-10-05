@extends('layouts.guest')

@section('title', 'Registro - Inferno Club')

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
  .text-link {
    color: #ff0000 !important;
    font-weight: 600;
    transition: all 0.3s ease;
  }
  .text-link:hover {
    color: #ff3333 !important;
    text-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
  }
  .alert-danger {
    background: rgba(255, 0, 0, 0.1) !important;
    border: 2px solid #ff0000 !important;
    color: #ff0000 !important;
  }
  .inferno-logo {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    filter: drop-shadow(0 0 20px rgba(255, 0, 0, 0.8));
  }
</style>

<div class="container-xxl min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row w-100 justify-content-center">
    <div class="col-md-7 col-lg-6 col-12">
      <div class="card inferno-card shadow-lg border-0 rounded-4 animate__animated animate__fadeInDown">
        <div class="card-body p-5">
          <!-- Logo grande -->
          <div class="text-center mb-4">
            <img src="{{ asset('img/inferno-logo.png') }}" alt="Inferno Club" class="inferno-logo" onerror="this.style.display='none'">
          </div>
          <h2 class="fw-bold text-center mb-2 inferno-title">INFERNO CLUB</h2>
          <p class="text-center inferno-subtitle mb-4">Crear Nueva Cuenta</p>

          @if($errors->has('danger'))
            <div class="alert alert-danger text-center fw-bold">
              {{ $errors->first('danger') }}
            </div>
          @endif

          <form method="POST" action="{{ route('register') }}" id="registerForm" autocomplete="off">
            @csrf
            <div class="mb-3">
              <label for="name" class="form-label">Nombre completo</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bx bx-user"></i></span>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Tu nombre" required autofocus />
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Correo electrónico</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bx bx-envelope"></i></span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required />
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bx bx-lock"></i></span>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Contraseña" required />
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="mb-3">
              <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bx bx-lock"></i></span>
                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="Repite tu contraseña" required />
                @error('password_confirmation')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="mb-3">
              <button type="submit" class="btn btn-inferno btn-lg w-100 fw-bold">
                <i class='bx bx-user-plus me-2'></i> Crear Cuenta
              </button>
            </div>
          </form>

          <div class="text-center mt-3">
            <span style="color: #cccccc;">¿Ya tienes una cuenta?</span>
            <a href="{{ route('login') }}" class="text-link ms-1">Inicia sesión aquí</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
