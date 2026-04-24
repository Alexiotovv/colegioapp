{{-- resources/views/modulos/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Módulo')

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

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-edit me-2" style="color: var(--primary-color);"></i>
            Editar Módulo: {{ $modulo->nombre }}
        </h4>
        <div>
            <a href="{{ route('admin.modulos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.modulos.update', $modulo) }}" id="moduloForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo" class="form-label required-field">Código</label>
                    <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                           id="codigo" name="codigo" value="{{ old('codigo', $modulo->codigo) }}" required>
                    <small class="text-muted">Ej: notas, usuarios, dashboard</small>
                    @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label required-field">Nombre</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre', $modulo->nombre) }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ruta" class="form-label">Ruta</label>
                    <input type="text" class="form-control @error('ruta') is-invalid @enderror" 
                           id="ruta" name="ruta" value="{{ old('ruta', $modulo->ruta) }}" placeholder="Ej: admin.dashboard">
                    <small class="text-muted">Nombre de la ruta en Laravel</small>
                    @error('ruta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="icono" class="form-label">Icono</label>
                    <div class="input-group">
                        <span class="input-group-text" id="iconoPreview"><i class="fas {{ $modulo->icono ?? 'fa-cube' }}"></i></span>
                        <input type="text" class="form-control @error('icono') is-invalid @enderror" 
                               id="icono" name="icono" value="{{ old('icono', $modulo->icono) }}" placeholder="Ej: fa-users, fa-home">
                    </div>
                    <small class="text-muted">Clase de Font Awesome (fa-users, fa-home, etc.)</small>
                    @error('icono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="padre_id" class="form-label">Módulo Padre</label>
                    <select class="form-select @error('padre_id') is-invalid @enderror" id="padre_id" name="padre_id">
                        <option value="">Ninguno (menú principal)</option>
                        @foreach($modulos as $item)
                            @if($item->id != $modulo->id)
                                <option value="{{ $item->id }}" {{ old('padre_id', $modulo->padre_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nombre }} ({{ $item->codigo }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <small class="text-muted">Para crear submenús</small>
                    @error('padre_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('orden') is-invalid @enderror" 
                           id="orden" name="orden" value="{{ old('orden', $modulo->orden) }}">
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $modulo->activo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i> Actualizar Módulo
                    </button>
                    <a href="{{ route('admin.modulos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Previsualizar icono
        $('#icono').on('keyup change', function() {
            let icono = $(this).val();
            if (icono) {
                $('#iconoPreview').html(`<i class="fas ${icono}"></i>`);
            } else {
                $('#iconoPreview').html('<i class="fas fa-cube"></i>');
            }
        });
        
        $('#moduloForm').on('submit', function() {
            $('#submitBtn').prop('disabled', true);
            $('#submitBtn').html('<span class="loading-spinner me-2"></span> Actualizando...');
        });
    });
</script>
@endsection