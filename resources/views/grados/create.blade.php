{{-- resources/views/grados/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Grado')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-plus-circle me-2"></i>Nuevo Grado</h4>
        <a href="{{ route('admin.grados.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.grados.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nivel_id" class="form-label required-field">Nivel</label>
                    <select class="form-select @error('nivel_id') is-invalid @enderror" id="nivel_id" name="nivel_id" required>
                        <option value="">Seleccionar nivel</option>
                        @foreach($niveles as $nivel)
                            <option value="{{ $nivel->id }}" {{ old('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                {{ $nivel->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('nivel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label required-field">Grado</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: 1ro, 2do, 3ro" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('orden') is-invalid @enderror" 
                           id="orden" name="orden" value="{{ old('orden', 0) }}">
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Guardar
            </button>
        </form>
    </div>
</div>
@endsection