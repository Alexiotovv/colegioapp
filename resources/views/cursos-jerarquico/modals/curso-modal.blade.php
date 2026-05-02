
<!-- Modal Curso -->
<div class="modal fade" id="modalCurso" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-book me-2"></i>
                    <span id="modalCursoTitle">Nuevo Curso</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCurso">
                <div class="modal-body">
                    <input type="hidden" id="curso_id" name="curso_id">
                    <input type="hidden" id="curso_nivel_id" name="nivel_id">
                    <input type="hidden" id="curso_anio_id" name="anio_academico_id">
                    <input type="hidden" name="aulas_excluidas_present" value="1">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="curso_codigo" class="form-label required-field">Código</label>
                            <input type="text" class="form-control" id="curso_codigo" name="codigo" 
                                   placeholder="Ej: MAT01" required>
                            <div class="invalid-feedback" id="curso_codigo_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="curso_tipo" class="form-label required-field">Tipo</label>
                            <select class="form-select" id="curso_tipo" name="tipo" required>
                                <option value="">Seleccionar</option>
                                <option value="AREA">Área Curricular</option>
                                <option value="TALLER">Taller</option>
                                <option value="TUTORIA">Tutoría</option>
                            </select>
                            <div class="invalid-feedback" id="curso_tipo_error"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="curso_nombre" class="form-label required-field">Nombre del Curso</label>
                        <input type="text" class="form-control" id="curso_nombre" name="nombre" 
                               placeholder="Ej: Matemática, Comunicación" required>
                        <div class="invalid-feedback" id="curso_nombre_error"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="curso_horas" class="form-label">Horas Semanales</label>
                            <input type="number" class="form-control" id="curso_horas" name="horas_semanales" 
                                   min="0" max="40" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="curso_orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="curso_orden" name="orden" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="curso_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="curso_descripcion" name="descripcion" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="curso_aulas_excluidas" class="form-label">No enseñar en estas aulas</label>
                        <select class="form-select" id="curso_aulas_excluidas" name="aulas_excluidas[]" multiple size="8">
                            @foreach($niveles as $nivel)
                                @php
                                    $aulasNivel = $aulasPorNivel[$nivel->id] ?? collect();
                                @endphp
                                @if($aulasNivel->count())
                                    <optgroup label="{{ $nivel->nombre }}">
                                        @foreach($aulasNivel as $aula)
                                            <option value="{{ $aula->id }}">{{ $aula->nombreCompleto }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">Si seleccionas un aula, este curso no aparecerá en la libreta de esa sección.</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Información:</strong> El curso se asignará al nivel seleccionado y al año académico actual.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCurso">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>