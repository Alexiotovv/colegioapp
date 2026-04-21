{{-- resources/views/capacidades/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Capacidad')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-edit me-2" style="color: var(--primary-color);"></i>
            Editar Capacidad: {{ $capacidad->nombre }}
        </h4>
        <a href="{{ route('admin.capacidades.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.capacidades.update', $capacidad) }}">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="competencia_id" class="form-label required-field">Competencia</label>
                <select class="form-select @error('competencia_id') is-invalid @enderror" id="competencia_id" name="competencia_id" required>
                    <option value="">Seleccionar competencia</option>
                    @foreach($competencias as $competencia)
                        <option value="{{ $competencia->id }}" {{ old('competencia_id', $capacidad->competencia_id) == $competencia->id ? 'selected' : '' }}>
                            {{ $competencia->curso->nombre ?? '' }}: {{ $competencia->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('competencia_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="nombre" class="form-label required-field">Nombre de la Capacidad</label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                       id="nombre" name="nombre" value="{{ old('nombre', $capacidad->nombre) }}" required>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ponderacion" class="form-label">Ponderación (%)</label>
                    <input type="number" step="0.01" class="form-control @error('ponderacion') is-invalid @enderror" 
                           id="ponderacion" name="ponderacion" value="{{ old('ponderacion', $capacidad->ponderacion) }}" min="0" max="100">
                    <small class="text-muted">Porcentaje que representa dentro de la competencia</small>
                    @error('ponderacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('orden') is-invalid @enderror" 
                           id="orden" name="orden" value="{{ old('orden', $capacidad->orden) }}">
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $capacidad->activo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                          id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $capacidad->descripcion) }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            @if($capacidad->notas()->count() > 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Nota:</strong> Esta capacidad tiene {{ $capacidad->notas()->count() }} notas registradas. 
                Modificarla puede afectar los registros existentes.
            </div>
            @endif
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Actualizar Capacidad
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