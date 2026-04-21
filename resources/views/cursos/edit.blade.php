{{-- resources/views/cursos/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Curso')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-edit me-2" style="color: var(--primary-color);"></i>
            Editar Curso: {{ $curso->nombre }}
        </h4>
        <div>
            <a href="{{ route('admin.competencias.index') }}?curso_id={{ $curso->id }}" class="btn btn-info">
                <i class="fas fa-star me-2"></i> Ver Competencias
            </a>
            <a href="{{ route('admin.cursos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.cursos.update', $curso) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo" class="form-label required-field">Código</label>
                    <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                           id="codigo" name="codigo" value="{{ old('codigo', $curso->codigo) }}" 
                           placeholder="Ej: MAT01, COM01" required>
                    @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label required-field">Tipo</label>
                    <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                        <option value="">Seleccionar tipo</option>
                        @foreach($tipos as $key => $label)
                            <option value="{{ $key }}" {{ old('tipo', $curso->tipo) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="nombre" class="form-label required-field">Nombre del Curso</label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                       id="nombre" name="nombre" value="{{ old('nombre', $curso->nombre) }}" 
                       placeholder="Ej: Matemática, Comunicación, Ciencia y Tecnología" required>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="grado_id" class="form-label required-field">Grado</label>
                    <select class="form-select @error('grado_id') is-invalid @enderror" id="grado_id" name="grado_id" required>
                        <option value="">Seleccionar grado</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id }}" {{ old('grado_id', $curso->grado_id) == $grado->id ? 'selected' : '' }}>
                                {{ $grado->nivel->nombre }} - {{ $grado->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('grado_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="horas_semanales" class="form-label">Horas Semanales</label>
                    <input type="number" class="form-control @error('horas_semanales') is-invalid @enderror" 
                           id="horas_semanales" name="horas_semanales" value="{{ old('horas_semanales', $curso->horas_semanales) }}" min="0" max="40">
                    @error('horas_semanales')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('orden') is-invalid @enderror" 
                           id="orden" name="orden" value="{{ old('orden', $curso->orden) }}">
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $curso->activo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                          id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $curso->descripcion) }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            @if($curso->competencias->count() > 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Nota:</strong> Este curso tiene {{ $curso->competencias->count() }} competencias asociadas. 
                Modificar el grado puede afectar la relación.
            </div>
            @endif
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Actualizar Curso
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