{{-- resources/views/libretas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Exportar Libretas')

@section('css')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .students-list {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .student-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    
    .student-card:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    
    .student-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        background-color: var(--primary-color);
        color: white;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        font-weight: bold;
        margin-right: 12px;
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
    
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .total-alumnos {
        font-size: 14px;
        color: #6c757d;
    }
    
    .total-alumnos strong {
        color: var(--primary-color);
        font-size: 18px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-print me-2" style="color: var(--primary-color);"></i>
            Exportar Libretas
        </h4>
        <a href="{{ route('admin.libretas.orden-merito.index') }}" class="btn btn-outline-success">
            <i class="fas fa-file-excel me-2"></i> Exportar Orden de Mérito
        </a>
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
                        <option value="{{ $periodo->id }}">
                            {{ $periodo->nombre }} - {{ $periodo->anioAcademico->anio ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="row mt-3 align-items-end">
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="soloPagosAlDia" name="soloPagosAlDia">
                    <label class="form-check-label" for="soloPagosAlDia">
                        Solo pagos al día
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <label for="mesLimite" class="form-label mb-2">Mes límite</label>
                <select class="form-select" id="mesLimite" disabled>
                    <option value="">Seleccionar mes</option>
                    <option value="marzo">Marzo</option>
                    <option value="abril">Abril</option>
                    <option value="mayo">Mayo</option>
                    <option value="junio">Junio</option>
                    <option value="julio">Julio</option>
                    <option value="agosto">Agosto</option>
                    <option value="setiembre">Setiembre</option>
                    <option value="octubre">Octubre</option>
                    <option value="noviembre">Noviembre</option>
                    <option value="diciembre">Diciembre</option>
                </select>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-info" id="btnPrevisualizarAula" style="display: none;">
                    <i class="fas fa-eye me-2"></i> Previsualizar Todo el Aula
                </button>
            </div>
        </div>
    </div>
    
    <div class="students-list" id="studentsList" style="display: none;">
        <div class="header-actions">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Alumnos del Aula
            </h5>
            <div class="total-alumnos" id="totalAlumnos">
                <i class="fas fa-chalkboard-user me-1"></i>
                Total: <strong id="totalCount">0</strong> alumnos
            </div>
        </div>
        <div id="studentsContainer"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Disable periodo by default; enable/reset when aula changes. Automatic load when periodo changes.
    $('#periodo_id').prop('disabled', true);
    $('#mesLimite').prop('disabled', true);

    // Habilitar/deshabilitar el select de mes según el checkbox
    $('#soloPagosAlDia').on('change', function() {
        if ($(this).is(':checked')) {
            $('#mesLimite').prop('disabled', false);
        } else {
            $('#mesLimite').prop('disabled', true).val('');
        }
    });

    $('#aula_id').on('change', function() {
        // Reset periodo selection to force user to reselect after aula change
        $('#periodo_id').val('');
        $('#periodo_id').prop('disabled', false);
        $('#soloPagosAlDia').prop('checked', false);
        $('#mesLimite').prop('disabled', true).val('');
        $('#studentsList').hide();
        $('#studentsContainer').html('');
        $('#totalCount').text('0');
        $('#btnExportarAula').hide();
        $('#btnPrevisualizarAula').hide();
    });

    $('#periodo_id').on('change', function() {
        let aulaId = $('#aula_id').val();
        let periodoId = $('#periodo_id').val();

        if (!aulaId || !periodoId) {
            return;
        }

        cargarAlumnosAutomaticamente(aulaId, periodoId);
    });

    function cargarAlumnosAutomaticamente(aulaId, periodoId) {
        // Show temporary loading state in the list area
        $('#studentsContainer').html('<div class="text-center py-3"><span class="loading-spinner"></span></div>');

        $.ajax({
            url: '{{ route("admin.libretas.alumnos-by-aula") }}',
            method: 'GET',
            data: { aula_id: aulaId },
            success: function(response) {
                let html = '';
                let total = response.length;

                for (let i = 0; i < response.length; i++) {
                    let matricula = response[i];
                    let numero = i + 1;
                    html += `
                        <div class="student-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <span class="student-number">${numero}</span>
                                        <div>
                                            <strong>${matricula.alumno.codigo_estudiante}</strong><br>
                                            <span>${matricula.alumno.apellido_paterno} ${matricula.alumno.apellido_materno}, ${matricula.alumno.nombres}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-info" onclick="previsualizar(${matricula.id})">
                                        <i class="fas fa-eye me-1"></i> Previsualizar
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }

                $('#studentsContainer').html(html);
                $('#totalCount').text(total);
                $('#studentsList').show();
                $('#btnExportarAula').show().data('aula-id', aulaId).data('periodo-id', periodoId);
                $('#btnPrevisualizarAula').show().data('aula-id', aulaId).data('periodo-id', periodoId);
            },
            error: function() {
                Swal.fire('Error', 'Error al cargar alumnos', 'error');
                $('#studentsContainer').html('');
                $('#studentsList').hide();
            }
        });
    }
    
    $('#btnExportarAula').on('click', function() {
        let aulaId = $(this).data('aula-id');
        let periodoId = $(this).data('periodo-id');
        
        let form = $('<form>', {
            'method': 'POST',
            'action': '{{ route("admin.libretas.exportar-aula") }}'
        }).append($('<input>', {
            'name': 'aula_id',
            'value': aulaId,
            'type': 'hidden'
        })).append($('<input>', {
            'name': 'periodo_id',
            'value': periodoId,
            'type': 'hidden'
        })).append($('<input>', {
            'name': '_token',
            'value': '{{ csrf_token() }}',
            'type': 'hidden'
        }));
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
    
    $('#btnPrevisualizarAula').on('click', function() {
        let aulaId = $(this).data('aula-id');
        let periodoId = $(this).data('periodo-id');
        let soloPagosAlDia = $('#soloPagosAlDia').is(':checked');
        let mesLimite = $('#mesLimite').val();
        
        let url = '/admin/libretas/previsualizar-aula?aula_id=' + aulaId + '&periodo_id=' + periodoId;
        
        if (soloPagosAlDia && mesLimite) {
            url += '&solo_pagos_al_dia=true&mes_limite=' + mesLimite;
        }
        
        window.open(url, '_blank');
    });
});

function previsualizar(matriculaId) {
    let periodoId = $('#periodo_id').val();
    window.open('/admin/libretas/previsualizar?matricula_id=' + matriculaId + '&periodo_id=' + periodoId, '_blank');
}

function exportarAlumno(matriculaId) {
    let periodoId = $('#periodo_id').val();
    
    let form = $('<form>', {
        'method': 'POST',
        'action': '{{ route("admin.libretas.exportar-alumno") }}'
    }).append($('<input>', {
        'name': 'matricula_id',
        'value': matriculaId,
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'periodo_id',
        'value': periodoId,
        'type': 'hidden'
    })).append($('<input>', {
        'name': '_token',
        'value': '{{ csrf_token() }}',
        'type': 'hidden'
    }));
    
    $('body').append(form);
    form.submit();
    form.remove();
}
</script>
@endsection