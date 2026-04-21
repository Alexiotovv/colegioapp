
<div class="modal fade" id="modalEvaluacion" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-list me-2"></i>
                    <span id="modalEvaluacionTitle">Nueva Evaluación</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEvaluacion">
                <div class="modal-body">
                    <input type="hidden" id="evaluacion_id" name="evaluacion_id">
                    <input type="hidden" id="evaluacion_nivel_id" name="nivel_id">
                    
                    <div class="mb-3">
                        <label for="evaluacion_nombre" class="form-label required-field">Nombre de la Evaluación</label>
                        <input type="text" class="form-control" id="evaluacion_nombre" name="nombre" required>
                        <div class="invalid-feedback" id="evaluacion_nombre_error"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="evaluacion_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="evaluacion_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="evaluacion_orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="evaluacion_orden" name="orden" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="evaluacion_activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="evaluacion_activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveEvaluacion">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>