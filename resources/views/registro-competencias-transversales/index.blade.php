{{-- resources/views/registro-competencias-transversales/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Registro de Competencias Transversales')

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
    
    .table-competencias {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }
    
    .table-competencias th, 
    .table-competencias td {
        padding: 10px 8px;
        vertical-align: middle;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    
    .table-competencias th {
        background-color: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    /* Columnas fijas */
    .table-competencias th:first-child,
    .table-competencias td:first-child {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 11;
        min-width: 60px;
    }
    
    .table-competencias th:nth-child(2),
    .table-competencias td:nth-child(2) {
        position: sticky;
        left: 60px;
        background-color: white;
        z-index: 11;
        min-width: 120px;
    }
    
    .table-competencias th:nth-child(3),
    .table-competencias td:nth-child(3) {
        position: sticky;
        left: 180px;
        background-color: white;
        z-index: 11;
        min-width: 220px;
    }
    
    .table-competencias th:first-child,
    .table-competencias th:nth-child(2),
    .table-competencias th:nth-child(3) {
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

    .nota-select.modified {
        position: relative;
    }

    .nota-select.modified::after {
        content: '';
        position: absolute;
        top: -4px;
        right: -4px;
        width: 8px;
        height: 8px;
        background: #dc3545;
        border-radius: 50%;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.8);
    }

    .select-wrapper {
        position: relative;
        display: inline-block;
        z-index: 20;
    }

    .select-wrapper.modified::after {
        content: '';
        position: absolute;
        top: -4px;
        right: -4px;
        width: 8px;
        height: 8px;
        background: #dc3545;
        border-radius: 50%;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.8);
        z-index: 30;
    }
    
    .conclusion-textarea {
        width: 200px;
        padding: 6px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        font-size: 11px;
        resize: vertical;
    }
    
    .registro-guardado {
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
    }
    
    .fab-menu {
        position: absolute;
        bottom: 70px;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        min-width: 200px;
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

    /* Progress Bar - Estilo limpio */
    .progress-container {
        background: white;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-size: 12px;
        color: #555;
    }

    .progress-title {
        font-weight: 500;
    }

    .progress-percentage {
        font-weight: 600;
        color: var(--primary-color);
    }

    .progress-bar-container {
        background-color: #e9ecef;
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
    }

    .progress-bar-fill {
        background-color: #28a745;
        width: 0%;
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .progress-stats {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        font-size: 10px;
        color: #888;
    }

    .progress-stats span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .progress-stats i {
        font-size: 10px;
    }

    .progress-stats .completed {
        color: #28a745;
    }

    .progress-stats .pending {
        color: #dc3545;
    }

    /* Estilos para el icono de mensaje */
    .btn-message {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        transition: all 0.3s;
        margin-left: 5px;
    }

    .btn-message:hover {
        background-color: #e9ecef;
    }

    .btn-message i {
        font-size: 16px;
    }

    /* Modal de conclusión */
    .modal-conclusion .modal-header {
        background: var(--primary-color);
        color: white;
    }

    .modal-conclusion .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
        /* Estilos adicionales para el toast */
    .toast-notification:hover {
        transform: translateX(0) scale(1.02);
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }

    .toast-close:hover {
        color: #333 !important;
    }

    /* Animación de entrada */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
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
@include('partials.toast')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-exchange-alt me-2" style="color: var(--primary-color);"></i>
            Registro de Competencias Transversales
        </h4>
    </div>
    
    <div class="filter-card">
        <div class="row g-3">
            <div class="col-md-6">
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
            <div class="col-md-6">
                <label for="periodo_id" class="form-label required-field">Periodo</label>
                <select class="form-select" id="periodo_id" required disabled>
                    <option value="">Seleccionar periodo</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}" data-activo="{{ $periodo->activo ? '1' : '0' }}">
                            {{ $periodo->nombre }} - {{ $periodo->anioAcademico->anio ?? '' }}
                            @if($periodo->activo)
                                <span class="badge bg-success ms-2">Habilitado</span>
                            @else
                                <span class="badge bg-secondary ms-2">Deshabilitado</span>
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        
    </div>

    <div class="table-container" id="tablaContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-exchange-alt me-2"></i>
                Registro de Competencias Transversales
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
        
        <div class="alert alert-info" id="infoConclusionRegla" style="display: none;">
            <i class="fas fa-info-circle me-2"></i>
            <span id="infoConclusionReglaText"></span>
        </div>
        
        <!-- Progress Bar -->
        <div class="progress-container" id="progressContainer" style="display: none;">
            <div class="progress-header">
                <span class="progress-title">
                    <i class="fas fa-chart-line me-1"></i> Avance de registro
                </span>
                <span class="progress-percentage" id="progressPercentage">0%</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="progressBarFill"></div>
            </div>
            <div class="progress-stats">
                <span class="completed">
                    <i class="fas fa-check-circle"></i> Completadas: <span id="completedCount">0</span>
                </span>
                <span class="pending">
                    <i class="fas fa-circle"></i> Pendientes: <span id="pendingCount">0</span>
                </span>
                <span>
                    <i class="fas fa-tasks"></i> Total: <span id="totalCount">0</span>
                </span>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered table-competencias" id="tablaCompetencias">
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

<div class="fab-container">
    <button class="fab-button" id="fabButton">
        <i class="fas fa-ellipsis-h"></i>
    </button>
    <div class="fab-menu" id="fabMenu">
        <button class="fab-menu-item" id="btnGuardarTodas">
            <i class="fas fa-save"></i>
            <span>Guardar todos los registros</span>
        </button>
        <button class="fab-menu-item" id="btnImprimirTodo">
            <i class="fas fa-print"></i>
            <span>Imprimir reporte</span>
        </button>
    </div>
</div>

<!-- Modal Conclusión Descriptiva -->
<div class="modal fade modal-conclusion" id="modalConclusion" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConclusionLabel">Conclusión Descriptiva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="conclusion_registro_id">
                <input type="hidden" id="conclusion_matricula_id">
                <input type="hidden" id="conclusion_competencia_id">
                <div class="alert alert-info mb-3" id="conclusion_info">
                    <!-- Información del alumno y competencia -->
                </div>
                <div class="mb-3">
                    <label for="conclusion_texto" class="form-label">Conclusión Descriptiva</label>
                    <textarea class="form-control" id="conclusion_texto" rows="5" 
                              placeholder="Escriba aquí la conclusión descriptiva..."></textarea>
                    <small class="text-muted">Describe el nivel de logro y recomendaciones.</small>
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
    let matriculasData = [];
    let competenciasData = [];
    let registrosData = {};
    let registrosHabilitados = false;
    let esAdmin = {{ auth()->user()->rol === 'admin' || (auth()->user()->role && auth()->user()->role->nombre === 'admin') ? 'true' : 'false' }};
    
    let opcionesNotas = ['AD', 'A', 'B', 'C', 'CND', 'ND'];
    let requiereConclusionBCPrimaria = false;
    let requiereConclusionBSecundaria = false;
    let aulaEsPrimaria = false;
    let aulaEsSecundaria = false;

    // Función para cargar opciones de notas desde el servidor
    function cargarOpcionesNotas() {
        $.ajax({
            url: '{{ route("admin.registro-competencias-transversales.opciones") }}',
            method: 'GET',
            async: false,
            success: function(response) {
                if (response && response.length > 0) {
                    opcionesNotas = response;
                    console.log('Opciones de notas CT cargadas:', opcionesNotas);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar opciones de notas:', xhr);
                // Mantener opciones por defecto
            }
        });
    }

    // Cargar opciones de notas al inicio
    cargarOpcionesNotas();

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
    
    function limpiarTablaCompetencias() {
        matriculasData = [];
        competenciasData = [];
        registrosData = {};
        registrosHabilitados = false;
        aulaEsPrimaria = false;
        aulaEsSecundaria = false;
        requiereConclusionBCPrimaria = false;
        requiereConclusionBSecundaria = false;
        $('#tablaBody').empty();
        $('#tablaHeader').empty();
        $('#tablaContainer').hide();
        $('#infoPeriodo').hide();
        $('#infoConclusionRegla').hide();
        $('#habilitacionLabel').text('Habilitar registro');
        $('#toggleHabilitacion').prop('checked', false);
        actualizarEstadoBotonGuardar();
    }

    function actualizarEstadoBotonGuardar() {
        let btn = $('#btnGuardarTodas');
        if (registrosHabilitados && matriculasData.length > 0 && competenciasData.length > 0) {
            btn.prop('disabled', false);
        } else {
            btn.prop('disabled', true);
        }
    }

    function cargarCompetenciasAutomaticamente() {
        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();

        if (!aulaId || !periodoId) return;

        $('#tablaBody').html(`<tr><td colspan="3" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>`);

        $.ajax({
            url: '{{ route("admin.registro-competencias-transversales.get-data") }}',
            method: 'GET',
            data: {
                aula_id: aulaId,
                periodo_id: periodoId
            },
            success: function(response) {
                matriculasData = response.matriculas || [];
                competenciasData = response.competencias || [];
                registrosData = response.registros || {};
                registrosHabilitados = response.registros_habilitados || false;
                aulaEsPrimaria = response.aula_es_primaria || false;
                aulaEsSecundaria = response.aula_es_secundaria || false;
                requiereConclusionBCPrimaria = response.requerir_conclusion_bc_primaria || false;
                requiereConclusionBSecundaria = response.requerir_conclusion_b_secundaria || false;

                if (esAdmin) {
                    $('#toggleHabilitacion').prop('checked', registrosHabilitados);
                    $('#habilitacionLabel').text(registrosHabilitados ? 'Registro habilitado' : 'Registro deshabilitado');
                }

                let periodoSelect = $('#periodo_id option:selected');
                let periodoNombre = periodoSelect.text();
                $('#infoPeriodoText').html(`<strong>Periodo:</strong> ${periodoNombre} - <strong>Estado:</strong> ${registrosHabilitados ? '<span class="badge bg-success">HABILITADO</span>' : '<span class="badge bg-secondary">DESHABILITADO</span>'}`);
                $('#infoPeriodo').show();
                if ((aulaEsPrimaria && requiereConclusionBCPrimaria) || (aulaEsSecundaria && requiereConclusionBSecundaria)) {
                    let messages = [];
                    if (aulaEsPrimaria && requiereConclusionBCPrimaria) {
                        messages.push('Las notas B/C en Primaria requieren una conclusión descriptiva.');
                    }
                    if (aulaEsSecundaria && requiereConclusionBSecundaria) {
                        messages.push('La nota C en Secundaria requiere una conclusión descriptiva.');
                    }
                    $('#infoConclusionReglaText').text(messages.join(' '));
                    $('#infoConclusionRegla').show();
                } else {
                    $('#infoConclusionRegla').hide();
                }

                renderTabla();
                actualizarEstadoBotonGuardar();
                $('#tablaContainer').show();
                actualizarEstadoBotonGuardar();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar datos', 'error');
                limpiarTablaCompetencias();
            }
        });
    }

    // Bind guided-select behavior
    $('#aula_id').on('change', function() {
        let aulaId = $(this).val();
        limpiarTablaCompetencias();
        // Reset periodo select to default when aula changes
        $('#periodo_id').val('');
        if (aulaId) {
            $('#periodo_id').prop('disabled', false);
        } else {
            $('#periodo_id').prop('disabled', true);
        }
        // Trigger change to ensure dependent logic runs (will not auto-load because value is empty)
        $('#periodo_id').trigger('change');
    });

    $('#periodo_id').on('change', function() {
        let periodoId = $(this).val();
        if ($('#aula_id').val() && periodoId) {
            cargarCompetenciasAutomaticamente();
        } else {
            limpiarTablaCompetencias();
        }
    });

    // Initialize state
    limpiarTablaCompetencias();
    
    function renderTabla() {
        if (!matriculasData || matriculasData.length === 0) {
            $('#tablaBody').html(`
                <tr>
                    <td colspan="3" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay estudiantes matriculados en esta aula.
                    </td>
                </tr>
            `);
            $('#progressContainer').hide();
            return;
        }
        
        if (!competenciasData || competenciasData.length === 0) {
            $('#tablaBody').html(`
                <tr>
                    <td colspan="3" class="text-center text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay competencias transversales registradas.
                    </td>
                </tr>
            `);
            $('#progressContainer').hide();
            return;
        }
        
        // Mostrar progress container
        $('#progressContainer').show();
        
        // Renderizar header
        let headerHtml = `
            <tr>
                <th style="min-width: 60px;">N°</th>
                <th style="min-width: 150px;">Código</th>
                <th style="min-width: 250px;">Alumno</th>
        `;
        
        for (let competencia of competenciasData) {
            headerHtml += `<th colspan="1">${competencia.nombre}<br><small class="text-muted">${competencia.nivel ? competencia.nivel.nombre : ''}</small></th>`;
        }
        headerHtml += `</tr>`;
        
        $('#tablaHeader').html(headerHtml);
        
        // Renderizar body
        let bodyHtml = '';
        let contador = 1;
        
        for (let matricula of matriculasData) {
            let registrosAlumno = registrosData[matricula.id] || {};
            
            bodyHtml += `<tr>
                <td><strong>${contador}</strong></td>
                <td>${matricula.alumno.codigo_estudiante || 'N/A'}</td>
                <td style="text-align: left;">
                    <strong>${matricula.alumno.apellido_paterno || ''} ${matricula.alumno.apellido_materno || ''}</strong><br>
                    <small>${matricula.alumno.nombres || ''}</small>
                </td>`;
            
            for (let competencia of competenciasData) {
                let registro = registrosAlumno[competencia.id];
                let notaValue = registro ? registro.nota : '';
                let notaGuardada = notaValue ? 'registro-guardado' : '';
                let tieneConclusion = registro && registro.conclusion;
                let registroId = registro ? registro.id : '';
                
                bodyHtml += `
                    <td style="text-align: center;">
                    <div class="select-wrapper">
                        <select class="form-select form-select-sm nota-select ${notaGuardada}"
                                data-matricula="${matricula.id}"
                                data-competencia="${competencia.id}"
                                data-registro-id="${registroId}"
                                ${!registrosHabilitados ? 'disabled' : ''}
                                style="width: 110px; margin: 0 auto; display: inline-block;">
                            <option value="">Seleccionar</option>
                            ${opcionesNotas.map(op => `<option value="${op}" ${notaValue === op ? 'selected' : ''}>${op}</option>`).join('')}
                        </select>
                    </div>
                    <input type="hidden" class="nota-valor" data-matricula="${matricula.id}" data-competencia="${competencia.id}" data-registro-id="${registroId}" value="${notaValue}">
                    <button class="btn-message" 
                            data-registro-id="${registroId}"
                            data-matricula-id="${matricula.id}"
                            data-competencia-id="${competencia.id}"
                            data-tiene-conclusion="${tieneConclusion ? 1 : 0}"
                            data-alumno="${matricula.alumno.nombre_completo}"
                            data-competencia="${competencia.nombre}"
                            data-nota="${notaValue}"
                            ${!registrosHabilitados ? 'disabled' : ''}>
                        <i class="fas fa-comment-dots" style="font-size: 16px; color: ${tieneConclusion ? '#28a745' : '#6c757d'};"></i>
                    </button>
                </td>
            `;
        }
        bodyHtml += `</tr>`;
        contador++;
    }
    
    $('#tablaBody').html(bodyHtml);
    $('.nota-valor').each(function() {
        $(this).data('initial', $(this).val() || '');
        let inicial = $(this).data('initial');
        let $wrapper = $(this).closest('td').find('.select-wrapper');
        if ($(this).val() !== inicial) {
            $wrapper.addClass('modified');
        } else {
            $wrapper.removeClass('modified');
        }
    });
    
    // Configurar eventos de selección
    $('.nota-select').off('change').on('change', function() {
        let valor = $(this).val();
        let $select = $(this);
        let $wrapper = $select.closest('.select-wrapper');
        let hiddenInput = $select.closest('td').find('.nota-valor');
        let btnMensaje = $select.closest('td').find('.btn-message');
        
        hiddenInput.val(valor);
        
        // Actualizar data-nota del botón mensaje
        btnMensaje.data('nota', valor);
        
        if (valor) {
            $select.addClass('registro-guardado');
        } else {
            $select.removeClass('registro-guardado');
        }

        let inicial = hiddenInput.data('initial') || '';
        if (valor !== inicial) {
            $wrapper.addClass('modified');
        } else {
            $wrapper.removeClass('modified');
        }

        let ruleActivePrimaria = requiereConclusionBCPrimaria && aulaEsPrimaria && ['B', 'C'].includes(valor);
        let ruleActiveSecundaria = requiereConclusionBSecundaria && aulaEsSecundaria && valor === 'B';
        let tieneConclusion = btnMensaje.data('tiene-conclusion') === 1 || btnMensaje.data('tiene-conclusion') === '1';

        if (tieneConclusion) {
            btnMensaje.find('i').css('color', '#28a745');
        } else if (ruleActivePrimaria || ruleActiveSecundaria) {
            let mensaje = ruleActivePrimaria
                ? 'Las notas B/C en Primaria requieren una conclusión descriptiva. Abra el icono de comentario para registrarla.'
                : 'La nota B en Secundaria requiere una conclusión descriptiva. Abra el icono de comentario para registrarla.';
            // Swal.fire('Atención', mensaje, 'info');
            btnMensaje.find('i').css('color', '#dc3545');
        } else {
            btnMensaje.find('i').css('color', '#6c757d');
        }
        
        actualizarProgreso();
    });
    
    // Configurar eventos de los botones de mensaje
    $('.btn-message').on('click', function() {
        let btn = $(this);
        let registroId = btn.data('registro-id');
        let matriculaId = btn.data('matricula-id');
        let competenciaId = btn.data('competencia-id');
        let alumnoNombre = btn.data('alumno');
        let competenciaNombre = btn.data('competencia');
        let notaValor = btn.data('nota');
        
        if (!notaValor) {
            Swal.fire('Advertencia', 'Primero debe seleccionar una nota antes de agregar una conclusión', 'warning');
            return;
        }
        
        abrirModalConclusion(registroId, matriculaId, competenciaId, alumnoNombre, competenciaNombre, notaValor);
    });
    
    actualizarProgreso();
}
    
    function guardarTodosLosRegistros() {
        if (!registrosHabilitados) {
            Swal.fire('Error', 'El registro de competencias transversales no está habilitado', 'error');
            return;
        }
        let aulaId = $('#aula_id').val();
        
        // 🔥 Verificar que todos los registros estén completos
        let totalInputs = 0;
        let completados = 0;
        
        $('.nota-valor').each(function() {
            totalInputs++;
            let valor = $(this).val();
            
            if (valor && valor.trim() !== '') {
                completados++;
            }
        });
        
        // if (completados < totalInputs) {
        //     Swal.fire({
        //         icon: 'warning',
        //         title: 'Registro incompleto',
        //         html: `Falta<strong> ${totalInputs - completados} </strong>registro${(totalInputs - completados) !== 1 ? 's' : ''} por completar.<br>Complete todas las notas antes de guardar.`,
        //         confirmButtonColor: '#3085d6',
        //         confirmButtonText: 'Entendido'
        //     });
        //     return;
        // }
        
        let registros = [];
        let periodoId = $('#periodo_id').val();
        
        $('.nota-valor').each(function() {
            let nota = $(this).val();
            let matriculaId = $(this).data('matricula');
            let competenciaId = $(this).data('competencia');
            let conclusion = $(this).closest('tr').find('.conclusion-textarea').val();
            
            if (nota) {
                registros.push({
                    matricula_id: matriculaId,
                    competencia_transversal_id: competenciaId,
                    nota: nota,
                    conclusion: conclusion || ''
                });
            }
        });
        
        if (registros.length === 0) {
            Swal.fire('Advertencia', 'No hay registros para guardar', 'warning');
            return;
        }
        
        let btn = $('#btnGuardarTodas');
        let originalHtml = btn.html();
        
        btn.prop('disabled', true);
        btn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: '{{ route("admin.registro-competencias-transversales.save") }}',
            method: 'POST',
            data: {
                registros: registros,
                periodo_id: periodoId,
                aula_id: aulaId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success').then(() => {
                        recargarDatos();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                btn.prop('disabled', false);
                btn.html(originalHtml);
                $('#fabMenu').removeClass('show');
            }
        });
    }
    
    function recargarDatos() {
        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();
        
        if (!aulaId || !periodoId) {
            return;
        }
        
        $('#tablaBody').html(`
            <tr>
                <td colspan="${competenciasData.length + 3}" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `);
        
        $.ajax({
            url: '{{ route("admin.registro-competencias-transversales.get-data") }}',
            method: 'GET',
            data: {
                aula_id: aulaId,
                periodo_id: periodoId
            },
            success: function(response) {
                matriculasData = response.matriculas || [];
                competenciasData = response.competencias || [];
                registrosData = response.registros || {};
                registrosHabilitados = response.registros_habilitados || false;
                
                renderTabla();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al recargar datos', 'error');
            },
            complete: function() {
                let btn = $('#btnGuardarTodas');
                btn.prop('disabled', false);
                btn.html('<i class="fas fa-save"></i> Guardar todos los registros');
                $('#fabMenu').removeClass('show');
            }
        });
    }

    function actualizarConclusionEnPantalla(matriculaId, competenciaId, conclusion, registroId, notaValor) {
        const keyMatricula = String(matriculaId);
        const keyCompetencia = String(competenciaId);

        if (!registrosData[keyMatricula]) {
            registrosData[keyMatricula] = {};
        }

        if (!registrosData[keyMatricula][keyCompetencia]) {
            registrosData[keyMatricula][keyCompetencia] = {};
        }

        registrosData[keyMatricula][keyCompetencia].id = registroId || registrosData[keyMatricula][keyCompetencia].id || '';
        registrosData[keyMatricula][keyCompetencia].nota = notaValor || registrosData[keyMatricula][keyCompetencia].nota || '';
        registrosData[keyMatricula][keyCompetencia].conclusion = conclusion;

        const $btnMensaje = $(`.btn-message[data-matricula-id="${matriculaId}"][data-competencia-id="${competenciaId}"]`);
        const $notaInput = $(`.nota-valor[data-matricula="${matriculaId}"][data-competencia="${competenciaId}"]`);

        if ($btnMensaje.length) {
            $btnMensaje.data('registro-id', registroId || '');
            $btnMensaje.data('tiene-conclusion', 1);
            $btnMensaje.data('nota', notaValor || '');
            $btnMensaje.find('i').css('color', '#28a745');
        }

        if ($notaInput.length && registroId) {
            $notaInput.data('registro-id', registroId);
        }
    }
    
    function imprimirReporte() {
        Swal.fire('Información', 'Funcionalidad de impresión en desarrollo', 'info');
        $('#fabMenu').removeClass('show');
    }
    
    $('#btnGuardarTodas').on('click', guardarTodosLosRegistros);
    $('#btnImprimirTodo').on('click', imprimirReporte);
    
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
            url: '{{ route("admin.registro-competencias-transversales.toggle-habilitacion") }}',
            method: 'POST',
            data: {
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    registrosHabilitados = response.habilitado;
                    $('#habilitacionLabel').text(registrosHabilitados ? 'Registro habilitado' : 'Registro deshabilitado');
                    $('.nota-select, .conclusion-textarea').prop('disabled', !registrosHabilitados);
                    
                    // 🔥 Actualizar progreso después de cambiar estado
                    if (registrosHabilitados) {
                        actualizarProgreso();
                    } else {
                        $('#btnGuardarTodas').prop('disabled', true);
                    }
                    
                    Swal.fire('Éxito', response.message, 'success');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar estado', 'error');
                $('#toggleHabilitacion').prop('checked', !habilitado);
            }
        });
    });


    function actualizarProgreso() {
        let totalInputs = 0;
        let completados = 0;
        
        $('.nota-valor').each(function() {
            totalInputs++;
            
            let valor = $(this).val();

            // DEBUG (opcional)
            // console.log('Valor:', valor);

            if (valor !== null && valor !== undefined && valor.trim() !== '') {
                completados++;
            }
        });

        let porcentaje = totalInputs > 0 ? Math.round((completados / totalInputs) * 100) : 0;

        $('#totalCount').text(totalInputs);
        $('#completedCount').text(completados);
        $('#pendingCount').text(totalInputs - completados);
        $('#progressPercentage').text(porcentaje + '%');
        $('#progressBarFill').css('width', porcentaje + '%');

        // Permitir guardado parcial mientras el registro esté habilitado
        $('#btnGuardarTodas').prop('disabled', !(registrosHabilitados && totalInputs > 0));
    }


    function abrirModalConclusion(registroId, matriculaId, competenciaId, alumnoNombre, competenciaNombre, notaValor) {
        $('#modalConclusionLabel').text('Conclusión Descriptiva');
        $('#conclusion_info').html(`
            <strong>Alumno:</strong> ${alumnoNombre}<br>
            <strong>Competencia:</strong> ${competenciaNombre}<br>
            <strong>Nota:</strong> ${notaValor}
        `);
        $('#conclusion_registro_id').val(registroId);
        $('#conclusion_matricula_id').val(matriculaId);
        $('#conclusion_competencia_id').val(competenciaId);
        $('#conclusion_texto').val('');
        $('#btnGuardarConclusion').data('nota', notaValor || '');
        
        // Si ya existe un registro, cargar la conclusión existente
        if (registroId) {
            // Buscar en los datos existentes
            for (let matId in registrosData) {
                for (let compId in registrosData[matId]) {
                    if (registrosData[matId][compId].id == registroId && registrosData[matId][compId].conclusion) {
                        $('#conclusion_texto').val(registrosData[matId][compId].conclusion);
                        break;
                    }
                }
            }
        }
        
        $('#modalConclusion').modal('show');
    }

    // Guardar conclusión
    $('#btnGuardarConclusion').on('click', function() {
        let registroId = $('#conclusion_registro_id').val();
        let matriculaId = $('#conclusion_matricula_id').val();
        let competenciaId = $('#conclusion_competencia_id').val();
        let conclusion = $('#conclusion_texto').val();
        let periodoId = $('#periodo_id').val();
        let nota = ($('#btnGuardarConclusion').data('nota') || '').toString().trim();
        
        if (!conclusion.trim()) {
            toast.warning('Por favor ingrese una conclusión');
            return;
        }
        
        $.ajax({
            url: '{{ route("admin.registro-competencias-transversales.save-conclusion") }}',
            method: 'POST',
            data: {
                registro_id: registroId,
                matricula_id: matriculaId,
                competencia_id: competenciaId,
                periodo_id: periodoId,
                nota: nota,
                conclusion: conclusion,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // 🔥 Usar toast en lugar de Swal.fire
                    toast.success('Conclusión guardada correctamente');
                    
                    $('#modalConclusion').modal('hide');

                    actualizarConclusionEnPantalla(
                        matriculaId,
                        competenciaId,
                        conclusion,
                        response.registro_id || registroId,
                        nota
                    );
                }
            },
            error: function(xhr) {
                toast.error(xhr.responseJSON?.message || 'Error al guardar la conclusión');
            }
        });
    });



});


</script>
@endsection