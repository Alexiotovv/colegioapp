@if(empty($tree) || count($tree) === 0)
    <div class="empty-tree">
        <div class="empty-tree-icon">📚</div>
        <div>No hay asignaciones de carga horaria para este año.</div>
    </div>
@else
    <div class="report-tree">
        @foreach($tree as $nivel)
            <div class="tree-item" data-key="nivel-{{ $nivel['id'] }}">
                <button type="button" class="toggle-button">
                    <span class="toggle-icon">▼</span>
                    <span class="node-label">📚 Nivel: {{ $nivel['nombre'] }}</span>
                </button>
                <div class="node-content collapsed">
                    @foreach($nivel['grados'] as $grado)
                        <div class="tree-item" data-key="grado-{{ $grado['id'] }}">
                            <button type="button" class="toggle-button grado-toggle">
                                <span class="toggle-icon">▼</span>
                                <span class="node-label">📖 Grado: {{ $grado['nombre'] }}</span>
                            </button>
                            <div class="node-content collapsed">
                                @foreach($grado['aulas'] as $aula)
                                    <div class="tree-item" data-key="aula-{{ $aula['id'] }}">
                                        <button type="button" class="toggle-button aula-toggle">
                                            <span class="toggle-icon">▼</span>
                                            <span class="node-label">{{ $aula['nombre'] }}</span>
                                        </button>
                                        <div class="node-content collapsed">
                                            @foreach($aula['docentes'] as $docente)
                                                <div class="tree-item" data-key="docente-{{ $docente['id'] }}">
                                                    <button type="button" class="toggle-button docente-toggle">
                                                        <span class="toggle-icon">▼</span>
                                                        <span class="node-label">👨‍🏫 {{ $docente['nombre'] }}</span>
                                                    </button>
                                                    <div class="node-content collapsed">
                                                        @foreach($docente['cursos'] as $curso)
                                                            <div class="course-row">
                                                                <div class="course-title">📘 {{ $curso['nombre'] }} <span class="course-hours">({{ $curso['horas_semanales'] }} h/sem)</span></div>
                                                                <div class="course-schedule">🕐 {{ $curso['detalle_horario'] }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
