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
                <td> style="text-align: center;">
                    <select class="form-select valoracion-select" data-matricula="${matricula.id}" data-evaluacion="${evaluacion.id}" ${!registrosHabilitados ? 'disabled' : ''} style="width: 110px; margin: 0 auto;">
                        <option value="">Seleccionar</option>
                        ${Object.keys(valoraciones).map(key => `<option value="${key}" ${valoracionValue === key ? 'selected' : ''}>${valoraciones[key]}</option>`).join('')}
                    </select>
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
    });
    
    // Evento change
    $('.valoracion-select').on('change', function() {
        if ($(this).val()) {
            $(this).addClass('registro-guardado');
        } else {
            $(this).removeClass('registro-guardado');
        }
        progressBar.update();
    });
}