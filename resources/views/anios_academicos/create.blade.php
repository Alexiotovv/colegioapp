{{-- resources/views/anios_academicos/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Año Académico')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-plus-circle me-2" style="color: var(--primary-color);"></i>
            Nuevo Año Académico
        </h4>
        <a href="{{ route('admin.anios.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.anios.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="anio" class="form-label required-field">Año</label>
                    <input type="text" 
                           class="form-control @error('anio') is-invalid @enderror" 
                           id="anio" 
                           name="anio" 
                           value="{{ old('anio') }}" 
                           placeholder="Ej: 2025"
                           maxlength="4"
                           required>
                    @error('anio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Ingrese el año en formato de 4 dígitos</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="activo" class="form-label">Estado</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo') ? 'checked' : '' }}>
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
                           value="{{ old('fecha_inicio') }}" 
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
                           value="{{ old('fecha_fin') }}" 
                           required>
                    @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Guardar
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

@section('scripts')
<script>
    $(document).ready(function() {
        // Validar que fecha_fin sea mayor que fecha_inicio
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