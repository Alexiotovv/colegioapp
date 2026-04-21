{{-- resources/views/competencias/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Competencia')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-edit me-2" style="color: var(--primary-color);"></i>
            Editar Competencia: {{ $competencia->nombre }}
        </h4>
        <div>
            <a href="{{ route('admin.capacidades.index') }}?competencia_id={{ $competencia->id }}" class="btn btn-info">
                <i class="fas fa-tasks me-2"></i> Ver Capacidades
            </a>
            <a href="{{ route('admin.competencias.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.competencias.update', $competencia) }}">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="curso_id" class="form-label required-field">Curso</label>
                <select class="form-select @error('curso_id') is-invalid @enderror" id="curso_id" name="curso_id" required>
                    <option value="">Seleccionar curso</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ old('curso_id', $competencia->curso_id) == $curso->id ? 'selected' : '' }}>
                            {{ $curso->grado->nivel->nombre ?? '' }} - {{ $curso->grado->nombre ?? '' }}: {{ $curso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('curso_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">
                    Curso actual: <strong>{{ $competencia->curso->nombre ?? 'N/A' }}</strong>
                    ({{ $competencia->curso->grado->nivel->nombre ?? '' }} - {{ $competencia->curso->grado->nombre ?? '' }})
                </small>
            </div>
            
            <div class="mb-3">
                <label for="nombre" class="form-label required-field">Nombre de la Competencia</label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                       id="nombre" name="nombre" value="{{ old('nombre', $competencia->nombre) }}" required>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ponderacion" class="form-label">Ponderación (%)</label>
                    <input type="number" step="0.01" class="form-control @error('ponderacion') is-invalid @enderror" 
                           id="ponderacion" name="ponderacion" value="{{ old('ponderacion', $competencia->ponderacion) }}" min="0" max="100">
                    <small class="text-muted">Porcentaje que representa dentro del curso</small>
                    @error('ponderacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('orden') is-invalid @enderror" 
                           id="orden" name="orden" value="{{ old('orden', $competencia->orden) }}">
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $competencia->activo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                          id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $competencia->descripcion) }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            @if($competencia->capacidades->count() > 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Nota:</strong> Esta competencia tiene {{ $competencia->capacidades->count() }} capacidades asociadas.
            </div>
            @endif
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Actualizar Competencia
            </button>
        </form>
    </div>
</div>
@endsection