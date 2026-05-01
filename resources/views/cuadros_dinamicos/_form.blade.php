<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nombre (título)</label>
        <input type="text" name="nombre" class="form-control" required value="{{ $cuadro->nombre ?? old('nombre') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nivel (opcional)</label>
        <select name="nivel_id" class="form-control">
            <option value="">Todos</option>
            @foreach($niveles as $n)
                <option value="{{ $n->id }}" {{ (isset($cuadro) && $cuadro->nivel_id == $n->id) ? 'selected' : '' }}>{{ $n->nombre }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">¿Mostrar solo en el nivel seleccionado?</label>
        <select name="mostrar_en_nivel_seleccionado" class="form-control">
            <option value="0" {{ (isset($cuadro) && !($cuadro->opciones['mostrar_en_nivel_seleccionado'] ?? false)) ? 'selected' : '' }}>No</option>
            <option value="1" {{ (isset($cuadro) && ($cuadro->opciones['mostrar_en_nivel_seleccionado'] ?? false)) ? 'selected' : '' }}>Sí</option>
        </select>
        <small class="text-muted d-block mt-1">Úsalo cuando el cuadro deba verse únicamente en el nivel elegido.</small>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Tipo</label>
        <select name="tipo" class="form-control">
            <option value="sin_evaluaciones" {{ (isset($cuadro) && $cuadro->tipo == 'sin_evaluaciones') ? 'selected' : '' }}>Sin evaluaciones</option>
            <option value="tabla_periodos" {{ (isset($cuadro) && $cuadro->tipo == 'tabla_periodos') ? 'selected' : '' }}>Tabla por periodos</option>
            <option value="leyenda" {{ (isset($cuadro) && $cuadro->tipo == 'leyenda') ? 'selected' : '' }}>Leyenda</option>
            <option value="tabla_generica" {{ (isset($cuadro) && $cuadro->tipo == 'tabla_generica') ? 'selected' : '' }}>Tabla genérica (n filas / n columnas)</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Tipo de nota</label>
        <select name="nota_tipo" class="form-control">
            <option value="none" {{ (isset($cuadro) && ($cuadro->nota_tipo ?? 'none') == 'none') ? 'selected' : '' }}>No aplica</option>
            <option value="numeric" {{ (isset($cuadro) && $cuadro->nota_tipo == 'numeric') ? 'selected' : '' }}>Numérica</option>
            <option value="literal" {{ (isset($cuadro) && $cuadro->nota_tipo == 'literal') ? 'selected' : '' }}>Literal</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Ancho</label>
        <select name="ancho" class="form-control">
            <option value="col-12" {{ (isset($cuadro) && $cuadro->ancho == 'col-12') ? 'selected' : '' }}>Ancho completo</option>
            <option value="col-6" {{ (isset($cuadro) && $cuadro->ancho == 'col-6') ? 'selected' : '' }}>Mitad</option>
        </select>
    </div>
</div>

<div id="opciones-tabla-generica" style="{{ (old('tipo') === 'tabla_generica' || (isset($cuadro) && $cuadro->tipo === 'tabla_generica')) ? 'display:block;' : 'display:none;' }}">
    <h5>Opciones - Tabla genérica</h5>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Cantidad de columnas</label>
            <input type="number" min="1" name="columnas_count" class="form-control" value="{{ isset($cuadro) && isset($cuadro->opciones['columnas']) ? $cuadro->opciones['columnas'] : 3 }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Cantidad de filas</label>
            <input type="number" min="0" name="filas_count" class="form-control" value="{{ isset($cuadro) && isset($cuadro->opciones['filas']) ? $cuadro->opciones['filas'] : 4 }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Mostrar fila de encabezados</label>
            <select name="mostrar_encabezados" class="form-control">
                <option value="0" {{ (isset($cuadro) && isset($cuadro->opciones['mostrar_encabezados']) && !$cuadro->opciones['mostrar_encabezados']) ? 'selected' : '' }}>No</option>
                <option value="1" {{ (isset($cuadro) && isset($cuadro->opciones['mostrar_encabezados']) && $cuadro->opciones['mostrar_encabezados']) ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <label class="form-label">Encabezados de columna (si aplica, una por línea)</label>
            <textarea name="encabezados_text" class="form-control" rows="3" placeholder="Cada línea será un encabezado de columna">@if(isset($cuadro) && isset($cuadro->opciones['encabezados'])){{ implode("\n", $cuadro->opciones['encabezados']) }}@endif</textarea>
            <small class="text-muted">Si hay menos encabezados que columnas, las restantes quedarán vacías.</small>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12">
            <label class="form-label">Valores de las celdas</label>
            <div id="celdas-editor" class="mb-2"></div>
            <small class="text-muted">Edite los valores que aparecerán en cada celda de la tabla. Cambiar columnas/filas reconstruye la matriz.</small>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <h5>Vista previa</h5>
        <div id="preview-cuadro" class="card p-2">
            <div id="preview-content">Seleccione "Tabla genérica" para ver la vista previa.</div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="activo" id="activo" {{ (isset($cuadro) ? ($cuadro->activo ? 'checked' : '') : 'checked') }}>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="mostrar_en_libreta" id="mostrar_en_libreta" {{ (isset($cuadro) ? ($cuadro->mostrar_en_libreta ? 'checked' : '') : 'checked') }}>
            <label class="form-check-label" for="mostrar_en_libreta">Mostrar en libreta</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="involucra_libreta" id="involucra_libreta" {{ (isset($cuadro) && $cuadro->involucra_libreta) ? 'checked' : '' }}>
            <label class="form-check-label" for="involucra_libreta">Involucra en libreta</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">{{ isset($cuadro) ? 'Actualizar' : 'Crear' }}</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const previewContent = document.getElementById('preview-content');
        const celdasEditor = document.getElementById('celdas-editor');
        const initialCeldas = @json(isset($cuadro) && isset($cuadro->opciones['celdas']) ? $cuadro->opciones['celdas'] : []);
        let currentCeldas = Array.isArray(initialCeldas) ? JSON.parse(JSON.stringify(initialCeldas)) : [];

        function escapeHtml(unsafe) {
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getCeldasFromInputs() {
            const filas = parseInt((document.querySelector('input[name="filas_count"]') || { value: 0 }).value, 10) || 0;
            const columnas = parseInt((document.querySelector('input[name="columnas_count"]') || { value: 3 }).value, 10) || 3;
            const celdas = [];
            for (let r = 0; r < filas; r++) {
                const row = [];
                for (let c = 0; c < columnas; c++) {
                    const sel = document.querySelector(`input[name="celdas[${r}][${c}]"]`);
                    row.push(sel ? (sel.value || '').toString() : '');
                }
                celdas.push(row);
            }
            return celdas;
        }

        function snapshotCurrentCeldas() {
            const filas = parseInt((document.querySelector('input[name="filas_count"]') || { value: 0 }).value, 10) || 0;
            const columnas = parseInt((document.querySelector('input[name="columnas_count"]') || { value: 3 }).value, 10) || 3;
            const snapshot = [];
            for (let r = 0; r < filas; r++) {
                const row = [];
                for (let c = 0; c < columnas; c++) {
                    const input = document.querySelector(`input[name="celdas[${r}][${c}]"]`);
                    row.push(input ? (input.value || '').toString() : '');
                }
                snapshot.push(row);
            }
            currentCeldas = snapshot;
        }

        function ensureMatrixSize(source, filas, columnas) {
            const result = [];
            for (let r = 0; r < filas; r++) {
                const sourceRow = Array.isArray(source[r]) ? source[r] : [];
                const row = [];
                for (let c = 0; c < columnas; c++) {
                    row.push(typeof sourceRow[c] !== 'undefined' ? sourceRow[c] : '');
                }
                result.push(row);
            }
            return result;
        }

        function renderTablaGenericaPreview() {
            const nombre = (document.querySelector('input[name="nombre"]') || { value: '' }).value.trim() || 'Título';
            const columnas = parseInt((document.querySelector('input[name="columnas_count"]') || { value: 3 }).value, 10) || 3;
            const filas = parseInt((document.querySelector('input[name="filas_count"]') || { value: 4 }).value, 10) || 0;
            const mostrarEncabezados = (document.querySelector('select[name="mostrar_encabezados"]') || { value: '0' }).value === '1';
            const encabezadosText = (document.querySelector('textarea[name="encabezados_text"]') || { value: '' }).value || '';
            const encabezados = encabezadosText.split(/\r?\n/).map(s => s.trim()).filter(s => s.length > 0);

            const celdas = getCeldasFromInputs();

            let html = '';
            html += `<table style="width:100%; border-collapse:collapse;">`;
            html += `<tr><td colspan="${columnas}" style="border:1px solid #333;padding:8px;text-align:center;font-weight:600;background:#f7f7f7;">${escapeHtml(nombre)}</td></tr>`;

            if (mostrarEncabezados) {
                html += '<tr>';
                for (let c = 0; c < columnas; c++) {
                    const h = encabezados[c] || '';
                    html += `<th style="border:1px solid #666;padding:6px;text-align:center;background:#fafafa;">${escapeHtml(h)}</th>`;
                }
                html += '</tr>';
            }

            for (let r = 0; r < filas; r++) {
                html += '<tr>';
                for (let c = 0; c < columnas; c++) {
                    const val = (celdas[r] && celdas[r][c]) ? celdas[r][c] : '';
                    html += `<td style="border:1px solid #666;padding:8px;height:36px;">${escapeHtml(val)}</td>`;
                }
                html += '</tr>';
            }

            html += '</table>';
            previewContent.innerHTML = html;
        }

        function renderCeldasEditor() {
            if (!celdasEditor) return;
            const columnas = parseInt((document.querySelector('input[name="columnas_count"]') || { value: 3 }).value, 10) || 3;
            const filas = parseInt((document.querySelector('input[name="filas_count"]') || { value: 4 }).value, 10) || 0;

            if (currentCeldas.length === 0 && initialCeldas.length > 0) {
                currentCeldas = JSON.parse(JSON.stringify(initialCeldas));
            } else {
                snapshotCurrentCeldas();
            }
            const matrix = ensureMatrixSize(currentCeldas, filas, columnas);
            currentCeldas = matrix;
            let html = '<table class="table table-sm" style="width:100%;">';
            html += '<thead><tr><th></th>';
            for (let c = 0; c < columnas; c++) html += `<th style="width:${Math.floor(100/columnas)}%">Col ${c+1}</th>`;
            html += '</tr></thead><tbody>';
            for (let r = 0; r < filas; r++) {
                html += '<tr>';
                html += `<th style="width:1%">Fila ${r+1}</th>`;
                for (let c = 0; c < columnas; c++) {
                    const val = (matrix && matrix[r] && typeof matrix[r][c] !== 'undefined') ? matrix[r][c] : '';
                    html += `<td><input type="text" name="celdas[${r}][${c}]" class="form-control form-control-sm" value="${escapeHtml(val)}"></td>`;
                }
                html += '</tr>';
            }
            html += '</tbody></table>';
            celdasEditor.innerHTML = html;
            const inputs = celdasEditor.querySelectorAll('input[name^="celdas["]');
            inputs.forEach(i => i.addEventListener('input', renderTablaGenericaPreview));
        }

        function renderPreview() {
            const tipo = (document.querySelector('select[name="tipo"]') || { value: '' }).value;
            if (tipo === 'tabla_generica') {
                renderTablaGenericaPreview();
            } else if (tipo === 'tabla_periodos') {
                previewContent.innerHTML = '<div class="text-muted">Vista previa: tabla por periodos (se renderiza con datos reales en la libreta)</div>';
            } else if (tipo === 'leyenda') {
                previewContent.innerHTML = '<div class="text-muted">Vista previa: leyenda (edite los valores en el campo apropiado)</div>';
            } else {
                previewContent.innerHTML = '<div class="text-muted">Seleccione "Tabla genérica" para ver la vista previa.</div>';
            }
        }

        const tipoSelect = document.querySelector('select[name="tipo"]');
        const opcionesDiv = document.getElementById('opciones-tabla-generica');
        function toggleOpciones() {
            if (!tipoSelect) return;
            if (tipoSelect.value === 'tabla_generica') {
                opcionesDiv.style.display = 'block';
                renderCeldasEditor();
            } else {
                opcionesDiv.style.display = 'none';
            }
            renderPreview();
        }
        if (tipoSelect) {
            tipoSelect.addEventListener('change', toggleOpciones);
        }

        const inputsToWatch = [
            'input[name="columnas_count"]',
            'input[name="filas_count"]',
            'select[name="mostrar_encabezados"]',
            'textarea[name="encabezados_text"]',
            'input[name="nombre"]'
        ];
        inputsToWatch.forEach(selector => {
            const el = document.querySelector(selector);
            if (el) el.addEventListener('input', function () { renderCeldasEditor(); renderPreview(); });
            if (el) el.addEventListener('change', function () { renderCeldasEditor(); renderPreview(); });
        });

        if (typeof toggleOpciones === 'function') toggleOpciones();
        renderPreview();
    });
</script>
