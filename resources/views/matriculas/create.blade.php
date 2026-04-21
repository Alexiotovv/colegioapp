{{-- resources/views/matriculas/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Matrícula')

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
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
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
    .aula-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        margin-top: 5px;
        font-size: 13px;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-address-card me-2" style="color: var(--primary-color);"></i>
            Nueva Matrícula
        </h4>
        <a href="{{ route('admin.matriculas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    
    <div class="form-container">
        <form id="matriculaForm">
            @csrf
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="alumno_id" class="form-label required-field">Alumno</label>
                    <div class="input-group">
                        <select class="form-select select2 @error('alumno_id') is-invalid @enderror" 
                                id="alumno_id" name="alumno_id" required>
                            <option value="">Seleccionar alumno</option>
                            @foreach($alumnos as $alumno)
                                <option value="{{ $alumno->id }}" data-dni="{{ $alumno->dni }}" data-nombre="{{ $alumno->nombre_completo }}">
                                    {{ $alumno->codigo_estudiante }} - {{ $alumno->nombre_completo }} ({{ $alumno->dni }})
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#nuevoAlumnoModal">
                            <i class="fas fa-plus"></i> Nuevo Alumno
                        </button>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        ¿No encuentras al alumno? Haz clic en "Nuevo Alumno" para registrarlo rápidamente
                    </small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="aula_id" class="form-label required-field">Aula (Grado - Sección - Turno)</label>
                    <select class="form-select select2 @error('aula_id') is-invalid @enderror" id="aula_id" name="aula_id" required>
                        <option value="">Seleccionar aula</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}" 
                                    data-nivel="{{ $aula->grado->nivel->nombre ?? '' }}"
                                    data-grado="{{ $aula->grado->nombre ?? '' }}"
                                    data-seccion="{{ $aula->seccion->nombre ?? '' }}"
                                    data-turno="{{ $aula->turno_nombre }}"
                                    data-anio="{{ $aula->anioAcademico->anio ?? '' }}"
                                    data-docente="{{ $aula->docente ? $aula->docente->nombre_completo : 'No asignado' }}">
                                {{ $aula->grado->nivel->nombre ?? '' }} - {{ $aula->grado->nombre ?? '' }} - 
                                Sección "{{ $aula->seccion->nombre ?? '' }}" ({{ $aula->turno_nombre }}) - 
                                {{ $aula->anioAcademico->anio ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="aula-info" id="aulaInfo" style="display: none;">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="aulaInfoText"></span>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_matricula" class="form-label required-field">Fecha de Matrícula</label>
                    <input type="date" class="form-control @error('fecha_matricula') is-invalid @enderror" 
                           id="fecha_matricula" name="fecha_matricula" value="{{ date('Y-m-d') }}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="apoderado_id" class="form-label">Apoderado Principal</label>
                    <select class="form-select select2" id="apoderado_id" name="apoderado_id">
                        <option value="">Seleccionar apoderado (opcional)</option>
                        @foreach($apoderados as $apoderado)
                            <option value="{{ $apoderado->id }}">
                                {{ $apoderado->nombre_completo }} - {{ $apoderado->dni }} ({{ $apoderado->telefono ?? 'Sin teléfono' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Información adicional sobre la matrícula..."></textarea>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Información:</strong> La matrícula quedará registrada como "Activa" y podrá ser modificada posteriormente.
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Registrar Matrícula
                    </button>
                    <a href="{{ route('admin.matriculas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Nuevo Alumno -->
<div class="modal fade" id="nuevoAlumnoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Registrar Nuevo Alumno
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="nuevoAlumnoForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_dni" class="form-label required-field">DNI</label>
                            <input type="text" class="form-control" id="modal_dni" name="dni" maxlength="8" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_sexo" class="form-label required-field">Sexo</label>
                            <select class="form-select" id="modal_sexo" name="sexo" required>
                                <option value="">Seleccionar</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="modal_apellido_paterno" class="form-label required-field">Apellido Paterno</label>
                            <input type="text" class="form-control" id="modal_apellido_paterno" name="apellido_paterno" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modal_apellido_materno" class="form-label required-field">Apellido Materno</label>
                            <input type="text" class="form-control" id="modal_apellido_materno" name="apellido_materno" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modal_nombres" class="form-label required-field">Nombres</label>
                            <input type="text" class="form-control" id="modal_nombres" name="nombres" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_fecha_nacimiento" class="form-label required-field">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="modal_fecha_nacimiento" name="fecha_nacimiento" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="modal_telefono" name="telefono">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="modal_direccion" name="direccion">
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="modal_email" name="email">
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Nota:</strong> Después de registrar el alumno, podrás asignar apoderados desde la edición del alumno.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="guardarAlumnoBtn">
                    <i class="fas fa-save me-2"></i>Guardar Alumno
                </button>
            </div>
        </div>
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
        placeholder: 'Buscar...',
        allowClear: true
    });
    
    // Mostrar información del aula seleccionada
    $('#aula_id').on('change', function() {
        let selectedOption = $(this).find('option:selected');
        let aulaInfo = $('#aulaInfo');
        let aulaInfoText = $('#aulaInfoText');
        
        if ($(this).val()) {
            let nivel = selectedOption.data('nivel');
            let grado = selectedOption.data('grado');
            let seccion = selectedOption.data('seccion');
            let turno = selectedOption.data('turno');
            let anio = selectedOption.data('anio');
            let docente = selectedOption.data('docente');
            
            aulaInfoText.html(`
                <strong>Información del Aula:</strong> ${nivel} ${grado} - Sección "${seccion}" - Turno: ${turno} - Año: ${anio} - Docente: ${docente}
            `);
            aulaInfo.show();
        } else {
            aulaInfo.hide();
        }
    });
    
    // Guardar nuevo alumno vía AJAX
    $('#guardarAlumnoBtn').on('click', function() {
        let btn = $(this);
        let formData = $('#nuevoAlumnoForm').serialize();
        
        btn.prop('disabled', true);
        btn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: '{{ route("api.alumnos.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    let newOption = new Option(
                        `${response.alumno.codigo_estudiante} - ${response.alumno.nombre_completo} (${response.alumno.dni})`,
                        response.alumno.id,
                        true,
                        true
                    );
                    $('#alumno_id').append(newOption).trigger('change');
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    $('#nuevoAlumnoForm')[0].reset();
                    $('#nuevoAlumnoModal').modal('hide');
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                let errorMessage = 'Error al registrar el alumno';
                
                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html('<i class="fas fa-save me-2"></i>Guardar Alumno');
            }
        });
    });
    
    // Enviar formulario de matrícula
    $('#matriculaForm').on('submit', function(e) {
        e.preventDefault();
        
        let submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner me-2"></span> Registrando...');
        
        $.ajax({
            url: '{{ route("admin.matriculas.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Matrícula registrada!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("admin.matriculas.index") }}';
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.message || 'Error al registrar la matrícula';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save me-2"></i>Registrar Matrícula');
            }
        });
    });
    
    // Validar DNI en el modal
    $('#modal_dni').on('keyup', function() {
        let dni = $(this).val();
        if (dni.length > 0 && !/^\d+$/.test(dni)) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">El DNI solo debe contener números</div>');
        } else if (dni.length > 0 && dni.length !== 8) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">El DNI debe tener 8 dígitos</div>');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endsection