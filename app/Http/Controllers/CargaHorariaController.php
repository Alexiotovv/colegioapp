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
        
        $cargas = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        // 🔥 Obtener docentes usando role_id
        $rolDocenteId = DB::table('roles')->where('nombre', 'docente')->value('id');
        $docentes = User::where('role_id', $rolDocenteId)
                        ->where('activo', true)
                        ->orderBy('name')
                        ->get();
        
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('carga-horaria.index', compact('cargas', 'docentes', 'cursos', 'anioActivo'));
    }

    public function create()
    {
        // 🔥 Obtener docentes de la tabla users con rol = 'docente'
        $rolDocenteId = DB::table('roles')->where('nombre', 'docente')->value('id');

        $docentes = User::where('role_id', $rolDocenteId)
                        ->where('activo', true)
                        ->orderBy('name')
                        ->get();
        
        // Depuración: ver cuántos docentes se encontraron
        \Log::info('Docentes encontrados: ' . $docentes->count());
        
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();
        $diasSemana = CargaHoraria::DIAS_SEMANA;
        
        return view('carga-horaria.create', compact('docentes', 'cursos', 'aulas', 'diasSemana'));
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
        
        // Verificar conflicto de horario para el mismo docente
        if ($request->dia_semana && $request->hora_inicio && $request->hora_fin) {
            $conflicto = CargaHoraria::where('docente_id', $request->docente_id)
                ->where('dia_semana', $request->dia_semana)
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
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Carga horaria asignada exitosamente',
                'data' => $carga->load(['docente', 'curso', 'aula'])
            ]);
        }
        
        return redirect()->route('admin.carga-horaria.index')
            ->with('success', 'Carga horaria asignada exitosamente');
    }
    
    public function edit(CargaHoraria $cargaHorarium)
    {
        // 🔥 Obtener docentes usando role_id
        $rolDocenteId = DB::table('roles')->where('nombre', 'docente')->value('id');
        $docentes = User::where('role_id', $rolDocenteId)
                        ->where('activo', true)
                        ->orderBy('name')
                        ->get();
        
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();
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
        
        // Verificar conflicto excluyendo el registro actual
        if ($request->dia_semana && $request->hora_inicio && $request->hora_fin) {
            $conflicto = CargaHoraria::where('docente_id', $request->docente_id)
                ->where('dia_semana', $request->dia_semana)
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
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Carga horaria actualizada exitosamente',
                'data' => $cargaHorarium->load(['docente', 'curso', 'aula'])
            ]);
        }
        
        return redirect()->route('admin.carga-horaria.index')
            ->with('success', 'Carga horaria actualizada exitosamente');
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
    
    // Métodos para AJAX
    public function getCursosByDocente(Request $request)
    {
        $cursos = Curso::with('nivel')
            ->where('activo', true)
            ->ordered()
            ->get();
        
        return response()->json($cursos);
    }
    
    public function getAulasByCurso(Request $request)
    {
        $curso = Curso::find($request->curso_id);
        
        if (!$curso) {
            return response()->json([]);
        }
        
        // Buscar aulas que tengan el mismo nivel que el curso
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
            ->where('nivel_id', $curso->nivel_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'grado_id', 'seccion_id', 'turno']);
        
        // Formatear respuesta
        $result = $aulas->map(function($aula) {
            return [
                'id' => $aula->id,
                'nombre' => $aula->nombre,
                'grado' => $aula->grado ? ['nombre' => $aula->grado->nombre] : null,
                'seccion' => $aula->seccion ? ['nombre' => $aula->seccion->nombre] : null,
                'turno_nombre' => $aula->turno_nombre,
                'turno' => $aula->turno
            ];
        });
        
        return response()->json($result);
    }
}