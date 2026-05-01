{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - ColcoopCV Sistema Escolar</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        
        .login-container {
            display: flex;
            height: 100vh;
            width: 100%;
        }
        
        /* Lado izquierdo - Formulario (30%) */
        .login-form {
            width: 30%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            z-index: 2;
            position: relative;
        }
        
        .form-wrapper {
            width: 100%;
            max-width: 350px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .logo-icon i {
            font-size: 40px;
            color: white;
        }
        
        .logo h2 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border-right: none;
        }
        
        .form-control {
            border-left: none;
            padding: 12px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        /* Lado derecho - Imagen (70%) */
        .login-image {
            width: 70%;
            position: relative;
            overflow: hidden;
        }
        
        .login-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.3));
        }
        
        .image-text {
            position: absolute;
            bottom: 50px;
            left: 50px;
            color: white;
            z-index: 2;
        }
        
        .image-text h3 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .image-text p {
            font-size: 16px;
            opacity: 0.9;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        /* Alertas */
        .alert-custom {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 12px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-form {
                width: 100%;
                position: absolute;
                z-index: 10;
                background: rgba(255,255,255,0.95);
                backdrop-filter: blur(10px);
            }
            
            .login-image {
                width: 100%;
            }
            
            .image-text {
                left: 20px;
                bottom: 20px;
            }
            
            .image-text h3 {
                font-size: 20px;
            }
        }
        
        /* Animations */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .form-wrapper {
            animation: slideInLeft 0.6s ease;
        }
        
        /* Loading spinner */
        .spinner-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    @php
        $logoLoginUrl = null;
        $dashboardImageUrl = null;
        if (isset($configInstitucion)) {
            if (!empty($configInstitucion->logo_login)) {
                $logoLoginUrl = asset('storage/' . $configInstitucion->logo_login);
            }
            if (!empty($configInstitucion->logo_dashboard)) {
                $dashboardImageUrl = asset('storage/' . $configInstitucion->logo_dashboard);
            }
        }
        $defaultDashboardUrl = 'https://images.pexels.com/photos/5212345/pexels-photo-5212345.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2';
    @endphp
    <div class="login-container">
        <!-- Lado izquierdo - Formulario (30%) -->
        <div class="login-form">
            <div class="form-wrapper">
                <div class="logo">
                    @if($logoLoginUrl)
                        <div class="logo-icon" style="background: transparent; box-shadow: none;">
                            <img src="{{ $logoLoginUrl }}" alt="Logo de la institución" style="max-width: 100%; max-height: 80px; display: block; margin: 0 auto;">
                        </div>
                    @else
                        <div class="logo-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    @endif
                    <h2>{{ $configInstitucion->nombre ?? 'ColcoopCV' }}</h2>
                    <p>{{ $configInstitucion->descripcion ?? 'Sistema de Gestión Escolar' }}</p>
                </div>
                
                @if(session('error'))
                    <div class="alert alert-danger alert-custom">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-warning alert-custom">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user me-1"></i> Usuario o Email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username') }}" 
                                   placeholder="Ingresa tu usuario o email"
                                   required 
                                   autofocus>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock me-1"></i> Contraseña
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Ingresa tu contraseña"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Recordarme
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login text-white" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                    </button>
                    
                    <div class="forgot-password">
                        <a href="#">
                            <i class="fas fa-question-circle me-1"></i> ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i> Sistema Seguro
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Lado derecho - Imagen (70%) -->
        <div class="login-image">
            <img src="{{ $dashboardImageUrl ?? $defaultDashboardUrl }}" 
                 alt="Colegio Imagen">
            <div class="image-overlay"></div>
            <div class="image-text">
                <h3>Bienvenido al Sistema</h3>
                <p>Gestión de notas, matrículas y pagos en un solo lugar</p>
                <div class="mt-3">
                    <span class="badge bg-light text-dark me-2">
                        <i class="fas fa-chart-line"></i> Notas en línea
                    </span>
                        {{-- <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-dollar-sign"></i> Pagos digitales
                        </span> --}}
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-users"></i> Comunicación directa
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Spinner de carga -->
    <div class="spinner-overlay" id="spinnerOverlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });
            
            // Mostrar spinner al enviar formulario
            $('#loginForm').on('submit', function() {
                $('#spinnerOverlay').fadeIn();
                $('#loginBtn').prop('disabled', true);
                $('#loginBtn').html('<i class="fas fa-spinner fa-spin me-2"></i> Validando...');
            });
            
            // Efecto de entrada
            $('.form-wrapper').hide().fadeIn(800);
            
            // Auto-focus en el primer campo
            $('#username').focus();
        });
    </script>
</body>
</html>