{{-- resources/views/carga-horaria/index.blade.php
@extends('layouts.app')

@section('title', 'Carga Horaria')

@section('css')
<style>
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .horario-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 12px;
        display: inline-block;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-clock me-2" style="color: var(--primary-color);"></i>
            Carga Horaria
        </h4>
        <a href="{{ route('admin.carga-horaria.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nueva Asignación
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="docente_id" class="form-select">
                    <option value="">Todos los docentes</option>
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id }}" {{ request('docente_id') == $docente->id ? 'selected' : '' }}>
                            {{ $docente->nombre_completo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="curso_id" class="form-select">
                    <option value="">Todos los cursos</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                            {{ $curso->nombre }}
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
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Docente</th>
                        <th>Curso</th>
                        <th>Aula</th>
                        <th>Horas/Sem</th>
                        <th>Horario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cargasBody">
                    @forelse($cargas as $carga)
                    <tr data-id="{{ $carga->id }}">
                        <td>{{ $carga->id }}</td>
                        <td>
                            <strong>{{ $carga->docente->name ?? 'N/A' }}</strong><br>
                            <small>{{ $carga->docente->email ?? '' }}</small>
                        </td>
                        <td>
                            {{ $carga->curso->nombre ?? 'N/A' }}<br>
                            <small>{{ $carga->curso->nivel->nombre ?? 'Sin nivel' }}</small>
                         </td>
                        <td>
                            {{ $carga->aula->nombre ?? 'N/A' }}<br>
                            <small>{{ $carga->aula->grado->nombre ?? '' }} "{{ $carga->aula->seccion->nombre ?? '' }}"</small>
                         </td>
                        
                        <td>{{ $carga->horas_semanales }} h/sem</td>
                        <td>
                            @if($carga->dia_semana)
                                <div class="horario-card">
                                    <i class="fas fa-calendar-day me-1"></i> {{ $carga->dia_semana_nombre }}<br>
                                    <i class="fas fa-clock me-1"></i> {{ $carga->horario }}
                                </div>
                            @else
                                <span class="text-muted">Horario flexible</span>
                            @endif
                         </td>
                        <td>{!! $carga->estado_badge !!}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.carga-horaria.edit', $carga) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-{{ $carga->estado == 'activo' ? 'secondary' : 'success' }}" 
                                        onclick="toggleEstado({{ $carga->id }})">
                                    <i class="fas fa-{{ $carga->estado == 'activo' ? 'ban' : 'check' }}"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCarga({{ $carga->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                         </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <i class="fas fa-inbox me-2"></i> No hay cargas horarias registradas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $cargas->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleEstado(id) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: 'Esta acción cambiará el estado de la carga horaria',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/carga-horaria/${id}/toggle`,
                method: 'PATCH',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al cambiar estado', 'error');
                }
            });
        }
    });
}

function deleteCarga(id) {
    Swal.fire({
        title: '¿Eliminar carga horaria?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/carga-horaria/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Eliminado', response.message, 'success').then(() => location.reload());
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al eliminar', 'error');
                }
            });
        }
    });
}
</script>
@endsection --}}



{{-- resources/views/carga-horaria/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Carga Horaria - Asignación de Cursos')

@section('css')
<style>
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .badge-estado-activo {
        background-color: #28a745;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
    }
    
    .badge-estado-inactivo {
        background-color: #dc3545;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
    }
    
    .curso-info {
        font-size: 13px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-chalkboard-user me-2" style="color: var(--primary-color);"></i>
            Asignación de Cursos a Docentes
        </h4>
        <a href="{{ route('admin.carga-horaria.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nueva Asignación
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="docente_id" class="form-select">
                    <option value="">Todos los docentes</option>
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id }}" {{ request('docente_id') == $docente->id ? 'selected' : '' }}>
                            {{ $docente->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="curso_id" class="form-select">
                    <option value="">Todos los cursos</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                            {{ $curso->nombre }} ({{ $curso->nivel->nombre ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
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
                        <th>Docente</th>
                        <th>Curso Asignado</th>
                        <th>Aula</th>
                        <th class="text-center">Horas</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cargas as $carga)
                    <tr>
                        <td>
                            <strong>{{ $carga->docente->name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $carga->docente->email ?? '' }}</small>
                        </td>
                        <td>
                            <strong>{{ $carga->curso->nombre ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $carga->curso->nivel->nombre ?? 'Sin nivel' }}</small>
                        </td>
                        <td>
                            {{ $carga->aula->nombre ?? 'N/A' }}<br>
                            <small class="text-muted">
                                {{ $carga->aula->grado->nombre ?? '' }} "{{ $carga->aula->seccion->nombre ?? '' }}"
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $carga->horas_semanales }} h/sem</span>
                        </td>
                        <td class="text-center">
                            <span class="badge-estado-{{ $carga->estado }}">
                                {{ $carga->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.carga-horaria.edit', $carga) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-{{ $carga->estado == 'activo' ? 'secondary' : 'success' }}" 
                                        onclick="toggleEstado({{ $carga->id }})">
                                    <i class="fas fa-{{ $carga->estado == 'activo' ? 'ban' : 'check' }}"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCarga({{ $carga->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            No hay asignaciones registradas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            <table>
        </div>
        <div class="mt-3">
            {{ $cargas->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleEstado(id) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: 'Esta acción cambiará el estado de la asignación',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/carga-horaria/${id}/toggle`,
                method: 'PATCH',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al cambiar estado', 'error');
                }
            });
        }
    });
}

function deleteCarga(id) {
    Swal.fire({
        title: '¿Eliminar asignación?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/carga-horaria/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Eliminado', response.message, 'success').then(() => location.reload());
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al eliminar', 'error');
                }
            });
        }
    });
}
</script>
@endsection