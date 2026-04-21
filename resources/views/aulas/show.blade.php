{{-- resources/views/aulas/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle del Aula')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-door-open me-2" style="color: var(--primary-color);"></i>
            Detalle del Aula: {{ $aula->nombre }}
        </h4>
        <div>
            <a href="{{ route('admin.aulas.edit', $aula) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i> Editar
            </a>
            <a href="{{ route('admin.aulas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información General</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Código:</th>
                            <td><strong>{{ $aula->codigo }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nombre:</th>
                            <td>{{ $aula->nombre }}</td>
                        </tr>
                        <tr>
                            <th>Turno:</th>
                            <td>{{ $aula->turno_nombre }}</td>
                        </tr>
                        <tr>
                            <th>Capacidad:</th>
                            <td>{{ $aula->capacidad }} estudiantes</td>
                        </tr>
                        <tr>
                            <th>Ubicación:</th>
                            <td>{{ $aula->ubicacion ?? 'No especificada' }}</td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                @if($aula->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Descripción:</th>
                            <td>{{ $aula->descripcion ?? 'No hay descripción' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Información Académica</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Nivel:</th>
                            <td>{{ $aula->nivel->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Grado:</th>
                            <td>{{ $aula->grado->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Sección:</th>
                            <td>{{ $aula->seccion->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Año Académico:</th>
                            <td>{{ $aula->anioAcademico->anio ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Docente Tutor:</th>
                            <td>
                                @if($aula->docente)
                                    <strong>{{ $aula->docente->nombre_completo }}</strong><br>
                                    <small class="text-muted">{{ $aula->docente->especialidad ?? 'Sin especialidad' }}</small>
                                @else
                                    <span class="text-muted">No asignado</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Información Adicional</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Fecha de Creación:</strong>
                    <p>{{ $aula->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Última Actualización:</strong>
                    <p>{{ $aula->updated_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection