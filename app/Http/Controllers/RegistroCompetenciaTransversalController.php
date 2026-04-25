<?php
// app/Http/Controllers/RegistroCompetenciaTransversalController.php

namespace App\Http\Controllers;

use App\Models\RegistroCompetenciaTransversal;
use App\Models\Aula;
use App\Models\CompetenciaTransversal;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ModuloRegistro;

class RegistroCompetenciaTransversalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
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
        
        // Obtener todas las competencias transversales activas
        $competencias = CompetenciaTransversal::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('registro-competencias-transversales.index', compact('aulas', 'competencias', 'periodos', 'anioActivo'));
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
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        // Obtener todas las competencias transversales activas
        $competencias = CompetenciaTransversal::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        // Obtener registros existentes
        $matriculaIds = $matriculas->pluck('id')->toArray();
        $competenciaIds = $competencias->pluck('id')->toArray();
        
        $registros = RegistroCompetenciaTransversal::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('competencia_transversal_id', $competenciaIds)
            ->get()
            ->groupBy('matricula_id')
            ->map(function($items) {
                return $items->keyBy('competencia_transversal_id');
            });
        
        $periodo = Periodo::find($periodoId);
        $registrosHabilitados = $periodo ? $periodo->activo : false;
        
        return response()->json([
            'matriculas' => $matriculas,
            'competencias' => $competencias,
            'registros' => $registros,
            'registros_habilitados' => $registrosHabilitados,
        ]);
    }
    
    public function saveRegistros(Request $request)
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        
        $request->validate([
            'registros' => 'required|array',
            'periodo_id' => 'required|exists:periodos,id',
        ]);
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar competencias transversales.'
            ], 422);
        }
        
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            $notasPermitidas = ['AD', 'A', 'B', 'C', 'CND', 'ND'];
            
            foreach ($request->registros as $item) {
                if (!in_array($item['nota'], $notasPermitidas)) {
                    throw new \Exception("Nota no válida: " . $item['nota']);
                }
                
                RegistroCompetenciaTransversal::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'competencia_transversal_id' => $item['competencia_transversal_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'nota' => $item['nota'],
                        'tipo_calificacion' => 'LITERAL',
                        // 'conclusion' => $item['conclusion'] ?? null,
                        'fecha_registro' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Competencias transversales guardadas exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las competencias: ' . $e->getMessage()
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
            'message' => $periodo->activo ? 'Registro de competencias transversales habilitado' : 'Registro de competencias transversales deshabilitado',
            'habilitado' => $periodo->activo
        ]);
    }


    public function saveConclusion(Request $request)
    {
        $request->validate([
            'matricula_id' => 'required|exists:matriculas,id',
            'competencia_id' => 'required|exists:competencias_transversales,id',
            'periodo_id' => 'required|exists:periodos,id',
            'conclusion' => 'required|string',
        ]);
        
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar conclusiones.'
            ], 422);
        }
        
        $registro = RegistroCompetenciaTransversal::updateOrCreate(
            [
                'matricula_id' => $request->matricula_id,
                'competencia_transversal_id' => $request->competencia_id,
                'periodo_id' => $request->periodo_id,
            ],
            [
                'docente_id' => auth()->id(),
                'conclusion' => $request->conclusion,
                'fecha_registro' => now(),
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Conclusión guardada exitosamente',
            'registro_id' => $registro->id
        ]);
    }

    public function getOpcionesNotas()
    {
        $modulo = ModuloRegistro::where('codigo', 'competencias_transversales')->first();
        
        if (!$modulo) {
            // Si no hay configuración, devolver opciones por defecto
            return response()->json(['AD', 'A', 'B', 'C', 'CND', 'ND']);
        }
        
        $tiposNotas = $modulo->tiposNotas()
            ->wherePivot('activo', true)
            ->orderBy('orden')
            ->get();
        
        $opciones = $tiposNotas->pluck('codigo')->toArray();
        
        return response()->json($opciones);
    }

}