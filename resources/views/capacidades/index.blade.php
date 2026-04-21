{{-- resources/views/capacidades/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Capacidades')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-tasks me-2" style="color: var(--primary-color);"></i>
            Capacidades
        </h4>
        <a href="{{ route('admin.capacidades.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nueva Capacidad
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar capacidad..." value="{{ request('search') }}">
                </div>
                <div class="col-md-5">
                    <select name="competencia_id" class="form-select">
                        <option value="">Todas las competencias</option>
                        @foreach($competencias as $competencia)
                            <option value="{{ $competencia->id }}" {{ request('competencia_id') == $competencia->id ? 'selected' : '' }}>
                                {{ $competencia->curso->nombre ?? '' }}: {{ $competencia->nombre }}
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
                        <th>Competencia</th>
                        <th>Capacidad</th>
                        <th>Ponderación</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($capacidades as $capacidad)
                    <tr>
                        <td>{{ $capacidad->competencia->nombre ?? 'N/A' }}</td>
                        <td>{{ $capacidad->nombre }}</td>
                        <td>{{ $capacidad->ponderacion }}%</td>
                        <td>{{ $capacidad->orden }}</td>
                        <td>
                            @if($capacidad->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.capacidades.edit', $capacidad) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.capacidades.toggle-active', $capacidad) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $capacidad->activo ? 'secondary' : 'success' }}">
                                        <i class="fas fa-{{ $capacidad->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.capacidades.destroy', $capacidad) }}" method="POST" class="d-inline" id="deleteForm{{ $capacidad->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('deleteForm{{ $capacidad->id }}', '¿Eliminar capacidad {{ $capacidad->nombre }}?')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay capacidades registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $capacidades->links() }}
    </div>
</div>
@endsection