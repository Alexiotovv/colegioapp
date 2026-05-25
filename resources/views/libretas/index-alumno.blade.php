{{-- resources/views/libretas/index-alumno.blade.php --}}
@extends('layouts.app')

@section('title', 'Exportar Libreta por Alumno')

@section('css')
<style>
    .search-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .results-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .student-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 14px;
        margin-bottom: 12px;
        transition: all 0.25s ease;
    }

    .student-item:hover {
        background-color: #f8f9fa;
        transform: translateX(4px);
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
        margin-right: 10px;
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

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .results-total {
        font-size: 14px;
        color: #6c757d;
    }

    .results-total strong {
        color: var(--primary-color);
        font-size: 18px;
    }

    .hint-text {
        font-size: 13px;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-graduate me-2" style="color: var(--primary-color);"></i>
            Exportar Libreta por Alumno
        </h4>
        <a href="{{ route('admin.libretas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver a Exportar Libretas
        </a>
    </div>

    <div class="search-card">
        <div class="row g-3 align-items-start">
            <div class="col-md-8">
                <label for="buscarAlumno" class="form-label required-field">Buscar alumno</label>
                <input
                    type="text"
                    id="buscarAlumno"
                    class="form-control"
                    placeholder="Escribe DNI, apellidos o nombres"
                    autocomplete="off"
                    disabled
                >
                <div class="hint-text mt-1">
                    Año activo: <strong>{{ $anioActivo->anio ?? 'No configurado' }}</strong>. Primero selecciona el bimestre para habilitar la búsqueda.
                </div>
            </div>
            <div class="col-md-4">
                <label for="periodo_id" class="form-label required-field">Bimestre</label>
                <select class="form-select" id="periodo_id" required>
                    <option value="">Seleccionar bimestre</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}">{{ $periodo->nombre }} - {{ $periodo->anioAcademico->anio ?? '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="results-card" id="resultsCard" style="display: none;">
        <div class="results-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Resultados de búsqueda
            </h5>
            <div class="results-total">
                Total: <strong id="resultsCount">0</strong>
            </div>
        </div>

        <div id="resultsContainer"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    let debounceTimer = null;

    $('#periodo_id').on('change', function () {
        const periodoId = $(this).val();

        if (periodoId) {
            $('#buscarAlumno').prop('disabled', false).focus();
        } else {
            $('#buscarAlumno').prop('disabled', true).val('');
            $('#resultsContainer').html('');
            $('#resultsCount').text('0');
            $('#resultsCard').hide();
        }
    });

    $('#buscarAlumno').on('input', function () {
        clearTimeout(debounceTimer);

        const periodoId = $('#periodo_id').val();
        if (!periodoId) {
            return;
        }

        const termino = $(this).val().trim();

        if (termino.length < 2) {
            $('#resultsContainer').html('');
            $('#resultsCount').text('0');
            $('#resultsCard').hide();
            return;
        }

        debounceTimer = setTimeout(function () {
            buscarAlumnos(termino);
        }, 350);
    });

    function buscarAlumnos(termino) {
        const periodoId = $('#periodo_id').val();
        if (!periodoId) {
            return;
        }

        $('#resultsCard').show();
        $('#resultsContainer').html('<div class="text-center py-3"><span class="loading-spinner"></span></div>');

        $.ajax({
            url: '{{ route("admin.libretas.alumno.buscar") }}',
            method: 'GET',
            data: { q: termino },
            success: function (response) {
                let html = '';

                if (!response.length) {
                    html = '<div class="alert alert-warning mb-0"><i class="fas fa-info-circle me-2"></i>No se encontraron alumnos con ese criterio.</div>';
                } else {
                    for (let i = 0; i < response.length; i++) {
                        const item = response[i];
                        const numero = i + 1;

                        html += `
                            <div class="student-item">
                                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                    <div>
                                        <div class="d-flex align-items-center">
                                            <span class="student-number">${numero}</span>
                                            <div>
                                                <strong>${item.codigo_estudiante}</strong>
                                                <span class="ms-2 text-muted">DNI: ${item.dni || '-'}</span><br>
                                                <span>${item.apellido_paterno} ${item.apellido_materno}, ${item.nombres}</span><br>
                                                <small class="text-muted">${item.aula_texto}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-info" onclick="previsualizarAlumno(${item.matricula_id})">
                                            <i class="fas fa-eye me-1"></i> Previsualizar
                                        </button>
                                        
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }

                $('#resultsContainer').html(html);
                $('#resultsCount').text(response.length);
            },
            error: function () {
                Swal.fire('Error', 'No se pudo realizar la búsqueda de alumnos.', 'error');
                $('#resultsContainer').html('');
                $('#resultsCount').text('0');
            }
        });
    }
});

function validarBimestreSeleccionado() {
    const periodoId = $('#periodo_id').val();

    if (!periodoId) {
        Swal.fire('Atención', 'Selecciona el bimestre para continuar.', 'warning');
        return null;
    }

    return periodoId;
}

function previsualizarAlumno(matriculaId) {
    const periodoId = validarBimestreSeleccionado();
    if (!periodoId) {
        return;
    }

    const url = '/admin/libretas/previsualizar?matricula_id=' + matriculaId + '&periodo_id=' + periodoId;
    window.open(url, '_blank');
}

function exportarAlumno(matriculaId) {
    const periodoId = validarBimestreSeleccionado();
    if (!periodoId) {
        return;
    }

    const form = $('<form>', {
        method: 'POST',
        action: '{{ route("admin.libretas.exportar-alumno") }}'
    }).append($('<input>', {
        name: 'matricula_id',
        value: matriculaId,
        type: 'hidden'
    })).append($('<input>', {
        name: 'periodo_id',
        value: periodoId,
        type: 'hidden'
    })).append($('<input>', {
        name: '_token',
        value: '{{ csrf_token() }}',
        type: 'hidden'
    }));

    $('body').append(form);
    form.submit();
    form.remove();
}
</script>
@endsection
