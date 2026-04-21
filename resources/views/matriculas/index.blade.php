{{-- resources/views/matriculas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Matrículas')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-address-card me-2" style="color: var(--primary-color);"></i>
            Matrículas
        </h4>
        <a href="{{ route('admin.matriculas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nueva Matrícula
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por alumno..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="aula_id" class="form-select">
                        <option value="">Todas las aulas</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}" {{ request('aula_id') == $aula->id ? 'selected' : '' }}>
                                {{ $aula->grado->nivel->nombre ?? '' }} - {{ $aula->grado->nombre ?? '' }} - 
                                Sec "{{ $aula->seccion->nombre ?? '' }}" - {{ $aula->anioAcademico->anio ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="anio_academico_id" class="form-select">
                        <option value="">Todos los años</option>
                        @foreach($anios as $anio)
                            <option value="{{ $anio->id }}" {{ request('anio_academico_id') == $anio->id ? 'selected' : '' }}>
                                {{ $anio->anio }} {{ $anio->activo ? '(Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Alumno</th>
                        <th>Aula</th>
                        <th>Grado - Sección</th>
                        <th>Año</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matriculas as $matricula)
                    <tr>
                        <td>{{ $matricula->alumno->codigo_estudiante ?? 'N/A' }}</td>
                        <td>
                            <strong>{{ $matricula->alumno->apellido_paterno ?? '' }} {{ $matricula->alumno->apellido_materno ?? '' }}</strong><br>
                            <small>{{ $matricula->alumno->nombres ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $matricula->aula->nombre ?? 'N/A' }}</td>
                        <td>
                            {{ $matricula->aula->grado->nombre ?? 'N/A' }} - 
                            "{{ $matricula->aula->seccion->nombre ?? 'N/A' }}"
                        </td>
                        <td>{{ $matricula->aula->anioAcademico->anio ?? 'N/A' }}</td>
                        <td>{{ $matricula->fecha_matricula->format('d/m/Y') }}</td>
                        <td>{!! $matricula->estado_badge !!}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.matriculas.show', $matricula) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.matriculas.edit', $matricula) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.matriculas.destroy', $matricula) }}" method="POST" class="d-inline" id="deleteForm{{ $matricula->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('deleteForm{{ $matricula->id }}', '¿Eliminar matrícula de {{ $matricula->alumno->nombre_completo ?? '' }}?')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="fas fa-inbox me-2"></i> No hay matrículas registradas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $matriculas->links() }}
    </div>
</div>
@endsection