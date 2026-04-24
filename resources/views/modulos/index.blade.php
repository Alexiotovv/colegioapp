{{-- resources/views/modulos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestión de Módulos')

@section('css')
<style>
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .badge-activo {
        background-color: #28a745;
        color: white;
    }
    
    .badge-inactivo {
        background-color: #dc3545;
        color: white;
    }
</style>
@endsection

@section('content')
@include('partials.toast')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-cubes me-2" style="color: var(--primary-color);"></i>
            Gestión de Módulos
        </h4>
        <a href="{{ route('admin.modulos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Módulo
        </a>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Ruta</th>
                        <th>Icono</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modulos as $modulo)
                    <tr>
                        <td><strong>{{ $modulo->codigo }}</strong></td>
                        <td>{{ $modulo->nombre }}</small></td>
                        <td>{{ $modulo->ruta ?? '-' }}</small></td>
                        <td><i class="fas {{ $modulo->icono ?? 'fa-cube' }}"></i> {{ $modulo->icono ?? '-' }}</small></td>
                        <td>{{ $modulo->orden }}</small></td>
                        <td>
                            @if($modulo->activo)
                                <span class="badge badge-activo">Activo</span>
                            @else
                                <span class="badge badge-inactivo">Inactivo</span>
                            @endif
                        </small>
                        <td>
                            <a href="{{ route('admin.modulos.edit', $modulo) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-{{ $modulo->activo ? 'secondary' : 'success' }}" 
                                    onclick="toggleModulo({{ $modulo->id }})">
                                <i class="fas fa-{{ $modulo->activo ? 'ban' : 'check' }}"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteModulo({{ $modulo->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </small>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleModulo(id) {
    Swal.fire({
        title: '¿Cambiar estado?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/modulos/${id}/toggle`,
                method: 'PATCH',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toast.success(response.message);
                        location.reload();
                    }
                }
            });
        }
    });
}

function deleteModulo(id) {
    Swal.fire({
        title: '¿Eliminar módulo?',
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
                url: `/admin/modulos/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toast.success(response.message);
                        location.reload();
                    }
                }
            });
        }
    });
}
</script>
@endsection