@extends('layouts.app')

@section('title', 'Reporte de Notas')

@section('css')
<style>
    .reporte-notas-page {
        min-height: 80vh;
    }

    .reporte-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        padding: 24px;
    }

    .reporte-header {
        padding-bottom: 16px;
        margin-bottom: 20px;
        border-bottom: 1px solid #eef2f7;
    }

    .reporte-header h4 {
        margin: 0;
        font-weight: 700;
        color: #111827;
    }

    .reporte-subtitle {
        color: #6b7280;
        margin-top: 6px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        align-items: end;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 18px;
    }

    .filters-grid label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .filters-grid select {
        width: 100%;
        border-radius: 8px;
    }

    .btn-descargar {
        background: #2563eb;
        border: none;
        color: #fff;
        padding: 11px 18px;
        border-radius: 8px;
        font-weight: 600;
        transition: background 0.2s ease;
    }

    .btn-descargar:hover {
        background: #1d4ed8;
    }

    .btn-descargar:disabled {
        background: #93c5fd;
        cursor: not-allowed;
    }

    .hint-box {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        border-radius: 10px;
        padding: 14px 16px;
        color: #1e3a8a;
    }

    .small-note {
        color: #6b7280;
        font-size: 0.92rem;
    }

    @media (max-width: 768px) {
        .reporte-card {
            padding: 16px;
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid reporte-notas-page">
    <div class="reporte-card">
        <div class="reporte-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div>
                <h4>Reporte de Notas</h4>
                <p class="reporte-subtitle mb-0">Descarga un archivo Excel con una hoja por curso, competencias, NL y conclusiones descriptivas.</p>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form id="formReporteNotas" action="{{ route('admin.reportes-notas.exportar') }}" method="GET" target="_self">
            <div class="filters-grid mb-3">
                <div>
                    <label for="anio_id">Año académico</label>
                    <select id="anio_id" name="anio_id" class="form-select" required>
                        @foreach($anios as $anio)
                            <option value="{{ $anio->id }}" {{ $anioSeleccionado && $anioSeleccionado->id === $anio->id ? 'selected' : '' }}>
                                {{ $anio->anio }}{{ $anio->activo ? ' (Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="periodo_id">Periodo</label>
                    <select id="periodo_id" name="periodo_id" class="form-select" required>
                        @forelse($periodos as $periodo)
                            <option value="{{ $periodo->id }}" {{ $periodoSeleccionado && $periodoSeleccionado->id === $periodo->id ? 'selected' : '' }}>
                                {{ $periodo->nombre_completo }}
                            </option>
                        @empty
                            <option value="">No hay periodos para el año seleccionado</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label for="aula_id">Aula</label>
                    <select id="aula_id" name="aula_id" class="form-select" required>
                        @forelse($aulas as $aula)
                            <option value="{{ $aula->id }}" {{ $aulaSeleccionada && $aulaSeleccionada->id === $aula->id ? 'selected' : '' }}>
                                {{ $aula->nombre_completo }}
                            </option>
                        @empty
                            <option value="">No hay aulas disponibles</option>
                        @endforelse
                    </select>
                </div>

                <div class="d-flex flex-column gap-2">
                    <button type="submit" id="btnDescargar" class="btn-descargar">
                        <i class="fas fa-file-excel me-2"></i> Descargar Excel
                    </button>
                    <span class="small-note">Los docentes solo verán sus aulas asignadas. Los administradores pueden descargar cualquier aula.</span>
                </div>
            </div>
        </form>

        <div class="hint-box mt-3">
            <strong>Formato del reporte:</strong> cada hoja corresponde a un curso del aula seleccionada. En cada hoja se incluye una cabecera de generalidades, los alumnos ordenados alfabéticamente por apellido paterno, apellido materno y nombres, la nota NL y la conclusión descriptiva de cada competencia.
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const endpointFiltros = '{{ route('admin.reportes-notas.filtros') }}';
        const anioSelect = document.getElementById('anio_id');
        const periodoSelect = document.getElementById('periodo_id');
        const aulaSelect = document.getElementById('aula_id');
        const btnDescargar = document.getElementById('btnDescargar');

        function cargarOpciones(select, items, placeholder) {
            select.innerHTML = '';
            if (!items || items.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = placeholder;
                select.appendChild(option);
                return;
            }

            items.forEach(function (item) {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.text || item.nombre || item.nombre_completo || item.nombreCompleto || item.grado || '';
                select.appendChild(option);
            });
        }

        function actualizarFiltros(anioId) {
            btnDescargar.disabled = true;
            fetch(endpointFiltros + '?anio_id=' + encodeURIComponent(anioId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                cargarOpciones(periodoSelect, data.periodos || [], 'No hay periodos');
                cargarOpciones(aulaSelect, data.aulas || [], 'No hay aulas disponibles');

                if (data.periodo_default_id) {
                    periodoSelect.value = String(data.periodo_default_id);
                }
                if (data.aula_default_id) {
                    aulaSelect.value = String(data.aula_default_id);
                }

                btnDescargar.disabled = !(anioSelect.value && periodoSelect.value && aulaSelect.value);
            })
            .catch(() => {
                alert('No se pudieron cargar los filtros del reporte.');
            });
        }

        anioSelect.addEventListener('change', function () {
            if (this.value) {
                actualizarFiltros(this.value);
            }
        });

        periodoSelect.addEventListener('change', function () {
            btnDescargar.disabled = !(anioSelect.value && periodoSelect.value && aulaSelect.value);
        });

        aulaSelect.addEventListener('change', function () {
            btnDescargar.disabled = !(anioSelect.value && periodoSelect.value && aulaSelect.value);
        });

        btnDescargar.disabled = !(anioSelect.value && periodoSelect.value && aulaSelect.value);
    })();
</script>
@endsection
