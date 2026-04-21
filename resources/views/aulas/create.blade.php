{{-- resources/views/aulas/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Aula')

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
    /* Estilos para Select2 */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border-radius: 8px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px;
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
            Nueva Aula
        </h4>
        <a href="{{ route('admin.aulas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.aulas.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label required-field">Nombre del Aula</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" 
                           placeholder="Ej: Aula de 1ro A, Laboratorio de Ciencias" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="turno" class="form-label required-field">Turno</label>
                    <select class="form-select @error('turno') is-invalid @enderror" id="turno" name="turno" required>
                        <option value="">Seleccionar turno</option>
                        @foreach($turnos as $key => $label)
                            <option value="{{ $key }}" {{ old('turno') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('turno')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="nivel_id" class="form-label required-field">Nivel</label>
                    <select class="form-select @error('nivel_id') is-invalid @enderror" id="nivel_id" name="nivel_id" required>
                        <option value="">Seleccionar nivel</option>
                        @foreach($niveles as $nivel)
                            <option value="{{ $nivel->id }}" {{ old('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                {{ $nivel->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('nivel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="grado_id" class="form-label required-field">Grado</label>
                    <select class="form-select @error('grado_id') is-invalid @enderror" id="grado_id" name="grado_id" required>
                        <option value="">Primero seleccione un nivel</option>
                    </select>
                    @error('grado_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="seccion_id" class="form-label required-field">Sección</label>
                    <select class="form-select @error('seccion_id') is-invalid @enderror" id="seccion_id" name="seccion_id" required>
                        <option value="">Seleccionar sección</option>
                        @foreach($secciones as $seccion)
                            <option value="{{ $seccion->id }}" {{ old('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                {{ $seccion->nombre }} - {{ $seccion->turno }}
                            </option>
                        @endforeach
                    </select>
                    @error('seccion_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="anio_academico_id" class="form-label required-field">Año Académico</label>
                    <select class="form-select @error('anio_academico_id') is-invalid @enderror" id="anio_academico_id" name="anio_academico_id" required>
                        <option value="">Seleccionar año</option>
                        @foreach($anios as $anio)
                            <option value="{{ $anio->id }}" {{ old('anio_academico_id', $anioActivo->id ?? '') == $anio->id ? 'selected' : '' }}>
                                {{ $anio->anio }} {{ $anio->activo ? '(Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('anio_academico_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="docente_id" class="form-label">Docente Tutor</label>
                    <select class="form-select select2 @error('docente_id') is-invalid @enderror" id="docente_id" name="docente_id">
                        <option value="">Seleccionar docente (opcional)</option>
                        @foreach($docentes as $docente)
                            <option value="{{ $docente->id }}" {{ old('docente_id') == $docente->id ? 'selected' : '' }}>
                                {{ $docente->name }} - {{ $docente->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('docente_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="capacidad" class="form-label">Capacidad (estudiantes)</label>
                    <input type="number" class="form-control @error('capacidad') is-invalid @enderror" 
                           id="capacidad" name="capacidad" value="{{ old('capacidad', 30) }}" min="1" max="100">
                    @error('capacidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="ubicacion" class="form-label">Ubicación</label>
                    <input type="text" class="form-control @error('ubicacion') is-invalid @enderror" 
                           id="ubicacion" name="ubicacion" value="{{ old('ubicacion') }}" 
                           placeholder="Ej: Pabellón A, 2do piso">
                    @error('ubicacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                          id="descripcion" name="descripcion" rows="3" 
                          placeholder="Información adicional sobre el aula...">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Nota:</strong> El código del aula se generará automáticamente basado en el grado, sección y año.
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Guardar Aula
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Cargar grados cuando se selecciona un nivel
    $('#docente_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Buscar docente...',
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron docentes";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });
    
    $('#nivel_id').on('change', function() {
        let nivelId = $(this).val();
        let gradoSelect = $('#grado_id');
        
        if (nivelId) {
            gradoSelect.html('<option value="">Cargando...</option>');
            
            $.ajax({
                url: '{{ route("admin.aulas.grados-by-nivel") }}',
                method: 'GET',
                data: { nivel_id: nivelId },
                success: function(response) {
                    gradoSelect.html('<option value="">Seleccionar grado</option>');
                    if (response.length > 0) {
                        for (let grado of response) {
                            gradoSelect.append(`<option value="${grado.id}">${grado.nombre}</option>`);
                        }
                    } else {
                        gradoSelect.append('<option value="">No hay grados disponibles</option>');
                    }
                },
                error: function() {
                    gradoSelect.html('<option value="">Error al cargar grados</option>');
                }
            });
        } else {
            gradoSelect.html('<option value="">Primero seleccione un nivel</option>');
        }
    });
    
    // Si ya hay un nivel seleccionado (por error de validación), cargar grados
    let nivelSeleccionado = $('#nivel_id').val();
    if (nivelSeleccionado) {
        $('#nivel_id').trigger('change');
    }
});
</script>
@endsection