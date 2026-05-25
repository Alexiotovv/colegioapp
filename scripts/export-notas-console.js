(() => {
  const STORAGE_PREFIX = 'colegioapp:notas-backup';
  const CONCLUSIONES_KEY_SUFFIX = ':conclusiones';
  const conclusionesLocales = {};

  const $all = (selector) => Array.from(document.querySelectorAll(selector));
  const $ = (selector) => document.querySelector(selector);

  const pageMeta = () => ({
    url: location.href,
    title: document.title,
    aula_id: $('#aula_id')?.value || '',
    curso_id: $('#curso_id')?.value || '',
    periodo_id: $('#periodo_id')?.value || '',
    generated_at: new Date().toISOString(),
  });

  const currentKey = () => {
    const meta = pageMeta();
    return `${STORAGE_PREFIX}:${meta.aula_id}:${meta.curso_id}:${meta.periodo_id}`;
  };

  const csvEscape = (value) => {
    const text = String(value ?? '');
    return `"${text.replace(/"/g, '""')}"`;
  };

  const rowKey = (matriculaId, competenciaId) => `${matriculaId || ''}_${competenciaId || ''}`;

  const conclusionsStorageKey = () => `${currentKey()}${CONCLUSIONES_KEY_SUFFIX}`;

  const cargarConclusionesLocalesGuardadas = () => {
    try {
      const raw = localStorage.getItem(conclusionsStorageKey());
      if (!raw) return;
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') return;

      Object.keys(parsed).forEach((k) => {
        if (typeof parsed[k] === 'string' && parsed[k].trim() !== '') {
          conclusionesLocales[k] = parsed[k].trim();
        }
      });
    } catch (error) {
      console.warn('No se pudieron cargar conclusiones locales:', error);
    }
  };

  const guardarConclusionesLocales = () => {
    localStorage.setItem(conclusionsStorageKey(), JSON.stringify(conclusionesLocales));
  };

  const leerConclusionesDesdeStorage = () => {
    try {
      const raw = localStorage.getItem(conclusionsStorageKey());
      if (!raw) return {};
      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (_error) {
      return {};
    }
  };

  const mergeConclusionesDesdeObjeto = (obj) => {
    if (!obj || typeof obj !== 'object') return 0;

    let merged = 0;
    Object.keys(obj).forEach((k) => {
      const texto = String(obj[k] || '').trim();
      if (!texto) return;
      conclusionesLocales[k] = texto;
      merged += 1;
    });

    if (merged > 0) {
      guardarConclusionesLocales();
    }

    return merged;
  };

  const capturarEstadoJSDelFront = () => {
    // Si la vista expone estructuras en window, las incorporamos al backup.
    const posiblesKeys = ['conclusionesPendientes', '__conclusionesPendientes', 'backupConclusionesPendientes'];
    posiblesKeys.forEach((key) => {
      if (window[key] && typeof window[key] === 'object') {
        mergeConclusionesDesdeObjeto(window[key]);
      }
    });

    capturarConclusionDesdeModal();
  };

  const obtenerConclusionesCombinadas = () => {
    // 1) lo capturado en esta ejecución
    capturarEstadoJSDelFront();

    // 2) lo ya guardado en localStorage para el contexto actual
    const storageMap = leerConclusionesDesdeStorage();
    const merged = { ...storageMap, ...conclusionesLocales };

    // sincronizar memoria local con el mapa combinado
    Object.keys(merged).forEach((k) => {
      const texto = String(merged[k] || '').trim();
      if (!texto) return;
      conclusionesLocales[k] = texto;
    });

    return { ...conclusionesLocales };
  };

  const capturarConclusionDesdeModal = () => {
    let matriculaId = $('#conclusion_matricula_id')?.value || '';
    let competenciaId = $('#conclusion_competencia_id')?.value || '';
    const notaId = $('#conclusion_nota_id')?.value || '';
    const texto = $('#conclusion_texto')?.value || '';

    // Fallback: si no vienen ids directos en hidden, inferirlos por nota_id desde la celda.
    if ((!matriculaId || !competenciaId) && notaId) {
      const select = document.querySelector(`.nota-select[data-nota-id="${notaId}"]`);
      if (select) {
        matriculaId = select.getAttribute('data-matricula') || '';
        competenciaId = select.getAttribute('data-competencia') || '';
      }
    }

    if (!matriculaId || !competenciaId || !texto.trim()) return;

    conclusionesLocales[rowKey(matriculaId, competenciaId)] = texto.trim();
  };

  const collectRows = (conclusionesMap = conclusionesLocales) => {
    return $all('.nota-select').map((select) => {
      const row = select.closest('tr');
      const cells = row ? row.querySelectorAll('td') : [];
      const alumnoCell = cells[2]?.innerText?.replace(/\s+/g, ' ')?.trim() || '';
      const codigoCell = cells[1]?.innerText?.replace(/\s+/g, ' ')?.trim() || '';
      const alumnoParts = alumnoCell.split(' | ');

      return {
        matricula_id: select.dataset.matricula || '',
        competencia_id: select.dataset.competencia || '',
        nota_id: select.dataset.notaId || '',
        nota: select.value || '',
        codigo_estudiante: codigoCell,
        alumno: alumnoCell,
        apellido_nombres: alumnoParts[0] || alumnoCell,
        nombres_extra: alumnoParts.slice(1).join(' | '),
        conclusion: conclusionesMap[rowKey(select.dataset.matricula, select.dataset.competencia)] || '',
        conclusion_fuente: conclusionesMap[rowKey(select.dataset.matricula, select.dataset.competencia)] ? 'local_modal' : '',
      };
    });
  };

  const obtenerMapaFilas = () => {
    const mapa = {};
    $all('.nota-select').forEach((select) => {
      const matriculaId = String(select.dataset.matricula || '').trim();
      const competenciaId = String(select.dataset.competencia || '').trim();
      const notaId = String(select.dataset.notaId || '').trim();
      if (!matriculaId || !competenciaId) return;

      const key = rowKey(matriculaId, competenciaId);
      mapa[key] = {
        key,
        matricula_id: matriculaId,
        competencia_id: competenciaId,
        nota_id: notaId,
      };
    });
    return mapa;
  };

  const obtenerConclusionesDesdeServidor = async () => {
    const mapaFilas = obtenerMapaFilas();
    const keysConNota = Object.keys(mapaFilas).filter((k) => mapaFilas[k].nota_id);
    const resultado = {};

    await Promise.all(keysConNota.map(async (key) => {
      const row = mapaFilas[key];
      try {
        const response = await fetch(`/admin/notas/conclusion/${row.nota_id}`, {
          method: 'GET',
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) return;
        const data = await response.json();
        const texto = String(data?.conclusion || '').trim();
        if (data?.success && texto) {
          resultado[key] = texto;
        }
      } catch (_error) {
        // Ignorar errores individuales para mantener la auditoría robusta.
      }
    }));

    return resultado;
  };

  const sincronizarConclusionesDesdeServidor = async () => {
    const desdeServidor = await obtenerConclusionesDesdeServidor();
    const merged = mergeConclusionesDesdeObjeto(desdeServidor);
    saveLocal();
    return {
      total_servidor: Object.keys(desdeServidor).length,
      agregadas: merged,
    };
  };

  const auditarCoberturaConclusiones = async () => {
    const mapaFilas = obtenerMapaFilas();
    const keys = Object.keys(mapaFilas);
    const keysConNotaId = keys.filter((k) => mapaFilas[k].nota_id);

    const localMap = obtenerConclusionesCombinadas();
    const serverMap = await obtenerConclusionesDesdeServidor();
    const finalMap = { ...localMap, ...serverMap };

    const conConclusion = keys.filter((k) => String(finalMap[k] || '').trim() !== '');
    const faltantes = keys.filter((k) => String(finalMap[k] || '').trim() === '');

    return {
      total_filas: keys.length,
      filas_con_nota_id: keysConNotaId.length,
      conclusiones_locales: Object.keys(localMap).length,
      conclusiones_desde_servidor: Object.keys(serverMap).length,
      filas_con_conclusion_total: conConclusion.length,
      filas_sin_conclusion: faltantes.length,
      faltantes_detalle: faltantes.map((k) => mapaFilas[k]),
    };
  };

  const buildPayload = () => {
    const conclusionesMap = obtenerConclusionesCombinadas();
    return {
      meta: pageMeta(),
      rows: collectRows(conclusionesMap),
    };
  };

  const saveLocal = () => {
    const payload = buildPayload();
    localStorage.setItem(currentKey(), JSON.stringify(payload));
    localStorage.setItem(`${currentKey()}:latest`, JSON.stringify(payload));
    guardarConclusionesLocales();
    return payload;
  };

  const parseCsv = (text) => {
    const rows = [];
    let current = '';
    let row = [];
    let inQuotes = false;

    for (let i = 0; i < text.length; i += 1) {
      const char = text[i];
      const next = text[i + 1];

      if (char === '"') {
        if (inQuotes && next === '"') {
          current += '"';
          i += 1;
        } else {
          inQuotes = !inQuotes;
        }
      } else if (char === ',' && !inQuotes) {
        row.push(current);
        current = '';
      } else if ((char === '\n' || char === '\r') && !inQuotes) {
        if (char === '\r' && next === '\n') i += 1;
        row.push(current);
        current = '';
        if (row.some((cell) => String(cell).trim() !== '')) {
          rows.push(row);
        }
        row = [];
      } else {
        current += char;
      }
    }

    if (current !== '' || row.length > 0) {
      row.push(current);
      if (row.some((cell) => String(cell).trim() !== '')) {
        rows.push(row);
      }
    }

    return rows;
  };

  const payloadFromCsvText = (csvText) => {
    const table = parseCsv(csvText);
    if (!table.length) return { meta: pageMeta(), rows: [] };

    const headers = table[0].map((h) => String(h || '').trim());
    const rows = table.slice(1).map((line) => {
      const item = {};
      headers.forEach((header, idx) => {
        item[header] = line[idx] ?? '';
      });

      return {
        matricula_id: String(item.matricula_id || '').trim(),
        competencia_id: String(item.competencia_id || '').trim(),
        nota_id: String(item.nota_id || '').trim(),
        nota: String(item.nota || '').trim(),
        codigo_estudiante: String(item.codigo_estudiante || '').trim(),
        alumno: String(item.alumno || '').trim(),
        apellido_nombres: String(item.apellido_nombres || '').trim(),
        nombres_extra: String(item.nombres_extra || '').trim(),
        conclusion: String(item.conclusion || '').trim(),
        conclusion_fuente: String(item.conclusion_fuente || '').trim(),
      };
    });

    return {
      meta: pageMeta(),
      rows,
    };
  };

  const actualizarIconoConclusion = (matriculaId, competenciaId, tieneTexto) => {
    const icon = document.querySelector(`.btn-message[data-matricula="${matriculaId}"][data-competencia-id="${competenciaId}"] i`);
    if (!icon) return;
    icon.style.color = tieneTexto ? '#28a745' : '#6c757d';
  };

  const aplicarConclusionesALaVista = (payloadRows) => {
    let restored = 0;

    payloadRows.forEach((row) => {
      const key = rowKey(row.matricula_id, row.competencia_id);
      const texto = String(row.conclusion || '').trim();
      if (!key || !texto) return;

      conclusionesLocales[key] = texto;
      actualizarIconoConclusion(row.matricula_id, row.competencia_id, true);
      restored += 1;
    });

    guardarConclusionesLocales();
    return restored;
  };

  const download = (content, filename, mimeType) => {
    const blob = new Blob([content], { type: mimeType });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(link.href), 1000);
  };

  const exportJson = (payload) => {
    const filename = `notas_backup_${payload.meta.aula_id || 'aula'}_${payload.meta.curso_id || 'curso'}_${payload.meta.periodo_id || 'periodo'}_${Date.now()}.json`;
    download(JSON.stringify(payload, null, 2), filename, 'application/json;charset=utf-8');
  };

  const exportCsv = (payload) => {
    const headers = [
      'matricula_id',
      'competencia_id',
      'nota_id',
      'nota',
      'codigo_estudiante',
      'alumno',
      'apellido_nombres',
      'nombres_extra',
      'conclusion',
      'conclusion_fuente',
    ];

    const lines = [headers.map(csvEscape).join(',')];
    payload.rows.forEach((row) => {
      lines.push([
        row.matricula_id,
        row.competencia_id,
        row.nota_id,
        row.nota,
        row.codigo_estudiante,
        row.alumno,
        row.apellido_nombres,
        row.nombres_extra,
        row.conclusion,
        row.conclusion_fuente,
      ].map(csvEscape).join(','));
    });

    const filename = `notas_backup_${payload.meta.aula_id || 'aula'}_${payload.meta.curso_id || 'curso'}_${payload.meta.periodo_id || 'periodo'}_${Date.now()}.csv`;
    download(lines.join('\n'), filename, 'text/csv;charset=utf-8');
  };

  const restoreFromPayload = (payload) => {
    if (!payload?.rows?.length) return { notas: 0, conclusiones: 0 };

    let restoredNotas = 0;
    payload.rows.forEach((row) => {
      if (!row.matricula_id || !row.competencia_id) return;

      const select = document.querySelector(`.nota-select[data-matricula="${row.matricula_id}"][data-competencia="${row.competencia_id}"]`);
      if (!select) return;

      select.value = row.nota || '';
      select.dispatchEvent(new Event('change', { bubbles: true }));
      restoredNotas += 1;
    });

    const restoredConclusiones = aplicarConclusionesALaVista(payload.rows);
    saveLocal();

    return {
      notas: restoredNotas,
      conclusiones: restoredConclusiones,
    };
  };

  const restoreLocal = () => {
    const raw = localStorage.getItem(currentKey()) || localStorage.getItem(`${currentKey()}:latest`);
    if (!raw) {
      console.warn('No se encontró backup local para esta combinación de aula/curso/periodo.');
      return { notas: 0, conclusiones: 0 };
    }

    const payload = JSON.parse(raw);
    return restoreFromPayload(payload);
  };

  const payloadFromText = (text, formatHint = '') => {
    const textClean = String(text || '').trim();
    if (!textClean) throw new Error('Texto vacío para importar.');

    const hint = String(formatHint || '').toLowerCase();
    const looksJson = hint === 'json' || textClean.startsWith('{') || textClean.startsWith('[');

    if (looksJson) {
      const parsed = JSON.parse(textClean);
      if (Array.isArray(parsed)) {
        return { meta: pageMeta(), rows: parsed };
      }
      if (parsed && Array.isArray(parsed.rows)) {
        return parsed;
      }
      throw new Error('JSON inválido: se esperaba { rows: [] } o un arreglo.');
    }

    return payloadFromCsvText(textClean);
  };

  const readFileAsText = (file) => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(String(reader.result || ''));
    reader.onerror = () => reject(new Error('No se pudo leer el archivo.'));
    reader.readAsText(file, 'utf-8');
  });

  const autoSaveHandler = (event) => {
    if (event.target && event.target.matches('.nota-select')) {
      saveLocal();
    }

    if (event.target && event.target.id === 'conclusion_texto') {
      capturarConclusionDesdeModal();
      saveLocal();
    }
  };

  const parseUrlEncodedConclusiones = (dataText) => {
    const incoming = {};
    if (!dataText || typeof dataText !== 'string') return incoming;

    const params = new URLSearchParams(dataText);
    const bucket = {};

    params.forEach((value, key) => {
      const match = key.match(/^conclusiones\[(\d+)\]\[(matricula_id|competencia_id|conclusion)\]$/);
      if (!match) return;

      const idx = match[1];
      const field = match[2];
      if (!bucket[idx]) bucket[idx] = {};
      bucket[idx][field] = value;
    });

    Object.keys(bucket).forEach((idx) => {
      const item = bucket[idx];
      const key = rowKey(item.matricula_id, item.competencia_id);
      const texto = String(item.conclusion || '').trim();
      if (key && texto) {
        incoming[key] = texto;
      }
    });

    return incoming;
  };

  document.addEventListener('click', (event) => {
    if (event.target && (event.target.id === 'btnGuardarConclusion' || event.target.closest('#btnGuardarConclusion'))) {
      capturarConclusionDesdeModal();
      saveLocal();
    }

    const btnMensaje = event.target?.closest ? event.target.closest('.btn-message') : null;
    if (btnMensaje) {
      const matriculaId = btnMensaje.getAttribute('data-matricula') || '';
      const competenciaId = btnMensaje.getAttribute('data-competencia-id') || btnMensaje.getAttribute('data-competencia') || '';
      const key = rowKey(matriculaId, competenciaId);
      const texto = conclusionesLocales[key] || '';

      if (texto) {
        setTimeout(() => {
          const textarea = $('#conclusion_texto');
          if (!textarea) return;
          if (!String(textarea.value || '').trim()) {
            textarea.value = texto;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
          }
        }, 250);
      }
    }
  }, true);

  document.addEventListener('change', autoSaveHandler, true);
  document.addEventListener('input', autoSaveHandler, true);
  window.addEventListener('beforeunload', saveLocal);

  window.exportNotasBackup = async (format = 'json') => {
    console.log('[Backup notas] Consultando conclusiones en el servidor...');
    await sincronizarConclusionesDesdeServidor();
    const payload = saveLocal();
    const normalizedFormat = String(format).toLowerCase();

    if (normalizedFormat === 'csv') {
      exportCsv(payload);
    } else {
      exportJson(payload);
    }

    return payload;
  };

  window.importarNotasBackupDesdeTexto = (text, format = '') => {
    const payload = payloadFromText(text, format);
    return restoreFromPayload(payload);
  };

  window.importarNotasBackupDesdeArchivo = async () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json,.csv,text/csv,application/json';

    const file = await new Promise((resolve) => {
      input.addEventListener('change', () => resolve(input.files?.[0] || null), { once: true });
      input.click();
    });

    if (!file) {
      return { notas: 0, conclusiones: 0 };
    }

    const text = await readFileAsText(file);
    const formatHint = file.name.toLowerCase().endsWith('.csv') ? 'csv' : 'json';
    return window.importarNotasBackupDesdeTexto(text, formatHint);
  };

  window.guardarBackupNotas = () => saveLocal();
  window.restaurarBackupNotas = () => restoreLocal();
  window.verBackupNotas = () => JSON.parse(localStorage.getItem(currentKey()) || localStorage.getItem(`${currentKey()}:latest`) || 'null');
  window.verConclusionesBackupNotas = () => ({ ...conclusionesLocales });
  window.sincronizarConclusionesServidorBackup = () => sincronizarConclusionesDesdeServidor();
  window.auditarConclusionesBackup = () => auditarCoberturaConclusiones();
  // Alias mantenido por compatibilidad — exportNotasBackup ya es exhaustivo
  window.exportNotasBackupExhaustivo = (format = 'json') => window.exportNotasBackup(format);

  if (window.jQuery && typeof window.jQuery === 'function') {
    window.jQuery(document).ajaxSend(function(_event, _jqXHR, settings) {
      const url = String(settings?.url || '');
      if (!url.includes('/admin/notas/save')) return;

      const data = settings?.data;
      if (!data) return;

      if (typeof data === 'string') {
        mergeConclusionesDesdeObjeto(parseUrlEncodedConclusiones(data));
      } else if (typeof data === 'object' && Array.isArray(data.conclusiones)) {
        const incoming = {};
        data.conclusiones.forEach((item) => {
          const key = rowKey(item?.matricula_id, item?.competencia_id);
          const texto = String(item?.conclusion || '').trim();
          if (key && texto) {
            incoming[key] = texto;
          }
        });
        mergeConclusionesDesdeObjeto(incoming);
      }

      // Tomar snapshot inmediato de lo que el front está enviando, aunque falle el POST.
      saveLocal();
    });
  }

  cargarConclusionesLocalesGuardadas();
  Object.keys(conclusionesLocales).forEach((key) => {
    const [matriculaId, competenciaId] = key.split('_');
    actualizarIconoConclusion(matriculaId, competenciaId, true);
  });

  const initialPayload = saveLocal();

  console.log('[Backup notas] listo.');
  console.log('Usa exportNotasBackup("json") o exportNotasBackup("csv") para descargar (consulta BD + localStorage).');
  console.log('exportNotasBackupExhaustivo es alias de exportNotasBackup (ambos buscan en servidor y local).');
  console.log('Usa auditarConclusionesBackup() para ver cobertura completa y faltantes.');
  console.log('Usa importarNotasBackupDesdeArchivo() para importar un JSON/CSV.');
  console.log('Usa importarNotasBackupDesdeTexto(texto, "json"|"csv") para importar manualmente.');
  console.log('Usa restaurarBackupNotas() para reponer desde localStorage.');
  console.log('Incluye captura de estado JS del front y del payload de guardar (si se intenta guardar).');
  console.log('Backup actual guardado en localStorage con clave:', currentKey());
  return initialPayload;
})();
