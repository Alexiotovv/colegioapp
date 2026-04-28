@php
    $alumno = $matricula->alumno;
    $aula = $matricula->aula;
    $grado = $aula->grado;
    $nivel = $grado ? $grado->nivel : null;
    $nivelId = $nivel->id ?? 0;
    $esPrimaria = $nivel && $nivel->nombre == 'Primaria';
    
    // ==================== 1. CURSOS Y COMPETENCIAS (filtrado por nivel) ====================
    $cursos = \App\Models\Curso::with(['competencias' => function($q){
        $q->where('activo', true)->orderBy('orden');
    }])
    ->where('nivel_id', $nivelId)
    ->where('activo', true)
    ->ordered()
    ->get();
    
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

    


<!-- ==================== TABLA DE NOTAS POR COMPETENCIA ==================== -->
<table class="tabla-notas">
    <thead>
        <tr>
            <th rowspan="2" style="width: 10%;">Área curricular</th>
            <th rowspan="2" style="width: 25%;">Competencias</th>
            @foreach($periodos as $periodo)
                <th colspan="2" style="text-align: center;">{{ $periodo->nombre }}</th>
            @endforeach
            <th rowspan="2" style="width: 12%;">NL alcanzado al finalizar el período lectivo</th>
        </tr>
        <tr>
            @foreach($periodos as $periodo)
                <th style="width: 8%;">NL</th>
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


<!-- ==================== COMPETENCIAS TRANSVERSALES ==================== -->
@if($competenciasTransversales && $competenciasTransversales->count() > 0)
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


<!-- ==================== EVALUACIÓN DEL PADRE DE FAMILIA ==================== -->
@if($evaluacionesPadre && $evaluacionesPadre->count() > 0)
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

<!-- ==================== EVALUACIONES ACTITUDINALES ==================== -->


@if($evaluacionesActitudinales && $evaluacionesActitudinales->count() > 0)
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

<!-- ==================== INASISTENCIAS Y OTRAS EVALUACIONES ==================== -->
<div class="two-columns">
    <!-- INASISTENCIAS -->
    <div class="column">
        <h5>INASISTENCIAS</h5>
        @if($tiposInasistencia && $tiposInasistencia->count() > 0)
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
    
    <!-- OTRAS EVALUACIONES -->
    <div class="column">
        <h5>COMPORTAMIENTO Y OTROS</h5>
        @if($otrasEvaluaciones && $otrasEvaluaciones->count() > 0)
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
        @else
            <p class="text-muted">No hay otras evaluaciones configuradas para este nivel.</p>
        @endif
    </div>
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