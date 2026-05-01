@extends('layouts.app')

@section('title', 'Reporte de Carga Horaria')

@section('css')
<style>
    .report-page {
        min-height: 80vh;
    }

    .report-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #e8ebee;
        padding: 24px;
    }

    .report-header {
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f3f5;
        margin-bottom: 20px;
    }

    .report-header h4 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
    }

    .report-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .report-actions select {
        min-width: 200px;
        max-width: 260px;
    }

    .report-tree {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .tree-item {
        background: #fbfcfd;
        border-radius: 12px;
        border: 1px solid #edf1f5;
        overflow: hidden;
    }

    .toggle-button {
        width: 100%;
        text-align: left;
        border: none;
        background: transparent;
        padding: 16px 20px;
        font-size: 0.98rem;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
    }

    .toggle-button:hover {
        background: #f5f7fa;
    }

    .toggle-icon {
        display: inline-flex;
        width: 20px;
        justify-content: center;
        color: #6b7280;
        transition: transform 0.2s ease;
    }

    .tree-item .node-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.28s ease, padding 0.28s ease;
        padding: 0 20px;
    }

    .tree-item .node-content.expanded {
        max-height: 3200px;
        padding: 0 20px 16px;
    }

    .tree-item .node-content .tree-item {
        background: transparent;
        border: none;
        margin-top: 6px;
    }

    .tree-item .node-content .toggle-button {
        padding: 12px 0;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .tree-item .node-content .course-row {
        padding: 12px 0 12px 28px;
        border-bottom: 1px solid #f3f5f7;
        color: #475569;
    }

    .tree-item .node-content .course-row:last-child {
        border-bottom: none;
    }

    .course-title {
        font-weight: 600;
        margin-bottom: 4px;
    }

    .course-hours,
    .course-schedule {
        color: #6b7280;
        font-size: 0.92rem;
    }

    .course-schedule {
        margin-left: 24px;
    }

    .empty-tree {
        border-radius: 12px;
        border: 1px dashed #d1d5db;
        padding: 36px;
        background: #f8fafc;
        text-align: center;
        color: #475569;
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }

    .empty-tree-icon {
        font-size: 2rem;
    }

    .nav-tabs {
        border-bottom: 2px solid #e8ebee;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6b7280;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #1f2937;
        border-bottom-color: #d1d5db;
    }

    .nav-tabs .nav-link.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background: transparent;
    }

    .tab-content {
        padding: 20px 0;
    }

    .export-filters {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        align-items: end;
    }

    .export-filters .form-group {
        margin: 0;
    }

    .export-filters label {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 6px;
        display: block;
        color: #374151;
    }

    .export-filters select,
    .export-filters input {
        border-radius: 8px;
        border: 1px solid #d1d5db;
    }

    .export-btn {
        background: #10b981;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .export-btn:hover {
        background: #059669;
    }

    .export-btn:disabled {
        background: #d1d5db;
        cursor: not-allowed;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: #f3f4f6;
    }

    .data-table th {
        padding: 14px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .data-table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        color: #475569;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .table-container {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .report-card {
            padding: 16px;
        }

        .toggle-button {
            padding: 14px 16px;
        }

        .tree-item .node-content {
            padding: 0 16px;
        }

        .tree-item .node-content.expanded {
            padding: 0 16px 14px;
        }

        .export-filters {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid report-page">
    <div class="report-card">
        <div class="report-header">
            <h4>Reporte de Carga Horaria</h4>
            <p class="mb-0 text-muted">Visualiza y exporta la asignación de docentes y cursos.</p>
        </div>

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="vista-tab" data-bs-toggle="tab" data-bs-target="#vista" type="button" role="tab" aria-controls="vista" aria-selected="true">
                    <i class="fas fa-tree me-2"></i> Vista Jerárquica
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="exportar-tab" data-bs-toggle="tab" data-bs-target="#exportar" type="button" role="tab" aria-controls="exportar" aria-selected="false">
                    <i class="fas fa-table me-2"></i> Exportar a Excel
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Tab 1: Vista Jerárquica -->
            <div class="tab-pane fade show active" id="vista" role="tabpanel" aria-labelledby="vista-tab">
                <div class="report-actions mb-4">
                    <label class="mb-0 text-muted">Año académico</label>
                    <select id="anioReporteSelect" class="form-select form-select-sm">
                        @foreach($anios as $anio)
                            <option value="{{ $anio->id }}" {{ $anioSeleccionado && $anio->id === $anioSeleccionado->id ? 'selected' : '' }}>
                                {{ $anio->anio }}{{ $anio->activo ? ' (Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="reporteTreeContainer" class="mt-4">
                    @include('carga-horaria.partials.reporte-arbol', ['tree' => $tree])
                </div>
            </div>

            <!-- Tab 2: Exportar a Excel -->
            <div class="tab-pane fade" id="exportar" role="tabpanel" aria-labelledby="exportar-tab">
                <div class="export-filters">
                    <div class="form-group">
                        <label for="anioExportar">Año Académico</label>
                        <select id="anioExportar" class="form-select form-select-sm">
                            @foreach($anios as $anio)
                                <option value="{{ $anio->id }}" {{ $anioSeleccionado && $anio->id === $anioSeleccionado->id ? 'selected' : '' }}>
                                    {{ $anio->anio }}{{ $anio->activo ? ' (Activo)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="aulaExportar">Aula</label>
                        <select id="aulaExportar" class="form-select form-select-sm">
                            <option value="">-- Todas las aulas --</option>
                        </select>
                    </div>
                    <button id="btnCargarTabla" class="export-btn">
                        <i class="fas fa-sync-alt me-2"></i> Cargar Datos
                    </button>
                    <button id="btnExportarExcel" class="export-btn" disabled>
                        <i class="fas fa-download me-2"></i> Exportar a Excel
                    </button>
                </div>

                <div class="table-container">
                    <table class="data-table" id="tablaExportacion">
                        <thead>
                            <tr>
                                <th>Profesor</th>
                                <th>Aula</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th>Turno</th>
                                <th>Curso</th>
                                <th>Horas/Semana</th>
                                <th>Día</th>
                                <th>Horario</th>
                                <th>Año Académico</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTabla">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Selecciona los filtros y haz clic en "Cargar Datos"</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js"></script>
<script>
    // Intento automático de fallback para cargar XLSX si el CDN principal falla.
    (function () {
        if (window.XLSX) return;
        const tryUrls = [
            'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js',
            'https://unpkg.com/xlsx@0.18.5/dist/xlsx.full.min.js'
        ];

        // función reutilizable para intentar cargar los CDNs en orden
        window.tryLoadXLSXFallback = function (onComplete) {
            if (window.XLSX) {
                if (typeof onComplete === 'function') onComplete(true);
                return;
            }

            let i = 0;
            function tryNext() {
                if (window.XLSX) {
                    if (typeof onComplete === 'function') onComplete(true);
                    return;
                }
                if (i >= tryUrls.length) {
                    if (typeof onComplete === 'function') onComplete(false);
                    // Mostrar mensaje no intrusivo en la UI si no está ya mostrado
                    const container = document.querySelector('.report-card');
                    if (container && !document.getElementById('xlsx-error')) {
                        const warn = document.createElement('div');
                        warn.id = 'xlsx-error';
                        warn.className = 'alert alert-warning mt-3';
                        warn.innerHTML = 'Atención: No se pudo cargar la librería de exportación (XLSX). La exportación a Excel no estará disponible.' +
                            ' <button id="btnRetryXLSX" class="btn btn-sm btn-outline-primary ms-2">Reintentar</button>';
                        container.insertBefore(warn, container.firstChild);
                        document.getElementById('btnRetryXLSX').addEventListener('click', function () {
                            // remover mensaje y reintentar
                            const el = document.getElementById('xlsx-error');
                            if (el) el.remove();
                            window.tryLoadXLSXFallback();
                        });
                    }
                    return;
                }

                const script = document.createElement('script');
                script.src = tryUrls[i++];
                script.onload = function () {
                    console.info('XLSX cargado desde fallback:', script.src);
                    if (typeof onComplete === 'function') onComplete(true);
                };
                script.onerror = function () {
                    console.warn('Fallo al cargar XLSX desde:', script.src);
                    tryNext();
                };
                document.head.appendChild(script);
            }

            tryNext();
        };

        // Esperar un pequeño tiempo para que el script inicial intente cargar, luego lanzar fallback si hace falta
        setTimeout(function () { if (!window.XLSX) window.tryLoadXLSXFallback(); }, 800);
    })();
</script>
<script>
    const reporteState = {
        expandedKeys: new Set(),
        selectedYear: '{{ $anioSeleccionado?->id ?? '' }}',
        allAulas: []
    };

    function toggleNode(key, element) {
        const content = element.closest('.tree-item').querySelector('.node-content');
        if (!content) return;

        const isExpanded = content.classList.contains('expanded');
        if (isExpanded) {
            content.classList.remove('expanded');
            element.querySelector('.toggle-icon').style.transform = 'rotate(0deg)';
            reporteState.expandedKeys.delete(key);
        } else {
            content.classList.add('expanded');
            element.querySelector('.toggle-icon').style.transform = 'rotate(180deg)';
            reporteState.expandedKeys.add(key);
        }
    }

    function bindTreeToggle() {
        document.querySelectorAll('#reporteTreeContainer .toggle-button').forEach(button => {
            button.addEventListener('click', function () {
                const treeItem = this.closest('.tree-item');
                if (!treeItem) return;
                const key = treeItem.dataset.key;
                toggleNode(key, this);
            });
        });
    }

    function restoreTreeState() {
        document.querySelectorAll('#reporteTreeContainer .tree-item').forEach(item => {
            const key = item.dataset.key;
            const content = item.querySelector('.node-content');
            const icon = item.querySelector('.toggle-icon');
            if (!content || !icon) return;

            if (reporteState.expandedKeys.has(key)) {
                content.classList.add('expanded');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.remove('expanded');
                icon.style.transform = 'rotate(0deg)';
            }
        });
    }

    function loadReporteData(anioId) {
        reporteState.selectedYear = anioId;
        const url = '{{ route('admin.carga-horaria-reporte.data') }}';

        fetch(`${url}?anio_id=${anioId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('reporteTreeContainer').innerHTML = data.html;
            restoreTreeState();
            bindTreeToggle();
        })
        .catch(() => {
            document.getElementById('reporteTreeContainer').innerHTML = '<div class="empty-tree">No se pudo cargar el informe. Intenta nuevamente.</div>';
        });
    }

    function cargarAulasPorAnio(anioId) {
        const url = '{{ route('admin.carga-horaria-reporte.aulas-disponibles') }}';
        
        fetch(`${url}?anio_id=${anioId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const aulaSelect = document.getElementById('aulaExportar');
            aulaSelect.innerHTML = '<option value="">-- Todas las aulas --</option>';
            reporteState.allAulas = data;
            
            data.forEach(aula => {
                const option = document.createElement('option');
                option.value = aula.id;
                option.textContent = `${aula.nombre} - ${aula.grado || ''} "${aula.seccion || ''}"`;
                aulaSelect.appendChild(option);
            });
        })
        .catch(err => console.error('Error al cargar aulas:', err));
    }

    function cargarTablaExportacion() {
        const anioId = document.getElementById('anioExportar').value;
        const aulaId = document.getElementById('aulaExportar').value;
        
        if (!anioId) {
            alert('Selecciona un año académico');
            return;
        }

        // siempre solicitamos el endpoint en modo exportación (export=1)
        // de este modo 'Todas las aulas' (aulaId vacío) retornará la lista completa
        let url = '{{ route('admin.carga-horaria-reporte.data') }}?anio_id=' + anioId + '&export=1';
        if (aulaId) {
            url += '&aula_id=' + aulaId;
        }

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            construirTablaExportacion(data.cargas || []);
            document.getElementById('btnExportarExcel').disabled = false;
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error al cargar los datos');
        });
    }

    function construirTablaExportacion(cargas) {
        const tbody = document.getElementById('cuerpoTabla');
        
        if (!cargas || cargas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No hay datos para mostrar</td></tr>';
            return;
        }

        let html = '';
        cargas.forEach(carga => {
            html += `
                <tr>
                    <td>${carga.docente_nombre || 'N/A'}</td>
                    <td>${carga.aula_nombre || 'N/A'}</td>
                    <td>${carga.grado_nombre || 'N/A'}</td>
                    <td>${carga.seccion_nombre || 'N/A'}</td>
                    <td>${carga.turno || 'N/A'}</td>
                    <td>${carga.curso_nombre || 'N/A'}</td>
                    <td>${carga.horas_semanales || '-'}</td>
                    <td>${carga.dia_semana || 'Flexible'}</td>
                    <td>${carga.horario || 'Flexible'}</td>
                    <td>${carga.anio_academico || 'N/A'}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function ensureXLSXLoaded(callback) {
        if (window.XLSX) {
            return callback();
        }

        const existing = document.querySelector('script[data-xlsx-fallback]');
        if (existing) {
            existing.addEventListener('load', callback);
            existing.addEventListener('error', function () {
                alert('No se pudo cargar la librería de exportación (XLSX).');
            });
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js';
        script.setAttribute('data-xlsx-fallback', '1');
        script.onload = callback;
        script.onerror = function () {
            alert('No se pudo cargar la librería de exportación (XLSX). Comprueba tu conexión.');
        };
        document.head.appendChild(script);
    }

    function exportarAExcel() {
        ensureXLSXLoaded(function () {
            const tabla = document.getElementById('tablaExportacion');
            try {
                const ws = XLSX.utils.table_to_sheet(tabla);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Carga Horaria');
                XLSX.writeFile(wb, `Carga_Horaria_${new Date().getTime()}.xlsx`);
            } catch (err) {
                console.error('Error exportando a Excel:', err);
                alert('Error al exportar a Excel. Revisa la consola para más detalles.');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindTreeToggle();

        document.getElementById('anioReporteSelect').addEventListener('change', function () {
            const selected = this.value;
            if (selected) {
                loadReporteData(selected);
            }
        });

        document.getElementById('anioExportar').addEventListener('change', function () {
            cargarAulasPorAnio(this.value);
        });

        document.getElementById('btnCargarTabla').addEventListener('click', cargarTablaExportacion);

        document.getElementById('btnExportarExcel').addEventListener('click', exportarAExcel);

        // Cargar aulas al iniciar
        const anioInicialExportar = document.getElementById('anioExportar').value;
        if (anioInicialExportar) {
            cargarAulasPorAnio(anioInicialExportar);
        }
    });
</script>
@endsection

