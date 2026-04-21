{{-- resources/views/anios_academicos/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Año Académico')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-edit me-2" style="color: var(--primary-color);"></i>
            Editar Año Académico: {{ $anio->anio }}
        </h4>
        <a href="{{ route('admin.anios.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.anios.update', $anio) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="anio" class="form-label required-field">Año</label>
                    <input type="text" 
                           class="form-control @error('anio') is-invalid @enderror" 
                           id="anio" 
                           name="anio" 
                           value="{{ old('anio', $anio->anio) }}" 
                           placeholder="Ej: 2025"
                           maxlength="4"
                           required>
                    @error('anio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="activo" class="form-label">Estado</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $anio->activo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">
                            Activar este año académico
                        </label>
                    </div>
                    <small class="text-muted">Si activa, desactivará automáticamente otros años</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_inicio" class="form-label required-field">Fecha de Inicio</label>
                    <input type="date" 
                           class="form-control @error('fecha_inicio') is-invalid @enderror" 
                           id="fecha_inicio" 
                           name="fecha_inicio" 
                           value="{{ old('fecha_inicio', $anio->fecha_inicio->format('Y-m-d')) }}" 
                           required>
                    @error('fecha_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="fecha_fin" class="form-label required-field">Fecha de Fin</label>
                    <input type="date" 
                           class="form-control @error('fecha_fin') is-invalid @enderror" 
                           id="fecha_fin" 
                           name="fecha_fin" 
                           value="{{ old('fecha_fin', $anio->fecha_fin->format('Y-m-d')) }}" 
                           required>
                    @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            @if($anio->activo)
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Este es el año académico activo actualmente. Si lo desactivas, deberás activar otro año.
            </div>
            @endif
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Actualizar
                    </button>
                    <a href="{{ route('admin.anios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                </div>
            </div>
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