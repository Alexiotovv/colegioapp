@extends('layouts.app')

@section('title', 'Registro de Orden de Méritos')

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

    .table-meritos {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .table-meritos th,
    .table-meritos td {
        padding: 10px 8px;
        vertical-align: middle;
        text-align: center;
        border: 1px solid #dee2e6;
    }

    .table-meritos th {
        background-color: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-meritos th:first-child,
    .table-meritos td:first-child {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 11;
        min-width: 60px;
    }

    .table-meritos th:nth-child(2),
    .table-meritos td:nth-child(2) {
        position: sticky;
        left: 60px;
        background-color: white;
        z-index: 11;
        min-width: 120px;
    }

    .table-meritos th:nth-child(3),
    .table-meritos td:nth-child(3) {
        position: sticky;
        left: 180px;
        background-color: white;
        z-index: 11;
        min-width: 220px;
    }

    .table-meritos th:first-child,
    .table-meritos th:nth-child(2),
    .table-meritos th:nth-child(3) {
        background-color: #f8f9fa;
        z-index: 12;
    }

    .merito-select {
        width: 140px;
        padding: 6px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        background-color: white;
        cursor: pointer;
    }

    .merito-select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(26, 71, 42, 0.25);
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

    .registro-guardado {
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
        min-width: 220px;
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

    .table-responsive {
        overflow: visible !important;
    }

    .table-container {
        overflow: visible !important;
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

    .badge-nota {
        background-color: #eef2f7;
        color: #34495e;
        border-radius: 14px;
        padding: 2px 8px;
        margin-right: 4px;
        display: inline-block;
    }
</style>
@endsection

@section('content')
@include('partials.toast')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-award me-2" style="color: var(--primary-color);"></i>
            Registro de Orden de Méritos
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
                <select class="form-select" id="periodo_id" required>
                    <option value="">Seleccionar periodo</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}" data-activo="{{ $periodo->activo ? '1' : '0' }}">
                            {{ $periodo->nombre }} - {{ $periodo->anioAcademico->anio ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12 text-end d-flex justify-content-end gap-2 flex-wrap">
                <button class="btn btn-outline-primary" id="btnCalcularOrdenAutomatico" style="display:none;">
                    <i class="fas fa-calculator me-2"></i> Calcular orden automático
                </button>
            </div>
        </div>
    </div>

    @include('partials.progress-bar')

    <div class="table-container" id="tablaContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-award me-2"></i>
                Registro de Orden de Méritos
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

        <div class="alert alert-warning" id="infoPrimaria" style="display: none;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            En <strong>Primaria</strong> solo se registra <strong>un alumno</strong> como primer lugar.
        </div>

        <div class="alert alert-light border" id="infoNotasConfig" style="display: none;">
            <i class="fas fa-tags me-2"></i>
            Escala configurada en módulo de notas: <span id="notasConfigValores">-</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-meritos" id="tablaMeritos">
                <thead id="tablaHeader"></thead>
                <tbody id="tablaBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="fab-container">
    <button class="fab-button" id="fabButton">
        <i class="fas fa-ellipsis-h"></i>
    </button>
    <div class="fab-menu" id="fabMenu">
        <button class="fab-menu-item" id="btnGuardarTodos">
            <i class="fas fa-save"></i>
            <span>Guardar todos los registros</span>
        </button>
        <button class="fab-menu-item" id="btnDescargarExcelOrdenMerito">
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
    let tiposOrdenMeritoData = [];
    let registrosData = {};
    let registrosHabilitados = false;
    let aulaEsPrimaria = false;
    let aulaEsSecundaria = false;
    let primerTipoId = null;
    let tiposNotasConfig = [];
    let esAdmin = {{ auth()->user()->rol === 'admin' || (auth()->user()->role && auth()->user()->role->nombre === 'admin') ? 'true' : 'false' }};

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

    function cargarOpcionesNotasConfig() {
        return $.ajax({
            url: '{{ route("admin.registro-orden-meritos.opciones") }}',
            method: 'GET',
            success: function(response) {
                // Aunque la configuración del módulo pueda existir, para Orden de Mérito
                // requerimos siempre valor numérico entero entre 1 y 40.
                tiposNotasConfig = response || [];
                $('#notasConfigValores').html('<strong>NUMÉRICO:</strong> 1 - 40');
                $('#infoNotasConfig').show();
            },
            error: function() {
                tiposNotasConfig = [];
                $('#notasConfigValores').html('<strong>NUMÉRICO:</strong> 1 - 40');
                $('#infoNotasConfig').show();
            }
        });
    }

    function limpiarEstadoTabla() {
        matriculasData = [];
        tiposOrdenMeritoData = [];
        registrosData = {};
        registrosHabilitados = false;
        aulaEsPrimaria = false;
        aulaEsSecundaria = false;
        primerTipoId = null;

        $('#tablaBody').empty();
        $('#tablaHeader').empty();
        $('#tablaContainer').hide();
        $('#infoPeriodo').hide();
        $('#infoPrimaria').hide();
        $('#infoNotasConfig').hide();
        $('#totalCount').text('0');
        $('#completedCount').text('0');
        $('#pendingCount').text('0');
        $('#progressPercentage').text('0%');
        $('#progressBarFill').css('width', '0%');
        $('#btnGuardarTodos').prop('disabled', true);
        $('#btnCalcularOrdenAutomatico').hide();
    }

    function cargarRegistros() {
        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();

        if (!aulaId || !periodoId) {
            limpiarEstadoTabla();
            return;
        }

        let $btn = $('#btnCargarRegistros');
        if ($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="loading-spinner me-2"></span> Cargando...');
        }

        $.ajax({
            url: '{{ route("admin.registro-orden-meritos.get-data") }}',
            method: 'GET',
            data: {
                aula_id: aulaId,
                periodo_id: periodoId
            },
            success: function(response) {
                matriculasData = response.matriculas || [];
                tiposOrdenMeritoData = response.tipos_orden_merito || [];
                registrosData = response.registros || {};
                registrosHabilitados = response.registros_habilitados || false;
                aulaEsPrimaria = response.aula_es_primaria || false;
                aulaEsSecundaria = response.aula_es_secundaria || false;
                primerTipoId = response.primer_tipo_id || null;

                if (esAdmin) {
                    $('#toggleHabilitacion').prop('checked', registrosHabilitados);
                    $('#habilitacionLabel').text(registrosHabilitados ? 'Registro habilitado' : 'Registro deshabilitado');
                }

                let periodoNombre = $('#periodo_id option:selected').text();
                $('#infoPeriodoText').html(`<strong>Periodo:</strong> ${periodoNombre} - <strong>Estado:</strong> ${registrosHabilitados ? '<span class="badge-habilitado">HABILITADO</span>' : '<span class="badge-deshabilitado">DESHABILITADO</span>'}`);
                $('#infoPeriodo').show();
                $('#infoPrimaria').toggle(aulaEsPrimaria);
                $('#btnCalcularOrdenAutomatico').toggle(aulaEsSecundaria);

                // Cargar configuración de notas primero, luego renderizar tabla para que los inputs se construyan correctamente
                cargarOpcionesNotasConfig().then(function() {
                    renderTabla();
                    $('#tablaContainer').show();
                }).catch(function() {
                    // Si falla la carga de configuración, seguimos mostrando la tabla sin inputs de nota
                    renderTabla();
                    $('#tablaContainer').show();
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar datos', 'error');
            },
            complete: function() {
                if ($btn.length) {
                    $btn.prop('disabled', false);
                    $btn.html('<i class="fas fa-search me-2"></i> Cargar Registros');
                }
            }
        });
    }

    $('#aula_id').on('change', function() {
        $('#periodo_id').val('');
        limpiarEstadoTabla();
    });

    $('#periodo_id').on('change', function() {
        cargarRegistros();
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

        if (!tiposOrdenMeritoData || tiposOrdenMeritoData.length === 0) {
            $('#tablaBody').html(`
                <tr>
                    <td colspan="4" class="text-center text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay tipos de orden de mérito configurados para este nivel.
                    </td>
                </tr>
            `);
            return;
        }

        let headerHtml = `
            <tr>
                <th style="min-width: 60px;">N°</th>
                <th style="min-width: 150px;">Código</th>
                <th style="min-width: 250px;">Alumno</th>
                <th style="min-width: 180px;">Orden de Mérito</th>
            </tr>
        `;
        $('#tablaHeader').html(headerHtml);

        let opciones = tiposOrdenMeritoData;
        if (aulaEsPrimaria && primerTipoId) {
            opciones = tiposOrdenMeritoData.filter(item => Number(item.id) === Number(primerTipoId));
        }

        let bodyHtml = '';
        let contador = 1;

        for (let matricula of matriculasData) {
            let registro = registrosData[matricula.id];
            let tipoId = registro ? registro.tipo_orden_merito_id : '';
            let registroGuardado = tipoId ? 'registro-guardado' : '';

            // Construir HTML para la columna de orden y nota: siempre input numérico entero 1..40
            let notaVal = registro && (registro.nota_valor !== undefined && registro.nota_valor !== null) ? registro.nota_valor : '';
            let notaHtml = `
                <div style="margin-top:8px;">
                    <input type="number" step="1" class="form-control form-control-sm nota-input" data-matricula="${matricula.id}"
                           value="${notaVal || ''}" min="1" max="40" style="width:120px; margin:0 auto;">
                </div>
                <input type="hidden" class="nota-valor" data-matricula="${matricula.id}" value="${notaVal || ''}">
            `;

            bodyHtml += `
                <tr>
                    <td><strong>${contador}</strong></td>
                    <td>${matricula.alumno.codigo_estudiante || 'N/A'}</td>
                    <td style="text-align: left;">
                        <strong>${matricula.alumno.apellido_paterno || ''} ${matricula.alumno.apellido_materno || ''}</strong><br>
                        <small>${matricula.alumno.nombres || ''}</small>
                    </td>
                    <td>
                        ${notaHtml}
                    </td>
                </tr>
            `;
            contador++;
        }

        $('#tablaBody').html(bodyHtml);

        // No usamos selects para orden; inicializamos estado de notas

        if (aulaEsPrimaria && registrosHabilitados) {
            normalizarPrimariaAlCargar();
        }

        // Inicializar y manejar inputs/selects de nota (según configuración)
        $('.nota-valor').each(function() {
            $(this).data('initial', $(this).val() || '');
            let inicial = $(this).data('initial');
            let $cell = $(this).closest('td');
            if ($(this).val() !== inicial) {
                $cell.find('.select-wrapper').addClass('modified');
            }
        });

        // No usamos selects literal: nota siempre es numérica (1..40)

        $('.nota-input').off('input').on('input', function() {
            let $input = $(this);
            let raw = $input.val();
            let $hidden = $input.closest('td').find('.nota-valor');
            let min = parseInt($input.attr('min') || '1', 10);
            let max = parseInt($input.attr('max') || '40', 10);

            if (raw === '') {
                $hidden.val('');
                $input.val('');
            } else {
                // Forzar entero y eliminar decimales
                let intVal = parseInt(raw, 10);
                if (isNaN(intVal)) {
                    intVal = '';
                    $input.val('');
                } else {
                    if (intVal < min) intVal = min;
                    if (intVal > max) intVal = max;
                    $input.val(intVal);
                }
                $hidden.val(intVal === '' ? '' : intVal);
            }

            if (aulaEsPrimaria && registrosHabilitados) {
                aplicarReglaPrimariaDesdeInput($input);
            }

            let inicial = $hidden.data('initial') || '';
            let $cell = $input.closest('td');
            let current = $hidden.val() || '';
            if ((current || '') !== (inicial || '')) {
                $cell.find('.select-wrapper').addClass('modified');
            } else {
                $cell.find('.select-wrapper').removeClass('modified');
            }
        });

        aplicarReglaPrimaria();
        actualizarProgreso();
    }

    function normalizarPrimariaAlCargar() {
        let $inputs = $('.nota-input');
        let $llenos = $inputs.filter(function() {
            return ($(this).val() || '').toString().trim() !== '';
        });

        if ($llenos.length <= 1) {
            return; // Solo 1 o ninguno es válido
        }

        // Si hay múltiples, dejar el primero y limpiar el resto
        let $primero = $llenos.first();
        $inputs.not($primero).each(function() {
            $(this).val('');
            $(this).closest('td').find('.nota-valor').val('');
        });
    }

    function aplicarReglaPrimaria() {
        // Usamos el input numérico para la regla: en Primaria solo un alumno puede tener valor (primer lugar)
        let $inputs = $('.nota-input');

        if (!aulaEsPrimaria) {
            // En secundaria, deshabilitar siempre
            $inputs.prop('disabled', true);
            return;
        }

        if (!registrosHabilitados) {
            $inputs.prop('disabled', true);
            return;
        }

        $inputs.prop('disabled', false);
    }

    function aplicarReglaPrimariaDesdeInput($inputActivo) {
        if (!aulaEsPrimaria || !registrosHabilitados) {
            return;
        }

        let valorActual = ($inputActivo.val() || '').toString().trim();
        let $hiddenActivo = $inputActivo.closest('td').find('.nota-valor');

        if (valorActual === '') {
            $hiddenActivo.val('');
            $inputActivo.closest('td').css('background-color', '');
            return;
        }

        // En primaria cualquier valor escrito se normaliza a 1
        $inputActivo.val(1);
        $hiddenActivo.val(1);

        // RESALTE VERDE SOLO cuando el usuario modifica manualmente
        $inputActivo.closest('td').css('background-color', '#d4edda');

        // Limpiar otros y quitar su resalte
        $('.nota-input').not($inputActivo).each(function() {
            $(this).val('');
            $(this).closest('td').find('.nota-valor').val('');
            $(this).closest('td').css('background-color', '');
        });
    }

    function actualizarProgreso() {
        let totalInputs = $('.nota-valor').length;
        let completados = 0;

        $('.nota-valor').each(function() {
            let valor = $(this).val();
            if (valor !== null && valor !== undefined && valor.toString().trim() !== '') {
                completados++;
            }
        });

        let porcentaje = totalInputs > 0 ? Math.round((completados / totalInputs) * 100) : 0;

        $('#totalCount').text(totalInputs);
        $('#completedCount').text(completados);
        $('#pendingCount').text(totalInputs - completados);
        $('#progressPercentage').text(porcentaje + '%');
        $('#progressBarFill').css('width', porcentaje + '%');

        $('#btnGuardarTodos').prop('disabled', !(registrosHabilitados && totalInputs > 0));
    }

    function guardarTodosLosRegistros() {
        if (!registrosHabilitados) {
            Swal.fire('Error', 'El registro de orden de mérito no está habilitado', 'error');
            return;
        }

        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();
        let registros = [];

        $('.nota-valor').each(function() {
            let $hidden = $(this);
            let matriculaId = $hidden.data('matricula');
            let notaVal = $hidden.val();

            registros.push({
                matricula_id: matriculaId,
                tipo_orden_merito_id: null,
                nota_valor: notaVal ? parseInt(notaVal, 10) : null,
                observacion: ''
            });
        });

        let btn = $('#btnGuardarTodos');
        let originalHtml = btn.html();
        btn.prop('disabled', true);
        btn.html('<span class="loading-spinner me-2"></span> Guardando...');

        $.ajax({
            url: '{{ route("admin.registro-orden-meritos.save") }}',
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
                        cargarRegistros();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    }

    function descargarOrdenMeritoExcel() {
        const aulaId = $('#aula_id').val();
        const periodoId = $('#periodo_id').val();

        if (!aulaId || !periodoId) {
            Swal.fire('Advertencia', 'Seleccione aula y periodo.', 'warning');
            return;
        }

        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("admin.registro-orden-meritos.export-excel") }}',
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

    $('#btnGuardarTodos').on('click', guardarTodosLosRegistros);
    $('#btnDescargarExcelOrdenMerito').on('click', descargarOrdenMeritoExcel);

    $('#btnCalcularOrdenAutomatico').on('click', function() {
        if (!aulaEsSecundaria) {
            return;
        }

        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();

        if (!aulaId || !periodoId) {
            Swal.fire('Error', 'Seleccione un aula y un periodo', 'error');
            return;
        }

        let $btn = $(this);
        let htmlOriginal = $btn.html();
        $btn.prop('disabled', true);
        $btn.html('<span class="loading-spinner me-2"></span> Calculando...');

        $.ajax({
            url: '{{ route("admin.registro-orden-meritos.calcular-automatico") }}',
            method: 'POST',
            data: {
                aula_id: aulaId,
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success && Array.isArray(response.ranking)) {
                    response.ranking.forEach(function(item) {
                        let $input = $(`.nota-input[data-matricula="${item.matricula_id}"]`);
                        let $hidden = $(`.nota-valor[data-matricula="${item.matricula_id}"]`);
                        $input.val(item.orden_merito);
                        $hidden.val(item.orden_merito);
                    });

                    actualizarProgreso();
                    Swal.fire('Éxito', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message || 'No se pudo calcular el orden', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo calcular el orden', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btn.html(htmlOriginal);
            }
        });
    });

    $('#toggleHabilitacion').on('change', function() {
        if (!esAdmin) {
            Swal.fire('Error', 'No tienes permisos para realizar esta acción', 'error');
            $(this).prop('checked', !$(this).is(':checked'));
            return;
        }

        let habilitado = $(this).is(':checked');
        let periodoId = $('#periodo_id').val();

        if (!periodoId) {
            $(this).prop('checked', !habilitado);
            return;
        }

        $.ajax({
            url: '{{ route("admin.registro-orden-meritos.toggle-habilitacion") }}',
            method: 'POST',
            data: {
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    registrosHabilitados = response.habilitado;
                    $('#habilitacionLabel').text(registrosHabilitados ? 'Registro habilitado' : 'Registro deshabilitado');
                    aplicarReglaPrimaria();
                    actualizarProgreso();
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
