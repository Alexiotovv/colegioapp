{{-- resources/views/carga-horaria/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Carga Horaria')

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
    .info-card {
        background: #e8f0fe;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
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
            <i class="fas fa-plus-circle me-2" style="color: var(--primary-color);"></i>
            Nueva Carga Horaria
        </h4>
        <a href="{{ route('admin.carga-horaria.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form id="cargaHorariaForm">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="docente_id" class="form-label required-field">Docente</label>
                    <select class="form-select select2" id="docente_id" name="docente_id" required>
                        <option value="">Seleccionar docente</option>
                        @foreach($docentes as $docente)
                            <option value="{{ $docente->id }}">
                                {{ $docente->name }} - {{ $docente->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                

            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="curso_id" class="form-label required-field">Curso</label>
                    <select class="form-select select2" id="curso_id" name="curso_id" required>
                        <option value="">Seleccionar curso</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}">
                                {{ $curso->nivel->nombre ?? '' }}: {{ $curso->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="aula_id" class="form-label required-field">Aula</label>
                    <select class="form-select" id="aula_id" name="aula_id" required>
                        <option value="">Primero seleccione un curso</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="horas_semanales" class="form-label required-field">Horas Semanales</label>
                    <input type="number" class="form-control" id="horas_semanales" name="horas_semanales" min="1" max="40" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="dia_semana" class="form-label">Día de la Semana</label>
                    <select class="form-select" id="dia_semana" name="dia_semana">
                        <option value="">Horario flexible (sin día específico)</option>
                        @foreach($diasSemana as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="row" id="horarioFields" style="display: none;">
                <div class="col-md-6 mb-3">
                    <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" step="300">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="hora_fin" class="form-label">Hora de Fin</label>
                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" step="300">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                          placeholder="Información adicional sobre la asignación..."></textarea>
            </div>
            
            <div class="info-card">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Información:</strong> La carga horaria asigna un curso a un docente en un aula específica.
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i> Guardar Asignación
                    </button>
                    <a href="{{ route('admin.carga-horaria.index') }}" class="btn btn-secondary">
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
        placeholder: 'Buscar...',
        allowClear: true
    });
    
    // Mostrar/ocultar campos de horario según selección de día
    $('#dia_semana').on('change', function() {
        if ($(this).val()) {
            $('#horarioFields').show();
        } else {
            $('#horarioFields').hide();
            $('#hora_inicio').val('');
            $('#hora_fin').val('');
        }
    });
    
    // Cargar aulas según el curso seleccionado
    $('#curso_id').on('change', function() {
        let cursoId = $(this).val();
        let aulaSelect = $('#aula_id');
        
        if (cursoId) {
            aulaSelect.html('<option value="">Cargando...</option>');
            
            $.ajax({
                url: '{{ route("admin.carga-horaria.aulas-by-curso") }}',
                method: 'GET',
                data: { curso_id: cursoId },
                success: function(response) {
                    aulaSelect.html('<option value="">Seleccionar aula</option>');
                    if (response && response.length > 0) {
                        for (let aula of response) {
                            aulaSelect.append(`<option value="${aula.id}">${aula.nombre} - ${aula.grado ? aula.grado.nombre : ''} "${aula.seccion ? aula.seccion.nombre : ''}" (${aula.turno_nombre || aula.turno})</option>`);
                        }
                    } else {
                        aulaSelect.append('<option value="">No hay aulas disponibles</option>');
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    aulaSelect.html('<option value="">Error al cargar aulas</option>');
                }
            });
        } else {
            aulaSelect.html('<option value="">Primero seleccione un curso</option>');
        }
    });
    
    // Enviar formulario
    $('#cargaHorariaForm').on('submit', function(e) {
        e.preventDefault();
        
        let submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: '{{ route("admin.carga-horaria.store") }}',
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
                        window.location.href = '{{ route("admin.carga-horaria.index") }}';
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.message || 'Error al guardar la asignación';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save me-2"></i> Guardar Asignación');
            }
        });
    });
});
</script>
@endsection