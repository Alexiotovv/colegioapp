<div class="modal fade" id="modalTipoOtraEvaluacion" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-list me-2"></i>
                    <span id="modalTipoTitle">Nuevo Tipo de Evaluación</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTipo">
                <div class="modal-body">
                    <input type="hidden" id="tipo_id" name="tipo_id">
                    <input type="hidden" id="tipo_nivel_id" name="nivel_id">
                    
                    <div class="mb-3">
                        <label for="tipo_nombre" class="form-label required-field">Nombre de la Evaluación</label>
                        <input type="text" class="form-control" id="tipo_nombre" name="nombre" required>
                        <div class="invalid-feedback" id="tipo_nombre_error"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="tipo_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_tipo_dato" class="form-label required-field">Tipo de Dato</label>
                            <select class="form-select" id="tipo_tipo_dato" name="tipo_dato" required>
                                <option value="NUMERICO">Numérico (1-40)</option>
                                <option value="LITERAL">Literal (AD, A, B, C, ND)</option>
                            </select>
                            <div class="invalid-feedback" id="tipo_tipo_dato_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo_orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="tipo_orden" name="orden" value="0">
                        </div>
                    </div>
                    
                    <!-- Campos para tipo numérico -->
                    <div class="row numeric-fields">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_min_valor" class="form-label">Valor Mínimo</label>
                            <input type="number" class="form-control" id="tipo_min_valor" name="min_valor" value="1" min="1" max="40">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo_max_valor" class="form-label">Valor Máximo</label>
                            <input type="number" class="form-control" id="tipo_max_valor" name="max_valor" value="40" min="1" max="40">
                        </div>
                    </div>
                    
                    <!-- Campos para tipo literal -->
                    <div class="row literal-fields" style="display: none;">
                        <div class="col-md-12 mb-3">
                            <label for="tipo_opciones_literales" class="form-label">Opciones Literales (separadas por comas)</label>
                            <input type="text" class="form-control" id="tipo_opciones_literales" name="opciones_literales" placeholder="Ej: AD, A, B, C, ND">
                            <small class="text-muted">Ingrese las opciones separadas por comas. Ejemplo: AD, A, B, C, ND</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
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