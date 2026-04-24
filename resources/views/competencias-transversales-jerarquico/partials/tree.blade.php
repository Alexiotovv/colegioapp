{{-- resources/views/competencias-transversales-jerarquico/partials/tree.blade.php --}}
@if($niveles && count($niveles) > 0)
    @foreach($niveles as $nivel)
    <div class="tree-level-1" data-nivel-id="{{ $nivel->id }}">
        <div class="tree-item">
            <div class="tree-header" onclick="toggleChildren(this)">
                <div class="tree-title">
                    <span class="toggle-icon">▶</span>
                    <i class="fas fa-layer-group" style="color: #2c5031;"></i>
                    <strong>{{ $nivel->nombre }}</strong>
                    <span class="badge bg-secondary">{{ $nivel->competenciasTransversales ? $nivel->competenciasTransversales->count() : 0 }} competencias</span>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showCompetenciaModal(null, {{ $nivel->id }})">
                        <i class="fas fa-plus"></i> Competencia
                    </button>
                </div>
            </div>
            <div class="tree-children">
                @if($nivel->competenciasTransversales && $nivel->competenciasTransversales->count() > 0)
                    @foreach($nivel->competenciasTransversales as $competencia)
                    <div class="tree-level-2" data-competencia-id="{{ $competencia->id }}">
                        <div class="tree-item">
                            <div class="tree-header">
                                <div class="tree-title">
                                    <i class="fas fa-exchange-alt" style="color: #c8a951;"></i>
                                    <strong>{{ $competencia->nombre }}</strong>
                                    <span class="badge badge-ct">{{ $competencia->orden }}</span>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); showCompetenciaModal({{ $competencia->id }}, {{ $nivel->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-{{ $competencia->activo ? 'secondary' : 'success' }}" 
                                            onclick="event.stopPropagation(); toggleCompetencia({{ $competencia->id }})">
                                        <i class="fas fa-{{ $competencia->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteCompetencia({{ $competencia->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-message">No hay competencias transversales registradas para este nivel</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <p class="text-muted">No hay niveles registrados</p>
        <button class="btn btn-primary" onclick="showCompetenciaModal(null, null)">
            <i class="fas fa-plus me-2"></i> Crear primera competencia
        </button>
    </div>
@endif