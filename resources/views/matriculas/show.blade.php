@extends('layouts.app')

@section('title', 'Detalle de Matrícula')

@section('css')
<style>
    .detail-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .detail-header {
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .detail-header h5 {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .detail-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 5px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .detail-value {
        font-size: 16px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .info-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }
    
    .btn-print {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }
    
    .btn-print:hover {
        background-color: #5a6268;
        border-color: #5a6268;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-address-card me-2" style="color: var(--primary-color);"></i>
            Detalle de Matrícula
        </h4>
        <div>
            <button onclick="window.print()" class="btn btn-print me-2">
                <i class="fas fa-print me-2"></i> Imprimir
            </button>
            <a href="{{ route('admin.matriculas.edit', $matricula) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-2"></i> Editar
            </a>
            <a href="{{ route('admin.matriculas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Información de la Matrícula -->
        <div class="col-md-6">
            <div class="detail-card">
                <div class="detail-header">
                    <h5><i class="fas fa-info-circle me-2"></i>Información de Matrícula</h5>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">ID Matrícula</div>
                        <div class="detail-value">#{{ $matricula->id }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Estado</div>
                        <div class="detail-value">{!! $matricula->estado_badge !!}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Fecha de Matrícula</div>
                        <div class="detail-value">{{ $matricula->fecha_matricula->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Fecha de Registro</div>
                        <div class="detail-value">{{ $matricula->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="detail-label">Observaciones</div>
                        <div class="detail-value">{{ $matricula->observaciones ?? 'No hay observaciones' }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información del Alumno -->
        <div class="col-md-6">
            <div class="detail-card">
                <div class="detail-header">
                    <h5><i class="fas fa-user-graduate me-2"></i>Información del Alumno</h5>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Código</div>
                        <div class="detail-value">{{ $matricula->alumno->codigo_estudiante ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">DNI</div>
                        <div class="detail-value">{{ $matricula->alumno->dni ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="detail-label">Nombre Completo</div>
                        <div class="detail-value">
                            <strong>{{ $matricula->alumno->nombre_completo ?? 'N/A' }}</strong>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Fecha de Nacimiento</div>
                        <div class="detail-value">{{ $matricula->alumno->fecha_nacimiento->format('d/m/Y') ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Edad</div>
                        <div class="detail-value">{{ $matricula->alumno->edad ?? 'N/A' }} años</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Teléfono</div>
                        <div class="detail-value">{{ $matricula->alumno->telefono ?? 'No registrado' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $matricula->alumno->email ?? 'No registrado' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Información del Aula -->
        <div class="col-md-6">
            <div class="detail-card">
                <div class="detail-header">
                    <h5><i class="fas fa-door-open me-2"></i>Información del Aula</h5>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Código de Aula</div>
                        <div class="detail-value">{{ $matricula->aula->codigo ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Nombre del Aula</div>
                        <div class="detail-value">{{ $matricula->aula->nombre ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="detail-label">Nivel</div>
                        <div class="detail-value">{{ $matricula->aula->grado->nivel->nombre ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Grado</div>
                        <div class="detail-value">{{ $matricula->aula->grado->nombre ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Sección</div>
                        <div class="detail-value">{{ $matricula->aula->seccion->nombre ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Turno</div>
                        <div class="detail-value">{{ $matricula->aula->turno_nombre ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Año Académico</div>
                        <div class="detail-value">{{ $matricula->aula->anioAcademico->anio ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Capacidad</div>
                        <div class="detail-value">{{ $matricula->aula->capacidad ?? 'N/A' }} estudiantes</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Ubicación</div>
                        <div class="detail-value">{{ $matricula->aula->ubicacion ?? 'No especificada' }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información del Docente Tutor y Apoderado -->
        <div class="col-md-6">
            <div class="detail-card">
                <div class="detail-header">
                    <h5><i class="fas fa-chalkboard-user me-2"></i>Docente Tutor</h5>
                </div>
                
                @if($matricula->aula->docente)
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Nombres</div>
                        <div class="detail-value">{{ $matricula->aula->docente->nombres ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Apellidos</div>
                        <div class="detail-value">{{ $matricula->aula->docente->apellido_paterno ?? '' }} {{ $matricula->aula->docente->apellido_materno ?? '' }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">DNI</div>
                        <div class="detail-value">{{ $matricula->aula->docente->dni ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Especialidad</div>
                        <div class="detail-value">{{ $matricula->aula->docente->especialidad ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Teléfono</div>
                        <div class="detail-value">{{ $matricula->aula->docente->telefono ?? 'No registrado' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $matricula->aula->docente->email ?? 'No registrado' }}</div>
                    </div>
                </div>
                @else
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No hay docente tutor asignado a este aula
                </div>
                @endif
            </div>
            
            <!-- Información del Apoderado -->
            <div class="detail-card">
                <div class="detail-header">
                    <h5><i class="fas fa-users me-2"></i>Apoderado Principal</h5>
                </div>
                
                @if($matricula->apoderado)
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">DNI</div>
                        <div class="detail-value">{{ $matricula->apoderado->dni ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Parentesco</div>
                        <div class="detail-value">{{ $matricula->apoderado->parentesco_nombre ?? 'N/A' }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="detail-label">Nombre Completo</div>
                        <div class="detail-value">
                            <strong>{{ $matricula->apoderado->nombre_completo ?? 'N/A' }}</strong>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Teléfono</div>
                        <div class="detail-value">{{ $matricula->apoderado->telefono ?? 'No registrado' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $matricula->apoderado->email ?? 'No registrado' }}</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="detail-label">Dirección</div>
                        <div class="detail-value">{{ $matricula->apoderado->direccion ?? 'No registrada' }}</div>
                    </div>
                </div>
                @else
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No se ha asignado un apoderado principal para esta matrícula
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Acciones adicionales -->
    <div class="detail-card">
        <div class="detail-header">
            <h5><i class="fas fa-cog me-2"></i>Acciones</h5>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.matriculas.edit', $matricula) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i> Editar Matrícula
                    </a>
                    <a href="{{ route('admin.alumnos.show', $matricula->alumno_id) }}" class="btn btn-info">
                        <i class="fas fa-user-graduate me-2"></i> Ver Alumno
                    </a>
                    @if($matricula->apoderado)
                    <a href="{{ route('admin.apoderados.show', $matricula->apoderado_id) }}" class="btn btn-secondary">
                        <i class="fas fa-users me-2"></i> Ver Apoderado
                    </a>
                    @endif
                    <a href="{{ route('admin.aulas.show', $matricula->aula_id) }}" class="btn btn-success">
                        <i class="fas fa-door-open me-2"></i> Ver Aula
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Función para imprimir (estilo optimizado)
        window.print = function() {
            window.print();
        };
    });
</script>
@endsection