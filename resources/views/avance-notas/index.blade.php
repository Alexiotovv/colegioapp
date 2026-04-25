{{-- resources/views/avance-notas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Avance de Registro de Notas')

@section('css')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .aulas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .aula-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: pointer;
    }
    
    .aula-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .aula-card-header {
        padding: 15px 20px;
        color: white;
        position: relative;
    }
    
    .aula-card-body {
        padding: 15px 20px;
    }
    
    .aula-card-footer {
        padding: 10px 20px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        font-size: 12px;
        color: #6c757d;
    }
    
    .progress-avance {
        margin-top: 10px;
    }
    
    .progress-avance .progress {
        height: 10px;
        border-radius: 5px;
    }
    
    .porcentaje-texto {
        font-size: 24px;
        font-weight: bold;
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    /* Modal de detalle */
    .modal-detalle .modal-dialog {
        max-width: 90%;
        width: 1200px;
    }
    
    .curso-avance-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .curso-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .competencia-item {
        font-size: 13px;
        padding: 5px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .stats-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .stats-badge.completed {
        background: #d4edda;
        color: #155724;
    }
    
    .stats-badge.pending {
        background: #f8d7da;
        color: #721c24;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-chart-line me-2" style="color: var(--primary-color);"></i>
            Avance de Registro de Notas
        </h4>
    </div>
    
    <!-- Filtros -->
    <div class="filter-card">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="periodo_id" class="form-label required-field">Periodo Académico</label>
                <select class="form-select" id="periodo_id" required>
                    <option value="">Seleccionar periodo</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}">
                            {{ $periodo->nombre }} ({{ $anioActivo->anio }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="nivel_id" class="form-label">Nivel</label>
                <select class="form-select" id="nivel_id">
                    <option value="">Todos los niveles</option>
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="grado_id" class="form-label">Grado</label>
                <select class="form-select" id="grado_id" disabled>
                    <option value="">Primero seleccione un nivel</option>
                </select>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12 text-end">
                <button class="btn btn-primary" id="btnCargarAvance">
                    <i class="fas fa-search me-2"></i> Ver Avance
                </button>
            </div>
        </div>
    </div>
    
    <!-- Grid de Aulas -->
    <div id="aulasContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>
                <i class="fas fa-door-open me-2"></i>
                Aulas - Avance de Registro
            </h5>
            <span class="badge bg-secondary" id="totalAulasCount">0 aulas</span>
        </div>
        <div class="aulas-grid" id="aulasGrid">
            <!-- Las aulas se cargarán aquí -->
        </div>
    </div>
</div>

<!-- Modal Detalle de Aula -->
<div class="modal fade modal-detalle" id="modalDetalleAula" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-chalkboard me-2"></i>
                    Detalle de Avance - <span id="modalAulaNombre"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetalleBody">
                <div class="text-center py-5">
                    <div class="loading-spinner"></div>
                    <p class="mt-2">Cargando detalles...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none;">
    <div class="loading-overlay">
        <div class="text-center bg-white p-4 rounded-4 shadow">
            <div class="loading-spinner" style="width: 40px; height: 40px;"></div>
            <p class="mt-3 mb-0">Cargando información...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let gradosPorNivel = {};
    
    // Cargar grados según nivel seleccionado
    $('#nivel_id').on('change', function() {
        let nivelId = $(this).val();
        let gradoSelect = $('#grado_id');
        
        if (nivelId) {
            $.ajax({
                url: "{{ url('admin/configuracion/grados-por-nivel') }}/" + nivelId,
                method: 'GET',
                success: function(response) {
                    gradoSelect.html('<option value="">Todos los grados</option>');
                    if (response.length > 0) {
                        for (let grado of response) {
                            gradoSelect.append(`<option value="${grado.id}">${grado.nombre}</option>`);
                        }
                        gradoSelect.prop('disabled', false);
                    } else {
                        gradoSelect.html('<option value="">No hay grados disponibles</option>');
                        gradoSelect.prop('disabled', true);
                    }
                },
                error: function() {
                    gradoSelect.html('<option value="">Error al cargar grados</option>');
                    gradoSelect.prop('disabled', true);
                }
            });
        } else {
            gradoSelect.html('<option value="">Primero seleccione un nivel</option>');
            gradoSelect.prop('disabled', true);
        }
    });
    
    // Cargar avance de aulas
    $('#btnCargarAvance').on('click', function() {
        let periodoId = $('#periodo_id').val();
        let nivelId = $('#nivel_id').val();
        let gradoId = $('#grado_id').val();
        
        if (!periodoId) {
            Swal.fire('Error', 'Seleccione un periodo académico', 'error');
            return;
        }
        
        mostrarLoading(true);
        
        $.ajax({
            url: '{{ route("admin.avance-notas.resumen-aulas") }}',
            method: 'GET',
            data: {
                periodo_id: periodoId,
                nivel_id: nivelId,
                grado_id: gradoId
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderizarAulas(response.data);
                    $('#totalAulasCount').text(response.data.length + ' aulas');
                    $('#aulasContainer').show();
                } else {
                    $('#aulasGrid').html(`
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay aulas disponibles para los filtros seleccionados</p>
                        </div>
                    `);
                    $('#aulasContainer').show();
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar los datos', 'error');
            },
            complete: function() {
                mostrarLoading(false);
            }
        });
    });
    
    // Renderizar las tarjetas de aulas
    function renderizarAulas(aulas) {
        let html = '';
        
        for (let item of aulas) {
            let aula = item.aula;
            let porcentaje = item.porcentaje;
            let color = item.color;
            let sinCursos = item.sin_cursos || false;
            let sinEstudiantes = item.sin_estudiantes || false;
            
            let estadoTexto = '';
            let estadoColor = '';
            
            if (sinCursos) {
                estadoTexto = 'Sin cursos asignados';
                estadoColor = '#dc3545';
            } else if (sinEstudiantes) {
                estadoTexto = 'Sin estudiantes matriculados';
                estadoColor = '#ffc107';
            }
            
            html += `
                <div class="aula-card" data-aula-id="${aula.id}" data-periodo-id="${$('#periodo_id').val()}" onclick="verDetalleAula(${aula.id}, '${aula.nombre.replace(/'/g, "\\'")}')">
                    <div class="aula-card-header" style="background: ${color};">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-door-open fa-2x"></i>
                            </div>
                            <div class="text-end">
                                <div class="porcentaje-texto">${porcentaje}%</div>
                                <small>Avance</small>
                            </div>
                        </div>
                    </div>
                    <div class="aula-card-body">
                        <h6 class="mb-1">${aula.nombre}</h6>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-graduation-cap"></i> ${aula.grado} - ${aula.seccion}<br>
                            <i class="fas fa-clock"></i> ${aula.turno}<br>
                            <i class="fas fa-chalkboard-user"></i> ${aula.docente}
                        </p>
                        <div class="progress-avance">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: ${porcentaje}%; background-color: ${color};" 
                                     aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="aula-card-footer">
                        ${sinCursos ? '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + estadoTexto + '</span>' : 
                          sinEstudiantes ? '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> ' + estadoTexto + '</span>' :
                          `<span><i class="fas fa-check-circle text-success"></i> Registrados: ${item.total_registrado || 0} / ${item.total_esperado || 0}</span>`}
                    </div>
                </div>
            `;
        }
        
        $('#aulasGrid').html(html);
    }
    
    // Función global para ver detalle del aula
    window.verDetalleAula = function(aulaId, aulaNombre) {
        let periodoId = $('#periodo_id').val();
        
        $('#modalAulaNombre').text(aulaNombre);
        $('#modalDetalleBody').html(`
            <div class="text-center py-5">
                <div class="loading-spinner"></div>
                <p class="mt-2">Cargando detalles del aula...</p>
            </div>
        `);
        $('#modalDetalleAula').modal('show');
        
        $.ajax({
            url: '{{ route("admin.avance-notas.avance-aula") }}',
            method: 'GET',
            data: {
                periodo_id: periodoId,
                aula_id: aulaId
            },
            success: function(response) {
                if (response.success) {
                    renderizarDetalleAula(response.data);
                } else {
                    $('#modalDetalleBody').html(`
                        <div class="alert alert-danger">Error al cargar los detalles</div>
                    `);
                }
            },
            error: function() {
                $('#modalDetalleBody').html(`
                    <div class="alert alert-danger">Error al cargar los detalles del aula</div>
                `);
            }
        });
    };
    
    // Renderizar detalle del aula
    function renderizarDetalleAula(data) {
        let aula = data.aula;
        let cursos = data.cursos;
        let matriculas = data.matriculas;
        let resumen = data.resumen;
        
        let html = `
            <div class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="mb-0">${cursos.length}</h3>
                                <small class="text-muted">Cursos asignados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="mb-0">${matriculas.length}</h3>
                                <small class="text-muted">Estudiantes matriculados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card" style="background: ${resumen.color}; color: white;">
                            <div class="card-body text-center">
                                <h3 class="mb-0">${resumen.porcentaje_global}%</h3>
                                <small>Avance global</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Resumen:</strong> ${resumen.total_registrado} de ${resumen.total_esperado} notas registradas 
                (${resumen.porcentaje_global}% completado)
            </div>
            
            <h6 class="mb-3"><i class="fas fa-book me-2"></i>Avance por Curso</h6>
        `;
        
        for (let curso of cursos) {
            let colorCurso = curso.porcentaje >= 90 ? '#28a745' : (curso.porcentaje >= 70 ? '#17a2b8' : (curso.porcentaje >= 50 ? '#ffc107' : '#dc3545'));
            
            html += `
                <div class="curso-avance-card">
                    <div class="curso-header">
                        <div>
                            <strong><i class="fas fa-book-open"></i> ${curso.curso_nombre}</strong>
                            <small class="text-muted ms-2">(${curso.curso_codigo})</small>
                        </div>
                        <div>
                            <span class="stats-badge completed">
                                <i class="fas fa-check-circle"></i> ${curso.total_registrado}/${curso.total_esperado}
                            </span>
                            <span class="stats-badge ${curso.porcentaje >= 100 ? 'completed' : 'pending'} ms-2">
                                ${curso.porcentaje}%
                            </span>
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: ${curso.porcentaje}%; background-color: ${colorCurso};" 
                             aria-valuenow="${curso.porcentaje}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-trophy"></i> Competencias: ${curso.total_competencias} | 
                        <i class="fas fa-users"></i> Estudiantes: ${curso.total_estudiantes}
                    </small>
                </div>
            `;
        }
        
        $('#modalDetalleBody').html(html);
    }
    
    function mostrarLoading(mostrar) {
        if (mostrar) {
            $('#loadingOverlay').fadeIn(200);
        } else {
            $('#loadingOverlay').fadeOut(200);
        }
    }
});
</script>

<style>
    .loading-spinner {
        display: inline-block;
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .aula-card {
        cursor: pointer;
    }
</style>
@endsection