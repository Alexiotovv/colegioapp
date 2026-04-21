{{-- resources/views/grados/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Grados')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-chalkboard me-2" style="color: var(--primary-color);"></i>
            Grados Académicos
        </h4>
        <a href="{{ route('admin.grados.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Grado
        </a>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nivel</th>
                        <th>Grado</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grados as $grado)
                    <tr>
                        <td>{{ $grado->id }}</td>
                        <td>{{ $grado->nivel->nombre ?? 'N/A' }}</td>
                        <td><strong>{{ $grado->nombre }}</strong></td>
                        <td>{{ $grado->orden }}</td>
                        <td>
                            @if($grado->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.grados.edit', $grado) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.grados.toggle-active', $grado) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-{{ $grado->activo ? 'secondary' : 'success' }}">
                                    <i class="fas fa-{{ $grado->activo ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.grados.destroy', $grado) }}" method="POST" class="d-inline" id="deleteForm{{ $grado->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('deleteForm{{ $grado->id }}', '¿Eliminar grado {{ $grado->nombre }}?')" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                         </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay grados registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $grados->links() }}
    </div>
</div>
@endsection