
@extends('layouts.app')

@section('title', 'Registro de Asistencias e Inasistencias')

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
    
    .table-asistencias {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }
    
    .table-asistencias th, 
    .table-asistencias td {
        padding: 10px 8px;
        vertical-align: middle;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    
    .table-asistencias th {
        background-color: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    /* Columnas fijas */
    .table-asistencias th:first-child,
    .table-asistencias td:first-child {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 11;
        min-width: 60px;
    }
    
    .table-asistencias th:nth-child(2),
    .table-asistencias td:nth-child(2) {
        position: sticky;
        left: 60px;
        background-color: white;
        z-index: 11;
        min-width: 120px;
    }
    
    .table-asistencias th:nth-child(3),
    .table-asistencias td:nth-child(3) {
        position: sticky;
        left: 180px;
        background-color: white;
        z-index: 11;
        min-width: 220px;
    }
    
    .table-asistencias th:first-child,
    .table-asistencias th:nth-child(2),
    .table-asistencias th:nth-child(3) {
        background-color: #f8f9fa;
        z-index: 12;
    }
    
    /* Inputs de cantidad */
    .cantidad-input {
        width: 70px;
        padding: 6px;
        text-align: center;
        border-radius: 6px;
        border: 1px solid #ced4da;
    }
    
    .cantidad-input:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(26, 71, 42, 0.25);
    }

    .input-wrapper {
        position: relative;
        display: inline-block;
    }

    .input-wrapper.modified::after {
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
        background-color: var(--primary-color);
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-calendar-check me-2" style="color: var(--primary-color);"></i>
            Registro de Asistencias e Inasistencias
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
        
        <div class="row mt-3">
            <div class="col-md-12 text-end">
                {{-- Carga automática: seleccione Aula y luego Periodo para listar registros --}}
            </div>
        </div>
    </div>
    
    @include('partials.progress-bar')


    <div class="table-container" id="tablaContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-calendar-check me-2"></i>
                Registro de Asistencias e Inasistencias
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
            <table class="table table-bordered table-asistencias" id="tablaAsistencias">
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
        <button class="fab-menu-item" id="btnDescargarExcelAsistencias">
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
    let tiposData = [];
    let registrosData = {};
    let registrosHabilitados = false;
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
    
    // Estado inicial: limpiar tabla y deshabilitar periodo/guardar
    limpiarTablaAsistencias();
    
    // Limpia la tabla y el estado cuando cambia el aula
    function limpiarTablaAsistencias() {
        matriculasData = [];
        tiposData = [];
        registrosData = {};
        registrosHabilitados = false;
        $('#tablaBody').html('');
        $('#tablaHeader').html('');
        $('#tablaContainer').hide();
        $('#infoPeriodo').hide();
        progressBar.hide();
        actualizarEstadoBotonGuardar();
    }

    function cargarAsistenciasAutomaticamente() {
        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();
        if (!aulaId || !periodoId) return;

        $('#tablaBody').html(`
            <tr>
                <td colspan="3" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `);

        $.ajax({
            url: '{{ route("admin.registro-asistencias.get-data") }}',
            method: 'GET',
            data: { aula_id: aulaId, periodo_id: periodoId },
            success: function(response) {
                matriculasData = response.matriculas || [];
                tiposData = response.tipos_inasistencia || [];
                registrosData = response.registros || {};
                registrosHabilitados = response.registros_habilitados || false;

                if (esAdmin) {
                    $('#toggleHabilitacion').prop('checked', registrosHabilitados);
                    $('#habilitacionLabel').text(registrosHabilitados ? 'Registro habilitado' : 'Registro deshabilitado');
                }

                let periodoSelect = $('#periodo_id option:selected');
                let periodoNombre = periodoSelect.text();
                $('#infoPeriodoText').html(`<strong>Periodo:</strong> ${periodoNombre} - <strong>Estado:</strong> ${registrosHabilitados ? '<span class="badge bg-success">HABILITADO</span>' : '<span class="badge bg-secondary">DESHABILITADO</span>'}`);
                $('#infoPeriodo').show();

                renderTabla();
                $('#tablaContainer').show();
                actualizarEstadoBotonGuardar();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar datos', 'error');
            },
            complete: function() {
                actualizarEstadoBotonGuardar();
            }
        });
    }

    // Al cambiar aula: resetear periodo, limpiar tabla y habilitar/deshabilitar periodo
    $('#aula_id').on('change', function() {
        let aulaId = $(this).val();
        $('#periodo_id').val('');
        $('#periodo_id').prop('disabled', !aulaId);
        limpiarTablaAsistencias();
    });

    // Al cambiar periodo: cargar automáticamente
    $('#periodo_id').on('change', function() {
        if (!$('#aula_id').val()) return;
        if (!$(this).val()) {
            limpiarTablaAsistencias();
            return;
        }
        cargarAsistenciasAutomaticamente();
    });

    function actualizarEstadoBotonGuardar() {
        let btn = $('#btnGuardarTodas');
        if (registrosHabilitados && matriculasData.length > 0 && tiposData.length > 0) {
            btn.prop('disabled', false);
        } else {
            btn.prop('disabled', true);
        }
    }
    
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
            return;
        }
        
        if (!tiposData || tiposData.length === 0) {
            $('#tablaBody').html(`
                <tr>
                    <td colspan="3" class="text-center text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay tipos de inasistencia registrados.
                    </td>
                </tr>
            `);
            return;
        }
        
        // Renderizar header
        let headerHtml = `
            <tr>
                <th style="min-width: 60px;">N°</th>
                <th style="min-width: 120px;">Código</th>
                <th style="min-width: 220px;">Alumno</th>
        `;
        
        for (let tipo of tiposData) {
            headerHtml += `<th style="min-width: 100px;">${tipo.nombre}<br><small class="text-muted">${tipo.nivel ? tipo.nivel.nombre : ''}</small></th>`;
        }
        // headerHtml += `<th style="min-width: 200px;">Observación</th>`;
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
            
            for (let tipo of tiposData) {
                let registro = registrosAlumno[tipo.id];
                let cantidadValue = registro ? registro.cantidad : '';
                
                bodyHtml += `
                    <td>
                        <div class="input-wrapper">
                            <input type="number" class="form-control cantidad-input" data-matricula="${matricula.id}" data-tipo="${tipo.id}" value="${cantidadValue}" min="0" ${!registrosHabilitados ? 'disabled' : ''} style="width: 80px; margin: 0 auto; text-align: center;">
                        </div>
                    </td>
                `;
            }
            
            // Observación general
            // let observacionValue = '';
            // bodyHtml += `
            //     <td>
            //         <input type="text" class="form-control observacion-input" data-matricula="${matricula.id}" value="${observacionValue.replace(/"/g, '&quot;')}" placeholder="Observación..." ${!registrosHabilitados ? 'disabled' : ''}>
            //     </td>
            // `;
            bodyHtml += `</tr>`;
            contador++;
        }
        
        $('#tablaBody').html(bodyHtml);
        
        // Cargar observaciones existentes
        for (let matricula of matriculasData) {
            let registrosAlumno = registrosData[matricula.id] || {};
            let observacion = '';
            for (let tipoId in registrosAlumno) {
                if (registrosAlumno[tipoId].observacion) {
                    observacion = registrosAlumno[tipoId].observacion;
                    break;
                }
            }
            if (observacion) {
                $(`.observacion-input[data-matricula="${matricula.id}"]`).val(observacion);
                $(`.observacion-input[data-matricula="${matricula.id}"]`).addClass('registro-guardado');
            }
        }
        
        // Marcar inputs que tienen valor
        $('.cantidad-input').each(function() {
            if ($(this).val() !== '') {
                $(this).addClass('registro-guardado');
            }
            $(this).data('initial', $(this).val() || '');
            if ($(this).val() !== $(this).data('initial')) {
                $(this).closest('.input-wrapper').addClass('modified');
            }
        });
        
        $('.observacion-input').each(function() {
            if ($(this).val()) {
                $(this).addClass('registro-guardado');
            }
        });
        
        // Eventos
        $('.cantidad-input').on('input', function() {
            let $input = $(this);
            let $wrapper = $input.closest('.input-wrapper');
            let valor = $input.val() || '';
            let inicial = $input.data('initial') || '';

            if (valor !== '') {
                $input.addClass('registro-guardado');
            } else {
                $input.removeClass('registro-guardado');
            }

            if (valor !== inicial) {
                $wrapper.addClass('modified');
            } else {
                $wrapper.removeClass('modified');
            }
        });
        
        $('.observacion-input').on('input', function() {
            if ($(this).val()) {
                $(this).addClass('registro-guardado');
            } else {
                $(this).removeClass('registro-guardado');
            }
        });

        progressBar.update();
    }

    $(document).on('input', '.cantidad-input', function() {
        progressBar.update();
    });

    function guardarTodosLosRegistros() {
        if (!registrosHabilitados) {
            Swal.fire('Error', 'El registro de asistencias no está habilitado', 'error');
            return;
        }
        
        let registros = [];
        let periodoId = $('#periodo_id').val();
        
        $('.cantidad-input').each(function() {
            let cantidad = $(this).val();
            let matriculaId = $(this).data('matricula');
            let tipoId = $(this).data('tipo');
            let observacion = $(this).closest('tr').find('.observacion-input').val();
            
            if (cantidad !== '' && cantidad !== null) {
                registros.push({
                    matricula_id: matriculaId,
                    tipo_inasistencia_id: tipoId,
                    cantidad: parseInt(cantidad), // aquí 0 ya entra normal
                    observacion: observacion || ''
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
            url: '{{ route("admin.registro-asistencias.save") }}',
            method: 'POST',
            data: {
                registros: registros,
                periodo_id: periodoId,
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
                <td colspan="${tiposData.length + 3}" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `);
        
        $.ajax({
            url: '{{ route("admin.registro-asistencias.get-data") }}',
            method: 'GET',
            data: {
                aula_id: aulaId,
                periodo_id: periodoId
            },
            success: function(response) {
                matriculasData = response.matriculas || [];
                tiposData = response.tipos_inasistencia || [];
                registrosData = response.registros || {};
                registrosHabilitados = response.registros_habilitados || false;
                
                renderTabla();
                actualizarEstadoBotonGuardar();
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
    
    function imprimirReporte() {
        Swal.fire('Información', 'Funcionalidad de impresión en desarrollo', 'info');
        $('#fabMenu').removeClass('show');
    }

    function descargarAsistenciasExcel() {
        const aulaId = $('#aula_id').val();
        const periodoId = $('#periodo_id').val();

        if (!aulaId || !periodoId) {
            Swal.fire('Advertencia', 'Seleccione aula y periodo.', 'warning');
            return;
        }

        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("admin.registro-asistencias.export-excel") }}',
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
    
    $('#btnGuardarTodas').on('click', guardarTodosLosRegistros);
    $('#btnDescargarExcelAsistencias').on('click', descargarAsistenciasExcel);
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
            url: '{{ route("admin.registro-asistencias.toggle-habilitacion") }}',
            method: 'POST',
            data: {
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    registrosHabilitados = response.habilitado;
                    $('#habilitacionLabel').text(registrosHabilitados ? 'Registro habilitado' : 'Registro deshabilitado');
                    $('.cantidad-input, .observacion-input').prop('disabled', !registrosHabilitados);
                    actualizarEstadoBotonGuardar();
                    Swal.fire('Éxito', response.message, 'success');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar estado', 'error');
                $('#toggleHabilitacion').prop('checked', !habilitado);
            }
        });
    });

    progressBar
    .init('progressContainer', '.cantidad-input')
    .show()
    .onUpdate(function(p, c, t) {
        console.log(`Progreso: ${p}%`);
    });

});
</script>
@endsection