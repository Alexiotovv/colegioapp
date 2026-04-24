{{-- resources/views/libretas/partials/libreta-alumno.blade.php --}}
@php
    $alumno = $matricula->alumno;
    $aula = $matricula->aula;
    $grado = $aula->grado;
    $nivel = $grado ? $grado->nivel : null;
    $esPrimaria = $nivel && $nivel->nombre == 'Primaria';
    
    // Obtener competencias y notas por periodo
    // $competencias = \App\Models\Competencia::where('activo', true)->orderBy('orden')->get();
    $cursos = \App\Models\Curso::with(['competencias' => function($q){
        $q->where('activo', true)->orderBy('orden');
    }])->get();
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
    
    // Obtener apreciaciones del tutor
    $apreciaciones = [];
    foreach ($periodos as $periodo) {
        $apreciacion = \App\Models\Apreciacion::where('matricula_id', $matricula->id)
            ->where('periodo_id', $periodo->id)
            ->first();
        $apreciaciones[$periodo->id] = $apreciacion;
    }
    
    // Obtener evaluaciones del padre de familia (de la tabla evaluaciones)
    $evaluacionesPadre = \App\Models\Evaluacion::where('activo', true)->orderBy('orden')->get();
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
    
    // Obtener inasistencias
    $tiposInasistencia = \App\Models\TipoInasistencia::where('activo', true)->orderBy('orden')->get();
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
@endphp


<!-- Tabla de Notas por Competencia -->
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
            $nivelLogroTotal = '';
            if ($contadorTotal > 0) {
                if ($promedioTotal >= 18) $nivelLogroTotal = 'AD';
                elseif ($promedioTotal >= 14) $nivelLogroTotal = 'A';
                elseif ($promedioTotal >= 11) $nivelLogroTotal = 'B';
                else $nivelLogroTotal = 'C';
            } else {
                $nivelLogroTotal = '-';
            }
        @endphp

    </tbody>
</table>

<!-- Conclusión Descriptiva -->
@php
    $conclusiones = [];
    foreach ($periodos as $periodo) {
        foreach ($cursos as $curso) {
            foreach ($curso->competencias as $competencia) {

                $nota = $notasPorPeriodo[$periodo->id][$competencia->id] ?? null;

                if ($nota && $nota->conclusionDescriptiva) {
                    $conclusiones[$periodo->id] = $nota->conclusionDescriptiva->conclusion;
                    break 2; // 🔥 IMPORTANTE: rompe ambos foreach
                }

            }
        }
    }
@endphp

<!-- ==================== COMPETENCIAS TRANSVERSALES ==================== -->
@php
    // Obtener competencias transversales y sus registros
    $competenciasTransversales = \App\Models\CompetenciaTransversal::where('activo', true)->orderBy('orden')->get();
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
@endphp

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

<!-- Apreciaciones del Tutor -->
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

<!-- Evaluación del Padre de Familia -->
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

<!-- Inasistencias y Otras Evaluaciones (dos columnas) -->
<div class="two-columns">
    <div class="column">
        {{-- <h5>INASISTENCIAS</h5> --}}
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
    </div>
    
    <div class="column">
        {{-- <h5>OTRAS EVALUACIONES</h5> --}}
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
                @php
                    $otrasEvaluaciones = \App\Models\TipoOtraEvaluacion::where('activo', true)->orderBy('orden')->get();
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
                @endphp
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
</div>

<!-- Firmas -->
<div class="firmas">
    <div class="firma">
        @if($configLibreta->firma_director && Storage::disk('public')->exists($configLibreta->firma_director))
            <img src="{{ Storage::url($configLibreta->firma_director) }}" alt="Firma Director" style="max-height: 90px;">
        @endif
        {{-- <div class="linea"></div>
        <p><strong>{{ $configLibreta->nombre_director ?? 'Director(a)' }}</strong></p>
        <p>{{ $configLibreta->cargo_director ?? 'DIRECTOR(A)' }}</p> --}}
    </div>
    
    @if($esPrimaria)
        <div class="firma">
            @if($configLibreta->firma_subdirector && Storage::disk('public')->exists($configLibreta->firma_subdirector))
                <img src="{{ Storage::url($configLibreta->firma_subdirector) }}" alt="Firma Subdirector" style="max-height: 90px;">
            @endif
            {{-- <div class="linea"></div>
            <p><strong>{{ $configLibreta->nombre_subdirector ?? 'Subdirector(a)' }}</strong></p>
            <p>{{ $configLibreta->cargo_subdirector ?? 'SUBDIRECTOR(A)' }}</p> --}}
        </div>
    @else
        <div class="firma">
            @if($configLibreta->firma_tutor && Storage::disk('public')->exists($configLibreta->firma_tutor))
                <img src="{{ Storage::url($configLibreta->firma_tutor) }}" alt="Firma Tutor" style="max-height: 90px;">
            @endif
            {{-- <div class="linea"></div>
            <p><strong>{{ $configLibreta->nombre_tutor ?? 'Tutor(a)' }}</strong></p>
            <p>{{ $configLibreta->cargo_tutor ?? 'TUTOR(A)' }}</p> --}}
        </div>
    @endif
</div>

<div class="footer">
    <p>{{ $configLibreta->texto_pie ?? '' }}</p>
    {{-- <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p> --}}
</div>