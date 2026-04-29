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
                                        <strong>Curso seleccionado:</strong>
                                        <span id="cursoSeleccionadoNombre"></span>
                                        <input type="hidden" id="cursoSeleccionadoId" name="curso_id">
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
                                    <button type="button" id="btnAgregar" class="btn btn-success w-100" onclick="agregarAsignacion()" disabled>
                                        <i class="fas fa-plus-circle me-2"></i> Agregar Curso
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
                        <div class="d-none d-md-flex align-items-center">
                            <i class="fas fa-arrow-right flecha-icono"></i>
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
let cursoSeleccionadoId = null;

$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Buscar...',
        allowClear: true
    });
    
    let todosLosCursos = [];
    let cursosAsignados = [];
    
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
                        Swal.fire('Error', 'Error al cargar los cursos disponibles', 'error');
                        mostrarLoading(false);
                        return;
                    }
                    console.log('Cursos totales cargados:', todosLosCursos.length);
                    cargarCursosDocente(docenteId);
                },
                error: function(xhr) {
                    console.error('Error en todos-cursos:', xhr);
                    Swal.fire('Error', 'Error al cargar los cursos', 'error');
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

    
    function cargarCursosDocente(docenteId) {
        $.ajax({
            url: '{{ route("admin.carga-horaria.cursos-by-docente") }}',
            method: 'GET',
            data: { docente_id: docenteId },
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
                            nombre: c.nombre || c.name || c.titulo || '',
                            nivel: c.nivel || c.nivel_nombre || '',
                            aula: item.aula || item.aula_nombre || (c.aula ? (c.aula.nombre || c.aula) : '')
                        };
                    }

                    // Caso genérico: intentar extraer campos conocidos
                    return {
                        id: item.id || item.curso_id || '',
                        carga_id: item.carga_id || item.id || null,
                        nombre: item.nombre || item.name || item.titulo || item.curso_nombre || 'Sin nombre',
                        nivel: item.nivel || item.nivel_nombre || '',
                        aula: item.aula || item.aula_nombre || ''
                    };
                });

                renderizarCursos();
                $('#cursosSection').show();
                mostrarLoading(false);
            },
            error: function(xhr) {
                console.error('Error en la petición:', xhr);
                let errorMsg = xhr.responseJSON?.message || 'Error al cargar cursos del docente';
                Swal.fire('Error', errorMsg, 'error');
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
    
    function renderizarCursos() {
        // IDs de cursos ya asignados (soportar id o curso_id)
        let idsAsignados = cursosAsignados.map(c => c.id || c.curso_id);
        
        // Cursos no asignados
        let cursosNoAsignados = todosLosCursos.filter(curso => !idsAsignados.includes(curso.id));
        
        // Actualizar contadores
        $('#asignadosCount').text(cursosAsignados.length);
        $('#disponiblesCount').text(cursosNoAsignados.length);
        
        // Renderizar cursos asignados
        if (cursosAsignados.length > 0) {
            let htmlAsignados = '';
            for (let curso of cursosAsignados) {
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
                            <i class="fas fa-layer-group me-1"></i> ${cursoNivel}<br>
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
                    Este docente aún no tiene cursos asignados
                </div>
            `);
        }
        
        // Renderizar cursos disponibles
        if (cursosNoAsignados.length > 0) {
            let htmlDisponibles = '';
            for (let curso of cursosNoAsignados) {
                let cursoNombre = curso.nombre || curso.name || curso.titulo || 'Sin nombre';
                let cursoNivel = curso.nivel || curso.nivel_nombre || '';
                let cursoId = curso.id || curso.curso_id || '';
                htmlDisponibles += `
                    <div class="curso-card ${cursoSeleccionadoId == cursoId ? 'seleccionado' : ''}" 
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
            
            // Agregar evento click a cursos disponibles
            $('.curso-card:not(.asignado)').on('click', function() {
                let cursoId = $(this).data('curso-id');
                let cursoNombre = $(this).data('curso-nombre');
                let cursoNivel = $(this).data('curso-nivel');
                
                // Remover selección de otros cursos
                $('.curso-card').removeClass('seleccionado');
                $(this).addClass('seleccionado');
                
                // Guardar curso seleccionado
                cursoSeleccionadoId = cursoId;
                $('#cursoSeleccionadoId').val(cursoId);
                $('#cursoSeleccionadoNombre').html(`<strong>${cursoNombre}</strong> <span class="text-muted">(${cursoNivel})</span>`);
                
                // Cargar aulas para este curso
                cargarAulas(cursoId);
                
                // Mostrar secciones
                $('#aulaSelectorContainer').show();
                $('#datosAdicionales').show();
                $('#botonesAccion').show();
                $('#btnAgregar').prop('disabled', true);
            });
        } else {
            $('#cursosDisponiblesList').html(`
                <div class="empty-cursos">
                    <i class="fas fa-trophy fa-2x mb-2 d-block"></i>
                    ¡Todos los cursos ya están asignados!
                </div>
            `);
            $('#aulaSelectorContainer').hide();
        }
    }
    
    function cargarAulas(cursoId) {
        let aulaSelect = $('#aula_id');
        aulaSelect.html('<option value="">Cargando aulas...</option>');
        aulaSelect.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.carga-horaria.aulas-by-curso") }}',
            method: 'GET',
            data: { curso_id: cursoId },
            success: function(response) {
                aulaSelect.html('<option value="">-- Seleccionar aula --</option>');
                if (response && response.length > 0) {
                    for (let aula of response) {
                        aulaSelect.append(`<option value="${aula.id}">
                            ${aula.nombre} - ${aula.grado || ''} "${aula.seccion || ''}" (${aula.turno_nombre || aula.turno})
                        </option>`);
                    }
                    aulaSelect.prop('disabled', false);
                    $('#btnAgregar').prop('disabled', false);
                } else {
                    aulaSelect.html('<option value="">No hay aulas disponibles para este nivel</option>');
                    aulaSelect.prop('disabled', true);
                    $('#btnAgregar').prop('disabled', true);
                    Swal.fire('Advertencia', 'No hay aulas disponibles para este curso', 'warning');
                }
            },
            error: function() {
                aulaSelect.html('<option value="">Error al cargar aulas</option>');
                aulaSelect.prop('disabled', true);
                $('#btnAgregar').prop('disabled', true);
            }
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
});

function agregarAsignacion() {
    let docenteId = $('#docente_id').val();
    let cursoId = $('#cursoSeleccionadoId').val();
    let aulaId = $('#aula_id').val();
    let horasSem = $('#horas_semanales').val();
    let diaSemana = $('#dia_semana').val();
    let horaInicio = $('#hora_inicio').val();
    let horaFin = $('#hora_fin').val();
    let observaciones = $('#observaciones').val();
    
    if (!docenteId || !cursoId || !aulaId) {
        Swal.fire('Error', 'Seleccione docente, curso y aula', 'error');
        return;
    }
    
    let btn = $('#btnAgregar');
    btn.prop('disabled', true);
    btn.html('<span class="loading-spinner me-2"></span> Guardando...');
    
    $.ajax({
        url: '{{ route("admin.carga-horaria.store") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            docente_id: docenteId,
            curso_id: cursoId,
            aula_id: aulaId,
            horas_semanales: horasSem,
            dia_semana: diaSemana,
            hora_inicio: horaInicio,
            hora_fin: horaFin,
            observaciones: observaciones
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Éxito', 'Asignación guardada exitosamente', 'success');
                limpiarFormulario();
                recargarCursosAsignados(docenteId);
            } else {
                Swal.fire('Error', response.message || 'Error al guardar', 'error');
                btn.prop('disabled', false);
                btn.html('<i class="fas fa-plus-circle me-2"></i> Agregar Curso');
            }
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON?.message || 'Error al guardar la asignación';
            Swal.fire('Error', errorMsg, 'error');
            btn.prop('disabled', false);
            btn.html('<i class="fas fa-plus-circle me-2"></i> Agregar Curso');
        }
    });
}

function recargarCursosAsignados(docenteId) {
    $.ajax({
        url: '{{ route("admin.carga-horaria.cursos-by-docente") }}',
        method: 'GET',
        data: { docente_id: docenteId },
        success: function(response) {
            let raw = null;
            if (Array.isArray(response)) raw = response;
            else if (response && Array.isArray(response.data)) raw = response.data;
            else if (response && Array.isArray(response.cursos)) raw = response.cursos;
            else if (response && Array.isArray(response.asignaciones)) raw = response.asignaciones;
            else raw = [];
            
            let cursosAsignados = raw.map(item => {
                if (item && (item.id || item.curso_id) && (item.nombre || item.name || item.titulo || item.curso_nombre)) {
                    return {
                        id: item.id || item.curso_id,
                        nombre: item.nombre || item.name || item.titulo || item.curso_nombre,
                        nivel: item.nivel || item.nivel_nombre || item.nivelId || '',
                        aula: item.aula || item.aula_nombre || item.aula_id || ''
                    };
                }
                if (item && (item.curso || item.curso_data || item.cursoObj)) {
                    let c = item.curso || item.curso_data || item.cursoObj;
                    return {
                        id: c.id || c.curso_id || '',
                        nombre: c.nombre || c.name || c.titulo || '',
                        nivel: c.nivel || c.nivel_nombre || '',
                        aula: item.aula || item.aula_nombre || (c.aula ? (c.aula.nombre || c.aula) : '')
                    };
                }
                return {
                    id: item.id || item.curso_id || '',
                    nombre: item.nombre || item.name || item.titulo || item.curso_nombre || 'Sin nombre',
                    nivel: item.nivel || item.nivel_nombre || '',
                    aula: item.aula || item.aula_nombre || ''
                };
            });
            
            let htmlAsignados = '';
            if (cursosAsignados.length > 0) {
                for (let curso of cursosAsignados) {
                    let cargaId = curso.carga_id || curso.cargaId || curso.asignacion_id || curso.asignacionId || curso.id || '';
                    htmlAsignados += `
                        <div class="curso-card asignado" data-curso-id="${curso.id}" data-carga-id="${cargaId}" style="position: relative;">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarCursoAsignado(${cargaId})" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="curso-nombre">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                ${curso.nombre}
                            </div>
                            <div class="curso-info">
                                <i class="fas fa-layer-group me-1"></i> ${curso.nivel}<br>
                                <i class="fas fa-door-open me-1"></i> Aula: ${curso.aula}
                            </div>
                            <span class="badge-asignado">Asignado</span>
                        </div>
                    `;
                }
            } else {
                htmlAsignados = `
                    <div class="empty-cursos">
                        <i class="fas fa-smile-wink fa-2x mb-2 d-block"></i>
                        Este docente aún no tiene cursos asignados
                    </div>
                `;
            }
            
            $('#cursosAsignadosList').html(htmlAsignados);
            $('#asignadosCount').text(cursosAsignados.length);
        },
        error: function() {
            console.error('Error al recargar cursos asignados');
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
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Eliminado', 'La asignación ha sido eliminada correctamente', 'success');
                        let docenteId = $('#docente_id').val();
                        recargarCursosAsignados(docenteId);
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'No se puede eliminar: el curso tiene datos enlazados o ya está en uso';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
}

function limpiarFormulario() {
    cursoSeleccionadoId = null;
    $('#cursoSeleccionadoId').val('');
    $('#aula_id').html('<option value="">-- Seleccionar aula --</option>').prop('disabled', true);
    $('#horas_semanales').val('4');
    $('#dia_semana').val('');
    $('#hora_inicio').val('');
    $('#hora_fin').val('');
    $('#observaciones').val('');
    $('#aulaSelectorContainer').hide();
    $('#datosAdicionales').hide();
    $('#botonesAccion').hide();
    let btn = $('#btnAgregar');
    btn.prop('disabled', false);
    btn.html('<i class="fas fa-plus-circle me-2"></i> Agregar Curso');
}
</script>
@endsection
