{{-- resources/views/registro-evaluaciones-actitudinales/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Registro de Evaluaciones Actitudinales')

@section('css')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Ajuste suave de tamaño para filtros */
    #aula_id,
    #periodo_id {
        font-size: 0.88rem;
        padding-top: 0.3rem;
        padding-bottom: 0.3rem;
        min-height: calc(1.4em + 0.65rem + 2px);
    }

    #aula_id option,
    #periodo_id option {
        font-size: 0.88rem;
        padding: 4px 8px;
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow-x: auto;
    }

    .table-evaluaciones {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-evaluaciones th, .table-evaluaciones td {
        padding: 10px 8px;
        vertical-align: middle;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    
    .table-evaluaciones th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .table-evaluaciones td:first-child,
    .table-evaluaciones th:first-child {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 11;
    }
    
    .table-evaluaciones th:first-child {
        background-color: #f8f9fa;
        z-index: 12;
    }
    
    .valoracion-select {
        width: 120px;
        padding: 0.3rem 0.45rem;
        font-size: 0.88rem;
        border-radius: 6px;
        border: 1px solid #ced4da;
        background-color: white;
        cursor: pointer;
    }

    /* Progress Bar - compacto */
    .progress-container {
        background: white;
        border-radius: 12px;
        padding: 10px 16px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        display: none;
    }

    .progress-header {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        column-gap: 12px;
        margin-bottom: 8px;
        font-size: 11px;
        color: #555;
    }

    .progress-title {
        font-weight: 500;
        justify-self: start;
        white-space: nowrap;
    }

    .progress-percentage {
        font-weight: 600;
        color: var(--primary-color);
        justify-self: center;
        font-size: 12px;
        line-height: 1;
    }

    .progress-bar-container {
        background-color: #e9ecef;
        border-radius: 10px;
        height: 6px;
        overflow: hidden;
    }

    .progress-bar-fill {
        background-color: var(--primary-color);
        width: 0%;
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .progress-stats {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 0;
        font-size: 10px;
        color: #888;
        justify-self: end;
        white-space: nowrap;
    }

    .progress-stats span {
        display: flex;
        align-items: center;
        gap: 4px;
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

    @media (max-width: 992px) {
        .progress-header {
            grid-template-columns: 1fr auto;
            row-gap: 6px;
        }

        .progress-stats {
            grid-column: 1 / -1;
            justify-self: start;
            justify-content: flex-start;
            flex-wrap: wrap;
            white-space: normal;
        }
    }

    .select-wrapper {
        position: relative;
        display: inline-block;
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
    }
    
    .valoracion-guardada {
        background-color: #d4edda;
        border-color: #28a745;
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="me-2" style="color: var(--primary-color);"></i>
            Registro de Evaluaciones Actitudinales
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
                            {{ $aula->nombre }}
                            "{{ $aula->seccion->nombre ?? '' }}" ({{ $aula->turno_nombre }}) - {{ $aula->anioAcademico->anio ?? '' }}

                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="periodo_id" class="form-label required-field">Periodo</label>
                <select class="form-select" id="periodo_id" required disabled>
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
        
    </div>

    <div class="progress-container" id="progressContainer">
        <div class="progress-header">
            <span class="progress-title">
                <i class="fas fa-chart-line me-1"></i> Avance de registro
            </span>
            <span class="progress-percentage" id="progressPercentage">0%</span>
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
        <div class="progress-bar-container">
            <div class="progress-bar-fill" id="progressBarFill"></div>
        </div>
    </div>
    
    <!-- Tabla -->
    <div class="table-container" id="tablaContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                Evaluaciones Actitudinales
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
            <table class="table table-bordered table-evaluaciones" id="tablaEvaluaciones">
                <thead id="tablaHeader"></thead>
                <tbody id="tablaBody"></tbody>
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
            <span>Guardar todas las evaluaciones</span>
        </button>
        <button class="fab-menu-item" id="btnDescargarExcelActitudinal">
            <i class="fas fa-file-excel"></i>
            <span>Descargar Excel</span>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let matriculasData = [];
    let evaluacionesData = [];
    let registrosData = {};
    let registroHabilitado = false;
    let opcionesValoracion = []; // Se cargarán desde el servidor
    let esAdmin = {{ auth()->user()->rol === 'admin' || (auth()->user()->role && auth()->user()->role->nombre === 'admin') ? 'true' : 'false' }};
    
    var progressBar = {
        update: function() {
            let total = 0;
            let completados = 0;
            $('.valoracion-select').each(function() {
                total++;
                let valor = $(this).val();
                if (valor && valor !== '') {
                    completados++;
                }
            });
            let porcentaje = total > 0 ? Math.round((completados / total) * 100) : 0;
            $('#totalCount').text(total);
            $('#completedCount').text(completados);
            $('#pendingCount').text(total - completados);
            $('#progressPercentage').text(porcentaje + '%');
            $('#progressBarFill').css('width', porcentaje + '%');
        }
    };
    
    function limpiarTablaEvaluaciones() {
        matriculasData = [];
        evaluacionesData = [];
        registrosData = {};
        registroHabilitado = false;

        $('#tablaHeader').empty();
        $('#tablaBody').empty();
        $('#tablaContainer').hide();
        $('#progressContainer').hide();
        $('#infoPeriodo').hide();
        $('#toggleHabilitacion').prop('checked', false);
        $('#habilitacionLabel').text('Habilitar registro');
        $('#totalCount').text('0');
        $('#completedCount').text('0');
        $('#pendingCount').text('0');
        $('#progressPercentage').text('0%');
        $('#progressBarFill').css('width', '0%');
        $('#btnGuardarTodas').prop('disabled', true);
    }

    function actualizarEstadoBotonGuardar() {
        let puedeGuardar = registroHabilitado && matriculasData.length > 0 && evaluacionesData.length > 0;
        $('#btnGuardarTodas').prop('disabled', !puedeGuardar);
    }

    function cargarEvaluacionesAutomaticamente() {
        let aulaId = $('#aula_id').val();
        let periodoSelect = $('#periodo_id');
        let periodoId = $('#periodo_id').val();
        
        if (!aulaId || !periodoId) {
            limpiarTablaEvaluaciones();
            return;
        }
        
        $.ajax({
            url: '{{ route("admin.registro-evaluaciones-actitudinales.get-data") }}',
            method: 'GET',
            data: { aula_id: aulaId, periodo_id: periodoId },
            success: function(response) {
                matriculasData = response.matriculas || [];
                evaluacionesData = response.evaluaciones || [];
                registrosData = response.registros || {};
                registroHabilitado = response.registro_habilitado || false;
                opcionesValoracion = response.opciones_valoracion || [];
                
                if (esAdmin) {
                    $('#toggleHabilitacion').prop('checked', registroHabilitado);
                    $('#habilitacionLabel').text(registroHabilitado ? 'Registro habilitado' : 'Registro deshabilitado');
                }
                
                let periodoSelect = $('#periodo_id option:selected');
                let periodoNombre = periodoSelect.text();
                $('#infoPeriodoText').html(`<strong>Periodo:</strong> ${periodoNombre} - <strong>Estado:</strong> ${registroHabilitado ? '<span class="badge-habilitado">HABILITADO</span>' : '<span class="badge-deshabilitado">DESHABILITADO</span>'}`);
                $('#infoPeriodo').show();
                
                renderTabla();
                $('#tablaContainer').show();
                $('#progressContainer').show();
                actualizarEstadoBotonGuardar();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar datos', 'error');
            }
        });
    }

    $('#aula_id').on('change', function() {
        $('#periodo_id').val('').prop('disabled', !$(this).val());
        limpiarTablaEvaluaciones();
    });

    $('#periodo_id').on('change', function() {
        cargarEvaluacionesAutomaticamente();
    });
    
    function renderTabla() {
        if (!matriculasData || matriculasData.length === 0) {
            $('#tablaBody').html('<tr><td colspan="2" class="text-center">No hay estudiantes matriculados</td></tr>');
            return;
        }
        
        if (!evaluacionesData || evaluacionesData.length === 0) {
            $('#tablaBody').html('<tr><td colspan="2" class="text-center">No hay evaluaciones actitudinales configuradas para este nivel</td></tr>');
            return;
        }
        
        let headerHtml = '<tr><th style="min-width: 150px;">Alumno</th>';
        for (let evaluacion of evaluacionesData) {
            headerHtml += `<th>${evaluacion.nombre}</th>`;
        }
        headerHtml += '</tr>';
        $('#tablaHeader').html(headerHtml);
        
        let bodyHtml = '';
        for (let matricula of matriculasData) {
            bodyHtml += `<tr>
                <td style="text-align: left;">
                    <strong>${matricula.alumno.apellido_paterno || ''} ${matricula.alumno.apellido_materno || ''}</strong><br>
                    <small>${matricula.alumno.nombres || ''}</small>
                </td>`;
            
                for (let evaluacion of evaluacionesData) {
                let registro = registrosData[matricula.id] ? registrosData[matricula.id][evaluacion.id] : null;
                let valor = registro ? registro.valoracion : '';
                let guardada = valor ? 'valoracion-guardada' : '';
                
                bodyHtml += `
                    <td style="text-align: center;">
                        <div class="select-wrapper">
                            <input type="text"
                                   class="form-control form-control-sm valoracion-select valoracion-input ${guardada}"
                                   data-matricula="${matricula.id}"
                                   data-evaluacion="${evaluacion.id}"
                                   data-original-value="${valor || ''}"
                                   data-prev-value="${valor || ''}"
                                   value="${valor || ''}"
                                   maxlength="12"
                                   autocomplete="off"
                                   ${!registroHabilitado ? 'disabled' : ''}
                                   style="width: 130px; margin: 0 auto; display: inline-block; text-transform: uppercase;">
                        </div>
                    </td>
                `;
            }
            bodyHtml += '</tr>';
        }
        
        $('#tablaBody').html(bodyHtml);

        $('.valoracion-select').each(function() {
            $(this).data('initial', $(this).val() || '');
            let wrapper = $(this).closest('.select-wrapper');
            if ($(this).val() !== $(this).data('initial')) {
                wrapper.addClass('modified');
            } else {
                wrapper.removeClass('modified');
            }
        });

        function normalizarValoracion(valor) {
            return (valor || '').toString().trim().toUpperCase();
        }

        function opcionesValoracionNormalizadas() {
            return (opcionesValoracion || []).map(function(opcion) {
                return normalizarValoracion(opcion.codigo || opcion);
            });
        }

        function esValoracionExactaPermitida(valor) {
            let normalizada = normalizarValoracion(valor);
            if (!normalizada) return false;
            return opcionesValoracionNormalizadas().includes(normalizada);
        }

        function esPrefijoValoracionPermitido(valor) {
            let normalizada = normalizarValoracion(valor);
            if (normalizada === '') return true;
            return opcionesValoracionNormalizadas().some(function(op) {
                return op.startsWith(normalizada);
            });
        }

        function enfocarSiguienteInputValoracion(inputActual) {
            let $inputs = $('.valoracion-input:enabled');
            let index = $inputs.index(inputActual);
            if (index === -1) return;

            let $siguiente = $inputs.eq(index + 1);
            if ($siguiente.length) {
                $siguiente.focus().select();
            } else {
                $(inputActual).blur();
            }
        }

        function actualizarEstadoValoracionDesdeInput($input, valor, esExacta) {
            let valorNormalizado = normalizarValoracion(valor);
            let original = normalizarValoracion($input.attr('data-original-value') || '');
            let cambioReal = esExacta && valorNormalizado !== original;

            $input.val(valorNormalizado);

            if (esExacta) {
                $input.addClass('valoracion-guardada');
                $input.attr('data-prev-value', valorNormalizado);
                $input.data('prev-value', valorNormalizado);
            } else {
                $input.removeClass('valoracion-guardada');
            }

            let wrapper = $input.closest('.select-wrapper');
            if (cambioReal) {
                wrapper.addClass('modified');
            } else {
                wrapper.removeClass('modified');
            }

            progressBar.update();
        }

        $('.valoracion-input').off('input').on('input', function() {
            let $input = $(this);
            let valor = normalizarValoracion($input.val());

            if (!esPrefijoValoracionPermitido(valor)) {
                let previo = normalizarValoracion($input.data('prev-value') || '');
                $input.val(previo);
                valor = previo;
            }

            actualizarEstadoValoracionDesdeInput($input, valor, esValoracionExactaPermitida(valor));
        });

        $('.valoracion-input').off('blur').on('blur', function() {
            let $input = $(this);
            let valor = normalizarValoracion($input.val());

            if (!esValoracionExactaPermitida(valor)) {
                valor = '';
            }

            actualizarEstadoValoracionDesdeInput($input, valor, esValoracionExactaPermitida(valor));
        });

        $('.valoracion-input').off('focus').on('focus', function() {
            this.select();
        });

        $('.valoracion-input').off('click').on('click', function() {
            this.select();
        });

        $('.valoracion-input').off('keydown').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                enfocarSiguienteInputValoracion(this);
            }
        });
        
        progressBar.update();
    }
    
    function guardarTodas() {
        if (!registroHabilitado) {
            Swal.fire('Error', 'El registro no está habilitado', 'error');
            return;
        }
        
        let registros = [];
        $('.valoracion-select').each(function() {
            let valor = $(this).val();
            if (valor) {
                registros.push({
                    matricula_id: $(this).data('matricula'),
                    evaluacion_id: $(this).data('evaluacion'),
                    valoracion: valor
                });
            }
        });
        
        if (registros.length === 0) {
            Swal.fire('Advertencia', 'No hay evaluaciones para guardar', 'warning');
            return;
        }
        
        let btn = $('#btnGuardarTodas');
        let originalHtml = btn.html();
        btn.prop('disabled', true);
        btn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: '{{ route("admin.registro-evaluaciones-actitudinales.save") }}',
            method: 'POST',
            data: {
                registros: registros,
                periodo_id: $('#periodo_id').val(),
                aula_id: $('#aula_id').val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success');
                    cargarEvaluacionesAutomaticamente();
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
                $('#fabMenu').removeClass('show');
            }
        });
    }

    function descargarEvaluacionesExcel() {
        const aulaId = $('#aula_id').val();
        const periodoId = $('#periodo_id').val();

        if (!aulaId || !periodoId) {
            Swal.fire('Advertencia', 'Seleccione aula y periodo.', 'warning');
            return;
        }

        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("admin.registro-evaluaciones-actitudinales.export-excel") }}',
            style: 'display:none',
        });

        form.append($('<input>', { type: 'hidden', name: 'aula_id', value: aulaId }));
        form.append($('<input>', { type: 'hidden', name: 'periodo_id', value: periodoId }));
        form.append($('<input>', { type: 'hidden', name: '_token', value: $('meta[name="csrf-token"]').attr('content') }));

        $('body').append(form);
        form[0].submit();
        setTimeout(() => form.remove(), 1000);
        $('#fabMenu').removeClass('show');
    }
    
    $('#btnGuardarTodas').on('click', guardarTodas);
    $('#btnDescargarExcelActitudinal').on('click', descargarEvaluacionesExcel);
    
    $('#toggleHabilitacion').on('change', function() {
        if (!esAdmin) {
            Swal.fire('Error', 'No tienes permisos', 'error');
            $(this).prop('checked', !$(this).is(':checked'));
            return;
        }
        
        let habilitado = $(this).is(':checked');
        let periodoId = $('#periodo_id').val();
        if (!periodoId) return;
        
        $.ajax({
            url: '{{ route("admin.registro-evaluaciones-actitudinales.toggle-habilitacion") }}',
            method: 'POST',
            data: { periodo_id: periodoId, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    registroHabilitado = response.habilitado;
                    $('#habilitacionLabel').text(registroHabilitado ? 'Registro habilitado' : 'Registro deshabilitado');
                    actualizarEstadoBotonGuardar();
                    Swal.fire('Éxito', response.message, 'success');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error');
                $('#toggleHabilitacion').prop('checked', !habilitado);
            }
        });
    });
    
    $('#fabButton').on('click', function(e) {
        e.stopPropagation();
        $('#fabMenu').toggleClass('show');
    });
    
    $(document).on('click', function() { $('#fabMenu').removeClass('show'); });
    $('#fabMenu').on('click', function(e) { e.stopPropagation(); });
});
</script>
@endsection