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
        cursor: pointer;
        transition: all 0.3s;
        border: 1px solid #e9ecef;
        position: relative;
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
        right: 10px;
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
                    <div class="cursos-container">
                        <!-- Columna: Cursos No Asignados (Disponibles) -->
                        <div class="cursos-columna">
                            <h6 class="text-center">
                                <i class="fas fa-clock text-warning me-1"></i> 
                                Cursos Disponibles
                                <span class="badge bg-secondary ms-2" id="disponiblesCount">0</span>
                            </h6>
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
                            <div class="cursos-lista" id="cursosAsignadosList">
                                <div class="text-center py-4 text-muted">
                                    Seleccione un docente para ver sus cursos
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información del curso seleccionado y selector de aula -->
                    <div id="aulaSelectorContainer" style="display: none;">
                        <div class="aula-selector">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Curso seleccionado:</strong> 
                                        <span id="cursoSeleccionadoNombre"></span>
                                        <input type="hidden" id="cursoSeleccionadoId" name="curso_id">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="aula_id" class="form-label fw-semibold">
                                        <i class="fas fa-door-open me-1"></i> Seleccionar Aula:
                                    </label>
                                    <select class="form-select" id="aula_id" name="aula_id" required>
                                        <option value="">-- Primero seleccione un curso --</option>
                                    </select>
                                    <small class="text-muted">Las aulas disponibles dependen del nivel del curso</small>
                                </div>
                            </div>
                        </div>
                    </div>
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
                <a href="{{ route('admin.carga-horaria.index') }}" class="btn btn-secondary px-4">
                    <i class="fas fa-times me-2"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-4" id="submitBtn" disabled>
                    <i class="fas fa-save me-2"></i> Asignar Curso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
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
    let cursoSeleccionadoId = null;
    
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
                        console.log('Cursos totales cargados:', todosLosCursos.length);
                        cargarCursosDocente(docenteId);
                    } else {
                        console.error('Respuesta inesperada en todos-cursos:', response);
                        todosLosCursos = [];
                        Swal.fire('Error', 'Error al cargar los cursos disponibles', 'error');
                        mostrarLoading(false);
                    }
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
                // Verificar que response es un array
                if (Array.isArray(response)) {
                    cursosAsignados = response;
                } else {
                    console.error('Respuesta inesperada:', response);
                    cursosAsignados = [];
                }
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
        // IDs de cursos ya asignados
        let idsAsignados = cursosAsignados.map(c => c.id);
        
        // Cursos no asignados
        let cursosNoAsignados = todosLosCursos.filter(curso => !idsAsignados.includes(curso.id));
        
        // Actualizar contadores
        $('#asignadosCount').text(cursosAsignados.length);
        $('#disponiblesCount').text(cursosNoAsignados.length);
        
        // Renderizar cursos asignados
        if (cursosAsignados.length > 0) {
            let htmlAsignados = '';
            for (let curso of cursosAsignados) {
                htmlAsignados += `
                    <div class="curso-card asignado" data-curso-id="${curso.id}">
                        <div class="curso-nombre">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            ${curso.nombre}
                        </div>
                        <div class="curso-info">
                            <i class="fas fa-layer-group me-1"></i> ${curso.nivel || 'Sin nivel'}<br>
                            <i class="fas fa-door-open me-1"></i> Aula: ${curso.aula || 'No asignada'}
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
                htmlDisponibles += `
                    <div class="curso-card ${cursoSeleccionadoId === curso.id ? 'seleccionado' : ''}" 
                         data-curso-id="${curso.id}"
                         data-curso-nombre="${curso.nombre}"
                         data-curso-nivel="${curso.nivel || ''}">
                        <div class="curso-nombre">
                            <i class="fas fa-book text-primary me-1"></i>
                            ${curso.nombre}
                        </div>
                        <div class="curso-info">
                            <i class="fas fa-layer-group me-1"></i> ${curso.nivel || 'Sin nivel'}
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
                $('#submitBtn').prop('disabled', true);
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
                    $('#submitBtn').prop('disabled', false);
                } else {
                    aulaSelect.html('<option value="">No hay aulas disponibles para este nivel</option>');
                    aulaSelect.prop('disabled', true);
                    $('#submitBtn').prop('disabled', true);
                    Swal.fire('Advertencia', 'No hay aulas disponibles para este curso', 'warning');
                }
            },
            error: function() {
                aulaSelect.html('<option value="">Error al cargar aulas</option>');
                aulaSelect.prop('disabled', true);
                $('#submitBtn').prop('disabled', true);
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
    
    // Enviar formulario
    $('#cargaHorariaForm').on('submit', function(e) {
        e.preventDefault();
        
        let docenteId = $('#docente_id').val();
        let cursoId = $('#cursoSeleccionadoId').val();
        let aulaId = $('#aula_id').val();
        
        if (!docenteId) {
            Swal.fire('Error', 'Seleccione un docente', 'error');
            return;
        }
        
        if (!cursoId) {
            Swal.fire('Error', 'Seleccione un curso', 'error');
            return;
        }
        
        if (!aulaId) {
            Swal.fire('Error', 'Seleccione un aula', 'error');
            return;
        }
        
        let submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner me-2"></span> Asignando...');
        
        $.ajax({
            url: '{{ route("admin.carga-horaria.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Asignación Exitosa!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("admin.carga-horaria.index") }}';
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.message || 'Error al guardar la asignación';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save me-2"></i> Asignar Curso');
            }
        });
    });
});
</script>
@endsection