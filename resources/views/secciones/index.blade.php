{{-- resources/views/secciones/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Secciones')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-users-viewfinder me-2" style="color: var(--primary-color);"></i>
            Secciones
        </h4>
        <a href="{{ route('admin.secciones.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nueva Sección
        </a>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sección</th>
                        <th>Turno</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($secciones as $seccion)
                    <tr>
                        <td>{{ $seccion->id }}</td>
                        <td><strong>{{ $seccion->nombre }}</strong></td>
                        <td>
                            <span class="badge bg-info">{{ $seccion->turno }}</span>
                        </td>
                        <td>
                            @if($seccion->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.secciones.edit', $seccion) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.secciones.toggle-active', $seccion) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-{{ $seccion->activo ? 'secondary' : 'success' }}">
                                    <i class="fas fa-{{ $seccion->activo ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.secciones.destroy', $seccion) }}" method="POST" class="d-inline" id="deleteForm{{ $seccion->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('deleteForm{{ $seccion->id }}', '¿Eliminar sección {{ $seccion->nombre }}?')" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay secciones registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $secciones->links() }}
    </div>
</div>
@endsection