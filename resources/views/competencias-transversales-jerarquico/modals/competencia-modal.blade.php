{{-- resources/views/competencias-transversales-jerarquico/modals/competencia-modal.blade.php --}}
<div class="modal fade" id="modalCompetenciaTransversal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>
                    <span id="modalCTitle">Nueva Competencia Transversal</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCompetencia">
                <div class="modal-body">
                    <input type="hidden" id="competencia_id" name="competencia_id">
                    <input type="hidden" id="competencia_nivel_id" name="nivel_id">
                    
                    <div class="mb-3">
                        <label for="competencia_nombre" class="form-label required-field">Nombre de la Competencia</label>
                        <input type="text" class="form-control" id="competencia_nombre" name="nombre" required>
                        <div class="invalid-feedback" id="competencia_nombre_error"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="competencia_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="competencia_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="competencia_orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="competencia_orden" name="orden" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="competencia_activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="competencia_activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCompetencia">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>