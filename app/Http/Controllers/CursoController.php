<?php
// app/Http/Controllers/CursoController.php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grado;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $query = Curso::with('grado.nivel');
        
        if ($request->filled('grado_id')) {
            $query->where('grado_id', $request->grado_id);
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%")
                  ->orWhere('codigo', 'LIKE', "%{$request->search}%");
        }
        
        $cursos = $query->orderBy('orden')->paginate(15)->withQueryString();
        $grados = Grado::with('nivel')->where('activo', true)->orderBy('nivel_id')->orderBy('orden')->get();
        $tipos = Curso::TIPOS;
        
        return view('cursos.index', compact('cursos', 'grados', 'tipos'));
    }
    
    public function create()
    {
        $grados = Grado::with('nivel')->where('activo', true)->orderBy('nivel_id')->orderBy('orden')->get();
        $tipos = Curso::TIPOS;
        return view('cursos.create', compact('grados', 'tipos'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:cursos,codigo',
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:' . implode(',', array_keys(Curso::TIPOS)),
            'grado_id' => 'required|exists:grados,id',
            'horas_semanales' => 'nullable|integer|min:1|max:40',
            'orden' => 'nullable|integer',
        ]);
        
        Curso::create([
            'codigo' => strtoupper($request->codigo),
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'grado_id' => $request->grado_id,
            'horas_semanales' => $request->horas_semanales ?? 0,
            'orden' => $request->orden ?? 0,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);
        
        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso creado exitosamente');
    }
    
    public function edit(Curso $curso)
    {
        $grados = Grado::with('nivel')->where('activo', true)->orderBy('nivel_id')->orderBy('orden')->get();
        $tipos = Curso::TIPOS;
        return view('cursos.edit', compact('curso', 'grados', 'tipos'));
    }
    
    public function update(Request $request, Curso $curso)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:cursos,codigo,' . $curso->id,
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:' . implode(',', array_keys(Curso::TIPOS)),
            'grado_id' => 'required|exists:grados,id',
            'horas_semanales' => 'nullable|integer|min:1|max:40',
            'orden' => 'nullable|integer',
        ]);
        
        $curso->update([
            'codigo' => strtoupper($request->codigo),
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'grado_id' => $request->grado_id,
            'horas_semanales' => $request->horas_semanales ?? 0,
            'orden' => $request->orden ?? 0,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);
        
        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso actualizado exitosamente');
    }
    
    public function destroy(Curso $curso)
    {
        if ($curso->competencias()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el curso porque tiene competencias asociadas');
        }
        
        $curso->delete();
        
        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso eliminado exitosamente');
    }
    
    public function toggleActive(Curso $curso)
    {
        $curso->update(['activo' => !$curso->activo]);
        return back()->with('success', 'Estado del curso actualizado');
    }
    
    public function getCursosByGrado(Request $request)
    {
        $cursos = Curso::where('grado_id', $request->grado_id)
                       ->where('activo', true)
                       ->orderBy('orden')
                       ->get(['id', 'nombre', 'codigo']);
        
        return response()->json($cursos);
    }
}