
@if($niveles && count($niveles) > 0)
    @foreach($niveles as $nivel)
    <div class="tree-level-1" data-nivel-id="{{ $nivel->id }}">
        <div class="tree-item">
            <div class="tree-header" onclick="toggleChildren(this)">
                <div class="tree-title">
                    <span class="toggle-icon">▶</span>
                    <i class="fas fa-layer-group" style="color: #2c5031;"></i>
                    <strong>{{ $nivel->nombre }}</strong>
                    <span class="badge bg-secondary">{{ $nivel->tipos_inasistencia ? $nivel->tipos_inasistencia->count() : 0 }} tipos</span>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showTipoModal(null, {{ $nivel->id }})">
                        <i class="fas fa-plus"></i> Tipo
                    </button>
                </div>
            </div>
            <div class="tree-children">
                @if($nivel->tiposInasistencia && $nivel->tiposInasistencia->count() > 0)
                    @foreach($nivel->tiposInasistencia as $tipo)
                    <div class="tree-level-2" data-tipo-id="{{ $tipo->id }}">
                        <div class="tree-item">
                            <div class="tree-header">
                                <div class="tree-title">
                                    <i class="fas fa-calendar-times" style="color: #c8a951;"></i>
                                    <strong>{{ $tipo->nombre }}</strong>
                                    <span class="badge badge-tipo">{{ $tipo->orden }}</span>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); showTipoModal({{ $tipo->id }}, {{ $nivel->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-{{ $tipo->activo ? 'secondary' : 'success' }}" 
                                            onclick="event.stopPropagation(); toggleTipo({{ $tipo->id }})">
                                        <i class="fas fa-{{ $tipo->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteTipo({{ $tipo->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-message">No hay tipos de inasistencia registrados para este nivel</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <p class="text-muted">No hay niveles registrados</p>
        <button class="btn btn-primary" onclick="showTipoModal(null, null)">
            <i class="fas fa-plus me-2"></i> Crear primer tipo
        </button>
    </div>
@endif