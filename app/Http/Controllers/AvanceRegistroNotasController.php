<?php
// app/Http/Controllers/AvanceRegistroNotasController.php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\AvanceNota;
use App\Models\Periodo;
use App\Models\Nivel;
use App\Models\AnioAcademico;
use App\Models\Matricula;
use App\Models\Curso;
use App\Models\CargaHoraria;
use App\Models\ConfiguracionAvanceCuadro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvanceRegistroNotasController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $esAdmin = $user && $user->isAdmin();
        $docenteId = auth()->id();

        $anioActivo = AnioAcademico::where('activo', true)->first();
        if (!$anioActivo) {
            return redirect()->back()->with('error', 'No hay un año académico activo configurado.');
        }

        $periodos = Periodo::where('anio_academico_id', $anioActivo->id)
            // ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $nivelesQuery = Nivel::where('activo', true)
            ->with(['grados' => fn ($query) => $query->where('activo', true)])
            ->orderBy('orden');

        if (!$esAdmin) {
            $nivelIdsAsignados = Aula::where('activo', true)
                ->where('anio_academico_id', $anioActivo->id)
                ->where(function ($q) use ($docenteId) {
                    $q->where('docente_id', $docenteId)
                        ->orWhereHas('cargaHoraria', function ($q2) use ($docenteId) {
                            $q2->where('docente_id', $docenteId)
                                ->where('estado', CargaHoraria::ESTADO_ACTIVO)
                                ->whereNull('deleted_at');
                        });
                })
                ->whereNotNull('nivel_id')
                ->distinct()
                ->pluck('nivel_id');

            $nivelesQuery->whereIn('id', $nivelIdsAsignados);
        }

        $niveles = $nivelesQuery->get();

        return view('progreso-avance-registro-notas.index', compact('niveles', 'periodos', 'anioActivo'));
    }

    public function getResumenAulas(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
            'nivel_id' => 'nullable|exists:niveles,id',
            'grado_id' => 'nullable|exists:grados,id',
        ]);

        $periodoId = $request->periodo_id;
        $anioActivo = AnioAcademico::where('activo', true)->first();
        if (!$anioActivo) {
            return response()->json(['success' => false, 'message' => 'No hay un año académico activo configurado.'], 422);
        }

        $user = auth()->user();
        $esAdmin = $user && $user->isAdmin();
        $docenteId = auth()->id();

        $query = Aula::where('anio_academico_id', $anioActivo->id)
            ->where('activo', true)
            ->with(['grado.nivel', 'seccion', 'docente']);

        if (!$esAdmin) {
            $query->where(function ($q) use ($docenteId) {
                $q->where('docente_id', $docenteId)
                    ->orWhereHas('cargaHoraria', function ($q2) use ($docenteId) {
                        $q2->where('docente_id', $docenteId)
                            ->where('estado', CargaHoraria::ESTADO_ACTIVO)
                            ->whereNull('deleted_at');
                    });
            });
        }

        if ($request->nivel_id) {
            $query->where('nivel_id', $request->nivel_id);
        }

        if ($request->grado_id) {
            $query->where('grado_id', $request->grado_id);
        }

        $aulas = $query->orderBy('grado_id')->orderBy('seccion_id')->get();
        if ($aulas->isEmpty()) {
            return response()->json(['success' => true, 'data' => [], 'data_completitud' => []]);
        }

        $aulaIds = $aulas->pluck('id');

        $estudiantesPorAula = DB::table('matriculas as m')
            ->whereIn('m.aula_id', $aulaIds)
            ->whereIn('m.estado', [Matricula::ESTADO_ACTIVA, Matricula::ESTADO_RETIRADA])
            ->select('m.aula_id', DB::raw('COUNT(m.id) as total_estudiantes'))
            ->groupBy('m.aula_id')
            ->pluck('total_estudiantes', 'm.aula_id');

        $totalCursosPorAula = DB::table('carga_horaria as ch')
            ->whereIn('ch.aula_id', $aulaIds)
            ->whereNull('ch.deleted_at')
            ->select('ch.aula_id', DB::raw('COUNT(DISTINCT ch.curso_id) as total_cursos'))
            ->groupBy('ch.aula_id')
            ->pluck('total_cursos', 'ch.aula_id');

        $competenciasPorCursoAula = DB::table('carga_horaria as ch')
            ->join('competencias as c', function ($join) {
                $join->on('c.curso_id', '=', 'ch.curso_id')
                    ->where('c.activo', true);
            })
            ->whereIn('ch.aula_id', $aulaIds)
            ->whereNull('ch.deleted_at')
            ->select('ch.aula_id', 'ch.curso_id', DB::raw('COUNT(DISTINCT c.id) as total_competencias'))
            ->groupBy('ch.aula_id', 'ch.curso_id')
            ->get();

        $registradasPorCursoAula = DB::table('notas_avance as n')
            ->join('competencias as c', function ($join) {
                $join->on('c.id', '=', 'n.competencia_id')
                    ->where('c.activo', true);
            })
            ->join('matriculas as m', 'm.id', '=', 'n.matricula_id')
            ->whereIn('m.aula_id', $aulaIds)
            ->whereIn('m.estado', [Matricula::ESTADO_ACTIVA, Matricula::ESTADO_RETIRADA])
            ->where('n.periodo_id', $periodoId)
            ->where('n.tipo_evaluacion', 'BIMESTRAL')
            ->whereNotNull('n.nota')
            ->where('n.nota', '!=', '')
            ->select('m.aula_id', 'c.curso_id', DB::raw('COUNT(DISTINCT n.id) as total_registrado'))
            ->groupBy('m.aula_id', 'c.curso_id')
            ->get();

        $competenciasAgrupadas = $competenciasPorCursoAula->groupBy('aula_id');
        $registradasMap = $registradasPorCursoAula->keyBy(fn ($row) => $row->aula_id . '-' . $row->curso_id);

        $todosNivelIds = $aulas->pluck('nivel_id')->filter()->unique()->values();
        $cuadrosConfigMap = ConfiguracionAvanceCuadro::whereIn('nivel_id', $todosNivelIds)
            ->get()
            ->keyBy('nivel_id')
            ->map(fn ($c) => $c->cuadros ?? []);

        $todosMatriculaIds = DB::table('matriculas as m')
            ->whereIn('m.aula_id', $aulaIds)
            ->whereIn('m.estado', [Matricula::ESTADO_ACTIVA, Matricula::ESTADO_RETIRADA])
            ->pluck('m.id');

        $registradosCT = $todosMatriculaIds->isNotEmpty()
            ? DB::table('registro_competencias_transversales as r')
                ->join('matriculas as m', 'm.id', '=', 'r.matricula_id')
                ->whereIn('m.aula_id', $aulaIds)
                ->where('r.periodo_id', $periodoId)
                ->whereNotNull('r.nota')
                ->where('r.nota', '!=', '')
                ->select('m.aula_id', DB::raw('COUNT(r.id) as total'))
                ->groupBy('m.aula_id')
                ->pluck('total', 'm.aula_id')
            : collect();

        $registradosApreciaciones = $todosMatriculaIds->isNotEmpty()
            ? DB::table('apreciaciones as a')
                ->join('matriculas as m', 'm.id', '=', 'a.matricula_id')
                ->whereIn('m.aula_id', $aulaIds)
                ->where('a.periodo_id', $periodoId)
                ->whereNotNull('a.apreciacion')
                ->where('a.apreciacion', '!=', '')
                ->select('m.aula_id', DB::raw('COUNT(a.id) as total'))
                ->groupBy('m.aula_id')
                ->pluck('total', 'm.aula_id')
            : collect();

        $registradosEvalPadre = $todosMatriculaIds->isNotEmpty()
            ? DB::table('registro_evaluaciones as r')
                ->join('matriculas as m', 'm.id', '=', 'r.matricula_id')
                ->whereIn('m.aula_id', $aulaIds)
                ->where('r.periodo_id', $periodoId)
                ->whereNotNull('r.valoracion')
                ->where('r.valoracion', '!=', '')
                ->select('m.aula_id', DB::raw('COUNT(r.id) as total'))
                ->groupBy('m.aula_id')
                ->pluck('total', 'm.aula_id')
            : collect();

        $registradosEvalActitudinal = $todosMatriculaIds->isNotEmpty()
            ? DB::table('reg_eval_actitudinales as r')
                ->join('matriculas as m', 'm.id', '=', 'r.matricula_id')
                ->whereIn('m.aula_id', $aulaIds)
                ->where('r.periodo_id', $periodoId)
                ->whereNotNull('r.valoracion')
                ->where('r.valoracion', '!=', '')
                ->select('m.aula_id', DB::raw('COUNT(r.id) as total'))
                ->groupBy('m.aula_id')
                ->pluck('total', 'm.aula_id')
            : collect();

        $registradosInasistencias = $todosMatriculaIds->isNotEmpty()
            ? DB::table('registro_asistencias as r')
                ->join('matriculas as m', 'm.id', '=', 'r.matricula_id')
                ->whereIn('m.aula_id', $aulaIds)
                ->where('r.periodo_id', $periodoId)
                ->whereNotNull('r.cantidad')
                ->select('m.aula_id', DB::raw('COUNT(r.id) as total'))
                ->groupBy('m.aula_id')
                ->pluck('total', 'm.aula_id')
            : collect();

        $registradosOtrasEval = $todosMatriculaIds->isNotEmpty()
            ? DB::table('registro_otras_evaluaciones as r')
                ->join('matriculas as m', 'm.id', '=', 'r.matricula_id')
                ->whereIn('m.aula_id', $aulaIds)
                ->where('r.periodo_id', $periodoId)
                ->whereNotNull('r.valor')
                ->where('r.valor', '!=', '')
                ->select('m.aula_id', DB::raw('COUNT(DISTINCT CONCAT(r.matricula_id, \'-\', r.tipo_otra_evaluacion_id)) as total'))
                ->groupBy('m.aula_id')
                ->pluck('total', 'm.aula_id')
            : collect();

        $aulasConAvance = [];
        $aulasCompletitud = [];

        foreach ($aulas as $aula) {
            $aulaId = $aula->id;
            $totalCursos = (int) ($totalCursosPorAula[$aulaId] ?? 0);
            $totalEstudiantes = (int) ($estudiantesPorAula[$aulaId] ?? 0);
            $cursosConCompetencias = $competenciasAgrupadas->get($aulaId, collect());

            $baseAula = [
                'id' => $aula->id,
                'nombre' => $aula->nombre,
                'codigo' => $aula->codigo,
                'grado' => $aula->grado->nombre ?? '',
                'seccion' => $aula->seccion->nombre ?? '',
                'turno' => $aula->turno,
                'docente' => $aula->docente ? $aula->docente->name : 'No asignado',
            ];

            if ($totalCursos === 0) {
                $aulasConAvance[] = [
                    'aula' => $baseAula,
                    'porcentaje' => 0,
                    'color' => '#dc3545',
                    'sin_cursos' => true,
                    'cuadros_avance' => [],
                    'libreta_porcentaje' => 0,
                ];

                $aulasCompletitud[] = [
                    'aula' => $baseAula,
                    'porcentaje' => 0,
                    'color' => '#dc3545',
                    'total_cursos' => 0,
                    'total_cursos_completos' => 0,
                    'total_cursos_evaluables' => 0,
                    'total_esperado' => 0,
                    'total_registrado' => 0,
                    'sin_cursos' => true,
                    'cuadros_avance' => [],
                    'libreta_porcentaje' => 0,
                ];
                continue;
            }

            if ($totalEstudiantes === 0) {
                $aulasConAvance[] = [
                    'aula' => $baseAula,
                    'porcentaje' => 0,
                    'color' => '#ffc107',
                    'sin_estudiantes' => true,
                    'cuadros_avance' => [],
                    'libreta_porcentaje' => 0,
                ];

                $aulasCompletitud[] = [
                    'aula' => $baseAula,
                    'porcentaje' => 0,
                    'color' => '#ffc107',
                    'total_cursos' => $totalCursos,
                    'total_cursos_completos' => 0,
                    'total_cursos_evaluables' => (int) $cursosConCompetencias->count(),
                    'total_esperado' => 0,
                    'total_registrado' => 0,
                    'sin_estudiantes' => true,
                    'cuadros_avance' => [],
                    'libreta_porcentaje' => 0,
                ];
                continue;
            }

            $totalEsperado = 0;
            $totalRegistrado = 0;
            $totalCursosEvaluables = 0;
            $totalCursosCompletos = 0;

            foreach ($cursosConCompetencias as $cursoAula) {
                $totalCompetencias = (int) $cursoAula->total_competencias;
                if ($totalCompetencias <= 0) {
                    continue;
                }

                $totalCursosEvaluables++;
                $esperadoCurso = $totalEstudiantes * $totalCompetencias;
                $registradoCurso = (int) ($registradasMap[$aulaId . '-' . $cursoAula->curso_id]->total_registrado ?? 0);

                $totalEsperado += $esperadoCurso;
                $totalRegistrado += $registradoCurso;

                if ($esperadoCurso > 0 && $registradoCurso >= $esperadoCurso) {
                    $totalCursosCompletos++;
                }
            }

            $porcentaje = $totalEsperado > 0 ? round(($totalRegistrado / $totalEsperado) * 100, 2) : 0;
            $porcentajeCompletitud = $totalCursosEvaluables > 0 ? round(($totalCursosCompletos / $totalCursosEvaluables) * 100, 2) : 0;

            $nivelId = $aula->nivel_id;
            $cuadrosHab = $cuadrosConfigMap->has($nivelId) ? ($cuadrosConfigMap->get($nivelId) ?? []) : null;
            $isEn = fn ($key) => $cuadrosHab === null || in_array($key, $cuadrosHab, true);

            $libreraReg = $totalRegistrado;
            $libreraEsp = $totalEsperado;
            $cuadrosAvance = [];

            if ($isEn('competencias_transversales')) {
                $c = DB::table('competencias_transversales')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($c > 0) {
                    $e = $totalEstudiantes * $c;
                    $r = min((int) ($registradosCT[$aulaId] ?? 0), $e);
                    $cuadrosAvance['competencias_transversales'] = [
                        'label' => 'Comp. Transversales',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
            if ($isEn('apreciaciones_tutor')) {
                $e = $totalEstudiantes;
                $r = min((int) ($registradosApreciaciones[$aulaId] ?? 0), $e);
                $cuadrosAvance['apreciaciones_tutor'] = [
                    'label' => 'Apreciaciones',
                    'registrado' => $r,
                    'esperado' => $e,
                    'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                ];
                $libreraReg += $r;
                $libreraEsp += $e;
            }
            if ($isEn('evaluacion_padre')) {
                $c = DB::table('evaluaciones')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($c > 0) {
                    $e = $totalEstudiantes * $c;
                    $r = min((int) ($registradosEvalPadre[$aulaId] ?? 0), $e);
                    $cuadrosAvance['evaluacion_padre'] = [
                        'label' => 'Eval. Padre',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
            if ($isEn('evaluaciones_actitudinales')) {
                $c = DB::table('eval_actitudinales')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($c > 0) {
                    $e = $totalEstudiantes * $c;
                    $r = min((int) ($registradosEvalActitudinal[$aulaId] ?? 0), $e);
                    $cuadrosAvance['evaluaciones_actitudinales'] = [
                        'label' => 'Eval. Actitudinal',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
            if ($isEn('inasistencias')) {
                $c = DB::table('tipos_inasistencia')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($c > 0) {
                    $e = $totalEstudiantes * $c;
                    $r = min((int) ($registradosInasistencias[$aulaId] ?? 0), $e);
                    $cuadrosAvance['inasistencias'] = [
                        'label' => 'Inasistencias',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
            if ($isEn('otras_evaluaciones')) {
                $c = DB::table('tipos_otras_evaluaciones')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($c > 0) {
                    $e = $totalEstudiantes * $c;
                    $r = min((int) ($registradosOtrasEval[$aulaId] ?? 0), $e);
                    $cuadrosAvance['otras_evaluaciones'] = [
                        'label' => 'Comportamiento',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }

            $libretaPorcentaje = $libreraEsp > 0 ? round($libreraReg / $libreraEsp * 100, 1) : 0;

            $aulasConAvance[] = [
                'aula' => $baseAula,
                'porcentaje' => $porcentaje,
                'color' => $this->getColorByPercentage($porcentaje),
                'total_esperado' => $totalEsperado,
                'total_registrado' => $totalRegistrado,
                'cuadros_avance' => $cuadrosAvance,
                'libreta_porcentaje' => $libretaPorcentaje,
            ];

            $aulasCompletitud[] = [
                'aula' => $baseAula,
                'porcentaje' => $porcentajeCompletitud,
                'color' => $this->getColorByPercentage($porcentajeCompletitud),
                'total_cursos' => $totalCursos,
                'total_cursos_completos' => $totalCursosCompletos,
                'total_cursos_evaluables' => $totalCursosEvaluables,
                'total_esperado' => $totalEsperado,
                'total_registrado' => $totalRegistrado,
                'cuadros_avance' => $cuadrosAvance,
                'libreta_porcentaje' => $libretaPorcentaje,
            ];
        }

        usort($aulasCompletitud, fn ($a, $b) => $b['porcentaje'] <=> $a['porcentaje']);

        return response()->json(['success' => true, 'data' => $aulasConAvance, 'data_completitud' => $aulasCompletitud]);
    }

    public function getAvanceByAula(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
            'aula_id' => 'required|exists:aulas,id',
        ]);

        $periodoId = $request->periodo_id;
        $aulaId = $request->aula_id;

        $user = auth()->user();
        $esAdmin = $user && $user->isAdmin();
        $docenteId = auth()->id();

        if (!$esAdmin) {
            $tieneAcceso = Aula::where('id', $aulaId)
                ->where(function ($q) use ($docenteId) {
                    $q->where('docente_id', $docenteId)
                        ->orWhereHas('cargaHoraria', function ($q2) use ($docenteId) {
                            $q2->where('docente_id', $docenteId)
                                ->where('estado', CargaHoraria::ESTADO_ACTIVO)
                                ->whereNull('deleted_at');
                        });
                })
                ->exists();

            if (!$tieneAcceso) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para consultar esta aula.'], 403);
            }
        }

        $aula = Aula::with(['grado.nivel', 'seccion', 'docente'])->findOrFail($aulaId);

        $matriculas = Matricula::with('alumno')
            ->where('aula_id', $aulaId)
            ->whereIn('estado', [Matricula::ESTADO_ACTIVA, Matricula::ESTADO_RETIRADA])
            ->orderBy('id')
            ->get();

        $cursoIds = CargaHoraria::where('aula_id', $aulaId)
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('curso_id')
            ->filter();

        $cursosAsignados = Curso::whereIn('id', $cursoIds)
            ->with(['competencias' => fn ($query) => $query->where('activo', true)])
            ->get();

        $matriculaIds = $matriculas->pluck('id');

        $notasPorCurso = collect();
        if ($matriculaIds->isNotEmpty() && $cursoIds->isNotEmpty()) {
            $notasPorCurso = DB::table('notas_avance as n')
                ->join('competencias as c', 'c.id', '=', 'n.competencia_id')
                ->whereIn('n.matricula_id', $matriculaIds)
                ->whereIn('c.curso_id', $cursoIds)
                ->where('n.periodo_id', $periodoId)
                ->where('n.tipo_evaluacion', 'BIMESTRAL')
                ->whereNotNull('n.nota')
                ->where('n.nota', '!=', '')
                ->where('c.activo', true)
                ->select('c.curso_id', DB::raw('COUNT(DISTINCT n.id) as total_registrado'))
                ->groupBy('c.curso_id')
                ->pluck('total_registrado', 'c.curso_id');
        }

        $detalleAvance = [];
        $totalEsperadoGlobal = 0;
        $totalRegistradoGlobal = 0;

        foreach ($cursosAsignados as $curso) {
            $competencias = $curso->competencias ?? collect();
            if ($competencias->isEmpty()) {
                continue;
            }

            $totalCompetencias = $competencias->count();
            $totalEsperadoCurso = $matriculas->count() * $totalCompetencias;
            $totalRegistradoCurso = (int) ($notasPorCurso[$curso->id] ?? 0);
            $porcentajeCurso = $totalEsperadoCurso > 0 ? round(($totalRegistradoCurso / $totalEsperadoCurso) * 100, 2) : 0;

            $detalleAvance[] = [
                'curso_id' => $curso->id,
                'curso_nombre' => $curso->nombre,
                'curso_codigo' => $curso->codigo,
                'total_competencias' => $totalCompetencias,
                'total_estudiantes' => $matriculas->count(),
                'total_esperado' => $totalEsperadoCurso,
                'total_registrado' => $totalRegistradoCurso,
                'porcentaje' => $porcentajeCurso,
                'competencias' => $competencias->map(fn ($comp) => [
                    'id' => $comp->id,
                    'nombre' => $comp->nombre,
                    'ponderacion' => $comp->ponderacion,
                ]),
            ];

            $totalEsperadoGlobal += $totalEsperadoCurso;
            $totalRegistradoGlobal += $totalRegistradoCurso;
        }

        $porcentajeGlobal = $totalEsperadoGlobal > 0 ? round(($totalRegistradoGlobal / $totalEsperadoGlobal) * 100, 2) : 0;

        $nivelId = $aula->nivel_id;
        $cuadrosHab = ConfiguracionAvanceCuadro::getCuadrosForNivel($nivelId);
        $isEn = fn ($key) => $cuadrosHab === null || in_array($key, $cuadrosHab, true);

        $libreraReg = $totalRegistradoGlobal;
        $libreraEsp = $totalEsperadoGlobal;
        $cuadrosAvance = [];

        $totalEstudiantes = $matriculas->count();
        if ($totalEstudiantes > 0) {
            if ($isEn('competencias_transversales')) {
                $ctCount = DB::table('competencias_transversales')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($ctCount > 0) {
                    $e = $totalEstudiantes * $ctCount;
                    $r = (int) DB::table('registro_competencias_transversales')
                        ->whereIn('matricula_id', $matriculaIds)
                        ->where('periodo_id', $periodoId)
                        ->whereNotNull('nota')
                        ->where('nota', '!=', '')
                        ->count();
                    $r = min($r, $e);
                    $cuadrosAvance['competencias_transversales'] = [
                        'label' => 'Comp. Transversales',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
            if ($isEn('apreciaciones_tutor')) {
                $e = $totalEstudiantes;
                $r = (int) DB::table('apreciaciones')
                    ->whereIn('matricula_id', $matriculaIds)
                    ->where('periodo_id', $periodoId)
                    ->whereNotNull('apreciacion')
                    ->where('apreciacion', '!=', '')
                    ->count();
                $r = min($r, $e);
                $cuadrosAvance['apreciaciones_tutor'] = [
                    'label' => 'Apreciaciones',
                    'registrado' => $r,
                    'esperado' => $e,
                    'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                ];
                $libreraReg += $r;
                $libreraEsp += $e;
            }
            if ($isEn('evaluacion_padre')) {
                $evalCount = DB::table('evaluaciones')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($evalCount > 0) {
                    $e = $totalEstudiantes * $evalCount;
                    $r = (int) DB::table('registro_evaluaciones')
                        ->whereIn('matricula_id', $matriculaIds)
                        ->where('periodo_id', $periodoId)
                        ->whereNotNull('valoracion')
                        ->where('valoracion', '!=', '')
                        ->count();
                    $r = min($r, $e);
                    $cuadrosAvance['evaluacion_padre'] = [
                        'label' => 'Eval. Padre',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
            if ($isEn('evaluaciones_actitudinales')) {
                $evalCount = DB::table('eval_actitudinales')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($evalCount > 0) {
                    $e = $totalEstudiantes * $evalCount;
                    $r = (int) DB::table('reg_eval_actitudinales')
                        ->whereIn('matricula_id', $matriculaIds)
                        ->where('periodo_id', $periodoId)
                        ->whereNotNull('valoracion')
                        ->where('valoracion', '!=', '')
                        ->count();
                    $r = min($r, $e);
                    $cuadrosAvance['evaluaciones_actitudinales'] = [
                        'label' => 'Eval. Actitudinal',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
            if ($isEn('inasistencias')) {
                $inasCount = DB::table('tipos_inasistencia')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($inasCount > 0) {
                    $e = $totalEstudiantes * $inasCount;
                    $r = (int) DB::table('registro_asistencias')
                        ->whereIn('matricula_id', $matriculaIds)
                        ->where('periodo_id', $periodoId)
                        ->whereNotNull('cantidad')
                        ->count();
                    $r = min($r, $e);
                    $cuadrosAvance['inasistencias'] = [
                        'label' => 'Inasistencias',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
            if ($isEn('otras_evaluaciones')) {
                $otrasCount = DB::table('tipos_otras_evaluaciones')
                    ->where('activo', true)
                    ->whereNull('deleted_at')
                    ->where('nivel_id', $nivelId)
                    ->count();
                if ($otrasCount > 0) {
                    $e = $totalEstudiantes * $otrasCount;
                    $r = (int) DB::table('registro_otras_evaluaciones')
                        ->whereIn('matricula_id', $matriculaIds)
                        ->where('periodo_id', $periodoId)
                        ->whereNotNull('valor')
                        ->where('valor', '!=', '')
                        ->distinct()
                        ->count(DB::raw("CONCAT(matricula_id, '-', tipo_otra_evaluacion_id)"));
                    $r = min($r, $e);
                    $cuadrosAvance['otras_evaluaciones'] = [
                        'label' => 'Comportamiento',
                        'registrado' => $r,
                        'esperado' => $e,
                        'porcentaje' => $e > 0 ? round($r / $e * 100, 1) : 0,
                    ];
                    $libreraReg += $r;
                    $libreraEsp += $e;
                }
            }
        }

        $libretaPorcentaje = $libreraEsp > 0 ? round($libreraReg / $libreraEsp * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'aula' => [
                    'id' => $aula->id,
                    'nombre' => $aula->nombre,
                    'codigo' => $aula->codigo,
                    'nivel' => $aula->grado->nivel->nombre ?? '',
                    'grado' => $aula->grado->nombre ?? '',
                    'seccion' => $aula->seccion->nombre ?? '',
                    'turno' => $aula->turno,
                    'docente' => $aula->docente ? $aula->docente->name : 'No asignado',
                ],
                'matriculas' => $matriculas->map(fn ($m) => [
                    'id' => $m->id,
                    'alumno_nombre' => trim(($m->alumno->apellido_paterno ?? '') . ' ' . ($m->alumno->apellido_materno ?? '') . ' ' . ($m->alumno->nombres ?? '')),
                    'alumno_codigo' => $m->alumno->codigo_estudiante ?? '',
                ]),
                'cursos' => $detalleAvance,
                'resumen' => [
                    'total_cursos' => $cursosAsignados->count(),
                    'total_esperado' => $totalEsperadoGlobal,
                    'total_registrado' => $totalRegistradoGlobal,
                    'porcentaje_global' => $porcentajeGlobal,
                    'color' => $this->getColorByPercentage($porcentajeGlobal),
                    'cuadros_avance' => $cuadrosAvance,
                    'libreta_porcentaje' => $libretaPorcentaje,
                ],
            ],
        ]);
    }

    private function getColorByPercentage($porcentaje)
    {
        if ($porcentaje >= 90) {
            return '#28a745';
        }
        if ($porcentaje >= 70) {
            return '#17a2b8';
        }
        if ($porcentaje >= 50) {
            return '#ffc107';
        }
        if ($porcentaje >= 25) {
            return '#fd7e14';
        }

        return '#dc3545';
    }
}
