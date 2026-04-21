{{-- resources/views/apoderados/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle del Apoderado')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-circle me-2" style="color: var(--primary-color);"></i>
            Detalle del Apoderado
        </h4>
        <div>
            <a href="{{ route('admin.apoderados.edit', $apoderado) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i> Editar
            </a>
            <a href="{{ route('admin.apoderados.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
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
                    <strong>DNI:</strong>
                    <p>{{ $apoderado->dni }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Sexo:</strong>
                    <p>{{ $apoderado->sexo_nombre }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Parentesco:</strong>
                    <p>{{ $apoderado->parentesco_nombre }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Notificaciones:</strong>
                    <p>
                        @if($apoderado->recibe_notificaciones)
                            <span class="badge bg-success">Activadas</span>
                        @else
                            <span class="badge bg-secondary">Desactivadas</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Nombre Completo:</strong>
                    <p>{{ $apoderado->nombre_completo }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Teléfono:</strong>
                    <p>{{ $apoderado->telefono ?? 'No registrado' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Email:</strong>
                    <p>{{ $apoderado->email ?? 'No registrado' }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Dirección:</strong>
                    <p>{{ $apoderado->direccion ?? 'No registrada' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <strong>Fecha de Registro:</strong>
                    <p>{{ $apoderado->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Última Actualización:</strong>
                    <p>{{ $apoderado->updated_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alumnos a cargo -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Alumnos a Cargo</h5>
        </div>
        <div class="card-body">
            @if($apoderado->alumnos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>DNI</th>
                                <th>Alumno</th>
                                <th>Grado Actual</th>
                                <th>Sección</th>
                                <th>Matrícula</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($apoderado->alumnos as $alumno)
                            <tr>
                                <td>{{ $alumno->codigo_estudiante }}</td>
                                <td>{{ $alumno->dni }}</td>
                                <td>
                                    <strong>{{ $alumno->nombre_completo }}</strong><br>
                                    <small>Edad: {{ $alumno->edad }} años</small>
                                </td>
                                <td>
                                    @php
                                        $matriculaActiva = $alumno->matriculas->firstWhere('estado', 'activa');
                                    @endphp
                                    {{ $matriculaActiva ? $matriculaActiva->grado->nombre : 'No matriculado' }}
                                </td>
                                <td>
                                    {{ $matriculaActiva ? $matriculaActiva->seccion->nombre : '-' }}
                                </td>
                                <td>
                                    @if($matriculaActiva)
                                        <span class="badge bg-success">{{ $matriculaActiva->anioAcademico->anio }}</span>
                                    @else
                                        <a href="{{ route('admin.matriculas.create') }}?alumno_id={{ $alumno->id }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Matricular
                                        </a>
                                    @endif
                                 </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No tiene alumnos asignados aún.
                    <a href="{{ route('admin.alumnos.index') }}">Asignar alumnos</a>
                </p>
            @endif
        </div>
    </div>
    
    <!-- Historial de Matrículas como Apoderado -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Matrículas Registradas</h5>
        </div>
        <div class="card-body">
            @if($apoderado->matriculas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th>Alumno</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($apoderado->matriculas as $matricula)
                            <tr>
                                <td>{{ $matricula->anioAcademico->anio }}</td>
                                <td>{{ $matricula->alumno->nombre_completo ?? 'N/A' }}</td>
                                <td>{{ $matricula->grado->nombre ?? 'N/A' }}</td>
                                <td>{{ $matricula->seccion->nombre ?? 'N/A' }}</td>
                                <td>{{ $matricula->fecha_matricula->format('d/m/Y') }}</td>
                                <td>{!! $matricula->estado_badge !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No ha registrado matrículas como apoderado principal.</p>
            @endif
        </div>
    </div>
</div>
@endsection