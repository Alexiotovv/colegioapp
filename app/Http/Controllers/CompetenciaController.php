<?php
// app/Http/Controllers/CompetenciaController.php

namespace App\Http\Controllers;

use App\Models\Competencia;
use App\Models\Curso;
use Illuminate\Http\Request;

class CompetenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = Competencia::with('curso.grado.nivel');
        
        if ($request->filled('curso_id')) {
            $query->where('curso_id', $request->curso_id);
        }
        
        if ($request->filled('grado_id')) {
            // Filtrar por grado a través del curso
            $query->whereHas('curso', function($q) use ($request) {
                $q->where('grado_id', $request->grado_id);
            });
        }
        
        if ($request->filled('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%");
        }
        
        $competencias = $query->orderBy('orden')->paginate(15)->withQueryString();
        
        // Para los filtros
        $cursos = Curso::with('grado.nivel')->where('activo', true)->ordered()->get();
        $grados = \App\Models\Grado::with('nivel')->where('activo', true)->ordered()->get();
        
        return view('competencias.index', compact('competencias', 'cursos', 'grados'));
    }
    
    public function create()
    {
        $cursos = Curso::with('grado.nivel')->where('activo', true)->ordered()->get();
        return view('competencias.create', compact('cursos'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'nombre' => 'required|string|max:250',
            'ponderacion' => 'nullable|numeric|min:0|max:100',
            'orden' => 'nullable|integer',
        ]);
        
        Competencia::create([
            'curso_id' => $request->curso_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ponderacion' => $request->ponderacion ?? 100,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return redirect()->route('admin.competencias.index')
            ->with('success', 'Competencia creada exitosamente');
    }
    
    public function edit(Competencia $competencia)
    {
        $cursos = Curso::with('nivel')->where('activo', true)->ordered()->get();
        return view('competencias.edit', compact('competencia', 'cursos'));
    }
    
    public function update(Request $request, Competencia $competencia)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'nombre' => 'required|string|max:250',
            'ponderacion' => 'nullable|numeric|min:0|max:100',
            'orden' => 'nullable|integer',
        ]);
        
        $competencia->update([
            'curso_id' => $request->curso_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ponderacion' => $request->ponderacion ?? 100,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return redirect()->route('admin.competencias.index')
            ->with('success', 'Competencia actualizada exitosamente');
    }
    
    public function destroy(Competencia $competencia)
    {
        if ($competencia->capacidades()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la competencia porque tiene capacidades asociadas');
        }
        
        $competencia->delete();
        
        return redirect()->route('admin.competencias.index')
            ->with('success', 'Competencia eliminada exitosamente');
    }
    
    public function toggleActive(Competencia $competencia)
    {
        $competencia->update(['activo' => !$competencia->activo]);
        return back()->with('success', 'Estado de la competencia actualizado');
    }
    
    public function getCompetenciasByCurso(Request $request)
    {
        $competencias = Competencia::where('curso_id', $request->curso_id)
                                   ->where('activo', true)
                                   ->orderBy('orden')
                                   ->get(['id', 'nombre', 'ponderacion']);
        
        return response()->json($competencias);
    }
    
    public function getJson(Competencia $competencia)
    {
        return response()->json([
            'id' => $competencia->id,
            'nombre' => $competencia->nombre,
            'ponderacion' => $competencia->ponderacion,
            'curso_id' => $competencia->curso_id,
        ]);
    }
}