<?php
// app/Http/Controllers/CargaHorariaController.php

namespace App\Http\Controllers;

use App\Models\CargaHoraria;
use App\Models\User;
use App\Models\Curso;
use App\Models\Aula;
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
        
        // Obtener docentes usando role_id
        $rolDocenteId = DB::table('roles')->where('nombre', 'docente')->value('id');
        $docentes = User::where('role_id', $rolDocenteId)
                        ->where('activo', true)
                        ->orderBy('name')
                        ->get(['id', 'name', 'email']);
        
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        $aulas = Aula::with(['grado.nivel', 'seccion'])->where('activo', true)->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('carga-horaria.index', compact('cargas', 'docentes', 'cursos', 'aulas', 'anioActivo'));
    }

    public function create()
    {
        $rolDocenteId = DB::table('roles')->where('nombre', 'docente')->value('id');
        $docentes = User::where('role_id', $rolDocenteId)
                        ->where('activo', true)
                        ->orderBy('name')
                        ->get(['id', 'name', 'email']);
        
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        $diasSemana = CargaHoraria::DIAS_SEMANA;
        
        return view('carga-horaria.create', compact('docentes', 'cursos', 'diasSemana'));
    }
    
    public function store(Request $request)
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
        $rolDocenteId = DB::table('roles')->where('nombre', 'docente')->value('id');
        $docentes = User::where('role_id', $rolDocenteId)
                        ->where('activo', true)
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
        $cargaHorarium->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Carga horaria eliminada exitosamente'
        ]);
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
    
    // app/Http/Controllers/CargaHorariaController.php

    public function getCursosByDocente(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id'
        ]);
        
        $cursosAsignados = CargaHoraria::where('docente_id', $request->docente_id)
            ->where('estado', 'activo')
            ->with(['curso' => function($q) {
                $q->with('nivel');
            }, 'aula'])
            ->get();
        
        // Verificar si hay resultados
        if ($cursosAsignados->isEmpty()) {
            return response()->json([]); // Array vacío está bien
        }
        
        // Mapear asegurando que todas las propiedades existan
        $resultado = $cursosAsignados->map(function($carga) {
            return [
                'id' => $carga->curso->id ?? null,
                'nombre' => $carga->curso->nombre ?? 'Curso no disponible',
                'nivel' => $carga->curso->nivel->nombre ?? 'Sin nivel',
                'aula' => $carga->aula->nombre ?? 'Aula no asignada',
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
        
        return response()->json($resultado);
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
        $existe = CargaHoraria::where('docente_id', $request->docente_id)
            ->where('curso_id', $request->curso_id)
            ->where('aula_id', $request->aula_id)
            ->exists();
        
        return response()->json(['existe' => $existe]);
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
                    'nombre' => $curso->nombre,
                    'nivel' => $curso->nivel->nombre ?? 'Sin nivel',
                    'codigo' => $curso->codigo
                ];
            });
        
        return response()->json($cursos);
    }
}