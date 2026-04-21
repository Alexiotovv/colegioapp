{{-- resources/views/libretas/partials/libreta-alumno.blade.php --}}
@php
    $alumno = $matricula->alumno;
    $aula = $matricula->aula;
    $grado = $aula->grado;
    $nivel = $grado ? $grado->nivel : null;
    $esPrimaria = $nivel && $nivel->nombre == 'Primaria';
    
    // Obtener competencias y notas por periodo
    $competencias = \App\Models\Competencia::where('activo', true)->orderBy('orden')->get();
    $notasPorPeriodo = [];
    foreach ($periodos as $periodo) {
        foreach ($competencias as $competencia) {
            $nota = \App\Models\Nota::where('matricula_id', $matricula->id)
                ->where('competencia_id', $competencia->id)
                ->where('periodo_id', $periodo->id)
                ->first();
            $notasPorPeriodo[$periodo->id][$competencia->id] = $nota;
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
    
    // Obtener evaluaciones del padre
    $tiposEvaluacion = \App\Models\TipoOtraEvaluacion::where('activo', true)->orderBy('orden')->get();
    $evaluacionesPadre = [];
    foreach ($periodos as $periodo) {
        foreach ($tiposEvaluacion as $tipo) {
            $evaluacion = \App\Models\RegistroOtraEvaluacion::where('matricula_id', $matricula->id)
                ->where('tipo_otra_evaluacion_id', $tipo->id)
                ->where('periodo_id', $periodo->id)
                ->first();
            $evaluacionesPadre[$periodo->id][$tipo->id] = $evaluacion;
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

<!-- Datos del estudiante -->
<table class="info-estudiante">
    <tr>
        <td width="30%">Apellidos y nombres del estudiante:</td>
        <td colspan="3"><strong>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}, {{ $alumno->nombres }}</strong></td>
    </tr>
    <tr>
        <td>Nivel:</td>
        <td width="35%">{{ $nivel ? $nivel->nombre : '' }}</td>
        <td width="15%">Grado:</td>
        <td width="20%">{{ $grado ? $grado->nombre : '' }}</td>
    </tr>
    <tr>
        <td>Sección:</td>
        <td>{{ $aula->seccion ? $aula->seccion->nombre : '' }}</td>
        <td>Turno:</td>
        <td>{{ $aula->turno_nombre ?? '' }}</td>
    </tr>
    <tr>
        <td>Código del estudiante:</td>
        <td>{{ $alumno->codigo_estudiante }}</td>
        <td>DNI:</td>
        <td>{{ $alumno->dni }}</td>
    </tr>
    <tr>
        <td>Docente/Tutor:</td>
        <td colspan="3">{{ $aula->docente ? $aula->docente->name : '' }}</td>
    </tr>
</table>

<!-- Tabla de Notas por Competencia -->
<table class="tabla-notas">
    <thead>
        <tr>
            <th rowspan="2">Área curricular / Competencias</th>
            @foreach($periodos as $periodo)
                <th colspan="1">{{ $periodo->nombre }}</th>
            @endforeach
            <th rowspan="2">Nivel de Logro</th>
        </tr>
    </thead>
    <tbody>
        @foreach($competencias as $competencia)
            @php
                $curso = $competencia->curso;
                $promedio = 0;
                $suma = 0;
                $contador = 0;
                $notasTextos = [];
            @endphp
            <tr>
                <td style="text-align: left;">
                    <strong>{{ $curso ? $curso->nombre : '' }}</strong><br>
                    <small>{{ $competencia->nombre }}</small>
                </td>
                @foreach($periodos as $periodo)
                    @php
                        $nota = $notasPorPeriodo[$periodo->id][$competencia->id] ?? null;
                        $valor = $nota ? $nota->nota : '-';
                        if (is_numeric($valor)) {
                            $suma += floatval($valor);
                            $contador++;
                        }
                        $notasTextos[] = $valor;
                    @endphp
                    <td>{{ $valor }}</td>
                @endforeach
                @php
                    $nivelLogro = '';
                    if ($contador > 0) {
                        $promedio = $suma / $contador;
                        if ($promedio >= 18) $nivelLogro = 'AD';
                        elseif ($promedio >= 14) $nivelLogro = 'A';
                        elseif ($promedio >= 11) $nivelLogro = 'B';
                        else $nivelLogro = 'C';
                    } else {
                        $nivelLogro = '-';
                    }
                @endphp
                <td><strong>{{ $nivelLogro }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Conclusión Descriptiva -->
@php
    $conclusiones = [];
    foreach ($periodos as $periodo) {
        foreach ($competencias as $competencia) {
            $nota = $notasPorPeriodo[$periodo->id][$competencia->id] ?? null;
            if ($nota && $nota->conclusionDescriptiva) {
                $conclusiones[$periodo->id] = $nota->conclusionDescriptiva->conclusion;
                break;
            }
        }
    }
@endphp

@if(count($conclusiones) > 0)
<table class="tabla-apreciaciones">
    <thead>
        <tr>
            <th>PERIODO</th>
            <th>CONCLUSIÓN DESCRIPTIVA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($periodos as $periodo)
            <tr>
                <td width="15%"><strong>{{ $periodo->nombre }}</strong></td>
                <td>{{ $conclusiones[$periodo->id] ?? '' }}</td>
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
        @foreach($tiposEvaluacion as $tipo)
            <tr>
                <td style="text-align: left;">{{ $tipo->nombre }}</td>
                @foreach($periodos as $periodo)
                    @php
                        $evaluacion = $evaluacionesPadre[$periodo->id][$tipo->id] ?? null;
                        $valor = $evaluacion ? $evaluacion->valor : '';
                    @endphp
                    <td>{{ $valor }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Inasistencias y Otras Evaluaciones (dos columnas) -->
<div class="two-columns">
    <div class="column">
        <h5>INASISTENCIAS</h5>
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
                            <td>{{ $valor }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="column">
        <h5>OTRAS EVALUACIONES</h5>
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
                            <td>{{ $valor }}</td>
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
            <img src="{{ public_path('storage/' . $configLibreta->firma_director) }}" alt="Firma Director" style="max-height: 40px;">
        @endif
        <div class="linea"></div>
        <p><strong>{{ $configLibreta->nombre_director ?? 'Director(a)' }}</strong></p>
        <p>{{ $configLibreta->cargo_director ?? 'DIRECTOR(A)' }}</p>
    </div>
    
    @if($esPrimaria)
        <div class="firma">
            @if($configLibreta->firma_subdirector && Storage::disk('public')->exists($configLibreta->firma_subdirector))
                <img src="{{ public_path('storage/' . $configLibreta->firma_subdirector) }}" alt="Firma Subdirector" style="max-height: 40px;">
            @endif
            <div class="linea"></div>
            <p><strong>{{ $configLibreta->nombre_subdirector ?? 'Subdirector(a)' }}</strong></p>
            <p>{{ $configLibreta->cargo_subdirector ?? 'SUBDIRECTOR(A)' }}</p>
        </div>
    @else
        <div class="firma">
            @if($configLibreta->firma_tutor && Storage::disk('public')->exists($configLibreta->firma_tutor))
                <img src="{{ public_path('storage/' . $configLibreta->firma_tutor) }}" alt="Firma Tutor" style="max-height: 40px;">
            @endif
            <div class="linea"></div>
            <p><strong>{{ $configLibreta->nombre_tutor ?? 'Tutor(a)' }}</strong></p>
            <p>{{ $configLibreta->cargo_tutor ?? 'TUTOR(A)' }}</p>
        </div>
    @endif
</div>

<div class="footer">
    <p>{{ $configLibreta->texto_pie ?? '' }}</p>
    <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
</div>