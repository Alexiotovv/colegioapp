{{-- resources/views/notas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Registro de Notas')

@section('css')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow-x: auto;
    }
    
    .table-notas {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-notas th, .table-notas td {
        padding: 10px 8px;
        vertical-align: middle;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    
    .table-notas th {
        background-color: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .table-notas td:first-child,
    .table-notas th:first-child {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 11;
    }
    
    .table-notas th:first-child {
        background-color: #f8f9fa;
        z-index: 12;
    }
    
    .table-notas td:nth-child(2),
    .table-notas th:nth-child(2) {
        position: sticky;
        left: 80px;
        background-color: white;
        z-index: 11;
    }
    
    .table-notas th:nth-child(2) {
        background-color: #f8f9fa;
        z-index: 12;
    }
    
    .nota-select {
        width: 90px;
        padding: 6px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        background-color: white;
        cursor: pointer;
    }
    
    .nota-select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(26, 71, 42, 0.25);
    }
    
    .nota-guardada {
        background-color: #d4edda;
        border-color: #28a745;
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
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background-color: #28a745;
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    
    .badge-habilitado {
        background-color: #28a745;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .badge-deshabilitado {
        background-color: #dc3545;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .dropdown-menu-notas {
        min-width: 80px;
        padding: 5px 0;
    }
    
    .dropdown-menu-notas .dropdown-item {
        padding: 5px 15px;
        font-size: 13px;
        text-align: center;
    }
    
    .dropdown-menu-notas .dropdown-item:hover {
        background-color: var(--primary-color);
        color: white;
    }
    
    /* Botón flotante */
    .fab-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
    }
    
    .fab-button {
        width: 60px;
        height: 60px;
        background-color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s;
        color: white;
        font-size: 24px;
        border: none;
    }
    
    .fab-button:hover {
        transform: scale(1.05);
        background-color: var(--primary-light);
    }
    
    .fab-menu {
        position: absolute;
        bottom: 70px;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        min-width: 180px;
        display: none;
        overflow: hidden;
        z-index: 1001;
    }
    
    .fab-menu.show {
        display: block;
        animation: fadeInUp 0.3s ease;
    }
    
    .fab-menu-item {
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: background 0.3s;
        border: none;
        background: white;
        width: 100%;
        text-align: left;
    }
    
    .fab-menu-item:hover {
        background-color: #f8f9fa;
    }
    
    .fab-menu-item i {
        width: 20px;
        color: var(--primary-color);
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    /* Ajuste para evitar scroll interno en la tabla y no se oculte el dropdown dentro de la tabla*/
    .table-responsive {
        overflow: visible !important;
    }

    .table-container {
        overflow: visible !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-edit me-2" style="color: var(--primary-color);"></i>
            Registro de Notas
        </h4>
    </div>
    
    <!-- Filtros -->
    <div class="filter-card">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="aula_id" class="form-label required-field">Aula</label>
                <select class="form-select" id="aula_id" required>
                    <option value="">Seleccionar aula</option>
                    @foreach($aulas as $aula)
                        <option value="{{ $aula->id }}">
                            {{ $aula->grado->nivel->nombre ?? '' }} - {{ $aula->grado->nombre ?? '' }} 
                            "{{ $aula->seccion->nombre ?? '' }}" ({{ $aula->turno_nombre }}) - {{ $aula->anioAcademico->anio ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="curso_id" class="form-label required-field">Curso</label>
                <select class="form-select" id="curso_id" disabled>
                    <option value="">Primero seleccione un aula</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="periodo_id" class="form-label required-field">Periodo</label>
                <select class="form-select" id="periodo_id" required>
                    <option value="">Seleccionar periodo</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}" data-activo="{{ $periodo->activo ? '1' : '0' }}">
                            {{ $periodo->nombre }} - {{ $periodo->anioAcademico->anio ?? '' }}
                            @if($periodo->activo)
                                <span class="badge-habilitado ms-2">Habilitado</span>
                            @else
                                <span class="badge-deshabilitado ms-2">Deshabilitado</span>
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12 text-end">
                <button class="btn btn-primary" id="btnCargarNotas">
                    <i class="fas fa-search me-2"></i> Cargar Notas
                </button>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Notas -->
    <div class="table-container" id="tablaContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Registro de Notas
            </h5>
            @if(auth()->user()->rol === 'admin' || (auth()->user()->role && auth()->user()->role->nombre === 'admin'))
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="toggleHabilitacion" style="width: 50px; height: 25px;">
                <label class="form-check-label ms-2" id="habilitacionLabel">Habilitar registro</label>
            </div>
            @endif
        </div>
        
        <div class="alert alert-info" id="infoPeriodo" style="display: none;">
            <i class="fas fa-info-circle me-2"></i>
            <span id="infoPeriodoText"></span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-notas" id="tablaNotas">
                <thead id="tablaHeader">
                    <!-- Header dinámico -->
                </thead>
                <tbody id="tablaBody">
                    <!-- Body dinámico -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Botón flotante -->
<div class="fab-container">
    <button class="fab-button" id="fabButton">
        <i class="fas fa-ellipsis-h"></i>
    </button>
    <div class="fab-menu" id="fabMenu">
        <button class="fab-menu-item" id="btnGuardarTodas">
            <i class="fas fa-save"></i>
            <span>Guardar todas las notas</span>
        </button>
        <button class="fab-menu-item" id="btnImprimirTodo">
            <i class="fas fa-print"></i>
            <span>Imprimir reporte</span>
        </button>
    </div>
</div>


<!-- Modal Conclusión Descriptiva -->
<div class="modal fade" id="modalConclusion" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalConclusionLabel">Conclusión Descriptiva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="conclusion_nota_id">
                <div class="alert alert-info mb-3" id="conclusion_info">
                    <!-- Información del alumno, competencia y nota -->
                </div>
                <div class="mb-3">
                    <label for="conclusion_texto" class="form-label">Conclusión Descriptiva</label>
                    <textarea class="form-control" id="conclusion_texto" rows="5" 
                              placeholder="Escriba aquí la conclusión descriptiva del logro del estudiante..."></textarea>
                    <small class="text-muted">Describe el nivel de logro, dificultades y recomendaciones para el estudiante.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarConclusion">
                    <i class="fas fa-save me-2"></i> Guardar Conclusión
                </button>
            </div>
        </div>
    </div>
</div>




@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let cursosData = [];
    let matriculasData = [];
    let competenciasData = [];
    let notasData = {};
    let notasHabilitadas = false;
    let esAdmin = {{ auth()->user()->rol === 'admin' || (auth()->user()->role && auth()->user()->role->nombre === 'admin') ? 'true' : 'false' }};
    
    // Opciones de notas
    const opcionesNotas = ['AD', 'A', 'B', 'C', 'CND', 'EXO'];
    
    // Toggle botón flotante
    $('#fabButton').on('click', function(e) {
        e.stopPropagation();
        $('#fabMenu').toggleClass('show');
    });
    
    $(document).on('click', function() {
        $('#fabMenu').removeClass('show');
    });
    
    $('#fabMenu').on('click', function(e) {
        e.stopPropagation();
    });
    
    // Cargar cursos según el aula seleccionada
    $('#aula_id').on('change', function() {
        let aulaId = $(this).val();
        let cursoSelect = $('#curso_id');
        
        if (aulaId) {
            cursoSelect.html('<option value="">Cargando...</option>');
            cursoSelect.prop('disabled', true);
            
            $.ajax({
                url: '{{ route("admin.notas.cursos-by-aula") }}',
                method: 'GET',
                data: { aula_id: aulaId },
                success: function(response) {
                    cursosData = response;
                    cursoSelect.html('<option value="">Seleccionar curso</option>');
                    
                    if (response.length > 0) {
                        for (let curso of response) {
                            cursoSelect.append(`<option value="${curso.id}">${curso.nombre} (${curso.nivel ? curso.nivel.nombre : ''})</option>`);
                        }
                        cursoSelect.prop('disabled', false);
                    } else {
                        cursoSelect.html('<option value="">No hay cursos asignados</option>');
                        cursoSelect.prop('disabled', true);
                    }
                },
                error: function() {
                    cursoSelect.html('<option value="">Error al cargar cursos</option>');
                    cursoSelect.prop('disabled', true);
                }
            });
        } else {
            cursoSelect.html('<option value="">Primero seleccione un aula</option>');
            cursoSelect.prop('disabled', true);
        }
    });
    
    // Cargar datos para el registro de notas
    $('#btnCargarNotas').on('click', function() {
        let aulaId = $('#aula_id').val();
        let cursoId = $('#curso_id').val();
        let periodoId = $('#periodo_id').val();
        
        if (!aulaId || !cursoId || !periodoId) {
            Swal.fire('Error', 'Complete todos los campos', 'error');
            return;
        }
        
        $('#btnCargarNotas').prop('disabled', true);
        $('#btnCargarNotas').html('<span class="loading-spinner me-2"></span> Cargando...');
        
        $.ajax({
            url: '{{ route("admin.notas.get-data") }}',
            method: 'GET',
            data: {
                aula_id: aulaId,
                curso_id: cursoId,
                periodo_id: periodoId
            },
            success: function(response) {
                console.log('Respuesta:', response);
                
                matriculasData = response.matriculas || [];
                competenciasData = response.competencias || [];
                notasData = response.notas || {};
                notasHabilitadas = response.notas_habilitadas || false;
                
                if (esAdmin) {
                    $('#toggleHabilitacion').prop('checked', notasHabilitadas);
                    $('#habilitacionLabel').text(notasHabilitadas ? 'Registro habilitado' : 'Registro deshabilitado');
                }
                
                let periodoSelect = $('#periodo_id option:selected');
                let periodoNombre = periodoSelect.text();
                $('#infoPeriodoText').html(`<strong>Periodo:</strong> ${periodoNombre} - <strong>Estado:</strong> ${notasHabilitadas ? '<span class="badge-habilitado">HABILITADO</span>' : '<span class="badge-deshabilitado">DESHABILITADO</span>'}`);
                $('#infoPeriodo').show();
                
                renderTabla();
                $('#tablaContainer').show();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar datos', 'error');
            },
            complete: function() {
                $('#btnCargarNotas').prop('disabled', false);
                $('#btnCargarNotas').html('<i class="fas fa-search me-2"></i> Cargar Notas');
            }
        });
    });
    
    function renderTabla() {
        if (!matriculasData || matriculasData.length === 0) {
            $('#tablaBody').html(`
                <tr>
                    <td colspan="4" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay estudiantes matriculados en esta aula.
                    </td>
                </tr>
            `);
            return;
        }
        
        if (!competenciasData || competenciasData.length === 0) {
            $('#tablaBody').html(`
                <tr>
                    <td colspan="4" class="text-center text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay competencias registradas para este curso.
                    </td>
                </tr>
            `);
            return;
        }
        
        // Renderizar header
        let headerHtml = `
            <tr>
                <th style="min-width: 60px;">N°</th>
                <th style="min-width: 150px;">Código</th>
                <th style="min-width: 250px;">Alumno</th>
        `;
        
        for (let competencia of competenciasData) {
            headerHtml += `<th colspan="1">${competencia.nombre}<br><small class="text-muted">${competencia.ponderacion}%</small></th>`;
        }
        headerHtml += `</tr>`;
        
        $('#tablaHeader').html(headerHtml);
        
        // Renderizar body
        let bodyHtml = '';
        let contador = 1;
        
        for (let matricula of matriculasData) {
            bodyHtml += `<tr>
                <td><strong>${contador}</strong></td>
                <td>${matricula.alumno.codigo_estudiante || 'N/A'}</td>
                <td style="text-align: left;">
                    <strong>${matricula.alumno.apellido_paterno || ''} ${matricula.alumno.apellido_materno || ''}</strong><br>
                    <small>${matricula.alumno.nombres || ''}</small>
                </td>`;
            
            for (let competencia of competenciasData) {
                let notaKey = matricula.id + '_' + competencia.id;
                let nota = notasData[notaKey];
                let notaValue = nota ? nota.nota : '';
                let notaGuardada = notaValue ? 'nota-guardada' : '';
                let notaId = nota ? nota.id : '';
                let tieneConclusion = nota && nota.tiene_conclusion;
                
                bodyHtml += `
                    <td style="text-align: center; vertical-align: middle;">
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle nota-select ${notaGuardada}" 
                                    type="button" data-bs-toggle="dropdown" 
                                    data-matricula="${matricula.id}" 
                                    data-competencia="${competencia.id}"
                                    data-nota-id="${notaId}"
                                    ${!notasHabilitadas ? 'disabled' : ''}>
                                ${notaValue || 'Seleccionar'}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-notas">
                                ${opcionesNotas.map(op => `<li><a class="dropdown-item" href="#" data-valor="${op}">${op}</a></li>`).join('')}
                            </ul>
                        </div>
                        <input type="hidden" class="nota-valor" data-matricula="${matricula.id}" data-competencia="${competencia.id}" data-nota-id="${notaId}" value="${notaValue}">
                        <button class="btn-message" 
                                data-nota-id="${notaId}"
                                data-alumno="${matricula.alumno.nombre_completo}"
                                data-competencia="${competencia.nombre}"
                                data-nota="${notaValue}"
                                style="background: none; border: none; cursor: pointer; margin-left: 5px;">
                            <i class="fas fa-comment-dots" style="font-size: 16px; color: ${tieneConclusion ? '#28a745' : '#6c757d'};"></i>
                        </button>
                    </td>
                `;
            }
            bodyHtml += `</tr>`;
            contador++;
        }
        
        $('#tablaBody').html(bodyHtml);
        
        // Configurar eventos de los botones de mensaje
        $('.btn-message').on('click', function() {
            let btn = $(this);
            let notaId = btn.data('nota-id');
            let alumnoNombre = btn.data('alumno');
            let competenciaNombre = btn.data('competencia');
            let notaValor = btn.data('nota');
            
            if (!notaId) {
                Swal.fire('Advertencia', 'Primero debe guardar la nota antes de agregar una conclusión', 'warning');
                return;
            }
            
            abrirModalConclusion(notaId, alumnoNombre, competenciaNombre, notaValor);
        });
        
        // Configurar eventos de los dropdowns
        $('.dropdown-menu .dropdown-item').on('click', function(e) {
            e.preventDefault();
            let valor = $(this).data('valor');
            let boton = $(this).closest('td').find('.dropdown-toggle');
            let hiddenInput = $(this).closest('td').find('.nota-valor');
            let btnMensaje = $(this).closest('td').find('.btn-message');
            
            boton.text(valor);
            hiddenInput.val(valor);
            
            // Actualizar data-nota del botón mensaje
            btnMensaje.data('nota', valor);
            
            if (valor) {
                boton.addClass('nota-guardada');
            } else {
                boton.removeClass('nota-guardada');
            }
        });

     

    }
    
    // Guardar todas las notas
    function guardarTodasLasNotas() {
        if (!notasHabilitadas) {
            Swal.fire('Error', 'El registro de notas no está habilitado', 'error');
            return;
        }
        
        let notas = [];
        let periodoId = $('#periodo_id').val();
        
        $('.nota-valor').each(function() {
            let nota = $(this).val();
            if (nota) {
                notas.push({
                    matricula_id: $(this).data('matricula'),
                    competencia_id: $(this).data('competencia'),
                    nota: nota,
                    observacion: ''
                });
            }
        });
        
        if (notas.length === 0) {
            Swal.fire('Advertencia', 'No hay notas para guardar', 'warning');
            return;
        }
        
        let btn = $('#btnGuardarTodas');
        let originalHtml = btn.html();
        
        btn.prop('disabled', true);
        btn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: '{{ route("admin.notas.save") }}',
            method: 'POST',
            data: {
                notas: notas,
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success');
                    $('#btnCargarNotas').click();
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar notas', 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
                $('#fabMenu').removeClass('show');
            }
        });
    }
    
    // Imprimir reporte
    function imprimirReporte() {
        Swal.fire('Información', 'Funcionalidad de impresión en desarrollo', 'info');
        $('#fabMenu').removeClass('show');
    }
    
    function abrirModalConclusion(notaId, alumnoNombre, competenciaNombre, notaValor) {
        $('#modalConclusionLabel').text(`Conclusión Descriptiva`);
        $('#conclusion_info').html(`
            <strong>Alumno:</strong> ${alumnoNombre}<br>
            <strong>Competencia:</strong> ${competenciaNombre}<br>
            <strong>Nota:</strong> ${notaValor}
        `);
        $('#conclusion_nota_id').val(notaId);
        $('#conclusion_texto').val('');
        
        $.ajax({
            url: '/admin/notas/conclusion/' + notaId,
            method: 'GET',
            success: function(response) {
                if (response.success && response.conclusion) {
                    $('#conclusion_texto').val(response.conclusion);
                }
            },
            error: function() {
                console.log('No hay conclusión previa');
            }
        });
        
        $('#modalConclusion').modal('show');
    }
    
    // Guardar conclusión
    $('#btnGuardarConclusion').on('click', function() {
        let notaId = $('#conclusion_nota_id').val();
        let conclusion = $('#conclusion_texto').val();
        
        if (!conclusion.trim()) {
            Swal.fire('Advertencia', 'Por favor ingrese una conclusión', 'warning');
            return;
        }
        
        $.ajax({
            url: '{{ route("admin.notas.save-conclusion") }}',
            method: 'POST',
            data: {
                nota_id: notaId,
                conclusion: conclusion,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success');
                    $('#modalConclusion').modal('hide');
                    
                    // Cambiar el color del icono a verde
                    $(`.btn-message[data-nota-id="${notaId}"] i`).css('color', '#28a745');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar la conclusión', 'error');
            }
        });
    });
    
    // Asignar eventos a los botones del menú flotante
    $('#btnGuardarTodas').on('click', guardarTodasLasNotas);
    $('#btnImprimirTodo').on('click', imprimirReporte);
    
    // Cambiar habilitación del registro de notas (solo admin)
    $('#toggleHabilitacion').on('change', function() {
        if (!esAdmin) {
            Swal.fire('Error', 'No tienes permisos para realizar esta acción', 'error');
            $(this).prop('checked', !$(this).is(':checked'));
            return;
        }
        
        let habilitado = $(this).is(':checked');
        let periodoId = $('#periodo_id').val();
        
        if (!periodoId) return;
        
        $.ajax({
            url: '{{ route("admin.notas.toggle-habilitacion") }}',
            method: 'POST',
            data: {
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    notasHabilitadas = response.habilitado;
                    $('#habilitacionLabel').text(notasHabilitadas ? 'Registro habilitado' : 'Registro deshabilitado');
                    Swal.fire('Éxito', response.message, 'success');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar estado', 'error');
                $('#toggleHabilitacion').prop('checked', !habilitado);
            }
        });
    });
});
</script>
@endsection