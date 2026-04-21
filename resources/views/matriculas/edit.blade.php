@extends('layouts.app')

@section('title', 'Editar Matrícula')

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
    .aula-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        margin-top: 5px;
        font-size: 13px;
    }
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
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
            Editar Matrícula
        </h4>
        <div>
            <a href="{{ route('admin.matriculas.show', $matricula) }}" class="btn btn-info">
                <i class="fas fa-eye me-2"></i> Ver Detalle
            </a>
            <a href="{{ route('admin.matriculas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="form-container">
        <form id="matriculaForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Alumno</label>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        <strong>{{ $matricula->alumno->nombre_completo ?? 'N/A' }}</strong><br>
                        <small>Código: {{ $matricula->alumno->codigo_estudiante ?? 'N/A' }} | DNI: {{ $matricula->alumno->dni ?? 'N/A' }}</small>
                    </div>
                    <input type="hidden" name="alumno_id" value="{{ $matricula->alumno_id }}">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="aula_id" class="form-label required-field">Aula (Grado - Sección - Turno)</label>
                    <select class="form-select @error('aula_id') is-invalid @enderror" id="aula_id" name="aula_id" required>
                        <option value="">Seleccionar aula</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}" 
                                    data-nivel="{{ $aula->grado->nivel->nombre ?? '' }}"
                                    data-grado="{{ $aula->grado->nombre ?? '' }}"
                                    data-seccion="{{ $aula->seccion->nombre ?? '' }}"
                                    data-turno="{{ $aula->turno_nombre }}"
                                    data-anio="{{ $aula->anioAcademico->anio ?? '' }}"
                                    data-docente="{{ $aula->docente ? $aula->docente->nombre_completo : 'No asignado' }}"
                                    {{ $matricula->aula_id == $aula->id ? 'selected' : '' }}>
                                {{ $aula->grado->nivel->nombre ?? '' }} - {{ $aula->grado->nombre ?? '' }} - 
                                Sección "{{ $aula->seccion->nombre ?? '' }}" ({{ $aula->turno_nombre }}) - 
                                {{ $aula->anioAcademico->anio ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="aula-info" id="aulaInfo" style="display: {{ $matricula->aula_id ? 'block' : 'none' }};">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="aulaInfoText">
                            @if($matricula->aula)
                                <strong>Información del Aula:</strong> 
                                {{ $matricula->aula->grado->nivel->nombre ?? '' }} 
                                {{ $matricula->aula->grado->nombre ?? '' }} - 
                                Sección "{{ $matricula->aula->seccion->nombre ?? '' }}" - 
                                Turno: {{ $matricula->aula->turno_nombre }} - 
                                Año: {{ $matricula->aula->anioAcademico->anio ?? '' }} - 
                                Docente: {{ $matricula->aula->docente ? $matricula->aula->docente->nombre_completo : 'No asignado' }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_matricula" class="form-label required-field">Fecha de Matrícula</label>
                    <input type="date" class="form-control @error('fecha_matricula') is-invalid @enderror" 
                           id="fecha_matricula" name="fecha_matricula" 
                           value="{{ $matricula->fecha_matricula->format('Y-m-d') }}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="estado" class="form-label required-field">Estado</label>
                    <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                        <option value="activa" {{ $matricula->estado == 'activa' ? 'selected' : '' }}>Activa</option>
                        <option value="retirada" {{ $matricula->estado == 'retirada' ? 'selected' : '' }}>Retirada</option>
                        <option value="culminada" {{ $matricula->estado == 'culminada' ? 'selected' : '' }}>Culminada</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="apoderado_id" class="form-label">Apoderado Principal</label>
                    <select class="form-select select2" id="apoderado_id" name="apoderado_id">
                        <option value="">Seleccionar apoderado (opcional)</option>
                        @foreach($apoderados as $apoderado)
                            <option value="{{ $apoderado->id }}" {{ $matricula->apoderado_id == $apoderado->id ? 'selected' : '' }}>
                                {{ $apoderado->nombre_completo }} - {{ $apoderado->dni }} ({{ $apoderado->telefono ?? 'Sin teléfono' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                          placeholder="Información adicional sobre la matrícula...">{{ $matricula->observaciones }}</textarea>
            </div>
            
            @if($matricula->estado == 'retirada')
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Matrícula Retirada:</strong> Esta matrícula ha sido retirada. Si deseas reactivarla, cambia el estado a "Activa".
            </div>
            @elseif($matricula->estado == 'culminada')
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Matrícula Culminada:</strong> Esta matrícula ha sido culminada exitosamente.
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Información:</strong> Puedes cambiar el estado de la matrícula según corresponda.
            </div>
            @endif
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i> Actualizar Matrícula
                    </button>
                    <a href="{{ route('admin.matriculas.index') }}" class="btn btn-secondary">
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
    
    // Enviar formulario de actualización
    $('#matriculaForm').on('submit', function(e) {
        e.preventDefault();
        
        let submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner me-2"></span> Actualizando...');
        
        $.ajax({
            url: '{{ route("admin.matriculas.update", $matricula) }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizada!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("admin.matriculas.index") }}';
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.message || 'Error al actualizar la matrícula';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save me-2"></i> Actualizar Matrícula');
            }
        });
    });
});
</script>
@endsection

@section('css')
<style>
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
@endsection