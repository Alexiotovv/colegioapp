<?php
// app/Http/Controllers/NotaController.php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\Aula;
use App\Models\Curso;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\Capacidad;
use App\Models\CargaHoraria;
use App\Models\AnioAcademico;
use App\Models\Competencia;
use App\Models\ConclusionDescriptiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ModuloRegistro;//Probablemente se vaya

class NotaController extends Controller
{
    
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Si es admin, ver todas las aulas
        // Si es docente, solo ver sus aulas asignadas por carga horaria
        if ($rol === 'admin') {
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        } else {
            // Docente: solo las aulas donde tiene carga horaria
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->whereHas('cargaHoraria', function($query) use ($docenteId) {
                    $query->where('docente_id', $docenteId)
                        ->where('estado', 'activo');
                })
                ->where('activo', true)
                ->distinct()
                ->orderBy('nombre')
                ->get();
        }
        
        $periodos = Periodo::with('anioAcademico')
                        ->orderBy('orden')
                        ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('notas.index', compact('aulas', 'periodos', 'anioActivo'));
    }
    
    public function getCursosByAula(Request $request)
    {
        $aulaId = $request->aula_id;
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Si es admin, ver todos los cursos del aula
        if ($rol === 'admin') {
            $cursos = Curso::with('nivel')
                ->where('activo', true)
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
        
        // Obtener cursos asignados al docente para esta aula
        $cursos = CargaHoraria::with(['curso.nivel'])
            ->where('docente_id', $docenteId)
            ->where('aula_id', $aulaId)
            ->where('estado', 'activo')
            ->get()
            ->pluck('curso')
            ->unique('id')
            ->values();
        
        return response()->json($cursos);
    }
    
    public function getDataForNotas(Request $request)
    {
        $aulaId = $request->aula_id;
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
        ->where('estado', 'activa')
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
        
        $notas = Nota::where('periodo_id', $periodoId)
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
        
        $periodo = Periodo::find($periodoId);
        $notasHabilitadas = $periodo ? $periodo->activo : false;
        
        return response()->json([
            'matriculas' => $matriculas,
            'competencias' => $competencias,
            'notas' => $notas,
            'notas_habilitadas' => $notasHabilitadas,
        ]);
    }
    
    // Guardar o actualizar notas
    public function saveNotas(Request $request)
    {
        $request->validate([
            'notas' => 'required|array',
            'periodo_id' => 'required|exists:periodos,id',
        ]);
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar notas.'
            ], 422);
        }
        
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            foreach ($request->notas as $item) {
                // 🔥 Usar updateOrCreate con los tres campos clave
                Nota::updateOrCreate(
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
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Notas guardadas exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las notas: ' . $e->getMessage()
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
        
        $periodo = Periodo::find($request->periodo_id);
        
        if (!$periodo) {
            return response()->json([
                'success' => false,
                'message' => 'Periodo no encontrado'
            ], 404);
        }
        
        $periodo->update(['activo' => !$periodo->activo]);
        
        return response()->json([
            'success' => true,
            'message' => $periodo->activo ? 'Registro de notas habilitado' : 'Registro de notas deshabilitado',
            'habilitado' => $periodo->activo
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

    public function getConclusion(Nota $nota)
    {
        $conclusion = $nota->conclusionDescriptiva;
        
        return response()->json([
            'success' => true,
            'nota_id' => $nota->id,
            'conclusion' => $conclusion ? $conclusion->conclusion : '',
            'alumno_nombre' => $nota->matricula->alumno->nombre_completo,
            'competencia_nombre' => $nota->competencia->nombre,
            'nota_valor' => $nota->nota
        ]);
    }

    public function saveConclusion(Request $request)
    {
        $request->validate([
            'nota_id' => 'required|exists:notas,id',
            'conclusion' => 'required|string|max:500',
        ]);
        
        $nota = Nota::find($request->nota_id);
        
        if (!$nota) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada'
            ], 404);
        }
        
        // Actualizar o crear la conclusión
        $conclusion = ConclusionDescriptiva::updateOrCreate(
            ['nota_id' => $request->nota_id],
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
        $modulo = ModuloRegistro::where('codigo', 'notas')->first();
        
        if (!$modulo) {
            return response()->json(['AD', 'A', 'B', 'C', 'CND', 'EXO']);
        }
        
        $tiposNotas = $modulo->getTiposNotasOptions();
        $opciones = $tiposNotas->pluck('codigo')->toArray();
        
        return response()->json($opciones);
    }


}