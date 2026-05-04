{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('css')
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }
    
    .stat-number {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #131415;
        font-size: 16px;
    }
    
    .recent-table {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .recent-table h5 {
        margin-bottom: 20px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Welcome Card -->
    <div class="welcome-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-2">
                    <i class="fas fa-chart-line me-2"></i>
                    ¡Bienvenido, {{ Auth::user()->name }}!
                </h4>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-calendar-alt me-2"></i>
                    {{ now()->format('l, d \\d\\e F \\d\\e Y') }}
                </p>
            </div>
            <div class="text-end">
                <i class="fas fa-graduation-cap fa-3x opacity-25"></i>
            </div>
        </div>
    </div>
    

    @if (Auth::user() && Auth::user()->isAdmin())
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number">{{ $stats['total_alumnos'] ?? 0 }}</div>
                            <div class="stat-label">Alumnos Activos</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number">{{ $stats['total_docentes'] ?? 0 }}</div>
                            <div class="stat-label">Docentes</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-user"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number">{{ $stats['total_matriculas'] ?? 0 }}</div>
                            <div class="stat-label">Matrículas Activas</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-address-card"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number">{{ $stats['total_notas'] ?? 0 }}</div>
                            <div class="stat-label">Notas Registradas</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Tables -->
        <div class="row">
            <div class="col-md-6">
                <div class="table-container">
                    <h5 class="mb-3">
                        <i class="fas fa-history me-2" style="color: var(--primary-color);"></i>
                        Notas Recientes
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th>Curso</th>
                                    <th>Competencia</th>
                                    <th>Nota</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notas_recientes ?? [] as $nota)
                                <tr>
                                    <td>{{ $nota->matricula->alumno->nombre_completo ?? 'N/A' }}</td>
                                    <td>{{ $nota->competencia->curso->nombre ?? 'N/A' }}</td>
                                    <td>{{ $nota->competencia->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ is_numeric($nota->nota) && $nota->nota >= 11 ? 'success' : 'secondary' }}">
                                            {{ $nota->nota }}
                                        </span>
                                    </td>
                                    <td>{{ $nota->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-inbox me-2"></i>No hay notas registradas
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @if (Auth::user() && Auth::user()->isAdmin())
            <div class="col-md-6">
                <div class="table-container">
                    <h5 class="mb-3">
                        <i class="fas fa-user-plus me-2" style="color: var(--primary-color);"></i>
                        Últimas Matrículas
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                    <th>Aula</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($matriculas_recientes ?? [] as $matricula)
                                <tr>
                                    <td>{{ $matricula->alumno->nombre_completo ?? 'N/A' }}</td>
                                    <td>{{ $matricula->aula->grado->nombre ?? 'N/A' }}</td>
                                    <td>{{ $matricula->aula->seccion->nombre ?? 'N/A' }}</td>
                                    <td>{{ $matricula->aula->nombre ?? 'N/A' }}</td>
                                    <td>{{ $matricula->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-inbox me-2"></i>No hay matrículas registradas
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif
</div>
@endsection