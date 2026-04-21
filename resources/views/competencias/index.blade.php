{{-- resources/views/competencias/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Competencias')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-star me-2" style="color: var(--primary-color);"></i>
            Competencias
        </h4>
        <a href="{{ route('admin.competencias.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nueva Competencia
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar competencia..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="grado_id" class="form-select">
                        <option value="">Todos los grados</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id }}" {{ request('grado_id') == $grado->id ? 'selected' : '' }}>
                                {{ $grado->nivel->nombre }} - {{ $grado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="curso_id" class="form-select">
                        <option value="">Todos los cursos</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                                {{ $curso->grado->nivel->nombre ?? '' }} - {{ $curso->grado->nombre ?? '' }}: {{ $curso->nombre }}
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
                        <th>Nivel - Grado</th>
                        <th>Curso</th>
                        <th>Competencia</th>
                        <th>Ponderación</th>
                        <th>Capacidades</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($competencias as $competencia)
                    <tr>
                        <td>
                            {{ $competencia->curso->grado->nivel->nombre ?? '' }} - 
                            {{ $competencia->curso->grado->nombre ?? '' }}
                        </td>
                        <td>
                            <strong>{{ $competencia->curso->nombre ?? 'N/A' }}</strong><br>
                            <small>{{ $competencia->curso->codigo ?? '' }}</small>
                        </td>
                        <td>{{ $competencia->nombre }}</td>
                        <td>{{ $competencia->ponderacion }}%</td>
                        <td>
                            <a href="{{ route('admin.capacidades.index') }}?competencia_id={{ $competencia->id }}" 
                               class="btn btn-sm btn-info">
                                {{ $competencia->capacidades->count() }} capacidades
                            </a>
                        </td>
                        <td>
                            @if($competencia->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.competencias.edit', $competencia) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.competencias.toggle-active', $competencia) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $competencia->activo ? 'secondary' : 'success' }}">
                                        <i class="fas fa-{{ $competencia->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.competencias.destroy', $competencia) }}" method="POST" class="d-inline" id="deleteForm{{ $competencia->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('deleteForm{{ $competencia->id }}', '¿Eliminar competencia {{ $competencia->nombre }}?')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay competencias registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $competencias->links() }}
    </div>
</div>
@endsection