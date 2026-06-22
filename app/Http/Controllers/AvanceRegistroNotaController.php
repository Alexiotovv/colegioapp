<?php
// app/Http/Controllers/AvanceRegistroNotaController.php

namespace App\Http\Controllers;

use App\Models\AvanceNota;
use App\Models\Aula;
use App\Models\Curso;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\Capacidad;
use App\Models\CargaHoraria;
use App\Models\AnioAcademico;
use App\Models\Competencia;
use App\Models\ConclusionDescriptivaAvance;
use App\Models\Configuracion;
use App\Models\ConfiguracionAvanceCuadro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ModuloRegistro;//Probablemente se vaya

class AvanceRegistroNotaController extends Controller
{
    
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Mostrar solo aulas que tienen carga horaria con curso asignado.
        // Para admin se muestran todas esas aulas; para docente solo las suyas.
        $queryAulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
            ->whereHas('cargaHoraria', function($query) use ($docenteId, $rol) {
                if ($rol !== 'admin') {
                    $query->where('docente_id', $docenteId);
                }
                $query->where('estado', CargaHoraria::ESTADO_ACTIVO)
                    ->whereNotNull('curso_id');
            })
            ->where('activo', true)
            ->distinct()
            ->orderBy('nombre');

        $aulas = $queryAulas->get();
        
        $periodos = Periodo::with('anioAcademico')
                        ->orderBy('orden')
                        ->get();

        $periodosHabilitados = $periodos
            ->pluck('avance_activo', 'id')
            ->map(fn ($estado) => (bool) $estado)
            ->toArray();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        // Obtener configuraciones de caracteres
        $caracteresConfig = [
            'conclusiones_caracteres_max' => Configuracion::getValor('conclusiones_caracteres_max', 500),
        ];
        
        return view('avance-registro-notas.index', compact('aulas', 'periodos', 'periodosHabilitados', 'anioActivo', 'caracteresConfig'));
    }
    
    public function getCursosByAula(Request $request)
    {
        $aulaId = $request->aula_id;
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Obtener el aula y su nivel
        $aula = Aula::with('nivel')->find($aulaId);
        if (!$aula) {
            return response()->json([]);
        }
        
        $nivelId = $aula->nivel_id;
        
        // Si es admin, ver todos los cursos del nivel del aula
        if ($rol === 'admin') {
            $cursos = Curso::with('nivel')
                ->where('activo', true)
                ->where('nivel_id', $nivelId)
                ->ordered()
                ->get();
            
            return response()->json($cursos);
        }
        
        // Verificar que el aula realmente pertenece al docente
        $tieneAcceso = CargaHoraria::where('docente_id', $docenteId)
            ->where('aula_id', $aulaId)
            ->where('estado', 'activo')
            ->exists();
        
        if (!$tieneAcceso) {
            return response()->json([]);
        }
        
        // Obtener cursos asignados al docente para esta aula y descartar asignaciones incompletas.
        $cursos = CargaHoraria::with(['curso.nivel'])
            ->where('docente_id', $docenteId)
            ->where('aula_id', $aulaId)
            ->where('estado', CargaHoraria::ESTADO_ACTIVO)
            ->whereNotNull('curso_id')
            ->whereHas('curso', function ($query) {
                $query->where('activo', true);
            })
            ->get()
            ->pluck('curso')
            ->filter()
            ->unique('id')
            ->values();
        
        return response()->json($cursos);
    }
    
    public function getDataForNotas(Request $request)
    {
        $aulaId = $request->aula_id;
        $aula = Aula::with(['grado.nivel'])->find($aulaId);
        $cursoId = $request->curso_id;
        $periodoId = $request->periodo_id;
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Si es docente, verificar que el aula le pertenece
        if ($rol !== 'admin') {
            $tieneAcceso = CargaHoraria::where('docente_id', $docenteId)
                ->where('aula_id', $aulaId)
                ->where('estado', 'activo')
                ->exists();
            
            if (!$tieneAcceso) {
                return response()->json(['error' => 'No tienes acceso a este aula'], 403);
            }
        }

        $matriculas = Matricula::with(['alumno'])
        ->where('aula_id', $aulaId)
        ->whereIn('estado', ['activa', 'retirada']) // agregado alexioto
        ->orderBy(
            DB::raw('CONCAT(
                (SELECT apellido_paterno FROM alumnos WHERE alumnos.id = matriculas.alumno_id), 
                " ", 
                (SELECT apellido_materno FROM alumnos WHERE alumnos.id = matriculas.alumno_id), 
                " ", 
                (SELECT nombres FROM alumnos WHERE alumnos.id = matriculas.alumno_id)
            )')
        )
        ->get();
        
        // Obtener competencias del curso
        $competencias = Competencia::where('curso_id', $cursoId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        // Obtener notas existentes
        $matriculaIds = $matriculas->pluck('id')->toArray();
        $competenciaIds = $competencias->pluck('id')->toArray();
        
        $notas = AvanceNota::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('competencia_id', $competenciaIds)
            ->with('conclusionDescriptiva')
            ->get()
            ->map(function($nota) {
                return [
                    'id' => $nota->id,
                    'matricula_id' => $nota->matricula_id,
                    'competencia_id' => $nota->competencia_id,
                    'nota' => $nota->nota,
                    'tiene_conclusion' => $nota->conclusionDescriptiva !== null
                ];
            })
            ->keyBy(function($item) {
                return $item['matricula_id'] . '_' . $item['competencia_id'];
            });
        
        $notasHabilitadas = Periodo::whereKey((int) $periodoId)
            ->value('avance_activo') ?? false;

        $esPrimaria = false;
        $esSecundaria = false;
        if ($aula && $aula->grado && $aula->grado->nivel) {
            $nivelNombre = $aula->grado->nivel->nombre;
            $esPrimaria = stripos($nivelNombre, 'primaria') !== false;
            $esSecundaria = stripos($nivelNombre, 'secundaria') !== false;
        }

        $requiereConclusionBCPrimaria = (bool) Configuracion::getValor('notas_requiere_conclusion_bc_primaria', false);
        $requiereConclusionBSecundaria = (bool) Configuracion::getValor('notas_requiere_conclusion_b_secundaria', false);
        $mostrarConclusionDescriptiva = true;
        $nivelId = $aula?->grado?->nivel?->id;
        if ($nivelId) {
            $mostrarConclusionDescriptiva = ConfiguracionAvanceCuadro::isConclusionVisibleForNivel($nivelId);
        }
        
        return response()->json([
            'matriculas' => $matriculas,
            'competencias' => $competencias,
            'notas' => $notas,
            'notas_habilitadas' => $notasHabilitadas,
            'aula_es_primaria' => $esPrimaria,
            'aula_es_secundaria' => $esSecundaria,
            'requerir_conclusion_bc_primaria' => $requiereConclusionBCPrimaria,
            'requerir_conclusion_b_secundaria' => $requiereConclusionBSecundaria,
            'mostrar_conclusion_descriptiva' => (bool) $mostrarConclusionDescriptiva,
        ]);
    }
    
    // Guardar o actualizar notas
    public function saveNotas(Request $request)
    {
        // Blindaje adicional para clientes con payload truncado o formatos mixtos:
        // intentar recuperar IDs críticos desde query params, headers o cuerpo JSON crudo.
        $aulaId = $request->input('aula_id')
            ?? $request->query('aula_id')
            ?? $request->header('X-Aula-Id');

        $periodoId = $request->input('periodo_id')
            ?? $request->query('periodo_id')
            ?? $request->header('X-Periodo-Id');

        if ((!$aulaId || !$periodoId) && $request->getContent()) {
            $rawBody = json_decode($request->getContent(), true);
            if (is_array($rawBody)) {
                $aulaId = $aulaId ?: ($rawBody['aula_id'] ?? null);
                $periodoId = $periodoId ?: ($rawBody['periodo_id'] ?? null);
            }
        }

        if ($aulaId !== null || $periodoId !== null) {
            $request->merge([
                'aula_id' => $aulaId,
                'periodo_id' => $periodoId,
            ]);
        }

        $request->validate([
            'notas' => 'required|array',
            'aula_id' => 'required|exists:aulas,id',
            'periodo_id' => 'required|exists:periodos,id',
            'conclusiones' => 'array',
            'conclusiones.*.matricula_id' => 'required|exists:matriculas,id',
            'conclusiones.*.competencia_id' => 'required|exists:competencias,id',
            'conclusiones.*.conclusion' => 'required|string',
        ]);
        
        $periodoAvanceActivo = Periodo::whereKey((int) $request->periodo_id)
            ->value('avance_activo') ?? false;

        if (!$periodoAvanceActivo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar avance de notas.'
            ], 422);
        }

        $aula = Aula::with(['grado.nivel'])->find($request->aula_id);
        $esPrimaria = false;
        $esSecundaria = false;
        if ($aula && $aula->grado && $aula->grado->nivel) {
            $nivelNombre = $aula->grado->nivel->nombre;
            $esPrimaria = stripos($nivelNombre, 'primaria') !== false;
            $esSecundaria = stripos($nivelNombre, 'secundaria') !== false;
        }
        $nivelId = $aula?->grado?->nivel?->id;
        $mostrarConclusionDescriptiva = true;
        if ($nivelId) {
            $mostrarConclusionDescriptiva = ConfiguracionAvanceCuadro::isConclusionVisibleForNivel($nivelId);
        }
        $requiereConclusionBCPrimaria = (bool) Configuracion::getValor('notas_requiere_conclusion_bc_primaria', false);
        $requiereConclusionBSecundaria = (bool) Configuracion::getValor('notas_requiere_conclusion_b_secundaria', false);

        $conclusionesMap = [];
        foreach ($request->conclusiones ?? [] as $conclusion) {
            $key = $conclusion['matricula_id'].'_'.$conclusion['competencia_id'];
            $conclusionesMap[$key] = trim($conclusion['conclusion']);
        }

        $matriculaIds = collect($request->notas)->pluck('matricula_id')->unique()->toArray();
        $competenciaIds = collect($request->notas)->pluck('competencia_id')->unique()->toArray();
        $existingNotas = AvanceNota::with('conclusionDescriptiva')
            ->where('periodo_id', $request->periodo_id)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('competencia_id', $competenciaIds)
            ->get()
            ->keyBy(function ($nota) {
                return $nota->matricula_id.'_'.$nota->competencia_id;
            });
        
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            foreach ($request->notas as $item) {
                $notaValor = strtoupper(trim($item['nota']));
                $key = $item['matricula_id'].'_'.$item['competencia_id'];
                $tieneConclusion = isset($conclusionesMap[$key]) && $conclusionesMap[$key] !== '';
                $existingNota = $existingNotas[$key] ?? null;
                $existingConclusion = $existingNota && $existingNota->conclusionDescriptiva ? true : false;
                $notaAnterior = $existingNota ? strtoupper(trim((string) $existingNota->nota)) : null;
                $requiereConclusionActualPrimaria =
                    $mostrarConclusionDescriptiva &&
                    $requiereConclusionBCPrimaria &&
                    $esPrimaria &&
                    in_array($notaValor, ['B', 'C']);
                $requiereConclusionActualSecundaria =
                    $mostrarConclusionDescriptiva &&
                    $requiereConclusionBSecundaria &&
                    $esSecundaria &&
                    $notaValor === 'C';
                $requiereConclusionActual = $requiereConclusionActualPrimaria || $requiereConclusionActualSecundaria;

                $requeriaConclusionAntesPrimaria =
                    $mostrarConclusionDescriptiva &&
                    $requiereConclusionBCPrimaria &&
                    $esPrimaria &&
                    in_array($notaAnterior, ['B', 'C']);
                $requeriaConclusionAntesSecundaria =
                    $mostrarConclusionDescriptiva &&
                    $requiereConclusionBSecundaria &&
                    $esSecundaria &&
                    $notaAnterior === 'C';
                $eliminarConclusionPorCambio =
                    ($requeriaConclusionAntesPrimaria || $requeriaConclusionAntesSecundaria) &&
                    !$requiereConclusionActual;

                if ($requiereConclusionActualPrimaria) {
                    if (!$tieneConclusion && !$existingConclusion) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Las notas B/C en aulas de Primaria requieren una conclusión descriptiva. Por favor registre la conclusión en el icono de comentario antes de guardar.'
                        ], 422);
                    }
                }

                if ($requiereConclusionActualSecundaria) {
                    if (!$tieneConclusion && !$existingConclusion) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'La nota C en aulas de Secundaria requiere una conclusión descriptiva. Por favor registre la conclusión en el icono de comentario antes de guardar.'
                        ], 422);
                    }
                }
                // 🔥 Usar updateOrCreate con los tres campos clave
                $notaModel = AvanceNota::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'competencia_id' => $item['competencia_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'nota' => $item['nota'],
                        'tipo_calificacion' => $this->determinarTipoCalificacion($item['nota']),
                        'fecha_registro' => now(),
                        'observacion' => $item['observacion'] ?? null,
                    ]
                );

                if ($mostrarConclusionDescriptiva && (!$requiereConclusionActual || $eliminarConclusionPorCambio) && $notaModel) {
                    ConclusionDescriptivaAvance::where('nota_avance_id', $notaModel->id)->delete();
                }

                if ($mostrarConclusionDescriptiva && isset($conclusionesMap[$key]) && $conclusionesMap[$key] !== '') {
                    if (!$requiereConclusionActual) {
                        continue;
                    }

                    if ($notaModel) {
                        ConclusionDescriptivaAvance::updateOrCreate(
                            ['nota_avance_id' => $notaModel->id],
                            ['conclusion' => $conclusionesMap[$key]]
                        );
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Avance de notas guardado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el avance de notas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // 🔥 Habilitar/Deshabilitar registro de notas para un periodo (solo ADMIN)
    public function toggleHabilitacion(Request $request)
    {
        // Verificar que el usuario sea admin
        if (auth()->user()->rol !== 'admin' && auth()->user()->role->nombre !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }
        
        $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
        ]);

        $periodo = Periodo::find($request->periodo_id);

        if (!$periodo) {
            return response()->json([
                'success' => false,
                'message' => 'Periodo no encontrado'
            ], 404);
        }

        $periodo->avance_activo = !$periodo->avance_activo;
        $periodo->save();

        return response()->json([
            'success' => true,
            'message' => $periodo->avance_activo ? 'Registro de avance de notas habilitado' : 'Registro de avance de notas deshabilitado',
            'habilitado' => (bool) $periodo->avance_activo
        ]);
    }
    
    private function determinarTipoCalificacion($nota)
    {
        $notasLiterales = ['AD', 'A', 'B', 'C', 'CND','EXO'];
        
        if (in_array(strtoupper($nota), $notasLiterales)) {
            return 'LITERAL';
        }
        
        if (is_numeric($nota)) {
            return 'NUMERICA';
        }
        
        return 'CUALITATIVA';
    }

    public function getConclusion(AvanceNota $notaAvance)
    {
        $conclusion = $notaAvance->conclusionDescriptiva;
        
        return response()->json([
            'success' => true,
            'nota_id' => $notaAvance->id,
            'conclusion' => $conclusion ? $conclusion->conclusion : '',
            'alumno_nombre' => $notaAvance->matricula->alumno->nombre_completo,
            'competencia_nombre' => $notaAvance->competencia->nombre,
            'nota_valor' => $notaAvance->nota
        ]);
    }

    public function saveConclusion(Request $request)
    {
        $request->validate([
            'nota_id' => 'required|exists:notas_avance,id',
            'conclusion' => 'required|string|max:500',
            'nota_valor_actual' => 'nullable|string|max:10',
        ]);
        
        $nota = AvanceNota::with(['matricula.aula.grado.nivel'])->find($request->nota_id);
        
        if (!$nota) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada'
            ], 404);
        }

        $nivelId = $nota->matricula?->aula?->grado?->nivel?->id;
        if ($nivelId && !ConfiguracionAvanceCuadro::isConclusionVisibleForNivel($nivelId)) {
            return response()->json([
                'success' => false,
                'message' => 'La conclusión descriptiva está deshabilitada para este nivel en Avance Cuadros.'
            ], 403);
        }

        $esPrimaria = false;
        $esSecundaria = false;
        $nivelNombre = $nota->matricula?->aula?->grado?->nivel?->nombre;
        if ($nivelNombre) {
            $esPrimaria = stripos($nivelNombre, 'primaria') !== false;
            $esSecundaria = stripos($nivelNombre, 'secundaria') !== false;
        }

        $requiereConclusionBCPrimaria = (bool) Configuracion::getValor('notas_requiere_conclusion_bc_primaria', false);
        $requiereConclusionBSecundaria = (bool) Configuracion::getValor('notas_requiere_conclusion_b_secundaria', false);
        $notaValor = strtoupper(trim((string) ($request->nota_valor_actual ?? $nota->nota)));

        $requiereConclusionActual =
            ($requiereConclusionBCPrimaria && $esPrimaria && in_array($notaValor, ['B', 'C'])) ||
            ($requiereConclusionBSecundaria && $esSecundaria && $notaValor === 'C');

        if (!$requiereConclusionActual) {
            ConclusionDescriptivaAvance::where('nota_avance_id', $nota->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'La nota actual no requiere conclusión descriptiva. Se eliminó cualquier conclusión existente.'
            ]);
        }
        
        // Actualizar o crear la conclusión
        $conclusion = ConclusionDescriptivaAvance::updateOrCreate(
            ['nota_avance_id' => $request->nota_id],
            ['conclusion' => $request->conclusion]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Conclusión guardada exitosamente',
            'conclusion' => $conclusion
        ]);
    }


    //Probablemente se vaya esta función, pero por ahora la dejo aquí para que el frontend pueda obtener las opciones de notas según la configuración
    public function getOpcionesNotas()
    {
        $modulo = ModuloRegistro::where('codigo', 'avance-registro-notas')->first();
        if (!$modulo) {
            $modulo = ModuloRegistro::where('codigo', 'notas')->first();
        }
        
        if (!$modulo) {
            return response()->json(['AD', 'A', 'B', 'C', 'CND', 'EXO']);
        }
        
        $tiposNotas = $modulo->getTiposNotasOptions();
        $opciones = $tiposNotas->pluck('codigo')->toArray();
        
        return response()->json($opciones);
    }


}