{{-- resources/views/cursos-jerarquico/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestión Curricular')

@section('css')
<style>
    .tree-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .tree-level-1 {
        margin-left: 0;
        border-left: 3px solid #2c5031;
        padding-left: 15px;
        margin-bottom: 20px;
    }
    
    .tree-level-2 {
        margin-left: 30px;
        border-left: 2px solid #c8a951;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    
    .tree-level-3 {
        margin-left: 30px;
        border-left: 2px solid #3498db;
        padding-left: 15px;
        margin-bottom: 10px;
    }
    
    .tree-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    
    .tree-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .tree-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }
    
    .tree-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .toggle-icon {
        cursor: pointer;
        transition: transform 0.3s;
        width: 24px;
        text-align: center;
    }
    
    .toggle-icon.rotated {
        transform: rotate(90deg);
    }
    
    .tree-children {
        margin-top: 10px;
        margin-left: 25px;
        display: none;
    }
    
    .tree-children.show {
        display: block;
    }
    
    .badge-curso {
        background-color: #2c5031;
        color: white;
    }
    
    .badge-competencia {
        background-color: #c8a951;
        color: white;
    }
    
    .badge-capacidad {
        background-color: #3498db;
        color: white;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .action-buttons .btn-sm {
        padding: 3px 8px;
    }
    
    .empty-message {
        color: #6c757d;
        font-style: italic;
        padding: 10px;
        text-align: center;
    }
    
    .year-selector {
        background: white;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .form-container {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .required-field::after {
        content: '*';
        color: var(--danger-color);
        margin-left: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-sitemap me-2" style="color: var(--primary-color);"></i>
            Gestión Curricular
        </h4>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCurso" onclick="resetCursoForm()">
                <i class="fas fa-plus me-2"></i> Nuevo Curso
            </button>
        </div>
    </div>
    
    <!-- Selector de Año Académico -->
    <div class="year-selector">
        <div class="row align-items-center">
            <div class="col-md-3">
                <label class="form-label fw-bold mb-0">Año Académico:</label>
            </div>
            <div class="col-md-6">
                <select id="anioSelector" class="form-select">
                    @foreach($anios as $anio)
                        <option value="{{ $anio->id }}" {{ $anioActivo && $anioActivo->id == $anio->id ? 'selected' : '' }}>
                            {{ $anio->anio }} {{ $anio->activo ? '(Activo)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button id="btnCambiarAnio" class="btn btn-outline-primary w-100">
                    <i class="fas fa-calendar-alt me-2"></i> Cambiar Año
                </button>
            </div>
        </div>
    </div>
    
    <!-- Estructura Jerárquica -->
    <div class="tree-container" id="treeContainer">
        <div id="loadingSpinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
        <div id="treeContent">
            @include('cursos-jerarquico.partials.tree', ['niveles' => $data])
        </div>
    </div>
</div>

<!-- Modales -->
@include('cursos-jerarquico.modals.curso-modal')
@include('cursos-jerarquico.modals.competencia-modal')
@include('cursos-jerarquico.modals.capacidad-modal')

@endsection

@section('scripts')
<script>
// Variables globales
let currentAnioId = {{ $anioActivo ? $anioActivo->id : 'null' }};

$(document).ready(function() {
    // ========== FUNCIONES DE TOGGLE ==========
    window.toggleChildren = function(element) {
        let parent = $(element).closest('.tree-item');
        let children = parent.siblings('.tree-children');
        let icon = $(element).find('.toggle-icon');
        
        children.toggleClass('show');
        icon.toggleClass('rotated');
    };
    
    // ========== FUNCIONES PARA CURSOS ==========
    window.showCursoModal = function(cursoId, nivelId) {
        resetCursoForm();
        
        if (cursoId) {
            $('#modalCursoTitle').text('Editar Curso');
            $('#btnSaveCurso').html('<i class="fas fa-save me-2"></i> Actualizar');
            
            // 🔥 Cambiar a la ruta de CursoJerarquicoController
            $.ajax({
                url: '/admin/cursos-jerarquico/curso/' + cursoId,
                method: 'GET',
                success: function(response) {
                    console.log('Curso:', response);
                    $('#curso_id').val(response.id);
                    $('#curso_codigo').val(response.codigo);
                    $('#curso_nombre').val(response.nombre);
                    $('#curso_tipo').val(response.tipo);
                    $('#curso_horas').val(response.horas_semanales);
                    $('#curso_orden').val(response.orden);
                    $('#curso_descripcion').val(response.descripcion);
                    $('#curso_nivel_id').val(response.nivel_id);
                    $('#curso_anio_id').val(response.anio_academico_id);
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error', 'No se pudieron cargar los datos del curso', 'error');
                }
            });
        } else {
            $('#modalCursoTitle').text('Nuevo Curso');
            $('#btnSaveCurso').html('<i class="fas fa-save me-2"></i> Guardar');
            $('#curso_nivel_id').val(nivelId);
            $('#curso_anio_id').val(currentAnioId);
            $('#formCurso')[0].reset();
        }
        
        $('#modalCurso').modal('show');
    };
    
    window.resetCursoForm = function() {
        $('#formCurso')[0].reset();
        $('#curso_id').val('');
        $('.invalid-feedback').text('');
        $('.form-control, .form-select').removeClass('is-invalid');
    };
    
    window.deleteCurso = function(cursoId) {
        Swal.fire({
            title: '¿Eliminar curso?',
            text: 'Esta acción eliminará el curso y todas sus competencias y capacidades',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/cursos-jerarquico/curso/' + cursoId,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Eliminado', response.message, 'success');
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                    }
                });
            }
        });
    };
    
    // ========== FUNCIONES PARA COMPETENCIAS ==========
    window.showCompetenciaModal = function(competenciaId, cursoId) {
        resetCompetenciaForm();
        
        // Obtener nombre del curso desde el DOM
        let cursoNombre = $(`.tree-level-2[data-curso-id="${cursoId}"] .tree-title strong`).first().text();
        $('#competencia_curso_nombre').val(cursoNombre);
        $('#competencia_curso_id').val(cursoId);
        
        if (competenciaId) {
            $('#modalCompetenciaTitle').text('Editar Competencia');
            $('#btnSaveCompetencia').html('<i class="fas fa-save me-2"></i> Actualizar');
            
            // 🔥 Cambiar a la ruta de CursoJerarquicoController
            $.ajax({
                url: '/admin/cursos-jerarquico/competencia/' + competenciaId,
                method: 'GET',
                success: function(response) {
                    console.log('Competencia:', response);
                    $('#competencia_id').val(response.id);
                    $('#competencia_nombre').val(response.nombre);
                    $('#competencia_ponderacion').val(response.ponderacion);
                    $('#competencia_orden').val(response.orden);
                    $('#competencia_descripcion').val(response.descripcion);
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error', 'No se pudieron cargar los datos de la competencia', 'error');
                }
            });
        } else {
            $('#modalCompetenciaTitle').text('Nueva Competencia');
            $('#btnSaveCompetencia').html('<i class="fas fa-save me-2"></i> Guardar');
            $('#formCompetencia')[0].reset();
        }
        
        $('#modalCompetencia').modal('show');
    };
    
    window.resetCompetenciaForm = function() {
        $('#formCompetencia')[0].reset();
        $('#competencia_id').val('');
        $('.invalid-feedback').text('');
        $('.form-control, .form-select').removeClass('is-invalid');
    };
    
    window.deleteCompetencia = function(competenciaId) {
        Swal.fire({
            title: '¿Eliminar competencia?',
            text: 'Esta acción eliminará la competencia y todas sus capacidades',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/cursos-jerarquico/competencia/' + competenciaId,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Eliminado', response.message, 'success');
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                    }
                });
            }
        });
    };
    
    // ========== FUNCIONES PARA CAPACIDADES ==========
    window.showCapacidadModal = function(capacidadId, competenciaId) {
        resetCapacidadForm();
        
        // Obtener nombre de la competencia desde el DOM
        let competenciaNombre = $(`.tree-level-3[data-competencia-id="${competenciaId}"] .tree-title strong`).first().text();
        $('#capacidad_competencia_nombre').val(competenciaNombre);
        $('#capacidad_competencia_id').val(competenciaId);
        
        if (capacidadId) {
            $('#modalCapacidadTitle').text('Editar Capacidad');
            $('#btnSaveCapacidad').html('<i class="fas fa-save me-2"></i> Actualizar');
            
            // 🔥 Cambiar a la ruta de CursoJerarquicoController
            $.ajax({
                url: '/admin/cursos-jerarquico/capacidad/' + capacidadId,
                method: 'GET',
                success: function(response) {
                    console.log('Capacidad:', response);
                    $('#capacidad_id').val(response.id);
                    $('#capacidad_nombre').val(response.nombre);
                    $('#capacidad_ponderacion').val(response.ponderacion);
                    $('#capacidad_orden').val(response.orden);
                    $('#capacidad_descripcion').val(response.descripcion);
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error', 'No se pudieron cargar los datos de la capacidad', 'error');
                }
            });
        } else {
            $('#modalCapacidadTitle').text('Nueva Capacidad');
            $('#btnSaveCapacidad').html('<i class="fas fa-save me-2"></i> Guardar');
            $('#formCapacidad')[0].reset();
        }
        
        $('#modalCapacidad').modal('show');
    };
    
    window.resetCapacidadForm = function() {
        $('#formCapacidad')[0].reset();
        $('#capacidad_id').val('');
        $('.invalid-feedback').text('');
        $('.form-control, .form-select').removeClass('is-invalid');
    };
    
    window.deleteCapacidad = function(capacidadId) {
        Swal.fire({
            title: '¿Eliminar capacidad?',
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
                    url: '/admin/cursos-jerarquico/capacidad/' + capacidadId,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Eliminado', response.message, 'success');
                            location.reload();
                        }
                    }
                });
            }
        });
    };
    
    // ========== ENVÍO DE FORMULARIOS ==========
    $('#formCurso').on('submit', function(e) {
        e.preventDefault();
        let cursoId = $('#curso_id').val();
        let url = cursoId ? '/admin/cursos/' + cursoId : '{{ route("admin.cursos-jerarquico.store-curso") }}';
        let method = cursoId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize() + '&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    $('#modalCurso').modal('hide');
                    Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    for (let field in errors) {
                        $(`#curso_${field}_error`).text(errors[field][0]);
                        $(`#curso_${field}`).addClass('is-invalid');
                    }
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                }
            }
        });
    });
    
    $('#formCompetencia').on('submit', function(e) {
        e.preventDefault();
        let competenciaId = $('#competencia_id').val();
        let url = competenciaId ? '/admin/competencias/' + competenciaId : '{{ route("admin.cursos-jerarquico.store-competencia") }}';
        let method = competenciaId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize() + '&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    $('#modalCompetencia').modal('hide');
                    Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    for (let field in errors) {
                        $(`#competencia_${field}_error`).text(errors[field][0]);
                        $(`#competencia_${field}`).addClass('is-invalid');
                    }
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                }
            }
        });
    });
    
    $('#formCapacidad').on('submit', function(e) {
        e.preventDefault();
        let capacidadId = $('#capacidad_id').val();
        let url = capacidadId ? '/admin/capacidades/' + capacidadId : '{{ route("admin.cursos-jerarquico.store-capacidad") }}';
        let method = capacidadId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize() + '&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    $('#modalCapacidad').modal('hide');
                    Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    for (let field in errors) {
                        $(`#capacidad_${field}_error`).text(errors[field][0]);
                        $(`#capacidad_${field}`).addClass('is-invalid');
                    }
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                }
            }
        });
    });
    
    // ========== CAMBIAR AÑO ACADÉMICO ==========
    $('#btnCambiarAnio').on('click', function() {
        let anioId = $('#anioSelector').val();
        if (anioId == currentAnioId) return;
        
        currentAnioId = anioId;
        $('#loadingSpinner').show();
        $('#treeContent').hide();
        
        $.ajax({
            url: '{{ route("admin.cursos-jerarquico.change-year") }}',
            method: 'POST',
            data: { anio_id: anioId, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success && response.html) {
                    $('#treeContent').html(response.html);
                } else if (response.success && response.data) {
                    // Si viene data en lugar de html, renderizar
                    renderTreeFromData(response.data);
                }
                bindTreeEvents();
            },
            error: function(xhr) {
                console.error(xhr);
                Swal.fire('Error', 'Error al cargar los datos', 'error');
            },
            complete: function() {
                $('#loadingSpinner').hide();
                $('#treeContent').show();
            }
        });
    });
    
    // Función para renderizar árbol desde datos JSON
    function renderTreeFromData(data) {
        let html = '';
        for (let nivel of data) {
            html += renderNivel(nivel);
        }
        $('#treeContent').html(html);
    }
    
    function renderNivel(nivel) {
        let cursosHtml = '';
        if (nivel.cursos && nivel.cursos.length > 0) {
            for (let curso of nivel.cursos) {
                cursosHtml += renderCurso(curso);
            }
        } else {
            cursosHtml = '<div class="empty-message">No hay cursos registrados para este nivel</div>';
        }
        
        return `
            <div class="tree-level-1" data-nivel-id="${nivel.id}">
                <div class="tree-item">
                    <div class="tree-header" onclick="toggleChildren(this)">
                        <div class="tree-title">
                            <span class="toggle-icon">▶</span>
                            <i class="fas fa-layer-group" style="color: #2c5031;"></i>
                            <strong>${escapeHtml(nivel.nombre)}</strong>
                            <span class="badge bg-secondary">${nivel.cursos ? nivel.cursos.length : 0} cursos</span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showCursoModal(null, ${nivel.id})">
                                <i class="fas fa-plus"></i> Curso
                            </button>
                        </div>
                    </div>
                    <div class="tree-children">
                        ${cursosHtml}
                    </div>
                </div>
            </div>
        `;
    }
    
    function renderCurso(curso) {
        let competenciasHtml = '';
        if (curso.competencias && curso.competencias.length > 0) {
            for (let competencia of curso.competencias) {
                competenciasHtml += renderCompetencia(competencia);
            }
        } else {
            competenciasHtml = '<div class="empty-message">No hay competencias registradas</div>';
        }
        
        return `
            <div class="tree-level-2" data-curso-id="${curso.id}">
                <div class="tree-item">
                    <div class="tree-header" onclick="toggleChildren(this)">
                        <div class="tree-title">
                            <span class="toggle-icon">▶</span>
                            <i class="fas fa-book" style="color: #c8a951;"></i>
                            <strong>${escapeHtml(curso.nombre)}</strong>
                            <span class="badge badge-curso">${escapeHtml(curso.codigo)}</span>
                            <span class="badge bg-info">${curso.horas_semanales} h/sem</span>
                            <span class="badge bg-secondary">${curso.competencias ? curso.competencias.length : 0} competencias</span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); showCursoModal(${curso.id}, ${curso.nivel_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showCompetenciaModal(null, ${curso.id})">
                                <i class="fas fa-plus"></i> Competencia
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteCurso(${curso.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="tree-children">
                        ${competenciasHtml}
                    </div>
                </div>
            </div>
        `;
    }
    
    function renderCompetencia(competencia) {
        let capacidadesHtml = '';
        if (competencia.capacidades && competencia.capacidades.length > 0) {
            for (let capacidad of competencia.capacidades) {
                capacidadesHtml += renderCapacidad(capacidad);
            }
        } else {
            capacidadesHtml = '<div class="empty-message">No hay capacidades registradas</div>';
        }
        
        return `
            <div class="tree-level-3" data-competencia-id="${competencia.id}">
                <div class="tree-item">
                    <div class="tree-header" onclick="toggleChildren(this)">
                        <div class="tree-title">
                            <span class="toggle-icon">▶</span>
                            <i class="fas fa-star" style="color: #3498db;"></i>
                            <strong>${escapeHtml(competencia.nombre)}</strong>
                            <span class="badge badge-competencia">${competencia.ponderacion}%</span>
                            <span class="badge bg-secondary">${competencia.capacidades ? competencia.capacidades.length : 0} capacidades</span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); showCompetenciaModal(${competencia.id}, ${competencia.curso_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showCapacidadModal(null, ${competencia.id})">
                                <i class="fas fa-plus"></i> Capacidad
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteCompetencia(${competencia.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="tree-children">
                        ${capacidadesHtml}
                    </div>
                </div>
            </div>
        `;
    }
    
    function renderCapacidad(capacidad) {
        return `
            <div class="tree-level-3" style="margin-left: 30px;" data-capacidad-id="${capacidad.id}">
                <div class="tree-item" style="background: #e8f0fe;">
                    <div class="tree-header">
                        <div class="tree-title">
                            <i class="fas fa-tasks" style="color: #2c5031;"></i>
                            <span>${escapeHtml(capacidad.nombre)}</span>
                            <span class="badge badge-capacidad">${capacidad.ponderacion}%</span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-warning" onclick="showCapacidadModal(${capacidad.id}, ${capacidad.competencia_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCapacidad(${capacidad.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    function bindTreeEvents() {
        $('.tree-level-1 .toggle-icon').addClass('rotated');
        $('.tree-level-1 .tree-children').addClass('show');
    }
    
    // Inicializar eventos
    bindTreeEvents();
});
</script>
@endsection