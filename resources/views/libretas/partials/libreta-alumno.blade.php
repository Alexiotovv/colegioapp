@php
    $alumno = $matricula->alumno;
    $aula = $matricula->aula;
    $grado = $aula->grado;
    $nivel = $grado ? $grado->nivel : null;
    $nivelId = $nivel->id ?? 0;
    $esPrimaria = $nivel && $nivel->nombre == 'Primaria';
    
    // ==================== 1. CURSOS Y COMPETENCIAS (filtrado por nivel) ====================
    $cursos = \App\Models\Curso::with([
        'competencias' => function($q){
            $q->where('activo', true)->orderBy('orden');
        },
        'aulasExcluidas:id'
    ])
    ->where('nivel_id', $nivelId)
    ->where('activo', true)
    ->ordered()
    ->get();

    $cursos = $cursos->reject(function ($curso) use ($aula) {
        return $curso->aulasExcluidas->contains('id', $aula->id);
    })->values();
    
    $notasPorPeriodo = [];
    
    foreach ($periodos as $periodo) {
        foreach ($cursos as $curso) {
            foreach ($curso->competencias as $competencia) {
                $nota = \App\Models\Nota::where('matricula_id', $matricula->id)
                    ->where('competencia_id', $competencia->id)
                    ->where('periodo_id', $periodo->id)
                    ->first();
                $notasPorPeriodo[$periodo->id][$competencia->id] = $nota;
            }
        }
    }
    
    // ==================== 2. APRECIACIONES DEL TUTOR ====================
    $apreciaciones = [];
    foreach ($periodos as $periodo) {
        $apreciacion = \App\Models\Apreciacion::where('matricula_id', $matricula->id)
            ->where('periodo_id', $periodo->id)
            ->first();
        $apreciaciones[$periodo->id] = $apreciacion;
    }
    
    // ==================== 3. EVALUACIONES DEL PADRE (filtrado por nivel) ====================
    $evaluacionesPadre = \App\Models\Evaluacion::where('activo', true)
        ->where('nivel_id', $nivelId)  // 👈 Filtrar por nivel
        ->orderBy('orden')
        ->get();
    
    $registrosEvaluacionesPadre = [];
    foreach ($periodos as $periodo) {
        foreach ($evaluacionesPadre as $evaluacion) {
            $registro = \App\Models\RegistroEvaluacion::where('matricula_id', $matricula->id)
                ->where('evaluacion_id', $evaluacion->id)
                ->where('periodo_id', $periodo->id)
                ->first();
            $registrosEvaluacionesPadre[$periodo->id][$evaluacion->id] = $registro;
        }
    }
    
    // ==================== 4. INASISTENCIAS (filtrado por nivel) ====================
    $tiposInasistencia = \App\Models\TipoInasistencia::where('activo', true)
        ->where('nivel_id', $nivelId)  // 👈 Filtrar por nivel
        ->orderBy('orden')
        ->get();
    
    $inasistencias = [];
    foreach ($periodos as $periodo) {
        foreach ($tiposInasistencia as $tipo) {
            $inasistencia = \App\Models\RegistroAsistencia::where('matricula_id', $matricula->id)
                ->where('tipo_inasistencia_id', $tipo->id)
                ->where('periodo_id', $periodo->id)
                ->first();
            $inasistencias[$periodo->id][$tipo->id] = $inasistencia;
        }
    }
    
    // ==================== 5. COMPETENCIAS TRANSVERSALES (filtrado por nivel) ====================
    $competenciasTransversales = \App\Models\CompetenciaTransversal::where('activo', true)
        ->where('nivel_id', $nivelId)  // 👈 Filtrar por nivel
        ->orderBy('orden')
        ->get();
    
    $registrosCT = [];
    foreach ($periodos as $periodo) {
        foreach ($competenciasTransversales as $ct) {
            $registro = \App\Models\RegistroCompetenciaTransversal::where('matricula_id', $matricula->id)
                ->where('competencia_transversal_id', $ct->id)
                ->where('periodo_id', $periodo->id)
                ->first();
            $registrosCT[$periodo->id][$ct->id] = $registro;
        }
    }
    
    // ==================== 6. OTRAS EVALUACIONES (filtrado por nivel) ====================
    $otrasEvaluaciones = \App\Models\TipoOtraEvaluacion::where('activo', true)
        ->where('nivel_id', $nivelId)  // 👈 Filtrar por nivel
        ->orderBy('orden')
        ->get();
    
    $registrosOtras = [];
    foreach ($periodos as $periodo) {
        foreach ($otrasEvaluaciones as $tipo) {
            $registro = \App\Models\RegistroOtraEvaluacion::where('matricula_id', $matricula->id)
                ->where('tipo_otra_evaluacion_id', $tipo->id)
                ->where('periodo_id', $periodo->id)
                ->first();
            $registrosOtras[$periodo->id][$tipo->id] = $registro;
        }
    }


    //Evaluación actitudinal
    $evaluacionesActitudinales = \App\Models\EvaluacionActitudinal::where('activo', true)
        ->where('nivel_id', $nivelId)
        ->orderBy('orden')
        ->get();
    $registrosEvaluacionesActitudinales = [];
    foreach ($periodos as $periodo) {
        foreach ($evaluacionesActitudinales as $evaluacion) {
                $registro = \App\Models\RegistroEvaluacionActitudinal::where('matricula_id', $matricula->id)
                    ->where('eval_actitudinal_id', $evaluacion->id)
                ->where('periodo_id', $periodo->id)
                ->first();
            $registrosEvaluacionesActitudinales[$periodo->id][$evaluacion->id] = $registro;
        }
    }


@endphp

@php
    // Obtener configuración de cuadros por nivel (si existe). Si es null => mostrar todos por defecto
    $cuadrosForNivel = \App\Models\ConfiguracionLibretaCuadro::getCuadrosForNivel($nivelId);
    function __cuadro_enabled($key, $cuadrosForNivel) {
        if ($cuadrosForNivel === null) return true; // no hay configuración -> mostrar todo
        return is_array($cuadrosForNivel) && in_array($key, $cuadrosForNivel);
    }
@endphp

    


<!-- ==================== TABLA DE NOTAS POR COMPETENCIA ==================== -->
@if(__cuadro_enabled('cursos_competencias', $cuadrosForNivel))
<table class="tabla-notas">
    <thead>
        <tr>
            <th rowspan="2" style="width: 10%;">Área curricular</th>
            <th rowspan="2" style="width: 35%;">Competencias</th>
            @foreach($periodos as $periodo)
                <th colspan="2" style="text-align: center;">{{ $periodo->nombre }}</th>
            @endforeach
            <th rowspan="2" style="width: 8%; max-width: 50px;">NL alcanzado</th>
        </tr>
        <tr>
            @foreach($periodos as $periodo)
                <th style="width: 5%; max-width: 40px;">NL</th>
                <th style="width: 20%;">Conclusión descriptiva</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php
            $lastCurso = null;
        @endphp
        
        @foreach($cursos as $curso)
            @foreach($curso->competencias as $index => $competencia)
                @php
                    $suma = 0;
                    $contador = 0;
                @endphp

                <tr>
                    @if($index == 0)
                        <td rowspan="{{ $curso->competencias->count() }}" style="vertical-align: middle; background-color: #f9f9f9;">
                            <strong>{{ $curso->nombre }}</strong>
                        </td>
                    @endif

                    <td style="text-align: left;">
                        {{ $competencia->nombre }}
                    </td>

                    @foreach($periodos as $periodo)
                        @php
                            $nota = $notasPorPeriodo[$periodo->id][$competencia->id] ?? null;
                            $valor = $nota ? $nota->nota : '-';
                            $conclusion = $nota && $nota->conclusionDescriptiva
                                ? $nota->conclusionDescriptiva->conclusion
                                : '';

                            if (is_numeric($valor)) {
                                $suma += floatval($valor);
                                $contador++;
                            }
                        @endphp

                        <td style="text-align: center;">
                            <strong>{{ $valor }}</strong>
                        </td>
                        <td style="text-align: left; font-size: 9px;">
                            {{ $conclusion }}
                        </td>
                    @endforeach

                    @php
                        $nivelLogro = '-';
                        if ($contador > 0) {
                            $promedio = $suma / $contador;
                            if ($promedio >= 18) $nivelLogro = 'AD';
                            elseif ($promedio >= 14) $nivelLogro = 'A';
                            elseif ($promedio >= 11) $nivelLogro = 'B';
                            else $nivelLogro = 'C';
                        }
                    @endphp

                    <td style="text-align: center;">
                        {{ $nivelLogro }}
                    </td>
                </tr>
            @endforeach
        @endforeach
        
        @php
            // Promedio final de todas las áreas
            $sumaTotal = 0;
            $contadorTotal = 0;
            foreach ($cursos as $curso) {
                foreach ($curso->competencias as $competencia) {
                    foreach ($periodos as $periodo) {
                        $nota = $notasPorPeriodo[$periodo->id][$competencia->id] ?? null;
                        $valor = $nota ? $nota->nota : '-';
                        if (is_numeric($valor)) {
                            $sumaTotal += floatval($valor);
                            $contadorTotal++;
                        }
                    }
                }
            }
            $promedioTotal = $contadorTotal > 0 ? $sumaTotal / $contadorTotal : 0;
            $nivelLogroTotal = '-';
            if ($contadorTotal > 0) {
                if ($promedioTotal >= 18) $nivelLogroTotal = 'AD';
                elseif ($promedioTotal >= 14) $nivelLogroTotal = 'A';
                elseif ($promedioTotal >= 11) $nivelLogroTotal = 'B';
                else $nivelLogroTotal = 'C';
            }
        @endphp

    </tbody>
</table>
@endif


<!-- ==================== COMPETENCIAS TRANSVERSALES ==================== -->
@if(__cuadro_enabled('competencias_transversales', $cuadrosForNivel) && $competenciasTransversales && $competenciasTransversales->count() > 0)
<table class="tabla-notas" style="margin-top: 20px;">
    <thead>
        <tr>
            <th rowspan="2" style="width: 35%;">Competencias Transversales</th>
            @foreach($periodos as $periodo)
                <th colspan="2" style="text-align: center;">{{ $periodo->nombre }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($periodos as $periodo)
                <th style="width: 8%;">NL</th>
                <th style="width: 20%;">Conclusión Descriptiva</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($competenciasTransversales as $ct)
            <tr>
                <td style="text-align: left;">
                    <strong>{{ $ct->nombre }}</strong>
                    @if($ct->descripcion)
                        <br><small style="font-size: 8px;">{{ $ct->descripcion }}</small>
                    @endif
                </td>
                @foreach($periodos as $periodo)
                    @php
                        $registro = $registrosCT[$periodo->id][$ct->id] ?? null;
                        $nota = $registro ? $registro->nota : '-';
                        $conclusion = $registro ? $registro->conclusion : '';
                    @endphp
                    <td style="text-align: center;">
                        <strong>{{ $nota }}</strong>
                    </td>
                    <td style="text-align: left; font-size: 9px;">
                        {{ $conclusion }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
@endif


<!-- ==================== APRECIACIONES DEL TUTOR ==================== -->
@if(__cuadro_enabled('apreciaciones_tutor', $cuadrosForNivel))
<table class="tabla-apreciaciones">
    <thead>
        <tr>
            <th>PERIODO</th>
            <th>APRECIACIÓN DEL TUTOR(A) SOBRE LAS ACTITUDES DEL ALUMNO</th>
        </tr>
    </thead>
    <tbody>
        @foreach($periodos as $periodo)
            <tr>
                <td width="15%"><strong>{{ $periodo->nombre }}</strong></td>
                <td>{{ $apreciaciones[$periodo->id]->apreciacion ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif


<!-- ==================== EVALUACIÓN DEL PADRE DE FAMILIA ==================== -->
@if(__cuadro_enabled('evaluacion_padre', $cuadrosForNivel) && $evaluacionesPadre && $evaluacionesPadre->count() > 0)
<table class="tabla-evaluacion-padres">
    <thead>
        <tr><th colspan="{{ 1 + $periodos->count() }}">EVALUACIÓN AL PADRE DE FAMILIA</th></tr>
        <tr>
            <th>DESCRIPCIÓN</th>
            @foreach($periodos as $periodo)
                <th>{{ $periodo->nombre }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($evaluacionesPadre as $evaluacion)
            <tr>
                <td style="text-align: left;">{{ $evaluacion->nombre }}</td>
                @foreach($periodos as $periodo)
                    @php
                        $registro = $registrosEvaluacionesPadre[$periodo->id][$evaluacion->id] ?? null;
                        $valor = $registro ? $registro->valoracion : '';
                    @endphp
                    <td style="text-align: center;">{{ $valor }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
@endif


<!-- ==================== EVALUACIONES ACTITUDINALES e INASISTENCIAS ==================== -->
<div class="two-columns">
    @if(__cuadro_enabled('evaluaciones_actitudinales', $cuadrosForNivel) && $evaluacionesActitudinales && $evaluacionesActitudinales->count() > 0)
        <table class="tabla-evaluacion-padres">
            <thead>
                <tr><th colspan="5">EVALUACIÓN ACTITUDINAL</th></tr>
                <tr>
                    <th>DESCRIPCIÓN</th>
                    @foreach($periodos as $periodo)
                        <th>{{ $periodo->nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($evaluacionesActitudinales as $evaluacion)
                    <tr>
                        <td style="text-align: left;">{{ $evaluacion->nombre }}@if($evaluacion->descripcion)<br><small>{{ $evaluacion->descripcion }}</small>@endif</td>
                        @foreach($periodos as $periodo)
                            @php
                                $registro = $registrosEvaluacionesActitudinales[$periodo->id][$evaluacion->id] ?? null;
                                $valor = $registro ? $registro->valoracion : '';
                            @endphp
                            <td style="text-align: center;">{{ $valor }}@if($registro && $registro->comentario)<br><small class="text-muted">{{ $registro->comentario }}</small>@endif</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    <!-- INASISTENCIAS -->
    <div class="column">
        {{-- <h5>INASISTENCIAS</h5> --}}
        @if(__cuadro_enabled('inasistencias', $cuadrosForNivel) && $tiposInasistencia && $tiposInasistencia->count() > 0)
        <table class="tabla-evaluacion-padres">
            <thead>
                <tr><th colspan="5">ASISTENCIAS</th></tr>
                <tr>
                    <th>DESCRIPCIÓN</th>
                    @foreach($periodos as $periodo)
                        <th>{{ $periodo->nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($tiposInasistencia as $tipo)
                    <tr>
                        <td style="text-align: left;">{{ $tipo->nombre }}</td>
                        @foreach($periodos as $periodo)
                            @php
                                $inasistencia = $inasistencias[$periodo->id][$tipo->id] ?? null;
                                $valor = $inasistencia ? $inasistencia->cantidad : '0';
                            @endphp
                            <td style="text-align: center;">{{ $valor }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p class="text-muted">No hay tipos de inasistencia configurados para este nivel.</p>
        @endif
    </div>

</div>

<!-- ==================== OTRAS EVALUACIONES ==================== -->
<div class="two-columns">

    @if(__cuadro_enabled('otras_evaluaciones', $cuadrosForNivel) && $otrasEvaluaciones && $otrasEvaluaciones->count() > 0)
    <!-- OTRAS EVALUACIONES -->
        <div class="column">
            <h5>COMPORTAMIENTO Y OTROS</h5>
            <table class="tabla-evaluacion-padres">
                <thead>
                    <tr>
                        <th>DESCRIPCIÓN</th>
                        @foreach($periodos as $periodo)
                            <th>{{ $periodo->nombre }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($otrasEvaluaciones as $tipo)
                        <tr>
                            <td style="text-align: left;">{{ $tipo->nombre }}</td>
                            @foreach($periodos as $periodo)
                                @php
                                    $registro = $registrosOtras[$periodo->id][$tipo->id] ?? null;
                                    $valor = $registro ? $registro->valor : '';
                                @endphp
                                <td style="text-align: center;">{{ $valor }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    


    @php
        // Render dynamic cuadros after the 'otras evaluaciones' column
        $cuadrosDinamicos = \App\Models\CuadroDinamico::where('mostrar_en_libreta', true)
            ->where('activo', true)
            ->where(function($q) use ($nivelId) {
                $q->whereNull('nivel_id')->orWhere('nivel_id', $nivelId);
            })
            ->orderBy('orden')
            ->get();

        if ($cuadrosDinamicos->count()) {
            echo '<div class="row" style="margin-top:20px;">';
            foreach ($cuadrosDinamicos as $cuadro) {
                $col = $cuadro->ancho ?? 'col-12';
                echo "<div class=\"${col} mb-3\">";
                echo "<div class=\"card p-1\">";
                if ($cuadro->tipo !== 'tabla_generica') {
                    echo "<h6 class=\"mb-2\">" . e($cuadro->nombre) . "</h6>";
                }

                $descripciones = $cuadro->descripciones()->orderBy('orden')->get();

                // If tipo indicates a table per period, show a table with period columns
                if ($cuadro->tipo === 'tabla_periodos') {
                    echo '<table class="tabla-evaluacion-padres" style="width:100%;">';
                    echo '<thead><tr><th>DESCRIPCIÓN</th>';
                    foreach ($periodos as $periodo) {
                        echo "<th>" . e($periodo->nombre) . "</th>";
                    }
                    echo '</tr></thead>';
                    echo '<tbody>';
                    if ($descripciones->count()) {
                        foreach ($descripciones as $d) {
                            echo '<tr>';
                            echo '<td style="text-align:left;">' . e($d->texto) . '</td>';
                            foreach ($periodos as $periodo) {
                                echo '<td style="text-align:center;">' . '&nbsp;' . '</td>';
                            }
                            echo '</tr>';
                        }
                    } else {
                        // empty row placeholder
                        echo '<tr>';
                        echo '<td style="text-align:left;">' . '&nbsp;' . '</td>';
                        foreach ($periodos as $periodo) {
                            echo '<td style="text-align:center;">' . '&nbsp;' . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    // support a generic table type with N columns / N rows and optional headers
                    if ($cuadro->tipo === 'tabla_generica') {
                        $op = $cuadro->opciones ?? [];
                        $cols = isset($op['columnas']) ? intval($op['columnas']) : 3;
                        $rows = isset($op['filas']) ? intval($op['filas']) : 4;
                        $mostrarEnc = isset($op['mostrar_encabezados']) && $op['mostrar_encabezados'];
                        $encabezados = isset($op['encabezados']) ? (array)$op['encabezados'] : [];
                        $celdas = isset($op['celdas']) ? (array)$op['celdas'] : [];

                        echo '<table class="tabla-evaluacion-padres" style="width:100%; border-collapse: collapse;">';
                        // Title row that spans all columns (header occupying full width)
                        echo '<thead>';
                        echo '<tr style="background:#f0f0f0;"><th colspan="' . $cols . '" style="text-align:center;padding:6px;border:1px solid #444;font-weight:700;">' . e($cuadro->nombre) . '</th></tr>';
                        if ($mostrarEnc) {
                            echo '<tr>';
                            for ($c = 0; $c < $cols; $c++) {
                                $label = isset($encabezados[$c]) ? e($encabezados[$c]) : '';
                                echo '<th style="padding:6px;border:1px solid #444;background:#fafafa;text-align:center;">' . $label . '</th>';
                            }
                            echo '</tr>';
                        }
                        echo '</thead>';

                        echo '<tbody>';
                        for ($r = 0; $r < max(1, $rows); $r++) {
                            echo '<tr>';
                            for ($c = 0; $c < $cols; $c++) {
                                $celValue = '';
                                if (isset($celdas[$r]) && is_array($celdas[$r]) && isset($celdas[$r][$c])) {
                                    $celValue = e($celdas[$r][$c]);
                                }
                                echo '<td style="padding:8px;border:1px solid #444;height:28px;vertical-align:middle;">' . $celValue . '</td>';
                            }
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                    // default: render descriptions as simple list or legend box
                    if ($descripciones->count()) {
                        echo '<ul class="mb-0" style="list-style:none;padding-left:0;">';
                        foreach ($descripciones as $d) {
                            // echo '<li style="padding:6px 0;border-bottom:1px solid #eee;">' . e($d->texto) . '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<div style="min-height:40px;">&nbsp;</div>';
                    }
                    }
                }

                echo '</div>'; // card
                echo '</div>'; // col
            }
            echo '</div>';
        }
    @endphp

    
</div>



<!-- ==================== FIRMAS ==================== -->
<div class="firmas">
    <div class="firma">
        @if($configLibreta->firma_director && Storage::disk('public')->exists($configLibreta->firma_director))
            <img src="{{ Storage::url($configLibreta->firma_director) }}" alt="Firma Director" style="max-height: 90px;">
        @endif
    </div>
    
    @if($esPrimaria)
        <div class="firma">
            @if($configLibreta->firma_subdirector && Storage::disk('public')->exists($configLibreta->firma_subdirector))
                <img src="{{ Storage::url($configLibreta->firma_subdirector) }}" alt="Firma Subdirector" style="max-height: 90px;">
            @endif
        </div>
    @else
        <div class="firma">
            @if($configLibreta->firma_tutor && Storage::disk('public')->exists($configLibreta->firma_tutor))
                <img src="{{ Storage::url($configLibreta->firma_tutor) }}" alt="Firma Tutor" style="max-height: 90px;">
            @endif
        </div>
    @endif
</div>

<div class="footer">
    <p>{{ $configLibreta->texto_pie ?? '' }}</p>
</div>