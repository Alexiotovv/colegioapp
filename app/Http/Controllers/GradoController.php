<?php
// app/Http/Controllers/GradoController.php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Nivel;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    public function index()
    {
        $grados = Grado::with('nivel')->orderBy('nivel_id')->orderBy('orden')->paginate(15);
        return view('grados.index', compact('grados'));
    }
    
    public function create()
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        return view('grados.create', compact('niveles'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nivel_id' => 'required|exists:niveles,id',
            'nombre' => 'required|string|max:20',
            'orden' => 'nullable|integer',
        ]);
        
        Grado::create([
            'nivel_id' => $request->nivel_id,
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? 0,
            'activo' => $request->activo ?? true,
        ]);
        
        return redirect()->route('admin.grados.index')
            ->with('success', 'Grado creado exitosamente');
    }
    
    public function edit(Grado $grado)
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        return view('grados.edit', compact('grado', 'niveles'));
    }
    
    public function update(Request $request, Grado $grado)
    {
        $request->validate([
            'nivel_id' => 'required|exists:niveles,id',
            'nombre' => 'required|string|max:20',
            'orden' => 'nullable|integer',
        ]);
        
        $grado->update([
            'nivel_id' => $request->nivel_id,
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? 0,
            'activo' => $request->activo ?? false,
        ]);
        
        return redirect()->route('admin.grados.index')
            ->with('success', 'Grado actualizado exitosamente');
    }
    
    public function destroy(Grado $grado)
    {
        // Verificar si tiene matrículas asociadas
        if ($grado->matriculas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el grado porque tiene matrículas asociadas');
        }
        
        $grado->delete();
        
        return redirect()->route('admin.grados.index')
            ->with('success', 'Grado eliminado exitosamente');
    }
    
    public function toggleActive(Grado $grado)
    {
        $grado->update(['activo' => !$grado->activo]);
        return back()->with('success', 'Estado del grado actualizado');
    }
}