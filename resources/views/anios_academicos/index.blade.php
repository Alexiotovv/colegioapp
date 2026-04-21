{{-- resources/views/anios_academicos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Años Académicos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-calendar-alt me-2" style="color: var(--primary-color);"></i>
            Años Académicos
        </h4>
        <a href="{{ route('admin.anios.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Año Académico
        </a>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Año</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                        <th>Días Transcurridos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($anios as $anio)
                    <tr>
                        <td>{{ $anio->id }}</td>
                        <td>
                            <strong>{{ $anio->anio }}</strong>
                        </td>
                        <td>{{ $anio->fecha_inicio->format('d/m/Y') }}</td>
                        <td>{{ $anio->fecha_fin->format('d/m/Y') }}</td>
                        <td>
                            @if($anio->activo)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Activo
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-clock me-1"></i> Inactivo
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $hoy = now();
                                $inicio = $anio->fecha_inicio;
                                $fin = $anio->fecha_fin;
                                $total = $inicio->diffInDays($fin);
                                $transcurridos = $hoy->gt($inicio) ? $inicio->diffInDays(min($hoy, $fin)) : 0;
                                $porcentaje = $total > 0 ? round(($transcurridos / $total) * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center">
                                <span class="me-2 small">{{ $porcentaje }}%</span>
                                <div class="progress flex-grow-1" style="height: 6px; width: 100px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.anios.edit', $anio) }}" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            @if(!$anio->activo)
                                <form action="{{ route('admin.anios.set-activo', $anio) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success" title="Activar">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.anios.destroy', $anio) }}" method="POST" class="d-inline" id="deleteForm{{ $anio->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('deleteForm{{ $anio->id }}', '¿Eliminar el año {{ $anio->anio }}?')" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="fas fa-calendar-times me-2"></i> No hay años académicos registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-3">
            {{ $anios->links() }}
        </div>
    </div>
</div>
@endsection