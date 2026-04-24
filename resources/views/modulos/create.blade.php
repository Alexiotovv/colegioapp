{{-- resources/views/modulos/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Módulo')

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
    
    /* Estilos para Select2 */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: calc(3rem + 2px);
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 2rem;
    }
</style>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-plus-circle me-2" style="color: var(--primary-color);"></i>
            Nuevo Módulo
        </h4>
        <a href="{{ route('admin.modulos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.modulos.store') }}" id="moduloForm">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo" class="form-label required-field">Código</label>
                    <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                           id="codigo" name="codigo" value="{{ old('codigo') }}" required>
                    <small class="text-muted">Ej: notas, usuarios, dashboard</small>
                    @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label required-field">Nombre</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ruta" class="form-label">Ruta</label>
                    <select class="form-select @error('ruta') is-invalid @enderror" 
                            id="ruta" name="ruta" style="width: 100%;">
                        <option value="">Seleccionar ruta existente...</option>
                        @foreach($rutasDisponibles as $ruta)
                            <option value="{{ $ruta['name'] }}" {{ old('ruta') == $ruta['name'] ? 'selected' : '' }}>
                                {{ $ruta['name'] }} → {{ $ruta['uri'] }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Selecciona una ruta existente del sistema</small>
                    @error('ruta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="icono" class="form-label">Icono</label>
                    <div class="input-group">
                        <span class="input-group-text" id="iconoPreview"><i class="fas fa-cube"></i></span>
                        <input type="text" class="form-control @error('icono') is-invalid @enderror" 
                               id="icono" name="icono" value="{{ old('icono') }}" placeholder="Ej: fa-users, fa-home">
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
                        @foreach($modulos as $modulo)
                            <option value="{{ $modulo->id }}">{{ $modulo->nombre }} ({{ $modulo->codigo }})</option>
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
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i> Guardar Módulo
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
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2
        $('#ruta').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccionar ruta existente...',
            allowClear: true,
            width: '100%'
        });
        
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
            $('#submitBtn').html('<span class="loading-spinner me-2"></span> Guardando...');
        });
    });
</script>
@endsection