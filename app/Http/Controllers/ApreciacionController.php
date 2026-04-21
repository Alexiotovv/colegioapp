<?php
// app/Http/Controllers/ApreciacionController.php

namespace App\Http\Controllers;

use App\Models\Apreciacion;
use App\Models\Aula;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\CargaHoraria;
use App\Models\AnioAcademico;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApreciacionController extends Controller
{
    public function index()
    {
        // Verificar si el usuario tiene rol tutor o admin
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        if (!in_array($rol, ['admin', 'tutor'])) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
        
        $docenteId = auth()->id();
        
        // Si es tutor, solo ver sus aulas asignadas (campo docente_id en aulas)
        // Si es admin, puede ver todas las aulas
        if ($rol === 'tutor') {
            // 🔥 CORREGIDO: Usar el campo docente_id de la tabla aulas
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->where('docente_id', $docenteId)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        } else {
            // Admin puede ver todas las aulas
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        }
        
        $periodos = Periodo::with('anioAcademico')
                        ->orderBy('orden')
                        ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        $maxCaracteres = Configuracion::getValor('apreciaciones_caracteres_max', 255);
        
        return view('apreciaciones.index', compact('aulas', 'periodos', 'anioActivo', 'maxCaracteres'));
    }
    
    public function getDataForApreciaciones(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        // Verificar permisos
        if (!in_array($rol, ['admin', 'tutor'])) {
            return response()->json(['error' => 'No tienes permisos'], 403);
        }
        
        // Si es tutor, verificar que el aula le pertenece (campo docente_id)
        if ($rol === 'tutor') {
            $tieneAcceso = Aula::where('id', $aulaId)
                ->where('docente_id', $user->id)
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
        
        // Obtener apreciaciones existentes
        $matriculaIds = $matriculas->pluck('id')->toArray();
        
        $apreciaciones = Apreciacion::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->get()
            ->keyBy('matricula_id');
        
        $periodo = Periodo::find($periodoId);
        $apreciacionesHabilitadas = $periodo ? $periodo->activo : false;
        
        $maxCaracteres = Configuracion::getValor('apreciaciones_caracteres_max', 255);
        
        return response()->json([
            'matriculas' => $matriculas,
            'apreciaciones' => $apreciaciones,
            'apreciaciones_habilitadas' => $apreciacionesHabilitadas,
            'max_caracteres' => $maxCaracteres
        ]);
    }
    
    // Guardar o actualizar apreciaciones
    public function saveApreciaciones(Request $request)
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        if (!in_array($rol, ['admin', 'tutor'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }
        
        $request->validate([
            'apreciaciones' => 'required|array',
            'periodo_id' => 'required|exists:periodos,id',
        ]);
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar apreciaciones.'
            ], 422);
        }
        
        $maxCaracteres = Configuracion::getValor('apreciaciones_caracteres_max', 255);
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            foreach ($request->apreciaciones as $item) {
                // Validar longitud de la apreciación
                if (strlen($item['apreciacion']) > $maxCaracteres) {
                    throw new \Exception("La apreciación excede el máximo de {$maxCaracteres} caracteres");
                }
                
                Apreciacion::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'apreciacion' => $item['apreciacion'],
                        'fecha_registro' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Apreciaciones guardadas exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las apreciaciones: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Habilitar/Deshabilitar registro de apreciaciones (solo ADMIN)
    public function toggleHabilitacion(Request $request)
    {

        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        // Solo admin puede habilitar/deshabilitar
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
            'message' => $periodo->activo ? 'Registro de apreciaciones habilitado' : 'Registro de apreciaciones deshabilitado',
            'habilitado' => $periodo->activo
        ]);
    }
}