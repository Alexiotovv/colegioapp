<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\AnioAcademico;
use App\Models\CargaHoraria;
use App\Models\Matricula;
use App\Models\ModuloRegistro;
use App\Models\Periodo;
use App\Models\Nota;
use App\Models\RegistroOrdenMerito;
use App\Models\TipoOrdenMerito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroOrdenMeritoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();

        if ($rol === 'admin') {
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        } else {
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->whereHas('cargaHoraria', function ($query) use ($docenteId) {
                    $query->where('docente_id', $docenteId)->where('estado', 'activo');
                })
                ->where('activo', true)
                ->distinct()
                ->orderBy('nombre')
                ->get();
        }

        $tiposOrdenMerito = TipoOrdenMerito::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $periodos = Periodo::with('anioAcademico')->orderBy('orden')->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();

        return view('registro-orden-meritos.index', compact('aulas', 'tiposOrdenMerito', 'periodos', 'anioActivo'));
    }

    public function getDataForRegistro(Request $request)
    {
        $request->validate([
            'aula_id' => 'required|exists:aulas,id',
            'periodo_id' => 'required|exists:periodos,id',
        ]);

        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;

        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();

        if ($rol !== 'admin') {
            $tieneAcceso = CargaHoraria::where('aula_id', $aulaId)
                ->where('docente_id', $docenteId)
                ->where('estado', CargaHoraria::ESTADO_ACTIVO)
                ->exists();

            if (!$tieneAcceso) {
                return response()->json(['error' => 'No tienes acceso a este aula'], 403);
            }
        }

        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();

        $aula = Aula::with('grado.nivel')->find($aulaId);
        $nivelId = optional(optional($aula->grado)->nivel)->id;
        $nivelNombre = strtolower(optional(optional($aula->grado)->nivel)->nombre ?? '');
        $aulaEsPrimaria = str_contains($nivelNombre, 'primaria');
        $aulaEsSecundaria = str_contains($nivelNombre, 'secundaria');

        $tiposOrdenMerito = TipoOrdenMerito::with('nivel')
            ->where('activo', true)
            ->when($nivelId !== null, function ($query) use ($nivelId) {
                $query->where(function ($q) use ($nivelId) {
                    $q->whereNull('nivel_id')->orWhere('nivel_id', $nivelId);
                });
            })
            ->orderBy('orden')
            ->get();

        $primerTipo = $tiposOrdenMerito->first();

        $matriculaIds = $matriculas->pluck('id')->toArray();
        $registros = RegistroOrdenMerito::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->get()
            ->keyBy('matricula_id');

        $periodo = Periodo::find($periodoId);
        $registrosHabilitados = $periodo ? $periodo->activo : false;

        return response()->json([
            'matriculas' => $matriculas,
            'tipos_orden_merito' => $tiposOrdenMerito,
            'registros' => $registros,
            'registros_habilitados' => $registrosHabilitados,
            'aula_es_primaria' => $aulaEsPrimaria,
            'aula_es_secundaria' => $aulaEsSecundaria,
            'primer_tipo_id' => $primerTipo ? $primerTipo->id : null,
        ]);
    }

    public function calcularOrdenMeritoAutomatico(Request $request)
    {
        $request->validate([
            'aula_id' => 'required|exists:aulas,id',
            'periodo_id' => 'required|exists:periodos,id',
        ]);

        $aula = Aula::with('grado.nivel')->findOrFail($request->aula_id);
        $nivelNombre = strtolower(optional(optional($aula->grado)->nivel)->nombre ?? '');

        if (!str_contains($nivelNombre, 'secundaria')) {
            return response()->json([
                'success' => false,
                'message' => 'El cálculo automático solo está disponible para aulas de Secundaria.',
            ], 422);
        }

        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();

        if ($rol !== 'admin') {
            $tieneAcceso = CargaHoraria::where('aula_id', $request->aula_id)
                ->where('docente_id', $docenteId)
                ->where('estado', CargaHoraria::ESTADO_ACTIVO)
                ->exists();

            if (!$tieneAcceso) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este aula',
                ], 403);
            }
        }

        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $request->aula_id)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();

        $cargas = CargaHoraria::with([
                'curso' => function ($query) {
                    $query->with(['competencias' => function ($q) {
                        $q->where('activo', true)->orderBy('orden')->orderBy('nombre');
                    }]);
                }
            ])
            ->where('aula_id', $request->aula_id)
            ->where('estado', CargaHoraria::ESTADO_ACTIVO)
            ->whereHas('curso', function ($query) use ($aula) {
                $query->where('activo', true)
                    ->where('anio_academico_id', $aula->anio_academico_id);
            })
            ->get()
            ->groupBy('curso_id')
            ->map(fn ($grupo) => $grupo->first())
            ->values()
            ->sortBy(function ($carga) {
                return [
                    $carga->curso?->orden ?? 9999,
                    $carga->curso?->nombre ?? '',
                    $carga->curso?->codigo ?? '',
                ];
            })
            ->values();

        if ($cargas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cursos asignados para el aula seleccionada.',
            ], 422);
        }

        $periodo = Periodo::findOrFail($request->periodo_id);
        if (!$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado.',
            ], 422);
        }

        $notas = $this->obtenerNotasParaOrdenMerito($request->periodo_id, $matriculas, $cargas);
        $ranking = $this->calcularRankingOrdenMerito($matriculas, $cargas, $notas);

        return response()->json([
            'success' => true,
            'message' => 'Orden de mérito calculado correctamente.',
            'ranking' => $ranking,
        ]);
    }

    public function saveRegistros(Request $request)
    {
        $request->validate([
            'registros' => 'required|array',
            'registros.*.matricula_id' => 'required|exists:matriculas,id',
            'registros.*.tipo_orden_merito_id' => 'nullable|exists:tipos_orden_merito,id',
            'registros.*.nota_valor' => 'nullable|integer|min:1|max:40',
            'registros.*.observacion' => 'nullable|string',
            'periodo_id' => 'required|exists:periodos,id',
            'aula_id' => 'required|exists:aulas,id',
        ]);

        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar orden de mérito.',
            ], 422);
        }

        $aula = Aula::with('grado.nivel')->find($request->aula_id);
        $nivelNombre = strtolower(optional(optional($aula->grado)->nivel)->nombre ?? '');
        $esPrimaria = str_contains($nivelNombre, 'primaria');

        $matriculasValidas = Matricula::where('aula_id', $request->aula_id)
            ->where('estado', 'activa')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $registros = collect($request->registros);
        $matriculasRecibidas = $registros->pluck('matricula_id')->map(fn ($id) => (int) $id)->unique()->values()->toArray();
        $noValidas = array_diff($matriculasRecibidas, $matriculasValidas);
        if (!empty($noValidas)) {
            return response()->json([
                'success' => false,
                'message' => 'Se detectaron matrículas que no pertenecen al aula seleccionada.',
            ], 422);
        }

        // Ahora la entrada principal es `nota_valor` (entero 1..40). Contamos los registros con nota.
        $conOrden = $registros->filter(function ($item) {
            return isset($item['nota_valor']) && $item['nota_valor'] !== null && $item['nota_valor'] !== '';
        })->values();

        if ($esPrimaria) {
            if ($conOrden->count() > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'En aulas de Primaria solo puede registrarse un alumno con orden de mérito.',
                ], 422);
            }

            if ($conOrden->count() === 1) {
                $valor = (int) $conOrden->first()['nota_valor'];
                if ($valor !== 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'En Primaria solo se puede registrar el primer lugar del aula (valor 1).',
                    ], 422);
                }
            }
        }

        DB::beginTransaction();

        try {
            $docenteId = auth()->id();

            foreach ($registros as $item) {
                $matriculaId = (int) $item['matricula_id'];
                $tipoOrdenMeritoId = !empty($item['tipo_orden_merito_id']) ? (int) $item['tipo_orden_merito_id'] : null;
                $notaValor = isset($item['nota_valor']) && $item['nota_valor'] !== null && $item['nota_valor'] !== '' ? (int) $item['nota_valor'] : null;

                // Si no hay tipo ni nota, borramos registro existente
                if ($tipoOrdenMeritoId === null && $notaValor === null) {
                    RegistroOrdenMerito::where('matricula_id', $matriculaId)
                        ->where('periodo_id', $request->periodo_id)
                        ->delete();
                    continue;
                }

                RegistroOrdenMerito::updateOrCreate(
                    [
                        'matricula_id' => $matriculaId,
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'tipo_orden_merito_id' => $tipoOrdenMeritoId,
                        'nota_valor' => $notaValor,
                        'docente_id' => $docenteId,
                        'observacion' => $item['observacion'] ?? null,
                        'fecha_registro' => now(),
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro de orden de mérito guardado exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleHabilitacion(Request $request)
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;

        if ($rol !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
            ], 403);
        }

        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo) {
            return response()->json([
                'success' => false,
                'message' => 'Periodo no encontrado',
            ], 404);
        }

        $periodo->update(['activo' => !$periodo->activo]);

        return response()->json([
            'success' => true,
            'message' => $periodo->activo
                ? 'Registro de orden de mérito habilitado'
                : 'Registro de orden de mérito deshabilitado',
            'habilitado' => $periodo->activo,
        ]);
    }

    public function getOpcionesNotas()
    {
        $modulo = ModuloRegistro::where('codigo', 'orden_merito')->first();

        if (!$modulo) {
            return response()->json([]);
        }

        // Usamos el helper del modelo para obtener opciones con tipo_dato y valor_numerico
        $tiposNotas = $modulo->getTiposNotasOptions();

        // Añadir info de escala (min/max) desde la tabla 'escalas_calificacion' si existe
        $result = $tiposNotas->map(function ($item) {
            $escala = DB::table('escalas_calificacion')->where('codigo', $item['codigo'])->first();

            return array_merge($item, [
                'escala_min' => $escala->valor_numerico_min ?? null,
                'escala_max' => $escala->valor_numerico_max ?? null,
            ]);
        })->values();

        return response()->json($result);
    }

    private function obtenerNotasParaOrdenMerito(int $periodoId, $matriculas, $cargas)
    {
        $matriculaIds = $matriculas->pluck('id')->all();
        $competenciaIds = $cargas->flatMap(function ($carga) {
            return $carga->curso?->competencias?->pluck('id') ?? collect();
        })->filter()->unique()->values()->all();

        if (empty($matriculaIds) || empty($competenciaIds)) {
            return collect();
        }

        return Nota::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('competencia_id', $competenciaIds)
            ->get()
            ->keyBy(function ($nota) {
                return $nota->matricula_id . '_' . $nota->competencia_id;
            });
    }

    private function calcularRankingOrdenMerito($matriculas, $cargas, $notas)
    {
        $mapaCursos = $cargas->map(function ($carga) {
            return [
                'curso_id' => $carga->curso?->id,
                'curso_nombre' => $carga->curso?->nombre ?? '',
                'curso_codigo' => $carga->curso?->codigo ?? '',
                'curso_orden' => $carga->curso?->orden ?? 9999,
                'competencias' => $carga->curso?->competencias?->where('activo', true)->sortBy('orden')->values() ?? collect(),
            ];
        })->sortBy(fn ($item) => [$item['curso_orden'], $item['curso_nombre'], $item['curso_codigo']])->values();

        $resultados = $matriculas->map(function ($matricula) use ($mapaCursos, $notas) {
            $promediosCurso = [];

            foreach ($mapaCursos as $curso) {
                $valoresCompetencias = [];

                foreach ($curso['competencias'] as $competencia) {
                    $nota = $notas[$matricula->id . '_' . $competencia->id] ?? null;
                    $valoresCompetencias[] = $this->convertirNotaANumero($nota?->nota);
                }

                $promedioCurso = count($valoresCompetencias) > 0
                    ? round(array_sum($valoresCompetencias) / count($valoresCompetencias), 2)
                    : 0;

                $promediosCurso[] = [
                    'curso_id' => $curso['curso_id'],
                    'curso_nombre' => $curso['curso_nombre'],
                    'nota' => $promedioCurso,
                ];
            }

            $promedioGeneral = count($promediosCurso) > 0
                ? round(array_sum(array_column($promediosCurso, 'nota')) / count($promediosCurso), 2)
                : 0;

            return [
                'matricula_id' => $matricula->id,
                'alumno_nombre' => $matricula->alumno?->nombre_completo ?? trim(($matricula->alumno?->apellido_paterno ?? '') . ' ' . ($matricula->alumno?->apellido_materno ?? '') . ' ' . ($matricula->alumno?->nombres ?? '')),
                'promedio_general' => $promedioGeneral,
                'cursos' => $promediosCurso,
                'firma' => implode('|', array_map(fn ($item) => number_format($item['nota'], 2, '.', ''), $promediosCurso)),
            ];
        })->values();

        $ordenados = $resultados->sort(function ($a, $b) {
            if (abs($b['promedio_general'] - $a['promedio_general']) > 0.0001) {
                return $b['promedio_general'] <=> $a['promedio_general'];
            }

            $countCursos = max(count($a['cursos']), count($b['cursos']));
            for ($i = 0; $i < $countCursos; $i++) {
                $notaA = $a['cursos'][$i]['nota'] ?? 0;
                $notaB = $b['cursos'][$i]['nota'] ?? 0;
                if (abs($notaB - $notaA) > 0.0001) {
                    return $notaB <=> $notaA;
                }
            }

            return 0;
        })->values();

        $final = collect();
        $grupoActual = collect();
        $firmaActual = null;

        foreach ($ordenados as $item) {
            $firma = number_format($item['promedio_general'], 2, '.', '') . '|' . $item['firma'];

            if ($firmaActual === null || $firma === $firmaActual) {
                $grupoActual->push($item);
                $firmaActual = $firma;
                continue;
            }

            $grupoFinal = $grupoActual->count() > 1 ? $grupoActual->shuffle()->values() : $grupoActual;
            foreach ($grupoFinal as $fila) {
                $final->push($fila);
            }

            $grupoActual = collect([$item]);
            $firmaActual = $firma;
        }

        if ($grupoActual->isNotEmpty()) {
            $grupoFinal = $grupoActual->count() > 1 ? $grupoActual->shuffle()->values() : $grupoActual;
            foreach ($grupoFinal as $fila) {
                $final->push($fila);
            }
        }

        return $final->values()->map(function ($item, $index) {
            return [
                'matricula_id' => $item['matricula_id'],
                'alumno_nombre' => $item['alumno_nombre'],
                'promedio_general' => $item['promedio_general'],
                'orden_merito' => $index + 1,
            ];
        });
    }

    private function convertirNotaANumero($nota): float
    {
        $nota = trim((string) $nota);

        if ($nota === '') {
            return 0.0;
        }

        $literal = strtoupper($nota);
        $mapa = [
            'AD' => 20,
            'A' => 17,
            'B' => 13,
            'C' => 11,
        ];

        if (isset($mapa[$literal])) {
            return (float) $mapa[$literal];
        }

        if (is_numeric($nota)) {
            return (float) $nota;
        }

        return 0.0;
    }
}
