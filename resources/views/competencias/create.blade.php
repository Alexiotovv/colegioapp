{{-- resources/views/competencias/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Competencia')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-plus-circle me-2"></i>Nueva Competencia</h4>
        <a href="{{ route('admin.competencias.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.competencias.store') }}">
            @csrf
            
            <div class="mb-3">
                <label for="curso_id" class="form-label required-field">Curso</label>
                <select class="form-select @error('curso_id') is-invalid @enderror" id="curso_id" name="curso_id" required>
                    <option value="">Seleccionar curso</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ old('curso_id') == $curso->id ? 'selected' : '' }}>
                            {{ $curso->grado->nivel->nombre ?? '' }} - {{ $curso->grado->nombre ?? '' }}: {{ $curso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('curso_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">El nivel y grado se heredan automáticamente del curso seleccionado</small>
            </div>
            
            <div class="mb-3">
                <label for="nombre" class="form-label required-field">Nombre de la Competencia</label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                       id="nombre" name="nombre" value="{{ old('nombre') }}" 
                       placeholder="Ej: Resuelve problemas de cantidad" required>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ponderacion" class="form-label">Ponderación (%)</label>
                    <input type="number" step="0.01" class="form-control @error('ponderacion') is-invalid @enderror" 
                           id="ponderacion" name="ponderacion" value="{{ old('ponderacion', 100) }}" min="0" max="100">
                    <small class="text-muted">Porcentaje que representa dentro del curso</small>
                    @error('ponderacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('orden') is-invalid @enderror" 
                           id="orden" name="orden" value="{{ old('orden', 0) }}">
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                          id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Información:</strong> La competencia pertenece al curso seleccionado. 
                El nivel y grado se determinan automáticamente a través del curso.
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Guardar Competencia
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