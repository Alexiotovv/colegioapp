@if($niveles && count($niveles) > 0)
    @foreach($niveles as $nivel)
    <div class="tree-level-1" data-nivel-id="{{ $nivel->id }}">
        <div class="tree-item">
            <div class="tree-header" onclick="toggleChildren(this)">
                <div class="tree-title">
                    <span class="toggle-icon">▶</span>
                    <i class="fas fa-layer-group" style="color: #2c5031;"></i>
                    <strong>{{ $nivel->nombre }}</strong>
                    <span class="badge bg-secondary">{{ $nivel->cursos ? $nivel->cursos->count() : 0 }} cursos</span>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showCursoModal(null, {{ $nivel->id }})">
                        <i class="fas fa-plus"></i> Curso
                    </button>
                </div>
            </div>
            <div class="tree-children">
                @if($nivel->cursos && $nivel->cursos->count() > 0)
                    @foreach($nivel->cursos as $curso)
                    <div class="tree-level-2" data-curso-id="{{ $curso->id }}">
                        <div class="tree-item">
                            <div class="tree-header" onclick="toggleChildren(this)">
                                <div class="tree-title">
                                    <span class="toggle-icon">▶</span>
                                    <i class="fas fa-book" style="color: #c8a951;"></i>
                                    <strong>{{ $curso->nombre }}</strong>
                                    <span class="badge badge-curso">{{ $curso->codigo }}</span>
                                    <span class="badge bg-info">{{ $curso->horas_semanales }} h/sem</span>
                                    <span class="badge bg-secondary">{{ $curso->competencias ? $curso->competencias->count() : 0 }} competencias</span>
                                    <span class="competencia-order-spinner d-none" data-curso-spinner="{{ $curso->id }}">
                                        <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
                                    </span>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); showCursoModal({{ $curso->id }}, {{ $nivel->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showCompetenciaModal(null, {{ $curso->id }})">
                                        <i class="fas fa-plus"></i> Competencia
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteCurso({{ $curso->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="tree-children">
                                @if($curso->competencias && $curso->competencias->count() > 0)
                                    <div class="competencias-sortable" data-curso-id="{{ $curso->id }}">
                                        @foreach($curso->competencias as $competencia)
                                        <div class="tree-level-3" data-competencia-id="{{ $competencia->id }}" data-orden="{{ (int) ($competencia->orden ?? 0) }}">
                                            <div class="tree-item">
                                                <div class="tree-header" onclick="toggleChildren(this)">
                                                    <div class="tree-title">
                                                        <span class="drag-handle" onclick="event.stopPropagation();" title="Arrastra para ordenar">
                                                            <i class="fas fa-grip-vertical"></i>
                                                        </span>
                                                        <span class="toggle-icon">▶</span>
                                                        <i class="fas fa-star" style="color: #3498db;"></i>
                                                        <span class="badge bg-light text-dark competencia-order-number">{{ (int) ($competencia->orden ?? 0) }}</span>
                                                        <strong>{{ $competencia->nombre }}</strong>
                                                        <span class="badge badge-competencia">{{ $competencia->ponderacion }}%</span>
                                                        <span class="badge bg-secondary">{{ $competencia->capacidades ? $competencia->capacidades->count() : 0 }} capacidades</span>
                                                    </div>
                                                    <div class="action-buttons">
                                                        <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); showCompetenciaModal({{ $competencia->id }}, {{ $curso->id }})">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); showCapacidadModal(null, {{ $competencia->id }})">
                                                            <i class="fas fa-plus"></i> Capacidad
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteCompetencia({{ $competencia->id }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="tree-children">
                                                    @if($competencia->capacidades && $competencia->capacidades->count() > 0)
                                                        @foreach($competencia->capacidades as $capacidad)
                                                        <div class="tree-level-3" style="margin-left: 30px;" data-capacidad-id="{{ $capacidad->id }}">
                                                            <div class="tree-item" style="background: #e8f0fe;">
                                                                <div class="tree-header">
                                                                    <div class="tree-title">
                                                                        <i class="fas fa-tasks" style="color: #2c5031;"></i>
                                                                        <span>{{ $capacidad->nombre }}</span>
                                                                        <span class="badge badge-capacidad">{{ $capacidad->ponderacion }}%</span>
                                                                    </div>
                                                                    <div class="action-buttons">
                                                                        <button class="btn btn-sm btn-warning" onclick="showCapacidadModal({{ $capacidad->id }}, {{ $competencia->id }})">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-danger" onclick="deleteCapacidad({{ $capacidad->id }})">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <div class="empty-message">No hay capacidades registradas</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-message">No hay competencias registradas</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-message">No hay cursos registrados para este nivel</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <p class="text-muted">No hay datos registrados para el año seleccionado</p>
        <button class="btn btn-primary" onclick="showCursoModal(null, null)">
            <i class="fas fa-plus me-2"></i> Crear primer curso
        </button>
    </div>
@endif