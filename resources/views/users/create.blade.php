{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('css')
<style>
    .form-container {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .required-field::after {
        content: '*';
        color: red;
        margin-left: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-plus me-2"></i>
            Crear Nuevo Usuario
        </h4>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.users.store') }}" id="userForm">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label required-field">Nombre Completo</label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label required-field">Nombre de Usuario</label>
                    <input type="text" 
                           class="form-control @error('username') is-invalid @enderror" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
                           required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label required-field">Correo Electrónico</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="role_id" class="form-label required-field">Rol</label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                        <option value="">Seleccionar rol</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ ucfirst($role->nombre) }} - {{ $role->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-2">
                    <button type="button" class="btn btn-sm btn-secondary" id="btnGenerarContrasena">
                        <i class="fas fa-key me-1"></i> Generar Contraseña Segura
                    </button>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label required-field">Contraseña</label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Mínimo 6 caracteres</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label required-field">Confirmar Contraseña</label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control @error('password_confirmation') is-invalid @enderror" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">
                            Usuario Activo
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i> Guardar Usuario
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Generar email y username automáticamente desde el nombre
        $('#name').on('input', function() {
            const nombre = $(this).val().trim();
            if (nombre) {
                const partes = nombre
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .split(/\s+/);

                if (partes.length >= 2) {
                    const primerNombre = partes[0];
                    const segundoApellido = partes.length === 2 ? partes[partes.length - 1] : partes[partes.length - 2];
                    const username = primerNombre + '.' + segundoApellido + '@colcoopcv.com';
                    $('#username').val(username);
                }

                const email = partes.join('') + '.0@colcoopcv.com';
                $('#email').val(email);
            }
        });

        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
        
        $('#toggleConfirmPassword').on('click', function() {
            const passwordField = $('#password_confirmation');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });
        
        // Mostrar spinner al enviar
        $('#userForm').on('submit', function() {
            $('#submitBtn').prop('disabled', true);
            $('#submitBtn').html('<i class="fas fa-spinner fa-spin me-2"></i> Guardando...');
        });

        // Generar contraseña aleatoria
$('#btnGenerarContrasena').on('click', function() {
    const length = 10;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
    let password = "";
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    $('#password').val(password);
    $('#password_confirmation').val(password);
    
    // Mostrar opción para copiar
    Swal.fire({
        icon: 'success',
        title: 'Contraseña generada',
        html: `
            <div class="input-group mt-3">
                <input type="text" class="form-control" id="contrasenaGenerada" value="${password}" readonly>
                <button class="btn btn-primary" id="btnCopiarContrasena" onclick="copiarContrasena()">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar',
        didOpen: () => {
            // Función para copiar
            window.copiarContrasena = function() {
                const input = document.getElementById('contrasenaGenerada');
                input.select();
                document.execCommand('copy');
                Swal.fire({
                    icon: 'success',
                    title: '¡Copiado!',
                    text: 'Contraseña copiada al portapapeles',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        }
    });
});

    });
</script>
@endsection