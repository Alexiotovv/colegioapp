<?php
// app/Http/Controllers/RegistroAsistenciaController.php

namespace App\Http\Controllers;

use App\Models\RegistroAsistencia;
use App\Models\CargaHoraria;
use App\Models\Aula;
use App\Models\TipoInasistencia;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroAsistenciaController extends Controller
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
            // Docente: solo sus aulas asignadas por carga horaria
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
        
        // Obtener todos los tipos de inasistencia activos
        $tiposInasistencia = TipoInasistencia::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('registro-asistencias.index', compact('aulas', 'tiposInasistencia', 'periodos', 'anioActivo'));
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
            $tieneAcceso = CargaHoraria::where('aula_id', $aulaId)
                ->where('docente_id', $docenteId)
                ->where('estado', CargaHoraria::ESTADO_ACTIVO)
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
        
        // Determinar el nivel del aula y obtener solo los tipos aplicables a ese nivel
        $aula = Aula::with('grado.nivel')->find($aulaId);
        $nivelId = null;
        if ($aula && $aula->grado && $aula->grado->nivel) {
            $nivelId = $aula->grado->nivel->id;
        }

        $tiposInasistencia = TipoInasistencia::with('nivel')
            ->where('activo', true)
            ->when($nivelId !== null, function ($query) use ($nivelId) {
                // incluir tipos que sean globales (nivel_id null) o que pertenezcan al nivel del aula
                $query->where(function ($q) use ($nivelId) {
                    $q->whereNull('nivel_id')->orWhere('nivel_id', $nivelId);
                });
            })
            ->orderBy('orden')
            ->get();
        
        // Obtener registros existentes
        $matriculaIds = $matriculas->pluck('id')->toArray();
        $tipoIds = $tiposInasistencia->pluck('id')->toArray();
        
        $registros = RegistroAsistencia::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('tipo_inasistencia_id', $tipoIds)
            ->get()
            ->groupBy('matricula_id')
            ->map(function($items) {
                return $items->keyBy('tipo_inasistencia_id');
            });
        
        $periodo = Periodo::find($periodoId);
        $registrosHabilitados = $periodo ? $periodo->activo : false;
        
        return response()->json([
            'matriculas' => $matriculas,
            'tipos_inasistencia' => $tiposInasistencia,
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
                'message' => 'El periodo no está habilitado para registrar asistencias.'
            ], 422);
        }
        
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            foreach ($request->registros as $item) {
                $cantidad = intval($item['cantidad']);
                
                RegistroAsistencia::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'tipo_inasistencia_id' => $item['tipo_inasistencia_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'cantidad' => $cantidad,
                        'observacion' => $item['observacion'] ?? null,
                        'fecha_registro' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Registros guardados exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los registros: ' . $e->getMessage()
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
            'message' => $periodo->activo ? 'Registro de asistencias habilitado' : 'Registro de asistencias deshabilitado',
            'habilitado' => $periodo->activo
        ]);
    }
}