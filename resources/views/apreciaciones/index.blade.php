{{-- resources/views/apreciaciones/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Registro de Apreciaciones')

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
    
    .table-apreciaciones {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-apreciaciones th, .table-apreciaciones td {
        padding: 10px 8px;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }
    
    .table-apreciaciones th {
        background-color: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .table-apreciaciones td:first-child,
    .table-apreciaciones th:first-child {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 11;
    }
    
    .table-apreciaciones th:first-child {
        background-color: #f8f9fa;
        z-index: 12;
    }
    
    .apreciacion-textarea {
        width: 100%;
        min-width: 400px;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        resize: vertical;
        font-size: 12px;
    }

    .apreciacion-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .apreciacion-wrapper.modified::after {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        background: #dc3545;
        border-radius: 50%;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.85);
        z-index: 10;
    }
    
    .apreciacion-textarea:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(26, 71, 42, 0.25);
    }
    
    .apreciacion-guardada {
        background-color: #d4edda;
        border-color: #28a745;
    }
    
    .char-counter {
        font-size: 10px;
        color: #6c757d;
        margin-top: 3px;
        text-align: right;
    }
    
    .char-counter.warning {
        color: #f0ad4e;
    }
    
    .char-counter.danger {
        color: #d9534f;
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

</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-comment-dots me-2" style="color: var(--primary-color);"></i>
            Registro de Apreciaciones del Tutor
        </h4>
    </div>
    
    <!-- Filtros -->
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
                <!-- Botón de carga eliminado: la carga ahora se realiza automáticamente al seleccionar el periodo -->
            </div>
        </div>
    </div>
    
    @include('partials.progress-bar')

    <!-- Tabla de Apreciaciones -->
    <div class="table-container" id="tablaContainer" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-comment-dots me-2"></i>
                Apreciaciones del Tutor
            </h5>
            <div class="d-flex align-items-center gap-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Máximo {{ $maxCaracteres }} caracteres
                </small>
                @if(auth()->user()->rol === 'admin' || (auth()->user()->role && auth()->user()->role->nombre === 'admin'))
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggleHabilitacion" style="width: 50px; height: 25px;">
                    <label class="form-check-label ms-2" id="habilitacionLabel">Habilitar registro</label>
                </div>
                @endif
            </div>
        </div>
        
        <div class="alert alert-info" id="infoPeriodo" style="display: none;">
            <i class="fas fa-info-circle me-2"></i>
            <span id="infoPeriodoText"></span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-apreciaciones" id="tablaApreciaciones">
                <thead>
                    <tr>
                        <th style="min-width: 60px;">N°</th>
                        <th style="min-width: 150px;">Código</th>
                        <th style="min-width: 250px;">Alumno</th>
                        <th style="min-width: 400px;">Apreciación del Tutor</th>
                    </tr>
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
            <span>Guardar todas las apreciaciones</span>
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
    let apreciacionesData = {};
    let apreciacionesHabilitadas = false;
    let maxCaracteres = {{ $maxCaracteres }};
    let esAdmin = {{ auth()->user()->rol === 'admin' || (auth()->user()->role && auth()->user()->role->nombre === 'admin') ? 'true' : 'false' }};
    
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
    
    // Habilitar periodo al seleccionar aula y auto-cargar al escoger periodo
    $('#aula_id').on('change', function() {
        let aulaId = $(this).val();
        // Reset periodo selection and disable table until periodo chosen
        $('#periodo_id').prop('disabled', aulaId ? false : true);
        $('#periodo_id').val('');
        $('#infoPeriodo').hide();
        $('#tablaContainer').hide();
        matriculasData = [];
        apreciacionesData = {};
    });

    $('#periodo_id').on('change', function() {
        cargarApreciacionesAutomaticamente();
    });

    function cargarApreciacionesAutomaticamente() {
        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();

        if (!aulaId || !periodoId) {
            return;
        }

        // Indicar carga
        $('#tablaContainer').hide();

        $.ajax({
            url: '{{ route("admin.apreciaciones.get-data") }}',
            method: 'GET',
            data: {
                aula_id: aulaId,
                periodo_id: periodoId
            },
            success: function(response) {
                matriculasData = response.matriculas || [];
                apreciacionesData = response.apreciaciones || {};
                apreciacionesHabilitadas = response.apreciaciones_habilitadas || false;
                maxCaracteres = response.max_caracteres || {{ $maxCaracteres }};

                if (esAdmin) {
                    $('#toggleHabilitacion').prop('checked', apreciacionesHabilitadas);
                    $('#habilitacionLabel').text(apreciacionesHabilitadas ? 'Registro habilitado' : 'Registro deshabilitado');
                }

                let periodoSelect = $('#periodo_id option:selected');
                let periodoNombre = periodoSelect.text();
                $('#infoPeriodoText').html(`<strong>Periodo:</strong> ${periodoNombre} - <strong>Estado:</strong> ${apreciacionesHabilitadas ? '<span class="badge-habilitado">HABILITADO</span>' : '<span class="badge-deshabilitado">DESHABILITADO</span>'}`);
                $('#infoPeriodo').show();

                renderTabla();
                $('#tablaContainer').show();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar datos', 'error');
            }
        });
    }
    
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
        
        let bodyHtml = '';
        let contador = 1;
        
        for (let matricula of matriculasData) {
            let apreciacion = apreciacionesData[matricula.id];
            let apreciacionValue = apreciacion ? apreciacion.apreciacion : '';
            let caracteresRestantes = maxCaracteres - apreciacionValue.length;
            
            bodyHtml += `
                <tr>
                    <td><strong>${contador}</strong></td>
                    <td>${matricula.alumno.codigo_estudiante || 'N/A'}</td>
                    <td style="text-align: left;">
                        <strong>${matricula.alumno.apellido_paterno || ''} ${matricula.alumno.apellido_materno || ''}</strong><br>
                        <small>${matricula.alumno.nombres || ''}</small>
                     </td>
                    <td style="text-align: left;">
                        <div class="apreciacion-wrapper">
                            <textarea class="apreciacion-textarea" 
                                      data-matricula="${matricula.id}"
                                      rows="3"
                                      maxlength="${maxCaracteres}"
                                      ${!apreciacionesHabilitadas ? 'disabled' : ''}
                                      placeholder="Escriba aquí la apreciación del tutor...">${apreciacionValue}</textarea>
                        </div>
                        <div class="char-counter ${caracteresRestantes < 50 ? (caracteresRestantes < 10 ? 'danger' : 'warning') : ''}">
                            ${caracteresRestantes} caracteres restantes
                        </div>
                    </td>
                </tr>
            `;
            contador++;
        }
        
        $('#tablaBody').html(bodyHtml);
        
        // Contador de caracteres en tiempo real
        $('.apreciacion-textarea').on('input', function() {
            let valor = $(this).val();
            let caracteresRestantes = maxCaracteres - valor.length;
            let wrapper = $(this).closest('.apreciacion-wrapper');
            let counterDiv = wrapper.next('.char-counter');
            
            counterDiv.text(caracteresRestantes + ' caracteres restantes');
            counterDiv.removeClass('warning danger');
            
            if (caracteresRestantes < 10) {
                counterDiv.addClass('danger');
            } else if (caracteresRestantes < 50) {
                counterDiv.addClass('warning');
            }
            
            if (valor) {
                $(this).addClass('apreciacion-guardada');
            } else {
                $(this).removeClass('apreciacion-guardada');
            }
            let inicial = $(this).data('initial') || '';
            if (valor !== inicial) {
                wrapper.addClass('modified');
            } else {
                wrapper.removeClass('modified');
            }
        });
        
        // Marcar textareas con contenido guardado
        $('.apreciacion-textarea').each(function() {
            if ($(this).val()) {
                $(this).addClass('apreciacion-guardada');
            }
            $(this).data('initial', $(this).val() || '');
        });

        progressBar.update();
    }
    
    // Guardar todas las apreciaciones
    function guardarTodasLasApreciaciones() {
        if (!apreciacionesHabilitadas) {
            Swal.fire('Error', 'El registro de apreciaciones no está habilitado', 'error');
            return;
        }
        
        let apreciaciones = [];
        let periodoId = $('#periodo_id').val();
        let error = false;
        let emptyError = false;
        
        $('.apreciacion-textarea').each(function() {
            let apreciacion = $(this).val();
            let matriculaId = $(this).data('matricula');
            let apreciacionTrim = apreciacion ? apreciacion.trim() : '';
            
            if (!apreciacionTrim) {
                emptyError = true;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
            
            if (apreciacion && apreciacion.length > maxCaracteres) {
                error = true;
                return false;
            }
            
            apreciaciones.push({
                matricula_id: matriculaId,
                apreciacion: apreciacion || ''
            });
        });
        
        if (emptyError) {
            Swal.fire('Error', 'No es posible dejar la apreciación en blanco. Complete todos los campos antes de guardar.', 'error');
            return;
        }
        
        if (error) {
            Swal.fire('Error', `Las apreciaciones no pueden exceder los ${maxCaracteres} caracteres`, 'error');
            return;
        }
        
        if (apreciaciones.length === 0) {
            Swal.fire('Advertencia', 'No hay apreciaciones para guardar', 'warning');
            return;
        }
        
        let btn = $('#btnGuardarTodas');
        let originalHtml = btn.html();
        
        btn.prop('disabled', true);
        btn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: '{{ route("admin.apreciaciones.save") }}',
            method: 'POST',
            data: {
                apreciaciones: apreciaciones,
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success');
                    // Limpiar marcas modificadas y recargar automáticamente
                    $('.apreciacion-wrapper').removeClass('modified');
                    cargarApreciacionesAutomaticamente();
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar apreciaciones', 'error');
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
    
    // Asignar eventos a los botones del menú flotante
    $('#btnGuardarTodas').on('click', guardarTodasLasApreciaciones);
    $('#btnImprimirTodo').on('click', imprimirReporte);
    
    // Cambiar habilitación del registro de apreciaciones (solo admin)
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
            url: '{{ route("admin.apreciaciones.toggle-habilitacion") }}',
            method: 'POST',
            data: {
                periodo_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    apreciacionesHabilitadas = response.habilitado;
                    $('#habilitacionLabel').text(apreciacionesHabilitadas ? 'Registro habilitado' : 'Registro deshabilitado');
                    
                    // Habilitar/deshabilitar textareas
                    $('.apreciacion-textarea').prop('disabled', !apreciacionesHabilitadas);
                    
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
    .init('progressContainer', '.apreciacion-textarea')
    .show()
    .onUpdate(function(p, c, t) {
        console.log(`Progreso: ${p}%`);
    });

    $(document).on('input', '.apreciacion-textarea', function() {
        progressBar.update();
    });
});
</script>
@endsection