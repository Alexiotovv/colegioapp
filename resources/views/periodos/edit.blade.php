{{-- resources/views/periodos/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Periodo Académico')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-edit me-2"></i>Editar Periodo: {{ $periodo->nombre }}</h4>
        <a href="{{ route('admin.periodos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.periodos.update', $periodo) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="anio_academico_id" class="form-label required-field">Año Académico</label>
                    <select class="form-select @error('anio_academico_id') is-invalid @enderror" 
                            id="anio_academico_id" name="anio_academico_id" required>
                        <option value="">Seleccionar año</option>
                        @foreach($anios as $anio)
                            <option value="{{ $anio->id }}" {{ old('anio_academico_id', $periodo->anio_academico_id) == $anio->id ? 'selected' : '' }}>
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
                        <option value="1" {{ old('orden', $periodo->orden) == 1 ? 'selected' : '' }}>I Bimestre</option>
                        <option value="2" {{ old('orden', $periodo->orden) == 2 ? 'selected' : '' }}>II Bimestre</option>
                        <option value="3" {{ old('orden', $periodo->orden) == 3 ? 'selected' : '' }}>III Bimestre</option>
                        <option value="4" {{ old('orden', $periodo->orden) == 4 ? 'selected' : '' }}>IV Bimestre</option>
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
                           id="nombre" name="nombre" value="{{ old('nombre', $periodo->nombre) }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $periodo->activo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activar este periodo</label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_inicio" class="form-label required-field">Fecha de Inicio</label>
                    <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                           id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $periodo->fecha_inicio->format('Y-m-d')) }}" required>
                    @error('fecha_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="fecha_fin" class="form-label required-field">Fecha de Fin</label>
                    <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" 
                           id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin', $periodo->fecha_fin->format('Y-m-d')) }}" required>
                    @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            @if($periodo->notas()->count() > 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Advertencia:</strong> Este periodo tiene {{ $periodo->notas()->count() }} notas registradas. 
                Cambiar las fechas podría afectar los reportes.
            </div>
            @endif
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Actualizar
            </button>
        </form>
    </div>
</div>
@endsection