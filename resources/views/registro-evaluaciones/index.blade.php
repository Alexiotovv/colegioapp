
@extends('layouts.app')

@section('title', 'Registro de Evaluaciones')

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
        position: relative;
    }

    .table-evaluaciones {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .table-evaluaciones th, 
    .table-evaluaciones td {
        padding: 10px 8px;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }
    .table-evaluaciones tbody tr:hover {
        background-color: #f8f9fa;
    }
    .table-evaluaciones td:first-child,
    .table-evaluaciones th:first-child {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 11;
    }
    .table-evaluaciones th:nth-child(2),
    .table-evaluaciones td:nth-child(2) {
        position: sticky;
        left: 60px;
        background-color: white;
        z-index: 11;
        min-width: 120px;
    }
    .table-evaluaciones th:nth-child(3),
    .table-evaluaciones td:nth-child(3) {
        position: sticky;
        left: 180px;
        background-color: white;
        z-index: 11;
        min-width: 220px;
    }

    .table-evaluaciones td:first-child::after,
    .table-evaluaciones th:first-child::after,
    .table-evaluaciones td:nth-child(2)::after,
    .table-evaluaciones th:nth-child(2)::after,
    .table-evaluaciones td:nth-child(3)::after,
    .table-evaluaciones th:nth-child(3)::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: 4px;
        box-shadow: inset -2px 0 5px -2px rgba(0,0,0,0.1);
    }

    .table-evaluaciones th:first-child,
    .table-evaluaciones th:nth-child(2),
    .table-evaluaciones th:nth-child(3) {
        background-color: #f8f9fa;
        z-index: 12;
    }


    .table-evaluaciones th:first-child {
        background-color: #f8f9fa;
        z-index: 12;
    }
    
    .valoracion-select {
        width: 90px;
        padding: 4px 6px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        background-color: white;
        cursor: pointer;
    }
    .valoracion-select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(26, 71, 42, 0.25);
    }
    .valoracion-select.modified {
        position: relative;
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
    
    .comentario-input {
        width: 200px;
        padding: 6px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        font-size: 12px;
    }
    
    .registro-guardado {
        background-color: #d4edda;
        border-color: #28a745;
    }
    .comentario-input:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(26, 71, 42, 0.25);
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
    table-evaluaciones th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
    }

    .table-evaluaciones th:first-child {
        z-index: 13;
    }

    .table-evaluaciones th:nth-child(2) {
        z-index: 13;
    }

    .table-evaluaciones th:nth-child(3) {
        z-index: 13;
    }

    .registro-guardado {
        background-color: #d4edda;
        border-color: #28a745;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .badge-siempre { background-color: #28a745; color: white; }
    .badge-casisiempre { background-color: #17a2b8; color: white; }
    .badge-algunasveces { background-color: #ffc107; color: #333; }
    .badge-nunca { background-color: #fd7e14; color: white; }

    
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
    @media (max-width: 768px) {
        .valoracion-select {
            width: 90px;
            font-size: 11px;
            padding: 3px 4px;
        }
        
        .comentario-input {
            width: 150px;
            font-size: 11px;
        }
        
        .table-evaluaciones th,
        .table-evaluaciones td {
            padding: 6px 4px;
            font-size: 11px;
        }
        
        .table-evaluaciones th:nth-child(3),
        .table-evaluaciones td:nth-child(3) {
            min-width: 180px;
        }
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
    .select-wrapper {
        position: relative;
        display: inline-block;
        z-index: 20; /* 👈 clave */
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
        z-index: 30; /* 👈 MÁS alto que la tabla */
    }
</style>
@endsection

@section('content')
@include('partials.toast')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-clipboard-list me-2" style="color: var(--primary-color);"></i>
            Registro de Evaluaciones del Padre de  Familia
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
                <button class="btn btn-primary" id="btnCargarRegistros">
                    <i class="fas fa-search me-2"></i> Cargar Evaluaciones
                </button>
            </div>
        </div>
    </div>
    
    @include('partials.progress-bar')

    <div class="table-container" id="tablaContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-clipboard-list me-2"></i>
                Registro de Evaluaciones
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
                <thead id="tablaHeader">
                    <tr>
                        <th style="min-width: 60px;">N°</th>
                        <th style="min-width: 150px;">Código</th>
                        <th style="min-width: 250px;">Alumno</th>
                        <!-- Las evaluaciones se agregarán dinámicamente -->
                        <th style="min-width: 250px;">Comentario General</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
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
            <span>Guardar todas las evaluaciones</span>
        </button>
        <button class="fab-menu-item" id="btnImprimirTodo">
            <i class="fas fa-print"></i>
            <span>Imprimir reporte</span>
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
    let registrosHabilitados = false;
    let esAdmin = {{ auth()->user()->rol === 'admin' || (auth()->user()->role && auth()->user()->role->nombre === 'admin') ? 'true' : 'false' }};
    
    // Valoraciones - cargadas dinámicamente
    let valoraciones = {};

    // Función para cargar valoraciones desde el servidor
    function cargarValoraciones() {
        $.ajax({
            url: '{{ route("admin.registro-evaluaciones.opciones") }}',
            method: 'GET',
            async: false,
            success: function(response) {
                if (response && response.length > 0) {
                    valoraciones = {};
                    for (let item of response) {
                        valoraciones[item.codigo] = item.nombre;
                    }
                    console.log('Valoraciones cargadas:', valoraciones);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar valoraciones:', xhr);
                // Valoraciones por defecto
                valoraciones = {
                    'SIEMPRE': 'Siempre',
                    'CASI SIEMPRE': 'Casi Siempre',
                    'ALGUNAS VECES': 'Algunas Veces',
                    'NUNCA': 'Nunca'
                };
            }
        });
    }

    // Cargar valoraciones al inicio
    cargarValoraciones();
    
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
    
    $('#btnCargarRegistros').on('click', function() {
        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();
        
        if (!aulaId || !periodoId) {
            Swal.fire('Error', 'Complete todos los campos', 'error');
            return;
        }
        
        $('#btnCargarRegistros').prop('disabled', true);
        $('#btnCargarRegistros').html('<span class="loading-spinner me-2"></span> Cargando...');
        
        $.ajax({
            url: '{{ route("admin.registro-evaluaciones.get-data") }}',
            method: 'GET',
            data: {
                aula_id: aulaId,
                periodo_id: periodoId
            },
            success: function(response) {

                matriculasData = response.matriculas || [];
                evaluacionesData = response.evaluaciones || [];
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
                progressBar.show().update();
                $('#tablaContainer').show();
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar datos', 'error');
            },
            complete: function() {
                $('#btnCargarRegistros').prop('disabled', false);
                $('#btnCargarRegistros').html('<i class="fas fa-search me-2"></i> Cargar Evaluaciones');
            }
        });
    });
    
    // Inicializar Progress Bar
    progressBar
        .init('progressContainer', '.valoracion-select')
        .show()
        .onComplete(function() {
            toast.success('¡Todos los registros completados!');
        })
        .onUpdate(function(porcentaje, completados, total) {
            console.log(`Progreso: ${porcentaje}% (${completados}/${total})`);
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
            $('#progressContainer').hide();
            return;
        }
        
        if (!evaluacionesData || evaluacionesData.length === 0) {
            $('#tablaBody').html(`
                <tr>
                    <td colspan="4" class="text-center text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No hay evaluaciones registradas.
                    </td>
                </tr>
            `);
            $('#progressContainer').hide();
            return;
        }
        
        $('#progressContainer').show();
        
        // Header
        let headerHtml = `
            <tr>
                <th style="min-width: 60px;">N°</th>
                <th style="min-width: 150px;">Código</th>
                <th style="min-width: 250px;">Alumno</th>
        `;
        
        for (let evaluacion of evaluacionesData) {
            headerHtml += `<th colspan="1">${evaluacion.nombre}<br><small class="text-muted">${evaluacion.nivel ? evaluacion.nivel.nombre : ''}</small></th>`;
        }
        headerHtml += `</tr>`;
        
        $('#tablaHeader').html(headerHtml);
        
        // Body
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
            
            for (let evaluacion of evaluacionesData) {
                let registro = registrosAlumno[evaluacion.id];
                let valoracionValue = registro ? registro.valoracion : '';
                
                bodyHtml += `
                    <td style="text-align: center;">
                        <div class="select-wrapper">
                            <select class="form-select valoracion-select" data-matricula="${matricula.id}" data-evaluacion="${evaluacion.id}" ${!registrosHabilitados ? 'disabled' : ''} style="width: 110px; margin: 0 auto;">
                                <option value="">Seleccionar</option>
                                ${Object.keys(valoraciones).map(key => `<option value="${key}" ${valoracionValue === key ? 'selected' : ''}>${valoraciones[key]}</option>`).join('')}
                            </select>
                        </div>
                    </td>
                `;
            }
            bodyHtml += `</tr>`;
            contador++;
        }
        
        $('#tablaBody').html(bodyHtml);
        
        // Marcar selects guardados
        $('.valoracion-select').each(function() {
            if ($(this).val()) {
                $(this).addClass('registro-guardado');
            }
            $(this).data('initial', $(this).val() || '');
        });
        
        // Evento change único
        $('.valoracion-select').off('change').on('change', function() {
            let $select = $(this);
            let valor = $select.val();
            
            if (valor) {
                $select.addClass('registro-guardado');
            } else {
                $select.removeClass('registro-guardado');
            }
            
            // Marcar como modificado
            let inicial = $select.data('initial') || '';
            let wrapper = $select.closest('.select-wrapper');

            if (valor !== inicial) {
                wrapper.addClass('modified');
            } else {
                wrapper.removeClass('modified');
            }
            
            progressBar.update();
        });

        // Evento para comentario
        $('.comentario-input').on('input', function() {
            if ($(this).val()) {
                $(this).addClass('registro-guardado');
            } else {
                $(this).removeClass('registro-guardado');
            }
        });
    }
    
    function guardarTodasLasEvaluaciones() {
        if (!registrosHabilitados) {
            Swal.fire('Error', 'El registro de evaluaciones no está habilitado', 'error');
            return;
        }
        
        let registros = [];
        let periodoId = $('#periodo_id').val();
        
        // Recorrer solo los selects que tienen valor seleccionado
        $('.valoracion-select').each(function() {
            let valoracion = $(this).val();
            
            // Solo guardar si hay una valoración seleccionada
            if (valoracion) {
                let matriculaId = $(this).data('matricula');
                let evaluacionId = $(this).data('evaluacion');
                let comentario = $(this).closest('tr').find('.comentario-input').val();
                
                registros.push({
                    matricula_id: matriculaId,
                    evaluacion_id: evaluacionId,
                    valoracion: valoracion,
                    comentario: comentario || ''
                });
            }
        });
        
        if (registros.length === 0) {
            Swal.fire('Advertencia', 'No hay evaluaciones seleccionadas para guardar', 'warning');
            return;
        }
        
        let btn = $('#btnGuardarTodas');
        let originalHtml = btn.html();
        
        btn.prop('disabled', true);
        btn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: '{{ route("admin.registro-evaluaciones.save") }}',
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
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html(originalHtml);
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
                <td colspan="${evaluacionesData.length + 3}" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `);
        
        $.ajax({
            url: '{{ route("admin.registro-evaluaciones.get-data") }}',
            method: 'GET',
            data: {
                aula_id: aulaId,
                periodo_id: periodoId
            },
            success: function(response) {
                matriculasData = response.matriculas || [];
                evaluacionesData = response.evaluaciones || [];
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
                btn.html('<i class="fas fa-save"></i> Guardar todas las evaluaciones');
                $('#fabMenu').removeClass('show');
            }
        });
    }
    
    function imprimirReporte() {
        Swal.fire('Información', 'Funcionalidad de impresión en desarrollo', 'info');
        $('#fabMenu').removeClass('show');
    }
    
    // function actualizarProgreso() {
    //     let totalInputs = 0;
    //     let completados = 0;
        
    //     $('.nota-valor').each(function() {
    //         totalInputs++;
            
    //         let valor = $(this).val();

    //         // DEBUG (opcional)
    //         // console.log('Valor:', valor);

    //         if (valor !== null && valor !== undefined && valor.trim() !== '') {
    //             completados++;
    //         }
    //     });

    //     let porcentaje = totalInputs > 0 ? Math.round((completados / totalInputs) * 100) : 0;

    //     $('#totalCount').text(totalInputs);
    //     $('#completedCount').text(completados);
    //     $('#pendingCount').text(totalInputs - completados);
    //     $('#progressPercentage').text(porcentaje + '%');
    //     $('#progressBarFill').css('width', porcentaje + '%');

    //     if (porcentaje === 100) {
    //         $('#btnGuardarTodas').prop('disabled', false);
    //     } else {
    //         $('#btnGuardarTodas').prop('disabled', true);
    //     }
    // }


    $('#btnGuardarTodas').on('click', guardarTodasLasEvaluaciones);
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
            url: '{{ route("admin.registro-evaluaciones.toggle-habilitacion") }}',
            method: 'POST',
            data: {
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    registrosHabilitados = response.habilitado;
                    $('#habilitacionLabel').text(registrosHabilitados ? 'Registro habilitado' : 'Registro deshabilitado');
                    $('.valoracion-select, .comentario-input').prop('disabled', !registrosHabilitados);
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