{{-- resources/views/apoderados/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Apoderados')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-users me-2" style="color: var(--primary-color);"></i>
            Apoderados
        </h4>
        <a href="{{ route('admin.apoderados.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Apoderado
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por nombre, DNI, email o teléfono..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="parentesco" class="form-select">
                        <option value="">Todos los parentescos</option>
                        @foreach($parentescos as $key => $label)
                            <option value="{{ $key }}" {{ request('parentesco') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Buscar
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
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Parentesco</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Alumnos</th>
                        <th>Notificaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apoderados as $apoderado)
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
                            <span class="badge bg-info">{{ $apoderado->alumnos_count ?? $apoderado->alumnos->count() }}</span>
                         </td>
                        <td>
                            @if($apoderado->recibe_notificaciones)
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
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.apoderados.show', $apoderado) }}" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.apoderados.edit', $apoderado) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.apoderados.toggle-notifications', $apoderado) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $apoderado->recibe_notificaciones ? 'secondary' : 'success' }}" title="{{ $apoderado->recibe_notificaciones ? 'Desactivar notificaciones' : 'Activar notificaciones' }}">
                                        <i class="fas fa-{{ $apoderado->recibe_notificaciones ? 'bell-slash' : 'bell' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.apoderados.destroy', $apoderado) }}" method="POST" class="d-inline" id="deleteForm{{ $apoderado->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('deleteForm{{ $apoderado->id }}', '¿Eliminar apoderado {{ $apoderado->nombre_completo }}?')" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                         </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="fas fa-inbox me-2"></i> No hay apoderados registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $apoderados->links() }}
    </div>
</div>
@endsection