{{-- resources/views/evaluaciones-actitudinales-jerarquico/partials/tree.blade.php --}}
@if($niveles && count($niveles) > 0)
    @foreach($niveles as $nivel)
    <div class="tree-level-1" data-nivel-id="{{ $nivel->id }}">
        <div class="tree-item">
            <div class="tree-header" onclick="toggleChildren(this)">
                <div class="tree-title">
                    <span class="toggle-icon">▶</span>
                    <i class="fas fa-layer-group" style="color: #2c5031;"></i>
                    <strong>{{ $nivel->nombre }}</strong>
                    <span class="badge bg-secondary">{{ $nivel->evaluacionesActitudinales ? $nivel->evaluacionesActitudinales->count() : 0 }} evaluaciones</span>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showEvaluacionModal(null, {{ $nivel->id }})">
                        <i class="fas fa-plus"></i> Evaluación
                    </button>
                </div>
            </div>
            <div class="tree-children">
                @if($nivel->evaluacionesActitudinales && $nivel->evaluacionesActitudinales->count() > 0)
                    @foreach($nivel->evaluacionesActitudinales as $evaluacion)
                    <div class="tree-level-2" data-evaluacion-id="{{ $evaluacion->id }}">
                        <div class="tree-item">
                            <div class="tree-header" onclick="toggleChildren(this)">
                                <div class="tree-title">
                                    <span class="toggle-icon">▶</span>
                                    <i class="fas fa-heart" style="color: #e83e8c;"></i>
                                    <strong>{{ $evaluacion->nombre }}</strong>
                                    <span class="badge badge-evaluacion">{{ $evaluacion->orden }}</span>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); showEvaluacionModal({{ $evaluacion->id }}, {{ $nivel->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-{{ $evaluacion->activo ? 'secondary' : 'success' }}" 
                                            onclick="event.stopPropagation(); toggleEvaluacion({{ $evaluacion->id }})">
                                        <i class="fas fa-{{ $evaluacion->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteEvaluacion({{ $evaluacion->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-message">No hay evaluaciones actitudinales para este nivel</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <p class="text-muted">No hay niveles registrados</p>
        <button class="btn btn-primary" onclick="showEvaluacionModal(null, null)">
            <i class="fas fa-plus me-2"></i> Crear primera evaluación
        </button>
    </div>
@endif