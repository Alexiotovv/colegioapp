{{-- resources/views/carga-horaria/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Carga Horaria')

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
        min-height: 42px;
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
            <i class="fas fa-edit me-2" style="color: var(--primary-color);"></i>
            Editar Asignación de Curso
        </h4>
        <a href="{{ route('admin.carga-horaria.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form id="cargaHorariaForm">
            @csrf
            @method('PUT')
            
            <!-- Datos principales -->
            <div class="row g-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="docente_id" class="form-label required-field fw-semibold">Docente</label>
                                    <select class="form-select select2" id="docente_id" name="docente_id" required>
                                        <option value="">-- Seleccionar docente --</option>
                                        @foreach($docentes as $docente)
                                            <option value="{{ $docente->id }}" {{ $cargaHorarium->docente_id == $docente->id ? 'selected' : '' }}>
                                                {{ $docente->name }} - {{ $docente->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="curso_id" class="form-label required-field fw-semibold">Curso</label>
                                    <select class="form-select select2" id="curso_id" name="curso_id" required>
                                        <option value="">-- Seleccionar curso --</option>
                                        @foreach($cursos as $curso)
                                            <option value="{{ $curso->id }}" {{ $cargaHorarium->curso_id == $curso->id ? 'selected' : '' }}>
                                                {{ $curso->nivel->nombre ?? '' }} - {{ $curso->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="aula_id" class="form-label required-field fw-semibold">Aula</label>
                                    <select class="form-select" id="aula_id" name="aula_id" required>
                                        <option value="">-- Seleccionar aula --</option>
                                        @foreach($aulas as $aula)
                                            <option value="{{ $aula->id }}" {{ $cargaHorarium->aula_id == $aula->id ? 'selected' : '' }}>
                                                {{ $aula->nombre }} - {{ $aula->grado->nombre ?? '' }} "{{ $aula->seccion->nombre ?? '' }}" ({{ $aula->turno_nombre ?? $aula->turno }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Datos adicionales (opcionales) -->
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-bold">
                            <i class="fas fa-clock me-2 text-muted"></i> Datos Adicionales <span class="text-muted fw-normal">(Opcional)</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="horas_semanales" class="form-label">Horas Semanales</label>
                                    <input type="number" class="form-control" id="horas_semanales" name="horas_semanales" 
                                           min="1" max="40" value="{{ $cargaHorarium->horas_semanales }}">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="dia_semana" class="form-label">Día (opcional)</label>
                                    <select class="form-select" id="dia_semana" name="dia_semana">
                                        <option value="">-- Sin día específico --</option>
                                        @foreach($diasSemana as $key => $label)
                                            <option value="{{ $key }}" {{ $cargaHorarium->dia_semana == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="hora_inicio" class="form-label">Hora inicio</label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" 
                                           value="{{ $cargaHorarium->hora_inicio ? \Carbon\Carbon::parse($cargaHorarium->hora_inicio)->format('H:i') : '' }}" step="300">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="hora_fin" class="form-label">Hora fin</label>
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" 
                                           value="{{ $cargaHorarium->hora_fin ? \Carbon\Carbon::parse($cargaHorarium->hora_fin)->format('H:i') : '' }}" step="300">
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="2" 
                                          placeholder="Información adicional...">{{ $cargaHorarium->observaciones }}</textarea>
                            </div>
                            
                            <div class="mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="estado" id="estado" value="activo" 
                                           {{ $cargaHorarium->estado == 'activo' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="estado">
                                        Asignación activa
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.carga-horaria.index') }}" class="btn btn-secondary px-4">
                    <i class="fas fa-times me-2"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                    <i class="fas fa-save me-2"></i> Actualizar Asignación
                </button>
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
    
    // Enviar formulario
    $('#cargaHorariaForm').on('submit', function(e) {
        e.preventDefault();
        
        let submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner me-2"></span> Actualizando...');
        
        $.ajax({
            url: '{{ route("admin.carga-horaria.update", $cargaHorarium) }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("admin.carga-horaria.index") }}';
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.message || 'Error al actualizar la asignación';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save me-2"></i> Actualizar Asignación');
            }
        });
    });
});
</script>
@endsection