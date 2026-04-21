<div class="modal fade" id="modalTipoInasistencia" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-check me-2"></i>
                    <span id="modalTipoTitle">Nuevo Tipo de Inasistencia</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTipo">
                <div class="modal-body">
                    <input type="hidden" id="tipo_id" name="tipo_id">
                    <input type="hidden" id="tipo_nivel_id" name="nivel_id">
                    
                    <div class="mb-3">
                        <label for="tipo_nombre" class="form-label required-field">Nombre del Tipo</label>
                        <input type="text" class="form-control" id="tipo_nombre" name="nombre" required>
                        <div class="invalid-feedback" id="tipo_nombre_error"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="tipo_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="tipo_orden" name="orden" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="tipo_activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="tipo_activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveTipo">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>