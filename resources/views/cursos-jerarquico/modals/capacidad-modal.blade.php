<!-- Modal Capacidad -->
<div class="modal fade" id="modalCapacidad" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-tasks me-2"></i>
                    <span id="modalCapacidadTitle">Nueva Capacidad</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCapacidad">
                <div class="modal-body">
                    <input type="hidden" id="capacidad_id" name="capacidad_id">
                    <input type="hidden" id="capacidad_competencia_id" name="competencia_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Competencia</label>
                        <input type="text" class="form-control" id="capacidad_competencia_nombre" readonly disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="capacidad_nombre" class="form-label required-field">Nombre de la Capacidad</label>
                        <input type="text" class="form-control" id="capacidad_nombre" name="nombre" 
                               placeholder="Ej: Traduce cantidades a expresiones numéricas" required>
                        <div class="invalid-feedback" id="capacidad_nombre_error"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="capacidad_ponderacion" class="form-label">Ponderación (%)</label>
                            <input type="number" step="0.01" class="form-control" id="capacidad_ponderacion" 
                                   name="ponderacion" min="0" max="100" value="100">
                            <small class="text-muted">Porcentaje dentro de la competencia</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="capacidad_orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="capacidad_orden" name="orden" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="capacidad_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="capacidad_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info" id="btnSaveCapacidad">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>