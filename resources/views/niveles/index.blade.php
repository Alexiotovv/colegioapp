{{-- resources/views/niveles/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Niveles Educativos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-layer-group me-2" style="color: var(--primary-color);"></i>
            Niveles Educativos
        </h4>
        <a href="{{ route('admin.niveles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Nivel
        </a>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Grados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($niveles as $nivel)
                    <tr>
                        <td>{{ $nivel->id }}</td>
                        <td>
                            <strong>{{ $nivel->nombre }}</strong>
                        </td>
                        <td>{{ $nivel->descripcion ?? '—' }}</td>
                        <td>{{ $nivel->orden }}</td>
                        <td>
                            @if($nivel->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ $nivel->grados_count ?? 0 }} </td>
                        <td>
                            <a href="{{ route('admin.niveles.edit', $nivel) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.niveles.toggle-active', $nivel) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-{{ $nivel->activo ? 'secondary' : 'success' }}">
                                    <i class="fas fa-{{ $nivel->activo ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.niveles.destroy', $nivel) }}" method="POST" class="d-inline" id="deleteForm{{ $nivel->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('deleteForm{{ $nivel->id }}', '¿Eliminar nivel {{ $nivel->nombre }}?')" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay niveles registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $niveles->links() }}
    </div>
</div>
@endsection