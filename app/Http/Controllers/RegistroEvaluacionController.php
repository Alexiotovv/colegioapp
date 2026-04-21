<?php
// app/Http/Controllers/RegistroEvaluacionController.php

namespace App\Http\Controllers;

use App\Models\RegistroEvaluacion;
use App\Models\Aula;
use App\Models\Evaluacion;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroEvaluacionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Roles permitidos: admin, tutor, auxiliar
        if (!in_array($rol, ['admin', 'tutor', 'auxiliar'])) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
        
        // Obtener aulas según el rol
        if ($rol === 'admin') {
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        } else {
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->where('docente_id', $docenteId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        }
        
        // 🔥 Obtener todas las evaluaciones activas
        $evaluaciones = Evaluacion::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('registro-evaluaciones.index', compact('aulas', 'evaluaciones', 'periodos', 'anioActivo'));
    }
    
    public function getDataForRegistro(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Verificar permisos
        if ($rol !== 'admin') {
            $tieneAcceso = Aula::where('id', $aulaId)
                ->where('docente_id', $docenteId)
                ->where('activo', true)
                ->exists();
            
            if (!$tieneAcceso) {
                return response()->json(['error' => 'No tienes acceso a este aula'], 403);
            }
        }
        
        // Obtener alumnos matriculados en el aula
        // $matriculas = Matricula::with(['alumno'])
        //     ->where('aula_id', $aulaId)
        //     ->where('estado', 'activa')
        //     ->get();
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
        
        // 🔥 Obtener todas las evaluaciones activas
        $evaluaciones = Evaluacion::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        // Obtener registros existentes
        $matriculaIds = $matriculas->pluck('id')->toArray();
        $evaluacionIds = $evaluaciones->pluck('id')->toArray();
        
        $registros = RegistroEvaluacion::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('evaluacion_id', $evaluacionIds)
            ->get()
            ->groupBy('matricula_id')
            ->map(function($items) {
                return $items->keyBy('evaluacion_id');
            });
        
        $periodo = Periodo::find($periodoId);
        $registrosHabilitados = $periodo ? $periodo->activo : false;
        
        return response()->json([
            'matriculas' => $matriculas,
            'evaluaciones' => $evaluaciones,  // 🔥 Asegurar que se envía 'evaluaciones'
            'registros' => $registros,
            'registros_habilitados' => $registrosHabilitados,
        ]);
    }
    
    public function saveRegistros(Request $request)
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        if (!in_array($rol, ['admin', 'tutor', 'auxiliar'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }
        
        $request->validate([
            'registros' => 'required|array',
            'periodo_id' => 'required|exists:periodos,id',
        ]);
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar evaluaciones.'
            ], 422);
        }
        
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            $valoracionesPermitidas = ['SIEMPRE', 'CASI SIEMPRE', 'ALGUNAS VECES', 'NUNCA'];
            
            foreach ($request->registros as $item) {
                if (!in_array($item['valoracion'], $valoracionesPermitidas)) {
                    throw new \Exception("Valoración no válida: " . $item['valoracion']);
                }
                
                RegistroEvaluacion::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'evaluacion_id' => $item['evaluacion_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'valoracion' => $item['valoracion'],
                        'comentario' => $item['comentario'] ?? null,
                        'fecha_registro' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Evaluaciones guardadas exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las evaluaciones: ' . $e->getMessage()
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
            'message' => $periodo->activo ? 'Registro de evaluaciones habilitado' : 'Registro de evaluaciones deshabilitado',
            'habilitado' => $periodo->activo
        ]);
    }
}