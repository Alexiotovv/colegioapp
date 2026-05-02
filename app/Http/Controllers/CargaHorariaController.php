<?php
// app/Http/Controllers/CargaHorariaController.php

namespace App\Http\Controllers;

use App\Models\CargaHoraria;
use App\Models\User;
use App\Models\Curso;
use App\Models\Aula;
use App\Models\Periodo;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;
use DB;

class CargaHorariaController extends Controller
{
    public function index(Request $request)
    {
        $query = CargaHoraria::with(['docente', 'curso.nivel', 'aula.grado.nivel', 'aula.seccion']);
        
        if ($request->filled('docente_id')) {
            $query->where('docente_id', $request->docente_id);
        }
        
        if ($request->filled('curso_id')) {
            $query->where('curso_id', $request->curso_id);
        }
        
        if ($request->filled('aula_id')) {
            $query->where('aula_id', $request->aula_id);
        }
        
        $cargas = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        // Obtener todos los usuarios excepto admin
        $docentes = User::whereHas('role', function($query) {
            $query->where('nombre', '!=', 'admin');
        })->where('activo', true)
                        ->orderBy('name')
                        ->get(['id', 'name', 'email']);
        
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        $aulas = Aula::with(['grado.nivel', 'seccion'])->where('activo', true)->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('carga-horaria.index', compact('cargas', 'docentes', 'cursos', 'aulas', 'anioActivo'));
    }

    public function create()
    {
        $docentes = User::whereHas('role', function($query) {
            $query->where('nombre', '!=', 'admin');
        })->where('activo', true)
                        ->orderBy('name')
                        ->get(['id', 'name', 'email']);
        
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        $diasSemana = CargaHoraria::DIAS_SEMANA;
        
        return view('carga-horaria.create', compact('docentes', 'cursos', 'diasSemana'));
    }
    
    public function store(Request $request)
    {
        // Verificar si viene el array de asignaciones (múltiples)
        if ($request->has('asignaciones')) {
            return $this->guardarMultiplesAsignaciones($request);
        }

        if ($request->boolean('aula_sin_curso')) {
            $request->validate([
                'docente_id' => 'required|exists:users,id',
                'aula_id' => 'required|exists:aulas,id',
            ]);

            $existing = CargaHoraria::where('docente_id', $request->docente_id)
                ->where('aula_id', $request->aula_id)
                ->whereNull('curso_id')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'El aula ya estaba asignada sin curso',
                    'data' => $existing->load(['docente', 'curso', 'aula'])
                ]);
            }

            $carga = CargaHoraria::create([
                'docente_id' => $request->docente_id,
                'curso_id' => null,
                'aula_id' => $request->aula_id,
                'horas_semanales' => $request->horas_semanales,
                'dia_semana' => $request->dia_semana,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'estado' => 'activo',
                'observaciones' => $request->observaciones,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aula asignada sin curso correctamente',
                'data' => $carga->load(['docente', 'curso', 'aula'])
            ]);
        }
        
        // Si no, procesar una asignación simple (para compatibilidad)
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'curso_id' => 'required|exists:cursos,id',
            'aula_id' => 'required|exists:aulas,id',
            'horas_semanales' => 'required|integer|min:1|max:40',
            'dia_semana' => 'nullable|string|in:' . implode(',', array_keys(CargaHoraria::DIAS_SEMANA)),
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
        ]);

        $curso = Curso::find($request->curso_id);
        $aula = Aula::find($request->aula_id);

        if (!$this->puedeAsignarCursoEnAula($curso, $aula)) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede asignar un curso de ' . ($curso?->nivel?->nombre ?? 'un nivel diferente') . ' en un aula de ' . ($aula?->nivel?->nombre ?? 'un nivel diferente') . '.'
            ], 422);
        }
        
        // 🔥 VALIDACIÓN: Verificar si ya existe la misma asignación
        $existeAsignacion = CargaHoraria::where('docente_id', $request->docente_id)
            ->where('curso_id', $request->curso_id)
            ->where('aula_id', $request->aula_id)
            ->exists();
        
        if ($existeAsignacion) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una asignación para este docente, curso y aula.'
            ], 422);
        }

        $conflictoAula = $this->conflictoCursoMismoAulaEnPeriodoActivo($request->curso_id, $request->aula_id, $request->docente_id);
        if ($conflictoAula) {
            return response()->json([
                'success' => false,
                'message' => 'Este curso ya está asignado a otro docente en el mismo aula para el año escolar activo y periodo actual.'
            ], 422);
        }
        
        // Verificar conflicto de horario para el mismo docente
        if ($request->dia_semana && $request->hora_inicio && $request->hora_fin) {
            $conflicto = CargaHoraria::where('docente_id', $request->docente_id)
                ->where('dia_semana', $request->dia_semana)
                ->where('estado', 'activo')
                ->where(function($q) use ($request) {
                    $q->whereBetween('hora_inicio', [$request->hora_inicio, $request->hora_fin])
                      ->orWhereBetween('hora_fin', [$request->hora_inicio, $request->hora_fin])
                      ->orWhere(function($q2) use ($request) {
                          $q2->where('hora_inicio', '<=', $request->hora_inicio)
                             ->where('hora_fin', '>=', $request->hora_fin);
                      });
                })
                ->exists();
                
            if ($conflicto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El docente ya tiene una carga horaria en ese horario'
                ], 422);
            }
        }
        
        $carga = CargaHoraria::create([
            'docente_id' => $request->docente_id,
            'curso_id' => $request->curso_id,
            'aula_id' => $request->aula_id,
            'horas_semanales' => $request->horas_semanales,
            'dia_semana' => $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'estado' => 'activo',
            'observaciones' => $request->observaciones,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Carga horaria asignada exitosamente',
            'data' => $carga->load(['docente', 'curso', 'aula'])
        ]);
    }
    
    public function edit(CargaHoraria $cargaHorarium)
    {
        $docentes = User::whereHas('role', function($query) {
            $query->where('nombre', '!=', 'admin');
        })->where('activo', true)
                        ->orderBy('name')
                        ->get(['id', 'name', 'email']);
        
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        $aulas = Aula::with(['grado.nivel', 'seccion'])->where('activo', true)->get();
        $diasSemana = CargaHoraria::DIAS_SEMANA;
        
        return view('carga-horaria.edit', compact('cargaHorarium', 'docentes', 'cursos', 'aulas', 'diasSemana'));
    }
    
    public function update(Request $request, CargaHoraria $cargaHorarium)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'curso_id' => 'required|exists:cursos,id',
            'aula_id' => 'required|exists:aulas,id',
            'horas_semanales' => 'required|integer|min:1|max:40',
            'dia_semana' => 'nullable|string|in:' . implode(',', array_keys(CargaHoraria::DIAS_SEMANA)),
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
        ]);
        
        // Verificar duplicado excluyendo el registro actual
        $existeAsignacion = CargaHoraria::where('docente_id', $request->docente_id)
            ->where('curso_id', $request->curso_id)
            ->where('aula_id', $request->aula_id)
            ->where('id', '!=', $cargaHorarium->id)
            ->exists();
        
        if ($existeAsignacion) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una asignación para este docente, curso y aula.'
            ], 422);
        }
        
        // Verificar conflicto excluyendo el registro actual
        if ($request->dia_semana && $request->hora_inicio && $request->hora_fin) {
            $conflicto = CargaHoraria::where('docente_id', $request->docente_id)
                ->where('dia_semana', $request->dia_semana)
                ->where('estado', 'activo')
                ->where('id', '!=', $cargaHorarium->id)
                ->where(function($q) use ($request) {
                    $q->whereBetween('hora_inicio', [$request->hora_inicio, $request->hora_fin])
                      ->orWhereBetween('hora_fin', [$request->hora_inicio, $request->hora_fin])
                      ->orWhere(function($q2) use ($request) {
                          $q2->where('hora_inicio', '<=', $request->hora_inicio)
                             ->where('hora_fin', '>=', $request->hora_fin);
                      });
                })
                ->exists();
                
            if ($conflicto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El docente ya tiene una carga horaria en ese horario'
                ], 422);
            }
        }
        
        $cargaHorarium->update([
            'docente_id' => $request->docente_id,
            'curso_id' => $request->curso_id,
            'aula_id' => $request->aula_id,
            'horas_semanales' => $request->horas_semanales,
            'dia_semana' => $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'estado' => $request->has('estado') ? 'activo' : 'inactivo',
            'observaciones' => $request->observaciones,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Carga horaria actualizada exitosamente',
            'data' => $cargaHorarium->load(['docente', 'curso', 'aula'])
        ]);
    }
    
    public function destroy(CargaHoraria $cargaHorarium)
    {
        try {
            $cargaHorarium->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Carga horaria eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function toggleActive(CargaHoraria $cargaHorarium)
    {
        $cargaHorarium->update(['estado' => $cargaHorarium->estado === 'activo' ? 'inactivo' : 'activo']);
        
        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'estado' => $cargaHorarium->estado
        ]);
    }

    /**
     * Asignar un aula al docente sin especificar curso (curso_id = null).
     * Si ya existe una asignación para el docente y el aula con curso_id null, devuelve la existente.
     */
    public function assignAulaOnly(Request $request)
    {
        $request->validate([
            'aula_id' => 'required|exists:aulas,id',
        ]);

        $docenteId = auth()->id();
        $aulaId = $request->aula_id;

        $existing = CargaHoraria::where('docente_id', $docenteId)
            ->where('aula_id', $aulaId)
            ->whereNull('curso_id')
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'message' => 'Asignación existente encontrada', 'data' => $existing]);
        }

        $carga = CargaHoraria::create([
            'docente_id' => $docenteId,
            'curso_id' => null,
            'aula_id' => $aulaId,
            'horas_semanales' => null,
            'dia_semana' => null,
            'hora_inicio' => null,
            'hora_fin' => null,
            'estado' => 'activo',
            'observaciones' => 'Asignación automática desde Registro de Competencias (sin curso)'
        ]);

        return response()->json(['success' => true, 'message' => 'Asignación creada', 'data' => $carga]);
    }

    /**
     * Remover la asignación creada sin curso (curso_id = null) para el docente y aula.
     * Se puede pasar `carga_id` o `aula_id`.
     */
    public function removeAulaOnly(Request $request)
    {
        $docenteId = auth()->id();

        if ($request->filled('carga_id')) {
            $carga = CargaHoraria::where('id', $request->carga_id)->where('docente_id', $docenteId)->first();
            if (!$carga) {
                return response()->json(['success' => false, 'message' => 'Asignación no encontrada'], 404);
            }
            // Only delete if it has curso_id null
            if (!is_null($carga->curso_id)) {
                return response()->json(['success' => false, 'message' => 'Esta asignación tiene curso asociado, no se puede eliminar con este endpoint'], 422);
            }
            $carga->delete();
            return response()->json(['success' => true, 'message' => 'Asignación eliminada']);
        }

        $request->validate(['aula_id' => 'required|exists:aulas,id']);
        $aulaId = $request->aula_id;

        $carga = CargaHoraria::where('docente_id', $docenteId)
            ->where('aula_id', $aulaId)
            ->whereNull('curso_id')
            ->first();

        if (!$carga) {
            return response()->json(['success' => false, 'message' => 'Asignación no encontrada'], 404);
        }

        $carga->delete();

        return response()->json(['success' => true, 'message' => 'Asignación eliminada']);
    }
    
    // app/Http/Controllers/CargaHorariaController.php

    public function getCursosByDocente(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id'
        ]);
        
        $asignaciones = CargaHoraria::where('docente_id', $request->docente_id)
            ->where('estado', 'activo')
            ->with(['curso' => function($q) {
                $q->with('nivel');
            }, 'aula'])
            ->get();
        
        $cursosAsignados = $asignaciones->filter(function ($carga) {
            return !is_null($carga->curso_id);
        })->values();

        $aulasSinCurso = $asignaciones->filter(function ($carga) {
            return is_null($carga->curso_id) && !is_null($carga->aula_id);
        })->values();
        
        // Mapear asegurando que todas las propiedades existan
        $resultado = $cursosAsignados->map(function($carga) {
            return [
                // id: curso id (mantener compatibilidad con lo ya esperado en el front)
                'id' => $carga->curso->id ?? null,
                // carga_id: id de la asignación (CargaHoraria) — necesario para eliminar
                'carga_id' => $carga->id ?? null,
                'nombre' => $carga->curso->nombre ?? 'Curso no disponible',
                'nivel' => $carga->curso->nivel->nombre ?? 'Sin nivel',
                'aula' => $carga->aula->nombre ?? 'Aula no asignada',
                'aula_id' => $carga->aula_id ?? null,
                'nivel_id' => $carga->curso->nivel_id ?? null,
                   'seccion' => $carga->aula->seccion->nombre ?? null,
                'horas_semanales' => $carga->horas_semanales ?? 0,
                'dia_semana' => $carga->dia_semana_nombre ?? null,
                'horario' => $carga->horario ?? null
            ];
        });
        
        // Depuración: Log para ver qué se está enviando
        \Log::info('Cursos asignados al docente', [
            'docente_id' => $request->docente_id,
            'cantidad' => $resultado->count(),
            'data' => $resultado->toArray()
        ]);
        
        return response()->json([
            'cursos' => $resultado,
            'aulas_sin_curso' => $aulasSinCurso->map(function ($carga) {
                return [
                    'id' => $carga->id,
                    'carga_id' => $carga->id,
                    'aula_id' => $carga->aula_id,
                    'aula' => $carga->aula->nombre ?? 'Aula no asignada',
                    'nivel' => $carga->aula?->grado?->nivel?->nombre ?? 'Sin nivel',
                    'seccion' => $carga->aula?->seccion?->nombre ?? null,
                ];
            })->values(),
        ]);
    }
    
    public function getAulasByCurso(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id'
        ]);
        
        $curso = Curso::find($request->curso_id);
        
        if (!$curso) {
            return response()->json([]);
        }
        
        $aulas = Aula::with(['grado.nivel', 'seccion'])
            ->where('nivel_id', $curso->nivel_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        
        $result = $aulas->map(function($aula) {
            return [
                'id' => $aula->id,
                'nombre' => $aula->nombre,
                'nivel_id' => $aula->nivel_id,
                'nivel_nombre' => $aula->grado?->nivel?->nombre ?? null,
                'grado' => $aula->grado ? $aula->grado->nombre : null,
                'seccion' => $aula->seccion ? $aula->seccion->nombre : null,
                'turno_nombre' => $aula->turno_nombre,
                'turno' => $aula->turno
            ];
        });
        
        return response()->json($result);
    }

    public function getAulasDisponibles()
    {
        $aulas = Aula::with(['grado.nivel', 'seccion'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $result = $aulas->map(function($aula) {
            return [
                'id' => $aula->id,
                'nombre' => $aula->nombre,
                'nivel_id' => $aula->nivel_id,
                'nivel_nombre' => $aula->grado?->nivel?->nombre ?? null,
                'grado' => $aula->grado ? $aula->grado->nombre : null,
                'seccion' => $aula->seccion ? $aula->seccion->nombre : null,
                'turno_nombre' => $aula->turno_nombre,
                'turno' => $aula->turno
            ];
        });

        return response()->json($result);
    }

    public function verificarDuplicado(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'aula_id' => 'required|exists:aulas,id',
            'curso_ids' => 'sometimes|array',
            'curso_ids.*' => 'integer|exists:cursos,id',
            'curso_id' => 'sometimes|integer|exists:cursos,id'
        ]);

        $cursoIds = [];
        if ($request->has('curso_ids')) {
            $cursoIds = $request->curso_ids;
        } elseif ($request->filled('curso_id')) {
            $cursoIds = [$request->curso_id];
        }

        $conflictos = [];
        if (!empty($cursoIds)) {
            $cargas = CargaHoraria::whereIn('curso_id', $cursoIds)
                ->where('aula_id', $request->aula_id)
                ->where('estado', 'activo')
                ->where('docente_id', '!=', $request->docente_id)
                ->whereHas('aula', function ($q) {
                    $anioActivo = AnioAcademico::where('activo', true)->first();
                    if ($anioActivo) {
                        $q->where('anio_academico_id', $anioActivo->id);
                    }
                })
                ->with('docente')
                ->get();

            $conflictos = $cargas->map(function ($carga) {
                return [
                    'curso_id' => $carga->curso_id,
                    'docente_id' => $carga->docente_id,
                    'docente_nombre' => $carga->docente->name ?? 'Otro docente'
                ];
            })->toArray();
        }

        return response()->json([
            'conflictos' => $conflictos,
            'existe' => count($conflictos) > 0
        ]);
    }

    public function getAllCursos()
    {
        $cursos = Curso::with('nivel')
            ->where('activo', true)
            ->ordered()
            ->get()
            ->map(function($curso) {
                return [
                    'id' => $curso->id,
                    'nivel_id' => $curso->nivel_id,
                    'nombre' => $curso->nombre,
                    'nivel' => $curso->nivel->nombre ?? 'Sin nivel',
                    'codigo' => $curso->codigo
                ];
            });
        
        return response()->json($cursos);
    }

    private function conflictoCursoMismoAulaEnPeriodoActivo($cursoId, $aulaId, $docenteId)
    {
        $aula = Aula::find($aulaId);
        if (!$aula) {
            return false;
        }

        $anioActivo = AnioAcademico::where('activo', true)->first();
        $periodoActivo = Periodo::where('activo', true)
            ->when($anioActivo, fn($query) => $query->where('anio_academico_id', $anioActivo->id))
            ->first();

        // Si no hay aula o año activo, usamos la comparación básica por aula y curso.
        $query = CargaHoraria::where('curso_id', $cursoId)
            ->where('aula_id', $aulaId)
            ->where('estado', 'activo')
            ->where('docente_id', '!=', $docenteId);

        // Asegurar que la aula pertenezca al año activo cuando exista.
        if ($anioActivo) {
            $query->whereHas('aula', fn($q) => $q->where('anio_academico_id', $anioActivo->id));
        }

        // Si hay periodo activo, asumimos que la validación se aplica al periodo actual.
        // Dado que CargaHoraria no almacena periodo explícito, se usa el hecho de que
        // el curso no puede repetirse en la misma aula dentro de la misma aula/año activo.

        return $query->exists();
    }

    public function guardarMultiplesAsignaciones(Request $request)
    {
        try {
            $asignacionesJson = $request->input('asignaciones');
            $asignaciones = json_decode($asignacionesJson, true);
            
            if (!is_array($asignaciones) || empty($asignaciones)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay asignaciones para guardar'
                ], 422);
            }
            
            $exitosas = 0;
            $errores = [];
            
            foreach ($asignaciones as $index => $datos) {
                try {
                    // Validar datos
                    if (!isset($datos['docente_id']) || !isset($datos['curso_id']) || !isset($datos['aula_id'])) {
                        $errores[] = "Asignación " . ($index + 1) . ": Datos incompletos";
                        continue;
                    }
                    
                    $curso = Curso::with('nivel')->find($datos['curso_id']);
                    $aula = Aula::with('nivel')->find($datos['aula_id']);

                    if (!$this->puedeAsignarCursoEnAula($curso, $aula)) {
                        $errores[] = 'Asignación ' . ($index + 1) . ': no se puede asignar un curso de ' . ($curso?->nivel?->nombre ?? 'un nivel diferente') . ' en un aula de ' . ($aula?->nivel?->nombre ?? 'un nivel diferente') . '.';
                        continue;
                    }

                    // Verificar duplicado
                    $existeAsignacion = CargaHoraria::where('docente_id', $datos['docente_id'])
                        ->where('curso_id', $datos['curso_id'])
                        ->where('aula_id', $datos['aula_id'])
                        ->exists();
                    
                    if ($existeAsignacion) {
                        $errores[] = "Asignación " . ($index + 1) . ": Ya existe esta combinación";
                        continue;
                    }

                        $conflictoAula = $this->conflictoCursoMismoAulaEnPeriodoActivo($datos['curso_id'], $datos['aula_id'], $datos['docente_id']);
                        if ($conflictoAula) {
                            $errores[] = "Asignación " . ($index + 1) . ": Este curso ya está asignado a otro docente en el mismo aula para el año escolar activo y periodo actual.";
                            continue;
                        }
                    
                    // Normalizar valores: convertir cadenas vacías a NULL y forzar tipos
                    $horasSem = isset($datos['horas_semanales']) && is_numeric($datos['horas_semanales']) ? (int) $datos['horas_semanales'] : 4;
                    $diaSemana = isset($datos['dia_semana']) && trim($datos['dia_semana']) !== '' ? $datos['dia_semana'] : null;
                    $horaInicio = isset($datos['hora_inicio']) && trim($datos['hora_inicio']) !== '' ? $datos['hora_inicio'] : null;
                    $horaFin = isset($datos['hora_fin']) && trim($datos['hora_fin']) !== '' ? $datos['hora_fin'] : null;

                    // Crear asignación con valores normalizados
                    CargaHoraria::create([
                        'docente_id' => $datos['docente_id'],
                        'curso_id' => $datos['curso_id'],
                        'aula_id' => $datos['aula_id'],
                        'horas_semanales' => $horasSem,
                        'dia_semana' => $diaSemana,
                        'hora_inicio' => $horaInicio,
                        'hora_fin' => $horaFin,
                        'estado' => 'activo',
                        'observaciones' => $datos['observaciones'] ?? null,
                    ]);
                    
                    $exitosas++;
                } catch (\Exception $e) {
                    $errores[] = "Asignación " . ($index + 1) . ": " . $e->getMessage();
                }
            }
            
            $mensaje = "$exitosas asignación(es) guardada(s) exitosamente";
            if (!empty($errores)) {
                $mensaje .= ". Errores: " . implode("; ", $errores);
            }
            
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'exitosas' => $exitosas,
                'errores' => $errores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar las asignaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    private function puedeAsignarCursoEnAula(?Curso $curso, ?Aula $aula): bool
    {
        if (!$curso || !$aula) {
            return false;
        }

        return (int) $curso->nivel_id === (int) $aula->nivel_id;
    }
}