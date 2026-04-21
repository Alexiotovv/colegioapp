<?php
// app/Http/Controllers/NivelController.php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function index()
    {
        $niveles = Nivel::orderBy('orden')->paginate(10);
        return view('niveles.index', compact('niveles'));
    }
    
    public function create()
    {
        return view('niveles.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:60|unique:niveles,nombre',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
        ]);
        
        Nivel::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->activo ?? true,
        ]);
        
        return redirect()->route('admin.niveles.index')
            ->with('success', 'Nivel educativo creado exitosamente');
    }
    
    public function edit(Nivel $nivele)
    {
        return view('niveles.edit', compact('nivele'));
    }
    
    public function update(Request $request, Nivel $nivele)
    {
        $request->validate([
            'nombre' => 'required|string|max:60|unique:niveles,nombre,' . $nivele->id,
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
        ]);
        
        $nivele->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->activo ?? false,
        ]);
        
        return redirect()->route('admin.niveles.index')
            ->with('success', 'Nivel educativo actualizado exitosamente');
    }
    
    public function destroy(Nivel $nivele)
    {
        // Verificar si tiene grados asociados
        if ($nivele->grados()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el nivel porque tiene grados asociados');
        }
        
        $nivele->delete();
        
        return redirect()->route('admin.niveles.index')
            ->with('success', 'Nivel educativo eliminado exitosamente');
    }
    
    public function toggleActive(Nivel $nivele)
    {
        $nivele->update(['activo' => !$nivele->activo]);
        return back()->with('success', 'Estado del nivel actualizado');
    }
}