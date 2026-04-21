{{-- resources/views/apoderados/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Apoderado')

@section('css')
<style>
    .form-container {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .required-field::after {
        content: '*';
        color: var(--danger-color);
        margin-left: 4px;
    }
    select[multiple] {
        min-height: 150px;
    }
    .is-invalid {
        border-color: #dc3545;
    }
    .invalid-feedback {
        display: block;
    }
    .valid-feedback {
        display: block;
        color: #28a745;
    }
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-plus me-2" style="color: var(--primary-color);"></i>
            Nuevo Apoderado
        </h4>
        <a href="{{ route('admin.apoderados.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form id="apoderadoForm" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="dni" class="form-label required-field">DNI</label>
                    <input type="text" class="form-control" 
                           id="dni" name="dni" maxlength="8" required>
                    <div class="invalid-feedback" id="dni_error"></div>
                    <div class="valid-feedback">✓ DNI válido</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="sexo" class="form-label required-field">Sexo</label>
                    <select class="form-select" id="sexo" name="sexo" required>
                        <option value="">Seleccionar</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                    <div class="invalid-feedback" id="sexo_error"></div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="parentesco" class="form-label required-field">Parentesco</label>
                    <select class="form-select" id="parentesco" name="parentesco" required>
                        <option value="">Seleccionar parentesco</option>
                        <option value="PADRE">Padre</option>
                        <option value="MADRE">Madre</option>
                        <option value="TUTOR">Tutor Legal</option>
                        <option value="HERMANO">Hermano/a</option>
                        <option value="ABUELO">Abuelo/a</option>
                        <option value="TIO">Tío/a</option>
                        <option value="OTRO">Otro</option>
                    </select>
                    <div class="invalid-feedback" id="parentesco_error"></div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="apellido_paterno" class="form-label required-field">Apellido Paterno</label>
                    <input type="text" class="form-control" 
                           id="apellido_paterno" name="apellido_paterno" required>
                    <div class="invalid-feedback" id="apellido_paterno_error"></div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="apellido_materno" class="form-label required-field">Apellido Materno</label>
                    <input type="text" class="form-control" 
                           id="apellido_materno" name="apellido_materno" required>
                    <div class="invalid-feedback" id="apellido_materno_error"></div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="nombres" class="form-label required-field">Nombres</label>
                    <input type="text" class="form-control" 
                           id="nombres" name="nombres" required>
                    <div class="invalid-feedback" id="nombres_error"></div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" 
                           id="telefono" name="telefono">
                    <div class="invalid-feedback" id="telefono_error"></div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" 
                           id="email" name="email">
                    <div class="invalid-feedback" id="email_error"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" 
                       id="direccion" name="direccion">
                <div class="invalid-feedback" id="direccion_error"></div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="alumnos" class="form-label">Alumnos a cargo</label>
                    <select class="form-select select2" id="alumnos" name="alumnos[]" multiple>
                        @foreach($alumnos as $alumno)
                            <option value="{{ $alumno->id }}">
                                {{ $alumno->codigo_estudiante }} - {{ $alumno->nombre_completo }} ({{ $alumno->dni }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Mantén presionado Ctrl para seleccionar múltiples alumnos</small>
                    <div class="invalid-feedback" id="alumnos_error"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recibe_notificaciones" id="recibe_notificaciones" value="1" checked>
                    <label class="form-check-label" for="recibe_notificaciones">
                        <i class="fas fa-bell me-1"></i> Recibir notificaciones del sistema
                    </label>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Nota:</strong> Los apoderados pueden tener múltiples alumnos a cargo.
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i> Guardar Apoderado
                    </button>
                    <a href="{{ route('admin.apoderados.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Seleccionar alumnos...',
        allowClear: true
    });
    
    // Validar DNI en tiempo real
    $('#dni').on('keyup blur', function() {
        let dni = $(this).val();
        let errorDiv = $('#dni_error');
        
        if (dni.length === 0) {
            $(this).removeClass('is-invalid is-valid');
            errorDiv.text('');
            return;
        }
        
        if (!/^\d+$/.test(dni)) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            errorDiv.text('El DNI solo debe contener números');
        } else if (dni.length !== 8) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            errorDiv.text('El DNI debe tener 8 dígitos');
        } else {
            // Verificar si el DNI ya existe
            $.ajax({
                url: '{{ route("admin.apoderados.verificar-dni") }}',
                method: 'GET',
                data: { dni: dni },
                success: function(response) {
                    if (response.exists) {
                        $('#dni').addClass('is-invalid').removeClass('is-valid');
                        errorDiv.text('Este DNI ya está registrado');
                    } else {
                        $('#dni').removeClass('is-invalid').addClass('is-valid');
                        errorDiv.text('');
                    }
                }
            });
        }
    });
    
    // Validar email en tiempo real
    $('#email').on('blur', function() {
        let email = $(this).val();
        let errorDiv = $('#email_error');
        
        if (email.length > 0) {
            let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                errorDiv.text('Ingrese un email válido');
            } else {
                // Verificar si el email ya existe
                $.ajax({
                    url: '{{ route("admin.apoderados.verificar-email") }}',
                    method: 'GET',
                    data: { email: email },
                    success: function(response) {
                        if (response.exists) {
                            $('#email').addClass('is-invalid').removeClass('is-valid');
                            errorDiv.text('Este email ya está registrado');
                        } else {
                            $('#email').removeClass('is-invalid').addClass('is-valid');
                            errorDiv.text('');
                        }
                    }
                });
            }
        } else {
            $(this).removeClass('is-invalid is-valid');
            errorDiv.text('');
        }
    });
    
    // Validar teléfono
    $('#telefono').on('keyup blur', function() {
        let telefono = $(this).val();
        let errorDiv = $('#telefono_error');
        
        if (telefono.length > 0 && !/^\d+$/.test(telefono)) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            errorDiv.text('El teléfono solo debe contener números');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            errorDiv.text('');
        }
    });
    
    // Validar campos requeridos
    function validarCampoRequerido(campo, nombre) {
        let valor = $(campo).val().trim();
        let errorDiv = $(campo + '_error');
        
        if (valor === '') {
            $(campo).addClass('is-invalid').removeClass('is-valid');
            errorDiv.text(`El campo ${nombre} es requerido`);
            return false;
        } else {
            $(campo).removeClass('is-invalid').addClass('is-valid');
            errorDiv.text('');
            return true;
        }
    }
    
    // Validar select requerido
    function validarSelectRequerido(campo, nombre) {
        let valor = $(campo).val();
        let errorDiv = $(campo + '_error');
        
        if (!valor || valor === '') {
            $(campo).addClass('is-invalid').removeClass('is-valid');
            errorDiv.text(`El campo ${nombre} es requerido`);
            return false;
        } else {
            $(campo).removeClass('is-invalid').addClass('is-valid');
            errorDiv.text('');
            return true;
        }
    }
    
    // Validar formulario completo antes de enviar
    $('#apoderadoForm').on('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Validar campos requeridos
        if (!validarCampoRequerido('#apellido_paterno', 'Apellido Paterno')) isValid = false;
        if (!validarCampoRequerido('#apellido_materno', 'Apellido Materno')) isValid = false;
        if (!validarCampoRequerido('#nombres', 'Nombres')) isValid = false;
        if (!validarSelectRequerido('#sexo', 'Sexo')) isValid = false;
        if (!validarSelectRequerido('#parentesco', 'Parentesco')) isValid = false;
        
        // Validar DNI específicamente
        let dni = $('#dni').val();
        if (dni.length === 0) {
            $('#dni').addClass('is-invalid').removeClass('is-valid');
            $('#dni_error').text('El DNI es requerido');
            isValid = false;
        } else if ($('#dni').hasClass('is-invalid')) {
            isValid = false;
        }
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Por favor, corrija los campos marcados en rojo',
                timer: 3000,
                showConfirmButton: false
            });
            return;
        }
        
        // Enviar formulario por AJAX
        let submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: '{{ route("admin.apoderados.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("admin.apoderados.index") }}';
                    });
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                let errorMessage = 'Error al guardar el apoderado';
                
                if (errors) {
                    // Mostrar errores específicos en cada campo
                    for (let field in errors) {
                        let errorDiv = $('#' + field + '_error');
                        let input = $('#' + field);
                        if (errorDiv.length) {
                            errorDiv.text(errors[field][0]);
                            input.addClass('is-invalid');
                        }
                    }
                    errorMessage = Object.values(errors).flat()[0];
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save me-2"></i> Guardar Apoderado');
            }
        });
    });
});
</script>
@endsection