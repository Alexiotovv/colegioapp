{{-- resources/views/periodos/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Periodo Académico')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-plus-circle me-2"></i>Nuevo Periodo Académico</h4>
        <a href="{{ route('admin.periodos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.periodos.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="anio_academico_id" class="form-label required-field">Año Académico</label>
                    <select class="form-select @error('anio_academico_id') is-invalid @enderror" 
                            id="anio_academico_id" name="anio_academico_id" required>
                        <option value="">Seleccionar año</option>
                        @foreach($anios as $anio)
                            <option value="{{ $anio->id }}" {{ old('anio_academico_id') == $anio->id ? 'selected' : '' }}>
                                {{ $anio->anio }} {{ $anio->activo ? '(Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('anio_academico_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="orden" class="form-label required-field">Bimestre</label>
                    <select class="form-select @error('orden') is-invalid @enderror" id="orden" name="orden" required>
                        <option value="">Seleccionar bimestre</option>
                        <option value="1" {{ old('orden') == 1 ? 'selected' : '' }}>I Bimestre</option>
                        <option value="2" {{ old('orden') == 2 ? 'selected' : '' }}>II Bimestre</option>
                        <option value="3" {{ old('orden') == 3 ? 'selected' : '' }}>III Bimestre</option>
                        <option value="4" {{ old('orden') == 4 ? 'selected' : '' }}>IV Bimestre</option>
                    </select>
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label required-field">Nombre del Periodo</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" 
                           placeholder="Ej: I Bimestre" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo') ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activar este periodo</label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_inicio" class="form-label required-field">Fecha de Inicio</label>
                    <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                           id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                    @error('fecha_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="fecha_fin" class="form-label required-field">Fecha de Fin</label>
                    <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" 
                           id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
                    @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Nota:</strong> Los periodos se ordenan automáticamente por el número de bimestre.
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Guardar
            </button>
        </form>
    </div>
</div>
@endsection

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
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-completar nombre según el bimestre seleccionado
        $('#orden').on('change', function() {
            var orden = $(this).val();
            var nombre = '';
            switch(parseInt(orden)) {
                case 1: nombre = 'I Bimestre'; break;
                case 2: nombre = 'II Bimestre'; break;
                case 3: nombre = 'III Bimestre'; break;
                case 4: nombre = 'IV Bimestre'; break;
            }
            $('#nombre').val(nombre);
        });
        
        // Validar fechas
        $('#fecha_fin').on('change', function() {
            var inicio = $('#fecha_inicio').val();
            var fin = $(this).val();
            if (inicio && fin && fin <= inicio) {
                $(this).addClass('is-invalid');
                alert('La fecha de fin debe ser mayor a la fecha de inicio');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>
@endsection