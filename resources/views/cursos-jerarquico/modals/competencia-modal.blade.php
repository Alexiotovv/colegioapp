
<!-- Modal Competencia -->
<div class="modal fade" id="modalCompetencia" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-star me-2"></i>
                    <span id="modalCompetenciaTitle">Nueva Competencia</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCompetencia">
                <div class="modal-body">
                    <input type="hidden" id="competencia_id" name="competencia_id">
                    <input type="hidden" id="competencia_curso_id" name="curso_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Curso</label>
                        <input type="text" class="form-control" id="competencia_curso_nombre" readonly disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="competencia_nombre" class="form-label required-field">Nombre de la Competencia</label>
                        <input type="text" class="form-control" id="competencia_nombre" name="nombre" 
                               placeholder="Ej: Resuelve problemas de cantidad" required>
                        <div class="invalid-feedback" id="competencia_nombre_error"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="competencia_ponderacion" class="form-label">Ponderación (%)</label>
                            <input type="number" step="0.01" class="form-control" id="competencia_ponderacion" 
                                   name="ponderacion" min="0" max="100" value="100">
                            <small class="text-muted">Porcentaje dentro del curso</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="competencia_orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="competencia_orden" name="orden" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="competencia_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="competencia_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnSaveCompetencia">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>