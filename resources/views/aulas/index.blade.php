{{-- resources/views/aulas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Aulas')

@section('css')
<style>
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-door-open me-2" style="color: var(--primary-color);"></i>
            Aulas
        </h4>
        <a href="{{ route('admin.aulas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nueva Aula
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" 
                       placeholder="Buscar aula..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
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
                <select name="nivel_id" class="form-select">
                    <option value="">Todos los niveles</option>
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel->id }}" {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                            {{ $nivel->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="turno" class="form-select">
                    <option value="">Todos los turnos</option>
                    @foreach($turnos as $key => $label)
                        <option value="{{ $key }}" {{ request('turno') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Nivel - Grado - Sección</th>
                        <th>Año</th>
                        <th>Turno</th>
                        <th>Docente Tutor</th>
                        <th>Capacidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aulas as $aula)
                    <tr>
                        <td><strong>{{ $aula->codigo }}</strong></td>
                        <td>{{ $aula->nombre }}</td>
                        <td>
                            {{ $aula->nivel->nombre ?? '' }} - 
                            {{ $aula->grado->nombre ?? '' }} - 
                            "{{ $aula->seccion->nombre ?? '' }}"
                        </td>
                        <td>{{ $aula->anioAcademico->anio ?? '' }}</td>
                        <td>{{ $aula->turno_nombre }}</td>
                        <td>
                            @if($aula->docente)
                                {{ $aula->docente->name }}
                            @else
                                <span class="text-muted">No asignado</span>
                            @endif
                        </td>
                        <td>{{ $aula->capacidad }} estudiantes</td>
                        <td>
                            @if($aula->activo)
                                <span class="status-badge bg-success text-white">
                                    <i class="fas fa-check-circle me-1"></i> Activo
                                </span>
                            @else
                                <span class="status-badge bg-secondary text-white">
                                    <i class="fas fa-ban me-1"></i> Inactivo
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.aulas.show', $aula) }}" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.aulas.edit', $aula) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.aulas.toggle-active', $aula) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $aula->activo ? 'secondary' : 'success' }}" title="{{ $aula->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas fa-{{ $aula->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.aulas.destroy', $aula) }}" method="POST" class="d-inline" id="deleteForm{{ $aula->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('deleteForm{{ $aula->id }}', '¿Eliminar el aula {{ $aula->nombre }}?')" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <i class="fas fa-inbox me-2"></i> No hay aulas registradas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $aulas->links() }}
    </div>
</div>
@endsection