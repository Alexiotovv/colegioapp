    {{-- resources/views/alumnos/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Alumno')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-edit me-2" style="color: var(--primary-color);"></i>
            Editar Alumno: {{ $alumno->nombre_completo }}
        </h4>
        <div>
            <a href="{{ route('admin.alumnos.show', $alumno) }}" class="btn btn-info">
                <i class="fas fa-eye me-2"></i>Ver Detalle
            </a>
            <a href="{{ route('admin.alumnos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.alumnos.update', $alumno) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo_estudiante" class="form-label">Código de Estudiante</label>
                    <input type="text" class="form-control" 
                           id="codigo_estudiante" name="codigo_estudiante" 
                           value="{{ $alumno->codigo_estudiante }}" readonly>
                    <small class="text-muted">El código no puede ser modificado</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="dni" class="form-label required-field">DNI</label>
                    <input type="text" class="form-control @error('dni') is-invalid @enderror" 
                           id="dni" name="dni" value="{{ old('dni', $alumno->dni) }}" maxlength="8" required>
                    @error('dni')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="apellido_paterno" class="form-label required-field">Apellido Paterno</label>
                    <input type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" 
                           id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $alumno->apellido_paterno) }}" required>
                    @error('apellido_paterno')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="apellido_materno" class="form-label required-field">Apellido Materno</label>
                    <input type="text" class="form-control @error('apellido_materno') is-invalid @enderror" 
                           id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $alumno->apellido_materno) }}" required>
                    @error('apellido_materno')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="nombres" class="form-label required-field">Nombres</label>
                    <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                           id="nombres" name="nombres" value="{{ old('nombres', $alumno->nombres) }}" required>
                    @error('nombres')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="fecha_nacimiento" class="form-label required-field">Fecha de Nacimiento</label>
                    <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                           id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento->format('Y-m-d')) }}" required>
                    @error('fecha_nacimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="sexo" class="form-label required-field">Sexo</label>
                    <select class="form-select @error('sexo') is-invalid @enderror" id="sexo" name="sexo" required>
                        <option value="">Seleccionar</option>
                        <option value="M" {{ old('sexo', $alumno->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo', $alumno->sexo) == 'F' ? 'selected' : '' }}>Femenino</option>
                    </select>
                    @error('sexo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $alumno->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                           id="direccion" name="direccion" value="{{ old('direccion', $alumno->direccion) }}">
                    @error('direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                           id="telefono" name="telefono" value="{{ old('telefono', $alumno->telefono) }}">
                    @error('telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="estado" class="form-label required-field">Estado</label>
                    <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                        <option value="activo" {{ old('estado', $alumno->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado', $alumno->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="retirado" {{ old('estado', $alumno->estado) == 'retirado' ? 'selected' : '' }}>Retirado</option>
                        <option value="egresado" {{ old('estado', $alumno->estado) == 'egresado' ? 'selected' : '' }}>Egresado</option>
                    </select>
                    @error('estado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="apoderados" class="form-label">Apoderados</label>
                    <select class="form-select @error('apoderados') is-invalid @enderror" 
                            id="apoderados" name="apoderados[]" multiple size="6">
                        @foreach($apoderados as $apoderado)
                            <option value="{{ $apoderado->id }}" 
                                {{ in_array($apoderado->id, $apoderadosSeleccionados) ? 'selected' : '' }}>
                                {{ $apoderado->nombre_completo }} - {{ $apoderado->dni }}
                                @if($apoderado->telefono) - {{ $apoderado->telefono }} @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Mantén presionado Ctrl (Windows/Linux) o Cmd (Mac) para seleccionar múltiples apoderados
                    </small>
                    @error('apoderados')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                          id="observaciones" name="observaciones" rows="4">{{ old('observaciones', $alumno->observaciones) }}</textarea>
                @error('observaciones')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Nota:</strong> Si cambias el estado del alumno a "Retirado" o "Egresado", 
                este no podrá ser matriculado en años académicos futuros hasta que se reactive.
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Actualizar Alumno
                    </button>
                    <a href="{{ route('admin.alumnos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
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
    
    select[multiple] {
        min-height: 150px;
        background-color: #f8f9fa;
    }
    
    select[multiple] option {
        padding: 8px 12px;
        border-bottom: 1px solid #e9ecef;
    }
    
    select[multiple] option:checked {
        background-color: var(--primary-color) linear-gradient(0deg, var(--primary-color) 0%, var(--primary-color) 100%);
        color: white;
    }
    
    select[multiple] option:hover {
        background-color: #e9ecef;
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Validar DNI (8 dígitos)
        $('#dni').on('keyup', function() {
            let dni = $(this).val();
            if (dni.length > 0 && !/^\d+$/.test(dni)) {
                $(this).addClass('is-invalid');
                $(this).next('.invalid-feedback').text('El DNI solo debe contener números');
            } else if (dni.length > 0 && dni.length !== 8) {
                $(this).addClass('is-invalid');
                $(this).next('.invalid-feedback').text('El DNI debe tener 8 dígitos');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Validar fecha de nacimiento (no puede ser futura)
        $('#fecha_nacimiento').on('change', function() {
            let fecha = new Date($(this).val());
            let hoy = new Date();
            if (fecha > hoy) {
                $(this).addClass('is-invalid');
                $(this).next('.invalid-feedback').text('La fecha de nacimiento no puede ser futura');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Mostrar spinner al enviar
        $('#submitBtn').on('click', function() {
            $(this).prop('disabled', true);
            $(this).html('<i class="fas fa-spinner fa-spin me-2"></i> Actualizando...');
            $('form').submit();
        });
        
        // Contador de apoderados seleccionados
        $('#apoderados').on('change', function() {
            let count = $(this).find('option:selected').length;
            if (count > 0) {
                $('label[for="apoderados"]').html(`Apoderados <span class="badge bg-primary">${count} seleccionado(s)</span>`);
            } else {
                $('label[for="apoderados"]').html('Apoderados');
            }
        }).trigger('change');
    });
</script>
@endsection