<?php
// app/Http/Controllers/CapacidadController.php

namespace App\Http\Controllers;

use App\Models\Capacidad;
use App\Models\Competencia;
use Illuminate\Http\Request;

class CapacidadController extends Controller
{
    public function index(Request $request)
    {
        $query = Capacidad::with('competencia.curso');
        
        if ($request->filled('competencia_id')) {
            $query->where('competencia_id', $request->competencia_id);
        }
        
        if ($request->filled('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%");
        }
        
        $capacidades = $query->orderBy('orden')->paginate(15)->withQueryString();
        $competencias = Competencia::with('curso')->where('activo', true)->ordered()->get();
        
        return view('capacidades.index', compact('capacidades', 'competencias'));
    }
    
    public function create()
    {
        $competencias = Competencia::with('curso')->where('activo', true)->ordered()->get();
        return view('capacidades.create', compact('competencias'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'competencia_id' => 'required|exists:competencias,id',
            'nombre' => 'required|string|max:250',
            'ponderacion' => 'nullable|numeric|min:0|max:100',
            'orden' => 'nullable|integer',
        ]);
        
        Capacidad::create([
            'competencia_id' => $request->competencia_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ponderacion' => $request->ponderacion ?? 100,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return redirect()->route('admin.capacidades.index')
            ->with('success', 'Capacidad creada exitosamente');
    }
    
    public function edit(Capacidad $capacidad)
    {
        $competencias = Competencia::with('curso')->where('activo', true)->ordered()->get();
        return view('capacidades.edit', compact('capacidad', 'competencias'));
    }
    
    public function update(Request $request, Capacidad $capacidad)
    {
        $request->validate([
            'competencia_id' => 'required|exists:competencias,id',
            'nombre' => 'required|string|max:250',
            'ponderacion' => 'nullable|numeric|min:0|max:100',
            'orden' => 'nullable|integer',
        ]);
        
        $capacidad->update([
            'competencia_id' => $request->competencia_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ponderacion' => $request->ponderacion ?? 100,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return redirect()->route('admin.capacidades.index')
            ->with('success', 'Capacidad actualizada exitosamente');
    }
    
    public function destroy(Capacidad $capacidad)
    {
        if ($capacidad->notas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la capacidad porque tiene notas asociadas');
        }
        
        $capacidad->delete();
        
        return redirect()->route('admin.capacidades.index')
            ->with('success', 'Capacidad eliminada exitosamente');
    }
    
    public function toggleActive(Capacidad $capacidad)
    {
        $capacidad->update(['activo' => !$capacidad->activo]);
        return back()->with('success', 'Estado de la capacidad actualizado');
    }
}