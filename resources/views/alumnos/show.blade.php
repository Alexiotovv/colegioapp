{{-- resources/views/alumnos/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle del Alumno')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-graduate me-2" style="color: var(--primary-color);"></i>
            Detalle del Alumno
        </h4>
        <div>
            <a href="{{ route('admin.alumnos.edit', $alumno) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <a href="{{ route('admin.alumnos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- Información Personal -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información Personal</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Código:</strong>
                    <p>{{ $alumno->codigo_estudiante }}</p>
                </div>
                <div class="col-md-3">
                    <strong>DNI:</strong>
                    <p>{{ $alumno->dni }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Sexo:</strong>
                    <p>{{ $alumno->sexo_nombre }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Edad:</strong>
                    <p>{{ $alumno->edad }} años</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Nombre Completo:</strong>
                    <p>{{ $alumno->nombre_completo }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Fecha de Nacimiento:</strong>
                    <p>{{ $alumno->fecha_nacimiento->format('d/m/Y') }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Dirección:</strong>
                    <p>{{ $alumno->direccion ?? 'No registrada' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Teléfono:</strong>
                    <p>{{ $alumno->telefono ?? 'No registrado' }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Email:</strong>
                    <p>{{ $alumno->email ?? 'No registrado' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Estado:</strong>
                    <p>
                        @if($alumno->estado == 'activo')
                            <span class="badge bg-success">Activo</span>
                        @elseif($alumno->estado == 'inactivo')
                            <span class="badge bg-secondary">Inactivo</span>
                        @elseif($alumno->estado == 'retirado')
                            <span class="badge bg-danger">Retirado</span>
                        @else
                            <span class="badge bg-info">Egresado</span>
                        @endif
                    </p>
                </div>
            </div>
            @if($alumno->observaciones)
            <div class="row">
                <div class="col-12">
                    <strong>Observaciones:</strong>
                    <p>{{ $alumno->observaciones }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Apoderados - SECCIÓN CORREGIDA -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Apoderados</h5>
        </div>
        <div class="card-body">
            @if($alumno->apoderados && $alumno->apoderados->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>DNI</th>
                                <th>Apoderado</th>
                                <th>Parentesco</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Notificaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumno->apoderados as $apoderado)
                            <tr>
                                <td>{{ $apoderado->dni }}</td>
                                <td>
                                    <strong>{{ $apoderado->apellido_paterno }} {{ $apoderado->apellido_materno }}</strong><br>
                                    <small>{{ $apoderado->nombres }}</small>
                                </td>
                                <td>{{ $apoderado->parentesco_nombre }}</td>
                                <td>{{ $apoderado->telefono ?? '—' }}</td>
                                <td>{{ $apoderado->email ?? '—' }}</td>
                                <td>
                                    @if($apoderado->pivot->recibe_notificaciones ?? $apoderado->recibe_notificaciones)
                                        <span class="badge bg-success">
                                            <i class="fas fa-bell me-1"></i> Activas
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-bell-slash me-1"></i> Inactivas
                                        </span>
                                    @endif
                                 </td>
                                <td>
                                    <a href="{{ route('admin.apoderados.show', $apoderado) }}" class="btn btn-sm btn-info" title="Ver apoderado">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                 </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>No hay apoderados registrados</strong><br>
                    <small>Este alumno no tiene apoderados asignados. 
                        <a href="{{ route('admin.apoderados.create') }}?alumno_id={{ $alumno->id }}" class="alert-link">
                            Haz clic aquí para asignar un apoderado
                        </a>
                    </small>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Matrículas -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Historial de Matrículas</h5>
        </div>
        <div class="card-body">
            @if($alumno->matriculas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Apoderado Principal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumno->matriculas as $matricula)
                                <tr>
                                    <td>{{ $matricula->aula->anioAcademico->anio ?? 'N/A' }}</td>
                                    <td>{{ $matricula->aula->grado->nombre ?? 'N/A' }}</td>
                                    <td>{{ $matricula->aula->seccion->nombre ?? 'N/A' }}</td>
                                    <td>{{ $matricula->fecha_matricula->format('d/m/Y') }}</td>
                                    <td>{{ ucfirst($matricula->estado) }}</td>
                                    <td>
                                        @if($matricula->apoderado)
                                            {{ $matricula->apoderado->nombre_completo }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">No hay matrículas registradas</p>
            @endif
        </div>
    </div>
</div>
@endsection