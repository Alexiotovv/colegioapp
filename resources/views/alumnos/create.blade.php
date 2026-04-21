{{-- resources/views/alumnos/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Alumno')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-user-plus me-2"></i>Registrar Nuevo Alumno</h4>
        <a href="{{ route('admin.alumnos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.alumnos.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo_estudiante" class="form-label">Código de Estudiante</label>
                    <input type="text" class="form-control @error('codigo_estudiante') is-invalid @enderror" 
                           id="codigo_estudiante" name="codigo_estudiante" 
                           value="{{ old('codigo_estudiante', $codigoGenerado) }}" readonly>
                    <small class="text-muted">Código generado automáticamente</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="dni" class="form-label required-field">DNI</label>
                    <input type="text" class="form-control @error('dni') is-invalid @enderror" 
                           id="dni" name="dni" value="{{ old('dni') }}" maxlength="8" required>
                    @error('dni')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="apellido_paterno" class="form-label required-field">Apellido Paterno</label>
                    <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                           id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                    @error('apellido_paterno')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="apellido_materno" class="form-label required-field">Apellido Materno</label>
                    <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror" 
                           id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" required>
                    @error('apellido_materno')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="nombres" class="form-label required-field">Nombres</label>
                    <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                           id="nombres" name="nombres" value="{{ old('nombres') }}" required>
                    @error('nombres')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="fecha_nacimiento" class="form-label required-field">Fecha de Nacimiento</label>
                    <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                           id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                    @error('fecha_nacimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="sexo" class="form-label required-field">Sexo</label>
                    <select class="form-select @error('sexo') is-invalid @enderror" id="sexo" name="sexo" required>
                        <option value="">Seleccionar</option>
                        <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                    </select>
                    @error('sexo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                           id="direccion" name="direccion" value="{{ old('direccion') }}">
                    @error('direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                           id="telefono" name="telefono" value="{{ old('telefono') }}">
                    @error('telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="apoderados" class="form-label">Apoderados</label>
                <select class="form-select @error('apoderados') is-invalid @enderror" 
                        id="apoderados" name="apoderados[]" multiple size="5">
                    @foreach($apoderados as $apoderado)
                        <option value="{{ $apoderado->id }}" {{ in_array($apoderado->id, old('apoderados', [])) ? 'selected' : '' }}>
                            {{ $apoderado->nombre_completo }} - {{ $apoderado->dni }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Mantén presionado Ctrl para seleccionar múltiples apoderados</small>
                @error('apoderados')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                          id="observaciones" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                @error('observaciones')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Guardar Alumno
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
    select[multiple] {
        min-height: 150px;
    }
</style>
@endsection