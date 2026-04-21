{{-- resources/views/cursos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Cursos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-book me-2" style="color: var(--primary-color);"></i>
            Cursos
        </h4>
        <a href="{{ route('admin.cursos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Curso
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar curso..." value="{{ request('search') }}">
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
                    <select name="tipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        @foreach($tipos as $key => $label)
                            <option value="{{ $key }}" {{ request('tipo') == $key ? 'selected' : '' }}>
                                {{ $label }}
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
                        <th>Curso</th>
                        <th>Nivel - Grado</th>
                        <th>Tipo</th>
                        <th>Horas</th>
                        <th>Comp.</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cursos as $curso)
                    <tr>
                        <td>{{ $curso->codigo }}</td>
                        <td>
                            {{ $carga->curso->nombre ?? 'N/A' }}<br>
                            <small>{{ $carga->curso->nivel->nombre ?? '' }}</small>
                        </td>
                        <td>
                            {{ $curso->grado->nivel->nombre ?? '' }} - {{ $curso->grado->nombre ?? '' }}
                        </td>
                        <td>{{ $curso->tipo_nombre }}</td>
                        <td>{{ $curso->horas_semanales }} h/semana</td>
                        <td>{{ $curso->competencias->count() }}</td>
                        <td>
                            @if($curso->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.competencias.index') }}?curso_id={{ $curso->id }}" 
                                   class="btn btn-sm btn-info" title="Ver competencias">
                                    <i class="fas fa-star"></i>
                                </a>
                                <a href="{{ route('admin.cursos.edit', $curso) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.cursos.toggle-active', $curso) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $curso->activo ? 'secondary' : 'success' }}">
                                        <i class="fas fa-{{ $curso->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.cursos.destroy', $curso) }}" method="POST" class="d-inline" id="deleteForm{{ $curso->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('deleteForm{{ $curso->id }}', '¿Eliminar curso {{ $curso->nombre }}?')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay cursos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $cursos->links() }}
    </div>
</div>
@endsection