{{-- resources/views/carga-horaria/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Carga Horaria')

@section('css')
<style>
    .form-container {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 42px;
    }
    
    /* Estilos para las tarjetas de cursos */
    .cursos-container {
        display: flex;
        gap: 20px;
        margin-top: 20px;
        min-height: 400px;
    }
    
    .cursos-columna {
        flex: 1;
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px;
        border: 1px solid #e9ecef;
    }
    
    .cursos-columna h6 {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .cursos-lista {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .curso-card {
        background: white;
        border-radius: 10px;
        padding: 12px 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
        border: 1px solid #e9ecef;
        position: relative;
    }

        .curso-card:not(.asignado) {
            cursor: pointer;
        }
    
    .curso-card:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .curso-card.asignado {
        background: #e8f5e9;
        border-left: 4px solid #28a745;
        cursor: not-allowed;
        opacity: 0.8;
    }
    
    .curso-card.asignado:hover {
        transform: none;
    }
    
    .curso-card.seleccionado {
        background: #e3f2fd;
        border: 2px solid #2196f3;
        box-shadow: 0 4px 12px rgba(33,150,243,0.2);
    }
    
    .curso-nombre {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .curso-info {
        font-size: 11px;
        color: #6c757d;
    }
    
    .badge-asignado {
        position: absolute;
        top: 10px;
        /* ubicar el badge hacia la mitad del lado derecho (antes del botón X) */
        right: 44px;
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
    }
    
    .badge-disponible {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #17a2b8;
        color: white;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
    }
    
    .aula-selector {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .empty-cursos {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .flecha-icono {
        font-size: 24px;
        color: #6c757d;
    }
    
    .cola-item {
        background: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 10px 12px;
        margin-bottom: 8px;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
@include('partials.toast')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-plus-circle me-2" style="color: var(--primary-color);"></i>
            Nueva Asignación de Curso
        </h4>
        <a href="{{ route('admin.carga-horaria.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form id="cargaHorariaForm">
            @csrf
            
            <!-- Selección de Docente -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-chalkboard-user me-2 text-primary"></i> 1. Seleccionar Docente
                </div>
                <div class="card-body">
                    <select class="form-select select2" id="docente_id" name="docente_id" required style="width: 100%;">
                        <option value="">-- Buscar docente --</option>
                        @foreach($docentes as $docente)
                            <option value="{{ $docente->id }}">
                                {{ $docente->name }} - {{ $docente->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Cursos Asignados vs No Asignados -->
            <div class="card mb-4 border-0 shadow-sm" id="cursosSection" style="display: none;">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-book me-2 text-primary"></i> 2. Cursos del Docente
                </div>
                <div class="card-body">
                    <!-- Selector de aula ahora ocupa todo el ancho sobre las columnas de cursos -->
                    <div id="aulaSelectorContainer" style="display: none; margin-bottom: 18px;">
                        <div class="aula-selector">
                            <div class="row align-items-center">
                                <div class="col-12 mb-2">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Aula seleccionada:</strong>
                                        <span id="aulaSeleccionadaNombre"></span>
                                        <input type="hidden" id="aulaSeleccionadaId" name="aula_id">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <label for="aula_id" class="form-label fw-semibold">
                                        <i class="fas fa-door-open me-1"></i> Seleccionar Aula:
                                    </label>
                                    <select class="form-select" id="aula_id" name="aula_id" required>
                                        <option value="">-- Primero seleccione un curso --</option>
                                    </select>
                                    <small class="text-muted">Las aulas disponibles dependen del nivel del curso</small>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" id="btnGuardarCursos" class="btn btn-primary w-100" onclick="guardarCursosSeleccionados()" disabled>
                                        <i class="fas fa-arrow-right me-2"></i> Guardar cursos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cursos-container">
                        <!-- Columna: Cursos No Asignados (Disponibles) -->
                        <div class="cursos-columna">
                            <h6 class="text-center">
                                <i class="fas fa-clock text-warning me-1"></i> 
                                Cursos Disponibles
                                <span class="badge bg-secondary ms-2" id="disponiblesCount">0</span>
                            </h6>
                                <!-- Búsqueda de cursos disponibles -->
                                <input type="text" class="form-control mb-2" id="buscarCursosDisponibles" placeholder="🔍 Buscar..." style="padding: 8px; font-size: 13px;">
                            <div class="cursos-lista" id="cursosDisponiblesList">
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando cursos...
                                </div>
                            </div>
                        </div>
                        
                        <!-- Flecha decorativa (opcional) -->
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <button type="button" id="btnGuardarCursosFlecha" class="btn btn-primary d-flex align-items-center" onclick="guardarCursosSeleccionados()" disabled>
                                <i class="fas fa-arrow-right flecha-icono me-2"></i>
                                Guardar cursos
                            </button>
                        </div>
                        
                        <!-- Columna: Cursos Ya Asignados -->
                        <div class="cursos-columna">
                            <h6 class="text-center">
                                <i class="fas fa-check-circle text-success me-1"></i> 
                                Cursos Asignados
                                <span class="badge bg-success ms-2" id="asignadosCount">0</span>
                            </h6>
                                <!-- Búsqueda de cursos asignados -->
                                <input type="text" class="form-control mb-2" id="buscarCursosAsignados" placeholder="🔍 Buscar..." style="padding: 8px; font-size: 13px;">
                            <div class="cursos-lista" id="cursosAsignadosList">
                                <div class="text-center py-4 text-muted">
                                    Seleccione un docente para ver sus cursos
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información del curso seleccionado y selector de aula (moved above) -->
                </div>
            </div>
            
            <!-- Datos Adicionales (Opcional) -->
            <div class="card mb-4 border-0 shadow-sm" id="datosAdicionales" style="display: none;">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-clock me-2 text-muted"></i> 3. Datos Adicionales <span class="text-muted fw-normal">(Opcional)</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="horas_semanales" class="form-label">Horas Semanales</label>
                            <input type="number" class="form-control" id="horas_semanales" name="horas_semanales" 
                                   min="1" max="40" placeholder="Ej: 4" value="4">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="dia_semana" class="form-label">Día (opcional)</label>
                            <select class="form-select" id="dia_semana" name="dia_semana">
                                <option value="">-- Sin día específico --</option>
                                @foreach($diasSemana as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="hora_inicio" class="form-label">Hora inicio</label>
                            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" step="300">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="hora_fin" class="form-label">Hora fin</label>
                            <input type="time" class="form-control" id="hora_fin" name="hora_fin" step="300">
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2" 
                                  placeholder="Información adicional sobre la asignación..."></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="d-flex justify-content-end gap-2 mt-3" id="botonesAccion" style="display: none;">
                <button type="button" class="btn btn-secondary px-4" onclick="limpiarFormulario()">
                    <i class="fas fa-redo me-2"></i> Limpiar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cola de asignaciones removida - guardado directo en BD -->
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let aulaSeleccionadaId = null;
let aulaSeleccionadaNivelId = null;
let selectedCourseIds = [];
let todosLosCursos = [];
let cursosAsignados = [];

$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Buscar...',
        allowClear: true
    });
    
    // todosLosCursos and cursosAsignados are declared globally above
    
    // Búsqueda de cursos disponibles
    $('#buscarCursosDisponibles').on('keyup', function() {
        let textoBusqueda = $(this).val().toLowerCase();
        $('.curso-card:not(.asignado)').each(function() {
            let nombreCurso = $(this).data('curso-nombre').toLowerCase();
            let nivelCurso = $(this).data('curso-nivel').toLowerCase();
            if (nombreCurso.includes(textoBusqueda) || nivelCurso.includes(textoBusqueda)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Búsqueda de cursos asignados
    $('#buscarCursosAsignados').on('keyup', function() {
        let texto = $(this).val().toLowerCase();
        $('.curso-card.asignado').each(function() {
            // Buscar en el nombre y en la info (nivel/aula)
            let nombre = $(this).find('.curso-nombre').text().toLowerCase();
            let info = $(this).find('.curso-info').text().toLowerCase();
            if (nombre.includes(texto) || info.includes(texto)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Cuando se selecciona un docente
    $('#docente_id').on('change', function() {
        let docenteId = $(this).val();
        
        if (docenteId) {
            mostrarLoading(true);
            
            // Cargar todos los cursos disponibles
            $.ajax({
                url: '{{ route("admin.carga-horaria.todos-cursos") }}',
                method: 'GET',
                success: function(response) {
                    if (Array.isArray(response)) {
                        todosLosCursos = response;
                    } else if (response && Array.isArray(response.data)) {
                        todosLosCursos = response.data;
                    } else {
                        console.error('Respuesta inesperada en todos-cursos:', response);
                            todosLosCursos = [];
                            toast.error('Error al cargar los cursos disponibles');
                        mostrarLoading(false);
                        return;
                    }
                    console.log('Cursos totales cargados:', todosLosCursos.length);
                        cargarAulasDisponibles();
                    cargarCursosDocente(docenteId);
                },
                error: function(xhr) {
                    console.error('Error en todos-cursos:', xhr);
                    toast.error('Error al cargar los cursos');
                    mostrarLoading(false);
                }
            });
        } else {
            $('#cursosSection').hide();
            $('#datosAdicionales').hide();
            $('#botonesAccion').hide();
            $('#aulaSelectorContainer').hide();
        }
    });

    $('#aula_id').on('change', function() {
        aulaSeleccionadaId = $(this).val() || null;
        aulaSeleccionadaNivelId = $(this).find('option:selected').data('nivel-id') || null;
        let aulaNombre = $(this).find('option:selected').text() || '';
        if (aulaSeleccionadaId) {
            $('#aulaSeleccionadaNombre').text(aulaNombre);
            selectedCourseIds = [];
            actualizarCursosDisponibles();
            renderizarCursos();
            $('#btnGuardarCursos').prop('disabled', true);
            $('#btnGuardarCursosFlecha').prop('disabled', true);
        } else {
            $('#aulaSeleccionadaNombre').text('');
            $('#cursosDisponiblesList').html(`
                <div class="empty-cursos">
                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                    Seleccione un aula para ver los cursos disponibles
                </div>
            `);
            $('#btnGuardarCursos').prop('disabled', true);
            $('#btnGuardarCursosFlecha').prop('disabled', true);
        }
    });

    
    function cargarCursosDocente(docenteId) {
        $.ajax({
            url: '{{ route("admin.carga-horaria.cursos-by-docente") }}',
            method: 'GET',
            data: { docente_id: docenteId },
            dataType: 'json',
            success: function(response) {
                // Normalizar distintos formatos de respuesta para cursos asignados
                let raw = null;
                if (Array.isArray(response)) raw = response;
                else if (response && Array.isArray(response.data)) raw = response.data;
                else if (response && Array.isArray(response.cursos)) raw = response.cursos;
                else if (response && Array.isArray(response.asignaciones)) raw = response.asignaciones;
                else raw = [];

                console.log('RAW cursos-asignados:', raw);

                // Normalizar cada elemento a { id, nombre, nivel, aula }
                cursosAsignados = raw.map(item => {
                    // Caso: el elemento ya es un curso
                    if (item && (item.id || item.curso_id) && (item.nombre || item.name || item.titulo || item.curso_nombre)) {
                        return {
                            id: item.id || item.curso_id,
                            carga_id: item.carga_id || item.asignacion_id || item.id || null,
                            aula_id: item.aula_id || item.aulaId || null,
                            nombre: item.nombre || item.name || item.titulo || item.curso_nombre,
                            nivel: item.nivel || item.nivel_nombre || item.nivelId || '',
                            aula: item.aula || item.aula_nombre || item.aula_id || ''
                        };
                    }

                    // Caso: elemento es una asignación que contiene objeto `curso` o `curso_data`
                    if (item && (item.curso || item.curso_data || item.cursoObj)) {
                        let c = item.curso || item.curso_data || item.cursoObj;
                        return {
                            id: c.id || c.curso_id || '',
                            carga_id: item.id || item.carga_id || null,
                            aula_id: item.aula_id || item.aulaId || item.aula?.id || null,
                            nombre: c.nombre || c.name || c.titulo || '',
                            nivel: c.nivel || c.nivel_nombre || '',
                            aula: item.aula || item.aula_nombre || (c.aula ? (c.aula.nombre || c.aula) : '')
                        };
                    }

                    // Caso genérico: intentar extraer campos conocidos
                    return {
                        id: item.id || item.curso_id || '',
                        carga_id: item.carga_id || item.id || null,
                        aula_id: item.aula_id || item.aulaId || null,
                        nombre: item.nombre || item.name || item.titulo || item.curso_nombre || 'Sin nombre',
                        nivel: item.nivel || item.nivel_nombre || '',
                        aula: item.aula || item.aula_nombre || ''
                    };
                });

                console.log('Cursos asignados procesados:', cursosAsignados.length);
                renderizarCursos();
                $('#cursosSection').show();
                mostrarLoading(false);
            },
            error: function(xhr) {
                console.error('Error en la petición:', xhr);
                let errorMsg = xhr.responseJSON?.message || 'Error al cargar cursos del docente';
                toast.error(errorMsg);
                $('#cursosAsignadosList').html(`
                    <div class="empty-cursos text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                        Error al cargar cursos asignados
                        <br><small class="text-muted">${errorMsg}</small>
                    </div>
                `);
                mostrarLoading(false);
            }
        });
    }

    function cargarAulasDisponibles() {
        $.ajax({
            url: '{{ route("admin.carga-horaria.aulas-disponibles") }}',
            method: 'GET',
            success: function(response) {
                let aulas = Array.isArray(response) ? response : (response?.data || []);
                let aulaSelect = $('#aula_id');
                aulaSelect.html('<option value="">-- Seleccionar aula --</option>');
                if (aulas.length > 0) {
                    for (let aula of aulas) {
                        aulaSelect.append(`<option value="${aula.id}" data-nivel-id="${aula.nivel_id || ''}" data-nivel-nombre="${aula.nivel_nombre || ''}">
                            ${aula.nombre} - ${aula.grado || ''} "${aula.seccion || ''}" (${aula.turno_nombre || aula.turno})
                        </option>`);
                    }
                } else {
                    aulaSelect.html('<option value="">No hay aulas disponibles</option>');
                }
                $('#aulaSelectorContainer').show();
                $('#datosAdicionales').show();
                $('#botonesAccion').show();
                $('#cursosDisponiblesList').html(`
                    <div class="empty-cursos">
                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                        Seleccione un aula para ver los cursos disponibles
                    </div>
                `);
                $('#btnGuardarCursos').prop('disabled', true);
                $('#btnGuardarCursosFlecha').prop('disabled', true);
            },
            error: function(xhr) {
                console.error('Error al cargar aulas disponibles:', xhr);
                $('#cursosDisponiblesList').html(`
                    <div class="empty-cursos text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                        Error al cargar las aulas disponibles
                    </div>
                `);
            }
        });
    }
    
    // Listener para resetear búsqueda cuando se carga docente
    $('#docente_id').on('change', function() {
        $('#buscarCursosDisponibles').val('');
        $('#buscarCursosAsignados').val('');
    });
});

// ========== FUNCIONES GLOBALES (Fuera del document.ready) ==========

function renderizarCursos() {
    // Filtrar cursos asignados por aula seleccionada, si existe
    let cursosAsignadosFiltrados = aulaSeleccionadaId
        ? cursosAsignados.filter(c => String(c.aula_id) === String(aulaSeleccionadaId))
        : cursosAsignados;
    // IDs de cursos ya asignados (soportar id o curso_id)
    let idsAsignados = cursosAsignadosFiltrados.map(c => c.id || c.curso_id);
    // Actualizar contadores
    $('#asignadosCount').text(cursosAsignadosFiltrados.length);
    $('#disponiblesCount').text(0);
    
    // Renderizar cursos asignados
    if (cursosAsignadosFiltrados.length > 0) {
        let htmlAsignados = '';
        for (let curso of cursosAsignadosFiltrados) {
            let cursoNombre = curso.nombre || curso.name || curso.titulo || 'Sin nombre';
            let cursoNivel = curso.nivel || curso.nivel_nombre || 'Sin nivel';
            let cursoAula = curso.aula || curso.aula_nombre || 'No asignada';
            let cursoId = curso.id || curso.curso_id || '';
            let cargaId = curso.carga_id || curso.cargaId || curso.asignacion_id || curso.asignacionId || '';
            htmlAsignados += `
                <div class="curso-card asignado" data-curso-id="${cursoId}" data-carga-id="${cargaId}" style="position: relative;">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarCursoAsignado(${cargaId})" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="curso-nombre">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        ${cursoNombre}
                    </div>
                    <div class="curso-info">
                            <i class="fas fa-layer-group me-1"></i> ${cursoNivel}
                            ${curso.seccion ? `<span class="badge bg-info ms-2">${curso.seccion}</span>` : ''}<br>
                            <i class="fas fa-door-open me-1"></i> Aula: ${cursoAula}
                    </div>
                    <span class="badge-asignado">Asignado</span>
                </div>
            `;
        }
        $('#cursosAsignadosList').html(htmlAsignados);
    } else {
        $('#cursosAsignadosList').html(`
            <div class="empty-cursos">
                <i class="fas fa-smile-wink fa-2x mb-2 d-block"></i>
                ${aulaSeleccionadaId ? 'Este docente no tiene cursos asignados en esta aula' : 'Este docente aún no tiene cursos asignados'}
            </div>
        `);
    }
    $('#cursosDisponiblesList').html(`
        <div class="empty-cursos">
            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
            Seleccione un aula para ver los cursos disponibles
        </div>
    `);

    if (aulaSeleccionadaId) {
        actualizarCursosDisponibles();
    }
}

function actualizarCursosDisponibles() {
    if (!aulaSeleccionadaId) {
        $('#cursosDisponiblesList').html(`
            <div class="empty-cursos">
                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                Seleccione un aula para ver los cursos disponibles
            </div>
        `);
        $('#btnGuardarCursos').prop('disabled', true);
        $('#btnGuardarCursosFlecha').prop('disabled', true);
        return;
    }

        let idsAsignados = cursosAsignados
            .filter(c => !aulaSeleccionadaId || String(c.aula_id) === String(aulaSeleccionadaId))
            .map(c => String(c.id || c.curso_id));
    let cursosFiltrados = todosLosCursos.filter(curso => {
        let nivelCoincide = aulaSeleccionadaNivelId ? curso.nivel_id == aulaSeleccionadaNivelId : true;
        return nivelCoincide && !idsAsignados.includes(String(curso.id));
    });

    $('#disponiblesCount').text(cursosFiltrados.length);
    renderizarCursosDisponibles(cursosFiltrados);
}

function renderizarCursosDisponibles(cursos) {
    if (cursos.length === 0) {
        $('#cursosDisponiblesList').html(`
            <div class="empty-cursos">
                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                No hay cursos disponibles para el nivel del aula seleccionada
            </div>
        `);
        $('#btnGuardarCursos').prop('disabled', true);
        $('#btnGuardarCursosFlecha').prop('disabled', true);
        return;
    }

    let htmlDisponibles = '';
    for (let curso of cursos) {
        let cursoNombre = curso.nombre || curso.name || curso.titulo || 'Sin nombre';
        let cursoNivel = curso.nivel || curso.nivel_nombre || '';
        let cursoId = String(curso.id || curso.curso_id || '');
        let seleccionado = selectedCourseIds.includes(cursoId) ? 'seleccionado' : '';
        htmlDisponibles += `
            <div class="curso-card ${seleccionado}" 
                 data-curso-id="${cursoId}"
                 data-curso-nombre="${cursoNombre}"
                 data-curso-nivel="${cursoNivel}">
                <div class="curso-nombre">
                    <i class="fas fa-book text-primary me-1"></i>
                    ${cursoNombre}
                </div>
                <div class="curso-info">
                    <i class="fas fa-layer-group me-1"></i> ${cursoNivel || 'Sin nivel'}
                </div>
                <span class="badge-disponible">Disponible</span>
            </div>
        `;
    }
    $('#cursosDisponiblesList').html(htmlDisponibles);

    $('.curso-card:not(.asignado)').on('click', function() {
        let cursoId = String($(this).data('curso-id'));
        if (!cursoId) {
            return;
        }
        if (selectedCourseIds.includes(cursoId)) {
            selectedCourseIds = selectedCourseIds.filter(id => id !== cursoId);
            $(this).removeClass('seleccionado');
        } else {
            selectedCourseIds.push(cursoId);
            $(this).addClass('seleccionado');
        }
        let activo = selectedCourseIds.length > 0;
        $('#btnGuardarCursos').prop('disabled', !activo);
        $('#btnGuardarCursosFlecha').prop('disabled', !activo);
    });
}

function mostrarLoading(mostrar) {
    if (mostrar) {
        $('#cursosDisponiblesList').html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Cargando cursos...</p>
            </div>
        `);
        $('#cursosAsignadosList').html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Cargando asignaciones...</p>
            </div>
        `);
    }
}

function guardarCursosSeleccionados() {
    let docenteId = $('#docente_id').val();
    let aulaId = $('#aula_id').val();
    let horasSem = $('#horas_semanales').val();
    let diaSemana = $('#dia_semana').val();
    let horaInicio = $('#hora_inicio').val();
    let horaFin = $('#hora_fin').val();
    let observaciones = $('#observaciones').val();

    if (!docenteId || !aulaId) {
        toast.error('Seleccione docente y aula antes de guardar');
        return;
    }
    if (selectedCourseIds.length === 0) {
        toast.error('Seleccione al menos un curso disponible');
        return;
    }

    let btn = $('#btnGuardarCursos');
    let btnFlecha = $('#btnGuardarCursosFlecha');
    btn.prop('disabled', true);
    btnFlecha.prop('disabled', true);
    btn.html('<span class="loading-spinner me-2"></span> Guardando...');
    btnFlecha.html('<span class="loading-spinner me-2"></span> Guardar cursos');

    let asignaciones = selectedCourseIds.map(cursoId => ({
        docente_id: docenteId,
        curso_id: cursoId,
        aula_id: aulaId,
        horas_semanales: horasSem,
        dia_semana: diaSemana,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
        observaciones: observaciones
    }));

    $.ajax({
        url: '{{ route("admin.carga-horaria.store") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            asignaciones: JSON.stringify(asignaciones)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toast.success(response.message || 'Asignaciones guardadas exitosamente');
                
                // Resetear estado local inmediatamente
                selectedCourseIds = [];
                $('#buscarCursosDisponibles').val('');
                $('#buscarCursosAsignados').val('');
                
                // Restaurar botones
                $('#btnGuardarCursos').html('<i class="fas fa-arrow-right me-2"></i> Guardar cursos');
                $('#btnGuardarCursosFlecha').html('<i class="fas fa-arrow-right me-2"></i> Guardar cursos');
                $('#btnGuardarCursos').prop('disabled', true);
                $('#btnGuardarCursosFlecha').prop('disabled', true);
                
                // Recargar datos del servidor (esto ocurre en paralelo, sin esperar)
                recargarCursosAsignados(docenteId);
            } else {
                toast.error(response.message || 'Error al guardar las asignaciones');
                $('#btnGuardarCursos').prop('disabled', false);
                $('#btnGuardarCursosFlecha').prop('disabled', false);
                $('#btnGuardarCursos').html('<i class="fas fa-arrow-right me-2"></i> Guardar cursos');
                $('#btnGuardarCursosFlecha').html('<i class="fas fa-arrow-right me-2"></i> Guardar cursos');
            }
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON?.message || 'Error al guardar las asignaciones';
            toast.error(errorMsg);
            $('#btnGuardarCursos').prop('disabled', false);
            $('#btnGuardarCursosFlecha').prop('disabled', false);
            $('#btnGuardarCursos').html('<i class="fas fa-arrow-right me-2"></i> Guardar cursos');
            $('#btnGuardarCursosFlecha').html('<i class="fas fa-arrow-right me-2"></i> Guardar cursos');
        }
    });
}

function recargarCursosAsignados(docenteId) {
    $.ajax({
        url: '{{ route("admin.carga-horaria.cursos-by-docente") }}',
        method: 'GET',
        data: { docente_id: docenteId },
        dataType: 'json',
        success: function(response) {
            let raw = null;
            if (Array.isArray(response)) raw = response;
            else if (response && Array.isArray(response.data)) raw = response.data;
            else if (response && Array.isArray(response.cursos)) raw = response.cursos;
            else if (response && Array.isArray(response.asignaciones)) raw = response.asignaciones;
            else raw = [];
            
            cursosAsignados = raw.map(item => {
                if (item && (item.id || item.curso_id) && (item.nombre || item.name || item.titulo || item.curso_nombre)) {
                    return {
                        id: item.id || item.curso_id,
                        carga_id: item.carga_id || item.asignacion_id || item.id || null,
                        aula_id: item.aula_id || item.aulaId || null,
                        seccion: item.seccion || item.aula_seccion || item.aula || '',
                        nombre: item.nombre || item.name || item.titulo || item.curso_nombre,
                        nivel: item.nivel || item.nivel_nombre || item.nivelId || '',
                        aula: item.aula || item.aula_nombre || item.aula_id || ''
                    };
                }
                if (item && (item.curso || item.curso_data || item.cursoObj)) {
                    let c = item.curso || item.curso_data || item.cursoObj;
                    return {
                        id: c.id || c.curso_id || '',
                        carga_id: item.id || item.carga_id || item.asignacion_id || null,
                        aula_id: item.aula_id || item.aulaId || item.aula?.id || null,
                        seccion: item.seccion || item.aula_seccion || item.aula || '',
                        nombre: c.nombre || c.name || c.titulo || '',
                        nivel: c.nivel || c.nivel_nombre || '',
                        aula: item.aula || item.aula_nombre || (c.aula ? (c.aula.nombre || c.aula) : '')
                    };
                }
                return {
                    id: item.id || item.curso_id || '',
                    carga_id: item.carga_id || item.asignacion_id || null,
                    aula_id: item.aula_id || item.aulaId || null,
                    seccion: item.seccion || item.aula_seccion || item.aula || '',
                    nombre: item.nombre || item.name || item.titulo || item.curso_nombre || 'Sin nombre',
                    nivel: item.nivel || item.nivel_nombre || '',
                    aula: item.aula || item.aula_nombre || ''
                };
            });
            
            console.log('Cursos asignados recargados:', cursosAsignados.length);
            renderizarCursos();
            if (aulaSeleccionadaId) {
                actualizarCursosDisponibles();
            }
            // Resetear búsqueda
            $('#buscarCursosAsignados').val('').trigger('keyup');
            selectedCourseIds = [];
        },
        error: function(xhr) {
            console.error('Error al recargar cursos asignados:', xhr);
        }
    });
}

function eliminarCursoAsignado(cargaId) {
    Swal.fire({
        title: '¿Eliminar asignación?',
        text: 'Esta acción no se puede deshacer. Si el curso tiene datos enlazados, no se podrá eliminar.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/carga-horaria/' + cargaId,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toast.success('La asignación ha sido eliminada correctamente');
                        let docenteId = $('#docente_id').val();
                        
                        // Recargar cursos asignados
                        recargarCursosAsignados(docenteId);
                        
                        // Actualizar cursos disponibles si hay aula seleccionada
                        if (aulaSeleccionadaId) {
                            actualizarCursosDisponibles();
                        }
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'No se puede eliminar: el curso tiene datos enlazados o ya está en uso';
                    toast.error(errorMsg);
                }
            });
        }
    });
}

function limpiarFormulario() {
    aulaSeleccionadaId = null;
    aulaSeleccionadaNivelId = null;
    selectedCourseIds = [];
    $('#aulaSeleccionadaId').val('');
    $('#aula_id').html('<option value="">-- Seleccionar aula --</option>').prop('disabled', true);
    $('#horas_semanales').val('4');
    $('#dia_semana').val('');
    $('#hora_inicio').val('');
    $('#hora_fin').val('');
    $('#observaciones').val('');
    $('#aulaSelectorContainer').hide();
    $('#datosAdicionales').hide();
    $('#botonesAccion').hide();
    let btn = $('#btnGuardarCursos');
    let btnFlecha = $('#btnGuardarCursosFlecha');
    btn.prop('disabled', true);
    btnFlecha.prop('disabled', true);
    btn.html('<i class="fas fa-arrow-right me-2"></i> Guardar cursos');
    btnFlecha.html('<i class="fas fa-arrow-right me-2"></i> Guardar cursos');
}
</script>
@endsection
