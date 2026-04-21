<?php
// app/Http/Controllers/SeccionController.php

namespace App\Http\Controllers;

use App\Models\Seccion;
use Illuminate\Http\Request;

class SeccionController extends Controller
{
    public function index()
    {
        $secciones = Seccion::orderBy('nombre')->paginate(10);
        return view('secciones.index', compact('secciones'));
    }
    
    public function create()
    {
        return view('secciones.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:2|unique:secciones,nombre',
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);
        
        Seccion::create([
            'nombre' => strtoupper($request->nombre),
            'turno' => $request->turno,
            'activo' => $request->activo ?? true,
        ]);
        
        return redirect()->route('admin.secciones.index')
            ->with('success', 'Sección creada exitosamente');
    }
    
    public function edit(Seccion $seccione)
    {
        return view('secciones.edit', compact('seccione'));
    }
    
    public function update(Request $request, Seccion $seccione)
    {
        $request->validate([
            'nombre' => 'required|string|max:2|unique:secciones,nombre,' . $seccione->id,
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);
        
        $seccione->update([
            'nombre' => strtoupper($request->nombre),
            'turno' => $request->turno,
            'activo' => $request->activo ?? false,
        ]);
        
        return redirect()->route('admin.secciones.index')
            ->with('success', 'Sección actualizada exitosamente');
    }
    
    public function destroy(Seccion $seccione)
    {
        // Verificar si tiene matrículas asociadas
        if ($seccione->matriculas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la sección porque tiene matrículas asociadas');
        }
        
        $seccione->delete();
        
        return redirect()->route('admin.secciones.index')
            ->with('success', 'Sección eliminada exitosamente');
    }
    
    public function toggleActive(Seccion $seccione)
    {
        $seccione->update(['activo' => !$seccione->activo]);
        return back()->with('success', 'Estado de la sección actualizado');
    }
}