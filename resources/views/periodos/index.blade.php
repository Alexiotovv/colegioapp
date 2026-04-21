{{-- resources/views/periodos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Periodos Académicos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-calendar-week me-2" style="color: var(--primary-color);"></i>
            Periodos Académicos (Bimestres)
        </h4>
        <a href="{{ route('admin.periodos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Periodo
        </a>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Año</th>
                        <th>Periodo</th>
                        <th>Orden</th>
                        <th>Fechas</th>
                        <th>Avance</th>
                        <th>Estado</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodos as $periodo)
                    <tr>
                        <td>{{ $periodo->id }}</td>
                        <td>
                            <strong>{{ $periodo->anioAcademico->anio ?? 'N/A' }}</strong>
                        </td>
                        <td>{{ $periodo->nombre }}</td>
                        <td>{{ $periodo->orden }}° Bimestre</td>
                        <td>
                            <small>
                                {{ $periodo->fecha_inicio->format('d/m/Y') }}<br>
                                al {{ $periodo->fecha_fin->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2 small">{{ $periodo->porcentajeAvance }}%</span>
                                <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                                    <div class="progress-bar bg-{{ $periodo->porcentajeAvance >= 100 ? 'secondary' : 'success' }}" 
                                         role="progressbar" 
                                         style="width: {{ $periodo->porcentajeAvance }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($periodo->activo)
                                @if($periodo->isEnCurso())
                                    <span class="badge bg-success">En curso</span>
                                @elseif(now()->lt($periodo->fecha_inicio))
                                    <span class="badge bg-info">Próximo</span>
                                @else
                                    <span class="badge bg-secondary">Finalizado</span>
                                @endif
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            @if($periodo->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.periodos.edit', $periodo) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.periodos.toggle-active', $periodo) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-{{ $periodo->activo ? 'secondary' : 'success' }}">
                                    <i class="fas fa-{{ $periodo->activo ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.periodos.destroy', $periodo) }}" method="POST" class="d-inline" id="deleteForm{{ $periodo->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('deleteForm{{ $periodo->id }}', '¿Eliminar el periodo {{ $periodo->nombre }}?')" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay periodos académicos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $periodos->links() }}
    </div>
</div>
@endsection