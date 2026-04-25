<?php
// app/Http/Controllers/RegistroOtraEvaluacionController.php

namespace App\Http\Controllers;

use App\Models\RegistroOtraEvaluacion;
use App\Models\Aula;
use App\Models\TipoOtraEvaluacion;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroOtraEvaluacionController extends Controller
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
        
        // Obtener todos los tipos de otras evaluaciones activos
        $tiposEvaluacion = TipoOtraEvaluacion::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('registro-otras-evaluaciones.index', compact('aulas', 'tiposEvaluacion', 'periodos', 'anioActivo'));
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
        
        // Obtener alumnos matriculados en el aula (ordenados alfabéticamente)
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        // Obtener todos los tipos de otras evaluaciones activos
        $tiposEvaluacion = TipoOtraEvaluacion::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        // Obtener registros existentes
        $matriculaIds = $matriculas->pluck('id')->toArray();
        $tipoIds = $tiposEvaluacion->pluck('id')->toArray();
        
        $registros = RegistroOtraEvaluacion::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('tipo_otra_evaluacion_id', $tipoIds)
            ->get()
            ->groupBy('matricula_id')
            ->map(function($items) {
                return $items->keyBy('tipo_otra_evaluacion_id');
            });
        
        $periodo = Periodo::find($periodoId);
        $registrosHabilitados = $periodo ? $periodo->activo : false;
        
        // Obtener configuraciones de cada tipo
        $configuraciones = [];
        foreach ($tiposEvaluacion as $tipo) {
            if ($tipo->tipo_dato === 'NUMERICO') {
                $configuraciones[$tipo->id] = [
                    'tipo' => 'NUMERICO',
                    'min' => $tipo->min_valor,
                    'max' => $tipo->max_valor,
                ];
            } else {
                $configuraciones[$tipo->id] = [
                    'tipo' => 'LITERAL',
                    'opciones' => $tipo->opciones_literales,
                ];
            }
        }
        
        return response()->json([
            'matriculas' => $matriculas,
            'tipos_evaluacion' => $tiposEvaluacion,
            'registros' => $registros,
            'registros_habilitados' => $registrosHabilitados,
            'configuraciones' => $configuraciones,
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
                'message' => 'El periodo no está habilitado para registrar evaluaciones.'
            ], 422);
        }
        
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            foreach ($request->registros as $item) {
                RegistroOtraEvaluacion::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'tipo_otra_evaluacion_id' => $item['tipo_otra_evaluacion_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'valor' => $item['valor'],
                        'observacion' => $item['observacion'] ?? null,
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