@extends('layouts.app')

@section('title', 'Alumnos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-graduate me-2" style="color: var(--primary-color);"></i>
            Alumnos
        </h4>
        <a href="{{ route('admin.alumnos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Alumno
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por nombre, DNI o código..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $key => $label)
                            <option value="{{ $key }}" {{ request('estado') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.alumnos.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo me-1"></i> Limpiar
                    </a>
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
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Edad</th>
                        <th>Grado Actual</th>
                        <th>Sección</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                    <tr>
                        <td>{{ $alumno->codigo_estudiante }}</td>
                        <td>{{ $alumno->dni }}</td>
                        <td>
                            <strong>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</strong><br>
                            <small>{{ $alumno->nombres }}</small>
                         </td>
                        <td>{{ $alumno->edad }} años</td>
                        <td>{{ $alumno->gradoActual->nombre ?? 'No matriculado' }}</td>
                        <td>{{ $alumno->seccionActual->nombre ?? '-' }}</td>
                        <td>
                            @if($alumno->estado == 'activo')
                                <span class="badge bg-success">Activo</span>
                            @elseif($alumno->estado == 'inactivo')
                                <span class="badge bg-secondary">Inactivo</span>
                            @elseif($alumno->estado == 'retirado')
                                <span class="badge bg-danger">Retirado</span>
                            @else
                                <span class="badge bg-info">Egresado</span>
                            @endif
                         </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.alumnos.show', $alumno) }}" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.alumnos.edit', $alumno) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">📚 Ver Matrículas</a></li>
                                    <li><a class="dropdown-item" href="#">📝 Ver Notas</a></li>
                                    <li><a class="dropdown-item" href="#">💰 Ver Pagos</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.alumnos.destroy', $alumno) }}" method="POST" id="deleteForm{{ $alumno->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="dropdown-item text-danger" 
                                                    onclick="confirmDelete('deleteForm{{ $alumno->id }}', '¿Eliminar alumno {{ $alumno->nombre_completo }}?')">
                                                🗑️ Eliminar
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                         </td>
                     </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="fas fa-inbox me-2"></i> No hay alumnos registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $alumnos->links() }}
    </div>
</div>
@endsection