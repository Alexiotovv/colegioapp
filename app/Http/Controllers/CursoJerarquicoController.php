<?php
// app/Http/Controllers/CursoJerarquicoController.php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Nivel;
use App\Models\AnioAcademico;
use App\Models\Curso;
use App\Models\Competencia;
use App\Models\Capacidad;
use Illuminate\Http\Request;

class CursoJerarquicoController extends Controller
{
    public function index()
    {
        $anios = AnioAcademico::orderBy('anio', 'desc')->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        $aulasPorNivel = Aula::with('nivel')
            ->where('activo', true)
            ->orderBy('nivel_id')
            ->orderBy('nombre')
            ->get()
            ->groupBy('nivel_id');
        
        // Obtener datos jerárquicos
        $data = $this->getHierarchyData($anioActivo ? $anioActivo->id : null);
        
        return view('cursos-jerarquico.index', compact('anios', 'anioActivo', 'niveles', 'aulasPorNivel', 'data'));
    }
    
    public function getHierarchyData($anioId = null)
    {
        if (!$anioId) {
            $anioActivo = AnioAcademico::where('activo', true)->first();
            $anioId = $anioActivo ? $anioActivo->id : null;
        }
        
        if (!$anioId) {
            return [];
        }
        
        $niveles = Nivel::where('activo', true)->with(['cursos' => function($query) use ($anioId) {
            $query->where('anio_academico_id', $anioId)
                  ->where('activo', true)
                  ->orderBy('orden');
        }])->orderBy('orden')->get();
        
        foreach ($niveles as $nivel) {
            foreach ($nivel->cursos as $curso) {
                $curso->competencias = Competencia::where('curso_id', $curso->id)
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->get();
                    
                foreach ($curso->competencias as $competencia) {
                    $competencia->capacidades = Capacidad::where('competencia_id', $competencia->id)
                        ->where('activo', true)
                        ->orderBy('orden')
                        ->get();
                }
            }
        }
        
        return $niveles;
    }
    
    public function storeCurso(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'required|string|max:20|unique:cursos,codigo',
            'tipo' => 'required|in:AREA,TALLER,TUTORIA',
            'nivel_id' => 'required|exists:niveles,id',
            'anio_academico_id' => 'required|exists:anio_academicos,id',
            'horas_semanales' => 'nullable|integer|min:0|max:40',
            'aulas_excluidas' => 'nullable|array',
            'aulas_excluidas.*' => 'integer|exists:aulas,id',
        ]);
        
        $curso = Curso::create([
            'codigo' => strtoupper($request->codigo),
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'horas_semanales' => $request->horas_semanales ?? 0,
            'orden' => $request->orden ?? 0,
            'descripcion' => $request->descripcion,
            'activo' => true,
            'nivel_id' => $request->nivel_id,
            'anio_academico_id' => $request->anio_academico_id,
        ]);

        $this->syncAulasExcluidas($curso, $request);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'curso' => $curso->load('aulasExcluidas'), 'message' => 'Curso creado exitosamente']);
        }
        
        return redirect()->back()->with('success', 'Curso creado exitosamente');
    }
    
    public function storeCompetencia(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'nombre' => 'required|string|max:250',
            'ponderacion' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $competencia = Competencia::create([
            'curso_id' => $request->curso_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ponderacion' => $request->ponderacion ?? 100,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'competencia' => $competencia, 'message' => 'Competencia creada exitosamente']);
        }
        
        return redirect()->back()->with('success', 'Competencia creada exitosamente');
    }
    
    public function storeCapacidad(Request $request)
    {
        $request->validate([
            'competencia_id' => 'required|exists:competencias,id',
            'nombre' => 'required|string|max:250',
            'ponderacion' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $capacidad = Capacidad::create([
            'competencia_id' => $request->competencia_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ponderacion' => $request->ponderacion ?? 100,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'capacidad' => $capacidad, 'message' => 'Capacidad creada exitosamente']);
        }
        
        return redirect()->back()->with('success', 'Capacidad creada exitosamente');
    }
    
    public function updateCurso(Request $request, Curso $curso)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'required|string|max:20|unique:cursos,codigo,' . $curso->id,
            'tipo' => 'required|in:AREA,TALLER,TUTORIA',
            'horas_semanales' => 'nullable|integer|min:0|max:40',
            'aulas_excluidas' => 'nullable|array',
            'aulas_excluidas.*' => 'integer|exists:aulas,id',
        ]);
        
        $curso->update([
            'codigo' => strtoupper($request->codigo),
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'horas_semanales' => $request->horas_semanales ?? 0,
            'orden' => $request->orden ?? 0,
            'descripcion' => $request->descripcion,
        ]);

        $this->syncAulasExcluidas($curso, $request);
        
        return response()->json(['success' => true, 'message' => 'Curso actualizado exitosamente']);
    }
    
    public function updateCompetencia(Request $request, Competencia $competencia)
    {
        $request->validate([
            'nombre' => 'required|string|max:250',
            'ponderacion' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $competencia->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ponderacion' => $request->ponderacion ?? 100,
            'orden' => $request->orden ?? 0,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Competencia actualizada exitosamente']);
    }
    
    public function updateCapacidad(Request $request, Capacidad $capacidad)
    {
        $request->validate([
            'nombre' => 'required|string|max:250',
            'ponderacion' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $capacidad->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ponderacion' => $request->ponderacion ?? 100,
            'orden' => $request->orden ?? 0,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Capacidad actualizada exitosamente']);
    }
    
    public function destroyCurso(Curso $curso)
    {
        if ($curso->competencias()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar el curso porque tiene competencias asociadas'], 422);
        }
        
        $curso->delete();
        return response()->json(['success' => true, 'message' => 'Curso eliminado exitosamente']);
    }
    
    public function destroyCompetencia(Competencia $competencia)
    {
        if ($competencia->capacidades()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar la competencia porque tiene capacidades asociadas'], 422);
        }
        
        $competencia->delete();
        return response()->json(['success' => true, 'message' => 'Competencia eliminada exitosamente']);
    }
    
    public function destroyCapacidad(Capacidad $capacidad)
    {
        $capacidad->delete();
        return response()->json(['success' => true, 'message' => 'Capacidad eliminada exitosamente']);
    }
    
    public function changeYear(Request $request)
    {
        $anioId = $request->anio_id;
        $data = $this->getHierarchyData($anioId);
        
        // Renderizar la vista parcial
        $html = view('cursos-jerarquico.partials.tree', ['niveles' => $data])->render();
        
        return response()->json([
            'success' => true, 
            'html' => $html,
            'data' => $data
        ]);
    }

    public function getCurso(Curso $curso)
    {
        return response()->json($curso->load('aulasExcluidas'));
    }

    public function getCompetencia(Competencia $competencia)
    {
        return response()->json($competencia);
    }

    public function getCapacidad(Capacidad $capacidad)
    {
        return response()->json($capacidad);
    }

    private function syncAulasExcluidas(Curso $curso, Request $request): void
    {
        if ($request->has('aulas_excluidas_present')) {
            $curso->aulasExcluidas()->sync(array_filter((array) $request->input('aulas_excluidas', [])));
        }
    }



    

}