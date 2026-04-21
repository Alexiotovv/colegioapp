{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('css')
<style>
    .btn-action {
        padding: 5px 10px;
        margin: 0 2px;
    }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-users me-2"></i>
            Gestión de Usuarios
        </h4>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Usuario
        </a>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <i class="fas fa-user-circle me-1"></i>
                            {{ $user->username }}
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-{{ $user->role->nombre == 'admin' ? 'danger' : ($user->role->nombre == 'docente' ? 'info' : 'secondary') }}">
                                {{ ucfirst($user->role->nombre) }}
                            </span>
                        </td>
                        <td>
                            @if($user->activo)
                                <span class="status-badge bg-success text-white">
                                    <i class="fas fa-check-circle me-1"></i> Activo
                                </span>
                            @else
                                <span class="status-badge bg-danger text-white">
                                    <i class="fas fa-ban me-1"></i> Inactivo
                                </span>
                            @endif
                        </td>
                        <td>{{ $user->ultimo_acceso ? $user->ultimo_acceso->diffForHumans() : 'Nunca' }}</td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning btn-action" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            @if($user->id != auth()->id())
                                <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $user->activo ? 'secondary' : 'success' }} btn-action" title="{{ $user->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas fa-{{ $user->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" id="deleteForm{{ $user->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('deleteForm{{ $user->id }}', '¿Eliminar usuario {{ $user->name }}?')" class="btn btn-sm btn-danger btn-action" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay usuarios registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection